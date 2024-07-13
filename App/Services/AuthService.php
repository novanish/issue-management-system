<?php

namespace App\Services;

use Config\AppConfig;
use Core\Database;
use Core\Exceptions\ValidationException;
use Core\Http\Cookie;
use Core\Http\Session;
use Core\ValidationHandler;
use Core\Validators\StringValidator;

/**
 * Class AuthService
 *
 * Service class for handling authentication operations.
 */
class AuthService
{

    /**
     * AuthService constructor.
     */
    public function __construct(
        private Database $db
    ) {
    }

    /**
     * Checks if a user exists with the given email.
     *
     * @param string $email The email to check.
     * @return bool True if the user exists, false otherwise.
     */
    private function doesUserExist(string $email): bool
    {
        $user = $this
            ->db
            ->query('SELECT id FROM users WHERE email = :email', compact('email'))
            ->fetch();

        return boolval($user);
    }

    /**
     * Validates sign up data.
     *
     * @param array $data The sign up data.
     * @return array Validation result.
     */
    public function validateSignUp(array $data): array
    {
        $customEmailValidator = function (string $email, StringValidator $strValidator) {
            $hasErrors = (bool) $strValidator->getError();
            if ($hasErrors) {
                return false;
            }

            return $this->doesUserExist($email);
        };

        $validators = [
            "name" => (new StringValidator($data['name']))
                ->trim()
                ->minLength(2, 'Name must be at least 2 characters long')
                ->maxLength(75, 'Name cannot exceed 75 characters')
                ->pattern('/^[A-Za-z\s]+$/', 'Name must contain only alphabetic characters'),
            "email" => (new StringValidator($data['email']))
                ->email()
                ->custom($customEmailValidator, 'Email is already taken'),
            "password" => (new StringValidator($data['password']))
                ->minLength(6, 'Password must be at least 6 characters long')
                ->maxLength(30, 'Password cannot exceed 30 characters')
                ->pattern(
                    '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,30}$/',
                    'Password must contain at least one lowercase letter, one uppercase letter, one digit, and one special character'
                ),
            "confirmPassword" => (new StringValidator($data['confirmPassword']))
                ->equals($data['password'], 'Please ensure both passwords match.'),
            "rememberMe" => (new StringValidator($data['rememberMe'], false))
        ];

        return ValidationHandler::validate($validators, $data);
    }

    /**
     * Validates sign in data.
     *
     * @param array $data The sign in data.
     * @return array Validation result.
     */
    public function validateSignIn(array $data): array
    {
        $validators = [
            "email" => (new StringValidator($data['email']))
                ->email(),
            "password" => (new StringValidator($data['password'])),
            "rememberMe" => (new StringValidator($data['rememberMe'], false))
        ];

        return ValidationHandler::validate($validators, $data);
    }

    /**
     * Signs up a user.
     *
     * @param array $data The user data.
     * @return void
     */
    public function signup(array $data): void
    {
        $hashPassword = password_hash($data["password"], PASSWORD_BCRYPT);

        $this
            ->db
            ->query('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)', [
                "name" => $data["name"],
                "email" => $data["email"],
                "password" => $hashPassword
            ]);

        $user = $this
            ->db
            ->query(
                "SELECT name, email, id, role FROM users WHERE email = :email",
                ["email" => $data['email']]
            )
            ->fetch();

        $this->setUserSession($user);

        if ($data['rememberMe'] === 'on') {
            $this->rememberUser($user['id']);
        }
    }

    /**
     * Creates a remember me token for the user.
     *
     * @param int $userId The user ID.
     * @return void
     */
    private function rememberUser(int $userId)
    {
        $rememberMeToken =  generateRememberMeToken();

        $query = <<<SQL
            INSERT INTO user_tokens (selector, hashed_validator, user_id, expiry)
                VALUES (:selector, :hashedValidator, :userId, DATE_ADD(NOW(), INTERVAL :day DAY))
        SQL;

        $day = AppConfig::REMEMBER_ME_EXPIRY_DAYS;
        $data = [
            "selector" => $rememberMeToken['selector'],
            "hashedValidator" => $rememberMeToken['hashedValidator'],
            "day" => $day,
            "userId" => $userId
        ];
        $this
            ->db
            ->query($query, $data);

        $date = new \DateTime();
        $date->modify("+ $day days");
        $expires = $date->format('D, d M Y H:i:s T');

        Cookie::set(AppConfig::REMEMBER_ME_COOKIE_NAME, $rememberMeToken['token'], ["Expires" => $expires]);
    }

    /**
     * Sets the session from the remember me token.
     *
     * @param array $parsedRememberMeToken The parsed remember me token.
     * @return void
     */
    public function setSessionFromRememberMeToken(array $parsedRememberMeToken)
    {
        $query = <<<SQL
            SELECT user_id, hashed_validator
            FROM user_tokens
            WHERE selector = :selector 
                AND expiry > NOW()
        SQL;

        $userToken = $this
            ->db
            ->query($query, ["selector" => $parsedRememberMeToken['selector']])
            ->fetch();

        if (!$userToken || !password_verify($parsedRememberMeToken['validator'], $userToken['hashed_validator'])) {
            Cookie::remove(AppConfig::REMEMBER_ME_COOKIE_NAME);
            return;
        }

        $query = <<<SQL
            SELECT name, email, id, role
            FROM users
            where id = :userId 
        SQL;

        $user = $this
            ->db
            ->query($query, ["userId" => $userToken['user_id']])
            ->fetch();

        $this->setUserSession($user);
    }

    /**
     * Signs in a user.
     *
     * @param array $data The sign in data.
     * @throws ValidationException If the sign in fails.
     * @return void
     */
    public function signin(array $data): void
    {
        $user = $this
            ->db
            ->query(
                'SELECT name, email, id, role, password FROM users WHERE email = :email',
                ["email" => $data['email']]
            )
            ->fetch();


        if (!$user || !password_verify($data['password'], $user['password'])) {
            throw new ValidationException(["formError" => "Invalid email or password."]);
        }


        unset($user['password']);
        $this->setUserSession($user);

        if ($data['rememberMe'] ?? 'off' === 'on') {
            $this->rememberUser($user['id']);
        }
    }

    /**
     * Sets the user session after successful sign up or sign in.
     *
     * @param array $userData The user data.
     * @return void
     */
    private function setUserSession(array $user): void
    {
        session_regenerate_id();
        Session::set('user', $user);
    }

    /**
     * Signs out a user by destroying the session.
     *
     * @return void
     */
    public function signout(): void
    {
        Session::destroy();

        $rememberMeToken = parseRememberMeToken();
        if (!$rememberMeToken) return;

        $query = <<<SQL
            DELETE FROM user_tokens
            WHERE selector = :selector
            LIMIT 1;
        SQL;

        $this
            ->db
            ->query($query, ['selector' => $rememberMeToken['selector']]);

        Cookie::remove(AppConfig::REMEMBER_ME_COOKIE_NAME);
    }

    /**
     * Deletes all user tokens except the current one.
     *
     * @param int $userId The user ID.
     * @return void
     */
    public function forgetTokenFromAllDeviceExpectCurrentOne(int $userId)
    {
        $query = <<<SQL
            DELETE FROM user_tokens
            WHERE user_id = :userId
                AND (:selector IS NULL OR selector != :selector)
        SQL;

        $parsedRememberMeToken = parseRememberMeToken();
        $selector = $parsedRememberMeToken ? $parsedRememberMeToken['selector'] : null;

        $this
            ->db
            ->query($query, compact('selector', 'userId'));
    }


    /**
     * Validates the data for changing the user's password.
     *
     * @param array $data The password change data.
     * @return array Validation result.
     */
    public function validateChangePassword(array $data): array
    {
        $validators = [
            "currentPassword" => (new StringValidator($data['currentPassword'])),
            "newPassword" => (new StringValidator($data['newPassword']))
                ->minLength(6, 'Password must be at least 6 characters long')
                ->maxLength(30, 'Password cannot exceed 30 characters')
                ->pattern(
                    '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,30}$/',
                    'Password must contain at least one lowercase letter, one uppercase letter, one digit, and one special character'
                ),
            "confirmNewPassword" => (new StringValidator($data['confirmNewPassword']))
                ->equals($data['newPassword'], 'Please ensure both passwords match.')
        ];

        return ValidationHandler::validate($validators, $data);
    }

    /**
     * Changes the password for the current user.
     *
     * @param array $data The password change data.
     * @throws ValidationException If the current password is incorrect or if the new password is the same as the current one.
     * @return void
     */
    public function changePassword(array $data): void
    {
        $sessionUser = Session::get('user');
        $user = $this
            ->db
            ->query(
                'SELECT password FROM users WHERE id = :id AND email = :email',
                ['id' => $sessionUser['id'], 'email' => $sessionUser['email']]
            )
            ->fetch();

        $isProvidedCurrentPasswordCorrect = password_verify($data['currentPassword'], $user['password']);
        if (!$isProvidedCurrentPasswordCorrect) {
            throw new ValidationException(["formError" => "The current password provided is incorrect. Please check your credentials and try again."]);
        }

        $isNewPasswordSameAsCurrent = $data['newPassword'] === $data['currentPassword'];
        if ($isNewPasswordSameAsCurrent) {
            throw new ValidationException(["formError" => "The new password cannot be the same as the current one. Please choose a different password."]);
        }

        $hashedPassword = password_hash($data['newPassword'], PASSWORD_BCRYPT);
        $query = 'UPDATE users  SET password = :password WHERE id = :id AND email = :email';
        $this->db->query(
            $query,
            ["password" => $hashedPassword, "email" => $sessionUser['email'], "id" => $sessionUser['id']]
        );

        $this->forgetTokenFromAllDeviceExpectCurrentOne($sessionUser['id']);
    }
}

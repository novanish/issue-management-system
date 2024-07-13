<?php

namespace App\Controllers;

use Core\Container;
use Core\Http\Request;
use App\Services\AuthService;
use Core\Router;
use Error;

/**
 * Class AuthController
 *
 * Controller class for handling authentication related actions.
 */
class AuthController extends \Core\Controller
{
    /**
     * @var AuthService The authentication service instance.
     */
    private AuthService $authService;

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->authService = Container::resolve(AuthService::class);
    }

    /**
     * Displays the sign-in form.
     *
     * Method: GET
     * Path: '/auth/signin'
     * Description: Renders the sign-in form view.
     *
     * @return void
     */
    public function signinView()
    {
        $this
            ->setPageTitle('Sign In')
            ->addStyle(css('/form'))
            ->renderView('/auth/signin');
    }

    /**
     * Displays the sign-up form.
     *
     * Method: GET
     * Path: '/auth/signup'
     * Description: Renders the sign-up form view.
     *
     * @return void
     */
    public function signupView()
    {
        $this
            ->setPageTitle('Sign Up')
            ->addStyle(css('/form'))
            ->renderView('/auth/signup');
    }

    /**
     * Handles the sign-up action.
     *
     * Method: POST
     * Path: '/auth/signup'
     * Description: Validates sign-up data and registers a new user.
     *
     * @return void
     */
    public function signup()
    {
        $data = $this
            ->authService
            ->validateSignUp(Request::body());

        $this->authService->signup($data);

        $redirectTo = Request::getQueryParameter('redirectTo', '/');
        Router::redirectTo($redirectTo);
    }

    /**
     * Handles the sign-in action.
     *
     * Method: POST
     * Path: '/auth/signin'
     * Description: Validates sign-in data and signs in the user.
     *
     * @return void
     */
    public function signin()
    {
        $data = $this
            ->authService
            ->validateSignIn(Request::body());

        $this->authService->signin($data);

        $redirectTo = Request::getQueryParameter('redirectTo', '/');

        Router::redirectTo($redirectTo);
    }


    /**
     * Handles the sign-out action.
     *
     * Method: POST
     * Path: '/auth/signout'
     * Description: Signs out the current user.
     *
     * @return void
     */
    public function signout()
    {
        $this->authService->signout();

        $redirectTo = Request::getQueryParameter('redirectTo', '/');
        Router::redirectTo($redirectTo);
    }

    /**
     * Displays the change password form.
     *
     * Method: GET
     * Path: '/auth/change-password'
     * Description: Renders the change password form view.
     *
     * @param string|null $message An optional message to display on the form.
     * @return void
     */
    public function changePasswordView($message = null)
    {
        $this
            ->setPageTitle('Change Password')
            ->addStyle(css('/form'))
            ->renderView('/auth/change-password', ['successFormMessage' => $message]);
    }

    /**
     * Handles the change password action.
     *
     * Method: PUT
     * Path: '/auth/change-password'
     * Description: Validates the change password data and updates the user's password.
     *
     * @throws ValidationException If the provided data is invalid or the current password is incorrect.
     * @return void
     */
    public function changePassword()
    {

        $data = $this
            ->authService
            ->validateChangePassword(Request::body());

        $this
            ->authService
            ->changePassword($data);

        $this->changePasswordView('Your password has been successfully changed.');
    }
}

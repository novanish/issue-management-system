<?php

namespace App\Services;

use Core\Database;

/**
 * Class UserService
 *
 * Service class for user management operations.
 */
class UserService
{
    /**
     * UserService constructor.
     *
     * @param Database $db The database instance.
     */
    public function __construct(
        private Database $db
    ) {
    }


    /**
     * Get all users from the database.
     *
     * @return array An array containing user IDs, names, and roles.
     */
    public function getAllUsers(): array
    {
        return $this
            ->db
            ->query('SELECT id, name, role FROM users')
            ->fetchAll();
    }

    /**
     * Get a user by their ID.
     *
     * @param int $id The user ID.
     * @return array|null An array containing the user's ID, name, and role, or null if not found.
     */
    public function getUserById(int $id)
    {
        return $this
            ->db
            ->query('SELECT id, name, role FROM users WHERE id = :id', compact('id'))
            ->fetch();
    }

    /**
     * Check if a user exists with the given ID.
     *
     * @param int $id The user ID.
     * @return bool True if the user exists, false otherwise.
     */
    public function doesUserExistWithId(int $id): bool
    {
        $user = $this
            ->db
            ->query('SELECT id FROM users WHERE id = :id', compact('id'))
            ->fetch();

        return boolval($user);
    }

    /**
     * Check if a user exists with the given email address.
     *
     * @param string $email The user's email address.
     * @return bool True if the user exists, false otherwise.
     */
    function doesUserExistWithEmail(string $email): bool
    {
        $user = $this
            ->db
            ->query('SELECT id FROM users WHERE email = :email', compact('email'))
            ->fetch();

        return boolval($user);
    }
}

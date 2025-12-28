<?php

namespace app\commands;

use app\models\User;

class UserCreateCommand implements Command
{
    public function execute(array $args): void
    {
        $username = $args[0] ?? null;
        $email = $args[1] ?? null;
        $password = $args[2] ?? null;

        if (!$username || !$email || !$password) {
            echo "Error: Missing arguments.\n";
            echo "Usage: " . $this->getUsage() . "\n";
            exit(1);
        }

        // Check if user exists
        $userModel = new User();
        $existingUsername = $userModel->eq('username', $username)->findAll();
        $existingEmail = $userModel->eq('email', $email)->findAll();

        if (!empty($existingUsername) || !empty($existingEmail)) {
            echo "Error: User with that username or email already exists.\n";
            exit(1);
        }

        // Create user
        try {
            $user = new User();
            $user->username = $username;
            $user->email = $email;
            $user->password_hash = password_hash($password, PASSWORD_DEFAULT);
            $user->save();
            echo "Success: User '{$username}' created.\n";
        } catch (\Exception $e) {
            echo "Error creating user: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    public function getDescription(): string
    {
        return "Create a new admin user";
    }

    public function getUsage(): string
    {
        return "user:create <username> <email> <password>";
    }
}

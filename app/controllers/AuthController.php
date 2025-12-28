<?php

namespace app\controllers;

use Flight;
use app\models\User;

class AuthController
{
    public function loginData()
    {
        // If already logged in, redirect to dashboard
        if (Flight::session()->get('user_id')) {
            Flight::redirect('/admin');
            return;
        }

        // Render login view (no layout needed for login usually, or a simple one)
        Flight::render('login.latte', []);
    }

    public function login()
    {
        $data = Flight::request()->data;
        $username = $data->username;
        $password = $data->password;

        if (!$username || !$password) {
            Flight::render('login.latte', ['error' => 'Username and password are required.']);
            return;
        }

        // Find user
        $userModel = new User();
        $users = $userModel->eq('username', $username)->findAll();
        $user = !empty($users) ? $users[0] : null;

        if ($user && password_verify($password, $user->password_hash)) {
            // Success
            Flight::session()->set('user_id', $user->id);
            Flight::redirect('/admin');
        } else {
            // Failure
            Flight::render('login.latte', ['error' => 'Invalid credentials.', 'username' => $username]);
        }
    }

    public function logout()
    {
        // Ensure session is started
        Flight::session();
        session_destroy();
        Flight::redirect('/login');
    }
}

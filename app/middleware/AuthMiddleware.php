<?php

namespace app\middleware;

use Flight;

class AuthMiddleware
{
    public function before()
    {
        // Simple session check
        // In a real app, you'd check $_SESSION['user_id'] or similar
        // For phase 1 verification, we'll skipping strict check if strictly checking session,
        // but let's implement the structure.

        $user = Flight::session()->get('user_id');
        if (!$user) {
            Flight::halt(401, json_encode(['error' => 'Unauthorized']));
            exit;
        }
    }
}

<?php

require 'vendor/autoload.php';

use app\core\Database;

// Load Config
$config = require 'app/config/config.php';

// Register Database
Flight::register('db', 'PDO', ["sqlite:{$config['database_path']}"], function ($db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec('PRAGMA journal_mode = WAL;');
    $db->exec('PRAGMA foreign_keys = ON;');
});

// Initialize Schema
try {
    // Ensure database directory exists
    if (!is_dir(dirname($config['database_path']))) {
        mkdir(dirname($config['database_path']), 0777, true);
    }
    Database::init();
} catch (Exception $e) {
    // Handle specific DB init errors if needed
    echo "Database Error: " . $e->getMessage();
    exit;
}

// Register Session Wrapper
Flight::map('session', function () {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return new class {
        public function get($key)
        {
            return $_SESSION[$key] ?? null;
        }
        public function set($key, $value)
        {
            $_SESSION[$key] = $value;
        }
    };
});

// Load Routes
require 'app/config/routes.php';

Flight::start();

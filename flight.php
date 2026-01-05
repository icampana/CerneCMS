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

// Configure Flight
Flight::set('flight.views.path', __DIR__ . '/content/themes/default');
Flight::set('flight.log_errors', true);

// Register Latte
Flight::register('view', 'Latte\Engine', [], function ($latte) {
    // Configure cache directory
    $cacheDir = __DIR__ . '/public/cache';

    // Attempt to create if missing
    if (!file_exists($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }

    // Check availability
    if (!is_dir($cacheDir) || !is_writable($cacheDir)) {
        // Variables for the template
        $path = str_replace(__DIR__ . '/', '', $cacheDir);
        $error = !file_exists($cacheDir) ? 'Directory could not be created' : 'Directory is not writable';

        http_response_code(500);
        require __DIR__ . '/app/views/errors/permission.php';
        die();
    }
    $latte->setTempDirectory($cacheDir);

    // Configure FileLoader
    $viewDir = Flight::get('flight.views.path');
    $latte->setLoader(new \Latte\Loaders\FileLoader($viewDir));

    // Register Menu & Settings Helpers
    $latte->addFunction('setting', fn(string $key, $default = null) => \app\models\Settings::get($key, $default));
    $latte->addFunction('menu', fn(string $slug, array $opts = []) => \app\services\MenuService::render($slug, $opts));
    $latte->addFunction('localMenu', function ($page = null) {
        // If page not passed, try to find it from Flight context or just pass null if not available easily
        // But templates usually have $page available if passed from controller.
        return \app\services\MenuService::renderLocalMenu($page);
    });
    $latte->addFunction('breadcrumbs', fn($page = null) => \app\services\MenuService::renderBreadcrumbs($page));
    $latte->addFunction('showSidebar', fn($page = null) => \app\services\MenuService::shouldShowSidebar($page));
});

// Override Flight::render to use Latte
Flight::map('render', function ($template, $data) {
    echo Flight::view()->render($template, $data);
});

// Load Routes
require 'app/config/routes.php';

Flight::start();

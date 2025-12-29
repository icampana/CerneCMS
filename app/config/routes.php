<?php

use app\controllers\AdminController;
use app\controllers\ApiController;
use app\controllers\AuthController;
use app\controllers\FrontendController;

// Auth Routes
Flight::route('GET /login', [AuthController::class, 'loginData']);
Flight::route('POST /login', [AuthController::class, 'login']);
Flight::route('GET /logout', [AuthController::class, 'logout']);

// Admin Routes
Flight::group('/admin', function () {
    Flight::route('/', [AdminController::class, 'dashboard']);
    Flight::route('/dashboard', [AdminController::class, 'dashboard']);
}, [new \app\middleware\AuthMiddleware()]);

// API Routes
Flight::group('/api', function () {
    // Page Search (must come before @id)
    Flight::route('GET /pages/search', [ApiController::class, 'searchPages']);
    Flight::route('GET /pages', [ApiController::class, 'getPages']);
    Flight::route('GET /pages/@id', [ApiController::class, 'getPage']);
    Flight::route('POST /pages', [ApiController::class, 'savePage']);

    // Media Routes
    Flight::route('GET /media', [ApiController::class, 'getMedia']);
    Flight::route('POST /media/upload', [ApiController::class, 'uploadMedia']);

    // Calendar Routes
    Flight::route('GET /calendar/events', [app\controllers\CalendarController::class, 'getEvents']);
    Flight::route('POST /calendar/events', [app\controllers\CalendarController::class, 'addEvent']);
    Flight::route('PUT /calendar/events/@id', [app\controllers\CalendarController::class, 'updateEvent']);
    Flight::route('DELETE /calendar/events/@id', [app\controllers\CalendarController::class, 'deleteEvent']);

    // Menu Routes
    Flight::route('GET /menus', [\app\controllers\MenuController::class, 'index']);
    Flight::route('POST /menus', [\app\controllers\MenuController::class, 'create']);
    Flight::route('GET /menus/@id', [\app\controllers\MenuController::class, 'show']);
    Flight::route('PUT /menus/@id', [\app\controllers\MenuController::class, 'update']);
    Flight::route('DELETE /menus/@id', [\app\controllers\MenuController::class, 'delete']);

    Flight::route('POST /menus/@id/items', [\app\controllers\MenuController::class, 'addItem']);
    Flight::route('PUT /menu-items/reorder', [\app\controllers\MenuController::class, 'reorderItems']);
    Flight::route('PUT /menu-items/@id', [\app\controllers\MenuController::class, 'updateItem']);
    Flight::route('DELETE /menu-items/@id', [\app\controllers\MenuController::class, 'deleteItem']);

    // Settings Routes
    Flight::route('GET /settings', [\app\controllers\SettingsController::class, 'index']);
    Flight::route('PUT /settings', [\app\controllers\SettingsController::class, 'update']);
    Flight::route('POST /settings/cache-clear', [\app\controllers\SettingsController::class, 'clearCache']);
}, [new \app\middleware\AuthMiddleware()]);

// Frontend Routes (Catch-all for pages)
Flight::route('/', [FrontendController::class, 'renderPage']);
Flight::route('/@slug', [FrontendController::class, 'renderPage']);


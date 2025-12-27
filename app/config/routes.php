<?php

use app\controllers\AdminController;
use app\controllers\ApiController;
use app\controllers\FrontendController;

// Admin Routes
Flight::group('/admin', function () {
    Flight::route('/', [AdminController::class, 'dashboard']);
    Flight::route('/dashboard', [AdminController::class, 'dashboard']);
});

// Dev Auth Route (Temporary)
Flight::route('GET /dev/login', function () {
    Flight::session()->set('user_id', 1);
    Flight::redirect('/admin');
});

// API Routes
Flight::group('/api', function () {
    Flight::route('GET /pages', [ApiController::class, 'getPages']);
    Flight::route('GET /pages/@id', [ApiController::class, 'getPage']);
    Flight::route('POST /pages', [ApiController::class, 'savePage']);
}, [new \app\middleware\AuthMiddleware()]);

// Frontend Routes (Catch-all for pages)
Flight::route('/', [FrontendController::class, 'renderPage']);
Flight::route('/@slug', [FrontendController::class, 'renderPage']);

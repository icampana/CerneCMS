<?php

use app\controllers\AdminController;
use app\controllers\ApiController;
use app\controllers\FrontendController;

// Admin Routes
Flight::group('/admin', function () {
    Flight::route('/', [AdminController::class, 'dashboard']);
    Flight::route('/dashboard', [AdminController::class, 'dashboard']);
});

// API Routes
Flight::group('/api', function () {
    Flight::route('GET /pages', [ApiController::class, 'getPages']);
    Flight::route('GET /pages/@id', [ApiController::class, 'getPage']);
    Flight::route('POST /pages', [ApiController::class, 'savePage']);
}, [new \app\middleware\AuthMiddleware()]);

// Frontend Catch-all
// We use a pattern that matches everything not already matched
Flight::route('/*', [FrontendController::class, 'render']);

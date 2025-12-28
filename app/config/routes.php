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
    Flight::route('GET /pages', [ApiController::class, 'getPages']);
    Flight::route('GET /pages/@id', [ApiController::class, 'getPage']);
    Flight::route('POST /pages', [ApiController::class, 'savePage']);
    Flight::route('GET /media', [ApiController::class, 'getMedia']);
    Flight::route('POST /media', [ApiController::class, 'uploadMedia']);

    // Calendar Routes
    Flight::route('GET /calendar/events', [app\controllers\CalendarController::class, 'getEvents']);
    Flight::route('POST /calendar/events', [app\controllers\CalendarController::class, 'addEvent']);
    Flight::route('PUT /calendar/events/@id', [app\controllers\CalendarController::class, 'updateEvent']);
    Flight::route('DELETE /calendar/events/@id', [app\controllers\CalendarController::class, 'deleteEvent']);
}, [new \app\middleware\AuthMiddleware()]);

// Frontend Routes (Catch-all for pages)
Flight::route('/', [FrontendController::class, 'renderPage']);
Flight::route('/@slug', [FrontendController::class, 'renderPage']);


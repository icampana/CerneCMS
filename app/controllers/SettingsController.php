<?php

namespace app\controllers;

use Flight;
use app\models\Settings;

class SettingsController
{
    public function index()
    {
        Flight::json(Settings::getAll());
    }

    public function update()
    {
        $json = Flight::request()->getBody();
        $data = json_decode($json, true);

        if ($data) {
            foreach ($data as $key => $value) {
                if (!empty($key)) {
                    // Convert booleans to 1/0 for database if necessary,
                    // though SQLite/ActiveRecord should handle it.
                    Settings::set($key, $value);
                }
            }
        }

        Flight::json(['success' => true]);
    }

    public function clearCache()
    {
        if (\app\helpers\Cache::clear()) {
            Flight::json(['success' => true, 'message' => 'Cache cleared']);
        } else {
            Flight::halt(500, json_encode(['error' => 'Failed to clear cache']));
        }
    }
}

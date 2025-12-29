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
        $data = Flight::request()->data;
        // Expects dictionary of key-values

        foreach ($data as $key => $value) {
            Settings::set($key, $value);
        }

        Flight::json(['success' => true]);
    }
}

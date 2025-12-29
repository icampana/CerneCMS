<?php

namespace app\models;

use flight\ActiveRecord;

class Settings extends ActiveRecord
{
    public function __construct($db = null)
    {
        parent::__construct($db, 'settings');
    }

    protected function onConstruct(self $self, array &$config)
    {
        $config['connection'] = \Flight::db();
        $config['primary_key'] = 'key'; // Override typical 'id'
    }

    public static function get($key, $default = null)
    {
        $model = new self();
        $setting = $model->eq('key', $key)->find();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value)
    {
        $model = new self();
        $setting = $model->eq('key', $key)->find();

        if (!$setting) {
            $setting = new self();
            $setting->key = $key;
        }

        $setting->value = $value;
        $setting->updated_at = date('Y-m-d H:i:s');
        $setting->save();
    }

    public static function getAll()
    {
        $model = new self();
        $all = $model->findAll();
        $result = [];
        foreach ($all as $setting) {
            $result[$setting->key] = $setting->value;
        }
        return $result;
    }
}

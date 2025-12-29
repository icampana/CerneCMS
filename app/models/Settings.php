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
        $config['auto_increment'] = false;
    }

    public static function get($key, $default = null)
    {
        $db = \Flight::db();
        $stmt = $db->prepare('SELECT value FROM settings WHERE "key" = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? $row['value'] : $default;
    }

    public static function set($key, $value)
    {
        $db = \Flight::db();
        $stmt = $db->prepare('INSERT OR REPLACE INTO settings ("key", value, updated_at) VALUES (?, ?, ?)');
        return $stmt->execute([$key, $value, date('Y-m-d H:i:s')]);
    }

    public static function getAll()
    {
        $db = \Flight::db();
        $stmt = $db->query('SELECT "key", value FROM settings');
        $all = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        foreach ($all as $row) {
            $result[$row['key']] = $row['value'];
        }
        return $result;
    }
}

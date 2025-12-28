<?php

namespace app\models;

use flight\ActiveRecord;

class User extends ActiveRecord
{

    public function __construct($db = null)
    {
        parent::__construct($db, 'users');
    }

    protected function onConstruct(self $self, array &$config)
    {
        $config['connection'] = \Flight::db();
    }
}

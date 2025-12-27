<?php

namespace app\models;

use flight\ActiveRecord;

class Post extends ActiveRecord
{
    public function __construct($db = null)
    {
        parent::__construct($db, 'posts');
    }

    protected function onConstruct(self $self, array &$config)
    {
        $config['connection'] = \Flight::db();
    }
}

<?php

namespace app\models;

use flight\ActiveRecord;

class Page extends ActiveRecord
{

    public function __construct($db = null)
    {
        parent::__construct($db, 'pages');
    }

    protected function onConstruct(self $self, array &$config)
    {
        $config['connection'] = \Flight::db();
    }
}

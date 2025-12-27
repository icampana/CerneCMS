<?php

namespace app\models;

use flight\ActiveRecord;

class Block extends ActiveRecord
{

    public function __construct($db = null)
    {
        parent::__construct($db, 'blocks');
    }

    protected function onConstruct(self $self, array &$config)
    {
        $config['connection'] = \Flight::db();
    }

    // Helper to decode JSON content automatically if needed,
    // but typically we handle this in the Controller or let frontend handle JSON.
}

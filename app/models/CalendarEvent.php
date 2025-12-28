<?php

namespace app\models;

use flight\ActiveRecord;

class CalendarEvent extends ActiveRecord
{
    public function __construct($db = null)
    {
        parent::__construct($db, 'calendar_events');
    }

    protected function onConstruct(self $self, array &$config)
    {
        $config['connection'] = \Flight::db();
    }
}

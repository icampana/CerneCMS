<?php

namespace app\models;

use flight\ActiveRecord;

class Menu extends ActiveRecord
{
    public function __construct($db = null)
    {
        parent::__construct($db, 'menus');
    }

    protected function onConstruct(self $self, array &$config)
    {
        $config['connection'] = \Flight::db();
    }

    /**
     * Get menu items organized hierarchically (max 2 levels)
     */
    public function getItems()
    {
        $itemModel = new MenuItem();
        $allItems = $itemModel->eq('menu_id', $this->id)->order('sort_order ASC')->findAll();

        $tree = [];
        $itemsById = [];

        // First pass: Index by ID and identifying top-level items
        foreach ($allItems as $item) {
            $item->children = []; // Initialize children array
            $itemsById[$item->id] = $item;
        }

        // Second pass: Build tree
        foreach ($itemsById as $id => $item) {
            if ($item->parent_id && isset($itemsById[$item->parent_id])) {
                $itemsById[$item->parent_id]->children[] = $item;
            } else {
                $tree[] = $item;
            }
        }

        return $tree;
    }

    /**
     * Get the primary menu
     */
    public static function getPrimary()
    {
        $model = new self();
        $menus = $model->eq('is_primary', 1)->findAll();
        return !empty($menus) ? $menus[0] : null;
    }

    /**
     * Set this menu as primary and unset others
     */
    public function setAsPrimary()
    {
        $db = \Flight::db();
        $db->exec("UPDATE menus SET is_primary = 0");

        $this->is_primary = 1;
        $this->save();
    }
}

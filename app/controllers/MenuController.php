<?php

namespace app\controllers;

use Flight;
use app\models\Menu;
use app\models\MenuItem;

class MenuController
{
    /**
     * List all menus
     */
    public function index()
    {
        $model = new Menu();
        $menus = $model->findAll();
        Flight::json($menus);
    }

    /**
     * Create a new menu
     */
    public function create()
    {
        $data = Flight::request()->data;

        try {
            $menu = new Menu();
            $menu->name = $data->name;
            $menu->slug = $data->slug;
            $menu->is_primary = isset($data->is_primary) && $data->is_primary ? 1 : 0;
            $menu->save();

            if ($menu->is_primary) {
                $menu->setAsPrimary();
            }

            Flight::json($menu);
        } catch (\Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    }

    /**
     * Get a single menu with items
     */
    public function show($id)
    {
        $model = new Menu();
        $menu = $model->eq('id', $id)->find();

        if (!$menu) {
            Flight::halt(404, 'Menu not found');
            return;
        }

        $items = $menu->getItems();
        $menu->items = $items;

        Flight::json($menu);
    }

    /**
     * Update a menu
     */
    public function update($id)
    {
        $data = Flight::request()->data; // This is a Collection wrapper in Flight usually, but json body access
        // Flight::request()->data is for form data. JSON body is Flight::request()->getBody() decoded or data->getData()?
        // Standard Flight setup for JSON: $data = Flight::request()->data; works if content-type is json.

        $model = new Menu();
        $menu = $model->eq('id', $id)->find();

        if (!$menu) {
            Flight::halt(404, 'Menu not found');
            return;
        }

        if (isset($data->name))
            $menu->name = $data->name;
        if (isset($data->slug))
            $menu->slug = $data->slug;

        $wasPrimary = $menu->is_primary;
        $isPrimary = isset($data->is_primary) ? $data->is_primary : $wasPrimary;

        $menu->is_primary = $isPrimary ? 1 : 0;
        $menu->save();

        if ($isPrimary && !$wasPrimary) {
            $menu->setAsPrimary();
        }

        Flight::json($menu);
    }

    public function delete($id)
    {
        $model = new Menu();
        $menu = $model->eq('id', $id)->find();
        if ($menu) {
            // Dependencies (items) are ON DELETE CASCADE in DB
            $db = Flight::db();
            $db->exec("DELETE FROM menus WHERE id = " . intval($id));
            Flight::json(['success' => true]);
        } else {
            Flight::halt(404, 'Menu not found');
        }
    }

    // --- Items ---

    public function addItem($id)
    {
        $data = Flight::request()->data;

        try {
            $item = new MenuItem();
            $item->menu_id = $id;
            $item->parent_id = $data->parent_id ?? null; // Can be null
            $item->title = $data->title;
            $item->link_type = $data->link_type; // internal, external, anchor
            $item->link_value = $data->link_value ?? '';
            $item->target_page_id = $data->target_page_id ?? null;
            $item->is_external = ($data->link_type === 'external') ? 1 : 0;
            $item->open_new_tab = isset($data->open_new_tab) && $data->open_new_tab ? 1 : 0;

            // Get max sort order
            $db = Flight::db();
            $stmt = $db->query("SELECT MAX(sort_order) as max_order FROM menu_items WHERE menu_id = " . intval($id));
            $res = $stmt->fetch();
            $item->sort_order = ($res['max_order'] ?? 0) + 1;

            $item->save();
            Flight::json($item);
        } catch (\Exception $e) {
            Flight::halt(400, $e->getMessage());
        }
    }

    public function updateItem($id)
    {
        $data = Flight::request()->data;
        $model = new MenuItem();
        $item = $model->eq('id', $id)->find();

        if (!$item) {
            Flight::halt(404, 'Item not found');
        }

        if (isset($data->title))
            $item->title = $data->title;
        if (isset($data->link_type))
            $item->link_type = $data->link_type;
        if (isset($data->link_value))
            $item->link_value = $data->link_value;
        if (isset($data->target_page_id))
            $item->target_page_id = $data->target_page_id;
        if (isset($data->open_new_tab))
            $item->open_new_tab = $data->open_new_tab ? 1 : 0;

        // Handling parent_id change is tricky if drag-drop reorder is separate.
        // We'll handle parent_id in reorder or specific update.
        if (property_exists($data, 'parent_id'))
            $item->parent_id = $data->parent_id;

        $item->save();
        Flight::json($item);
    }

    public function deleteItem($id)
    {
        $db = Flight::db();
        $db->exec("DELETE FROM menu_items WHERE id = " . intval($id));
        Flight::json(['success' => true]);
    }

    /**
     * Batch reorder/reparent items
     * Expects payload: [ {id: 1, parent_id: null, sort_order: 0}, {id: 2, parent_id: 1, sort_order: 1} ...]
     */
    public function reorderItems()
    {
        $items = Flight::request()->data;
        $db = Flight::db();
        $db->beginTransaction();

        try {
            foreach ($items as $item) {
                // Update each
                $stmt = $db->prepare("UPDATE menu_items SET parent_id = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([
                    $item['parent_id'] ?? null,
                    $item['sort_order'],
                    $item['id']
                ]);
            }
            $db->commit();
            Flight::json(['success' => true]);
        } catch (\Exception $e) {
            $db->rollBack();
            Flight::halt(400, $e->getMessage());
        }
    }
}

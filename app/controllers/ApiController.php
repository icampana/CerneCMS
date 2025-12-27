<?php

namespace app\controllers;

use Flight;
use app\models\Page;
use app\models\Block;

class ApiController
{

    public function getPages()
    {
        $pageModel = new Page();
        $pages = $pageModel->findAll();
        Flight::json($pages);
    }

    public function getPage($id)
    {
        $pageModel = new Page();
        $page = $pageModel->find($id);
        if ($page) {
            Flight::json($page);
        } else {
            Flight::halt(404, json_encode(['error' => 'Page not found']));
        }
    }

    public function savePage()
    {
        $data = Flight::request()->data;

        // Basic Validation
        if (empty($data->title) || empty($data->slug)) {
            Flight::halt(400, json_encode(['error' => 'Title and Slug are required']));
            return;
        }

        // 1. Handle Page
        // Flight ActiveRecord: custom queries use fluid interface
        $pageModel = new Page();
        // find() returns array of records if conditions are messed up?
        // Docs say: $model->eq('column', 'value')->find(); returns (one) object or false
        // findAll() returns array.

        $pages = $pageModel->eq('slug', $data->slug)->findAll();
        $page = !empty($pages) ? $pages[0] : null;

        if (!$page) {
            $page = new Page();
            $page->slug = $data->slug;
        }

        $page->title = $data->title;
        $page->updated_at = date('Y-m-d H:i:s');
        $page->save();

        // 2. Handle Content (Block)
        // We assume a single 'main' block for now which holds the Tiptap content
        $blockModel = new Block();
        // ActiveRecord find usually returns the object or false.
        // We need to query by page_id AND zone.
        // Flight ActiveRecord might simple array conditions.
        // findAll returns array
        $blocks = $blockModel->eq('page_id', $page->id)->eq('zone', 'main')->findAll();
        $block = !empty($blocks) ? $blocks[0] : null;

        if (!$block) {
            $block = new Block();
            $block->page_id = $page->id; // $page->id should be populated after save()
            $block->zone = 'main';
            $block->type = 'tiptap';
        }

        // Content is passed as a generic object/array from JSON body, we store as string
        $block->content_json = json_encode($data->content);
        $block->updated_at = date('Y-m-d H:i:s');
        $block->save();

        Flight::json([
            'status' => 'success',
            'data' => [
                'page' => $page->item, // Flight AR item data
                'id' => $page->id
            ]
        ]);
    }
}

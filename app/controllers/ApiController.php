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
        // Logic to save page and blacks would go here
        Flight::json(['status' => 'success', 'data' => $data]);
    }
}

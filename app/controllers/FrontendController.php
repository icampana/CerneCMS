<?php

namespace app\controllers;

use Flight;
use app\models\Page;

class FrontendController
{

    public function render($slug = 'home')
    {
        // If slug is empty or root, default to home
        if (!$slug)
            $slug = 'home';

        $pageModel = new Page();
        // Use findOne equivalent
        $page = $pageModel->eq('slug', $slug)->find();

        // Flight Active Record might return an empty object, so we check for primary key
        if ($page->id) {
            // In the future this will load the theme layout
            echo "<h1>" . htmlspecialchars($page->title ?? '') . "</h1>";
        } else {
            Flight::halt(404, "Page '$slug' not found.");
        }
    }
}

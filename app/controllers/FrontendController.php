<?php

namespace app\controllers;

use Flight;
use app\models\Page;

class FrontendController
{

    public function renderPage($slug = null)
    {
        // 0. Maintenance Mode Check
        $isMaintenance = \app\models\Settings::get('maintenance_mode', false);
        $isAdmin = \Flight::session()->get('user_id') !== null;

        if ($isMaintenance && !$isAdmin) {
            Flight::render('maintenance.latte', []);
            return;
        }

        // 1. Determine Slug (Default to 'home' for root path)
        // If route is /*, Flight passes the full path. We need to handle that.
        // But we will change route to /@slug
        $slug = $slug ?? 'home';
        if ($slug === '/')
            $slug = 'home'; // normalize if Flight passes /

        // 2. Fetch Page
        $pageModel = new \app\models\Page();
        // Uses the findAll [0] pattern we verified works
        $pages = $pageModel->eq('slug', $slug)->findAll();
        $page = !empty($pages) ? $pages[0] : null;

        if (!$page) {
            Flight::halt(404, "Page '{$slug}' not found.");
            return;
        }

        // 3. Fetch Block Content
        $blockModel = new \app\models\Block();
        $blocks = $blockModel->eq('page_id', $page->id)->eq('zone', 'main')->findAll();
        $block = !empty($blocks) ? $blocks[0] : null;

        // 4. Render Content
        $htmlContent = '';
        if ($block && !empty($block->content_json)) {
            $renderer = new \app\services\BlockRenderer();
            $htmlContent = $renderer->render($block->content_json);
        }

        // 5. Render View
        Flight::render('page.latte', [
            'title' => $page->title,
            'page' => $page, // Pass the Active Record object
            'content' => $htmlContent
        ]);
    }
}

<?php

namespace app\models;

use flight\ActiveRecord;

class MenuItem extends ActiveRecord
{
    public function __construct($db = null)
    {
        parent::__construct($db, 'menu_items');
    }

    protected function onConstruct(self $self, array &$config)
    {
        $config['connection'] = \Flight::db();
    }

    /**
     * Resolve the full URL for this item
     */
    public function getUrl()
    {
        if ($this->link_type === 'external') {
            return $this->link_value;
        }

        if ($this->link_type === 'internal') {
            // Check if we have the target page cached/available or fetch it
            if ($this->target_page_id) {
                // We're doing a simplified lookup here.
                // For high performance, we might want to join tables in the Menu::getItems query later.
                $pageModel = new Page();
                $page = $pageModel->eq('id', $this->target_page_id)->find();
                if ($page) {
                    return $page->slug === 'home' ? '/' : '/' . $page->slug;
                }
            }
            // Fallback if page not found
            return '#';
        }

        if ($this->link_type === 'anchor') {
            // format: page_id#anchor or just #anchor
            // parse link_value which we'll store as "page_id#anchor" or just "#anchor"
            // Actually, plan said link_value stores "page_slug#anchor".
            // Let's assume link_value holds the full reference for anchors or we rely on target_page_id

            if ($this->target_page_id) {
                $pageModel = new Page();
                $page = $pageModel->eq('id', $this->target_page_id)->find();
                if ($page) {
                    $base = $page->slug === 'home' ? '/' : '/' . $page->slug;
                    return $base . '#' . $this->link_value;
                }
            }
            return '#' . $this->link_value;
        }

        return '#';
    }

    public function isActive($currentSlug)
    {
        $url = $this->getUrl();
        $currentUrl = $currentSlug === 'home' ? '/' : '/' . $currentSlug;

        // Exact match
        if ($url === $currentUrl) {
            return true;
        }

        // TODO: Handle parent active state or partial matching if needed
        return false;
    }
}

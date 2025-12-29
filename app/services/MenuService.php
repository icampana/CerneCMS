<?php

namespace app\services;

use app\models\Menu;
use app\models\Page;
use app\models\Settings;

class MenuService
{
    /**
     * Render a menu by slug
     */
    public static function render(string $slug, array $options = []): string
    {
        if ($slug === 'primary') {
            $menu = Menu::getPrimary();
        } else {
            $menuModel = new Menu();
            $menu = $menuModel->eq('slug', $slug)->find();
        }

        if (!$menu) {
            return '';
        }

        $items = $menu->getItems();
        if (empty($items)) {
            return '';
        }

        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        // Normalize
        $currentPath = rtrim($currentPath, '/') ?: '/';

        // Match against page slugs if needed, but we'll use simple URL matching
        // Actually, let's extract current slug from path if possible, but URI matching is safer strictly.
        // Assuming /slug format.
        $currentSlug = trim($currentPath, '/');
        if ($currentSlug === '')
            $currentSlug = 'home';

        $html = '<ul class="flex flex-col md:flex-row gap-6 items-center">';

        foreach ($items as $item) {
            $url = $item->getUrl();
            $isActive = $item->isActive($currentSlug);
            $activeClass = $isActive ? 'text-blue-600 font-bold' : 'text-gray-600 hover:text-blue-600';
            $target = $item->open_new_tab ? 'target="_blank" rel="noreferrer"' : '';

            // Check for children (dropdown)
            if (!empty($item->children)) {
                $html .= "<li class=\"relative group\">";
                $html .= "<a href=\"{$url}\" class=\"flex items-center gap-1 {$activeClass}\" {$target}>{$item->title} ";
                // Dropdown arrow
                $html .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>';
                $html .= "</a>";

                // Dropdown menu
                $html .= '<div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">';
                foreach ($item->children as $child) {
                    $childUrl = $child->getUrl();
                    $childTarget = $child->open_new_tab ? 'target="_blank" rel="noreferrer"' : '';
                    $html .= "<a href=\"{$childUrl}\" class=\"block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100\" {$childTarget}>{$child->title}</a>";
                }
                $html .= '</div>';

                $html .= "</li>";
            } else {
                $html .= "<li><a href=\"{$url}\" class=\"{$activeClass}\" {$target}>{$item->title}</a></li>";
            }
        }

        $html .= '</ul>';
        return $html;
    }

    /**
     * Render local menu (sidebar navigation)
     */
    public static function renderLocalMenu(?Page $page): string
    {
        if (!$page)
            return '';

        // Determine context:
        // 1. If page has children, show page (as header) and children
        // 2. If page has parent, show parent (as header) and siblings (including self)

        $children = $page->getChildren();
        $parent = $page->getParent();

        $title = '';
        $items = [];

        if (!empty($children)) {
            // Context: Parent viewing children
            $title = $page->title;
            // Add self as "Overview" or just use title
            foreach ($children as $child) {
                $items[] = $child;
            }
        } elseif ($parent) {
            // Context: Child viewing siblings
            $title = $parent->title;
            // Get siblings including self
            $siblings = $page->getSiblings();
            // Note: getSiblings() excludes self in Page.php currently?
            // Let's check Page.php: ne('id', $this->id) -> Yes it excludes self.
            // We want to reconstruct the list properly specific to the parent's children.
            // So better to fetch parent's children directly.
            $items = $parent->getChildren();
        } else {
            // Root page with no children. No submenu.
            return '';
        }

        if (empty($items))
            return '';

        $html = '<nav class="local-menu mb-8">';
        $html .= "<h3 class=\"font-bold text-gray-900 mb-4 uppercase tracking-wider text-sm\">{$title}</h3>";
        $html .= '<ul class="space-y-2 border-l-2 border-gray-100">';

        foreach ($items as $item) {
            // Assuming simplified URL logic (slug)
            $url = $item->slug === 'home' ? '/' : '/' . $item->slug;
            $isActive = $item->id === $page->id;

            $itemClass = $isActive
                ? 'border-l-2 border-blue-600 text-blue-600 font-medium pl-3 -ml-[2px]'
                : 'text-gray-600 hover:text-gray-900 hover:border-gray-300 pl-3 -ml-[2px] border-l-2 border-transparent block transition-colors';

            $html .= "<li><a href=\"{$url}\" class=\"{$itemClass}\">{$item->title}</a></li>";
        }

        $html .= '</ul></nav>';
        return $html;
    }

    public static function renderBreadcrumbs(?Page $page): string
    {
        if (!$page || $page->slug === 'home')
            return '';

        $crumbs = $page->getBreadcrumbs();
        // Add Home
        $homeUrl = '/';

        $html = '<nav class="flex text-sm text-gray-500 mb-6" aria-label="Breadcrumb">';
        $html .= '<ol class="inline-flex items-center space-x-1 md:space-x-3">';

        // Home
        $html .= '<li class="inline-flex items-center">';
        $html .= '<a href="/" class="inline-flex items-center hover:text-blue-600 text-gray-700">';
        $html .= '<svg class="w-3 h-3 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/></svg>';
        $html .= 'Home</a></li>';

        foreach ($crumbs as $crumb) {
            $url = '/' . $crumb->slug;
            $html .= '<li><div class="flex items-center">';
            $html .= '<svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>';
            $html .= "<a href=\"{$url}\" class=\"ml-1 hover:text-blue-600 md:ml-2\">{$crumb->title}</a>";
            $html .= '</div></li>';
        }

        // Current
        $html .= '<li aria-current="page"><div class="flex items-center">';
        $html .= '<svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>';
        $html .= "<span class=\"ml-1 text-gray-500 md:ml-2 font-medium\">{$page->title}</span>";
        $html .= '</div></li>';

        $html .= '</ol></nav>';
        return $html;
    }

    public static function shouldShowSidebar(?Page $page): bool
    {
        if (!$page)
            return false;

        // 1. Check Site Settings
        $globalSetting = Settings::get('sidebar_enabled', 'internal'); // 'all', 'internal', 'none'

        if ($globalSetting === 'none') {
            return false;
        }

        // 2. Map global setting to default
        $canShow = true;
        if ($globalSetting === 'internal' && $page->slug === 'home') {
            $canShow = false;
        }

        // 3. Check Page Overrides
        // We need to fetch page meta settings. Assuming it's in meta_json or a new column.
        // For now, let's assume meta_json has 'sidebar_override'
        if (!empty($page->meta_json)) {
            $meta = json_decode($page->meta_json, true);
            if (isset($meta['sidebar_override'])) {
                $override = $meta['sidebar_override']; // 'inherit', 'show', 'hide'
                if ($override === 'show')
                    return true;
                if ($override === 'hide')
                    return false;
            }
        }

        // 4. Finally, only show if we actually have something to show (local menu)
        // Or if we implemented other sidebar widgets.
        // For now, Sidebar implies Local Menu availability.
        if ($canShow) {
            $hasSiblingsFn = function () use ($page) {
                return $page->parent_id || !empty($page->getChildren());
            };
            // Only show sidebar if there is a menu structure to show?
            // User requirement: "Enable sidebar on the whole site... add local menu to sidebar".
            // If there's no local menu, sidebar might be empty.
            if (!$hasSiblingsFn()) {
                // Determine if we should show empty sidebar? Probably not.
                return false;
            }
            return true;
        }

        return false;
    }
}

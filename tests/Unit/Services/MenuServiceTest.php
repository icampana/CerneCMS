<?php

namespace tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use app\services\MenuService;
use app\models\Menu;
use app\models\MenuItem;
use app\models\Page;

class MenuServiceTest extends TestCase
{
    public function testRenderMenuBySlug()
    {
        $menu = new Menu();
        $menu->name = 'Test Menu';
        $menu->slug = 'test';
        $menu->save();

        $item = new MenuItem();
        $item->menu_id = $menu->id;
        $item->title = 'Test Item';
        $item->link_type = 'external';
        $item->link_value = '/test';
        $item->sort_order = 1;
        $item->save();

        $_SERVER['REQUEST_URI'] = '/';

        $html = MenuService::render('test');

        $this->assertStringContainsString('Test Item', $html);
        $this->assertStringContainsString('/test', $html);
        $this->assertStringContainsString('<ul', $html);
    }

    public function testRenderPrimaryMenu()
    {
        $menu = new Menu();
        $menu->name = 'Primary';
        $menu->slug = 'primary';
        $menu->is_primary = 1;
        $menu->save();

        $item = new MenuItem();
        $item->menu_id = $menu->id;
        $item->title = 'Home';
        $item->link_type = 'external';
        $item->link_value = '/';
        $item->sort_order = 1;
        $item->save();

        $_SERVER['REQUEST_URI'] = '/';

        $html = MenuService::render('primary');

        $this->assertStringContainsString('Home', $html);
        $this->assertStringContainsString('/', $html);
    }

    public function testRenderNonExistentMenuReturnsEmpty()
    {
        $_SERVER['REQUEST_URI'] = '/';

        $html = MenuService::render('non-existent');

        $this->assertEquals('', $html);
    }

    public function testRenderMenuWithDropdownItems()
    {
        $menu = new Menu();
        $menu->name = 'Test Menu';
        $menu->slug = 'test';
        $menu->save();

        $parentItem = new MenuItem();
        $parentItem->menu_id = $menu->id;
        $parentItem->title = 'Parent';
        $parentItem->link_type = 'external';
        $parentItem->link_value = '/parent';
        $parentItem->sort_order = 1;
        $parentItem->save();

        $childItem = new MenuItem();
        $childItem->menu_id = $menu->id;
        $childItem->title = 'Child';
        $childItem->link_type = 'external';
        $childItem->link_value = '/child';
        $childItem->parent_id = $parentItem->id;
        $childItem->sort_order = 2;
        $childItem->save();

        $_SERVER['REQUEST_URI'] = '/';

        $html = MenuService::render('test');

        $this->assertStringContainsString('Parent', $html);
        $this->assertStringContainsString('Child', $html);
        $this->assertStringContainsString('group-hover', $html);
    }

    public function testRenderMenuWithActiveItem()
    {
        $page = new Page();
        $page->title = 'About';
        $page->slug = 'about';
        $page->save();

        $menu = new Menu();
        $menu->name = 'Test Menu';
        $menu->slug = 'test';
        $menu->save();

        $item = new MenuItem();
        $item->menu_id = $menu->id;
        $item->title = 'About';
        $item->link_type = 'internal';
        $item->target_page_id = $page->id;
        $item->sort_order = 1;
        $item->save();

        $_SERVER['REQUEST_URI'] = '/about';

        $html = MenuService::render('test');

        $this->assertStringContainsString('text-blue-600 font-bold', $html);
    }

    public function testRenderMenuWithOpenNewTab()
    {
        $menu = new Menu();
        $menu->name = 'Test Menu';
        $menu->slug = 'test';
        $menu->save();

        $item = new MenuItem();
        $item->menu_id = $menu->id;
        $item->title = 'External';
        $item->link_type = 'external';
        $item->link_value = 'https://example.com';
        $item->open_new_tab = 1;
        $item->sort_order = 1;
        $item->save();

        $_SERVER['REQUEST_URI'] = '/';

        $html = MenuService::render('test');

        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringContainsString('rel="noreferrer"', $html);
    }

    public function testRenderLocalMenuForPageWithChildren()
    {
        $parent = new Page();
        $parent->title = 'Parent';
        $parent->slug = 'parent';
        $parent->save();

        $child1 = new Page();
        $child1->title = 'Child 1';
        $child1->slug = 'child-1';
        $child1->parent_id = $parent->id;
        $child1->save();

        $child2 = new Page();
        $child2->title = 'Child 2';
        $child2->slug = 'child-2';
        $child2->parent_id = $parent->id;
        $child2->save();

        $_SERVER['REQUEST_URI'] = '/parent';

        $html = MenuService::renderLocalMenu($parent);

        $this->assertStringContainsString('Parent', $html);
        $this->assertStringContainsString('Child 1', $html);
        $this->assertStringContainsString('Child 2', $html);
        $this->assertStringContainsString('local-menu', $html);
    }

    public function testRenderLocalMenuForPageWithParent()
    {
        $parent = new Page();
        $parent->title = 'Parent';
        $parent->slug = 'parent';
        $parent->save();

        $child1 = new Page();
        $child1->title = 'Child 1';
        $child1->slug = 'child-1';
        $child1->parent_id = $parent->id;
        $child1->save();

        $child2 = new Page();
        $child2->title = 'Child 2';
        $child2->slug = 'child-2';
        $child2->parent_id = $parent->id;
        $child2->save();

        $_SERVER['REQUEST_URI'] = '/child-1';

        $html = MenuService::renderLocalMenu($child1);

        $this->assertStringContainsString('Parent', $html);
        $this->assertStringContainsString('Child 1', $html);
        $this->assertStringContainsString('Child 2', $html);
    }

    public function testRenderLocalMenuForRootPageWithNoChildren()
    {
        $page = new Page();
        $page->title = 'Root';
        $page->slug = 'root';
        $page->save();

        $_SERVER['REQUEST_URI'] = '/root';

        $html = MenuService::renderLocalMenu($page);

        $this->assertEquals('', $html);
    }

    public function testRenderLocalMenuWithActiveItem()
    {
        $parent = new Page();
        $parent->title = 'Parent';
        $parent->slug = 'parent';
        $parent->save();

        $child1 = new Page();
        $child1->title = 'Child 1';
        $child1->slug = 'child-1';
        $child1->parent_id = $parent->id;
        $child1->save();

        $child2 = new Page();
        $child2->title = 'Child 2';
        $child2->slug = 'child-2';
        $child2->parent_id = $parent->id;
        $child2->save();

        $_SERVER['REQUEST_URI'] = '/child-1';

        $html = MenuService::renderLocalMenu($child1);

        $this->assertStringContainsString('border-blue-600', $html);
        $this->assertStringContainsString('text-blue-600', $html);
    }

    public function testRenderBreadcrumbsForRootPage()
    {
        $page = new Page();
        $page->title = 'Home';
        $page->slug = 'home';
        $page->save();

        $_SERVER['REQUEST_URI'] = '/';

        $html = MenuService::renderBreadcrumbs($page);

        $this->assertEquals('', $html);
    }

    public function testRenderBreadcrumbsForSingleLevel()
    {
        $root = new Page();
        $root->title = 'Root';
        $root->slug = 'root';
        $root->save();

        $child = new Page();
        $child->title = 'Child';
        $child->slug = 'child';
        $child->parent_id = $root->id;
        $child->save();

        $_SERVER['REQUEST_URI'] = '/child';

        $html = MenuService::renderBreadcrumbs($child);

        $this->assertStringContainsString('Root', $html);
        $this->assertStringContainsString('Child', $html);
        $this->assertStringContainsString('/root', $html);
    }

    public function testRenderBreadcrumbsForMultiLevel()
    {
        $root = new Page();
        $root->title = 'Root';
        $root->slug = 'root';
        $root->save();

        $level1 = new Page();
        $level1->title = 'Level 1';
        $level1->slug = 'level-1';
        $level1->parent_id = $root->id;
        $level1->save();

        $level2 = new Page();
        $level2->title = 'Level 2';
        $level2->slug = 'level-2';
        $level2->parent_id = $level1->id;
        $level2->save();

        $_SERVER['REQUEST_URI'] = '/level-2';

        $html = MenuService::renderBreadcrumbs($level2);

        $this->assertStringContainsString('Root', $html);
        $this->assertStringContainsString('Level 1', $html);
        $this->assertStringContainsString('Level 2', $html);
        $this->assertStringContainsString('/root', $html);
        $this->assertStringContainsString('/level-1', $html);
    }

    public function testShouldShowSidebarWithGlobalSettingNone()
    {
        $page = new Page();
        $page->title = 'Test';
        $page->slug = 'test';
        $page->save();

        \app\models\Settings::set('sidebar_enabled', 'none');

        $shouldShow = MenuService::shouldShowSidebar($page);

        $this->assertFalse($shouldShow);
    }

    public function testShouldShowSidebarWithGlobalSettingAll()
    {
        $parent = new Page();
        $parent->title = 'Parent';
        $parent->slug = 'parent';
        $parent->save();

        $child = new Page();
        $child->title = 'Child';
        $child->slug = 'child';
        $child->parent_id = $parent->id;
        $child->save();

        \app\models\Settings::set('sidebar_enabled', 'all');

        $shouldShow = MenuService::shouldShowSidebar($child);

        $this->assertTrue($shouldShow);
    }

    public function testShouldShowSidebarWithPageOverrideShow()
    {
        $parent = new Page();
        $parent->title = 'Parent';
        $parent->slug = 'parent';
        $parent->save();

        $child = new Page();
        $child->title = 'Child';
        $child->slug = 'child';
        $child->parent_id = $parent->id;
        $child->meta_json = json_encode(['sidebar_override' => 'show']);
        $child->save();

        \app\models\Settings::set('sidebar_enabled', 'none');

        $shouldShow = MenuService::shouldShowSidebar($child);

        $this->assertTrue($shouldShow);
    }

    public function testShouldShowSidebarWithPageOverrideHide()
    {
        $parent = new Page();
        $parent->title = 'Parent';
        $parent->slug = 'parent';
        $parent->save();

        $child = new Page();
        $child->title = 'Child';
        $child->slug = 'child';
        $child->parent_id = $parent->id;
        $child->meta_json = json_encode(['sidebar_override' => 'hide']);
        $child->save();

        \app\models\Settings::set('sidebar_enabled', 'all');

        $shouldShow = MenuService::shouldShowSidebar($child);

        $this->assertFalse($shouldShow);
    }

    public function testShouldShowSidebarForHomePageWithInternalSetting()
    {
        $page = new Page();
        $page->title = 'Home';
        $page->slug = 'home';
        $page->save();

        \app\models\Settings::set('sidebar_enabled', 'internal');

        $shouldShow = MenuService::shouldShowSidebar($page);

        $this->assertFalse($shouldShow);
    }

    public function testShouldShowSidebarForRootPageWithNoChildren()
    {
        $page = new Page();
        $page->title = 'Root';
        $page->slug = 'root';
        $page->save();

        \app\models\Settings::set('sidebar_enabled', 'all');

        $shouldShow = MenuService::shouldShowSidebar($page);

        $this->assertFalse($shouldShow);
    }

    protected function tearDown(): void
    {
        // Clean up $_SERVER
        unset($_SERVER['REQUEST_URI']);
    }
}

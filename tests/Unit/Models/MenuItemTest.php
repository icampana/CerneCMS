<?php

namespace tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use app\models\MenuItem;
use app\models\Page;

class MenuItemTest extends TestCase
{
    public function testCreateMenuItem()
    {
        $menu = new \app\models\Menu();
        $menu->name = 'Test Menu';
        $menu->slug = 'test';
        $menu->save();

        $item = new MenuItem();
        $item->menu_id = $menu->id;
        $item->title = 'Test Item';
        $item->link_type = 'external';
        $item->link_value = 'https://example.com';
        $item->sort_order = 1;
        $item->save();

        $this->assertNotNull($item->id);
        $this->assertEquals('Test Item', $item->title);
        $this->assertEquals('external', $item->link_type);
        $this->assertEquals('https://example.com', $item->link_value);
    }

    public function testGetExternalUrl()
    {
        $item = new MenuItem();
        $item->link_type = 'external';
        $item->link_value = 'https://example.com';

        $url = $item->getUrl();

        $this->assertEquals('https://example.com', $url);
    }

    public function testGetInternalUrl()
    {
        $page = new Page();
        $page->title = 'Test Page';
        $page->slug = 'test-page';
        $page->save();

        $item = new MenuItem();
        $item->link_type = 'internal';
        $item->target_page_id = $page->id;

        $url = $item->getUrl();

        $this->assertEquals('/test-page', $url);
    }

    public function testGetInternalUrlForHomePage()
    {
        $page = new Page();
        $page->title = 'Home';
        $page->slug = 'home';
        $page->save();

        $item = new MenuItem();
        $item->link_type = 'internal';
        $item->target_page_id = $page->id;

        $url = $item->getUrl();

        $this->assertEquals('/', $url);
    }

    public function testGetInternalUrlForMissingPage()
    {
        $item = new MenuItem();
        $item->link_type = 'internal';
        $item->target_page_id = 99999;

        $url = $item->getUrl();

        $this->assertEquals('#', $url);
    }

    public function testGetAnchorUrlWithPage()
    {
        $page = new Page();
        $page->title = 'Test Page';
        $page->slug = 'test-page';
        $page->save();

        $item = new MenuItem();
        $item->link_type = 'anchor';
        $item->target_page_id = $page->id;
        $item->link_value = 'section';

        $url = $item->getUrl();

        $this->assertEquals('/test-page#section', $url);
    }

    public function testGetAnchorUrlWithoutPage()
    {
        $item = new MenuItem();
        $item->link_type = 'anchor';
        $item->link_value = 'section';

        $url = $item->getUrl();

        $this->assertEquals('#section', $url);
    }

    public function testGetUrlForUnknownLinkType()
    {
        $item = new MenuItem();
        $item->link_type = 'unknown';
        $item->link_value = 'something';

        $url = $item->getUrl();

        $this->assertEquals('#', $url);
    }

    public function testIsActiveForExternalLink()
    {
        $item = new MenuItem();
        $item->link_type = 'external';
        $item->link_value = 'https://example.com';

        $this->assertFalse($item->isActive('home'));
        $this->assertFalse($item->isActive('about'));
    }

    public function testIsActiveForInternalLink()
    {
        $page = new Page();
        $page->title = 'About';
        $page->slug = 'about';
        $page->save();

        $item = new MenuItem();
        $item->link_type = 'internal';
        $item->target_page_id = $page->id;

        $this->assertTrue($item->isActive('about'));
        $this->assertFalse($item->isActive('contact'));
    }

    public function testIsActiveForHomePage()
    {
        $page = new Page();
        $page->title = 'Home';
        $page->slug = 'home';
        $page->save();

        $item = new MenuItem();
        $item->link_type = 'internal';
        $item->target_page_id = $page->id;

        $this->assertTrue($item->isActive('home'));
    }

    public function testIsActiveForAnchorLink()
    {
        $page = new Page();
        $page->title = 'Test Page';
        $page->slug = 'test-page';
        $page->save();

        $item = new MenuItem();
        $item->link_type = 'anchor';
        $item->target_page_id = $page->id;
        $item->link_value = 'section';

        $this->assertTrue($item->isActive('test-page'));
        $this->assertFalse($item->isActive('other-page'));
    }

    public function testOpenNewTabFalse()
    {
        $item = new MenuItem();
        $item->open_new_tab = 0;

        $this->assertEquals(0, $item->open_new_tab);
    }

    public function testOpenNewTabTrue()
    {
        $item = new MenuItem();
        $item->open_new_tab = 1;

        $this->assertEquals(1, $item->open_new_tab);
    }

    public function testUpdateMenuItem()
    {
        $menu = new \app\models\Menu();
        $menu->name = 'Test Menu';
        $menu->slug = 'test';
        $menu->save();

        $item = new MenuItem();
        $item->menu_id = $menu->id;
        $item->title = 'Original Title';
        $item->link_type = 'external';
        $item->link_value = '/original';
        $item->sort_order = 1;
        $item->save();

        $item->title = 'Updated Title';
        $item->link_value = '/updated';
        $item->save();

        $updatedItem = (new MenuItem())->eq('id', $item->id)->find();
        $this->assertEquals('Updated Title', $updatedItem->title);
        $this->assertEquals('/updated', $updatedItem->link_value);
    }

    public function testDeleteMenuItem()
    {
        $menu = new \app\models\Menu();
        $menu->name = 'Test Menu';
        $menu->slug = 'test';
        $menu->save();

        $item = new MenuItem();
        $item->menu_id = $menu->id;
        $item->title = 'To Delete';
        $item->link_type = 'external';
        $item->link_value = '/delete';
        $item->sort_order = 1;
        $item->save();

        $itemId = $item->id;
        $item->delete();

        $foundItem = (new MenuItem())->eq('id', $itemId)->find();
        $this->assertFalse($foundItem);
    }

    public function testSortOrder()
    {
        $menu = new \app\models\Menu();
        $menu->name = 'Test Menu';
        $menu->slug = 'test';
        $menu->save();

        $item1 = new MenuItem();
        $item1->menu_id = $menu->id;
        $item1->title = 'First';
        $item1->link_type = 'external';
        $item1->link_value = '/first';
        $item1->sort_order = 10;
        $item1->save();

        $item2 = new MenuItem();
        $item2->menu_id = $menu->id;
        $item2->title = 'Second';
        $item2->link_type = 'external';
        $item2->link_value = '/second';
        $item2->sort_order = 20;
        $item2->save();

        $this->assertEquals(10, $item1->sort_order);
        $this->assertEquals(20, $item2->sort_order);
    }
}

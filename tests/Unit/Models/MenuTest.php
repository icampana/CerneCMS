<?php

namespace tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use app\models\Menu;
use app\models\MenuItem;

class MenuTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clean up any existing menus before each test
        $menuModel = new Menu();
        $menus = $menuModel->findAll();
        foreach ($menus as $menu) {
            $menu->delete();
        }
    }

    public function testCreateMenu()
    {
        $menu = new Menu();
        $menu->name = 'Test Menu';
        $menu->slug = 'test-menu-' . uniqid();
        $menu->is_primary = 0;
        $menu->save();

        $this->assertNotNull($menu->id);
        $this->assertEquals('Test Menu', $menu->name);
        $this->assertEquals('test-menu-' . uniqid(), $menu->slug);
        $this->assertEquals(0, $menu->is_primary);
    }

    public function testFindMenuBySlug()
    {
        $menu = new Menu();
        $menu->name = 'Find Me';
        $menu->slug = 'find-me';
        $menu->save();

        $foundMenu = (new Menu())->eq('slug', 'find-me')->find();

        $this->assertNotNull($foundMenu);
        $this->assertEquals('Find Me', $foundMenu->name);
    }

    public function testGetPrimaryMenu()
    {
        $menu = new Menu();
        $menu->name = 'Main Menu';
        $menu->slug = 'main';
        $menu->is_primary = 1;
        $menu->save();

        $primary = Menu::getPrimary();

        $this->assertNotNull($primary);
        $this->assertEquals('Main Menu', $primary->name);
        $this->assertEquals('main', $primary->slug);
        $this->assertEquals(1, $primary->is_primary);
    }

    public function testGetPrimaryReturnsNullWhenNoneExists()
    {
        $primary = Menu::getPrimary();

        $this->assertNull($primary);
    }

    public function testSetAsPrimaryUnsetsOthers()
    {
        $menu1 = new Menu();
        $menu1->name = 'Menu 1';
        $menu1->slug = 'menu-1';
        $menu1->is_primary = 1;
        $menu1->save();

        $menu2 = new Menu();
        $menu2->name = 'Menu 2';
        $menu2->slug = 'menu-2';
        $menu2->is_primary = 0;
        $menu2->save();

        $menu2->setAsPrimary();

        // Refresh both menus
        $menu1 = (new Menu())->eq('id', $menu1->id)->find();
        $menu2 = (new Menu())->eq('id', $menu2->id)->find();

        $this->assertEquals(0, $menu1->is_primary);
        $this->assertEquals(1, $menu2->is_primary);
    }

    public function testGetItemsReturnsEmptyArrayForNewMenu()
    {
        $menu = new Menu();
        $menu->name = 'Empty Menu';
        $menu->slug = 'empty';
        $menu->save();

        $items = $menu->getItems();

        $this->assertIsArray($items);
        $this->assertCount(0, $items);
    }

    public function testGetItemsReturnsTopLevelItems()
    {
        $menu = new Menu();
        $menu->name = 'Test Menu';
        $menu->slug = 'test';
        $menu->save();

        $item1 = new MenuItem();
        $item1->menu_id = $menu->id;
        $item1->title = 'Item 1';
        $item1->link_type = 'external';
        $item1->link_value = '/item1';
        $item1->sort_order = 1;
        $item1->save();

        $item2 = new MenuItem();
        $item2->menu_id = $menu->id;
        $item2->title = 'Item 2';
        $item2->link_type = 'external';
        $item2->link_value = '/item2';
        $item2->sort_order = 2;
        $item2->save();

        $items = $menu->getItems();

        $this->assertCount(2, $items);
        $this->assertEquals('Item 1', $items[0]->title);
        $this->assertEquals('Item 2', $items[1]->title);
    }

    public function testGetItemsHierarchically()
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

        $items = $menu->getItems();

        $this->assertCount(1, $items);
        $this->assertEquals('Parent', $items[0]->title);
        $this->assertIsArray($items[0]->children);
        $this->assertCount(1, $items[0]->children);
        $this->assertEquals('Child', $items[0]->children[0]->title);
    }

    public function testGetItemsMultipleChildren()
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

        $child1 = new MenuItem();
        $child1->menu_id = $menu->id;
        $child1->title = 'Child 1';
        $child1->link_type = 'external';
        $child1->link_value = '/child1';
        $child1->parent_id = $parentItem->id;
        $child1->sort_order = 2;
        $child1->save();

        $child2 = new MenuItem();
        $child2->menu_id = $menu->id;
        $child2->title = 'Child 2';
        $child2->link_type = 'external';
        $child2->link_value = '/child2';
        $child2->parent_id = $parentItem->id;
        $child2->sort_order = 3;
        $child2->save();

        $items = $menu->getItems();

        $this->assertCount(1, $items);
        $this->assertCount(2, $items[0]->children);
        $this->assertEquals('Child 1', $items[0]->children[0]->title);
        $this->assertEquals('Child 2', $items[0]->children[1]->title);
    }

    public function testGetItemsMultipleParents()
    {
        $menu = new Menu();
        $menu->name = 'Test Menu';
        $menu->slug = 'test';
        $menu->save();

        $parent1 = new MenuItem();
        $parent1->menu_id = $menu->id;
        $parent1->title = 'Parent 1';
        $parent1->link_type = 'external';
        $parent1->link_value = '/parent1';
        $parent1->sort_order = 1;
        $parent1->save();

        $parent2 = new MenuItem();
        $parent2->menu_id = $menu->id;
        $parent2->title = 'Parent 2';
        $parent2->link_type = 'external';
        $parent2->link_value = '/parent2';
        $parent2->sort_order = 2;
        $parent2->save();

        $child1 = new MenuItem();
        $child1->menu_id = $menu->id;
        $child1->title = 'Child 1';
        $child1->link_type = 'external';
        $child1->link_value = '/child1';
        $child1->parent_id = $parent1->id;
        $child1->sort_order = 3;
        $child1->save();

        $child2 = new MenuItem();
        $child2->menu_id = $menu->id;
        $child2->title = 'Child 2';
        $child2->link_type = 'external';
        $child2->link_value = '/child2';
        $child2->parent_id = $parent2->id;
        $child2->sort_order = 4;
        $child2->save();

        $items = $menu->getItems();

        $this->assertCount(2, $items);
        $this->assertEquals('Parent 1', $items[0]->title);
        $this->assertEquals('Parent 2', $items[1]->title);
        $this->assertCount(1, $items[0]->children);
        $this->assertCount(1, $items[1]->children);
    }

    public function testGetItemsOrderedBySortOrder()
    {
        $menu = new Menu();
        $menu->name = 'Test Menu';
        $menu->slug = 'test';
        $menu->save();

        $item3 = new MenuItem();
        $item3->menu_id = $menu->id;
        $item3->title = 'Third';
        $item3->link_type = 'external';
        $item3->link_value = '/third';
        $item3->sort_order = 3;
        $item3->save();

        $item1 = new MenuItem();
        $item1->menu_id = $menu->id;
        $item1->title = 'First';
        $item1->link_type = 'external';
        $item1->link_value = '/first';
        $item1->sort_order = 1;
        $item1->save();

        $item2 = new MenuItem();
        $item2->menu_id = $menu->id;
        $item2->title = 'Second';
        $item2->link_type = 'external';
        $item2->link_value = '/second';
        $item2->sort_order = 2;
        $item2->save();

        $items = $menu->getItems();

        $this->assertCount(3, $items);
        $this->assertEquals('First', $items[0]->title);
        $this->assertEquals('Second', $items[1]->title);
        $this->assertEquals('Third', $items[2]->title);
    }

    public function testUpdateMenu()
    {
        $menu = new Menu();
        $menu->name = 'Original Name';
        $menu->slug = 'original';
        $menu->save();

        $menu->name = 'Updated Name';
        $menu->save();

        $updatedMenu = (new Menu())->eq('id', $menu->id)->find();
        $this->assertEquals('Updated Name', $updatedMenu->name);
    }

    public function testDeleteMenu()
    {
        $menu = new Menu();
        $menu->name = 'To Delete';
        $menu->slug = 'to-delete';
        $menu->save();

        $menuId = $menu->id;
        $menu->delete();

        $foundMenu = (new Menu())->eq('id', $menuId)->find();
        $this->assertFalse($foundMenu);
    }

    public function testUniqueSlugConstraint()
    {
        $menu1 = new Menu();
        $menu1->name = 'First';
        $menu1->slug = 'duplicate-slug';
        $menu1->save();

        $menu2 = new Menu();
        $menu2->name = 'Second';
        $menu2->slug = 'duplicate-slug';

        $this->expectException(\PDOException::class);
        $menu2->save();
    }
}

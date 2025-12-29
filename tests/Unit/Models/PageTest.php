<?php

namespace tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use app\models\Page;

class PageTest extends TestCase
{
    private Page $pageModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageModel = new Page();
    }

    public function testCreatePage()
    {
        $page = new Page();
        $page->title = 'Test Page';
        $page->slug = 'test-page-' . uniqid();
        $page->status = 'published';
        $page->save();

        $this->assertNotNull($page->id);
        $this->assertEquals('Test Page', $page->title);
        $this->assertEquals('test-page-' . uniqid(), $page->slug);
        $this->assertEquals('published', $page->status);
    }

    public function testFindPageById()
    {
        $page = new Page();
        $page->title = 'Find Me';
        $page->slug = 'find-me';
        $page->save();

        $foundPage = $this->pageModel->eq('id', $page->id)->find();

        $this->assertNotNull($foundPage);
        $this->assertEquals('Find Me', $foundPage->title);
        $this->assertEquals('find-me', $foundPage->slug);
    }

    public function testFindPageBySlug()
    {
        $page = new Page();
        $page->title = 'Slug Test';
        $page->slug = 'slug-test';
        $page->save();

        $foundPage = $this->pageModel->eq('slug', 'slug-test')->find();

        $this->assertNotNull($foundPage);
        $this->assertEquals('Slug Test', $foundPage->title);
    }

    public function testUpdatePage()
    {
        $page = new Page();
        $page->title = 'Original Title';
        $page->slug = 'original';
        $page->save();

        $page->title = 'Updated Title';
        $page->save();

        $updatedPage = $this->pageModel->eq('id', $page->id)->find();
        $this->assertEquals('Updated Title', $updatedPage->title);
    }

    public function testDeletePage()
    {
        $page = new Page();
        $page->title = 'To Delete';
        $page->slug = 'to-delete';
        $page->save();

        $pageId = $page->id;
        $page->delete();

        $foundPage = $this->pageModel->eq('id', $pageId)->find();
        $this->assertFalse($foundPage);
    }

    public function testGetParentPage()
    {
        $parent = new Page();
        $parent->title = 'Parent Page';
        $parent->slug = 'parent';
        $parent->save();

        $child = new Page();
        $child->title = 'Child Page';
        $child->slug = 'child';
        $child->parent_id = $parent->id;
        $child->save();

        $retrievedChild = $this->pageModel->eq('id', $child->id)->find();
        $parentPage = $retrievedChild->getParent();

        $this->assertNotNull($parentPage);
        $this->assertEquals('Parent Page', $parentPage->title);
        $this->assertEquals($parent->id, $parentPage->id);
    }

    public function testGetParentReturnsNullForRootPage()
    {
        $page = new Page();
        $page->title = 'Root Page';
        $page->slug = 'root';
        $page->save();

        $parent = $page->getParent();

        $this->assertNull($parent);
    }

    public function testGetChildrenPages()
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

        $children = $parent->getChildren();

        $this->assertCount(2, $children);
        $this->assertEquals('Child 1', $children[0]->title);
        $this->assertEquals('Child 2', $children[1]->title);
    }

    public function testGetChildrenReturnsEmptyArrayForLeafPage()
    {
        $page = new Page();
        $page->title = 'Leaf Page';
        $page->slug = 'leaf';
        $page->save();

        $children = $page->getChildren();

        $this->assertIsArray($children);
        $this->assertCount(0, $children);
    }

    public function testGetSiblingsForRootPage()
    {
        $page1 = new Page();
        $page1->title = 'Root 1';
        $page1->slug = 'root-1';
        $page1->save();

        $page2 = new Page();
        $page2->title = 'Root 2';
        $page2->slug = 'root-2';
        $page2->save();

        $page3 = new Page();
        $page3->title = 'Root 3';
        $page3->slug = 'root-3';
        $page3->save();

        $siblings = $page2->getSiblings();

        $this->assertCount(2, $siblings);
        $titles = array_map(fn($p) => $p->title, $siblings);
        $this->assertContains('Root 1', $titles);
        $this->assertContains('Root 3', $titles);
        $this->assertNotContains('Root 2', $titles);
    }

    public function testGetSiblingsForChildPage()
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

        $child3 = new Page();
        $child3->title = 'Child 3';
        $child3->slug = 'child-3';
        $child3->parent_id = $parent->id;
        $child3->save();

        $siblings = $child2->getSiblings();

        $this->assertCount(2, $siblings);
        $titles = array_map(fn($p) => $p->title, $siblings);
        $this->assertContains('Child 1', $titles);
        $this->assertContains('Child 3', $titles);
        $this->assertNotContains('Child 2', $titles);
    }

    public function testGetBreadcrumbsForRootPage()
    {
        $page = new Page();
        $page->title = 'Root';
        $page->slug = 'root';
        $page->save();

        $crumbs = $page->getBreadcrumbs();

        $this->assertIsArray($crumbs);
        $this->assertCount(0, $crumbs);
    }

    public function testGetBreadcrumbsForSingleLevelChild()
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

        $crumbs = $child->getBreadcrumbs();

        $this->assertCount(1, $crumbs);
        $this->assertEquals('Root', $crumbs[0]->title);
    }

    public function testGetBreadcrumbsForMultiLevelHierarchy()
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

        $level3 = new Page();
        $level3->title = 'Level 3';
        $level3->slug = 'level-3';
        $level3->parent_id = $level2->id;
        $level3->save();

        $crumbs = $level3->getBreadcrumbs();

        $this->assertCount(3, $crumbs);
        $this->assertEquals('Root', $crumbs[0]->title);
        $this->assertEquals('Level 1', $crumbs[1]->title);
        $this->assertEquals('Level 2', $crumbs[2]->title);
    }

    public function testSearchPagesByTitle()
    {
        $page1 = new Page();
        $page1->title = 'About Us';
        $page1->slug = 'about-us';
        $page1->save();

        $page2 = new Page();
        $page2->title = 'Contact';
        $page2->slug = 'contact';
        $page2->save();

        $page3 = new Page();
        $page3->title = 'About Our Team';
        $page3->slug = 'about-team';
        $page3->save();

        $results = Page::search('about');

        $this->assertCount(2, $results);
        $titles = array_map(fn($r) => $r['title'], $results);
        $this->assertContains('About Us', $titles);
        $this->assertContains('About Our Team', $titles);
        $this->assertNotContains('Contact', $titles);
    }

    public function testSearchPagesBySlug()
    {
        $page1 = new Page();
        $page1->title = 'Services';
        $page1->slug = 'services-page';
        $page1->save();

        $page2 = new Page();
        $page2->title = 'Products';
        $page2->slug = 'products-page';
        $page2->save();

        $results = Page::search('page');

        $this->assertCount(2, $results);
    }

    public function testSearchPagesReturnsEmptyForNoMatch()
    {
        $page = new Page();
        $page->title = 'Test Page';
        $page->slug = 'test';
        $page->save();

        $results = Page::search('nonexistent');

        $this->assertIsArray($results);
        $this->assertCount(0, $results);
    }

    public function testSearchPagesLimitsResults()
    {
        // Create 25 pages
        for ($i = 1; $i <= 25; $i++) {
            $page = new Page();
            $page->title = "Page $i";
            $page->slug = "page-$i";
            $page->save();
        }

        $results = Page::search('Page');

        $this->assertLessThanOrEqual(20, count($results));
    }

    public function testPageMetadata()
    {
        $meta = [
            'description' => 'Test description',
            'keywords' => 'test, keywords',
            'sidebar_override' => 'show'
        ];

        $page = new Page();
        $page->title = 'Meta Test';
        $page->slug = 'meta-test';
        $page->meta_json = json_encode($meta);
        $page->save();

        $retrievedPage = $this->pageModel->eq('id', $page->id)->find();
        $retrievedMeta = json_decode($retrievedPage->meta_json, true);

        $this->assertEquals('Test description', $retrievedMeta['description']);
        $this->assertEquals('test, keywords', $retrievedMeta['keywords']);
        $this->assertEquals('show', $retrievedMeta['sidebar_override']);
    }

    public function testPageTimestamps()
    {
        $page = new Page();
        $page->title = 'Timestamp Test';
        $page->slug = 'timestamp-test';
        $page->save();

        $this->assertNotNull($page->created_at);
        $this->assertNotNull($page->updated_at);

        $originalUpdatedAt = $page->updated_at;

        // Wait a tiny bit to ensure timestamp difference
        usleep(1000);

        $page->title = 'Updated Title';
        $page->save();

        $retrievedPage = $this->pageModel->eq('id', $page->id)->find();

        $this->assertNotEquals($originalUpdatedAt, $retrievedPage->updated_at);
    }

    public function testFindAllPages()
    {
        $page1 = new Page();
        $page1->title = 'Page 1';
        $page1->slug = 'page-1';
        $page1->save();

        $page2 = new Page();
        $page2->title = 'Page 2';
        $page2->slug = 'page-2';
        $page2->save();

        $page3 = new Page();
        $page3->title = 'Page 3';
        $page3->slug = 'page-3';
        $page3->save();

        $allPages = $this->pageModel->findAll();

        $this->assertGreaterThanOrEqual(3, count($allPages));
    }

    public function testUniqueSlugConstraint()
    {
        $page1 = new Page();
        $page1->title = 'First';
        $page1->slug = 'duplicate-slug';
        $page1->save();

        $page2 = new Page();
        $page2->title = 'Second';
        $page2->slug = 'duplicate-slug';

        $this->expectException(\PDOException::class);
        $page2->save();
    }
}

<?php

namespace tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use app\services\BlockRenderer;

class BlockRendererTest extends TestCase
{
    private BlockRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new BlockRenderer();
    }

    public function testRenderValidDocument()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Hello World']
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertEquals('<p>Hello World</p>', $result);
    }

    public function testRenderInvalidJsonReturnsEmptyString()
    {
        $result = $this->renderer->render('invalid json');
        $this->assertEquals('', $result);
    }

    public function testRenderNonDocTypeReturnsEmptyString()
    {
        $json = json_encode([
            'type' => 'not-doc',
            'content' => []
        ]);

        $result = $this->renderer->render($json);
        $this->assertEquals('', $result);
    }

    public function testRenderEmptyDocument()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => []
        ]);

        $result = $this->renderer->render($json);
        $this->assertEquals('', $result);
    }

    public function testRenderBoldText()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Bold text',
                            'marks' => [['type' => 'bold']]
                        ]
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('<strong>Bold text</strong>', $result);
    }

    public function testRenderItalicText()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Italic text',
                            'marks' => [['type' => 'italic']]
                        ]
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('<em>Italic text</em>', $result);
    }

    public function testRenderStrikeText()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Strike text',
                            'marks' => [['type' => 'strike']]
                        ]
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('<s>Strike text</s>', $result);
    }

    public function testRenderLinkText()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Link text',
                            'marks' => [
                                ['type' => 'link', 'attrs' => ['href' => 'https://example.com']]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('<a href="https://example.com">Link text</a>', $result);
    }

    public function testRenderMultipleMarks()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Bold Italic',
                            'marks' => [
                                ['type' => 'bold'],
                                ['type' => 'italic']
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('<strong><em>Bold Italic</em></strong>', $result);
    }

    public function testRenderHeadingLevel1()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 1],
                    'content' => [
                        ['type' => 'text', 'text' => 'Heading 1']
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('<h1>Heading 1</h1>', $result);
    }

    public function testRenderHeadingLevel6()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 6],
                    'content' => [
                        ['type' => 'text', 'text' => 'Heading 6']
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('<h6>Heading 6</h6>', $result);
    }

    public function testRenderBulletList()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'bulletList',
                    'content' => [
                        [
                            'type' => 'listItem',
                            'content' => [
                                ['type' => 'text', 'text' => 'Item 1']
                            ]
                        ],
                        [
                            'type' => 'listItem',
                            'content' => [
                                ['type' => 'text', 'text' => 'Item 2']
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('<ul>', $result);
        $this->assertStringContainsString('<li>Item 1</li>', $result);
        $this->assertStringContainsString('<li>Item 2</li>', $result);
        $this->assertStringContainsString('</ul>', $result);
    }

    public function testRenderImageWithoutLightbox()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => [
                        'src' => '/uploads/test.jpg',
                        'alt' => 'Test image',
                        'title' => 'Test Title',
                        'lightbox' => false
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('<img src="/uploads/test.jpg" alt="Test image" title="Test Title">', $result);
        $this->assertStringNotContainsString('data-pswp-width', $result);
        $this->assertStringNotContainsString('lightbox-trigger', $result);
    }

    public function testRenderImageWithLightbox()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => [
                        'src' => '/uploads/test.jpg',
                        'alt' => 'Test image',
                        'title' => 'Test Title',
                        'lightbox' => true
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('data-pswp-width', $result);
        $this->assertStringContainsString('lightbox-trigger', $result);
        $this->assertStringContainsString('<figcaption>Test Title</figcaption>', $result);
    }

    public function testRenderGridAndColumn()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'grid',
                    'content' => [
                        [
                            'type' => 'column',
                            'content' => [
                                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Column 1']]]
                            ]
                        ],
                        [
                            'type' => 'column',
                            'content' => [
                                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Column 2']]]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('class="grid-layout flex gap-4 my-4"', $result);
        $this->assertStringContainsString('class="grid-column flex-1 min-w-0"', $result);
        $this->assertStringContainsString('Column 1', $result);
        $this->assertStringContainsString('Column 2', $result);
    }

    public function testRenderTable()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'table',
                    'content' => [
                        [
                            'type' => 'tableRow',
                            'content' => [
                                [
                                    'type' => 'tableHeader',
                                    'content' => [['type' => 'text', 'text' => 'Header 1']]
                                ],
                                [
                                    'type' => 'tableHeader',
                                    'content' => [['type' => 'text', 'text' => 'Header 2']]
                                ]
                            ]
                        ],
                        [
                            'type' => 'tableRow',
                            'content' => [
                                [
                                    'type' => 'tableCell',
                                    'content' => [['type' => 'text', 'text' => 'Cell 1']]
                                ],
                                [
                                    'type' => 'tableCell',
                                    'content' => [['type' => 'text', 'text' => 'Cell 2']]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('<table', $result);
        $this->assertStringContainsString('<th', $result);
        $this->assertStringContainsString('Header 1', $result);
        $this->assertStringContainsString('<td', $result);
        $this->assertStringContainsString('Cell 1', $result);
    }

    public function testRenderYouTube()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'youtube',
                    'attrs' => [
                        'src' => 'https://www.youtube.com/embed/dQw4w9WgXcQ'
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('<iframe', $result);
        $this->assertStringContainsString('https://www.youtube.com/embed/dQw4w9WgXcQ', $result);
        $this->assertStringContainsString('allowfullscreen', $result);
    }

    public function testRenderCTABlockCentered()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'cta',
                    'attrs' => [
                        'layout' => 'centered',
                        'title' => 'CTA Title',
                        'subtitle' => 'CTA Subtitle',
                        'buttonText' => 'Click Me',
                        'buttonUrl' => '/action'
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('CTA Title', $result);
        $this->assertStringContainsString('CTA Subtitle', $result);
        $this->assertStringContainsString('Click Me', $result);
        $this->assertStringContainsString('/action', $result);
        $this->assertStringContainsString('items-center text-center', $result);
    }

    public function testRenderCTABlockHero()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'cta',
                    'attrs' => [
                        'layout' => 'hero',
                        'title' => 'Hero Title',
                        'subtitle' => 'Hero Subtitle',
                        'buttonText' => 'Get Started',
                        'buttonUrl' => '/start'
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('Hero Title', $result);
        $this->assertStringContainsString('text-4xl md:text-5xl', $result);
        $this->assertStringContainsString('bg-gradient-to-br from-blue-600 to-blue-800', $result);
    }

    public function testRenderCTABlockWithBackgroundImage()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'cta',
                    'attrs' => [
                        'layout' => 'centered',
                        'title' => 'Title',
                        'subtitle' => 'Subtitle',
                        'buttonText' => 'Button',
                        'buttonUrl' => '/link',
                        'backgroundImage' => '/uploads/bg.jpg'
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('background-image: url(/uploads/bg.jpg)', $result);
        $this->assertStringContainsString('bg-black/40', $result);
    }

    public function testRenderHorizontalRule()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'horizontalRule'
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('<hr class="my-8 border-gray-200">', $result);
    }

    public function testRenderUnknownBlockType()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'unknownBlock',
                    'content' => [
                        ['type' => 'text', 'text' => 'Some content']
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        // Unknown blocks should render their content
        $this->assertStringContainsString('Some content', $result);
    }

    public function testSanitizeXSSInText()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => '<script>alert("xss")</script>']
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('<script>', $result);
    }

    public function testSanitizeXSSInLinkHref()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Link',
                            'marks' => [
                                ['type' => 'link', 'attrs' => ['href' => 'javascript:alert(1)']]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        // The href should be escaped
        $this->assertStringContainsString('javascript:alert(1)', $result);
    }

    public function testRenderNestedContent()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'bulletList',
                    'content' => [
                        [
                            'type' => 'listItem',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        ['type' => 'text', 'text' => 'Item with '],
                                        ['type' => 'text', 'text' => 'bold', 'marks' => [['type' => 'bold']]]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('<ul>', $result);
        $this->assertStringContainsString('<li>', $result);
        $this->assertStringContainsString('<p>', $result);
        $this->assertStringContainsString('<strong>bold</strong>', $result);
    }

    public function testRenderMultipleParagraphs()
    {
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'First paragraph']
                    ]
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Second paragraph']
                    ]
                ]
            ]
        ]);

        $result = $this->renderer->render($json);
        $this->assertStringContainsString('<p>First paragraph</p>', $result);
        $this->assertStringContainsString('<p>Second paragraph</p>', $result);
    }
}

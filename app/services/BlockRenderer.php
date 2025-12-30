<?php

namespace app\services;

use app\services\blocks\BlockRendererInterface;
use app\services\blocks\CtaRenderer;
use app\services\blocks\FormBlockRenderer;
use app\services\blocks\GalleryRenderer;
use app\services\blocks\GridRenderer;
use app\services\blocks\HeadingRenderer;
use app\services\blocks\HorizontalRuleRenderer;
use app\services\blocks\ImageRenderer;
use app\services\blocks\ListItemRenderer;
use app\services\blocks\ListRenderer;
use app\services\blocks\ParagraphRenderer;
use app\services\blocks\TableRenderer;
use app\services\blocks\YoutubeRenderer;

class BlockRenderer
{
    private array $renderers = [];

    public function __construct()
    {
        $this->registerDefaultRenderers();
    }

    private function registerDefaultRenderers(): void
    {
        $this->registerRenderer('heading', new HeadingRenderer());
        $this->registerRenderer('paragraph', new ParagraphRenderer());
        $this->registerRenderer('bulletList', new ListRenderer());
        $this->registerRenderer('inputlist', new ListRenderer());
        $this->registerRenderer('listItem', new ListItemRenderer());
        $this->registerRenderer('image', new ImageRenderer());
        $this->registerRenderer('grid', new GridRenderer());
        $this->registerRenderer('column', new GridRenderer());
        $this->registerRenderer('table', new TableRenderer());
        $this->registerRenderer('tableRow', new TableRenderer());
        $this->registerRenderer('tableHeader', new TableRenderer());
        $this->registerRenderer('tableCell', new TableRenderer());
        $this->registerRenderer('youtube', new YoutubeRenderer());
        $this->registerRenderer('cta', new CtaRenderer());
        $this->registerRenderer('gallery', new GalleryRenderer());
        $this->registerRenderer('formBlock', new FormBlockRenderer());
        $this->registerRenderer('horizontalRule', new HorizontalRuleRenderer());
    }

    public function registerRenderer(string $type, BlockRendererInterface $renderer): void
    {
        $this->renderers[$type] = $renderer;
    }

    /**
     * Render Tiptap JSON string to HTML
     */
    public function render(string $json): string
    {
        $data = json_decode($json, true);

        if (!$data || !isset($data['type']) || $data['type'] !== 'doc') {
            return '';
        }

        return $this->renderNodes($data['content'] ?? []);
    }

    public function renderNodes(array $nodes): string
    {
        $html = '';
        foreach ($nodes as $node) {
            $html .= $this->renderNode($node);
        }
        return $html;
    }

    private function renderNode(array $node): string
    {
        $type = $node['type'];

        // Handle Text Nodes
        if ($type === 'text') {
            $text = $node['text'] ?? '';
            // Sanitize
            $text = htmlspecialchars($text);

            // Handle Marks
            if (isset($node['marks'])) {
                foreach (array_reverse($node['marks']) as $mark) {
                    $text = $this->applyMark($mark, $text);
                }
            }
            return $text;
        }

        // Delegate to registered renderer
        if (isset($this->renderers[$type])) {
            return $this->renderers[$type]->render($node, $this);
        }

        // Fallback for unknown blocks (return content if any)
        return isset($node['content']) ? $this->renderNodes($node['content']) : '';
    }

    private function applyMark(array $mark, string $text): string
    {
        switch ($mark['type']) {
            case 'bold':
                return "<strong>{$text}</strong>";
            case 'italic':
                return "<em>{$text}</em>";
            case 'strike':
                return "<s>{$text}</s>";
            case 'link':
                $href = $mark['attrs']['href'] ?? '#';
                return "<a href=\"{$href}\">{$text}</a>";
            default:
                return $text;
        }
    }
}

<?php

namespace app\services;

class BlockRenderer
{
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

    private function renderNodes(array $nodes): string
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
        $content = isset($node['content']) ? $this->renderNodes($node['content']) : '';
        $text = $node['text'] ?? '';

        // Sanitize first
        // Note: We might need to handle entities intelligently if Tiptap sends them?
        // Usually Tiptap sends raw chars.
        $text = htmlspecialchars($text);

        // Handle Marks (Bold, Italic, Strike)
        if (isset($node['marks'])) {
            foreach (array_reverse($node['marks']) as $mark) {
                $text = $this->applyMark($mark, $text);
            }
        }

        // If it's a text node, return the text (possibly marked)
        if ($type === 'text') {
            return $text;
        }

        // Handle Blocks
        switch ($type) {
            case 'heading':
                $level = $node['attrs']['level'] ?? 1;
                return "<h{$level}>{$content}</h{$level}>";

            case 'paragraph':
                return "<p>{$content}</p>";

            case 'bulletList':
                return "<ul>{$content}</ul>";

            case 'inputlist': // Tiptap 'bulletList' sometimes
                return "<ul>{$content}</ul>";

            case 'listItem':
                return "<li>{$content}</li>";

            case 'image':
                $src = $node['attrs']['src'] ?? '';
                $alt = $node['attrs']['alt'] ?? '';
                $title = $node['attrs']['title'] ?? '';
                $caption = $title ? "<figcaption>{$title}</figcaption>" : "";
                return "<figure><img src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\">{$caption}</figure>";

            case 'grid':
                return "<div class=\"grid-layout flex gap-4 my-4\">{$content}</div>";

            case 'column':
                return "<div class=\"grid-column flex-1 min-w-0\">{$content}</div>";

            default:
                // Fallback for unknown blocks (or just ignore)
                return $content;
        }
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

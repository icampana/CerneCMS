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
                $lightbox = $node['attrs']['lightbox'] ?? false;
                $caption = $title ? "<figcaption>{$title}</figcaption>" : "";

                $lightboxAttr = $lightbox ? 'data-pswp-width="auto" data-pswp-height="auto"' : '';
                $lightboxClass = $lightbox ? 'cursor-pointer hover:opacity-90' : '';

                return "<figure><img src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\" {$lightboxAttr} class=\"{$lightboxClass}\">{$caption}</figure>";

            case 'grid':
                return "<div class=\"grid-layout flex gap-4 my-4\">{$content}</div>";

            case 'column':
                return "<div class=\"grid-column flex-1 min-w-0\">{$content}</div>";

            case 'table':
                return "<div class=\"overflow-x-auto my-4\"><table class=\"w-full text-left border-collapse\"><tbody>{$content}</tbody></table></div>";

            case 'tableRow':
                return "<tr class=\"border-b border-gray-200\">{$content}</tr>";

            case 'tableHeader':
                return "<th class=\"p-2 bg-gray-50 font-bold border border-gray-200\">{$content}</th>";

            case 'tableCell':
                return "<td class=\"p-2 border border-gray-200\">{$content}</td>";

            case 'youtube':
                $src = $node['attrs']['src'] ?? '';
                // Ensure src is embeddable or trust input? Tiptap extension handles normalisation usually.
                return "<div class=\"aspect-video w-full my-4 rounded-lg overflow-hidden\"><iframe src=\"{$src}\" class=\"w-full h-full\" frameborder=\"0\" allowfullscreen></iframe></div>";

            case 'horizontalRule':
                return "<hr class=\"my-8 border-gray-200\">";

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

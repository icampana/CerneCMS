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
                // Use standard cursor-pointer if not wrapping in anchor, but anchor is better.
                if ($lightbox) {
                    return "<figure><a href=\"{$src}\" {$lightboxAttr} class=\"lightbox-trigger block cursor-zoom-in\"><img src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\" class=\"hover:opacity-90 transition-opacity\"></a>{$caption}</figure>";
                } else {
                    return "<figure><img src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\">{$caption}</figure>";
                }

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

            case 'cta':
                $layout = $node['attrs']['layout'] ?? 'centered';
                $title = htmlspecialchars($node['attrs']['title'] ?? 'Ready to get started?');
                $subtitle = htmlspecialchars($node['attrs']['subtitle'] ?? 'Join thousands of satisfied customers today.');
                $buttonText = htmlspecialchars($node['attrs']['buttonText'] ?? 'Get Started');
                $buttonUrl = htmlspecialchars($node['attrs']['buttonUrl'] ?? '#');
                $backgroundImage = $node['attrs']['backgroundImage'] ?? '';
                $textColor = $node['attrs']['textColor'] ?? 'auto';

                // Determine text color classes
                $isHeroOrBg = $layout === 'hero' || !empty($backgroundImage);
                $autoTextColor = $isHeroOrBg ? 'text-white' : 'text-gray-900';
                $textColorClass = $textColor === 'light' ? 'text-white' : ($textColor === 'dark' ? 'text-gray-900' : $autoTextColor);

                // Layout classes
                $layoutClasses =
                    $layout === 'centered' ? 'items-center text-center' :
                    ($layout === 'split' ? 'md:flex-row md:items-center md:justify-between' :
                        ($layout === 'hero' ? 'items-center text-center py-20 min-h-[400px]' : ''));

                // Background classes
                $bgClasses = !$backgroundImage && $layout !== 'hero' ? 'bg-gray-50' :
                    ($layout === 'hero' && !$backgroundImage ? 'bg-gradient-to-br from-blue-600 to-blue-800' : '');

                $bgStyle = $backgroundImage ? "background-image: url({$backgroundImage}); background-size: cover; background-position: center;" : '';

                // Button classes
                $btnClasses = $isHeroOrBg ? 'bg-white text-gray-900 hover:bg-gray-100' : 'bg-blue-600 text-white hover:bg-blue-700';

                // Title size classes
                $titleClasses = $layout === 'hero' ? 'text-4xl md:text-5xl font-bold' : 'text-3xl md:text-4xl font-bold';
                $subtitleClasses = $layout === 'hero' ? 'text-xl opacity-90' : 'text-lg opacity-90';

                // Overlay
                $overlay = ($backgroundImage || $layout === 'hero') ? '<div class="absolute inset-0 bg-black/40" style="pointer-events: none;"></div>' : '';

                return "
                <div class=\"cta-wrapper my-8\" style=\"margin: 2rem 0;\">
                    <div class=\"cta-container rounded-2xl overflow-hidden relative shadow-lg transition-all duration-300 {$bgClasses}\" style=\"{$bgStyle}\">
                        {$overlay}
                        <div class=\"relative z-10 p-8 md:p-12 flex flex-col gap-6 {$layoutClasses} {$textColorClass}\">
                            <div class=\"flex flex-col gap-4 max-w-2xl\">
                                <h2 class=\"{$titleClasses}\">{$title}</h2>
                                <p class=\"{$subtitleClasses}\">{$subtitle}</p>
                            </div>
                            <div class=\"flex-shrink-0\">
                                <a href=\"{$buttonUrl}\" class=\"inline-block px-6 py-3 rounded-lg font-semibold transition-transform hover:scale-105 active:scale-95 {$btnClasses}\">
                                    {$buttonText}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>";

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

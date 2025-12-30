<?php

namespace app\services\blocks;

use app\services\BlockRenderer;

class ImageRenderer implements BlockRendererInterface
{
    public function render(array $node, BlockRenderer $renderer): string
    {
        $src = $node['attrs']['src'] ?? '';
        $alt = $node['attrs']['alt'] ?? '';
        $title = $node['attrs']['title'] ?? '';
        $lightbox = $node['attrs']['lightbox'] ?? false;
        $caption = $title ? "<figcaption>{$title}</figcaption>" : "";

        $lightboxAttr = $lightbox ? 'data-pswp-width="auto" data-pswp-height="auto"' : '';

        if ($lightbox) {
            return "<figure><a href=\"{$src}\" {$lightboxAttr} class=\"lightbox-trigger block cursor-zoom-in\"><img src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\" class=\"hover:opacity-90 transition-opacity\"></a>{$caption}</figure>";
        } else {
            return "<figure><img src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\">{$caption}</figure>";
        }
    }
}

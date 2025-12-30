<?php

namespace app\services\blocks;

use app\services\BlockRenderer;

class YoutubeRenderer implements BlockRendererInterface
{
    public function render(array $node, BlockRenderer $renderer): string
    {
        $src = $node['attrs']['src'] ?? '';
        return "<div class=\"aspect-video w-full my-4 rounded-lg overflow-hidden\"><iframe src=\"{$src}\" class=\"w-full h-full\" frameborder=\"0\" allowfullscreen></iframe></div>";
    }
}

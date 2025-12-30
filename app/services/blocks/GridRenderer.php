<?php

namespace app\services\blocks;

use app\services\BlockRenderer;

class GridRenderer implements BlockRendererInterface
{
    public function render(array $node, BlockRenderer $renderer): string
    {
        $content = isset($node['content']) ? $renderer->renderNodes($node['content']) : '';
        $type = $node['type'];

        if ($type === 'grid') {
            return "<div class=\"grid-layout flex gap-4 my-4\">{$content}</div>";
        } elseif ($type === 'column') {
            return "<div class=\"grid-column flex-1 min-w-0\">{$content}</div>";
        }

        return $content;
    }
}

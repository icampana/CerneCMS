<?php

namespace app\services\blocks;

use app\services\BlockRenderer;

class ListItemRenderer implements BlockRendererInterface
{
    public function render(array $node, BlockRenderer $renderer): string
    {
        $content = isset($node['content']) ? $renderer->renderNodes($node['content']) : '';
        return "<li>{$content}</li>";
    }
}

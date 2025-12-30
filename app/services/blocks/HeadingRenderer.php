<?php

namespace app\services\blocks;

use app\services\BlockRenderer;

class HeadingRenderer implements BlockRendererInterface
{
    public function render(array $node, BlockRenderer $renderer): string
    {
        $level = $node['attrs']['level'] ?? 1;
        $content = isset($node['content']) ? $renderer->renderNodes($node['content']) : '';
        return "<h{$level}>{$content}</h{$level}>";
    }
}

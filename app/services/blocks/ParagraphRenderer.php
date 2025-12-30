<?php

namespace app\services\blocks;

use app\services\BlockRenderer;

class ParagraphRenderer implements BlockRendererInterface
{
    public function render(array $node, BlockRenderer $renderer): string
    {
        $content = isset($node['content']) ? $renderer->renderNodes($node['content']) : '';
        return "<p>{$content}</p>";
    }
}

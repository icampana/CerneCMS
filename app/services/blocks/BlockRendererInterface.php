<?php

namespace app\services\blocks;

use app\services\BlockRenderer;

interface BlockRendererInterface
{
    /**
     * Render a Tiptap node to HTML
     *
     * @param array $node The node data from the JSON
     * @param BlockRenderer $renderer The main renderer instance (for recursive calls)
     * @return string HTML output
     */
    public function render(array $node, BlockRenderer $renderer): string;
}

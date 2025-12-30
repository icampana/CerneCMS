<?php

namespace app\services\blocks;

use app\services\BlockRenderer;

class HorizontalRuleRenderer implements BlockRendererInterface
{
    public function render(array $node, BlockRenderer $renderer): string
    {
        return '<hr class="my-8 border-gray-200">';
    }
}

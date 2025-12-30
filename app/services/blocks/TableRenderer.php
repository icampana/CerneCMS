<?php

namespace app\services\blocks;

use app\services\BlockRenderer;

class TableRenderer implements BlockRendererInterface
{
    public function render(array $node, BlockRenderer $renderer): string
    {
        $content = isset($node['content']) ? $renderer->renderNodes($node['content']) : '';
        $type = $node['type'];

        switch ($type) {
            case 'table':
                return "<div class=\"overflow-x-auto my-4\"><table class=\"w-full text-left border-collapse\"><tbody>{$content}</tbody></table></div>";
            case 'tableRow':
                return "<tr class=\"border-b border-gray-200\">{$content}</tr>";
            case 'tableHeader':
                return "<th class=\"p-2 bg-gray-50 font-bold border border-gray-200\">{$content}</th>";
            case 'tableCell':
                return "<td class=\"p-2 border border-gray-200\">{$content}</td>";
            default:
                return $content;
        }
    }
}

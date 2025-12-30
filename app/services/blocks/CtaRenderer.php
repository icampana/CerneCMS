<?php

namespace app\services\blocks;

use app\services\BlockRenderer;

class CtaRenderer implements BlockRendererInterface
{
    public function render(array $node, BlockRenderer $renderer): string
    {
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
    }
}

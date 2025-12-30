<?php

namespace app\services\blocks;

use app\services\BlockRenderer;

class GalleryRenderer implements BlockRendererInterface
{
    public function render(array $node, BlockRenderer $renderer): string
    {
        $layout = $node['attrs']['layout'] ?? 'standard';
        $showCaptions = $node['attrs']['showCaptions'] ?? false;
        $autoplay = $node['attrs']['autoplay'] ?? true;
        $autoplaySpeed = $node['attrs']['autoplaySpeed'] ?? 3000;
        $gap = $node['attrs']['gap'] ?? 8;
        $columns = $node['attrs']['columns'] ?? 3;

        $images = $node['attrs']['images'] ?? [];
        $galleryId = 'gallery-' . uniqid();

        if (empty($images)) {
            return '<div class="gallery-empty border-2 border-dashed border-gray-300 rounded-lg p-12 text-center text-gray-500">No images in gallery</div>';
        }

        $html = '<div class="gallery-wrapper my-8">';

        if ($layout === 'masonry') {
            // Masonry Layout
            $html .= '<div class="masonry-gallery" style="column-count: ' . $columns . '; column-gap: ' . $gap . 'px;">';
            foreach ($images as $image) {
                // Image objects in attrs are flat objects, not nodes with 'attrs'
                $src = htmlspecialchars($image['src'] ?? '');
                $alt = htmlspecialchars($image['alt'] ?? '');
                $caption = htmlspecialchars($image['caption'] ?? '');
                $html .= '<div class="masonry-item mb-4" style="break-inside: avoid;">';
                $html .= '<img src="' . $src . '" alt="' . $alt . '" class="w-full rounded-lg">';
                if ($showCaptions && $caption) {
                    $html .= '<p class="mt-2 text-sm text-gray-600">' . $caption . '</p>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        } elseif ($layout === 'slideshow') {
            // Slideshow Layout
            // Pass configuration via data attributes for the frontend JS
            $autoplayVal = $autoplay ? 'true' : 'false';
            $html .= '<div class="swiper-gallery relative" id="' . $galleryId . '"
                data-autoplay="' . $autoplayVal . '"
                data-autoplay-speed="' . $autoplaySpeed . '"
                data-gap="' . $gap . '">';

            $html .= '<div class="swiper">';
            $html .= '<div class="swiper-wrapper">';
            foreach ($images as $image) {
                $src = htmlspecialchars($image['src'] ?? '');
                $alt = htmlspecialchars($image['alt'] ?? '');
                $caption = htmlspecialchars($image['caption'] ?? '');
                $html .= '<div class="swiper-slide relative">';
                $html .= '<img src="' . $src . '" alt="' . $alt . '" class="w-full h-auto rounded-lg">';
                if ($showCaptions && $caption) {
                    $html .= '<div class="absolute bottom-0 left-0 right-0 bg-black/60 text-white p-4 rounded-b-lg">';
                    $html .= '<p class="text-sm">' . $caption . '</p>';
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
            if (count($images) > 1) {
                $html .= '<div class="swiper-button-next"></div>';
                $html .= '<div class="swiper-button-prev"></div>';
                $html .= '<div class="swiper-pagination"></div>';
            }
            $html .= '</div>';
            $html .= '</div>';
        } else {
            // Standard Layout
            $html .= '<div class="standard-gallery grid" style="grid-template-columns: repeat(' . $columns . ', 1fr); gap: ' . $gap . 'px;">';
            foreach ($images as $image) {
                $src = htmlspecialchars($image['src'] ?? '');
                $alt = htmlspecialchars($image['alt'] ?? '');
                $caption = htmlspecialchars($image['caption'] ?? '');
                $html .= '<div class="standard-item">';
                $html .= '<img src="' . $src . '" alt="' . $alt . '" class="w-full h-48 object-cover rounded-lg">';
                if ($showCaptions && $caption) {
                    $html .= '<p class="mt-2 text-sm text-gray-600">' . $caption . '</p>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }
}

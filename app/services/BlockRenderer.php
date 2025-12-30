<?php

namespace app\services;

class BlockRenderer
{
    /**
     * Render Tiptap JSON string to HTML
     */
    public function render(string $json): string
    {
        $data = json_decode($json, true);

        if (!$data || !isset($data['type']) || $data['type'] !== 'doc') {
            return '';
        }

        return $this->renderNodes($data['content'] ?? []);
    }

    private function renderNodes(array $nodes): string
    {
        $html = '';
        foreach ($nodes as $node) {
            $html .= $this->renderNode($node);
        }
        return $html;
    }

    private function renderNode(array $node): string
    {
        $type = $node['type'];
        $content = isset($node['content']) ? $this->renderNodes($node['content']) : '';
        $text = $node['text'] ?? '';

        // Sanitize first
        // Note: We might need to handle entities intelligently if Tiptap sends them?
        // Usually Tiptap sends raw chars.
        $text = htmlspecialchars($text);

        // Handle Marks (Bold, Italic, Strike)
        if (isset($node['marks'])) {
            foreach (array_reverse($node['marks']) as $mark) {
                $text = $this->applyMark($mark, $text);
            }
        }

        // If it's a text node, return the text (possibly marked)
        if ($type === 'text') {
            return $text;
        }

        // Handle Blocks
        switch ($type) {
            case 'heading':
                $level = $node['attrs']['level'] ?? 1;
                return "<h{$level}>{$content}</h{$level}>";

            case 'paragraph':
                return "<p>{$content}</p>";

            case 'bulletList':
                return "<ul>{$content}</ul>";

            case 'inputlist': // Tiptap 'bulletList' sometimes
                return "<ul>{$content}</ul>";

            case 'listItem':
                return "<li>{$content}</li>";

            case 'image':
                $src = $node['attrs']['src'] ?? '';
                $alt = $node['attrs']['alt'] ?? '';
                $title = $node['attrs']['title'] ?? '';
                $lightbox = $node['attrs']['lightbox'] ?? false;
                $caption = $title ? "<figcaption>{$title}</figcaption>" : "";

                $lightboxAttr = $lightbox ? 'data-pswp-width="auto" data-pswp-height="auto"' : '';
                // Use standard cursor-pointer if not wrapping in anchor, but anchor is better.
                if ($lightbox) {
                    return "<figure><a href=\"{$src}\" {$lightboxAttr} class=\"lightbox-trigger block cursor-zoom-in\"><img src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\" class=\"hover:opacity-90 transition-opacity\"></a>{$caption}</figure>";
                } else {
                    return "<figure><img src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\">{$caption}</figure>";
                }

            case 'grid':
                return "<div class=\"grid-layout flex gap-4 my-4\">{$content}</div>";

            case 'column':
                return "<div class=\"grid-column flex-1 min-w-0\">{$content}</div>";

            case 'table':
                return "<div class=\"overflow-x-auto my-4\"><table class=\"w-full text-left border-collapse\"><tbody>{$content}</tbody></table></div>";

            case 'tableRow':
                return "<tr class=\"border-b border-gray-200\">{$content}</tr>";

            case 'tableHeader':
                return "<th class=\"p-2 bg-gray-50 font-bold border border-gray-200\">{$content}</th>";

            case 'tableCell':
                return "<td class=\"p-2 border border-gray-200\">{$content}</td>";


            case 'youtube':
                $src = $node['attrs']['src'] ?? '';
                // Ensure src is embeddable or trust input? Tiptap extension handles normalisation usually.
                return "<div class=\"aspect-video w-full my-4 rounded-lg overflow-hidden\"><iframe src=\"{$src}\" class=\"w-full h-full\" frameborder=\"0\" allowfullscreen></iframe></div>";

            case 'cta':
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

            case 'horizontalRule':
                return "<hr class=\"my-8 border-gray-200\">";

            case 'gallery':
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

            case 'formBlock':
                $formId = $node['attrs']['formId'] ?? null;
                if (!$formId)
                    return '';

                $formModel = new \app\models\Form();
                $form = $formModel->find($formId);

                if (!$form || $form->status !== 'active')
                    return '';

                $fields = $form->getFields();
                $settings = $form->getSettings();

                $token = \app\helpers\CSRF::generate();
                $action = "/forms/{$form->slug}/submit";

                $html = "<form action=\"{$action}\" method=\"POST\" class=\"cerne-form space-y-4 my-8 p-6 bg-gray-50 rounded-lg border border-gray-200\" data-slug=\"{$form->slug}\">";
                $html .= "<input type=\"hidden\" name=\"csrf_token\" value=\"{$token}\">";

                $html .= "<div class=\"form-message hidden p-4 mb-4 text-sm rounded-lg\" role=\"alert\"></div>";

                foreach ($fields as $field) {
                    $html .= "<div class=\"form-group\">";
                    $label = htmlspecialchars($field['label']);
                    $name = htmlspecialchars($field['name']);
                    $required = !empty($field['required']) ? 'required' : '';
                    $reqStar = !empty($field['required']) ? '<span class="text-red-500">*</span>' : '';
                    $placeholder = htmlspecialchars($field['placeholder'] ?? '');

                    if ($field['type'] !== 'checkbox') {
                        $html .= "<label for=\"field_{$name}\" class=\"block mb-2 text-sm font-medium text-gray-900\">{$label} {$reqStar}</label>";
                    }

                    if ($field['type'] === 'textarea') {
                        $html .= "<textarea id=\"field_{$name}\" name=\"{$name}\" rows=\"4\" class=\"block p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500\" placeholder=\"{$placeholder}\" {$required}></textarea>";
                    } elseif ($field['type'] === 'select') {
                        $html .= "<select id=\"field_{$name}\" name=\"{$name}\" class=\"bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5\" {$required}>";
                        $options = is_array($field['options']) ? $field['options'] : (is_string($field['options']) ? array_map('trim', explode(',', $field['options'])) : []);
                        $html .= "<option value=\"\">Select an option</option>";
                        foreach ($options as $opt) {
                            $optHtml = htmlspecialchars($opt);
                            $html .= "<option value=\"{$optHtml}\">{$optHtml}</option>";
                        }
                        $html .= "</select>";
                    } elseif ($field['type'] === 'checkbox') {
                        $html .= "<div class=\"flex items-center\">";
                        $html .= "<input id=\"field_{$name}\" type=\"checkbox\" name=\"{$name}\" class=\"w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2\" {$required}>";
                        $html .= "<label for=\"field_{$name}\" class=\"ms-2 text-sm font-medium text-gray-900\">{$label} {$reqStar}</label>";
                        $html .= "</div>";
                    } else {
                        $type = htmlspecialchars($field['type']);
                        $html .= "<input type=\"{$type}\" id=\"field_{$name}\" name=\"{$name}\" class=\"bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5\" placeholder=\"{$placeholder}\" {$required}>";
                    }

                    $html .= "</div>";
                }

                $submitLabel = htmlspecialchars($settings['submitLabel'] ?? 'Submit');
                $html .= "<button type=\"submit\" class=\"text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center disabled:opacity-50 disabled:cursor-not-allowed\">";
                $html .= "<span class=\"submit-text\">{$submitLabel}</span>";
                $html .= "<span class=\"loading-spinner hidden ml-2 animate-spin\">⟳</span>";
                $html .= "</button>";

                $html .= "</form>";
                return $html;

            default:
                // Fallback for unknown blocks (or just ignore)
                return $content;
        }
    }

    private function applyMark(array $mark, string $text): string
    {
        switch ($mark['type']) {
            case 'bold':
                return "<strong>{$text}</strong>";
            case 'italic':
                return "<em>{$text}</em>";
            case 'strike':
                return "<s>{$text}</s>";
            case 'link':
                $href = $mark['attrs']['href'] ?? '#';
                return "<a href=\"{$href}\">{$text}</a>";
            default:
                return $text;
        }
    }
}

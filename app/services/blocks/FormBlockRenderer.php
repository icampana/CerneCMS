<?php

namespace app\services\blocks;

use app\services\BlockRenderer;

class FormBlockRenderer implements BlockRendererInterface
{
    public function render(array $node, BlockRenderer $renderer): string
    {
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
    }
}

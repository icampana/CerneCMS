<?php

namespace app\helpers;

class Vite
{
    public static function asset($entry)
    {
        $manifestPath = __DIR__ . '/../../public/assets/.vite/manifest.json';

        // Development Mode: Hot Module Replacement (HMR)
        if (!file_exists($manifestPath)) {
            return "http://localhost:5173/src/{$entry}";
        }

        // Production Mode: Read from Manifest
        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (isset($manifest["src/{$entry}"])) {
            return '/assets/' . $manifest["src/{$entry}"]['file'];
        }

        return '';
    }

    public static function css($entry)
    {
        $manifestPath = __DIR__ . '/../../public/assets/.vite/manifest.json';
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (isset($manifest["src/{$entry}"]) && isset($manifest["src/{$entry}"]['css'])) {
                return array_map(function ($file) {
                    return '/assets/' . $file;
                }, $manifest["src/{$entry}"]['css']);
            }
        }
        return [];
    }
    /**
     * Output the HTML tags for Vite assets (JS + CSS)
     */
    public static function assets()
    {
        $manifestPath = __DIR__ . '/../../public/assets/.vite/manifest.json';
        $isDev = !file_exists($manifestPath);

        if ($isDev) {
            return '
                <script type="module" src="http://localhost:5173/@vite/client"></script>
                <script type="module" src="http://localhost:5173/src/main.js"></script>
            ';
        }

        // Production
        $html = '';

        // CSS
        $cssFiles = self::css('main.js');
        foreach ($cssFiles as $css) {
            $html .= '<link rel="stylesheet" href="' . $css . '">';
        }

        // JS
        $jsFile = self::asset('main.js');
        if ($jsFile) {
            $html .= '<script type="module" src="' . $jsFile . '"></script>';
        }

        return $html;
    }
}

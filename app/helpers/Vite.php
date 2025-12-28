<?php

namespace app\helpers;

class Vite
{
    /**
     * Determine if we are in development mode.
     */
    private static function isDev()
    {
        $manifestPath = __DIR__ . '/../../public/assets/.vite/manifest.json';
        $devFlagFile = __DIR__ . '/../../.dev';

        // Check multiple sources for dev mode:
        // 1. .dev file exists in project root
        // 2. VITE_DEV env variable
        // 3. No manifest file exists
        $viteDev = getenv('VITE_DEV') ?: ($_ENV['VITE_DEV'] ?? ($_SERVER['VITE_DEV'] ?? 'false'));

        // Debug logging to terminal
        // error_log("VITE_DEV env var: " . var_export($viteDev, true));

        return file_exists($devFlagFile) || $viteDev === 'true' || !file_exists($manifestPath);
    }

    public static function asset($entry)
    {
        $manifestPath = __DIR__ . '/../../public/assets/.vite/manifest.json';

        if (self::isDev()) {
            return "http://localhost:5173/src/{$entry}";
        }

        // Production Mode: Read from Manifest
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (isset($manifest["src/{$entry}"])) {
                return '/assets/' . $manifest["src/{$entry}"]['file'];
            }
        }

        return '';
    }

    public static function css($entry)
    {
        $manifestPath = __DIR__ . '/../../public/assets/.vite/manifest.json';

        if (self::isDev()) {
            // In dev mode, CSS is usually injected by Vite JS client, so we might not need explicit CSS links,
            // or we might want to return nothing if Vite handles it.
            // However, if we need to return something, it would be empty array as Vite client handles CSS import.
            return [];
        }

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
        if (self::isDev()) {
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

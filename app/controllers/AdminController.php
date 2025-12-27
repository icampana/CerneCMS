<?php

namespace app\controllers;

use Flight;

class AdminController
{

    public function dashboard()
    {
        $script = \app\helpers\Vite::asset('main.js');
        // CSS handling needs improvement in Vite helper to return string, but for now typical Vite manifest usage:
        $css = '';
        $cssFiles = \app\helpers\Vite::css('main.js');
        foreach ($cssFiles as $file) {
            $css .= '<link rel="stylesheet" href="' . $file . '">';
        }

        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CerneCMS Admin</title>
    {$css}
</head>
<body>
    <div id="cms-editor"></div>

    <script type="module" src="{$script}"></script>
</body>
</html>
HTML;
    }
}

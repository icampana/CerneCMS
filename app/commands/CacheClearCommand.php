<?php

namespace app\commands;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class CacheClearCommand implements Command
{
    public function execute(array $args): void
    {
        echo "Clearing cache...\n";
        $cacheDir = __DIR__ . '/../../public/cache';

        if (is_dir($cacheDir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($cacheDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $todo($fileinfo->getRealPath());
            }
            echo "✔ Cache cleared successfully.\n";
        } else {
            echo "✔ Cache directory does not exist (nothing to clear).\n";
        }
    }

    public function getDescription(): string
    {
        return "Clear the application cache (Latte)";
    }

    public function getUsage(): string
    {
        return "cache:clear";
    }
}

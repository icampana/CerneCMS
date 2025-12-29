<?php

namespace app\helpers;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Cache
{
    /**
     * Clear the application cache directory.
     *
     * @return bool True if cleared or directory doesn't exist, false on failure.
     */
    public static function clear(): bool
    {
        $cacheDir = __DIR__ . '/../../public/cache';

        if (!is_dir($cacheDir)) {
            return true;
        }

        try {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($cacheDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $todo($fileinfo->getRealPath());
            }
            return true;
        } catch (\Exception $e) {
            error_log("Failed to clear cache: " . $e->getMessage());
            return false;
        }
    }
}

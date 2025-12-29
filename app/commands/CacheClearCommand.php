<?php

namespace app\commands;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class CacheClearCommand implements Command
{
    public function execute(array $args): void
    {
        echo "Clearing cache...\n";

        if (\app\helpers\Cache::clear()) {
            echo "✔ Cache cleared successfully.\n";
        } else {
            echo "✘ Failed to clear cache. Check logs/permissions.\n";
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

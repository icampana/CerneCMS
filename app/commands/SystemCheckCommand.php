<?php

namespace app\commands;

class SystemCheckCommand implements Command
{
    private array $paths = [
        'public/cache' => '0777',
        'content/database' => '0777',
        'content/uploads' => '0777',
    ];

    public function execute(array $args): void
    {
        echo "Checking system permissions...\n\n";

        $root = dirname(__DIR__, 2);
        $hasErrors = false;

        foreach ($this->paths as $relPath => $mode) {
            $path = $root . '/' . $relPath;
            echo "Checking {$relPath}... ";

            // Check existence
            if (!file_exists($path)) {
                echo "MISSING. Attempting to create... ";
                if (@mkdir($path, octdec($mode), true)) {
                    echo "CREATED.\n";
                } else {
                    echo "\033[31mFAILED\033[0m (Could not create directory)\n";
                    $hasErrors = true;
                    continue; // Skip permission check if creation failed
                }
            }

            // Check writable
            if (is_writable($path)) {
                echo "\033[32mOK\033[0m\n";
            } else {
                echo "NOT WRITABLE. Attempting to fix... ";
                if (@chmod($path, octdec($mode))) {
                    echo "\033[32mFIXED\033[0m\n";
                } else {
                    echo "\033[31mFAILED\033[0m (Run: chmod $mode $relPath)\n";
                    $hasErrors = true;
                }
            }
        }

        echo "\n";
        if ($hasErrors) {
            echo "\033[31mSome checks failed. You may need to run commands manually with sudo.\033[0m\n";
        } else {
            echo "\033[32mAll systems check passed!\033[0m\n";
        }
    }

    public function getDescription(): string
    {
        return "Check and attempt to fix directory permissions";
    }

    public function getUsage(): string
    {
        return "system:check";
    }
}

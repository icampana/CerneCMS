<?php

namespace app\commands;

use Flight;
use app\models\Page;
use app\models\Block;
use PDO;

class DemoResetCommand implements Command
{
    public function execute(array $args): void
    {
        // 1. Confirm action
        echo "\033[31mWARNING: This will delete ALL pages, blocks, menus, and uploaded files.\033[0m\n";
        echo "Are you sure you want to proceed? (y/N): ";

        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim(strtolower($line)) != 'y') {
            echo "Aborted.\n";
            return;
        }

        $db = Flight::db();

        // 2. Clear Database
        echo "Clearing database...\n";

        $tables = ['pages', 'blocks', 'menus', 'menu_items', 'posts', 'forms'];
        foreach ($tables as $table) {
            // Check if table exists first (just in case)
            $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
            if ($stmt->fetch()) {
                $db->exec("DELETE FROM $table");
                $db->exec("DELETE FROM sqlite_sequence WHERE name='$table'");
            }
        }

        // 3. Clear Uploads
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads';
        echo "Cleaning uploads directory ($uploadDir)...\n";

        if (is_dir($uploadDir)) {
            $files = glob($uploadDir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== '.gitignore') {
                    unlink($file);
                }
            }
        } else {
            // Ensure it exists for the fresh start
            mkdir($uploadDir, 0777, true);
        }

        // 4. Seed Default Content
        echo "Seeding default content...\n";

        // Create Home Page
        $db->exec("INSERT INTO pages (slug, title, status, layout, created_at, updated_at) VALUES
            ('home', 'Home', 'published', 'default', datetime('now'), datetime('now'))");
        $homeId = $db->lastInsertId();

        // Create Home Block (Welcome Content)
        $welcomeContent = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 1],
                    'content' => [['type' => 'text', 'text' => 'Welcome to CerneCMS']]
                ],
                [
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => 'This is your new homepage. You can edit this content in the admin panel.']]
                ]
            ]
        ]);

        $stmt = $db->prepare("INSERT INTO blocks (page_id, zone, type, content_json, created_at, updated_at) VALUES (?, ?, ?, ?, datetime('now'), datetime('now'))");
        $stmt->execute([$homeId, 'main', 'tiptap', $welcomeContent]);

        // Create Main Menu
        $db->exec("INSERT INTO menus (name, slug, is_primary, location, created_at, updated_at) VALUES
            ('Main Menu', 'main', 1, 'header', datetime('now'), datetime('now'))");
        $menuId = $db->lastInsertId();

        // Create Menu Item (Link to Home)
        $stmt = $db->prepare("INSERT INTO menu_items (menu_id, title, link_type, link_value, target_page_id, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, datetime('now'))");
        $stmt->execute([$menuId, 'Home', 'page', 'home', $homeId, 0]);

        echo "\033[32m✔ Reset complete. Site is ready.\033[0m\n";
    }

    public function getDescription(): string
    {
        return "Reset pages, menus, and uploads to a fresh demo state";
    }

    public function getUsage(): string
    {
        return "demo:reset";
    }
}

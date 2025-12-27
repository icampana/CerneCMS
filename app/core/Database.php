<?php

namespace app\core;

use Flight;

class Database
{

    public static function init()
    {
        $db = Flight::db();

        // Check if 'pages' table exists
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='pages'");

        if (!$stmt->fetch()) {
            // Create pages table
            $db->exec("CREATE TABLE pages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                slug TEXT UNIQUE NOT NULL,
                title TEXT,
                status TEXT DEFAULT 'draft',
                layout TEXT DEFAULT 'default',
                meta_json TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            // Create blocks table
            $db->exec("CREATE TABLE blocks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                page_id INTEGER,
                zone TEXT,
                type TEXT,
                content_json TEXT,
                settings_json TEXT,
                sort_order INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(page_id) REFERENCES pages(id) ON DELETE CASCADE
            )");

            // Create indexes
            $db->exec("CREATE INDEX idx_pages_slug ON pages(slug)");
            $db->exec("CREATE INDEX idx_blocks_page_id ON blocks(page_id)");
            $db->exec("CREATE INDEX idx_blocks_page_id ON blocks(page_id)");
        }

        // Check if 'posts' table exists
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='posts'");

        if (!$stmt->fetch()) {
            // Create posts table
            $db->exec("CREATE TABLE posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                slug TEXT UNIQUE NOT NULL,
                title TEXT,
                content_json TEXT,
                excerpt TEXT,
                status TEXT DEFAULT 'draft',
                published_at DATETIME,
                author_id INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $db->exec("CREATE INDEX idx_posts_slug ON posts(slug)");
        }
    }
}

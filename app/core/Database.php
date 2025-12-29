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
        // Check if 'users' table exists
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");

        if (!$stmt->fetch()) {
            // Create users table
            $db->exec("CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $db->exec("CREATE INDEX idx_users_username ON users(username)");
            $db->exec("CREATE INDEX idx_users_email ON users(email)");
        }

        // Check if 'calendar_events' table exists
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='calendar_events'");

        if (!$stmt->fetch()) {
            // Create calendar_events table
            $db->exec("CREATE TABLE calendar_events (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                start_date DATETIME NOT NULL,
                end_date DATETIME,
                url TEXT,
                image TEXT,
                description TEXT,
                color TEXT DEFAULT '#000000',
                is_visible INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            // $db->exec("CREATE INDEX idx_calendar_events_widget_id ON calendar_events(widget_id)"); // Removed
            $db->exec("CREATE INDEX idx_calendar_events_start_date ON calendar_events(start_date)");
        }

        // Check for parent_id in pages
        $cols = $db->query("PRAGMA table_info(pages)")->fetchAll();
        $hasParentId = false;
        foreach ($cols as $col) {
            if ($col['name'] === 'parent_id') {
                $hasParentId = true;
                break;
            }
        }
        if (!$hasParentId) {
            $db->exec("ALTER TABLE pages ADD COLUMN parent_id INTEGER REFERENCES pages(id) ON DELETE SET NULL");
        }

        // Check if 'menus' table exists
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='menus'");
        if (!$stmt->fetch()) {
            $db->exec("CREATE TABLE menus (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                is_primary INTEGER DEFAULT 0,
                location TEXT DEFAULT 'header',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
        }

        // Check if 'menu_items' table exists
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='menu_items'");
        if (!$stmt->fetch()) {
            $db->exec("CREATE TABLE menu_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                menu_id INTEGER NOT NULL,
                parent_id INTEGER DEFAULT NULL,
                title TEXT NOT NULL,
                link_type TEXT NOT NULL,
                link_value TEXT NOT NULL,
                target_page_id INTEGER,
                is_external INTEGER DEFAULT 0,
                open_new_tab INTEGER DEFAULT 0,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(menu_id) REFERENCES menus(id) ON DELETE CASCADE,
                FOREIGN KEY(parent_id) REFERENCES menu_items(id) ON DELETE CASCADE,
                FOREIGN KEY(target_page_id) REFERENCES pages(id) ON DELETE SET NULL
            )");
            $db->exec("CREATE INDEX idx_menu_items_menu_id ON menu_items(menu_id)");
            $db->exec("CREATE INDEX idx_menu_items_parent_id ON menu_items(parent_id)");
        }

        // Check if 'settings' table exists
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='settings'");
        if (!$stmt->fetch()) {
            $db->exec("CREATE TABLE settings (
                key TEXT PRIMARY KEY,
                value TEXT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            // Insert default settings
            $stmt = $db->prepare("INSERT INTO settings (key, value) VALUES (?, ?)");
            $stmt->execute(['sidebar_enabled', 'internal']);
            $stmt->execute(['sidebar_menu_slug', NULL]);
        }
    }
}

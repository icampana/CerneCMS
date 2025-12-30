<?php

namespace app\models;

use flight\ActiveRecord;
use Flight;

class Form extends ActiveRecord
{
    public function __construct($db = null)
    {
        parent::__construct($db, 'forms');
    }

    protected function onConstruct(self $self, array &$config)
    {
        $config['connection'] = Flight::db();
    }

    /**
     * Get parsed fields definition
     */
    public function getFields(): array
    {
        if (empty($this->fields_json)) {
            return [];
        }
        $decoded = json_decode($this->fields_json, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get parsed settings
     */
    public function getSettings(): array
    {
        if (empty($this->settings_json)) {
            return [];
        }
        $decoded = json_decode($this->settings_json, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Create the dynamic response table for this form
     */
    public function createResponseTable()
    {
        if (!$this->id) {
            throw new \Exception("Form must be saved before creating response table");
        }

        $tableName = 'form_responses_' . (int) $this->id;
        $db = Flight::db();

        // Sanitize table name (though int cast protects us)
        $db->exec("CREATE TABLE IF NOT EXISTS {$tableName} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            data_json TEXT NOT NULL,
            ip_address TEXT,
            user_agent TEXT,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_read INTEGER DEFAULT 0
        )");

        $db->exec("CREATE INDEX IF NOT EXISTS idx_{$tableName}_submitted_at ON {$tableName}(submitted_at)");
    }

    /**
     * Drop the dynamic response table
     */
    public function dropResponseTable()
    {
        if (!$this->id)
            return;

        $tableName = 'form_responses_' . (int) $this->id;
        $db = Flight::db();
        $db->exec("DROP TABLE IF EXISTS {$tableName}");
    }

    /**
     * Get responses for this form
     */
    public function getResponses($limit = 100, $offset = 0)
    {
        if (!$this->id)
            return [];

        $tableName = 'form_responses_' . (int) $this->id;
        $db = Flight::db();

        // Verify table exists first to avoid fatal errors if out of sync
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$tableName}'");
        if (!$stmt->fetch())
            return [];

        $stmt = $db->prepare("SELECT * FROM {$tableName} ORDER BY submitted_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $responses = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['data'] = json_decode($row['data_json'], true);
            $responses[] = $row;
        }

        return $responses;
    }

    /**
     * Count total responses
     */
    public function countResponses()
    {
        if (!$this->id)
            return 0;

        $tableName = 'form_responses_' . (int) $this->id;
        $db = Flight::db();

        // Verify table exists
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$tableName}'");
        if (!$stmt->fetch())
            return 0;

        $stmt = $db->query("SELECT COUNT(*) FROM {$tableName}");
        return $stmt->fetchColumn();
    }

    /**
     * Override delete to clean up response table
     */
    public function delete()
    {
        $this->dropResponseTable();
        // Since ActiveRecord::delete() doesn't exist in Flight's simple AR (it uses unmap or similar usually),
        // we might check how other models delete or use direct DB if needed.
        // Assuming standard behavior or base class has it.
        // If not, we do:
        $db = Flight::db();
        $stmt = $db->prepare("DELETE FROM forms WHERE id = :id");
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
    }
}

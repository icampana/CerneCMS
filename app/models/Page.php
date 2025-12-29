<?php

namespace app\models;

use flight\ActiveRecord;

class Page extends ActiveRecord
{

    public function __construct($db = null)
    {
        parent::__construct($db, 'pages');
    }

    protected function onConstruct(self $self, array &$config)
    {
        $config['connection'] = \Flight::db();
    }

    public function getParent()
    {
        if (!$this->parent_id) {
            return null;
        }
        $model = new self();
        return $model->eq('id', $this->parent_id)->find();
    }

    public function getChildren()
    {
        $model = new self();
        return $model->eq('parent_id', $this->id)->findAll();
    }

    public function getSiblings()
    {
        if (!$this->parent_id) {
            // Root pages
            $model = new self();
            return $model->isNull('parent_id')->ne('id', $this->id)->findAll();
        }
        $model = new self();
        return $model->eq('parent_id', $this->parent_id)->ne('id', $this->id)->findAll();
    }

    /**
     * Get ancestors ordered from root to parent
     */
    public function getBreadcrumbs()
    {
        $crumbs = [];
        $current = $this;

        while ($current->parent_id) {
            $parent = $current->getParent();
            if (!$parent)
                break;
            array_unshift($crumbs, $parent);
            $current = $parent;
        }

        return $crumbs;
    }

    public static function search($query)
    {
        $db = \Flight::db();
        $term = '%' . $query . '%';
        $sql = "SELECT id, title, slug, parent_id FROM pages WHERE title LIKE ? OR slug LIKE ? LIMIT 20";
        $stmt = $db->prepare($sql);
        $stmt->execute([$term, $term]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

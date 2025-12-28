<?php

namespace app\controllers;

use Flight;
use app\models\Page;
use app\models\Block;

class ApiController
{

    public function getPages()
    {
        $pageModel = new Page();
        $pages = $pageModel->findAll();
        Flight::json($pages);
    }

    public function getPage($id)
    {
        $pageModel = new Page();
        $page = $pageModel->find($id);

        if ($page) {
            // Fetch associated content block
            $blockModel = new Block();
            $blocks = $blockModel->eq('page_id', $page->id)->eq('zone', 'main')->findAll();
            $block = !empty($blocks) ? $blocks[0] : null;

            $response = [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'status' => $page->status,
                'created_at' => $page->created_at,
                'updated_at' => $page->updated_at,
                // Return content as object (decoded from the stored JSON string)
                'content' => $block ? json_decode($block->content_json) : null
            ];

            Flight::json($response);
        } else {
            Flight::halt(404, json_encode(['error' => 'Page not found']));
        }
    }

    public function savePage()
    {
        $data = Flight::request()->data;

        // Basic Validation
        if (empty($data->title) || empty($data->slug)) {
            Flight::halt(400, json_encode(['error' => 'Title and Slug are required']));
            return;
        }

        // 1. Handle Page
        // Flight ActiveRecord: custom queries use fluid interface
        $pageModel = new Page();
        // find() returns array of records if conditions are messed up?
        // Docs say: $model->eq('column', 'value')->find(); returns (one) object or false
        // findAll() returns array.

        $pages = $pageModel->eq('slug', $data->slug)->findAll();
        $page = !empty($pages) ? $pages[0] : null;

        if (!$page) {
            $page = new Page();
            $page->slug = $data->slug;
        }

        $page->title = $data->title;
        $page->updated_at = date('Y-m-d H:i:s');
        $page->save();

        // 2. Handle Content (Block)
        // We assume a single 'main' block for now which holds the Tiptap content
        $blockModel = new Block();
        // ActiveRecord find usually returns the object or false.
        // We need to query by page_id AND zone.
        // Flight ActiveRecord might simple array conditions.
        // findAll returns array
        $blocks = $blockModel->eq('page_id', $page->id)->eq('zone', 'main')->findAll();
        $block = !empty($blocks) ? $blocks[0] : null;

        if (!$block) {
            $block = new Block();
            $block->page_id = $page->id; // $page->id should be populated after save()
            $block->zone = 'main';
            $block->type = 'tiptap';
        }

        // Content is passed as a generic object/array from JSON body, we store as string
        $block->content_json = json_encode($data->content);
        $block->updated_at = date('Y-m-d H:i:s');
        $block->save();

        Flight::json([
            'status' => 'success',
            'data' => [
                'page' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'updated_at' => $page->updated_at
                ],
                'id' => $page->id
            ]
        ]);
    }
    public function getMedia()
    {
        $uploadDir = __DIR__ . '/../../public/assets/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $files = array_diff(scandir($uploadDir), ['.', '..', '.gitignore']);
        $media = [];

        foreach ($files as $file) {
            $media[] = [
                'name' => $file,
                'url' => '/assets/uploads/' . $file,
                'type' => mime_content_type($uploadDir . '/' . $file)
            ];
        }

        Flight::json($media);
    }

    public function uploadMedia()
    {
        $uploadDir = __DIR__ . '/../../public/assets/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (empty($_FILES['file'])) {
            Flight::halt(400, json_encode(['error' => 'No file uploaded']));
        }

        $file = $_FILES['file'];
        $filename = basename($file['name']);
        // Sanitize filename
        $filename = preg_replace('/[^a-zA-Z0-9\._-]/', '', $filename);

        // Target path
        $targetPath = $uploadDir . '/' . $filename;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
            ];
            $message = $errorMessages[$file['error']] ?? 'Unknown upload error';
            Flight::halt(400, json_encode(['error' => $message]));
            return;
        }

        // Move file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            Flight::json([
                'status' => 'success',
                'url' => '/assets/uploads/' . $filename,
                'name' => $filename
            ]);
        } else {
            // Log the error for debugging
            error_log("Failed to move uploaded file from {$file['tmp_name']} to {$targetPath}");
            // Check if directory is writable
            if (!is_writable($uploadDir)) {
                error_log("Upload directory is not writable: $uploadDir");
                Flight::halt(500, json_encode(['error' => 'Upload directory permission denied']));
                return;
            }
            Flight::halt(500, json_encode(['error' => 'Failed to move uploaded file. Check server logs.']));
        }
    }
}

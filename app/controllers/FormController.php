<?php

namespace app\controllers;

use Flight;
use app\models\Form;

class FormController
{
    public function index()
    {
        $model = new Form();
        $forms = $model->findAll();

        $result = [];
        foreach ($forms as $form) {
            $row = $form->getRow();
            // Count responses
            $row['response_count'] = $form->countResponses();
            $result[] = $row;
        }

        Flight::json($result);
    }

    public function show($id)
    {
        $model = new Form();
        $form = $model->find($id);

        if (!$form) {
            Flight::halt(404, json_encode(['error' => 'Form not found']));
            return;
        }

        Flight::json($form->getRow());
    }

    public function create()
    {
        $data = Flight::request()->data;

        if (empty($data->name)) {
            Flight::halt(400, json_encode(['error' => 'Name is required']));
            return;
        }

        $form = new Form();
        $form->name = $data->name;
        // Generate slug from name if not provided
        $slug = $data->slug ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data->name)));
        $form->slug = $slug;
        $form->fields_json = is_string($data->fields_json) ? $data->fields_json : json_encode($data->fields_json ?? []);
        $form->settings_json = is_string($data->settings_json) ? $data->settings_json : json_encode($data->settings_json ?? []);
        $form->status = $data->status ?? 'active';

        try {
            $form->save();
            // Create response table
            $form->createResponseTable();
            Flight::json($form->getRow());
        } catch (\Exception $e) {
            Flight::halt(500, json_encode(['error' => $e->getMessage()]));
        }
    }

    public function update($id)
    {
        $data = Flight::request()->data;
        $model = new Form();
        $form = $model->find($id);

        if (!$form) {
            Flight::halt(404, json_encode(['error' => 'Form not found']));
            return;
        }

        if (isset($data->name))
            $form->name = $data->name;
        if (isset($data->slug))
            $form->slug = $data->slug; // Should validation uniqueness
        if (isset($data->fields_json))
            $form->fields_json = is_string($data->fields_json) ? $data->fields_json : json_encode($data->fields_json);
        if (isset($data->settings_json))
            $form->settings_json = is_string($data->settings_json) ? $data->settings_json : json_encode($data->settings_json);
        if (isset($data->status))
            $form->status = $data->status;

        $form->updated_at = date('Y-m-d H:i:s');
        $form->save();

        Flight::json($form->getRow());
    }

    public function delete($id)
    {
        $model = new Form();
        $form = $model->find($id);

        if ($form) {
            $form->delete(); // This triggers table drop in our custom delete
        }

        Flight::json(['status' => 'success']);
    }

    public function responses($id)
    {
        $model = new Form();
        $form = $model->find($id);

        if (!$form) {
            Flight::halt(404, json_encode(['error' => 'Form not found']));
            return;
        }

        $limit = Flight::request()->query->limit ?? 50;
        $offset = Flight::request()->query->offset ?? 0;

        $responses = $form->getResponses($limit, $offset);
        Flight::json($responses);
    }

    public function deleteResponse($id, $rid)
    {
        $model = new Form();
        $form = $model->find($id);

        if (!$form) {
            Flight::halt(404, json_encode(['error' => 'Form not found']));
            return;
        }

        $db = Flight::db();
        $tableName = 'form_responses_' . (int) $id;

        // Verify table exists
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$tableName}'");
        if (!$stmt->fetch()) {
            Flight::halt(404, json_encode(['error' => 'Response table not found']));
            return;
        }

        $stmt = $db->prepare("DELETE FROM {$tableName} WHERE id = :rid");
        $stmt->bindValue(':rid', $rid, \PDO::PARAM_INT);
        $stmt->execute();

        Flight::json(['status' => 'success']);
    }

    public function export($id)
    {
        $model = new Form();
        $form = $model->find($id);

        if (!$form) {
            Flight::halt(404, 'Form not found');
            return;
        }

        $responses = $form->getResponses(10000, 0); // High limit for export

        // Headers
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="form-' . $form->slug . '-responses.csv"');

        $out = fopen('php://output', 'w');

        if (empty($responses)) {
            fputcsv($out, ['No responses found']);
            fclose($out);
            return;
        }

        // Flatten headers from first response (or field defs)
        // Ideally we use field defs to ensure column order
        $headers = ['ID', 'Submitted At', 'IP Address'];
        $fields = $form->getFields();
        foreach ($fields as $field) {
            $headers[] = $field['label'] ?? $field['name'];
        }

        fputcsv($out, $headers);

        foreach ($responses as $resp) {
            $row = [
                $resp['id'],
                $resp['submitted_at'],
                $resp['ip_address']
            ];

            $data = $resp['data'] ?? [];
            foreach ($fields as $field) {
                $name = $field['name'];
                $val = $data[$name] ?? '';
                if (is_array($val))
                    $val = implode(', ', $val);
                $row[] = $val;
            }

            fputcsv($out, $row);
        }

        fclose($out);
    }
}

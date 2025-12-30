<?php

namespace app\controllers;

use Flight;
use app\models\Form;
use app\helpers\CSRF;
use app\helpers\RateLimiter;
use app\helpers\Valitron;

class FormSubmissionController
{
    public function submit($slug)
    {
        // 1. Fetch Form
        // We need to query by slug manually since AR `find` uses ID
        $db = Flight::db();
        $stmt = $db->prepare("SELECT * FROM forms WHERE slug = :slug AND status = 'active'");
        $stmt->bindValue(':slug', $slug);
        $res = $stmt->execute();
        $formData = $res->fetchArray(SQLITE3_ASSOC);

        if (!$formData) {
            Flight::halt(404, json_encode(['error' => 'Form not found or inactive']));
            return;
        }

        $formId = $formData['id'];
        $formName = $formData['name'];
        $fields = json_decode($formData['fields_json'], true);

        // 2. CSRF Check
        // Expect token in header or body
        $data = Flight::request()->data->getData();
        $token = $data['csrf_token'] ?? Flight::request()->header('X-CSRF-Token');

        // Temporarily bypass CSRF for testing if needed, but implementation requires it
        if (!CSRF::validate($token)) {
            Flight::halt(403, json_encode(['error' => 'Invalid security token. Please refresh and try again.']));
            return;
        }

        // 3. Rate Limiting
        $ip = Flight::request()->ip;
        $rateKey = "submission:{$formId}:{$ip}";
        if (!RateLimiter::attempt($rateKey, 5, 60)) {
            Flight::halt(429, json_encode(['error' => 'Too many attempts. Please try again later.']));
            return;
        }

        // 4. Validation
        $rules = [];
        // Map form field definitions to Valitron rules
        foreach ($fields as $field) {
            $fieldName = $field['name'];

            if (!empty($field['required'])) {
                $rules[$fieldName][] = ['required'];
            }

            if ($field['type'] === 'email') {
                $rules[$fieldName][] = ['email'];
            }

            // Add other standard validations based on field props if we add them later (min, max, etc)
        }

        $v = Valitron::make($data, $rules);
        if (!$v->validate()) {
            Flight::halt(400, json_encode([
                'error' => 'Validation failed',
                'fields' => $v->errors()
            ]));
            return;
        }

        // 5. Store Response
        $tableName = 'form_responses_' . (int) $formId;

        // Filter data to only include defined fields (prevent pollution)
        $cleanData = [];
        foreach ($fields as $field) {
            $name = $field['name'];
            if (isset($data[$name])) {
                $cleanData[$name] = $data[$name];
            }
        }

        $json = json_encode($cleanData);
        $userAgent = Flight::request()->user_agent;

        $insertStmt = $db->prepare("INSERT INTO {$tableName} (data_json, ip_address, user_agent) VALUES (:json, :ip, :ua)");
        $insertStmt->bindValue(':json', $json, SQLITE3_TEXT);
        $insertStmt->bindValue(':ip', $ip, SQLITE3_TEXT);
        $insertStmt->bindValue(':ua', $userAgent, SQLITE3_TEXT);

        if ($insertStmt->execute()) {
            Flight::json(['success' => true, 'message' => 'Form submitted successfully']);
        } else {
            Flight::halt(500, json_encode(['error' => 'Failed to transform submission']));
        }
    }
}

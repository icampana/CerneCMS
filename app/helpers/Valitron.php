<?php

namespace app\helpers;

use Valitron\Validator;

class Valitron
{
    /**
     * Create a new validator instance
     */
    public static function make(array $data, array $rules = []): Validator
    {
        $v = new Validator($data);

        // Add custom rules mapping here if needed
        // Valitron uses 'ruleName' => [['field1', 'field2'], 'param'] syntax usually,
        // or mapFieldsRules style.

        $v->mapFieldsRules($rules);

        return $v;
    }
}

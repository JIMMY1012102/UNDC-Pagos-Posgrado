<?php

namespace App\Modules\Usuarios\Validation;

use Illuminate\Validation\Rule;

final class BuyerProfileRules
{
    public static function normalize(array $input): array
    {
        foreach (['first_names', 'last_names', 'document_type', 'document_number', 'phone', 'contact_email'] as $field) {
            if (isset($input[$field]) && is_string($input[$field])) {
                $input[$field] = trim($input[$field]);
                if (in_array($field, ['document_type', 'document_number'], true)) {
                    $input[$field] = strtoupper($input[$field]);
                }
            }
        }

        return $input;
    }

    /** Recibe datos normalizados; los documentos se validan antes de guardarlos. */
    public static function rules(array $input): array
    {
        $format = ($input['document_type'] ?? null) === 'DNI'
            ? '/^[0-9]{8}$/D'
            : '/^[A-Z0-9]{6,20}$/D';

        return [
            'first_names' => ['required', 'string', 'max:160'],
            'last_names' => ['required', 'string', 'max:160'],
            'document_type' => ['required', 'string', Rule::in(['DNI', 'CE', 'PASAPORTE'])],
            'document_number' => ['required', 'string', 'regex:'.$format],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}

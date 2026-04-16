<?php
/**
 * Template Validator
 *
 * Server-side validation of submitted form data against a JSON template schema.
 * Mirrors the client-side React validation logic so rules cannot be bypassed.
 *
 * Usage:
 *   $validator = new \Core\TemplateValidator($schema);
 *   $errors    = $validator->validate($submittedData, $files);
 *   if (!empty($errors)) { ... }
 *
 * @package Core
 */

namespace Core;

class TemplateValidator
{
    private array $schema;

    public function __construct(array $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Validate submitted data against the schema.
     *
     * @param array $data  – flat field_name → value map (string/array values)
     * @param array $files – $_FILES-style array keyed by field_name
     * @return array       – ['field_name' => 'error message'] (empty = valid)
     */
    public function validate(array $data, array $files = []): array
    {
        $errors     = [];
        $visibleIds = $this->computeVisibleFieldIds($data);

        foreach ($this->schema['sections'] ?? [] as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                $fieldId   = $field['id']   ?? '';
                $fieldName = $field['name'] ?? '';

                // Skip fields hidden by conditional logic
                if ($fieldId !== '' && !in_array($fieldId, $visibleIds, true)) {
                    continue;
                }

                $fieldType = $field['type'] ?? 'text';
                $isFile    = $fieldType === 'file';

                if ($isFile) {
                    $error = $this->validateFileField($field, $files[$fieldName] ?? null);
                } else {
                    $value = $data[$fieldName] ?? null;
                    $error = $this->validateField($field, $value);
                }

                if ($error !== null) {
                    $errors[$fieldName] = $error;
                }
            }
        }

        return $errors;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Compute which field IDs are visible given the current form values.
     * Mirrors useConditionalLogic.js logic exactly.
     */
    private function computeVisibleFieldIds(array $data): array
    {
        $allIds = [];
        foreach ($this->schema['sections'] ?? [] as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                $allIds[] = $field['id'] ?? '';
            }
        }
        $allIds = array_filter($allIds);

        $hidden = [];
        foreach ($this->schema['conditional_logic'] ?? [] as $rule) {
            $triggerField = $rule['trigger_field'] ?? '';
            $triggerValue = $data[$triggerField] ?? null;
            $operator     = $rule['operator']     ?? 'equals';
            $ruleValue    = $rule['trigger_value'] ?? null;
            $effect       = $rule['effect']        ?? 'show';
            $targets      = $rule['target_fields'] ?? [];

            $match = $this->evaluateOperator($operator, $triggerValue, $ruleValue);

            foreach ($targets as $fieldId) {
                if ($effect === 'show' && !$match) {
                    $hidden[] = $fieldId;
                } elseif ($effect === 'hide' && $match) {
                    $hidden[] = $fieldId;
                }
            }
        }

        return array_values(array_diff($allIds, array_unique($hidden)));
    }

    private function evaluateOperator(string $operator, mixed $triggerValue, mixed $ruleValue): bool
    {
        return match ($operator) {
            'equals'     => (string) $triggerValue === (string) $ruleValue,
            'not_equals' => (string) $triggerValue !== (string) $ruleValue,
            'in'         => is_array($ruleValue) && in_array($triggerValue, $ruleValue, true),
            'not_in'     => is_array($ruleValue) && !in_array($triggerValue, $ruleValue, true),
            'contains'   => is_string($triggerValue) && is_string($ruleValue) && str_contains($triggerValue, $ruleValue),
            default      => false,
        };
    }

    private function validateField(array $field, mixed $value): ?string
    {
        $label    = $field['label'] ?? ($field['name'] ?? 'Field');
        $required = !empty($field['required']);
        $rules    = $field['validation'] ?? [];
        $type     = $field['type'] ?? 'text';

        // Normalise value
        if (is_string($value)) {
            $value = trim($value);
        }

        $isEmpty = ($value === null || $value === '' || $value === []);

        if ($required && $isEmpty) {
            return "{$label} is required.";
        }
        if ($isEmpty) {
            return null; // optional + empty — nothing more to check
        }

        // String-based validations
        if (in_array($type, ['text', 'textarea', 'select', 'radio'], true)) {
            $strVal = (string) $value;
            if (isset($rules['minLength']) && mb_strlen($strVal) < (int) $rules['minLength']) {
                return "{$label} must be at least {$rules['minLength']} characters.";
            }
            if (isset($rules['maxLength']) && mb_strlen($strVal) > (int) $rules['maxLength']) {
                return "{$label} must not exceed {$rules['maxLength']} characters.";
            }
            if (!empty($rules['pattern']) && !preg_match('/' . $rules['pattern'] . '/', $strVal)) {
                return !empty($rules['patternMessage']) ? $rules['patternMessage'] : "{$label} has an invalid format.";
            }
        }

        // Number validation
        if ($type === 'number') {
            if (!is_numeric($value)) {
                return "{$label} must be a number.";
            }
            $num = (float) $value;
            if (isset($rules['min']) && $num < (float) $rules['min']) {
                return "{$label} must be at least {$rules['min']}.";
            }
            if (isset($rules['max']) && $num > (float) $rules['max']) {
                return "{$label} must be at most {$rules['max']}.";
            }
        }

        // Date validation
        if ($type === 'date' && $value !== '') {
            $d = \DateTime::createFromFormat('Y-m-d', (string) $value);
            if (!$d || $d->format('Y-m-d') !== $value) {
                return "{$label} must be a valid date (YYYY-MM-DD).";
            }
        }

        // Checkbox — at least one must be checked if required (already handled above)

        return null;
    }

    private function validateFileField(array $field, mixed $fileEntry): ?string
    {
        $label    = $field['label'] ?? ($field['name'] ?? 'File');
        $required = !empty($field['required']);
        $rules    = $field['validation'] ?? [];

        $noFile = (
            $fileEntry === null ||
            (isset($fileEntry['error']) && (int) $fileEntry['error'] === UPLOAD_ERR_NO_FILE)
        );

        if ($required && $noFile) {
            return "{$label} is required.";
        }
        if ($noFile) {
            return null;
        }

        if ((int) ($fileEntry['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return "{$label}: file upload failed (error code {$fileEntry['error']}).";
        }

        $mimeType  = mime_content_type($fileEntry['tmp_name'] ?? '') ?: ($fileEntry['type'] ?? '');
        $sizeBytes = (int) ($fileEntry['size'] ?? 0);

        if (!empty($rules['accept']) && is_array($rules['accept'])) {
            $allowed = array_map('strtolower', $rules['accept']);
            if (!in_array(strtolower($mimeType), $allowed, true)) {
                return "{$label}: file type '{$mimeType}' is not allowed.";
            }
        }

        if (!empty($rules['maxSizeMB'])) {
            $maxBytes = (float) $rules['maxSizeMB'] * 1024 * 1024;
            if ($sizeBytes > $maxBytes) {
                return "{$label}: file size must not exceed {$rules['maxSizeMB']} MB.";
            }
        }

        return null;
    }
}

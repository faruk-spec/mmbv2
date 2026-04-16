import { useState, useCallback } from 'react';

/**
 * useFormState — manages field values and client-side validation.
 * @param {object} schema – the JSON template schema
 */
export function useFormState(schema) {
  const [values, setValues] = useState(() => buildDefaults(schema));
  const [errors, setErrors] = useState({});

  const setValue = useCallback((name, value) => {
    setValues(prev => ({ ...prev, [name]: value }));
    setErrors(prev => {
      const next = { ...prev };
      delete next[name];
      return next;
    });
  }, []);

  const validate = useCallback((visibleFieldIds) => {
    const newErrors = {};
    for (const section of schema.sections ?? []) {
      for (const field of section.fields ?? []) {
        const fid  = field.id   ?? '';
        const name = field.name ?? '';
        if (fid !== '' && !visibleFieldIds.has(fid)) continue;
        const error = validateField(field, values[name]);
        if (error) newErrors[name] = error;
      }
    }
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  }, [schema, values]);

  const reset = useCallback(() => {
    setValues(buildDefaults(schema));
    setErrors({});
  }, [schema]);

  return { values, setValue, errors, validate, reset };
}

function buildDefaults(schema) {
  const defaults = {};
  for (const section of schema?.sections ?? []) {
    for (const field of section.fields ?? []) {
      const name = field.name ?? '';
      if (!name) continue;
      const configuredDefault = field.default_value;
      switch (field.type) {
        case 'checkbox':
          defaults[name] = parseBooleanDefault(configuredDefault);
          break;
        case 'number':
          defaults[name] = configuredDefault ?? '';
          break;
        default:
          defaults[name] = configuredDefault ?? '';
      }
    }
  }
  return defaults;
}

function parseBooleanDefault(value) {
  if (typeof value === 'boolean') return value;
  if (typeof value !== 'string') return false;
  return ['true', '1', 'yes', 'on'].includes(value.trim().toLowerCase());
}

function validateField(field, value) {
  const label    = field.label ?? (field.name ?? 'Field');
  const required = !!field.required;
  const rules    = field.validation ?? {};
  const type     = field.type ?? 'text';

  const val = (typeof value === 'string') ? value.trim() : value;
  const isEmpty = val === null || val === '' || val === undefined || val === false;

  if (required && isEmpty) return `${label} is required.`;
  if (isEmpty) return null;

  if (['text', 'textarea', 'select', 'radio'].includes(type)) {
    const str = String(val);
    if (rules.minLength && str.length < rules.minLength)
      return `${label} must be at least ${rules.minLength} characters.`;
    if (rules.maxLength && str.length > rules.maxLength)
      return `${label} must not exceed ${rules.maxLength} characters.`;
    if (rules.pattern && !new RegExp(rules.pattern).test(str))
      return rules.patternMessage ?? `${label} has an invalid format.`;
  }
  if (type === 'number') {
    if (isNaN(Number(val))) return `${label} must be a number.`;
    const num = Number(val);
    if (rules.min !== undefined && num < rules.min) return `${label} must be at least ${rules.min}.`;
    if (rules.max !== undefined && num > rules.max) return `${label} must be at most ${rules.max}.`;
  }
  return null;
}

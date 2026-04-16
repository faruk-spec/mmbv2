import { useMemo } from 'react';

/**
 * useConditionalLogic — compute visible field IDs given the current form values.
 * Mirrors Core\TemplateValidator::computeVisibleFieldIds() in PHP.
 *
 * @param {object} schema  – JSON template schema
 * @param {object} values  – current field values
 * @returns {Set<string>}  – set of visible field IDs
 */
export function useConditionalLogic(schema, values) {
  return useMemo(() => {
    const allIds = new Set(
      (schema?.sections ?? []).flatMap(s => (s.fields ?? []).map(f => f.id).filter(Boolean))
    );
    const hidden = new Set();

    for (const rule of (schema?.conditional_logic ?? [])) {
      const triggerValue = values[rule.trigger_field];
      const match = evaluateOperator(rule.operator, triggerValue, rule.trigger_value);

      for (const fieldId of (rule.target_fields ?? [])) {
        if (rule.effect === 'show' && !match) hidden.add(fieldId);
        if (rule.effect === 'hide' && match)  hidden.add(fieldId);
      }
    }

    return new Set([...allIds].filter(id => !hidden.has(id)));
  }, [schema, values]);
}

function evaluateOperator(operator, triggerValue, ruleValue) {
  switch (operator) {
    case 'equals':     return String(triggerValue) === String(ruleValue);
    case 'not_equals': return String(triggerValue) !== String(ruleValue);
    case 'in':         return Array.isArray(ruleValue) && ruleValue.includes(triggerValue);
    case 'not_in':     return Array.isArray(ruleValue) && !ruleValue.includes(triggerValue);
    case 'contains':   return typeof triggerValue === 'string' && triggerValue.includes(ruleValue);
    default:           return false;
  }
}

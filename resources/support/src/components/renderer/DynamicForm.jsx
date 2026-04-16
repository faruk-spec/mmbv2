import React from 'react';
import { useFormState }        from '../../hooks/useFormState.js';
import { useConditionalLogic } from '../../hooks/useConditionalLogic.js';
import FormSection             from './FormSection.jsx';
import FieldWrapper            from './FieldWrapper.jsx';

/**
 * DynamicForm — reads a JSON schema and renders the form.
 * @prop {object}   schema    – the template schema
 * @prop {function} onSubmit  – called with (values, files) on valid submit
 * @prop {boolean}  [disabled]
 */
export default function DynamicForm({ schema, onSubmit, disabled = false }) {
  const { values, setValue, errors, validate } = useFormState(schema);
  const visibleFields = useConditionalLogic(schema, values);

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validate(visibleFields)) {
      onSubmit(values);
    }
  };

  return (
    <form onSubmit={handleSubmit} noValidate>
      {(schema?.sections ?? []).map(section => {
        const sectionFields = (section.fields ?? []).filter(f => visibleFields.has(f.id));
        if (sectionFields.length === 0) return null;

        return (
          <FormSection key={section.id} section={section}>
            {sectionFields.map(field => (
              <FieldWrapper
                key={field.id}
                field={field}
                value={values[field.name]}
                onChange={val => setValue(field.name, val)}
                error={errors[field.name]}
              />
            ))}
          </FormSection>
        );
      })}

      <button type="submit" className="sp-btn-submit" disabled={disabled}>
        Submit Ticket
      </button>
    </form>
  );
}

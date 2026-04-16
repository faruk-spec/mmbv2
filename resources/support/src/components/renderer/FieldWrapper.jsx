import React from 'react';
import TextField      from './fields/TextField.jsx';
import TextareaField  from './fields/TextareaField.jsx';
import SelectField    from './fields/SelectField.jsx';
import RadioField     from './fields/RadioField.jsx';
import CheckboxField  from './fields/CheckboxField.jsx';
import FileField      from './fields/FileField.jsx';
import DateField      from './fields/DateField.jsx';
import NumberField    from './fields/NumberField.jsx';

const RENDERERS = {
  text:     TextField,
  textarea: TextareaField,
  select:   SelectField,
  radio:    RadioField,
  checkbox: CheckboxField,
  file:     FileField,
  date:     DateField,
  number:   NumberField,
};

export default function FieldWrapper({ field, value, onChange, error }) {
  const Renderer = RENDERERS[field.type] ?? TextField;

  return (
    <div className={`sp-field-wrap${error ? ' sp-field-error' : ''}`}>
      <label className="sp-label" htmlFor={`field_${field.name}`}>
        {field.label ?? field.name}
        {field.required && <span className="sp-required" aria-hidden="true"> *</span>}
      </label>

      {field.help_text && (
        <p className="sp-help-text">{field.help_text}</p>
      )}

      <Renderer field={field} value={value} onChange={onChange} />

      {error && <p className="sp-error-msg">{error}</p>}
    </div>
  );
}

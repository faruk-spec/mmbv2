import React from 'react';

export default function TextareaField({ field, value, onChange }) {
  return (
    <textarea
      className="sp-textarea"
      id={`field_${field.name}`}
      name={field.name}
      value={value ?? ''}
      onChange={e => onChange(e.target.value)}
      placeholder={field.placeholder ?? ''}
      rows={field.rows ?? 4}
      required={field.required}
    />
  );
}

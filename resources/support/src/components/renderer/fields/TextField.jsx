import React from 'react';

export default function TextField({ field, value, onChange }) {
  return (
    <input
      type="text"
      className="sp-input"
      id={`field_${field.name}`}
      name={field.name}
      value={value ?? ''}
      onChange={e => onChange(e.target.value)}
      placeholder={field.placeholder ?? ''}
      maxLength={field.validation?.maxLength}
      required={field.required}
    />
  );
}

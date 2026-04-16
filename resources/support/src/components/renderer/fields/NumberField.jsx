import React from 'react';

export default function NumberField({ field, value, onChange }) {
  const rules = field.validation ?? {};
  return (
    <input
      type="number"
      className="sp-input"
      id={`field_${field.name}`}
      name={field.name}
      value={value ?? ''}
      onChange={e => onChange(e.target.value)}
      min={rules.min}
      max={rules.max}
      placeholder={field.placeholder ?? ''}
      required={field.required}
    />
  );
}

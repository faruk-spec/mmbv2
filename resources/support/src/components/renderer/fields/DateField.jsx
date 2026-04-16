import React from 'react';

export default function DateField({ field, value, onChange }) {
  return (
    <input
      type="date"
      className="sp-input"
      id={`field_${field.name}`}
      name={field.name}
      value={value ?? ''}
      onChange={e => onChange(e.target.value)}
      required={field.required}
    />
  );
}

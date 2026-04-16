import React from 'react';

export default function SelectField({ field, value, onChange }) {
  return (
    <select
      className="sp-select"
      id={`field_${field.name}`}
      name={field.name}
      value={value ?? ''}
      onChange={e => onChange(e.target.value)}
      required={field.required}
    >
      <option value="">— Select —</option>
      {(field.options ?? []).map(opt => (
        <option key={opt.value} value={opt.value}>{opt.label}</option>
      ))}
    </select>
  );
}

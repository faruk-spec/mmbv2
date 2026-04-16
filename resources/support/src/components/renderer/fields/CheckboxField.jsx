import React from 'react';

export default function CheckboxField({ field, value, onChange }) {
  return (
    <label className="sp-checkbox-item">
      <input
        type="checkbox"
        name={field.name}
        checked={!!value}
        onChange={e => onChange(e.target.checked)}
      />
      <span>{field.checkboxLabel ?? field.label ?? field.name}</span>
    </label>
  );
}

import React from 'react';

export default function RadioField({ field, value, onChange }) {
  return (
    <div className="sp-radio-group">
      {(field.options ?? []).map(opt => (
        <label key={opt.value} className="sp-radio-item">
          <input
            type="radio"
            name={field.name}
            value={opt.value}
            checked={value === opt.value}
            onChange={() => onChange(opt.value)}
            required={field.required && !value}
          />
          <span>{opt.label}</span>
        </label>
      ))}
    </div>
  );
}

import React from 'react';

export default function FileField({ field, onChange }) {
  const rules  = field.validation ?? {};
  const accept = Array.isArray(rules.accept) ? rules.accept.join(',') : undefined;

  return (
    <input
      type="file"
      className="sp-file"
      id={`field_${field.name}`}
      name={field.name}
      accept={accept}
      required={field.required}
      onChange={e => onChange(e.target.files?.[0] ?? null)}
    />
  );
}

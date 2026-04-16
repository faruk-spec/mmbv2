import React from 'react';

export default function FieldEditor({ field, onUpdate, onClose }) {
  if (!field) {
    return (
      <div className="bldr-editor-empty">
        <i className="fas fa-mouse-pointer" style={{ fontSize: '1.6rem', color: 'var(--text-secondary,#8892a6)', display: 'block', marginBottom: 8 }}></i>
        Select a field to edit its properties
      </div>
    );
  }

  const update = (key, val) => onUpdate(field.id, { [key]: val });

  const updateValidation = (key, val) => {
    onUpdate(field.id, { validation: { ...(field.validation ?? {}), [key]: val === '' ? undefined : val } });
  };

  const updateOption = (idx, key, val) => {
    const opts = [...(field.options ?? [])];
    opts[idx] = { ...opts[idx], [key]: val };
    onUpdate(field.id, { options: opts });
  };

  const addOption = () => {
    const opts  = [...(field.options ?? [])];
    const n     = opts.length + 1;
    opts.push({ value: `option_${n}`, label: `Option ${n}` });
    onUpdate(field.id, { options: opts });
  };

  const removeOption = (idx) => {
    const opts = (field.options ?? []).filter((_, i) => i !== idx);
    onUpdate(field.id, { options: opts });
  };

  const hasOptions = ['select', 'radio', 'checkbox'].includes(field.type);

  return (
    <div className="bldr-editor">
      <div className="bldr-editor-head">
        <span style={{ fontWeight: 600, fontSize: '.9rem' }}>{field.type} field</span>
        <button type="button" onClick={onClose} className="bldr-editor-close"><i className="fas fa-times"></i></button>
      </div>

      <div className="bldr-editor-body">

        <EditorRow label="Label">
          <input className="bldr-ei" value={field.label ?? ''} onChange={e => update('label', e.target.value)} placeholder="Field label" />
        </EditorRow>

        <EditorRow label="Field Name (key)">
          <input className="bldr-ei" value={field.name ?? ''} onChange={e => update('name', e.target.value.replace(/[^a-z0-9_]/gi, '_'))} placeholder="field_name" />
        </EditorRow>

        <EditorRow label="Placeholder">
          <input className="bldr-ei" value={field.placeholder ?? ''} onChange={e => update('placeholder', e.target.value)} placeholder="Optional placeholder..." />
        </EditorRow>

        <EditorRow label="Help Text">
          <input className="bldr-ei" value={field.help_text ?? ''} onChange={e => update('help_text', e.target.value)} placeholder="Optional hint..." />
        </EditorRow>

        <EditorRow label="Default Value">
          <input className="bldr-ei" value={field.default_value ?? ''} onChange={e => update('default_value', e.target.value)} placeholder="Optional initial value..." />
        </EditorRow>

        <EditorRow label="Width">
          <select className="bldr-ei" value={field.width ?? 'full'} onChange={e => update('width', e.target.value)}>
            <option value="full">Full Width</option>
            <option value="half">Half Width</option>
            <option value="third">Third Width</option>
          </select>
        </EditorRow>

        <EditorRow label="Required">
          <label className="bldr-toggle">
            <input type="checkbox" checked={!!field.required} onChange={e => update('required', e.target.checked)} />
            <span className="bldr-toggle-slider"></span>
          </label>
        </EditorRow>

        {/* Validation rules */}
        {['text', 'textarea'].includes(field.type) && (
          <>
            <EditorRow label="Min Length">
              <input type="number" className="bldr-ei" min="0" value={field.validation?.minLength ?? ''} onChange={e => updateValidation('minLength', e.target.value === '' ? undefined : +e.target.value)} />
            </EditorRow>
            <EditorRow label="Max Length">
              <input type="number" className="bldr-ei" min="0" value={field.validation?.maxLength ?? ''} onChange={e => updateValidation('maxLength', e.target.value === '' ? undefined : +e.target.value)} />
            </EditorRow>
          </>
        )}

        {field.type === 'number' && (
          <>
            <EditorRow label="Min Value">
              <input type="number" className="bldr-ei" value={field.validation?.min ?? ''} onChange={e => updateValidation('min', e.target.value === '' ? undefined : +e.target.value)} />
            </EditorRow>
            <EditorRow label="Max Value">
              <input type="number" className="bldr-ei" value={field.validation?.max ?? ''} onChange={e => updateValidation('max', e.target.value === '' ? undefined : +e.target.value)} />
            </EditorRow>
          </>
        )}

        {field.type === 'file' && (
          <>
            <EditorRow label="Accepted MIME Types">
              <input className="bldr-ei" value={(field.validation?.accept ?? []).join(', ')} onChange={e => updateValidation('accept', e.target.value.split(',').map(s => s.trim()).filter(Boolean))} placeholder="image/png, image/jpeg" />
            </EditorRow>
            <EditorRow label="Max Size (MB)">
              <input type="number" className="bldr-ei" min="0.1" step="0.1" value={field.validation?.maxSizeMB ?? ''} onChange={e => updateValidation('maxSizeMB', e.target.value === '' ? undefined : +e.target.value)} />
            </EditorRow>
          </>
        )}

        {/* Options editor */}
        {hasOptions && (
          <div className="bldr-editor-section">
            <div className="bldr-editor-section-title">Options</div>
            {(field.options ?? []).map((opt, i) => (
              <div key={i} className="bldr-option-row">
                <input className="bldr-ei bldr-ei-sm" value={opt.value} onChange={e => updateOption(i, 'value', e.target.value)} placeholder="value" />
                <input className="bldr-ei bldr-ei-sm" value={opt.label} onChange={e => updateOption(i, 'label', e.target.value)} placeholder="Label" />
                <button type="button" onClick={() => removeOption(i)} className="bldr-opt-rm"><i className="fas fa-times"></i></button>
              </div>
            ))}
            <button type="button" className="bldr-add-opt" onClick={addOption}>
              <i className="fas fa-plus"></i> Add Option
            </button>
          </div>
        )}

      </div>
    </div>
  );
}

function EditorRow({ label, children }) {
  return (
    <div className="bldr-editor-row">
      <label className="bldr-editor-label">{label}</label>
      <div>{children}</div>
    </div>
  );
}

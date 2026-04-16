import React, { useState } from 'react';
import {
  DndContext, closestCenter, PointerSensor,
  useSensor, useSensors, DragOverlay,
} from '@dnd-kit/core';
import {
  SortableContext, verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import FieldPalette            from './FieldPalette.jsx';
import SectionBlock            from './SectionBlock.jsx';
import FieldEditor             from './FieldEditor.jsx';
import ConditionalLogicEditor  from './ConditionalLogicEditor.jsx';
import { useBuilderState }     from '../../hooks/useBuilderState.js';

export default function FormBuilder({ initialSchema, onSave, saving }) {
  const {
    schema,
    selectedFieldId, setSelectedFieldId,
    addSection, updateSection, removeSection,
    addField, updateField, removeField,
    moveField, moveSections,
    updateConditionalLogic,
    updateUiSettings,
  } = useBuilderState(initialSchema);

  const [activeId,   setActiveId]   = useState(null);
  const [activeTab,  setActiveTab]  = useState('build'); // 'build' | 'logic' | 'customize' | 'preview'
  const ui = schema?.ui ?? {};
  const previewSectionStyle = getPreviewSectionStyle(ui.section_style);

  const sensors = useSensors(
    useSensor(PointerSensor, { activationConstraint: { distance: 5 } })
  );

  // Find the active field/section for the drag overlay
  const activeField = activeId
    ? (schema.sections ?? []).flatMap(s => s.fields).find(f => f.id === activeId)
    : null;
  const activeSection = activeId
    ? (schema.sections ?? []).find(s => s.id === activeId)
    : null;

  function handleDragStart({ active }) {
    setActiveId(active.id);
  }

  function handleDragEnd({ active, over }) {
    setActiveId(null);
    if (!over || active.id === over.id) return;

    const isPalette = active.data.current?.isPalette;
    if (isPalette) {
      // Dropped from palette: add to target section
      const sectionId = over.data.current?.sectionId ?? over.id;
      const section   = (schema.sections ?? []).find(s => s.id === sectionId);
      if (section) {
        addField(section.id, active.data.current.fieldType);
      }
      return;
    }

    const isSection = active.data.current?.type === 'section';
    const isField   = active.data.current?.type === 'field';

    if (isSection) {
      moveSections(active.id, over.id);
    } else if (isField) {
      moveField(active.id, over.id);
    }
  }

  // Locate selected field object
  const selectedField = selectedFieldId
    ? (schema.sections ?? []).flatMap(s => s.fields).find(f => f.id === selectedFieldId)
    : null;

  return (
    <DndContext
      sensors={sensors}
      collisionDetection={closestCenter}
      onDragStart={handleDragStart}
      onDragEnd={handleDragEnd}
    >
    <div style={{ display: 'flex', gap: 16, alignItems: 'flex-start' }}>
      <style>{BUILDER_STYLES}</style>

      {/* Left panel: palette */}
      <div style={{ width: 160, flexShrink: 0 }}>
        <FieldPalette />
        <button
          type="button"
          className="bldr-add-section-btn"
          onClick={addSection}
        >
          <i className="fas fa-plus"></i> Add Section
        </button>
      </div>

      {/* Centre: canvas */}
      <div style={{ flex: 1, minWidth: 0 }}>
        {/* Tabs */}
        <div className="bldr-tabs">
          {['build', 'logic', 'customize', 'preview'].map(tab => (
            <button
              key={tab}
              type="button"
              className={`bldr-tab${activeTab === tab ? ' active' : ''}`}
              onClick={() => setActiveTab(tab)}
            >
              {tab === 'build' ? '🔨 Build' : tab === 'logic' ? '⚡ Conditions' : tab === 'customize' ? '🎨 Customize' : '👁 Preview'}
            </button>
          ))}
          <button
            type="button"
            className="bldr-save-btn"
            onClick={() => onSave(schema)}
            disabled={saving}
          >
            {saving ? 'Saving…' : '💾 Save Template'}
          </button>
        </div>

        {activeTab === 'build' && (
          <SortableContext
            items={(schema.sections ?? []).map(s => s.id)}
            strategy={verticalListSortingStrategy}
          >
            {(schema.sections ?? []).length === 0 && (
              <div className="bldr-empty-canvas">
                No sections yet. Click "Add Section" to start.
              </div>
            )}
            {(schema.sections ?? []).map(section => (
              <SectionBlock
                key={section.id}
                section={section}
                onUpdateSection={updateSection}
                onRemoveSection={removeSection}
                onRemoveField={removeField}
                onAddField={addField}
                selectedFieldId={selectedFieldId}
                onSelectField={setSelectedFieldId}
              />
            ))}
          </SortableContext>
        )}

        {activeTab === 'logic' && (
          <ConditionalLogicEditor
            schema={schema}
            rules={schema.conditional_logic ?? []}
            onChange={updateConditionalLogic}
          />
        )}

        {activeTab === 'customize' && (
          <FormCustomizationPanel ui={ui} onChange={updateUiSettings} />
        )}

        {activeTab === 'preview' && (
          <FormPreview schema={schema} ui={ui} />
        )}
      </div>

      {/* Right panel: field editor */}
      <div style={{ width: 280, flexShrink: 0 }}>
        <FieldEditor
          field={selectedField}
          onUpdate={updateField}
          onClose={() => setSelectedFieldId(null)}
        />
      </div>
    </div>

    {/* DragOverlay must be inside DndContext but outside the tab conditional */}
    <DragOverlay>
      {activeField && (
        <div className="bldr-field-card drag-overlay">
          <i className="fas fa-grip-vertical"></i>
          <span>{activeField.label || activeField.name}</span>
        </div>
      )}
      {!activeField && activeSection && (
        <div className="bldr-section drag-overlay">
          <strong>{activeSection.title}</strong>
        </div>
      )}
      {!activeField && !activeSection && activeId && activeId.startsWith('palette__') && (
        <div className="bldr-palette-item drag-overlay">
          <i className="fas fa-plus"></i>
          <span>{activeId.replace('palette__', '')}</span>
        </div>
      )}
    </DragOverlay>
    </DndContext>
  );
}

function getPreviewSectionStyle(sectionStyle) {
  const minimal = sectionStyle === 'minimal';
  return {
    marginBottom: 20,
    border: minimal ? 'none' : '1px solid var(--border-color,rgba(255,255,255,.08))',
    borderRadius: 10,
    overflow: 'hidden',
    background: minimal ? 'transparent' : undefined,
  };
}

/* ── Edit-Incident style form preview (Image 2 reference) ─── */
function FormPreview({ schema = {}, ui = {} }) {
  const accentColor = ui.accent_color || 'var(--cyan,#00f0ff)';
  const sections    = schema.sections ?? [];

  return (
    <div className="fp-root">
      {/* Header bar */}
      <div className="fp-header">
        <div className="fp-header-left">
          <button type="button" className="fp-back-btn">
            <i className="fas fa-arrow-left"></i>
          </button>
          <span className="fp-header-title">{ui.form_title || 'Create Support Ticket'}</span>
        </div>
        <div className="fp-header-right">
          <span className="fp-template-label">Select Template</span>
          <div className="fp-template-sel">
            <span className="fp-template-icon" style={{ background: accentColor }}><i className="fas fa-bolt"></i></span>
            <span className="fp-template-name">{schema.sections?.[0]?.title || 'Default'}</span>
            <i className="fas fa-chevron-down fp-template-chev"></i>
          </div>
        </div>
      </div>

      <div className="fp-divider"></div>

      {/* Body */}
      <div className="fp-body">
        {sections.length === 0 && (
          <div className="fp-empty">No sections yet — add sections in the Build tab.</div>
        )}

        {sections.map((section, si) => {
          const fields = section.fields ?? [];
          // Group fields into rows based on width
          const rows = [];
          let i = 0;
          while (i < fields.length) {
            const f = fields[i];
            const w = f.width ?? 'full';
            if (w === 'full') {
              rows.push([f]);
              i++;
            } else if (w === 'half') {
              const next = fields[i + 1];
              if (next && (next.width === 'half' || next.width === 'third')) {
                rows.push([f, next]);
                i += 2;
              } else {
                rows.push([f]);
                i++;
              }
            } else if (w === 'third') {
              const pair = fields.slice(i, i + 3).filter(x => x.width === 'third');
              rows.push(pair.length >= 2 ? fields.slice(i, i + Math.min(pair.length, 3)) : [f]);
              i += (pair.length >= 2 ? Math.min(pair.length, 3) : 1);
            } else {
              rows.push([f]);
              i++;
            }
          }

          return (
            <div key={section.id} className="fp-section">
              {/* Section header */}
              <div className="fp-section-header">
                <span className="fp-section-title">{section.title || `Section ${si + 1}`}</span>
                {section.description && (
                  <span className="fp-section-desc">{section.description}</span>
                )}
              </div>
              <div className="fp-section-divider"></div>

              {/* Fields in rows */}
              {rows.map((row, ri) => (
                <div key={ri} className="fp-row">
                  {row.map(f => {
                    const colClass = f.width === 'third' ? 'fp-col-third' : f.width === 'half' ? 'fp-col-half' : 'fp-col-full';
                    return (
                      <div key={f.id} className={`fp-field-col ${colClass}`}>
                        <label className="fp-label">
                          {f.required && <span className="fp-required">*</span>}
                          {f.label ?? f.name}
                        </label>
                        <PreviewFieldInput field={f} accentColor={accentColor} />
                        {f.help_text && (
                          <div className="fp-help">{f.help_text}</div>
                        )}
                      </div>
                    );
                  })}
                </div>
              ))}

              {fields.length === 0 && (
                <div className="fp-empty-section">No fields in this section</div>
              )}
            </div>
          );
        })}

        {/* Submit button */}
        {sections.length > 0 && (
          <div className="fp-footer">
            <button type="button" className="fp-submit-btn" style={{ background: accentColor }}>
              {ui.submit_label || 'Submit Ticket'}
            </button>
            <button type="button" className="fp-cancel-btn">Cancel</button>
          </div>
        )}
      </div>
    </div>
  );
}

function PreviewFieldInput({ field, accentColor }) {
  const t = field.type;
  if (t === 'textarea') {
    return (
      <textarea
        className="fp-input fp-textarea"
        readOnly
        placeholder={field.placeholder || ''}
        defaultValue={field.default_value || ''}
        rows={3}
      />
    );
  }
  if (t === 'select') {
    const opts = field.options ?? [];
    return (
      <div className="fp-select-wrap">
        <select className="fp-input" disabled>
          {opts.length === 0 && <option>{field.placeholder || '-- Select --'}</option>}
          {opts.map((o, i) => (
            <option key={i}>{typeof o === 'string' ? o : o.label}</option>
          ))}
        </select>
        <i className="fas fa-chevron-down fp-sel-arrow"></i>
      </div>
    );
  }
  if (t === 'checkbox') {
    return (
      <label className="fp-checkbox-wrap">
        <input type="checkbox" readOnly checked={false} style={{ accentColor }} />
        <span>{field.label ?? field.name}</span>
      </label>
    );
  }
  if (t === 'radio') {
    const opts = field.options ?? ['Option 1', 'Option 2'];
    return (
      <div className="fp-radio-group">
        {opts.map((o, i) => (
          <label key={i} className="fp-radio-label">
            <input type="radio" readOnly name={`prev_${field.id}`} style={{ accentColor }} />
            <span>{typeof o === 'string' ? o : o.label}</span>
          </label>
        ))}
      </div>
    );
  }
  if (t === 'date') {
    return <input type="date" className="fp-input" readOnly defaultValue={field.default_value || ''} />;
  }
  if (t === 'number') {
    return <input type="number" className="fp-input" readOnly placeholder={field.placeholder || ''} defaultValue={field.default_value || ''} />;
  }
  if (t === 'file') {
    return (
      <div className="fp-file-btn">
        <i className="fas fa-upload"></i> Choose File
      </div>
    );
  }
  // default: text
  return <input type="text" className="fp-input" readOnly placeholder={field.placeholder || ''} defaultValue={field.default_value || ''} />;
}

function FormCustomizationPanel({ ui = {}, onChange }) {
  const set = (key, value) => onChange({ [key]: value });

  return (
    <div className="bldr-customize">
      <div className="bldr-custom-grid">
        <div className="bldr-editor-row">
          <label className="bldr-editor-label">Form Title</label>
          <input className="bldr-ei" value={ui.form_title ?? ''} onChange={(e) => set('form_title', e.target.value)} />
        </div>
        <div className="bldr-editor-row">
          <label className="bldr-editor-label">Submit Button Label</label>
          <input className="bldr-ei" value={ui.submit_label ?? ''} onChange={(e) => set('submit_label', e.target.value)} />
        </div>
        <div className="bldr-editor-row" style={{ gridColumn: '1 / -1' }}>
          <label className="bldr-editor-label">Form Subtitle</label>
          <input className="bldr-ei" value={ui.form_subtitle ?? ''} onChange={(e) => set('form_subtitle', e.target.value)} />
        </div>
        <div className="bldr-editor-row">
          <label className="bldr-editor-label">Success Title</label>
          <input className="bldr-ei" value={ui.success_title ?? ''} onChange={(e) => set('success_title', e.target.value)} />
        </div>
        <div className="bldr-editor-row">
          <label className="bldr-editor-label">Success Message</label>
          <input className="bldr-ei" value={ui.success_message ?? ''} onChange={(e) => set('success_message', e.target.value)} />
        </div>
        <div className="bldr-editor-row">
          <label className="bldr-editor-label">Accent Color</label>
          <input className="bldr-ei" type="color" value={ui.accent_color ?? '#00f0ff'} onChange={(e) => set('accent_color', e.target.value)} />
        </div>
        <div className="bldr-editor-row">
          <label className="bldr-editor-label">Section Style</label>
          <select className="bldr-ei" value={ui.section_style ?? 'card'} onChange={(e) => set('section_style', e.target.value)}>
            <option value="card">Card</option>
            <option value="minimal">Minimal</option>
          </select>
        </div>
        <div className="bldr-editor-row">
          <label className="bldr-editor-label">Compact Mode</label>
          <label className="bldr-toggle">
            <input type="checkbox" checked={!!ui.compact_mode} onChange={(e) => set('compact_mode', e.target.checked)} />
            <span className="bldr-toggle-slider"></span>
          </label>
        </div>
      </div>
    </div>
  );
}

const BUILDER_STYLES = `
/* Palette */
.bldr-palette { background: var(--bg-card,#0f0f18); border: 1px solid var(--border-color,rgba(255,255,255,.08)); border-radius: 10px; overflow: hidden; margin-bottom: 10px; }
.bldr-palette-title { padding: 10px 12px; font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--text-secondary,#8892a6); letter-spacing: .05em; border-bottom: 1px solid var(--border-color,rgba(255,255,255,.06)); }
.bldr-palette-list { padding: 8px; display: flex; flex-direction: column; gap: 4px; }
.bldr-palette-item { display: flex; align-items: center; gap: 8px; padding: 7px 10px; border-radius: 7px; border: 1px solid var(--border-color,rgba(255,255,255,.06)); background: var(--bg-secondary,rgba(255,255,255,.03)); color: var(--text-primary,#e8eefc); font-size: .8rem; cursor: grab; user-select: none; }
.bldr-palette-item:hover { background: var(--hover-bg,rgba(0,240,255,.06)); border-color: color-mix(in srgb,var(--cyan,#00f0ff) 30%,transparent); }
.bldr-palette-item i { width: 14px; color: var(--cyan,#00f0ff); }
.bldr-add-section-btn { width: 100%; padding: 8px; border: 1px dashed var(--border-color,rgba(255,255,255,.15)); background: transparent; color: var(--text-secondary,#8892a6); border-radius: 8px; font-size: .8rem; cursor: pointer; }
.bldr-add-section-btn:hover { border-color: var(--cyan,#00f0ff); color: var(--cyan,#00f0ff); }

/* Tabs */
.bldr-tabs { display: flex; gap: 6px; margin-bottom: 12px; align-items: center; flex-wrap: wrap; }
.bldr-tab { padding: 6px 14px; border-radius: 7px; border: 1px solid var(--border-color,rgba(255,255,255,.1)); background: var(--bg-card,rgba(255,255,255,.03)); color: var(--text-secondary,#8892a6); font-size: .8rem; cursor: pointer; }
.bldr-tab.active { background: color-mix(in srgb,var(--cyan,#00f0ff) 8%,transparent); border-color: color-mix(in srgb,var(--cyan,#00f0ff) 30%,transparent); color: var(--cyan,#00f0ff); }
.bldr-save-btn { margin-left: auto; padding: 7px 16px; background: var(--cyan,#00f0ff); color: var(--bg-primary,#0a0a14); border: none; border-radius: 7px; font-size: .82rem; font-weight: 700; cursor: pointer; }
.bldr-save-btn:hover { opacity: .9; }
.bldr-save-btn:disabled { opacity: .5; cursor: not-allowed; }

/* Canvas */
.bldr-empty-canvas { border: 2px dashed var(--border-color,rgba(255,255,255,.08)); border-radius: 12px; padding: 40px; text-align: center; color: var(--text-secondary,#8892a6); font-size: .85rem; }

/* Section */
.bldr-section { border: 1px solid var(--border-color,rgba(255,255,255,.08)); border-radius: 12px; overflow: hidden; margin-bottom: 12px; background: var(--bg-card,rgba(255,255,255,.02)); }
.bldr-section.drop-over { border-color: color-mix(in srgb,var(--cyan,#00f0ff) 40%,transparent); background: color-mix(in srgb,var(--cyan,#00f0ff) 4%,transparent); }
.bldr-section-head { padding: 10px 12px; background: var(--bg-secondary,rgba(255,255,255,.04)); border-bottom: 1px solid var(--border-color,rgba(255,255,255,.06)); display: flex; align-items: center; gap: 8px; }
.bldr-section-drag { color: var(--text-secondary,#8892a6); cursor: grab; font-size: .8rem; }
.bldr-section-title-input { flex: 1; background: transparent; border: none; outline: none; color: var(--text-primary,#e8eefc); font-size: .88rem; font-weight: 600; }
.bldr-section-collapse-toggle { display: flex; align-items: center; gap: 5px; font-size: .72rem; color: var(--text-secondary,#8892a6); cursor: pointer; white-space: nowrap; }
.bldr-section-rm { background: none; border: none; color: var(--text-secondary,#8892a6); cursor: pointer; font-size: .8rem; padding: 3px; }
.bldr-section-rm:hover { color: var(--red,#ef4444); }
.bldr-section-fields { padding: 10px; min-height: 48px; }
.bldr-drop-hint { border: 2px dashed var(--border-color,rgba(255,255,255,.07)); border-radius: 8px; padding: 14px; text-align: center; color: var(--text-secondary,#8892a6); font-size: .78rem; }

/* Field card */
.bldr-field-card { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border: 1px solid var(--border-color,rgba(255,255,255,.08)); border-radius: 8px; margin-bottom: 6px; background: var(--bg-card,rgba(255,255,255,.03)); cursor: pointer; user-select: none; }
.bldr-field-card:hover { border-color: color-mix(in srgb,var(--border-color,rgba(255,255,255,.1)) 150%,transparent); background: var(--hover-bg,rgba(255,255,255,.05)); }
.bldr-field-card.selected { border-color: color-mix(in srgb,var(--cyan,#00f0ff) 35%,transparent); background: color-mix(in srgb,var(--cyan,#00f0ff) 5%,transparent); }
.bldr-field-card.drag-overlay { box-shadow: 0 8px 20px rgba(0,0,0,.4); }
.bldr-field-drag { color: var(--text-secondary,#8892a6); cursor: grab; font-size: .8rem; flex-shrink: 0; }
.bldr-field-type-icon { width: 20px; text-align: center; color: var(--cyan,#00f0ff); font-size: .8rem; flex-shrink: 0; }
.bldr-field-info { flex: 1; min-width: 0; }
.bldr-field-label { font-size: .83rem; font-weight: 500; color: var(--text-primary,#e8eefc); display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.bldr-field-meta { font-size: .7rem; color: var(--text-secondary,#8892a6); }
.bldr-field-rm { background: none; border: none; color: var(--text-secondary,#8892a6); cursor: pointer; font-size: .78rem; padding: 3px; flex-shrink: 0; }
.bldr-field-rm:hover { color: var(--red,#ef4444); }

/* Editor panel */
.bldr-editor { background: var(--bg-card,rgba(255,255,255,.03)); border: 1px solid var(--border-color,rgba(255,255,255,.08)); border-radius: 12px; overflow: hidden; }
.bldr-editor-empty { padding: 30px 16px; text-align: center; font-size: .82rem; color: var(--text-secondary,#8892a6); }
.bldr-editor-head { padding: 10px 14px; border-bottom: 1px solid var(--border-color,rgba(255,255,255,.06)); display: flex; align-items: center; justify-content: space-between; }
.bldr-editor-close { background: none; border: none; color: var(--text-secondary,#8892a6); cursor: pointer; }
.bldr-editor-close:hover { color: var(--red,#ef4444); }
.bldr-editor-body { padding: 12px 14px; max-height: 70vh; overflow-y: auto; }
.bldr-editor-row { margin-bottom: 10px; }
.bldr-editor-label { font-size: .72rem; font-weight: 600; color: var(--text-secondary,#8892a6); margin-bottom: 4px; display: block; }
.bldr-ei { width: 100%; padding: 6px 9px; border: 1px solid var(--border-color,rgba(255,255,255,.1)); border-radius: 6px; background: var(--bg-secondary,rgba(255,255,255,.04)); color: var(--text-primary,#e8eefc); font-size: .82rem; outline: none; box-sizing: border-box; }
.bldr-ei:focus { border-color: color-mix(in srgb,var(--cyan,#00f0ff) 35%,transparent); }
.bldr-ei-sm { flex: 1; }
.bldr-editor-section { margin-top: 14px; }
.bldr-editor-section-title { font-size: .72rem; font-weight: 700; text-transform: uppercase; color: var(--text-secondary,#8892a6); letter-spacing: .05em; margin-bottom: 8px; }
.bldr-option-row { display: flex; gap: 6px; margin-bottom: 6px; align-items: center; }
.bldr-opt-rm { background: none; border: none; color: var(--text-secondary,#8892a6); cursor: pointer; font-size: .78rem; flex-shrink: 0; }
.bldr-opt-rm:hover { color: var(--red,#ef4444); }
.bldr-add-opt { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; background: color-mix(in srgb,var(--cyan,#0ea5e9) 10%,transparent); border: 1px solid color-mix(in srgb,var(--cyan,#0ea5e9) 25%,transparent); color: var(--cyan,#0ea5e9); border-radius: 6px; font-size: .75rem; cursor: pointer; }
.bldr-toggle { position: relative; display: inline-block; width: 36px; height: 20px; }
.bldr-toggle input { opacity: 0; width: 0; height: 0; }
.bldr-toggle-slider { position: absolute; inset: 0; background: var(--border-color,rgba(255,255,255,.1)); border-radius: 20px; cursor: pointer; transition: .2s; }
.bldr-toggle input:checked + .bldr-toggle-slider { background: var(--cyan,#00f0ff); }
.bldr-toggle-slider:before { content: ""; position: absolute; height: 14px; width: 14px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: .2s; }
.bldr-toggle input:checked + .bldr-toggle-slider:before { transform: translateX(16px); }

/* Conditional logic */
.cle-root { background: var(--bg-card,rgba(255,255,255,.03)); border: 1px solid var(--border-color,rgba(255,255,255,.08)); border-radius: 12px; overflow: hidden; }
.cle-header { padding: 12px 16px; border-bottom: 1px solid var(--border-color,rgba(255,255,255,.06)); display: flex; align-items: center; justify-content: space-between; font-size: .88rem; font-weight: 600; color: var(--text-primary,#e8eefc); }
.cle-add-btn { padding: 5px 12px; background: color-mix(in srgb,var(--cyan,#00f0ff) 10%,transparent); border: 1px solid color-mix(in srgb,var(--cyan,#00f0ff) 25%,transparent); color: var(--cyan,#00f0ff); border-radius: 6px; font-size: .77rem; cursor: pointer; }
.cle-empty { padding: 20px 16px; color: var(--text-secondary,#8892a6); font-size: .82rem; }
.cle-rule { padding: 14px 16px; border-bottom: 1px solid var(--border-color,rgba(255,255,255,.06)); }
.cle-rule-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap; }
.cle-rule-label { font-size: .72rem; color: var(--text-secondary,#8892a6); width: 90px; flex-shrink: 0; }
.cle-sel { flex: 1; min-width: 100px; padding: 5px 8px; border: 1px solid var(--border-color,rgba(255,255,255,.1)); border-radius: 6px; background: var(--bg-secondary,rgba(255,255,255,.04)); color: var(--text-primary,#e8eefc); font-size: .8rem; outline: none; }
.cle-inp { flex: 1; min-width: 100px; padding: 5px 8px; border: 1px solid var(--border-color,rgba(255,255,255,.1)); border-radius: 6px; background: var(--bg-secondary,rgba(255,255,255,.04)); color: var(--text-primary,#e8eefc); font-size: .8rem; outline: none; }
.cle-rm-btn { padding: 4px 10px; background: color-mix(in srgb,var(--red,#ef4444) 8%,transparent); border: 1px solid color-mix(in srgb,var(--red,#ef4444) 20%,transparent); color: var(--red,#ef4444); border-radius: 6px; font-size: .75rem; cursor: pointer; }

/* Preview */
.bldr-preview { }
.bldr-customize { border: 1px solid var(--border-color,rgba(255,255,255,.08)); border-radius: 12px; padding: 14px; background: var(--bg-card,rgba(255,255,255,.02)); }
.bldr-custom-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 10px; }
@media (max-width: 900px) { .bldr-custom-grid { grid-template-columns: 1fr; } }

/* ── FormPreview — Edit-Incident style (Image 2) ─────────── */
.fp-root { border: 1px solid var(--border-color,rgba(255,255,255,.09)); border-radius: 12px; overflow: hidden; background: var(--bg-card,#0f0f18); }
.fp-header { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; background: var(--bg-secondary,rgba(255,255,255,.03)); }
.fp-header-left { display: flex; align-items: center; gap: 10px; }
.fp-back-btn { width: 28px; height: 28px; border: 1px solid var(--border-color,rgba(255,255,255,.12)); border-radius: 6px; background: transparent; color: var(--text-secondary,#8892a6); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .75rem; }
.fp-header-title { font-size: 1rem; font-weight: 700; color: var(--text-primary,#e8eefc); }
.fp-header-right { display: flex; align-items: center; gap: 8px; }
.fp-template-label { font-size: .76rem; color: var(--text-secondary,#8892a6); }
.fp-template-sel { display: flex; align-items: center; gap: 7px; border: 1px solid var(--border-color,rgba(255,255,255,.12)); border-radius: 7px; padding: 5px 12px; background: var(--bg-secondary,rgba(255,255,255,.03)); cursor: default; }
.fp-template-icon { width: 20px; height: 20px; border-radius: 5px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .7rem; }
.fp-template-name { font-size: .8rem; color: var(--text-primary,#e8eefc); font-weight: 500; }
.fp-template-chev { font-size: .65rem; color: var(--text-secondary,#8892a6); }
.fp-divider { height: 1px; background: var(--border-color,rgba(255,255,255,.08)); }
.fp-body { padding: 18px 20px; }
.fp-empty { padding: 30px; text-align: center; color: var(--text-secondary,#8892a6); font-size: .83rem; }
.fp-section { margin-bottom: 24px; }
.fp-section-header { margin-bottom: 4px; }
.fp-section-title { font-size: .9rem; font-weight: 700; color: var(--text-primary,#e8eefc); }
.fp-section-desc { display: block; font-size: .76rem; color: var(--text-secondary,#8892a6); margin-top: 2px; }
.fp-section-divider { height: 1px; background: var(--border-color,rgba(255,255,255,.07)); margin: 8px 0 14px; }
.fp-row { display: flex; gap: 14px; margin-bottom: 12px; flex-wrap: wrap; }
.fp-col-full  { flex: 1 1 100%; min-width: 0; }
.fp-col-half  { flex: 1 1 calc(50% - 8px); min-width: 180px; }
.fp-col-third { flex: 1 1 calc(33% - 10px); min-width: 140px; }
.fp-field-col { display: flex; flex-direction: column; gap: 4px; }
.fp-label { font-size: .77rem; font-weight: 600; color: var(--text-secondary,#8892a6); }
.fp-required { color: var(--red,#f87171); margin-right: 3px; }
.fp-input { width: 100%; padding: 7px 10px; border: 1px solid var(--border-color,rgba(255,255,255,.12)); border-radius: 6px; background: var(--bg-secondary,rgba(255,255,255,.04)); color: var(--text-primary,#e8eefc); font-size: .83rem; outline: none; box-sizing: border-box; font-family: inherit; }
.fp-textarea { resize: none; min-height: 80px; }
.fp-select-wrap { position: relative; }
.fp-select-wrap select { -webkit-appearance: none; appearance: none; width: 100%; padding: 7px 30px 7px 10px; border: 1px solid var(--border-color,rgba(255,255,255,.12)); border-radius: 6px; background: var(--bg-secondary,rgba(255,255,255,.04)); color: var(--text-primary,#e8eefc); font-size: .83rem; cursor: default; }
.fp-sel-arrow { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: .68rem; color: var(--text-secondary,#8892a6); pointer-events: none; }
.fp-radio-group { display: flex; gap: 14px; flex-wrap: wrap; }
.fp-radio-label { display: flex; align-items: center; gap: 5px; font-size: .81rem; color: var(--text-primary,#e8eefc); cursor: default; }
.fp-checkbox-wrap { display: flex; align-items: center; gap: 6px; font-size: .81rem; color: var(--text-primary,#e8eefc); cursor: default; }
.fp-file-btn { padding: 7px 14px; border: 1px solid var(--border-color,rgba(255,255,255,.12)); border-radius: 6px; color: var(--text-secondary,#8892a6); font-size: .8rem; display: inline-flex; align-items: center; gap: 6px; background: var(--bg-secondary,rgba(255,255,255,.04)); cursor: default; }
.fp-help { font-size: .72rem; color: var(--text-secondary,#8892a6); margin-top: 2px; }
.fp-empty-section { font-size: .78rem; color: var(--text-secondary,#8892a6); padding: 4px 0; }
.fp-footer { display: flex; align-items: center; gap: 10px; padding-top: 8px; border-top: 1px solid var(--border-color,rgba(255,255,255,.07)); margin-top: 10px; }
.fp-submit-btn { padding: 9px 24px; color: var(--bg-primary,#0a0a14); border: none; border-radius: 7px; font-size: .85rem; font-weight: 700; cursor: default; }
.fp-cancel-btn { padding: 9px 18px; border: 1px solid var(--border-color,rgba(255,255,255,.12)); border-radius: 7px; font-size: .85rem; background: transparent; color: var(--text-secondary,#8892a6); cursor: default; }
`;

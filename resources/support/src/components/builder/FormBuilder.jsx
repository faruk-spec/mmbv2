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
          <div className="bldr-preview">
            <div style={{ marginBottom: 14 }}>
              <h3 style={{ color: 'var(--text-primary,#e8eefc)', margin: '0 0 4px' }}>{ui.form_title || 'Create Support Ticket'}</h3>
              <p style={{ color: 'var(--text-secondary,#8892a6)', margin: 0, fontSize: '.82rem' }}>{ui.form_subtitle || 'Form subtitle'}</p>
            </div>
            <p style={{ color: 'var(--text-secondary,#8892a6)', fontSize: '.82rem', marginBottom: 12 }}>
              This is a read-only preview of how the form will look to users.
            </p>
            {(schema.sections ?? []).map(section => (
              <div key={section.id} style={previewSectionStyle}>
                <div style={{ padding: '10px 14px', background: ui.section_style === 'minimal' ? 'transparent' : 'var(--bg-card,rgba(255,255,255,.03))', fontWeight: 600, color: 'var(--text-primary,#e8eefc)', fontSize: '.88rem' }}>{section.title}</div>
                {section.description && (
                  <div style={{ padding: '0 14px 8px', color: 'var(--text-secondary,#8892a6)', fontSize: '.78rem' }}>{section.description}</div>
                )}
                <div style={{ padding: 14 }}>
                  {(section.fields ?? []).map(f => (
                    <div key={f.id} style={{ marginBottom: 12, maxWidth: f.width === 'half' ? '50%' : f.width === 'third' ? '33%' : '100%' }}>
                      <div style={{ fontSize: '.75rem', fontWeight: 600, color: 'var(--text-secondary,#8892a6)', marginBottom: 4 }}>
                        {f.label ?? f.name}
                        {f.required && <span style={{ color: 'var(--red,#f87171)' }}> *</span>}
                      </div>
                      <div style={{ background: 'var(--bg-secondary,rgba(255,255,255,.04))', border: '1px solid var(--border-color,rgba(255,255,255,.1))', borderRadius: 7, padding: '7px 10px', color: 'var(--text-secondary,#8892a6)', fontSize: '.82rem' }}>
                        {f.type} field
                        {f.placeholder ? ` — "${f.placeholder}"` : ''}
                      </div>
                    </div>
                  ))}
                    {(section.fields ?? []).length === 0 && (
                      <span style={{ color: 'var(--text-secondary,#8892a6)', fontSize: '.8rem' }}>Empty section</span>
                    )}
                </div>
              </div>
            ))}
            <button type="button" className="bldr-save-btn" style={{ marginLeft: 0, background: ui.accent_color || 'var(--cyan,#00f0ff)' }}>
              {ui.submit_label || 'Submit Ticket'}
            </button>
          </div>
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
`;

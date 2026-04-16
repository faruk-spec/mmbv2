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
  } = useBuilderState(initialSchema);

  const [activeId,   setActiveId]   = useState(null);
  const [activeTab,  setActiveTab]  = useState('build'); // 'build' | 'logic' | 'preview'

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
          {['build', 'logic', 'preview'].map(tab => (
            <button
              key={tab}
              type="button"
              className={`bldr-tab${activeTab === tab ? ' active' : ''}`}
              onClick={() => setActiveTab(tab)}
            >
              {tab === 'build' ? '🔨 Build' : tab === 'logic' ? '⚡ Conditions' : '👁 Preview'}
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
          <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragStart={handleDragStart}
            onDragEnd={handleDragEnd}
          >
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

            <DragOverlay>
              {activeField && (
                <div className="bldr-field-card drag-overlay">
                  <i className={`fas fa-grip-vertical`}></i>
                  <span>{activeField.label || activeField.name}</span>
                </div>
              )}
              {activeSection && (
                <div className="bldr-section drag-overlay">
                  <strong>{activeSection.title}</strong>
                </div>
              )}
            </DragOverlay>
          </DndContext>
        )}

        {activeTab === 'logic' && (
          <ConditionalLogicEditor
            schema={schema}
            rules={schema.conditional_logic ?? []}
            onChange={updateConditionalLogic}
          />
        )}

        {activeTab === 'preview' && (
          <div className="bldr-preview">
            <p style={{ color: '#8892a6', fontSize: '.82rem', marginBottom: 12 }}>
              This is a read-only preview of how the form will look to users.
            </p>
            {(schema.sections ?? []).map(section => (
              <div key={section.id} style={{ marginBottom: 20, border: '1px solid rgba(255,255,255,.08)', borderRadius: 10, overflow: 'hidden' }}>
                <div style={{ padding: '10px 14px', background: 'rgba(255,255,255,.03)', fontWeight: 600, color: '#e8eefc', fontSize: '.88rem' }}>{section.title}</div>
                <div style={{ padding: 14 }}>
                  {(section.fields ?? []).map(f => (
                    <div key={f.id} style={{ marginBottom: 12 }}>
                      <div style={{ fontSize: '.75rem', fontWeight: 600, color: '#8892a6', marginBottom: 4 }}>
                        {f.label ?? f.name}
                        {f.required && <span style={{ color: '#f87171' }}> *</span>}
                      </div>
                      <div style={{ background: 'rgba(255,255,255,.04)', border: '1px solid rgba(255,255,255,.1)', borderRadius: 7, padding: '7px 10px', color: '#4b5563', fontSize: '.82rem' }}>
                        {f.type} field
                        {f.placeholder ? ` — "${f.placeholder}"` : ''}
                      </div>
                    </div>
                  ))}
                  {(section.fields ?? []).length === 0 && (
                    <span style={{ color: '#4b5563', fontSize: '.8rem' }}>Empty section</span>
                  )}
                </div>
              </div>
            ))}
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
  );
}

const BUILDER_STYLES = `
/* Palette */
.bldr-palette { background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08); border-radius: 10px; overflow: hidden; margin-bottom: 10px; }
.bldr-palette-title { padding: 10px 12px; font-size: .72rem; font-weight: 700; text-transform: uppercase; color: #8892a6; letter-spacing: .05em; border-bottom: 1px solid rgba(255,255,255,.06); }
.bldr-palette-list { padding: 8px; display: flex; flex-direction: column; gap: 4px; }
.bldr-palette-item { display: flex; align-items: center; gap: 8px; padding: 7px 10px; border-radius: 7px; border: 1px solid rgba(255,255,255,.06); background: rgba(255,255,255,.03); color: #e8eefc; font-size: .8rem; cursor: grab; user-select: none; }
.bldr-palette-item:hover { background: rgba(0,240,255,.06); border-color: rgba(0,240,255,.2); }
.bldr-palette-item i { width: 14px; color: #00f0ff; }
.bldr-add-section-btn { width: 100%; padding: 8px; border: 1px dashed rgba(255,255,255,.15); background: transparent; color: #8892a6; border-radius: 8px; font-size: .8rem; cursor: pointer; }
.bldr-add-section-btn:hover { border-color: #00f0ff55; color: #00f0ff; }

/* Tabs */
.bldr-tabs { display: flex; gap: 6px; margin-bottom: 12px; align-items: center; flex-wrap: wrap; }
.bldr-tab { padding: 6px 14px; border-radius: 7px; border: 1px solid rgba(255,255,255,.1); background: rgba(255,255,255,.03); color: #8892a6; font-size: .8rem; cursor: pointer; }
.bldr-tab.active { background: rgba(0,240,255,.08); border-color: rgba(0,240,255,.3); color: #00f0ff; }
.bldr-save-btn { margin-left: auto; padding: 7px 16px; background: #00f0ff; color: #0a0a14; border: none; border-radius: 7px; font-size: .82rem; font-weight: 700; cursor: pointer; }
.bldr-save-btn:hover { opacity: .9; }
.bldr-save-btn:disabled { opacity: .5; cursor: not-allowed; }

/* Canvas */
.bldr-empty-canvas { border: 2px dashed rgba(255,255,255,.08); border-radius: 12px; padding: 40px; text-align: center; color: #4b5563; font-size: .85rem; }

/* Section */
.bldr-section { border: 1px solid rgba(255,255,255,.08); border-radius: 12px; overflow: hidden; margin-bottom: 12px; background: rgba(255,255,255,.02); }
.bldr-section.drop-over { border-color: #00f0ff66; background: rgba(0,240,255,.04); }
.bldr-section-head { padding: 10px 12px; background: rgba(255,255,255,.04); border-bottom: 1px solid rgba(255,255,255,.06); display: flex; align-items: center; gap: 8px; }
.bldr-section-drag { color: #4b5563; cursor: grab; font-size: .8rem; }
.bldr-section-title-input { flex: 1; background: transparent; border: none; outline: none; color: #e8eefc; font-size: .88rem; font-weight: 600; }
.bldr-section-collapse-toggle { display: flex; align-items: center; gap: 5px; font-size: .72rem; color: #8892a6; cursor: pointer; white-space: nowrap; }
.bldr-section-rm { background: none; border: none; color: #4b5563; cursor: pointer; font-size: .8rem; padding: 3px; }
.bldr-section-rm:hover { color: #ef4444; }
.bldr-section-fields { padding: 10px; min-height: 48px; }
.bldr-drop-hint { border: 2px dashed rgba(255,255,255,.07); border-radius: 8px; padding: 14px; text-align: center; color: #4b5563; font-size: .78rem; }

/* Field card */
.bldr-field-card { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border: 1px solid rgba(255,255,255,.08); border-radius: 8px; margin-bottom: 6px; background: rgba(255,255,255,.03); cursor: pointer; user-select: none; }
.bldr-field-card:hover { border-color: rgba(255,255,255,.15); background: rgba(255,255,255,.05); }
.bldr-field-card.selected { border-color: #00f0ff55; background: rgba(0,240,255,.05); }
.bldr-field-card.drag-overlay { box-shadow: 0 8px 20px rgba(0,0,0,.4); }
.bldr-field-drag { color: #4b5563; cursor: grab; font-size: .8rem; flex-shrink: 0; }
.bldr-field-type-icon { width: 20px; text-align: center; color: #00f0ff; font-size: .8rem; flex-shrink: 0; }
.bldr-field-info { flex: 1; min-width: 0; }
.bldr-field-label { font-size: .83rem; font-weight: 500; color: #e8eefc; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.bldr-field-meta { font-size: .7rem; color: #8892a6; }
.bldr-field-rm { background: none; border: none; color: #4b5563; cursor: pointer; font-size: .78rem; padding: 3px; flex-shrink: 0; }
.bldr-field-rm:hover { color: #ef4444; }

/* Editor panel */
.bldr-editor { background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08); border-radius: 12px; overflow: hidden; }
.bldr-editor-empty { padding: 30px 16px; text-align: center; font-size: .82rem; color: #4b5563; }
.bldr-editor-head { padding: 10px 14px; border-bottom: 1px solid rgba(255,255,255,.06); display: flex; align-items: center; justify-content: space-between; }
.bldr-editor-close { background: none; border: none; color: #8892a6; cursor: pointer; }
.bldr-editor-close:hover { color: #ef4444; }
.bldr-editor-body { padding: 12px 14px; max-height: 70vh; overflow-y: auto; }
.bldr-editor-row { margin-bottom: 10px; }
.bldr-editor-label { font-size: .72rem; font-weight: 600; color: #8892a6; margin-bottom: 4px; display: block; }
.bldr-ei { width: 100%; padding: 6px 9px; border: 1px solid rgba(255,255,255,.1); border-radius: 6px; background: rgba(255,255,255,.04); color: #e8eefc; font-size: .82rem; outline: none; box-sizing: border-box; }
.bldr-ei:focus { border-color: #00f0ff55; }
.bldr-ei-sm { flex: 1; }
.bldr-editor-section { margin-top: 14px; }
.bldr-editor-section-title { font-size: .72rem; font-weight: 700; text-transform: uppercase; color: #8892a6; letter-spacing: .05em; margin-bottom: 8px; }
.bldr-option-row { display: flex; gap: 6px; margin-bottom: 6px; align-items: center; }
.bldr-opt-rm { background: none; border: none; color: #4b5563; cursor: pointer; font-size: .78rem; flex-shrink: 0; }
.bldr-opt-rm:hover { color: #ef4444; }
.bldr-add-opt { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; background: rgba(14,165,233,.1); border: 1px solid rgba(14,165,233,.25); color: #0ea5e9; border-radius: 6px; font-size: .75rem; cursor: pointer; }
.bldr-toggle { position: relative; display: inline-block; width: 36px; height: 20px; }
.bldr-toggle input { opacity: 0; width: 0; height: 0; }
.bldr-toggle-slider { position: absolute; inset: 0; background: rgba(255,255,255,.1); border-radius: 20px; cursor: pointer; transition: .2s; }
.bldr-toggle input:checked + .bldr-toggle-slider { background: #00f0ff; }
.bldr-toggle-slider:before { content: ""; position: absolute; height: 14px; width: 14px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: .2s; }
.bldr-toggle input:checked + .bldr-toggle-slider:before { transform: translateX(16px); }

/* Conditional logic */
.cle-root { background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08); border-radius: 12px; overflow: hidden; }
.cle-header { padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,.06); display: flex; align-items: center; justify-content: space-between; font-size: .88rem; font-weight: 600; color: #e8eefc; }
.cle-add-btn { padding: 5px 12px; background: rgba(0,240,255,.1); border: 1px solid rgba(0,240,255,.25); color: #00f0ff; border-radius: 6px; font-size: .77rem; cursor: pointer; }
.cle-empty { padding: 20px 16px; color: #4b5563; font-size: .82rem; }
.cle-rule { padding: 14px 16px; border-bottom: 1px solid rgba(255,255,255,.06); }
.cle-rule-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap; }
.cle-rule-label { font-size: .72rem; color: #8892a6; width: 90px; flex-shrink: 0; }
.cle-sel { flex: 1; min-width: 100px; padding: 5px 8px; border: 1px solid rgba(255,255,255,.1); border-radius: 6px; background: rgba(255,255,255,.04); color: #e8eefc; font-size: .8rem; outline: none; }
.cle-inp { flex: 1; min-width: 100px; padding: 5px 8px; border: 1px solid rgba(255,255,255,.1); border-radius: 6px; background: rgba(255,255,255,.04); color: #e8eefc; font-size: .8rem; outline: none; }
.cle-rm-btn { padding: 4px 10px; background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.2); color: #ef4444; border-radius: 6px; font-size: .75rem; cursor: pointer; }

/* Preview */
.bldr-preview { }
`;

import { useState, useCallback } from 'react';
import { arrayMove } from '@dnd-kit/sortable';

const VALID_FIELD_WIDTHS = ['full', 'half', 'third'];

function uid() {
  if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
    return crypto.randomUUID();
  }
  // Fallback for environments without crypto.randomUUID
  return 'id_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11);
}

function defaultField(type) {
  const base = {
    id:       uid(),
    type,
    name:     type + '_' + Math.floor(Math.random() * 9999),
    label:    type.charAt(0).toUpperCase() + type.slice(1) + ' Field',
    required: false,
    width:    'full',
    default_value: '',
    validation: {},
  };
  if (['select', 'radio', 'checkbox'].includes(type)) {
    base.options = [
      { value: 'option_1', label: 'Option 1' },
      { value: 'option_2', label: 'Option 2' },
    ];
  }
  return base;
}

function defaultSection() {
  return { id: uid(), title: 'New Section', description: '', collapsible: false, fields: [] };
}

function defaultUiSettings() {
  return {
    form_title: 'Create Support Ticket',
    form_subtitle: 'Provide complete details so the support team can help quickly.',
    submit_label: 'Submit Ticket',
    success_title: 'Ticket Submitted!',
    success_message: 'Your ticket has been created. Our team will get back to you shortly.',
    accent_color: '#00f0ff',
    section_style: 'card',
    compact_mode: false,
  };
}

function normalizeField(field = {}) {
  return {
    ...defaultField(field.type ?? 'text'),
    ...field,
    width: VALID_FIELD_WIDTHS.includes(field.width) ? field.width : 'full',
  };
}

function normalizeSchema(initial = null) {
  const base = initial ?? {};
  const sections = Array.isArray(base.sections) && base.sections.length > 0
    ? base.sections.map((section) => ({
        ...defaultSection(),
        ...section,
        fields: Array.isArray(section?.fields) ? section.fields.map(normalizeField) : [],
      }))
    : [defaultSection()];

  return {
    ...base,
    sections,
    conditional_logic: Array.isArray(base.conditional_logic) ? base.conditional_logic : [],
    ui: { ...defaultUiSettings(), ...(base.ui ?? {}) },
  };
}

/**
 * useBuilderState — schema edit + drag-and-drop state for FormBuilder.
 * @param {object|null} initial – existing schema (or null for new)
 */
export function useBuilderState(initial = null) {
  const [schema, setSchema] = useState(() => normalizeSchema(initial));
  const [selectedFieldId, setSelectedFieldId] = useState(null);

  // ── Section operations ──────────────────────────────────────────────────────

  const addSection = useCallback(() => {
    setSchema(prev => ({
      ...prev,
      sections: [...prev.sections, defaultSection()],
    }));
  }, []);

  const updateSection = useCallback((sectionId, patch) => {
    setSchema(prev => ({
      ...prev,
      sections: prev.sections.map(s => s.id === sectionId ? { ...s, ...patch } : s),
    }));
  }, []);

  const removeSection = useCallback((sectionId) => {
    setSchema(prev => ({
      ...prev,
      sections: prev.sections.filter(s => s.id !== sectionId),
    }));
  }, []);

  // ── Field operations ────────────────────────────────────────────────────────

  const addField = useCallback((sectionId, fieldType) => {
    const field = defaultField(fieldType);
    setSchema(prev => ({
      ...prev,
      sections: prev.sections.map(s =>
        s.id === sectionId ? { ...s, fields: [...s.fields, field] } : s
      ),
    }));
    setSelectedFieldId(field.id);
    return field.id;
  }, []);

  const updateField = useCallback((fieldId, patch) => {
    setSchema(prev => ({
      ...prev,
      sections: prev.sections.map(s => ({
        ...s,
        fields: s.fields.map(f => f.id === fieldId ? { ...f, ...patch } : f),
      })),
    }));
  }, []);

  const removeField = useCallback((fieldId) => {
    setSchema(prev => ({
      ...prev,
      sections: prev.sections.map(s => ({
        ...s,
        fields: s.fields.filter(f => f.id !== fieldId),
      })),
      conditional_logic: (prev.conditional_logic ?? []).filter(
        r => !r.target_fields?.includes(fieldId) && r.trigger_field !== getFieldName(prev, fieldId)
      ),
    }));
    setSelectedFieldId(id => id === fieldId ? null : id);
  }, []);

  // ── Drag-and-drop ───────────────────────────────────────────────────────────

  const moveField = useCallback((activeId, overId) => {
    setSchema(prev => {
      // Find source section + position
      let srcSection = null, srcIdx = -1, field = null;
      for (const s of prev.sections) {
        const idx = s.fields.findIndex(f => f.id === activeId);
        if (idx !== -1) { srcSection = s; srcIdx = idx; field = s.fields[idx]; break; }
      }
      if (!field) return prev;

      // Find destination section + position
      let dstSection = null, dstIdx = -1;
      for (const s of prev.sections) {
        const idx = s.fields.findIndex(f => f.id === overId);
        if (idx !== -1) { dstSection = s; dstIdx = idx; break; }
      }
      // Also check if overId is a section id
      if (!dstSection) {
        dstSection = prev.sections.find(s => s.id === overId);
        dstIdx = dstSection ? dstSection.fields.length : -1;
      }
      if (!dstSection) return prev;

      if (srcSection.id === dstSection.id) {
        // Same-section reorder
        const newFields = arrayMove(srcSection.fields, srcIdx, dstIdx);
        return {
          ...prev,
          sections: prev.sections.map(s =>
            s.id === srcSection.id ? { ...s, fields: newFields } : s
          ),
        };
      } else {
        // Cross-section move
        const srcFields = srcSection.fields.filter(f => f.id !== activeId);
        const dstFields = [...dstSection.fields];
        dstFields.splice(dstIdx, 0, field);
        return {
          ...prev,
          sections: prev.sections.map(s => {
            if (s.id === srcSection.id) return { ...s, fields: srcFields };
            if (s.id === dstSection.id) return { ...s, fields: dstFields };
            return s;
          }),
        };
      }
    });
  }, []);

  const moveSections = useCallback((activeId, overId) => {
    setSchema(prev => {
      const oldIdx = prev.sections.findIndex(s => s.id === activeId);
      const newIdx = prev.sections.findIndex(s => s.id === overId);
      if (oldIdx === -1 || newIdx === -1) return prev;
      return { ...prev, sections: arrayMove(prev.sections, oldIdx, newIdx) };
    });
  }, []);

  // ── Conditional logic ───────────────────────────────────────────────────────

  const updateConditionalLogic = useCallback((rules) => {
    setSchema(prev => ({ ...prev, conditional_logic: rules }));
  }, []);

  const updateUiSettings = useCallback((patch) => {
    setSchema(prev => ({
      ...prev,
      ui: {
        ...(prev.ui ?? defaultUiSettings()),
        ...patch,
      },
    }));
  }, []);

  return {
    schema, setSchema,
    selectedFieldId, setSelectedFieldId,
    addSection, updateSection, removeSection,
    addField, updateField, removeField,
    moveField, moveSections,
    updateConditionalLogic,
    updateUiSettings,
  };
}

function getFieldName(schema, fieldId) {
  for (const s of schema.sections ?? []) {
    for (const f of s.fields ?? []) {
      if (f.id === fieldId) return f.name ?? '';
    }
  }
  return '';
}

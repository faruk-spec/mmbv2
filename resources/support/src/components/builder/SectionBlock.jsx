import React from 'react';
import { useSortable }  from '@dnd-kit/sortable';
import { SortableContext, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { useDroppable } from '@dnd-kit/core';
import { CSS }          from '@dnd-kit/utilities';
import FieldCard        from './FieldCard.jsx';

export default function SectionBlock({
  section,
  onUpdateSection,
  onRemoveSection,
  onRemoveField,
  onAddField,
  selectedFieldId,
  onSelectField,
}) {
  const {
    attributes, listeners, setNodeRef: setSortRef,
    transform, transition, isDragging,
  } = useSortable({ id: section.id, data: { type: 'section' } });

  const { setNodeRef: setDropRef, isOver } = useDroppable({ id: section.id });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.4 : 1,
  };

  function setRef(el) {
    setSortRef(el);
    setDropRef(el);
  }

  return (
    <div
      ref={setRef}
      style={style}
      className={`bldr-section${isOver ? ' drop-over' : ''}`}
    >
      <div className="bldr-section-head">
        <span className="bldr-section-drag" {...attributes} {...listeners} title="Drag section">
          <i className="fas fa-grip-lines"></i>
        </span>
        <input
          className="bldr-section-title-input"
          value={section.title}
          onChange={e => onUpdateSection(section.id, { title: e.target.value })}
          placeholder="Section title"
        />
        <label className="bldr-section-collapse-toggle" title="Collapsible section">
          <input
            type="checkbox"
            checked={!!section.collapsible}
            onChange={e => onUpdateSection(section.id, { collapsible: e.target.checked })}
          />
          Collapsible
        </label>
        <button
          type="button"
          className="bldr-section-rm"
          title="Remove section"
          onClick={() => onRemoveSection(section.id)}
        >
          <i className="fas fa-trash"></i>
        </button>
      </div>

      <div className="bldr-section-fields">
        <SortableContext items={(section.fields ?? []).map(f => f.id)} strategy={verticalListSortingStrategy}>
          {(section.fields ?? []).length === 0 && (
            <div className="bldr-drop-hint">
              <i className="fas fa-arrow-down"></i> Drop fields here
            </div>
          )}
          {(section.fields ?? []).map(field => (
            <FieldCard
              key={field.id}
              field={field}
              sectionId={section.id}
              isSelected={selectedFieldId === field.id}
              onSelect={onSelectField}
              onRemove={onRemoveField}
            />
          ))}
        </SortableContext>
      </div>
    </div>
  );
}

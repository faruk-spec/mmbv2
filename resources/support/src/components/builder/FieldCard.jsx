import React from 'react';
import { useSortable } from '@dnd-kit/sortable';
import { CSS }         from '@dnd-kit/utilities';

const TYPE_ICONS = {
  text: 'font', textarea: 'align-left', select: 'list-ul',
  radio: 'circle-dot', checkbox: 'check-square', file: 'file-arrow-up',
  date: 'calendar-days', number: 'hashtag',
};

export default function FieldCard({ field, sectionId, isSelected, onSelect, onRemove }) {
  const {
    attributes, listeners, setNodeRef,
    transform, transition, isDragging,
  } = useSortable({ id: field.id, data: { type: 'field', sectionId } });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.35 : 1,
  };

  return (
    <div
      ref={setNodeRef}
      style={style}
      className={`bldr-field-card${isSelected ? ' selected' : ''}`}
      onClick={() => onSelect(field.id)}
    >
      <span className="bldr-field-drag" {...attributes} {...listeners} title="Drag to reorder">
        <i className="fas fa-grip-vertical"></i>
      </span>
      <span className="bldr-field-type-icon">
        <i className={`fas fa-${TYPE_ICONS[field.type] ?? 'font'}`}></i>
      </span>
      <div className="bldr-field-info">
        <span className="bldr-field-label">{field.label || field.name}</span>
        <span className="bldr-field-meta">{field.type}{field.required ? ' · required' : ''}</span>
      </div>
      <button
        type="button"
        className="bldr-field-rm"
        title="Remove field"
        onClick={e => { e.stopPropagation(); onRemove(field.id); }}
      >
        <i className="fas fa-times"></i>
      </button>
    </div>
  );
}

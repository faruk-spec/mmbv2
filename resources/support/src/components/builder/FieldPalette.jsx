import React from 'react';
import { useDraggable } from '@dnd-kit/core';

const FIELD_TYPES = [
  { type: 'text',     label: 'Text',      icon: 'font'          },
  { type: 'textarea', label: 'Textarea',  icon: 'align-left'    },
  { type: 'select',   label: 'Dropdown',  icon: 'list-ul'       },
  { type: 'radio',    label: 'Radio',     icon: 'circle-dot'    },
  { type: 'checkbox', label: 'Checkbox',  icon: 'check-square'  },
  { type: 'file',     label: 'File',      icon: 'file-arrow-up' },
  { type: 'date',     label: 'Date',      icon: 'calendar-days' },
  { type: 'number',   label: 'Number',    icon: 'hashtag'       },
];

function DraggableType({ fieldType }) {
  const { attributes, listeners, setNodeRef, isDragging } = useDraggable({
    id:   'palette__' + fieldType.type,
    data: { isPalette: true, fieldType: fieldType.type },
  });

  return (
    <div
      ref={setNodeRef}
      {...listeners}
      {...attributes}
      className="bldr-palette-item"
      style={{ opacity: isDragging ? 0.4 : 1 }}
    >
      <i className={`fas fa-${fieldType.icon}`}></i>
      <span>{fieldType.label}</span>
    </div>
  );
}

export default function FieldPalette() {
  return (
    <div className="bldr-palette">
      <div className="bldr-palette-title">Field Types</div>
      <div className="bldr-palette-list">
        {FIELD_TYPES.map(ft => (
          <DraggableType key={ft.type} fieldType={ft} />
        ))}
      </div>
    </div>
  );
}

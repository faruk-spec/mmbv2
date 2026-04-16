import React, { useState } from 'react';

export default function FormSection({ section, children, ui = {} }) {
  const [collapsed, setCollapsed] = useState(false);
  const minimal = ui.section_style === 'minimal';
  const compact = !!ui.compact_mode;

  return (
    <div className={`sp-section${minimal ? ' sp-section-minimal' : ''}${compact ? ' sp-section-compact' : ''}`}>
      <div
        className="sp-section-head"
        onClick={() => section.collapsible && setCollapsed(c => !c)}
        style={{ cursor: section.collapsible ? 'pointer' : 'default' }}
      >
        <div>
          <h3 className="sp-section-title">{section.title}</h3>
          {section.description && <p style={{ margin: '3px 0 0', color: 'var(--text-secondary,#8892a6)', fontSize: '.74rem' }}>{section.description}</p>}
        </div>
        {section.collapsible && <span className="sp-section-toggle">{collapsed ? '▼' : '▲'}</span>}
      </div>
      {!collapsed && (
        <div className="sp-section-body">{children}</div>
      )}
    </div>
  );
}

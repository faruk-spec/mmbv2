import React, { useState } from 'react';

export default function FormSection({ section, children }) {
  const [collapsed, setCollapsed] = useState(false);

  return (
    <div className="sp-section">
      <div
        className="sp-section-head"
        onClick={() => section.collapsible && setCollapsed(c => !c)}
        style={{ cursor: section.collapsible ? 'pointer' : 'default' }}
      >
        <h3 className="sp-section-title">{section.title}</h3>
        {section.collapsible && (
          <span className="sp-section-toggle">{collapsed ? '▼' : '▲'}</span>
        )}
      </div>
      {!collapsed && (
        <div className="sp-section-body">{children}</div>
      )}
    </div>
  );
}

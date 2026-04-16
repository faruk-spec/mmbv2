import React from 'react';

export default function GroupSelector({ groups, onSelect }) {
  if (!groups.length) {
    return <p style={{ color: '#8892a6' }}>No support groups available. Please contact an administrator.</p>;
  }

  return (
    <div className="sp-card-grid">
      {groups.map(g => (
        <button
          key={g.id}
          className="sp-group-card"
          onClick={() => onSelect(g)}
          type="button"
        >
          <span
            className="sp-group-icon"
            style={{ background: (g.color ?? '#00f0ff') + '22', color: g.color ?? '#00f0ff' }}
          >
            <i className={`fas fa-${g.icon ?? 'users'}`}></i>
          </span>
          <span className="sp-group-name">{g.name}</span>
          {g.description && <span className="sp-group-desc">{g.description}</span>}
        </button>
      ))}
    </div>
  );
}

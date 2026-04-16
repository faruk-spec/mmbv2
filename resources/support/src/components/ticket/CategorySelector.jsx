import React from 'react';

export default function CategorySelector({ categories, group, onSelect, onBack }) {
  return (
    <div>
      <button type="button" className="sp-back-btn" onClick={onBack}>
        ← Back to Groups
      </button>
      <h3 className="sp-wizard-subtitle">{group.name}</h3>

      {categories.length === 0 ? (
        <p style={{ color: '#8892a6' }}>No categories available for this group.</p>
      ) : (
        <div className="sp-card-grid">
          {categories.map(cat => (
            <button
              key={cat.id}
              className="sp-cat-card"
              onClick={() => onSelect(cat)}
              type="button"
            >
              <span className="sp-cat-icon">
                <i className={`fas fa-${cat.icon ?? 'tag'}`}></i>
              </span>
              <span className="sp-cat-name">{cat.name}</span>
              {cat.description && <span className="sp-cat-desc">{cat.description}</span>}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}

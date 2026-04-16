import React, { useState } from 'react';
import FormBuilder from '../components/builder/FormBuilder.jsx';

export default function TemplateBuilderPage() {
  const cfg = window.__SUPPORT_BUILDER_CONFIG__ ?? {};

  const [saving,       setSaving]       = useState(false);
  const [saveMessage,  setSaveMessage]  = useState(null);
  const [saveError,    setSaveError]    = useState(null);
  const [currentVer,   setCurrentVer]   = useState(cfg.currentVersion ?? 0);

  const handleSave = async (schema) => {
    setSaving(true);
    setSaveMessage(null);
    setSaveError(null);
    try {
      const res  = await fetch(`${cfg.apiBase}/template/${cfg.categoryId}`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ schema }),
      });
      const json = await res.json();
      if (json.ok) {
        setCurrentVer(json.data?.version ?? currentVer + 1);
        setSaveMessage(`Template saved! Version ${json.data?.version ?? currentVer + 1}`);
      } else {
        setSaveError(json.error ?? 'Save failed.');
      }
    } catch (e) {
      setSaveError('Network error: ' + e.message);
    } finally {
      setSaving(false);
    }
  };

  return (
    <div style={{ padding: '24px 28px' }}>
      <style>{PAGE_STYLES}</style>

      {/* Breadcrumb */}
      <div className="tplb-breadcrumb">
        <a href="/admin/support/groups" className="tplb-bc-link">Groups</a>
        <span> / </span>
        <a href={cfg.builderBackUrl} className="tplb-bc-link">{cfg.groupName}</a>
        <span> / </span>
        <strong>{cfg.categoryName}</strong>
      </div>

      {/* Page header */}
      <div className="tplb-header">
        <div>
          <h1 className="tplb-title">Form Builder — {cfg.categoryName}</h1>
          <p className="tplb-sub">
            {currentVer > 0
              ? `Currently at version ${currentVer}. Saving creates a new version.`
              : 'No template yet — create the first version below.'}
          </p>
        </div>
        {cfg.history?.length > 0 && (
          <details className="tplb-history-dropdown">
            <summary>Version History ({cfg.history.length})</summary>
            <div className="tplb-history-list">
              {cfg.history.map(h => (
                <div key={h.id} className={`tplb-history-item${h.is_active ? ' active' : ''}`}>
                  v{h.version} {h.is_active ? '(current)' : ''}
                  <span style={{ color: '#6b7280', fontSize: '.72rem', marginLeft: 8 }}>{h.created_at}</span>
                  {h.created_by_name && <span style={{ color: '#6b7280', fontSize: '.72rem', marginLeft: 8 }}>by {h.created_by_name}</span>}
                </div>
              ))}
            </div>
          </details>
        )}
      </div>

      {/* Save messages */}
      {saveMessage && (
        <div className="tplb-alert tplb-alert-ok">✓ {saveMessage}</div>
      )}
      {saveError && (
        <div className="tplb-alert tplb-alert-err">✗ {saveError}</div>
      )}

      <FormBuilder
        initialSchema={cfg.existingSchema}
        onSave={handleSave}
        saving={saving}
      />
    </div>
  );
}

const PAGE_STYLES = `
.tplb-breadcrumb { font-size: .78rem; color: #8892a6; margin-bottom: 12px; }
.tplb-bc-link { color: #8892a6; text-decoration: none; }
.tplb-bc-link:hover { color: #00f0ff; }
.tplb-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; gap: 16px; flex-wrap: wrap; }
.tplb-title { font-size: 1.35rem; font-weight: 700; color: #e8eefc; margin: 0 0 4px; }
.tplb-sub { font-size: .83rem; color: #8892a6; margin: 0; }
.tplb-history-dropdown summary { cursor: pointer; font-size: .82rem; color: #8892a6; padding: 6px 12px; border: 1px solid rgba(255,255,255,.1); border-radius: 8px; list-style: none; }
.tplb-history-dropdown summary::-webkit-details-marker { display: none; }
.tplb-history-dropdown summary:hover { color: #e8eefc; border-color: rgba(255,255,255,.2); }
.tplb-history-list { margin-top: 6px; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08); border-radius: 8px; padding: 8px; max-height: 200px; overflow-y: auto; }
.tplb-history-item { padding: 5px 8px; font-size: .8rem; color: #8892a6; border-radius: 5px; }
.tplb-history-item.active { color: #00f0ff; font-weight: 600; }
.tplb-alert { padding: 9px 14px; border-radius: 8px; font-size: .83rem; margin-bottom: 14px; }
.tplb-alert-ok { background: rgba(34,197,94,.08); border: 1px solid rgba(34,197,94,.2); color: #4ade80; }
.tplb-alert-err { background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.2); color: #f87171; }
`;

import React, { useState, useEffect } from 'react';
import GroupSelector    from './GroupSelector.jsx';
import CategorySelector from './CategorySelector.jsx';
import DynamicForm      from '../renderer/DynamicForm.jsx';

const STEPS = ['group', 'category', 'form', 'done'];

const PRIORITY_LABELS = {
  low:    '🟢 Low',
  medium: '🔵 Medium',
  high:   '🟠 High',
  urgent: '🔴 Urgent',
};

export default function TicketCreateWizard() {
  const cfg = window.__SUPPORT_WIZARD_CONFIG__ ?? {};
  const apiBase   = cfg.apiBase   ?? '/api/support';
  const submitUrl = cfg.submitUrl ?? '/api/support/tickets';

  const [step,       setStep]       = useState('group');
  const [groups,     setGroups]     = useState([]);
  const [categories, setCategories] = useState([]);
  const [schema,     setSchema]     = useState(null);
  const [templateId, setTemplateId] = useState(null);

  const [selectedGroup,    setSelectedGroup]    = useState(null);
  const [selectedCategory, setSelectedCategory] = useState(null);
  const [subject,          setSubject]          = useState('');
  const [priority,         setPriority]         = useState('medium');
  const [submitting,       setSubmitting]        = useState(false);
  const [error,            setError]             = useState(null);
  const [ticketId,         setTicketId]          = useState(null);

  // Fetch groups on mount
  useEffect(() => {
    fetch(`${apiBase}/groups`)
      .then(r => r.json())
      .then(j => { if (j.ok) setGroups(j.data ?? []); })
      .catch(() => setError('Failed to load support groups.'));
  }, [apiBase]);

  const handleSelectGroup = async (group) => {
    setSelectedGroup(group);
    setError(null);
    const res  = await fetch(`${apiBase}/categories?group_id=${group.id}`);
    const json = await res.json();
    if (json.ok) {
      setCategories(json.data ?? []);
      setStep('category');
    } else {
      setError('Failed to load categories.');
    }
  };

  const handleSelectCategory = async (cat) => {
    setSelectedCategory(cat);
    setError(null);
    const res  = await fetch(`${apiBase}/template?category_id=${cat.id}`);
    const json = await res.json();
    if (json.ok) {
      setSchema(json.data.schema);
      setTemplateId(json.data.template_id);
      setPriority(json.data.schema?.default_priority ?? 'medium');
      setStep('form');
    } else {
      setError(json.error ?? 'No form template available for this category.');
    }
  };

  const handleFormSubmit = async (formValues) => {
    if (!subject.trim()) {
      setError('Please enter a subject for your ticket.');
      return;
    }
    setSubmitting(true);
    setError(null);
    try {
      const res  = await fetch(submitUrl, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({
          template_id: templateId,
          subject:     subject.trim(),
          priority,
          data:        formValues,
        }),
      });
      const json = await res.json();
      if (json.ok) {
        setTicketId(json.data.ticket_id);
        setStep('done');
      } else {
        setError(json.error ?? 'Submission failed. Please try again.');
      }
    } catch (e) {
      setError('Network error: ' + e.message);
    } finally {
      setSubmitting(false);
    }
  };

  // Progress indicator
  const stepIdx = STEPS.indexOf(step);

  return (
    <div className="sp-wizard">
      <style>{STYLES}</style>

      {/* Header */}
      <div className="sp-wizard-header">
        <h2 className="sp-wizard-title">Create Support Ticket</h2>
        {step !== 'done' && (
          <div className="sp-progress">
            {['Select Department', 'Select Issue Type', 'Fill Form', 'Done'].map((label, i) => (
              <React.Fragment key={i}>
                <div className={`sp-progress-step ${i <= stepIdx ? 'active' : ''}`}>
                  <div className="sp-progress-dot">{i < stepIdx ? '✓' : i + 1}</div>
                  <span className="sp-progress-label">{label}</span>
                </div>
                {i < 3 && <div className={`sp-progress-line ${i < stepIdx ? 'done' : ''}`} />}
              </React.Fragment>
            ))}
          </div>
        )}
      </div>

      {/* Error banner */}
      {error && (
        <div className="sp-alert-error">
          <i className="fas fa-circle-exclamation"></i> {error}
        </div>
      )}

      {/* Step content */}
      <div className="sp-wizard-body">

        {step === 'group' && (
          <GroupSelector groups={groups} onSelect={handleSelectGroup} />
        )}

        {step === 'category' && selectedGroup && (
          <CategorySelector
            categories={categories}
            group={selectedGroup}
            onSelect={handleSelectCategory}
            onBack={() => { setStep('group'); setSelectedGroup(null); }}
          />
        )}

        {step === 'form' && schema && (
          <div>
            <div className="sp-breadcrumb">
              <button type="button" className="sp-back-btn" onClick={() => setStep('category')}>← Back</button>
              <span className="sp-breadcrumb-path">{selectedGroup?.name} › {selectedCategory?.name}</span>
            </div>

            {/* Subject & Priority */}
            <div className="sp-meta-row">
              <div className="sp-field-wrap" style={{ flex: 1 }}>
                <label className="sp-label" htmlFor="ticket-subject">
                  Subject <span className="sp-required">*</span>
                </label>
                <input
                  id="ticket-subject"
                  type="text"
                  className="sp-input"
                  value={subject}
                  onChange={e => setSubject(e.target.value)}
                  placeholder="Brief summary of your issue"
                  maxLength={255}
                />
              </div>
              <div className="sp-field-wrap">
                <label className="sp-label" htmlFor="ticket-priority">Priority</label>
                <select
                  id="ticket-priority"
                  className="sp-select"
                  value={priority}
                  onChange={e => setPriority(e.target.value)}
                >
                  {Object.entries(PRIORITY_LABELS).map(([v, l]) => (
                    <option key={v} value={v}>{l}</option>
                  ))}
                </select>
              </div>
            </div>

            <DynamicForm schema={schema} onSubmit={handleFormSubmit} disabled={submitting} />
          </div>
        )}

        {step === 'done' && (
          <div className="sp-done">
            <div className="sp-done-icon">✓</div>
            <h3>Ticket Submitted!</h3>
            <p>Your ticket <strong>#{ticketId}</strong> has been created. Our team will get back to you shortly.</p>
            <div className="sp-done-actions">
              <a href="/support" className="sp-btn-submit" style={{ textDecoration: 'none', display: 'inline-block' }}>
                View My Tickets
              </a>
              <button
                type="button"
                className="sp-btn-secondary"
                onClick={() => {
                  setStep('group'); setSubject(''); setError(null);
                  setSelectedGroup(null); setSelectedCategory(null);
                  setSchema(null); setTemplateId(null);
                }}
              >
                Submit Another
              </button>
            </div>
          </div>
        )}

      </div>
    </div>
  );
}

const STYLES = `
.sp-wizard { max-width: 760px; font-family: inherit; }
.sp-wizard-header { margin-bottom: 24px; }
.sp-wizard-title { font-size: 1.4rem; font-weight: 700; color: #e8eefc; margin: 0 0 16px; }
.sp-wizard-subtitle { font-size: 1rem; font-weight: 600; color: #e8eefc; margin: 0 0 14px; }

.sp-progress { display: flex; align-items: center; gap: 0; flex-wrap: wrap; gap: 4px; }
.sp-progress-step { display: flex; align-items: center; gap: 6px; }
.sp-progress-dot { width: 24px; height: 24px; border-radius: 50%; background: rgba(255,255,255,.1); color: #8892a6; font-size: .75rem; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,.1); flex-shrink: 0; }
.sp-progress-step.active .sp-progress-dot { background: #00f0ff22; color: #00f0ff; border-color: #00f0ff55; }
.sp-progress-label { font-size: .78rem; color: #8892a6; }
.sp-progress-step.active .sp-progress-label { color: #e8eefc; }
.sp-progress-line { flex: 1; height: 1px; min-width: 20px; background: rgba(255,255,255,.1); }
.sp-progress-line.done { background: #00f0ff55; }

.sp-alert-error { background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.2); color: #f87171; padding: 10px 14px; border-radius: 8px; font-size: .85rem; margin-bottom: 14px; }
.sp-wizard-body { }

/* Cards */
.sp-card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; }
.sp-group-card, .sp-cat-card { display: flex; flex-direction: column; align-items: flex-start; gap: 6px; padding: 16px; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08); border-radius: 12px; cursor: pointer; text-align: left; width: 100%; transition: border-color .15s, background .15s; }
.sp-group-card:hover, .sp-cat-card:hover { border-color: #00f0ff55; background: rgba(0,240,255,.04); }
.sp-group-icon, .sp-cat-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.sp-cat-icon { background: rgba(0,240,255,.1); color: #00f0ff; }
.sp-group-name, .sp-cat-name { font-size: .9rem; font-weight: 600; color: #e8eefc; }
.sp-group-desc, .sp-cat-desc { font-size: .75rem; color: #8892a6; }

/* Back button */
.sp-back-btn { background: none; border: none; color: #8892a6; cursor: pointer; font-size: .83rem; padding: 0; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 12px; }
.sp-back-btn:hover { color: #00f0ff; }
.sp-breadcrumb { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
.sp-breadcrumb-path { font-size: .82rem; color: #8892a6; }

/* Meta row */
.sp-meta-row { display: flex; gap: 12px; margin-bottom: 4px; flex-wrap: wrap; }
.sp-meta-row .sp-field-wrap { flex: 1; min-width: 180px; }

/* Fields */
.sp-field-wrap { margin-bottom: 16px; }
.sp-field-wrap.sp-field-error .sp-input,
.sp-field-wrap.sp-field-error .sp-textarea,
.sp-field-wrap.sp-field-error .sp-select { border-color: #f87171; }
.sp-label { display: block; font-size: .78rem; font-weight: 600; color: #8892a6; margin-bottom: 5px; }
.sp-required { color: #f87171; }
.sp-help-text { font-size: .73rem; color: #8892a6; margin: 0 0 6px; }
.sp-error-msg { font-size: .75rem; color: #f87171; margin: 4px 0 0; }
.sp-input, .sp-textarea, .sp-select {
  width: 100%; padding: 8px 12px; border: 1px solid rgba(255,255,255,.1);
  border-radius: 8px; background: rgba(255,255,255,.04); color: #e8eefc;
  font-size: .88rem; outline: none; box-sizing: border-box; font-family: inherit;
}
.sp-input:focus, .sp-textarea:focus, .sp-select:focus { border-color: #00f0ff55; }
.sp-textarea { resize: vertical; min-height: 80px; }
.sp-select option { background: #1a1a2e; }
.sp-radio-group { display: flex; flex-direction: column; gap: 8px; }
.sp-radio-item { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: .88rem; color: #e8eefc; }
.sp-radio-item input { accent-color: #00f0ff; }
.sp-checkbox-item { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: .88rem; color: #e8eefc; }
.sp-checkbox-item input { accent-color: #00f0ff; }
.sp-file { color: #e8eefc; font-size: .85rem; }

/* Section */
.sp-section { margin-bottom: 24px; border: 1px solid rgba(255,255,255,.07); border-radius: 12px; overflow: hidden; }
.sp-section-head { padding: 12px 16px; background: rgba(255,255,255,.03); display: flex; align-items: center; justify-content: space-between; }
.sp-section-title { margin: 0; font-size: .9rem; font-weight: 600; color: #e8eefc; }
.sp-section-toggle { color: #8892a6; font-size: .75rem; }
.sp-section-body { padding: 16px; }

/* Submit */
.sp-btn-submit { padding: 10px 24px; background: #00f0ff; color: #0a0a14; border: none; border-radius: 8px; font-size: .9rem; font-weight: 700; cursor: pointer; }
.sp-btn-submit:hover { opacity: .9; }
.sp-btn-submit:disabled { opacity: .5; cursor: not-allowed; }
.sp-btn-secondary { padding: 10px 24px; background: rgba(255,255,255,.08); color: #e8eefc; border: 1px solid rgba(255,255,255,.12); border-radius: 8px; font-size: .9rem; font-weight: 600; cursor: pointer; }
.sp-btn-secondary:hover { background: rgba(255,255,255,.12); }

/* Done */
.sp-done { text-align: center; padding: 40px 20px; }
.sp-done-icon { font-size: 3rem; color: #22c55e; background: rgba(34,197,94,.1); border-radius: 50%; width: 80px; height: 80px; line-height: 80px; margin: 0 auto 16px; }
.sp-done h3 { font-size: 1.3rem; color: #e8eefc; margin: 0 0 8px; }
.sp-done p { color: #8892a6; margin: 0 0 20px; }
.sp-done-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
`;

import React from 'react';

const OPERATORS = [
  { value: 'equals',     label: '= equals'      },
  { value: 'not_equals', label: '≠ not equals'  },
  { value: 'in',         label: '∈ in (comma-separated values)' },
  { value: 'not_in',     label: '∉ not in'      },
  { value: 'contains',   label: '⊇ contains'    },
];

export default function ConditionalLogicEditor({ schema, rules, onChange }) {
  // Gather all field names
  const allFields = (schema?.sections ?? []).flatMap(s => (s.fields ?? []).map(f => ({ id: f.id, name: f.name, label: f.label ?? f.name })));

  const addRule = () => {
    onChange([...rules, {
      id: 'rule_' + Date.now(),
      trigger_field: allFields[0]?.name ?? '',
      operator:      'equals',
      trigger_value: '',
      effect:        'show',
      target_fields: [],
    }]);
  };

  const updateRule = (idx, patch) => {
    const next = rules.map((r, i) => i === idx ? { ...r, ...patch } : r);
    onChange(next);
  };

  const removeRule = (idx) => {
    onChange(rules.filter((_, i) => i !== idx));
  };

  return (
    <div className="cle-root">
      <div className="cle-header">
        <span>Conditional Logic Rules</span>
        <button type="button" className="cle-add-btn" onClick={addRule}>
          <i className="fas fa-plus"></i> Add Rule
        </button>
      </div>

      {rules.length === 0 && (
        <p className="cle-empty">No rules yet. Click "Add Rule" to show/hide fields based on values.</p>
      )}

      {rules.map((rule, idx) => (
        <div key={rule.id ?? idx} className="cle-rule">
          <div className="cle-rule-row">
            <span className="cle-rule-label">When field</span>
            <select className="cle-sel" value={rule.trigger_field} onChange={e => updateRule(idx, { trigger_field: e.target.value })}>
              {allFields.map(f => <option key={f.name} value={f.name}>{f.label}</option>)}
            </select>
          </div>
          <div className="cle-rule-row">
            <span className="cle-rule-label">Operator</span>
            <select className="cle-sel" value={rule.operator} onChange={e => updateRule(idx, { operator: e.target.value })}>
              {OPERATORS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
            </select>
          </div>
          <div className="cle-rule-row">
            <span className="cle-rule-label">Value</span>
            <input className="cle-inp" value={Array.isArray(rule.trigger_value) ? rule.trigger_value.join(',') : (rule.trigger_value ?? '')} onChange={e => {
              const raw = e.target.value;
              const val = ['in','not_in'].includes(rule.operator)
                ? raw.split(',').map(s => s.trim()).filter(Boolean)
                : raw;
              updateRule(idx, { trigger_value: val });
            }} placeholder="trigger value" />
          </div>
          <div className="cle-rule-row">
            <span className="cle-rule-label">Effect</span>
            <select className="cle-sel" value={rule.effect} onChange={e => updateRule(idx, { effect: e.target.value })}>
              <option value="show">Show</option>
              <option value="hide">Hide</option>
            </select>
          </div>
          <div className="cle-rule-row">
            <span className="cle-rule-label">Target fields</span>
            <select
              className="cle-sel"
              multiple
              size={Math.min(allFields.length, 5)}
              value={rule.target_fields ?? []}
              onChange={e => {
                const selected = [...e.target.selectedOptions].map(o => o.value);
                updateRule(idx, { target_fields: selected });
              }}
            >
              {allFields.map(f => <option key={f.id} value={f.id}>{f.label}</option>)}
            </select>
          </div>
          <button type="button" className="cle-rm-btn" onClick={() => removeRule(idx)}>
            <i className="fas fa-trash"></i> Remove Rule
          </button>
        </div>
      ))}
    </div>
  );
}

<?php use Core\View; use Core\Security; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<style>
/* ── Editor wrapper ─────────────────────────────────────────── */
.rxe-wrap {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 64px);
    min-height: 0;
    overflow: hidden;
}

/* ── Top bar ────────────────────────────────────────────────── */
.rxe-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    background: var(--bg-card);
    border-bottom: 1px solid var(--border-color);
    flex-shrink: 0;
    flex-wrap: wrap;
}
.rxe-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--text-secondary);
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
    white-space: nowrap;
}
.rxe-back:hover { color: var(--cyan); text-decoration: none; }
.rxe-title-input {
    flex: 1;
    min-width: 140px;
    max-width: 340px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 0.93rem;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    padding: 8px 12px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.rxe-title-input:focus {
    border-color: rgba(0,240,255,0.5);
    box-shadow: 0 0 0 3px rgba(0,240,255,0.07);
}
.rxe-bar-spacer { flex: 1; }
.rxe-save-status {
    font-size: 0.78rem;
    color: var(--text-secondary);
    white-space: nowrap;
}
.rxe-bar-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    white-space: nowrap;
    border: 1px solid var(--border-color);
    background: transparent;
    color: var(--text-secondary);
}
.rxe-bar-btn:hover {
    border-color: rgba(0,240,255,0.35);
    color: var(--cyan);
    background: rgba(0,240,255,0.06);
    text-decoration: none;
}
.rxe-bar-btn.primary {
    background: linear-gradient(135deg, var(--cyan), var(--purple));
    border-color: transparent;
    color: #06060a;
}
.rxe-bar-btn.primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(0,240,255,0.35);
    color: #06060a;
}

/* ── Body ───────────────────────────────────────────────────── */
.rxe-body {
    display: flex;
    flex: 1;
    min-height: 0;
    overflow: hidden;
}

/* ── Section nav ────────────────────────────────────────────── */
.rxe-nav {
    width: 200px;
    flex-shrink: 0;
    background: var(--bg-card);
    border-right: 1px solid var(--border-color);
    overflow-y: auto;
    padding: 12px 8px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.rxe-nav-group {
    font-size: 0.67rem;
    font-weight: 700;
    letter-spacing: 0.9px;
    text-transform: uppercase;
    color: var(--text-secondary);
    padding: 8px 10px 4px;
}
.rxe-nav-btn {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 9px 12px;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: var(--text-secondary);
    font-size: 0.84rem;
    font-weight: 500;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
    width: 100%;
}
.rxe-nav-btn:hover {
    background: rgba(0,240,255,0.06);
    color: var(--text-primary);
}
.rxe-nav-btn.active {
    background: rgba(0,240,255,0.1);
    color: var(--cyan);
    font-weight: 600;
}
.rxe-nav-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--border-color);
    flex-shrink: 0;
    transition: background 0.2s;
}
.rxe-nav-btn.active .rxe-nav-dot { background: var(--cyan); }

/* ── Form area ──────────────────────────────────────────────── */
.rxe-form-area {
    flex: 1;
    overflow-y: auto;
    padding: 28px 32px 80px;
    min-width: 0;
}
.rxe-panel { display: none; }
.rxe-panel.active { display: block; }

/* ── Section heading ────────────────────────────────────────── */
.rxe-section-heading {
    margin-bottom: 24px;
}
.rxe-section-heading h2 {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 4px;
}
.rxe-section-heading p {
    font-size: 0.83rem;
    color: var(--text-secondary);
    margin: 0;
}

/* ── Form grid ──────────────────────────────────────────────── */
.rxe-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}
.rxe-row.full { grid-template-columns: 1fr; }
.rxe-row.three { grid-template-columns: 1fr 1fr 1fr; }
.rxe-field {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.rxe-label {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    color: var(--text-secondary);
}
.rxe-input,
.rxe-textarea,
.rxe-select {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 0.88rem;
    font-family: 'Poppins', sans-serif;
    padding: 9px 12px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    width: 100%;
    box-sizing: border-box;
}
.rxe-input::placeholder,
.rxe-textarea::placeholder { color: var(--text-secondary); opacity: 0.55; }
.rxe-input:focus,
.rxe-textarea:focus,
.rxe-select:focus {
    border-color: rgba(0,240,255,0.5);
    box-shadow: 0 0 0 3px rgba(0,240,255,0.07);
}
.rxe-textarea { resize: vertical; min-height: 90px; line-height: 1.55; }
.rxe-select { appearance: none; cursor: pointer; }
.rxe-checkbox-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 4px;
}
.rxe-checkbox-row input[type="checkbox"] {
    width: 16px; height: 16px;
    accent-color: var(--cyan);
    cursor: pointer;
    flex-shrink: 0;
}
.rxe-checkbox-row label {
    font-size: 0.85rem;
    color: var(--text-secondary);
    cursor: pointer;
}

/* ── Item cards (repeatable sections) ───────────────────────── */
.rxe-items { display: flex; flex-direction: column; gap: 14px; margin-bottom: 16px; }
.rxe-item-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
    transition: border-color 0.2s;
}
.rxe-item-card:focus-within { border-color: rgba(0,240,255,0.3); }
.rxe-item-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
    cursor: pointer;
    user-select: none;
}
.rxe-item-head-title {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--text-primary);
}
.rxe-item-head-subtitle {
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-top: 2px;
}
.rxe-item-actions {
    display: flex;
    align-items: center;
    gap: 4px;
}
.rxe-btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px; height: 28px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--text-secondary);
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    font-size: 0.82rem;
    transition: all 0.15s;
}
.rxe-btn-icon:hover { background: rgba(255,255,255,0.06); color: var(--text-primary); }
.rxe-btn-icon.danger:hover { background: rgba(255,107,107,0.12); color: var(--red); }
.rxe-item-body { padding: 16px; display: block; }
.rxe-item-body.collapsed { display: none; }

.rxe-add-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 10px;
    border: 1px dashed rgba(0,240,255,0.35);
    background: transparent;
    color: var(--cyan);
    font-size: 0.84rem;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    transition: all 0.2s;
    width: 100%;
    justify-content: center;
}
.rxe-add-btn:hover {
    background: rgba(0,240,255,0.06);
    border-color: rgba(0,240,255,0.55);
}

/* ── Bullets list ───────────────────────────────────────────── */
.rxe-bullets { display: flex; flex-direction: column; gap: 6px; }
.rxe-bullet-row {
    display: flex;
    align-items: center;
    gap: 6px;
}
.rxe-bullet-row .rxe-input { flex: 1; }

/* ── Skills tags ────────────────────────────────────────────── */
.rxe-skills-area {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 10px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    min-height: 50px;
    transition: border-color 0.2s;
}
.rxe-skills-area:focus-within { border-color: rgba(0,240,255,0.5); }
.rxe-skill-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 20px;
    background: rgba(0,240,255,0.1);
    border: 1px solid rgba(0,240,255,0.25);
    color: var(--cyan);
    font-size: 0.8rem;
    font-weight: 600;
}
.rxe-skill-tag button {
    background: none; border: none; cursor: pointer;
    color: inherit; padding: 0; line-height: 1; opacity: 0.7;
    font-size: 0.75rem;
}
.rxe-skill-tag button:hover { opacity: 1; }
.rxe-skill-input {
    background: transparent;
    border: none;
    color: var(--text-primary);
    font-size: 0.85rem;
    font-family: 'Poppins', sans-serif;
    outline: none;
    min-width: 120px;
    flex: 1;
}
.rxe-skill-input::placeholder { color: var(--text-secondary); opacity: 0.5; }

/* ── AI assist ──────────────────────────────────────────────── */
.rxe-ai-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}
.rxe-ai-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    border: 1px solid rgba(153,69,255,0.35);
    background: rgba(153,69,255,0.08);
    color: var(--purple);
    font-size: 0.8rem;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    transition: all 0.2s;
}
.rxe-ai-btn:hover {
    background: rgba(153,69,255,0.15);
    border-color: rgba(153,69,255,0.55);
}
.rxe-ai-suggestions {
    display: none;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 14px;
    margin-top: 10px;
}
.rxe-ai-suggestions.open { display: block; }
.rxe-ai-suggestion-item {
    padding: 10px 12px;
    border-radius: 8px;
    font-size: 0.85rem;
    line-height: 1.5;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.15s;
    margin-bottom: 6px;
    border: 1px solid transparent;
}
.rxe-ai-suggestion-item:hover {
    background: rgba(0,240,255,0.06);
    border-color: rgba(0,240,255,0.2);
    color: var(--text-primary);
}
.rxe-ai-suggestion-item:last-child { margin-bottom: 0; }

/* ── Score bar ──────────────────────────────────────────────── */
.rxe-score-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}
.rxe-score-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.rxe-score-header h3 { font-size: 0.95rem; font-weight: 700; margin: 0; color: var(--text-primary); }
.rxe-score-num {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1;
    color: var(--cyan);
}
.rxe-score-grade {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-secondary);
    margin-top: 2px;
}
.rxe-score-bar-track {
    height: 6px;
    border-radius: 3px;
    background: var(--bg-secondary);
    overflow: hidden;
    margin-bottom: 12px;
}
.rxe-score-bar-fill {
    height: 100%;
    border-radius: 3px;
    background: linear-gradient(90deg, var(--cyan), var(--purple));
    transition: width 0.6s ease;
}
.rxe-score-suggestions { font-size: 0.8rem; color: var(--text-secondary); }
.rxe-score-suggestion { padding: 3px 0; }
.rxe-score-suggestion::before { content: '• '; color: var(--orange); }

/* ── Theme picker ───────────────────────────────────────────── */
.rxe-theme-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px;
    margin-bottom: 24px;
}
.rxe-theme-card {
    border-radius: 10px;
    border: 2px solid var(--border-color);
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s;
}
.rxe-theme-card:hover {
    transform: translateY(-2px);
    border-color: rgba(0,240,255,0.35);
}
.rxe-theme-card.active {
    border-color: var(--cyan);
    box-shadow: 0 0 0 3px rgba(0,240,255,0.15);
}
.rxe-theme-preview {
    height: 60px;
    display: flex;
    align-items: flex-start;
    padding: 8px;
}
.rxe-theme-preview-line {
    height: 5px;
    border-radius: 3px;
    width: 60%;
    margin-bottom: 4px;
}
.rxe-theme-preview-line.short { width: 40%; }
.rxe-theme-name {
    padding: 7px 10px;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-primary);
    border-top: 1px solid var(--border-color);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Section order ──────────────────────────────────────────── */
.rxe-section-order-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 16px;
}
.rxe-order-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--text-primary);
}
.rxe-order-item input[type="checkbox"] {
    accent-color: var(--cyan);
    width: 15px; height: 15px;
    flex-shrink: 0;
}
.rxe-order-drag { cursor: grab; color: var(--text-secondary); margin-left: auto; }

/* ── Toast notification ─────────────────────────────────────── */
.rxe-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    padding: 12px 20px;
    border-radius: 10px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    font-size: 0.85rem;
    font-weight: 500;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    z-index: 9999;
    transform: translateY(80px);
    opacity: 0;
    transition: transform 0.3s, opacity 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.rxe-toast.show { transform: translateY(0); opacity: 1; }
.rxe-toast.success { border-color: rgba(0,255,136,0.4); }
.rxe-toast.error { border-color: rgba(255,107,107,0.4); }

/* ── New badge ──────────────────────────────────────────────── */
.rxe-new-badge {
    background: linear-gradient(135deg, var(--cyan), var(--purple));
    color: #06060a;
    font-size: 0.7rem;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 4px;
    margin-left: 8px;
    letter-spacing: 0.5px;
}

/* ── Live preview pane ──────────────────────────────────────── */
.rxe-editor-main {
    display: flex;
    flex: 1;
    min-height: 0;
    overflow: hidden;
}
.rxe-editor-left {
    display: flex;
    flex: 1;
    min-width: 0;
    min-height: 0;
    overflow: hidden;
}
.rxe-splitter {
    width: 5px;
    flex-shrink: 0;
    background: var(--border-color);
    cursor: col-resize;
    transition: background 0.2s;
    position: relative;
    z-index: 10;
}
.rxe-splitter:hover, .rxe-splitter.dragging { background: var(--cyan); }
.rxe-preview-pane {
    width: 42%;
    flex-shrink: 0;
    background: #1a1a2e;
    border-left: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    min-width: 280px;
    overflow: hidden;
    transition: width 0.25s;
}
.rxe-preview-pane.hidden { width: 0 !important; min-width: 0; border: none; overflow: hidden; }
.rxe-preview-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 14px;
    background: var(--bg-card);
    border-bottom: 1px solid var(--border-color);
    flex-shrink: 0;
}
.rxe-preview-header span {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: var(--text-secondary);
}
.rxe-preview-header-btns { display: flex; gap: 8px; align-items: center; }
.rxe-preview-header-btn {
    background: none;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    color: var(--text-secondary);
    font-size: 0.72rem;
    font-family: 'Poppins', sans-serif;
    padding: 4px 9px;
    cursor: pointer;
    transition: all 0.2s;
}
.rxe-preview-header-btn:hover { border-color: var(--cyan); color: var(--cyan); }
.rxe-preview-iframe-wrap {
    flex: 1;
    overflow: auto;
    background: #1a1a2e;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 12px;
}
#rxe-preview-frame {
    width: 794px;
    min-height: 1123px;
    border: none;
    background: #fff;
    transform-origin: top left;
    display: block;
    box-shadow: 0 4px 32px rgba(0,0,0,0.5);
}
.rxe-preview-loading {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(10,10,15,0.6);
    font-size: 0.8rem;
    color: var(--text-secondary);
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.2s;
}
.rxe-preview-loading.show { opacity: 1; }

/* ── Custom modal ───────────────────────────────────────────── */
.rxe-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.65);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
}
.rxe-modal-overlay.open { opacity: 1; pointer-events: auto; }
.rxe-modal-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 24px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    transform: scale(0.95) translateY(-10px);
    transition: transform 0.2s;
}
.rxe-modal-overlay.open .rxe-modal-box { transform: scale(1) translateY(0); }
.rxe-modal-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}
.rxe-modal-btns { display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px; }

/* ── Better score breakdown ─────────────────────────────────── */
.rxe-score-ring-wrap {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}
.rxe-score-ring {
    position: relative;
    width: 90px;
    height: 90px;
    flex-shrink: 0;
}
.rxe-score-ring svg { transform: rotate(-90deg); }
.rxe-score-ring-text {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}
.rxe-score-ring-num { font-size: 1.5rem; font-weight: 800; line-height: 1; }
.rxe-score-ring-label { font-size: 0.62rem; font-weight: 700; color: var(--text-secondary); letter-spacing: 0.5px; margin-top: 2px; }
.rxe-score-info h3 { margin: 0 0 4px; font-size: 1.1rem; font-weight: 800; color: var(--text-primary); }
.rxe-score-grade-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    background: rgba(0,240,255,0.12);
    color: var(--cyan);
    border: 1px solid rgba(0,240,255,0.3);
}
.rxe-score-breakdown { margin: 0 0 16px; }
.rxe-score-breakdown-title {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: var(--text-secondary);
    margin-bottom: 10px;
}
.rxe-score-cat {
    margin-bottom: 10px;
}
.rxe-score-cat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 4px;
}
.rxe-score-cat-name { font-size: 0.8rem; font-weight: 600; color: var(--text-primary); }
.rxe-score-cat-pts { font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); }
.rxe-score-cat-bar {
    height: 5px;
    background: var(--bg-secondary);
    border-radius: 3px;
    overflow: hidden;
}
.rxe-score-cat-fill {
    height: 100%;
    border-radius: 3px;
    background: linear-gradient(90deg, var(--cyan), var(--purple));
    transition: width 0.6s ease;
}

/* ── AI suggestion item added state ─────────────────────────── */
.rxe-ai-suggestion-item.added {
    opacity: 0.4;
    text-decoration: line-through;
    cursor: default;
    pointer-events: none;
}

/* ── Responsive ─────────────────────────────────────────────── */
@media (max-width: 960px) {
    .rxe-preview-pane { display: none !important; }
    .rxe-splitter { display: none; }
    .rxe-btn-toggle-preview { display: none !important; }
}
@media (max-width: 768px) {
    .rxe-nav { width: 160px; }
    .rxe-form-area { padding: 16px 16px 60px; }
    .rxe-row { grid-template-columns: 1fr; }
    .rxe-row.three { grid-template-columns: 1fr; }
    .rxe-title-input { max-width: 160px; }
}
@media (max-width: 560px) {
    .rxe-nav { display: none; }
    .rxe-form-area { padding: 14px 14px 60px; }
}
</style>

<div class="rxe-wrap">

    <!-- Top bar -->
    <div class="rxe-bar">
        <a href="/projects/resumex" class="rxe-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Dashboard
        </a>
        <input id="resumeTitle" type="text" class="rxe-title-input"
               value="<?= htmlspecialchars($resume['title'] ?? 'My Resume', ENT_QUOTES, 'UTF-8') ?>"
               placeholder="Resume title" maxlength="255">
        <div class="rxe-bar-spacer"></div>
        <span id="saveStatus" class="rxe-save-status">All changes saved</span>
        <button type="button" class="rxe-bar-btn rxe-btn-toggle-preview" id="btnTogglePreview" onclick="togglePreviewPane()" title="Toggle live preview">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
            Preview
        </button>
        <button type="button" class="rxe-bar-btn" onclick="showSection('score'); scoreResume();" title="Analyse your resume">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            Score
        </button>
        <a href="/projects/resumex/preview/<?= (int)$resume['id'] ?>" target="_blank" class="rxe-bar-btn" title="Open preview in new tab">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            Preview
        </a>
        <a href="/projects/resumex/download/<?= (int)$resume['id'] ?>" target="_blank" class="rxe-bar-btn" title="Download / Print">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Download
        </a>
        <button type="button" class="rxe-bar-btn primary" onclick="saveResume()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
            Save
        </button>
    </div>

    <div class="rxe-body">
        <div class="rxe-editor-main">
        <div class="rxe-editor-left">
        <!-- Section navigation -->
        <nav class="rxe-nav">
            <div class="rxe-nav-group">Basics</div>
            <button type="button" class="rxe-nav-btn active" data-section="contact" onclick="showSection('contact')">
                <span class="rxe-nav-dot"></span> Contact Info
            </button>
            <button type="button" class="rxe-nav-btn" data-section="summary" onclick="showSection('summary')">
                <span class="rxe-nav-dot"></span> Summary
            </button>
            <div class="rxe-nav-group">Experience</div>
            <button type="button" class="rxe-nav-btn" data-section="experience" onclick="showSection('experience')">
                <span class="rxe-nav-dot"></span> Work Experience
            </button>
            <button type="button" class="rxe-nav-btn" data-section="education" onclick="showSection('education')">
                <span class="rxe-nav-dot"></span> Education
            </button>
            <button type="button" class="rxe-nav-btn" data-section="skills" onclick="showSection('skills')">
                <span class="rxe-nav-dot"></span> Skills
            </button>
            <div class="rxe-nav-group">More</div>
            <button type="button" class="rxe-nav-btn" data-section="projects" onclick="showSection('projects')">
                <span class="rxe-nav-dot"></span> Projects
            </button>
            <button type="button" class="rxe-nav-btn" data-section="certifications" onclick="showSection('certifications')">
                <span class="rxe-nav-dot"></span> Certifications
            </button>
            <button type="button" class="rxe-nav-btn" data-section="awards" onclick="showSection('awards')">
                <span class="rxe-nav-dot"></span> Awards
            </button>
            <button type="button" class="rxe-nav-btn" data-section="volunteer" onclick="showSection('volunteer')">
                <span class="rxe-nav-dot"></span> Volunteer
            </button>
            <button type="button" class="rxe-nav-btn" data-section="languages" onclick="showSection('languages')">
                <span class="rxe-nav-dot"></span> Languages
            </button>
            <button type="button" class="rxe-nav-btn" data-section="hobbies" onclick="showSection('hobbies')">
                <span class="rxe-nav-dot"></span> Hobbies
            </button>
            <button type="button" class="rxe-nav-btn" data-section="references" onclick="showSection('references')">
                <span class="rxe-nav-dot"></span> References
            </button>
            <button type="button" class="rxe-nav-btn" data-section="publications" onclick="showSection('publications')">
                <span class="rxe-nav-dot"></span> Publications
            </button>
            <div class="rxe-nav-group">Design</div>
            <button type="button" class="rxe-nav-btn" data-section="theme" onclick="showSection('theme')">
                <span class="rxe-nav-dot"></span> Theme &amp; Style
            </button>
            <button type="button" class="rxe-nav-btn" data-section="score" onclick="showSection('score'); scoreResume();">
                <span class="rxe-nav-dot"></span> Resume Score
            </button>
        </nav>

        <!-- Form panels -->
        <div class="rxe-form-area">

            <?php if (isset($_GET['new'])): ?>
            <div style="background: rgba(0,240,255,0.08); border: 1px solid rgba(0,240,255,0.25); border-radius: 10px; padding: 14px 18px; margin-bottom: 24px; font-size: 0.88rem; color: var(--cyan); display: flex; align-items: center; gap: 10px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                Your resume was created! Start filling in your details below.
                <span class="rxe-new-badge">NEW</span>
            </div>
            <?php endif; ?>

            <!-- Contact Info -->
            <div id="panel-contact" class="rxe-panel active">
                <div class="rxe-section-heading">
                    <h2>Contact Information</h2>
                    <p>Your personal details that will appear at the top of your resume.</p>
                </div>
                <div class="rxe-row">
                    <div class="rxe-field">
                        <label class="rxe-label">Full Name *</label>
                        <input class="rxe-input" id="c_name" type="text" placeholder="e.g. Jane Smith" maxlength="100">
                    </div>
                    <div class="rxe-field">
                        <label class="rxe-label">Email</label>
                        <input class="rxe-input" id="c_email" type="email" placeholder="jane@example.com" maxlength="120">
                    </div>
                </div>
                <div class="rxe-row">
                    <div class="rxe-field">
                        <label class="rxe-label">Phone</label>
                        <input class="rxe-input" id="c_phone" type="text" placeholder="+1 555 000 0000" maxlength="30">
                    </div>
                    <div class="rxe-field">
                        <label class="rxe-label">Location</label>
                        <input class="rxe-input" id="c_location" type="text" placeholder="City, Country" maxlength="100">
                    </div>
                </div>
                <div class="rxe-row">
                    <div class="rxe-field">
                        <label class="rxe-label">Website / Portfolio</label>
                        <input class="rxe-input" id="c_website" type="url" placeholder="https://yoursite.com" maxlength="200">
                    </div>
                    <div class="rxe-field">
                        <label class="rxe-label">LinkedIn</label>
                        <input class="rxe-input" id="c_linkedin" type="url" placeholder="https://linkedin.com/in/jane" maxlength="200">
                    </div>
                </div>
                <div class="rxe-row">
                    <div class="rxe-field">
                        <label class="rxe-label">GitHub</label>
                        <input class="rxe-input" id="c_github" type="url" placeholder="https://github.com/jane" maxlength="200">
                    </div>
                    <div class="rxe-field">
                        <label class="rxe-label">Photo URL <small style="text-transform: none; font-weight: 400">(optional)</small></label>
                        <input class="rxe-input" id="c_photo" type="url" placeholder="https://…/photo.jpg" maxlength="500">
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div id="panel-summary" class="rxe-panel">
                <div class="rxe-section-heading">
                    <h2>Professional Summary</h2>
                    <p>A short paragraph (2–4 sentences) summarising your experience and goals.</p>
                </div>
                <div class="rxe-ai-bar">
                    <button type="button" class="rxe-ai-btn" onclick="aiSuggestSummary()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        AI Suggest
                    </button>
                    <span id="sumCharCount" style="font-size: 0.78rem; color: var(--text-secondary);"></span>
                </div>
                <div id="aiSumSuggestions" class="rxe-ai-suggestions"></div>
                <div class="rxe-row full">
                    <div class="rxe-field">
                        <label class="rxe-label">Summary</label>
                        <textarea class="rxe-textarea" id="f_summary" rows="5"
                                  placeholder="Results-driven professional with X years of experience…"></textarea>
                    </div>
                </div>
            </div>

            <!-- Work Experience -->
            <div id="panel-experience" class="rxe-panel">
                <div class="rxe-section-heading">
                    <h2>Work Experience</h2>
                    <p>List your positions in reverse chronological order (most recent first).</p>
                </div>
                <div id="exp-list" class="rxe-items"></div>
                <button type="button" class="rxe-add-btn" onclick="addExperience()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Position
                </button>
            </div>

            <!-- Education -->
            <div id="panel-education" class="rxe-panel">
                <div class="rxe-section-heading">
                    <h2>Education</h2>
                    <p>Your academic qualifications.</p>
                </div>
                <div id="edu-list" class="rxe-items"></div>
                <button type="button" class="rxe-add-btn" onclick="addEducation()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Education
                </button>
            </div>

            <!-- Skills -->
            <div id="panel-skills" class="rxe-panel">
                <div class="rxe-section-heading">
                    <h2>Skills</h2>
                    <p>Type a skill and press <kbd>Enter</kbd> or comma to add it.</p>
                </div>
                <div class="rxe-ai-bar">
                    <button type="button" class="rxe-ai-btn" onclick="aiSuggestSkills()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        AI Suggest
                    </button>
                </div>
                <div id="aiSkillSuggestions" class="rxe-ai-suggestions"></div>
                <div id="skills-area" class="rxe-skills-area">
                    <input id="skillInput" class="rxe-skill-input" type="text"
                           placeholder="Type a skill and press Enter…">
                </div>
            </div>

            <!-- Projects -->
            <div id="panel-projects" class="rxe-panel">
                <div class="rxe-section-heading">
                    <h2>Projects</h2>
                    <p>Side projects, open source contributions, or notable work.</p>
                </div>
                <div id="proj-list" class="rxe-items"></div>
                <button type="button" class="rxe-add-btn" onclick="addProject()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Project
                </button>
            </div>

            <!-- Certifications -->
            <div id="panel-certifications" class="rxe-panel">
                <div class="rxe-section-heading">
                    <h2>Certifications</h2>
                    <p>Professional certificates and licences.</p>
                </div>
                <div id="cert-list" class="rxe-items"></div>
                <button type="button" class="rxe-add-btn" onclick="addCertification()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Certification
                </button>
            </div>

            <!-- Awards -->
            <div id="panel-awards" class="rxe-panel">
                <div class="rxe-section-heading">
                    <h2>Awards &amp; Achievements</h2>
                    <p>Prizes, honours, and recognitions.</p>
                </div>
                <div id="award-list" class="rxe-items"></div>
                <button type="button" class="rxe-add-btn" onclick="addAward()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Award
                </button>
            </div>

            <!-- Volunteer -->
            <div id="panel-volunteer" class="rxe-panel">
                <div class="rxe-section-heading">
                    <h2>Volunteer Work</h2>
                    <p>Community involvement and unpaid positions.</p>
                </div>
                <div id="vol-list" class="rxe-items"></div>
                <button type="button" class="rxe-add-btn" onclick="addVolunteer()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Volunteer Role
                </button>
            </div>

            <!-- Languages -->
            <div id="panel-languages" class="rxe-panel">
                <div class="rxe-section-heading">
                    <h2>Languages</h2>
                    <p>Languages you speak and your proficiency level.</p>
                </div>
                <div id="lang-list" class="rxe-items"></div>
                <button type="button" class="rxe-add-btn" onclick="addLanguage()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Language
                </button>
            </div>

            <!-- Hobbies -->
            <div id="panel-hobbies" class="rxe-panel">
                <div class="rxe-section-heading">
                    <h2>Hobbies &amp; Interests</h2>
                    <p>Type a hobby and press <kbd>Enter</kbd> or comma to add it.</p>
                </div>
                <div id="hobbies-area" class="rxe-skills-area">
                    <input id="hobbyInput" class="rxe-skill-input" type="text"
                           placeholder="Type a hobby and press Enter…">
                </div>
            </div>

            <!-- References -->
            <div id="panel-references" class="rxe-panel">
                <div class="rxe-section-heading">
                    <h2>References</h2>
                    <p>Professional references who can vouch for you.</p>
                </div>
                <div id="ref-list" class="rxe-items"></div>
                <button type="button" class="rxe-add-btn" onclick="addReference()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Reference
                </button>
            </div>

            <!-- Publications -->
            <div id="panel-publications" class="rxe-panel">
                <div class="rxe-section-heading">
                    <h2>Publications</h2>
                    <p>Research papers, articles, or books you have published.</p>
                </div>
                <div id="pub-list" class="rxe-items"></div>
                <button type="button" class="rxe-add-btn" onclick="addPublication()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Publication
                </button>
            </div>

            <!-- Theme & Style -->
            <div id="panel-theme" class="rxe-panel">
                <div class="rxe-section-heading">
                    <h2>Theme &amp; Style</h2>
                    <p>Choose a colour theme for your resume.</p>
                </div>
                <div id="theme-grid" class="rxe-theme-grid"></div>

                <div class="rxe-section-heading" style="margin-top: 24px;">
                    <h2>Section Visibility &amp; Order</h2>
                    <p>Uncheck sections to hide them on your resume.</p>
                </div>
                <div id="section-order-list" class="rxe-section-order-list"></div>
            </div>

            <!-- Resume Score -->
            <div id="panel-score" class="rxe-panel">
                <div class="rxe-section-heading">
                    <h2>Resume Score</h2>
                    <p>See how complete and strong your resume is across all sections.</p>
                </div>
                <div class="rxe-score-box">
                    <div class="rxe-score-ring-wrap">
                        <div class="rxe-score-ring">
                            <svg width="90" height="90" viewBox="0 0 90 90">
                                <circle cx="45" cy="45" r="38" fill="none" stroke="var(--bg-secondary)" stroke-width="8"/>
                                <circle id="scoreRingCircle" cx="45" cy="45" r="38" fill="none" stroke="var(--cyan)" stroke-width="8"
                                    stroke-dasharray="238.76" stroke-dashoffset="238.76"
                                    stroke-linecap="round" style="transition:stroke-dashoffset 0.7s ease, stroke 0.4s;"/>
                            </svg>
                            <div class="rxe-score-ring-text">
                                <span id="scoreNum" class="rxe-score-ring-num" style="color:var(--cyan)">—</span>
                                <span class="rxe-score-ring-label">/100</span>
                            </div>
                        </div>
                        <div class="rxe-score-info">
                            <h3 id="scoreLabel">Run Analysis</h3>
                            <span id="scoreGrade" class="rxe-score-grade-badge">—</span>
                        </div>
                    </div>
                    <div class="rxe-score-breakdown">
                        <div class="rxe-score-breakdown-title">Section Breakdown</div>
                        <div id="scoreBreakdown"></div>
                    </div>
                    <div id="scoreSuggestions" class="rxe-score-suggestions"></div>
                </div>
                <button type="button" class="rxe-bar-btn primary" onclick="scoreResume()" style="width:100%; justify-content:center; padding: 12px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Analyse Resume
                </button>
            </div>

        </div><!-- /rxe-form-area -->
        </div><!-- /rxe-editor-left -->

        <!-- Splitter -->
        <div class="rxe-splitter" id="rxe-splitter"></div>

        <!-- Live preview pane -->
        <div class="rxe-preview-pane" id="rxe-preview-pane">
            <div class="rxe-preview-header">
                <span>&#128064; Live Preview</span>
                <div class="rxe-preview-header-btns">
                    <button type="button" class="rxe-preview-header-btn" onclick="updateLivePreview()">&#8635; Refresh</button>
                    <a href="/projects/resumex/preview/<?= (int)$resume['id'] ?>" target="_blank" class="rxe-preview-header-btn">&#10138; Full page</a>
                </div>
            </div>
            <div class="rxe-preview-iframe-wrap" id="rxe-preview-iframe-wrap">
                <iframe id="rxe-preview-frame" sandbox="allow-same-origin" title="Resume preview"></iframe>
            </div>
        </div>

        </div><!-- /rxe-editor-main -->
    </div><!-- /rxe-body -->
</div><!-- /rxe-wrap -->

<!-- Custom prompt modal -->
<div id="rxe-prompt-modal" class="rxe-modal-overlay" role="dialog" aria-modal="true">
    <div class="rxe-modal-box">
        <div class="rxe-modal-title" id="rxe-prompt-title">Enter value</div>
        <input id="rxe-prompt-input" class="rxe-input" type="text" placeholder="" style="margin-top:12px;width:100%;">
        <div class="rxe-modal-btns">
            <button type="button" class="rxe-bar-btn" id="rxe-prompt-cancel">Cancel</button>
            <button type="button" class="rxe-bar-btn primary" id="rxe-prompt-ok">OK</button>
        </div>
    </div>
</div>

<div id="rxe-toast" class="rxe-toast"></div>

<script>
(function () {
'use strict';

/* ── Initial data from PHP ──────────────────────────────────── */
var resumeData    = <?= json_encode($resumeData,    JSON_HEX_TAG | JSON_HEX_APOS) ?>;
var themeSettings = <?= json_encode($themeSettings, JSON_HEX_TAG | JSON_HEX_APOS) ?>;
var allThemes     = <?= json_encode($allThemes,     JSON_HEX_TAG | JSON_HEX_APOS) ?>;
var csrfToken     = <?= json_encode($csrfToken) ?>;
var resumeId      = <?= (int)$resume['id'] ?>;

/* ── Ensure resumeData defaults ─────────────────────────────── */
resumeData.contact        = resumeData.contact        || {};
resumeData.summary        = resumeData.summary        || '';
resumeData.experience     = resumeData.experience     || [];
resumeData.education      = resumeData.education      || [];
resumeData.skills         = resumeData.skills         || [];
resumeData.projects       = resumeData.projects       || [];
resumeData.certifications = resumeData.certifications || [];
resumeData.awards         = resumeData.awards         || [];
resumeData.volunteer      = resumeData.volunteer      || [];
resumeData.languages      = resumeData.languages      || [];
resumeData.hobbies        = resumeData.hobbies        || [];
resumeData.references     = resumeData.references     || [];
resumeData.publications   = resumeData.publications   || [];
resumeData.hidden_sections = resumeData.hidden_sections || [];
resumeData.section_order  = resumeData.section_order  || ['contact','summary','experience','education','skills','projects','certifications','awards','volunteer','languages','hobbies','references','publications'];

/* ── Custom modal (replaces browser prompt) ─────────────────── */
var _modalCallback = null;
(function initModal() {
    var modal  = document.getElementById('rxe-prompt-modal');
    var input  = document.getElementById('rxe-prompt-input');
    var title  = document.getElementById('rxe-prompt-title');
    var btnOk  = document.getElementById('rxe-prompt-ok');
    var btnCan = document.getElementById('rxe-prompt-cancel');
    function close(val) {
        modal.classList.remove('open');
        if (_modalCallback) { _modalCallback(val); _modalCallback = null; }
    }
    btnOk.addEventListener('click', function() { close(input.value.trim()); });
    btnCan.addEventListener('click', function() { close(null); });
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); close(input.value.trim()); }
        if (e.key === 'Escape') { close(null); }
    });
    modal.addEventListener('click', function(e) { if (e.target === modal) close(null); });
    window.openPromptModal = function(titleText, placeholder, defaultVal, cb) {
        title.textContent = titleText;
        input.placeholder = placeholder || '';
        input.value = defaultVal || '';
        _modalCallback = cb;
        modal.classList.add('open');
        setTimeout(function() { input.focus(); input.select(); }, 100);
    };
}());

/* ── Live Preview ────────────────────────────────────────────── */
var previewVisible = true;
var previewTimer = null;

window.togglePreviewPane = function() {
    var pane = document.getElementById('rxe-preview-pane');
    var splitter = document.getElementById('rxe-splitter');
    previewVisible = !previewVisible;
    if (previewVisible) {
        pane.classList.remove('hidden');
        splitter.style.display = '';
        document.getElementById('btnTogglePreview').style.borderColor = 'rgba(0,240,255,0.35)';
        document.getElementById('btnTogglePreview').style.color = 'var(--cyan)';
        updateLivePreview();
    } else {
        pane.classList.add('hidden');
        splitter.style.display = 'none';
        document.getElementById('btnTogglePreview').style.borderColor = '';
        document.getElementById('btnTogglePreview').style.color = '';
    }
};

function schedulePreviewUpdate() {
    if (!previewVisible) return;
    clearTimeout(previewTimer);
    previewTimer = setTimeout(updateLivePreview, 600);
}

window.updateLivePreview = function() {
    var frame = document.getElementById('rxe-preview-frame');
    if (!frame) return;
    var wrap = document.getElementById('rxe-preview-iframe-wrap');
    var paneW = wrap ? wrap.clientWidth - 24 : 0;
    var scale = paneW > 0 ? Math.min(1, paneW / 794) : 1;
    frame.style.transform = 'scale(' + scale + ')';
    frame.style.marginBottom = Math.round((1123 * scale) - 1123 + 12) + 'px';
    frame.srcdoc = buildPreviewHTML();
};

function he(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function buildPreviewHTML() {
    var t   = themeSettings;
    var d   = resumeData;
    var pri = t.primaryColor    || '#00f0ff';
    var sec = t.secondaryColor  || '#9945ff';
    var bg  = t.backgroundColor || '#0a0a0f';
    var sur = t.surfaceColor    || '#12121e';
    var txt = t.textColor       || '#e0e6ff';
    var mut = t.textMuted       || '#6b7280';
    var brd = t.borderColor     || 'rgba(0,240,255,0.15)';
    var ff  = t.fontFamily      || 'Poppins';
    var fs  = t.fontSize        || 14;
    var contact   = d.contact   || {};
    var hidden    = d.hidden_sections || [];
    var order     = d.section_order   || ['contact','summary','experience','education','skills','projects','certifications','awards','volunteer','languages','hobbies','references','publications'];
    var twoCol    = (t.layoutMode || 'single') === 'two-column';
    var spacing   = t.spacing || 'comfortable';
    var pad = spacing === 'compact' ? '14px' : (spacing === 'spacious' ? '28px' : '20px');

    function isHidden(s) { return hidden.indexOf(s) !== -1; }

    function sectionHtml(key) {
        if (isHidden(key)) return '';
        var sections = {
            contact: function() {
                var name = he(contact.name || '');
                var sub  = [contact.email, contact.phone, contact.location].filter(Boolean).map(he).join(' &nbsp;·&nbsp; ');
                var links= [contact.website, contact.linkedin, contact.github].filter(Boolean);
                if (!name && !sub) return '';
                return '<div style="margin-bottom:' + pad + ';padding-bottom:' + pad + ';border-bottom:1px solid ' + brd + '">' +
                    (name ? '<h1 style="margin:0 0 4px;font-size:1.8em;font-weight:800;background:linear-gradient(135deg,' + pri + ',' + sec + ');-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">' + name + '</h1>' : '') +
                    (sub  ? '<p style="margin:0 0 4px;font-size:0.85em;color:' + mut + '">' + sub + '</p>' : '') +
                    (links.length ? '<p style="margin:0;font-size:0.78em;color:' + mut + '">' + links.map(he).join(' &nbsp;·&nbsp; ') + '</p>' : '') +
                '</div>';
            },
            summary: function() {
                var s = d.summary || '';
                if (!s.trim()) return '';
                return '<div style="margin-bottom:' + pad + '">' +
                    '<h2 style="margin:0 0 8px;font-size:0.72em;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:' + pri + '">Summary</h2>' +
                    '<p style="margin:0;font-size:0.88em;line-height:1.6;color:' + txt + '">' + he(s) + '</p>' +
                '</div>';
            },
            experience: function() {
                var exp = d.experience || [];
                if (!exp.length) return '';
                return '<div style="margin-bottom:' + pad + '">' +
                    '<h2 style="margin:0 0 10px;font-size:0.72em;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:' + pri + '">Experience</h2>' +
                    exp.map(function(e) {
                        var bullets = (e.bullets||[]).filter(Boolean);
                        return '<div style="margin-bottom:12px">' +
                            '<div style="display:flex;justify-content:space-between;align-items:baseline">' +
                                '<span style="font-weight:700;font-size:0.9em;color:' + txt + '">' + he(e.title||'') + '</span>' +
                                '<span style="font-size:0.75em;color:' + mut + '">' + he(e.start_date||'') + (e.start_date ? ' – ' : '') + (e.current ? 'Present' : he(e.end_date||'')) + '</span>' +
                            '</div>' +
                            '<div style="font-size:0.8em;color:' + pri + ';margin-bottom:4px">' + he(e.company||'') + (e.location ? ' &nbsp;·&nbsp; <span style="color:' + mut + '">' + he(e.location) + '</span>' : '') + '</div>' +
                            (e.description ? '<p style="margin:0 0 4px;font-size:0.82em;color:' + mut + '">' + he(e.description) + '</p>' : '') +
                            (bullets.length ? '<ul style="margin:4px 0 0 16px;padding:0">' + bullets.map(function(b){ return '<li style="font-size:0.82em;color:' + txt + ';margin-bottom:2px">' + he(b) + '</li>'; }).join('') + '</ul>' : '') +
                        '</div>';
                    }).join('') +
                '</div>';
            },
            education: function() {
                var edu = d.education || [];
                if (!edu.length) return '';
                return '<div style="margin-bottom:' + pad + '">' +
                    '<h2 style="margin:0 0 10px;font-size:0.72em;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:' + pri + '">Education</h2>' +
                    edu.map(function(e) {
                        return '<div style="margin-bottom:8px">' +
                            '<div style="display:flex;justify-content:space-between;align-items:baseline">' +
                                '<span style="font-weight:700;font-size:0.9em;color:' + txt + '">' + he(e.school||'') + '</span>' +
                                '<span style="font-size:0.75em;color:' + mut + '">' + he(e.start_date||'') + (e.start_date && e.end_date ? ' – ' : '') + he(e.end_date||'') + '</span>' +
                            '</div>' +
                            '<div style="font-size:0.82em;color:' + mut + '">' + he(e.degree||'') + (e.field ? ' · ' + he(e.field) : '') + (e.gpa ? ' · GPA: ' + he(e.gpa) : '') + '</div>' +
                        '</div>';
                    }).join('') +
                '</div>';
            },
            skills: function() {
                var skills = d.skills || [];
                if (!skills.length) return '';
                return '<div style="margin-bottom:' + pad + '">' +
                    '<h2 style="margin:0 0 8px;font-size:0.72em;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:' + pri + '">Skills</h2>' +
                    '<div style="display:flex;flex-wrap:wrap;gap:6px">' +
                    skills.map(function(s) {
                        var n = typeof s === 'string' ? s : (s.name || '');
                        return '<span style="padding:3px 10px;border-radius:20px;font-size:0.78em;font-weight:600;background:' + pri + '22;color:' + pri + ';border:1px solid ' + pri + '44">' + he(n) + '</span>';
                    }).join('') +
                    '</div></div>';
            },
            projects: function() {
                var pp = d.projects || [];
                if (!pp.length) return '';
                return '<div style="margin-bottom:' + pad + '">' +
                    '<h2 style="margin:0 0 10px;font-size:0.72em;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:' + pri + '">Projects</h2>' +
                    pp.map(function(p) {
                        return '<div style="margin-bottom:8px">' +
                            '<span style="font-weight:700;font-size:0.88em;color:' + txt + '">' + he(p.name||'') + '</span>' +
                            (p.technologies ? '<span style="font-size:0.75em;color:' + mut + ';margin-left:6px">' + (Array.isArray(p.technologies) ? p.technologies.join(', ') : he(p.technologies)) + '</span>' : '') +
                            (p.description ? '<p style="margin:2px 0 0;font-size:0.8em;color:' + mut + '">' + he(p.description) + '</p>' : '') +
                        '</div>';
                    }).join('') +
                '</div>';
            },
            certifications: function() {
                var cc = d.certifications || [];
                if (!cc.length) return '';
                return '<div style="margin-bottom:' + pad + '">' +
                    '<h2 style="margin:0 0 10px;font-size:0.72em;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:' + pri + '">Certifications</h2>' +
                    cc.map(function(c) {
                        return '<div style="margin-bottom:6px;display:flex;justify-content:space-between">' +
                            '<span style="font-size:0.85em;color:' + txt + ';font-weight:600">' + he(c.name||'') + (c.issuer ? ' <span style="color:' + mut + ';font-weight:400">· ' + he(c.issuer) + '</span>' : '') + '</span>' +
                            (c.date ? '<span style="font-size:0.75em;color:' + mut + '">' + he(c.date) + '</span>' : '') +
                        '</div>';
                    }).join('') +
                '</div>';
            },
            awards: function() {
                var aa = d.awards || [];
                if (!aa.length) return '';
                return '<div style="margin-bottom:' + pad + '">' +
                    '<h2 style="margin:0 0 10px;font-size:0.72em;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:' + pri + '">Awards</h2>' +
                    aa.map(function(a) {
                        return '<div style="margin-bottom:6px">' +
                            '<span style="font-size:0.85em;color:' + txt + ';font-weight:600">' + he(a.title||'') + '</span>' +
                            (a.issuer ? ' <span style="font-size:0.78em;color:' + mut + '">· ' + he(a.issuer) + '</span>' : '') +
                            (a.date ? ' <span style="font-size:0.75em;color:' + mut + '">(' + he(a.date) + ')</span>' : '') +
                        '</div>';
                    }).join('') +
                '</div>';
            },
            volunteer: function() {
                var vv = d.volunteer || [];
                if (!vv.length) return '';
                return '<div style="margin-bottom:' + pad + '">' +
                    '<h2 style="margin:0 0 10px;font-size:0.72em;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:' + pri + '">Volunteer</h2>' +
                    vv.map(function(v) {
                        return '<div style="margin-bottom:8px">' +
                            '<span style="font-weight:700;font-size:0.88em;color:' + txt + '">' + he(v.role||'') + '</span>' +
                            (v.organization ? ' <span style="font-size:0.8em;color:' + pri + '">@ ' + he(v.organization) + '</span>' : '') +
                            (v.description ? '<p style="margin:2px 0 0;font-size:0.8em;color:' + mut + '">' + he(v.description) + '</p>' : '') +
                        '</div>';
                    }).join('') +
                '</div>';
            },
            languages: function() {
                var ll = d.languages || [];
                if (!ll.length) return '';
                return '<div style="margin-bottom:' + pad + '">' +
                    '<h2 style="margin:0 0 8px;font-size:0.72em;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:' + pri + '">Languages</h2>' +
                    '<div style="display:flex;flex-wrap:wrap;gap:6px">' +
                    ll.map(function(l) {
                        return '<span style="padding:3px 10px;border-radius:20px;font-size:0.78em;border:1px solid ' + brd + ';color:' + txt + '">' + he(l.language||'') + (l.level ? ' · <em style="color:' + mut + '">' + he(l.level) + '</em>' : '') + '</span>';
                    }).join('') + '</div></div>';
            },
            hobbies: function() {
                var hh = d.hobbies || [];
                if (!hh.length) return '';
                return '<div style="margin-bottom:' + pad + '">' +
                    '<h2 style="margin:0 0 8px;font-size:0.72em;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:' + pri + '">Hobbies</h2>' +
                    '<p style="margin:0;font-size:0.85em;color:' + txt + '">' + hh.map(he).join(' &nbsp;·&nbsp; ') + '</p>' +
                '</div>';
            },
            references: function() {
                var rr = d.references || [];
                if (!rr.length) return '';
                return '<div style="margin-bottom:' + pad + '">' +
                    '<h2 style="margin:0 0 10px;font-size:0.72em;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:' + pri + '">References</h2>' +
                    rr.map(function(r) {
                        return '<div style="margin-bottom:8px">' +
                            '<span style="font-weight:700;font-size:0.88em;color:' + txt + '">' + he(r.name||'') + '</span>' +
                            (r.title || r.company ? '<div style="font-size:0.78em;color:' + mut + '">' + [r.title, r.company].filter(Boolean).map(he).join(', ') + '</div>' : '') +
                            (r.email ? '<div style="font-size:0.75em;color:' + mut + '">' + he(r.email) + (r.phone ? ' · ' + he(r.phone) : '') + '</div>' : '') +
                        '</div>';
                    }).join('') +
                '</div>';
            },
            publications: function() {
                var pp = d.publications || [];
                if (!pp.length) return '';
                return '<div style="margin-bottom:' + pad + '">' +
                    '<h2 style="margin:0 0 10px;font-size:0.72em;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:' + pri + '">Publications</h2>' +
                    pp.map(function(p) {
                        return '<div style="margin-bottom:8px">' +
                            '<span style="font-weight:700;font-size:0.88em;color:' + txt + '">' + he(p.title||'') + '</span>' +
                            (p.journal ? '<span style="font-size:0.78em;color:' + mut + '"> · ' + he(p.journal) + '</span>' : '') +
                            (p.date ? '<span style="font-size:0.75em;color:' + mut + '"> (' + he(p.date) + ')</span>' : '') +
                        '</div>';
                    }).join('') +
                '</div>';
            }
        };
        return sections[key] ? sections[key]() : '';
    }

    var body = order.map(sectionHtml).join('');

    return '<!DOCTYPE html><html><head><meta charset="utf-8">' +
        '<link rel="preconnect" href="https://fonts.googleapis.com">' +
        '<link href="https://fonts.googleapis.com/css2?family=' + encodeURIComponent(ff) + ':wght@400;500;600;700;800&display=swap" rel="stylesheet">' +
        '<style>' +
        '*{box-sizing:border-box;margin:0;padding:0}' +
        'body{font-family:"' + ff + '",sans-serif;font-size:' + fs + 'px;background:' + bg + ';color:' + txt + ';padding:' + pad + ';line-height:1.5;min-height:1123px;}' +
        'ul{list-style:disc}' +
        '</style></head><body>' +
        body + '</body></html>';
}

/* ── Splitter resize ─────────────────────────────────────────── */
(function initSplitter() {
    var splitter = document.getElementById('rxe-splitter');
    var previewPane = document.getElementById('rxe-preview-pane');
    var isDragging = false;
    splitter.addEventListener('mousedown', function(e) {
        isDragging = true;
        splitter.classList.add('dragging');
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';
        e.preventDefault();
    });
    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        var wrap = document.querySelector('.rxe-editor-main');
        var rect = wrap.getBoundingClientRect();
        var totalW = rect.width;
        var fromRight = rect.right - e.clientX;
        var pct = Math.min(70, Math.max(20, (fromRight / totalW) * 100));
        previewPane.style.width = pct + '%';
        schedulePreviewUpdate();
    });
    document.addEventListener('mouseup', function() {
        if (!isDragging) return;
        isDragging = false;
        splitter.classList.remove('dragging');
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
    });
}());

/* ── Utility helpers ────────────────────────────────────────── */
function esc(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function val(obj, key) { return obj && obj[key] != null ? obj[key] : ''; }

/* ── Save state ─────────────────────────────────────────────── */
var saveTimer = null;
var saveStatusEl = document.getElementById('saveStatus');

function markDirty() {
    saveStatusEl.textContent = 'Unsaved changes…';
    clearTimeout(saveTimer);
    saveTimer = setTimeout(saveResume, 3000);
    schedulePreviewUpdate();
}

/* ── Show/hide sections ─────────────────────────────────────── */
window.showSection = function (name) {
    document.querySelectorAll('.rxe-panel').forEach(function (p) { p.classList.remove('active'); });
    document.querySelectorAll('.rxe-nav-btn').forEach(function (b) { b.classList.remove('active'); });
    var panel = document.getElementById('panel-' + name);
    var btn   = document.querySelector('[data-section="' + name + '"]');
    if (panel) panel.classList.add('active');
    if (btn)   btn.classList.add('active');
};

/* ── Save (AJAX) ────────────────────────────────────────────── */
window.saveResume = function () {
    clearTimeout(saveTimer);
    readContactFromDOM();
    readSummaryFromDOM();
    var title = (document.getElementById('resumeTitle').value || 'My Resume').trim();

    saveStatusEl.textContent = 'Saving…';
    fetch('/projects/resumex/edit/' + resumeId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({
            _token: csrfToken,
            title: title,
            template: themeSettings.key || 'midnight-pro',
            resume_data: resumeData,
            theme_settings: themeSettings
        })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success) {
            saveStatusEl.textContent = 'Saved ✓';
            showToast('Saved successfully', 'success');
            setTimeout(function () { saveStatusEl.textContent = 'All changes saved'; }, 3000);
        } else {
            saveStatusEl.textContent = 'Save failed';
            showToast('Save failed. Please try again.', 'error');
        }
    })
    .catch(function () {
        saveStatusEl.textContent = 'Network error';
        showToast('Network error. Check your connection.', 'error');
    });
};

/* ── Toast ──────────────────────────────────────────────────── */
var toastEl = document.getElementById('rxe-toast');
var toastTimer;
function showToast(msg, type) {
    clearTimeout(toastTimer);
    toastEl.textContent = msg;
    toastEl.className = 'rxe-toast ' + (type || '');
    requestAnimationFrame(function () { toastEl.classList.add('show'); });
    toastTimer = setTimeout(function () { toastEl.classList.remove('show'); }, 3500);
}

/* ── Keyboard shortcut (Ctrl/Cmd + S) ──────────────────────── */
document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        saveResume();
    }
});

/* ══════════════════════════════════════════════════════════════
   CONTACT
══════════════════════════════════════════════════════════════ */
function initContact() {
    var c = resumeData.contact || {};
    var fields = ['name','email','phone','location','website','linkedin','github','photo'];
    fields.forEach(function (f) {
        var el = document.getElementById('c_' + f);
        if (!el) return;
        el.value = val(c, f);
        el.addEventListener('input', function () { markDirty(); });
    });
}
function readContactFromDOM() {
    if (!resumeData.contact) resumeData.contact = {};
    var fields = ['name','email','phone','location','website','linkedin','github','photo'];
    fields.forEach(function (f) {
        var el = document.getElementById('c_' + f);
        if (el) resumeData.contact[f] = el.value.trim();
    });
}

/* ══════════════════════════════════════════════════════════════
   SUMMARY
══════════════════════════════════════════════════════════════ */
function initSummary() {
    var el = document.getElementById('f_summary');
    el.value = resumeData.summary || '';
    el.addEventListener('input', function () {
        resumeData.summary = el.value;
        document.getElementById('sumCharCount').textContent = el.value.length + ' chars';
        markDirty();
    });
    document.getElementById('sumCharCount').textContent = el.value.length + ' chars';
}
function readSummaryFromDOM() {
    resumeData.summary = (document.getElementById('f_summary').value || '');
}

/* ── AI Summary suggestions ─────────────────────────────────── */
window.aiSuggestSummary = function () {
    var jobTitle = resumeData.experience && resumeData.experience[0]
        ? (resumeData.experience[0].title || '')
        : '';
    function doFetch(jt) {
        var expYears = resumeData.experience ? resumeData.experience.length * 2 : 0;
        var skillStr = (resumeData.skills || []).slice(0, 5).map(function (s) {
            return typeof s === 'string' ? s : (s.name || '');
        }).join(', ');
        var fd = new FormData();
        fd.append('_token', csrfToken);
        fd.append('job_title', jt);
        fd.append('experience_years', expYears);
        fd.append('skills', skillStr);
        fetch('/projects/resumex/ai/suggest-summary', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) return;
            var box = document.getElementById('aiSumSuggestions');
            box.innerHTML = '<div style="font-size:0.75rem;font-weight:700;color:var(--text-secondary);margin-bottom:8px;">Click a suggestion to use it:</div>' +
                data.suggestions.map(function (s) {
                    return '<div class="rxe-ai-suggestion-item" onclick="useSummarySuggestion(this.textContent)">' + esc(s) + '</div>';
                }).join('');
            box.classList.add('open');
        });
    }
    if (jobTitle) {
        doFetch(jobTitle);
    } else {
        openPromptModal('Enter your job title for AI suggestions', 'e.g. Software Engineer', '', function(t) {
            if (t) doFetch(t);
        });
    }
};
window.useSummarySuggestion = function (text) {
    var el = document.getElementById('f_summary');
    el.value = text;
    resumeData.summary = text;
    document.getElementById('aiSumSuggestions').classList.remove('open');
    markDirty();
};

/* ══════════════════════════════════════════════════════════════
   EXPERIENCE
══════════════════════════════════════════════════════════════ */
function renderExperience() {
    var list = document.getElementById('exp-list');
    if (!resumeData.experience || !resumeData.experience.length) { list.innerHTML = ''; return; }
    list.innerHTML = resumeData.experience.map(function (exp, i) {
        var title = val(exp,'title') || 'New Position';
        var company = val(exp,'company') ? ' at ' + esc(val(exp,'company')) : '';
        var bullets = (exp.bullets || []).map(function (b, bi) {
            return '<div class="rxe-bullet-row">' +
                '<input class="rxe-input" type="text" value="' + esc(b) + '" placeholder="Bullet point…" ' +
                    'oninput="resumeData.experience[' + i + '].bullets[' + bi + ']=this.value; markDirty();">' +
                '<button type="button" class="rxe-btn-icon danger" title="Remove bullet" ' +
                    'onclick="removeExpBullet(' + i + ',' + bi + ')">✕</button>' +
            '</div>';
        }).join('');
        return '<div class="rxe-item-card">' +
            '<div class="rxe-item-head" onclick="toggleItem(this)">' +
                '<div>' +
                    '<div class="rxe-item-head-title">' + esc(title) + esc(company) + '</div>' +
                    '<div class="rxe-item-head-subtitle">' + esc(val(exp,'start_date')) + (val(exp,'start_date') ? ' – ' + (exp.current ? 'Present' : esc(val(exp,'end_date'))) : '') + '</div>' +
                '</div>' +
                '<div class="rxe-item-actions">' +
                    (i > 0 ? '<button type="button" class="rxe-btn-icon" title="Move up" onclick="event.stopPropagation(); moveExp(' + i + ',-1)">↑</button>' : '') +
                    (i < resumeData.experience.length - 1 ? '<button type="button" class="rxe-btn-icon" title="Move down" onclick="event.stopPropagation(); moveExp(' + i + ',1)">↓</button>' : '') +
                    '<button type="button" class="rxe-btn-icon danger" title="Remove" onclick="event.stopPropagation(); removeExperience(' + i + ')">✕</button>' +
                '</div>' +
            '</div>' +
            '<div class="rxe-item-body">' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">Job Title</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(exp,'title')) + '" placeholder="e.g. Software Engineer" oninput="resumeData.experience[' + i + '].title=this.value; updateExpHead(' + i + ',this); markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">Company</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(exp,'company')) + '" placeholder="e.g. Acme Corp" oninput="resumeData.experience[' + i + '].company=this.value; updateExpHead(' + i + ',this); markDirty();"></div></div>' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">Location</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(exp,'location')) + '" placeholder="City, Country" oninput="resumeData.experience[' + i + '].location=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">Start Date</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(exp,'start_date')) + '" placeholder="Jan 2020" oninput="resumeData.experience[' + i + '].start_date=this.value; markDirty();"></div></div>' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">End Date</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(exp,'end_date')) + '" placeholder="Dec 2023" ' + (exp.current ? 'disabled' : '') + ' oninput="resumeData.experience[' + i + '].end_date=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">&nbsp;</label>' +
                    '<div class="rxe-checkbox-row"><input type="checkbox" id="expCurr' + i + '" ' + (exp.current ? 'checked' : '') + ' onchange="resumeData.experience[' + i + '].current=this.checked; renderExperience(); markDirty();">' +
                    '<label for="expCurr' + i + '">Currently working here</label></div></div></div>' +
                '<div class="rxe-row full"><div class="rxe-field"><label class="rxe-label">Description</label>' +
                    '<textarea class="rxe-textarea" placeholder="Brief description of responsibilities…" oninput="resumeData.experience[' + i + '].description=this.value; markDirty();">' + esc(val(exp,'description')) + '</textarea></div></div>' +
                '<div class="rxe-field" style="margin-bottom:8px"><label class="rxe-label">Bullet Points</label>' +
                    '<div class="rxe-bullets" id="expBullets' + i + '">' + bullets + '</div>' +
                    '<button type="button" class="rxe-add-btn" style="margin-top:6px;" onclick="addExpBullet(' + i + ')">+ Add Bullet</button></div>' +
                '<div style="margin-top:8px;">' +
                    '<button type="button" class="rxe-ai-btn" onclick="aiSuggestBullets(' + i + ')">' +
                    '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg> AI Bullet Suggestions</button></div>' +
                '<div id="aiBullets' + i + '" class="rxe-ai-suggestions"></div>' +
            '</div>' +
        '</div>';
    }).join('');
}
window.addExperience = function () {
    resumeData.experience.push({ title:'', company:'', location:'', start_date:'', end_date:'', current:false, description:'', bullets:[] });
    renderExperience();
    markDirty();
};
window.removeExperience = function (i) {
    resumeData.experience.splice(i, 1);
    renderExperience();
    markDirty();
};
window.moveExp = function (i, dir) {
    var j = i + dir;
    if (j < 0 || j >= resumeData.experience.length) return;
    var tmp = resumeData.experience[i];
    resumeData.experience[i] = resumeData.experience[j];
    resumeData.experience[j] = tmp;
    renderExperience();
    markDirty();
};
window.addExpBullet = function (i) {
    resumeData.experience[i].bullets = resumeData.experience[i].bullets || [];
    resumeData.experience[i].bullets.push('');
    renderExperience();
    markDirty();
};
window.removeExpBullet = function (i, bi) {
    resumeData.experience[i].bullets.splice(bi, 1);
    renderExperience();
    markDirty();
};
window.updateExpHead = function (i) {
    var exp = resumeData.experience[i];
    var card = document.querySelectorAll('#exp-list .rxe-item-card')[i];
    if (!card) return;
    var titleEl = card.querySelector('.rxe-item-head-title');
    if (titleEl) titleEl.textContent = (exp.title || 'New Position') + (exp.company ? ' at ' + exp.company : '');
};
window.aiSuggestBullets = function (i) {
    var exp = resumeData.experience[i] || {};
    var fd = new FormData();
    fd.append('_token', csrfToken);
    fd.append('job_title', exp.title || '');
    fd.append('company', exp.company || '');
    fetch('/projects/resumex/ai/suggest-bullets', { method: 'POST', body: fd })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!data.success) return;
        var box = document.getElementById('aiBullets' + i);
        box.innerHTML = '<div style="font-size:0.75rem;font-weight:700;color:var(--text-secondary);margin-bottom:8px;">Click to add a bullet:</div>' +
            data.bullets.map(function (b) {
                return '<div class="rxe-ai-suggestion-item" onclick="addBulletFromAI(' + i + ',this.textContent)">' + esc(b) + '</div>';
            }).join('');
        box.classList.add('open');
    });
};
window.addBulletFromAI = function (i, text) {
    resumeData.experience[i].bullets = resumeData.experience[i].bullets || [];
    resumeData.experience[i].bullets.push(text);
    document.getElementById('aiBullets' + i).classList.remove('open');
    renderExperience();
    markDirty();
};

/* ══════════════════════════════════════════════════════════════
   EDUCATION
══════════════════════════════════════════════════════════════ */
function renderEducation() {
    var list = document.getElementById('edu-list');
    if (!resumeData.education || !resumeData.education.length) { list.innerHTML = ''; return; }
    list.innerHTML = resumeData.education.map(function (edu, i) {
        return '<div class="rxe-item-card">' +
            '<div class="rxe-item-head" onclick="toggleItem(this)">' +
                '<div>' +
                    '<div class="rxe-item-head-title">' + esc(val(edu,'school') || 'New School') + '</div>' +
                    '<div class="rxe-item-head-subtitle">' + esc(val(edu,'degree')) + (val(edu,'field') ? ' – ' + esc(val(edu,'field')) : '') + '</div>' +
                '</div>' +
                '<div class="rxe-item-actions">' +
                    (i > 0 ? '<button type="button" class="rxe-btn-icon" onclick="event.stopPropagation(); moveEdu(' + i + ',-1)">↑</button>' : '') +
                    (i < resumeData.education.length - 1 ? '<button type="button" class="rxe-btn-icon" onclick="event.stopPropagation(); moveEdu(' + i + ',1)">↓</button>' : '') +
                    '<button type="button" class="rxe-btn-icon danger" onclick="event.stopPropagation(); removeEducation(' + i + ')">✕</button>' +
                '</div>' +
            '</div>' +
            '<div class="rxe-item-body">' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">School / University</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(edu,'school')) + '" placeholder="e.g. MIT" oninput="resumeData.education[' + i + '].school=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">Degree</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(edu,'degree')) + '" placeholder="e.g. Bachelor of Science" oninput="resumeData.education[' + i + '].degree=this.value; markDirty();"></div></div>' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">Field of Study</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(edu,'field')) + '" placeholder="e.g. Computer Science" oninput="resumeData.education[' + i + '].field=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">Location</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(edu,'location')) + '" placeholder="City, Country" oninput="resumeData.education[' + i + '].location=this.value; markDirty();"></div></div>' +
                '<div class="rxe-row three"><div class="rxe-field"><label class="rxe-label">Start Year</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(edu,'start_date')) + '" placeholder="2018" oninput="resumeData.education[' + i + '].start_date=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">End Year</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(edu,'end_date')) + '" placeholder="2022" oninput="resumeData.education[' + i + '].end_date=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">GPA / Grade</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(edu,'gpa')) + '" placeholder="3.8" oninput="resumeData.education[' + i + '].gpa=this.value; markDirty();"></div></div>' +
                '<div class="rxe-row full"><div class="rxe-field"><label class="rxe-label">Description</label>' +
                    '<textarea class="rxe-textarea" placeholder="Additional details, achievements, activities…" oninput="resumeData.education[' + i + '].description=this.value; markDirty();">' + esc(val(edu,'description')) + '</textarea></div></div>' +
            '</div>' +
        '</div>';
    }).join('');
}
window.addEducation = function () {
    resumeData.education.push({ school:'', degree:'', field:'', location:'', start_date:'', end_date:'', gpa:'', description:'' });
    renderEducation();
    markDirty();
};
window.removeEducation = function (i) {
    resumeData.education.splice(i, 1);
    renderEducation();
    markDirty();
};
window.moveEdu = function (i, dir) {
    var j = i + dir;
    if (j < 0 || j >= resumeData.education.length) return;
    var tmp = resumeData.education[i];
    resumeData.education[i] = resumeData.education[j];
    resumeData.education[j] = tmp;
    renderEducation();
    markDirty();
};

/* ══════════════════════════════════════════════════════════════
   SKILLS
══════════════════════════════════════════════════════════════ */
function renderSkills() {
    var area = document.getElementById('skills-area');
    // Remove old tags
    area.querySelectorAll('.rxe-skill-tag').forEach(function (t) { t.remove(); });
    // Re-insert tags before the input
    var input = document.getElementById('skillInput');
    (resumeData.skills || []).forEach(function (s, i) {
        var name = typeof s === 'string' ? s : (s.name || '');
        var tag = document.createElement('span');
        tag.className = 'rxe-skill-tag';
        tag.innerHTML = esc(name) + '<button type="button" title="Remove" onclick="removeSkill(' + i + ')">✕</button>';
        area.insertBefore(tag, input);
    });
}
window.removeSkill = function (i) {
    resumeData.skills.splice(i, 1);
    renderSkills();
    markDirty();
};
function addSkill(name) {
    name = name.trim();
    if (!name) return;
    if (!Array.isArray(resumeData.skills)) resumeData.skills = [];
    // Avoid duplicates
    var exists = resumeData.skills.some(function (s) {
        return (typeof s === 'string' ? s : s.name) === name;
    });
    if (!exists) {
        resumeData.skills.push(name);
        renderSkills();
        markDirty();
    }
}
document.getElementById('skillInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        addSkill(this.value.replace(',', ''));
        this.value = '';
    }
});

window.aiSuggestSkills = function () {
    var jobTitle = resumeData.experience && resumeData.experience[0]
        ? resumeData.experience[0].title
        : '';
    function doFetch(jt) {
        var fd = new FormData();
        fd.append('_token', csrfToken);
        fd.append('job_title', jt);
        fetch('/projects/resumex/ai/suggest-skills', { method:'POST', body:fd })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) return;
            var box = document.getElementById('aiSkillSuggestions');
            box.innerHTML = '<div style="font-size:0.75rem;font-weight:700;color:var(--text-secondary);margin-bottom:8px;">Click to add a skill:</div>';
            data.skills.forEach(function(s) {
                var el = document.createElement('div');
                el.className = 'rxe-ai-suggestion-item';
                el.dataset.skill = s;
                el.textContent = s;
                box.appendChild(el);
            });
            box.classList.add('open');
        });
    }
    if (jobTitle) {
        doFetch(jobTitle);
    } else {
        openPromptModal('Enter your job title for skill suggestions', 'e.g. Frontend Developer', '', function(t) {
            if (t) doFetch(t);
        });
    }
};

/* ── Skill suggestion click via event delegation ────────────── */
document.getElementById('aiSkillSuggestions').addEventListener('click', function(e) {
    var item = e.target.closest('.rxe-ai-suggestion-item');
    if (!item || item.classList.contains('added')) return;
    var skill = item.dataset.skill;
    if (!skill) return;
    addSkill(skill);
    item.classList.add('added');
});

/* ══════════════════════════════════════════════════════════════
   PROJECTS
══════════════════════════════════════════════════════════════ */
function renderProjects() {
    var list = document.getElementById('proj-list');
    if (!resumeData.projects || !resumeData.projects.length) { list.innerHTML = ''; return; }
    list.innerHTML = resumeData.projects.map(function (p, i) {
        return '<div class="rxe-item-card">' +
            '<div class="rxe-item-head" onclick="toggleItem(this)">' +
                '<div><div class="rxe-item-head-title">' + esc(val(p,'name') || 'New Project') + '</div></div>' +
                '<div class="rxe-item-actions">' +
                    (i > 0 ? '<button type="button" class="rxe-btn-icon" onclick="event.stopPropagation(); moveProj(' + i + ',-1)">↑</button>' : '') +
                    (i < resumeData.projects.length-1 ? '<button type="button" class="rxe-btn-icon" onclick="event.stopPropagation(); moveProj(' + i + ',1)">↓</button>' : '') +
                    '<button type="button" class="rxe-btn-icon danger" onclick="event.stopPropagation(); removeProject(' + i + ')">✕</button>' +
                '</div>' +
            '</div>' +
            '<div class="rxe-item-body">' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">Project Name</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(p,'name')) + '" placeholder="e.g. Portfolio Website" oninput="resumeData.projects[' + i + '].name=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">URL / Link</label>' +
                    '<input class="rxe-input" type="url" value="' + esc(val(p,'url')) + '" placeholder="https://…" oninput="resumeData.projects[' + i + '].url=this.value; markDirty();"></div></div>' +
                '<div class="rxe-row full"><div class="rxe-field"><label class="rxe-label">Technologies</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(Array.isArray(p.technologies) ? p.technologies.join(', ') : val(p,'technologies')) + '" placeholder="React, Node.js, PostgreSQL…" oninput="resumeData.projects[' + i + '].technologies=this.value.split(/,\\s*/); markDirty();"></div></div>' +
                '<div class="rxe-row full"><div class="rxe-field"><label class="rxe-label">Description</label>' +
                    '<textarea class="rxe-textarea" placeholder="What the project does and your contribution…" oninput="resumeData.projects[' + i + '].description=this.value; markDirty();">' + esc(val(p,'description')) + '</textarea></div></div>' +
            '</div>' +
        '</div>';
    }).join('');
}
window.addProject = function () {
    resumeData.projects.push({ name:'', description:'', url:'', technologies:[], bullets:[] });
    renderProjects(); markDirty();
};
window.removeProject = function (i) { resumeData.projects.splice(i,1); renderProjects(); markDirty(); };
window.moveProj = function (i, dir) {
    var j = i+dir;
    if (j<0||j>=resumeData.projects.length) return;
    var t=resumeData.projects[i]; resumeData.projects[i]=resumeData.projects[j]; resumeData.projects[j]=t;
    renderProjects(); markDirty();
};

/* ══════════════════════════════════════════════════════════════
   CERTIFICATIONS
══════════════════════════════════════════════════════════════ */
function renderCertifications() {
    var list = document.getElementById('cert-list');
    if (!resumeData.certifications || !resumeData.certifications.length) { list.innerHTML=''; return; }
    list.innerHTML = resumeData.certifications.map(function (c, i) {
        return '<div class="rxe-item-card">' +
            '<div class="rxe-item-head" onclick="toggleItem(this)">' +
                '<div><div class="rxe-item-head-title">' + esc(val(c,'name')||'New Certification') + '</div>' +
                '<div class="rxe-item-head-subtitle">' + esc(val(c,'issuer')) + '</div></div>' +
                '<div class="rxe-item-actions"><button type="button" class="rxe-btn-icon danger" onclick="event.stopPropagation(); removeCert(' + i + ')">✕</button></div>' +
            '</div>' +
            '<div class="rxe-item-body">' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">Certificate Name</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(c,'name')) + '" placeholder="e.g. AWS Solutions Architect" oninput="resumeData.certifications[' + i + '].name=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">Issuing Organisation</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(c,'issuer')) + '" placeholder="e.g. Amazon" oninput="resumeData.certifications[' + i + '].issuer=this.value; markDirty();"></div></div>' +
                '<div class="rxe-row three"><div class="rxe-field"><label class="rxe-label">Issue Date</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(c,'date')) + '" placeholder="Jan 2023" oninput="resumeData.certifications[' + i + '].date=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">Expiry</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(c,'expiry')) + '" placeholder="No expiry" oninput="resumeData.certifications[' + i + '].expiry=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">Credential ID</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(c,'id')) + '" placeholder="ABC-1234" oninput="resumeData.certifications[' + i + '].id=this.value; markDirty();"></div></div>' +
                '<div class="rxe-row full"><div class="rxe-field"><label class="rxe-label">URL</label>' +
                    '<input class="rxe-input" type="url" value="' + esc(val(c,'url')) + '" placeholder="https://…" oninput="resumeData.certifications[' + i + '].url=this.value; markDirty();"></div></div>' +
            '</div>' +
        '</div>';
    }).join('');
}
window.addCertification = function () {
    resumeData.certifications.push({ name:'', issuer:'', date:'', expiry:'', url:'', id:'' });
    renderCertifications(); markDirty();
};
window.removeCert = function (i) { resumeData.certifications.splice(i,1); renderCertifications(); markDirty(); };

/* ══════════════════════════════════════════════════════════════
   AWARDS
══════════════════════════════════════════════════════════════ */
function renderAwards() {
    var list = document.getElementById('award-list');
    if (!resumeData.awards || !resumeData.awards.length) { list.innerHTML=''; return; }
    list.innerHTML = resumeData.awards.map(function (a, i) {
        return '<div class="rxe-item-card">' +
            '<div class="rxe-item-head" onclick="toggleItem(this)">' +
                '<div><div class="rxe-item-head-title">' + esc(val(a,'title')||'New Award') + '</div></div>' +
                '<div class="rxe-item-actions"><button type="button" class="rxe-btn-icon danger" onclick="event.stopPropagation(); removeAward(' + i + ')">✕</button></div>' +
            '</div>' +
            '<div class="rxe-item-body">' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">Award Title</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(a,'title')) + '" placeholder="e.g. Employee of the Year" oninput="resumeData.awards[' + i + '].title=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">Issuer</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(a,'issuer')) + '" placeholder="Organisation name" oninput="resumeData.awards[' + i + '].issuer=this.value; markDirty();"></div></div>' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">Date</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(a,'date')) + '" placeholder="2023" oninput="resumeData.awards[' + i + '].date=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">Description</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(a,'description')) + '" placeholder="Brief description…" oninput="resumeData.awards[' + i + '].description=this.value; markDirty();"></div></div>' +
            '</div>' +
        '</div>';
    }).join('');
}
window.addAward = function () {
    resumeData.awards.push({ title:'', issuer:'', date:'', description:'' });
    renderAwards(); markDirty();
};
window.removeAward = function (i) { resumeData.awards.splice(i,1); renderAwards(); markDirty(); };

/* ══════════════════════════════════════════════════════════════
   VOLUNTEER
══════════════════════════════════════════════════════════════ */
function renderVolunteer() {
    var list = document.getElementById('vol-list');
    if (!resumeData.volunteer || !resumeData.volunteer.length) { list.innerHTML=''; return; }
    list.innerHTML = resumeData.volunteer.map(function (v, i) {
        return '<div class="rxe-item-card">' +
            '<div class="rxe-item-head" onclick="toggleItem(this)">' +
                '<div><div class="rxe-item-head-title">' + esc(val(v,'role')||'New Role') + '</div>' +
                '<div class="rxe-item-head-subtitle">' + esc(val(v,'organization')) + '</div></div>' +
                '<div class="rxe-item-actions"><button type="button" class="rxe-btn-icon danger" onclick="event.stopPropagation(); removeVol(' + i + ')">✕</button></div>' +
            '</div>' +
            '<div class="rxe-item-body">' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">Role</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(v,'role')) + '" placeholder="e.g. Mentor" oninput="resumeData.volunteer[' + i + '].role=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">Organisation</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(v,'organization')) + '" placeholder="e.g. Code.org" oninput="resumeData.volunteer[' + i + '].organization=this.value; markDirty();"></div></div>' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">Start Date</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(v,'start_date')) + '" placeholder="Jan 2020" oninput="resumeData.volunteer[' + i + '].start_date=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">End Date</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(v,'end_date')) + '" placeholder="Present" oninput="resumeData.volunteer[' + i + '].end_date=this.value; markDirty();"></div></div>' +
                '<div class="rxe-row full"><div class="rxe-field"><label class="rxe-label">Description</label>' +
                    '<textarea class="rxe-textarea" placeholder="Describe your contribution…" oninput="resumeData.volunteer[' + i + '].description=this.value; markDirty();">' + esc(val(v,'description')) + '</textarea></div></div>' +
            '</div>' +
        '</div>';
    }).join('');
}
window.addVolunteer = function () {
    resumeData.volunteer.push({ organization:'', role:'', start_date:'', end_date:'', description:'' });
    renderVolunteer(); markDirty();
};
window.removeVol = function (i) { resumeData.volunteer.splice(i,1); renderVolunteer(); markDirty(); };

/* ══════════════════════════════════════════════════════════════
   LANGUAGES
══════════════════════════════════════════════════════════════ */
function renderLanguages() {
    var list = document.getElementById('lang-list');
    if (!resumeData.languages || !resumeData.languages.length) { list.innerHTML=''; return; }
    var levels = ['Native','Fluent','Advanced','Intermediate','Basic','Elementary'];
    list.innerHTML = resumeData.languages.map(function (l, i) {
        var opts = levels.map(function (lv) {
            return '<option value="' + lv + '" ' + (val(l,'level')===lv?'selected':'') + '>' + lv + '</option>';
        }).join('');
        return '<div class="rxe-item-card">' +
            '<div class="rxe-item-head" onclick="toggleItem(this)">' +
                '<div><div class="rxe-item-head-title">' + esc(val(l,'language')||'New Language') + ' — ' + esc(val(l,'level')||'') + '</div></div>' +
                '<div class="rxe-item-actions"><button type="button" class="rxe-btn-icon danger" onclick="event.stopPropagation(); removeLang(' + i + ')">✕</button></div>' +
            '</div>' +
            '<div class="rxe-item-body"><div class="rxe-row">' +
                '<div class="rxe-field"><label class="rxe-label">Language</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(l,'language')) + '" placeholder="e.g. English" oninput="resumeData.languages[' + i + '].language=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">Proficiency</label>' +
                    '<select class="rxe-select" onchange="resumeData.languages[' + i + '].level=this.value; markDirty();"><option value="">Select…</option>' + opts + '</select></div>' +
            '</div></div>' +
        '</div>';
    }).join('');
}
window.addLanguage = function () {
    resumeData.languages.push({ language:'', level:'' });
    renderLanguages(); markDirty();
};
window.removeLang = function (i) { resumeData.languages.splice(i,1); renderLanguages(); markDirty(); };

/* ══════════════════════════════════════════════════════════════
   HOBBIES
══════════════════════════════════════════════════════════════ */
function renderHobbies() {
    var area = document.getElementById('hobbies-area');
    area.querySelectorAll('.rxe-skill-tag').forEach(function (t) { t.remove(); });
    var input = document.getElementById('hobbyInput');
    (resumeData.hobbies || []).forEach(function (h, i) {
        var tag = document.createElement('span');
        tag.className = 'rxe-skill-tag';
        tag.style.borderColor = 'rgba(255,170,0,0.3)';
        tag.style.background = 'rgba(255,170,0,0.1)';
        tag.style.color = 'var(--orange)';
        tag.innerHTML = esc(h) + '<button type="button" title="Remove" onclick="removeHobby(' + i + ')">✕</button>';
        area.insertBefore(tag, input);
    });
}
window.removeHobby = function (i) { resumeData.hobbies.splice(i,1); renderHobbies(); markDirty(); };
function addHobby(name) {
    name = name.trim();
    if (!name) return;
    if (!Array.isArray(resumeData.hobbies)) resumeData.hobbies = [];
    if (!resumeData.hobbies.includes(name)) { resumeData.hobbies.push(name); renderHobbies(); markDirty(); }
}
document.getElementById('hobbyInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        addHobby(this.value.replace(',',''));
        this.value = '';
    }
});

/* ══════════════════════════════════════════════════════════════
   REFERENCES
══════════════════════════════════════════════════════════════ */
function renderReferences() {
    var list = document.getElementById('ref-list');
    if (!resumeData.references || !resumeData.references.length) { list.innerHTML=''; return; }
    list.innerHTML = resumeData.references.map(function (r, i) {
        return '<div class="rxe-item-card">' +
            '<div class="rxe-item-head" onclick="toggleItem(this)">' +
                '<div><div class="rxe-item-head-title">' + esc(val(r,'name')||'New Reference') + '</div>' +
                '<div class="rxe-item-head-subtitle">' + esc(val(r,'title')) + (val(r,'company')?' at '+esc(val(r,'company')):'') + '</div></div>' +
                '<div class="rxe-item-actions"><button type="button" class="rxe-btn-icon danger" onclick="event.stopPropagation(); removeRef(' + i + ')">✕</button></div>' +
            '</div>' +
            '<div class="rxe-item-body">' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">Full Name</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(r,'name')) + '" placeholder="e.g. John Doe" oninput="resumeData.references[' + i + '].name=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">Job Title</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(r,'title')) + '" placeholder="e.g. CTO" oninput="resumeData.references[' + i + '].title=this.value; markDirty();"></div></div>' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">Company</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(r,'company')) + '" placeholder="e.g. Acme Corp" oninput="resumeData.references[' + i + '].company=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">Email</label>' +
                    '<input class="rxe-input" type="email" value="' + esc(val(r,'email')) + '" placeholder="john@example.com" oninput="resumeData.references[' + i + '].email=this.value; markDirty();"></div></div>' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">Phone</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(r,'phone')) + '" placeholder="+1 555 000 0000" oninput="resumeData.references[' + i + '].phone=this.value; markDirty();"></div></div>' +
            '</div>' +
        '</div>';
    }).join('');
}
window.addReference = function () {
    resumeData.references.push({ name:'', title:'', company:'', email:'', phone:'' });
    renderReferences(); markDirty();
};
window.removeRef = function (i) { resumeData.references.splice(i,1); renderReferences(); markDirty(); };

/* ══════════════════════════════════════════════════════════════
   PUBLICATIONS
══════════════════════════════════════════════════════════════ */
function renderPublications() {
    var list = document.getElementById('pub-list');
    if (!resumeData.publications || !resumeData.publications.length) { list.innerHTML=''; return; }
    list.innerHTML = resumeData.publications.map(function (p, i) {
        return '<div class="rxe-item-card">' +
            '<div class="rxe-item-head" onclick="toggleItem(this)">' +
                '<div><div class="rxe-item-head-title">' + esc(val(p,'title')||'New Publication') + '</div>' +
                '<div class="rxe-item-head-subtitle">' + esc(val(p,'journal')) + '</div></div>' +
                '<div class="rxe-item-actions"><button type="button" class="rxe-btn-icon danger" onclick="event.stopPropagation(); removePub(' + i + ')">✕</button></div>' +
            '</div>' +
            '<div class="rxe-item-body">' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">Title</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(p,'title')) + '" placeholder="Publication title" oninput="resumeData.publications[' + i + '].title=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">Authors</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(p,'authors')) + '" placeholder="e.g. Smith J., Doe A." oninput="resumeData.publications[' + i + '].authors=this.value; markDirty();"></div></div>' +
                '<div class="rxe-row"><div class="rxe-field"><label class="rxe-label">Journal / Publisher</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(p,'journal')) + '" placeholder="e.g. Nature" oninput="resumeData.publications[' + i + '].journal=this.value; markDirty();"></div>' +
                '<div class="rxe-field"><label class="rxe-label">Date</label>' +
                    '<input class="rxe-input" type="text" value="' + esc(val(p,'date')) + '" placeholder="2023" oninput="resumeData.publications[' + i + '].date=this.value; markDirty();"></div></div>' +
                '<div class="rxe-row full"><div class="rxe-field"><label class="rxe-label">URL / DOI</label>' +
                    '<input class="rxe-input" type="url" value="' + esc(val(p,'url')) + '" placeholder="https://doi.org/…" oninput="resumeData.publications[' + i + '].url=this.value; markDirty();"></div></div>' +
            '</div>' +
        '</div>';
    }).join('');
}
window.addPublication = function () {
    resumeData.publications.push({ title:'', authors:'', journal:'', date:'', url:'', description:'' });
    renderPublications(); markDirty();
};
window.removePub = function (i) { resumeData.publications.splice(i,1); renderPublications(); markDirty(); };

/* ══════════════════════════════════════════════════════════════
   THEME
══════════════════════════════════════════════════════════════ */
function renderThemeGrid() {
    var grid = document.getElementById('theme-grid');
    grid.innerHTML = Object.values(allThemes).map(function (t) {
        var active = (themeSettings.key === t.key) ? 'active' : '';
        return '<div class="rxe-theme-card ' + active + '" onclick="selectTheme(\'' + esc(t.key) + '\')">' +
            '<div class="rxe-theme-preview" style="background:' + esc(t.backgroundColor) + '">' +
                '<div>' +
                    '<div class="rxe-theme-preview-line" style="background:' + esc(t.primaryColor) + '"></div>' +
                    '<div class="rxe-theme-preview-line short" style="background:' + esc(t.secondaryColor) + '; opacity:0.7"></div>' +
                    '<div class="rxe-theme-preview-line" style="background:' + esc(t.textColor) + '; opacity:0.35; width:80%"></div>' +
                    '<div class="rxe-theme-preview-line short" style="background:' + esc(t.textColor) + '; opacity:0.2"></div>' +
                '</div>' +
            '</div>' +
            '<div class="rxe-theme-name" style="background:' + esc(t.surfaceColor) + '; color:' + esc(t.textColor) + '">' + esc(t.name) + '</div>' +
        '</div>';
    }).join('');
}
window.selectTheme = function (key) {
    if (!allThemes[key]) return;
    themeSettings = JSON.parse(JSON.stringify(allThemes[key]));
    renderThemeGrid();
    markDirty();
};

/* ══════════════════════════════════════════════════════════════
   SECTION ORDER
══════════════════════════════════════════════════════════════ */
var SECTION_LABELS = {
    contact:'Contact Info', summary:'Summary', experience:'Work Experience',
    education:'Education', skills:'Skills', projects:'Projects',
    certifications:'Certifications', awards:'Awards', volunteer:'Volunteer',
    languages:'Languages', hobbies:'Hobbies', references:'References',
    publications:'Publications'
};
function renderSectionOrder() {
    var wrap = document.getElementById('section-order-list');
    var order = resumeData.section_order || Object.keys(SECTION_LABELS);
    wrap.innerHTML = order.map(function (sec) {
        var hidden = (resumeData.hidden_sections || []).includes(sec);
        return '<div class="rxe-order-item">' +
            '<input type="checkbox" id="vis_' + sec + '" ' + (!hidden ? 'checked' : '') +
                ' onchange="toggleSectionVisibility(\'' + sec + '\', this.checked)">' +
            '<label for="vis_' + sec + '">' + (SECTION_LABELS[sec] || sec) + '</label>' +
        '</div>';
    }).join('');
}
window.toggleSectionVisibility = function (sec, visible) {
    if (!Array.isArray(resumeData.hidden_sections)) resumeData.hidden_sections = [];
    if (visible) {
        resumeData.hidden_sections = resumeData.hidden_sections.filter(function (s) { return s !== sec; });
    } else if (!resumeData.hidden_sections.includes(sec)) {
        resumeData.hidden_sections.push(sec);
    }
    markDirty();
};

/* ══════════════════════════════════════════════════════════════
   SCORE
══════════════════════════════════════════════════════════════ */
window.scoreResume = function () {
    readContactFromDOM();
    readSummaryFromDOM();
    fetch('/projects/resumex/ai/score', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({ _token: csrfToken, resume_data: resumeData })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!data.success) return;
        var score = data.score;
        var scoreColor = score >= 80 ? 'var(--green)' : (score >= 60 ? 'var(--cyan)' : (score >= 40 ? 'var(--orange)' : 'var(--red)'));
        // Ring
        var circumference = 238.76;
        var offset = circumference - (score / 100) * circumference;
        var ring = document.getElementById('scoreRingCircle');
        if (ring) {
            ring.style.strokeDashoffset = offset;
            ring.style.stroke = scoreColor;
        }
        var numEl = document.getElementById('scoreNum');
        if (numEl) { numEl.textContent = score; numEl.style.color = scoreColor; }
        var labelEl = document.getElementById('scoreLabel');
        if (labelEl) labelEl.textContent = data.label || 'Analysis Complete';
        var gradeEl = document.getElementById('scoreGrade');
        if (gradeEl) {
            gradeEl.textContent = 'Grade ' + data.grade;
            gradeEl.style.background = scoreColor.replace('var(','').replace(')','') !== scoreColor
                ? scoreColor + '22' : 'rgba(0,240,255,0.12)';
            gradeEl.style.color = scoreColor;
            gradeEl.style.borderColor = scoreColor + '44';
        }
        // Breakdown bars
        var bdEl = document.getElementById('scoreBreakdown');
        if (bdEl && data.breakdown) {
            bdEl.innerHTML = Object.values(data.breakdown).map(function(cat) {
                var pct = cat.max > 0 ? Math.round((cat.score / cat.max) * 100) : 0;
                return '<div class="rxe-score-cat">' +
                    '<div class="rxe-score-cat-header">' +
                        '<span class="rxe-score-cat-name">' + esc(cat.label) + '</span>' +
                        '<span class="rxe-score-cat-pts">' + cat.score + '/' + cat.max + '</span>' +
                    '</div>' +
                    '<div class="rxe-score-cat-bar"><div class="rxe-score-cat-fill" style="width:' + pct + '%"></div></div>' +
                '</div>';
            }).join('');
        }
        var sugg = document.getElementById('scoreSuggestions');
        if (data.suggestions && data.suggestions.length) {
            sugg.innerHTML = '<div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-secondary);margin-bottom:8px;">Suggestions</div>' +
                data.suggestions.map(function (s) {
                    return '<div class="rxe-score-suggestion">' + esc(s) + '</div>';
                }).join('');
        } else {
            sugg.innerHTML = '<div style="color:var(--green);font-size:0.85rem;padding:8px 0;">&#10003; Great job! Your resume is well-rounded.</div>';
        }
    });
};

/* ══════════════════════════════════════════════════════════════
   COLLAPSE / EXPAND ITEM CARDS
══════════════════════════════════════════════════════════════ */
window.toggleItem = function (headEl) {
    var body = headEl.nextElementSibling;
    if (body) body.classList.toggle('collapsed');
};

/* ══════════════════════════════════════════════════════════════
   INIT
══════════════════════════════════════════════════════════════ */
initContact();
initSummary();
renderExperience();
renderEducation();
renderSkills();
renderProjects();
renderCertifications();
renderAwards();
renderVolunteer();
renderLanguages();
renderHobbies();
renderReferences();
renderPublications();
renderThemeGrid();
renderSectionOrder();

// Initial live preview
setTimeout(updateLivePreview, 400);

// Highlight preview toggle button as active
(function() {
    var btn = document.getElementById('btnTogglePreview');
    if (btn) { btn.style.borderColor = 'rgba(0,240,255,0.35)'; btn.style.color = 'var(--cyan)'; }
}());

}());
</script>
<?php View::end(); ?>

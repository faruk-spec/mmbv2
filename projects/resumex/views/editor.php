<?php use Core\View; use Core\Security; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<style>
/* ── Strip main layout padding so editor starts right below navbar ── */
body .main {
    padding: 0 !important;
}

/* ── Editor wrapper ─────────────────────────────────────────── */
.rxe-wrap {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 56px);
    min-height: 0;
    overflow: hidden;
    width: 100%;
    position: relative;
}

/* ── Top bar ────────────────────────────────────────────────── */
.rxe-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 14px;
    background: var(--bg-card);
    border-bottom: 1px solid var(--border-color);
    flex-shrink: 0;
    flex-wrap: wrap;
    width: 100%;
    position: sticky;
    top: 0;
    z-index: 50;
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
    max-width: 280px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 0.86rem;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    padding: 5px 10px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.rxe-title-input:focus {
    border-color: rgba(0,240,255,0.5);
    box-shadow: 0 0 0 3px rgba(0,240,255,0.07);
}
.rxe-bar-spacer { flex: 1; }
.rxe-save-status {
    font-size: 0.72rem;
    color: var(--text-secondary);
    white-space: nowrap;
}
.rxe-bar-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 11px;
    border-radius: 8px;
    font-size: 0.78rem;
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
.rxe-color-dots {
    display: flex;
    gap: 5px;
    padding: 5px 10px 7px;
    flex-wrap: wrap;
}
.rxe-color-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    cursor: pointer;
    border: 2px solid transparent;
    transition: transform 0.15s, border-color 0.15s;
    flex-shrink: 0;
}
.rxe-color-dot:hover { transform: scale(1.25); }
.rxe-color-dot.active-dot { border-color: #fff; box-shadow: 0 0 0 2px rgba(255,255,255,0.4); }

/* ── Colour palette (large swatches in theme panel) ─────────── */
.rxe-colour-palette {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 8px;
}
.rxe-colour-swatch {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}
.rxe-colour-swatch-dot {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 3px solid transparent;
    transition: transform 0.15s, border-color 0.15s, box-shadow 0.15s;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
.rxe-colour-swatch:hover .rxe-colour-swatch-dot { transform: scale(1.15); }
.rxe-colour-swatch.active .rxe-colour-swatch-dot {
    border-color: var(--cyan);
    box-shadow: 0 0 0 3px rgba(0,240,255,0.25);
}
.rxe-colour-swatch-label {
    font-size: 0.65rem;
    font-weight: 600;
    color: var(--text-secondary);
    text-align: center;
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
    width: 55%;
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
    overflow-y: auto;
    overflow-x: hidden;
    background: #1a1a2e;
    display: flex;
    align-items: flex-start;
    justify-content: flex-start;
    padding: 4px;
}
#rxe-preview-frame {
    width: 794px;
    height: 1123px;
    min-height: unset;
    border: none;
    background: #fff;
    display: block;
    box-shadow: 0 4px 32px rgba(0,0,0,0.5);
    transform-origin: top left;
    flex-shrink: 0;
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

/* ── Template picker modal ───────────────────────────────────── */
#rxe-tpl-modal { padding: 40px 20px; }
.rxe-tpl-filter-btn {
    padding: 5px 14px; border-radius: 20px; border: 1px solid var(--border-color);
    background: transparent; color: var(--text-secondary); font-size: 0.75rem;
    font-family: 'Poppins', sans-serif; cursor: pointer; transition: all 0.18s; font-weight: 500;
}
.rxe-tpl-filter-btn:hover { border-color: rgba(0,240,255,0.4); color: var(--cyan); }
.rxe-tpl-filter-btn.active { background: var(--cyan); border-color: var(--cyan); color: #06060a; font-weight: 700; }
.rxe-tpl-card {
    border-radius: 10px; border: 2px solid var(--border-color); overflow: hidden;
    cursor: pointer; transition: all 0.18s; background: var(--bg-secondary);
    display: flex; flex-direction: column;
}
.rxe-tpl-card:hover { border-color: rgba(0,240,255,0.5); transform: translateY(-2px); box-shadow: 0 6px 24px rgba(0,0,0,0.3); }
.rxe-tpl-card.active-tpl { border-color: var(--cyan); box-shadow: 0 0 0 2px rgba(0,240,255,0.3); }
.rxe-tpl-thumb { height: 140px; position: relative; overflow: hidden; flex-shrink: 0; }
.rxe-tpl-info { padding: 10px 12px; }
.rxe-tpl-name { font-size: 0.78rem; font-weight: 700; color: var(--text-primary); }
.rxe-tpl-cat { font-size: 0.68rem; color: var(--text-secondary); margin-top: 2px; }
.rxe-tpl-badge { display: inline-block; padding: 1px 7px; border-radius: 10px; font-size: 0.62rem; font-weight: 600; background: var(--cyan); color: #06060a; margin-top: 4px; }
.rxe-tpl-layout-tag { display: inline-block; padding: 1px 7px; border-radius: 10px; font-size: 0.62rem; font-weight: 600; border: 1px solid var(--border-color); color: var(--text-secondary); margin-top: 4px; margin-left: 4px; }

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
    /* On mobile, show the bar Preview button (overrides the hide rule) */
    .rxe-btn-toggle-preview { display: inline-flex !important; }
    .rxe-desktop-only { display: none !important; }
    /* Keep bar on one line — no wrapping */
    .rxe-bar { flex-wrap: nowrap; overflow-x: hidden; }
    /* Mobile preview overlay mode — positioned within .rxe-wrap (same div), top is set dynamically to the bar height */
    .rxe-preview-pane.mobile-open {
        display: flex !important;
        position: absolute;
        top: var(--rxe-bar-h, 44px);
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 200;
        width: 100% !important;
        min-width: unset;
        border-left: none;
    }
    /* Hide floating FAB — we use the bar button now */
    .rxe-mobile-preview-toggle { display: none !important; }
}
@media (max-width: 768px) {
    .rxe-nav { width: 160px; }
    .rxe-form-area { padding: 16px 12px 80px; }
    .rxe-row { grid-template-columns: 1fr; }
    .rxe-row.three { grid-template-columns: 1fr; }
    .rxe-title-input { max-width: 100px; min-width: 60px; }
    .rxe-save-status { display: none; }
    .rxe-bar-spacer { flex: 1; min-width: 0; }
    /* Compress all bar buttons to icon-only */
    .rxe-bar-btn { padding: 5px 7px; gap: 0; font-size: 0; min-width: 0; }
    .rxe-bar-btn svg { flex-shrink: 0; }
    .rxe-bar-btn.primary { padding: 5px 8px; font-size: 0.75rem; gap: 3px; }
}
@media (max-width: 560px) {
    .rxe-nav { display: none; }
    .rxe-mobile-nav-bar { display: flex !important; }
    .rxe-form-area { padding: 12px 12px 80px; }
    .rxe-title-input { max-width: 80px; min-width: 50px; font-size: 0.75rem; }
    .rxe-bar { gap: 3px; padding: 5px 6px; }
    .rxe-back span { display: none; } /* hide Dashboard text, keep arrow */
    .rxe-back { padding: 3px; }
}
/* Mobile bottom nav bar */
.rxe-mobile-nav-bar {
    display: none;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 100;
    background: var(--bg-card);
    border-top: 1px solid var(--border-color);
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.rxe-mobile-nav-bar::-webkit-scrollbar { display: none; }
.rxe-mobile-nav-inner {
    display: flex;
    min-width: max-content;
    padding: 4px 8px;
    gap: 2px;
}
.rxe-mobile-nav-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 6px 10px;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: var(--text-secondary);
    font-size: 0.62rem;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    white-space: nowrap;
    min-width: 52px;
    transition: all 0.15s;
}
.rxe-mobile-nav-btn.active { color: var(--cyan); background: rgba(0,240,255,0.08); }
.rxe-mobile-nav-btn:hover { color: var(--cyan); }
.rxe-mobile-preview-toggle {
    display: none;
    position: fixed;
    bottom: 68px;
    right: 16px;
    z-index: 150;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, var(--cyan), var(--purple));
    color: #06060a;
    font-size: 1.2rem;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(0,240,255,0.35);
    align-items: center;
    justify-content: center;
    transition: transform 0.2s;
}
.rxe-mobile-preview-toggle:hover { transform: scale(1.1); }
</style>

<div class="rxe-wrap">

    <!-- Top bar -->
    <div class="rxe-bar">
        <a href="/projects/resumex" class="rxe-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            <span>Dashboard</span>
        </a>
        <input id="resumeTitle" type="text" class="rxe-title-input"
               value="<?= htmlspecialchars($resume['title'] ?? 'My Resume', ENT_QUOTES, 'UTF-8') ?>"
               placeholder="Resume title" maxlength="255">
        <div class="rxe-bar-spacer"></div>
        <span id="saveStatus" class="rxe-save-status">All changes saved</span>
        <button type="button" class="rxe-bar-btn" onclick="openTemplatePicker()" title="Change resume template">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Template
        </button>
        <button type="button" class="rxe-bar-btn rxe-btn-toggle-preview" id="btnTogglePreview" onclick="handlePreviewBtn()" title="Toggle live preview">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            Preview
        </button>
        <button type="button" class="rxe-bar-btn" onclick="showSection('score'); scoreResume();" title="Analyse your resume">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            Score
        </button>
        <button type="button" id="btnDownload" class="rxe-bar-btn" onclick="downloadResume()" title="Download as PDF">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            Download
        </button>
        <button type="button" class="rxe-bar-btn rxe-desktop-only" onclick="printResume()" title="Print resume">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print
        </button>
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
            <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(0,240,255,0.07);border:1px solid rgba(0,240,255,0.2);border-radius:20px;padding:5px 12px;margin-bottom:16px;font-size:0.77rem;font-weight:600;color:var(--cyan);">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Resume created — fill in your details below
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
                        <label class="rxe-label">Job Title</label>
                        <input class="rxe-input" id="c_job_title" type="text" placeholder="e.g. Interior Designer" maxlength="120">
                    </div>
                </div>
                <div class="rxe-row">
                    <div class="rxe-field">
                        <label class="rxe-label">Email</label>
                        <input class="rxe-input" id="c_email" type="email" placeholder="jane@example.com" maxlength="120">
                    </div>
                    <div class="rxe-field">
                        <label class="rxe-label">Phone</label>
                        <input class="rxe-input" id="c_phone" type="text" placeholder="+1 555 000 0000" maxlength="30">
                    </div>
                </div>
                <div class="rxe-row full">
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
            </div><!-- /panel-contact -->

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
                    <p>Select a template and customise its colour.</p>
                </div>

                <!-- Template selector (only 2) -->
                <div id="theme-grid" class="rxe-theme-grid"></div>

                <!-- Colour options for the active template -->
                <div class="rxe-section-heading" style="margin-top: 20px;">
                    <h2>Colour Options</h2>
                    <p>Pick an accent colour for your resume.</p>
                </div>
                <div id="rxe-colour-palette" class="rxe-colour-palette"></div>

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
                    <!-- Mobile: "Edit" close button (visible only in mobile overlay mode) -->
                    <button type="button" class="rxe-preview-header-btn rxe-mobile-edit-btn" id="btnPreviewClose" onclick="toggleMobilePreview()" style="display:none">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        Edit
                    </button>
                    <button type="button" class="rxe-preview-header-btn" onclick="updateLivePreview()">&#8635; Refresh</button>
                    <a href="/projects/resumex/preview/<?= (int)$resume['id'] ?>" target="_blank" class="rxe-preview-header-btn rxe-desktop-only">&#10138; Full page</a>
                </div>
            </div>
            <div class="rxe-preview-iframe-wrap" id="rxe-preview-iframe-wrap">
                <iframe id="rxe-preview-frame" sandbox="allow-same-origin allow-scripts" title="Resume preview"></iframe>
            </div>
        </div>

        </div><!-- /rxe-editor-main -->
    </div><!-- /rxe-body -->
</div><!-- /rxe-wrap -->

<!-- Mobile bottom navigation bar (shown on ≤560px) -->
<div class="rxe-mobile-nav-bar" id="rxeMobileNavBar">
    <div class="rxe-mobile-nav-inner">
        <button type="button" class="rxe-mobile-nav-btn active" data-section="contact" onclick="showSection('contact')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            Contact
        </button>
        <button type="button" class="rxe-mobile-nav-btn" data-section="summary" onclick="showSection('summary')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Summary
        </button>
        <button type="button" class="rxe-mobile-nav-btn" data-section="experience" onclick="showSection('experience')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
            Experience
        </button>
        <button type="button" class="rxe-mobile-nav-btn" data-section="education" onclick="showSection('education')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
            Education
        </button>
        <button type="button" class="rxe-mobile-nav-btn" data-section="skills" onclick="showSection('skills')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
            Skills
        </button>
        <button type="button" class="rxe-mobile-nav-btn" data-section="projects" onclick="showSection('projects')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
            Projects
        </button>
        <button type="button" class="rxe-mobile-nav-btn" data-section="theme" onclick="showSection('theme')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41M12 2v2M12 20v2M2 12h2M20 12h2"></path></svg>
            Theme
        </button>
        <button type="button" class="rxe-mobile-nav-btn" data-section="score" onclick="showSection('score'); scoreResume();">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            Score
        </button>
    </div>
</div>

<!-- Mobile floating preview toggle button -->
<button type="button" class="rxe-mobile-preview-toggle" id="btnMobilePreview" onclick="toggleMobilePreview()" aria-label="Toggle preview">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
</button>

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

<!-- Template Picker Modal -->
<div id="rxe-tpl-modal" class="rxe-modal-overlay" role="dialog" aria-modal="true" style="align-items:flex-start;padding:0;">
    <div style="background:var(--bg-card);width:100%;max-width:980px;margin:auto;border-radius:14px;overflow:hidden;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,0.6);">
        <!-- Header -->
        <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid var(--border-color);flex-shrink:0;">
            <div>
                <div style="font-size:1.05rem;font-weight:700;color:var(--text-primary);">Choose a Template</div>
                <div style="font-size:0.78rem;color:var(--text-secondary);margin-top:2px;">Select a design — your content is preserved</div>
            </div>
            <button type="button" onclick="closeTemplatePicker()" style="background:none;border:none;color:var(--text-secondary);font-size:1.4rem;cursor:pointer;line-height:1;padding:4px 8px;">&times;</button>
        </div>
        <!-- Filter tabs -->
        <div style="display:flex;gap:6px;padding:12px 24px;border-bottom:1px solid var(--border-color);flex-shrink:0;flex-wrap:wrap;" id="rxe-tpl-filters">
            <button class="rxe-tpl-filter-btn active" data-filter="all">All</button>
            <button class="rxe-tpl-filter-btn" data-filter="professional">Professional</button>
            <button class="rxe-tpl-filter-btn" data-filter="creative">Creative</button>
            <button class="rxe-tpl-filter-btn" data-filter="minimal">Minimal</button>
            <button class="rxe-tpl-filter-btn" data-filter="dark">Dark</button>
            <button class="rxe-tpl-filter-btn" data-filter="sidebar">Sidebar</button>
            <button class="rxe-tpl-filter-btn" data-filter="timeline">Timeline</button>
        </div>
        <!-- Grid -->
        <div id="rxe-tpl-grid" style="overflow-y:auto;padding:20px 24px;display:grid;grid-template-columns:repeat(auto-fill,minmax(175px,1fr));gap:14px;flex:1;"></div>
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

/* Unified handler: desktop → togglePreviewPane, mobile → toggleMobilePreview */
window.handlePreviewBtn = function() {
    if (window.innerWidth <= 960) {
        toggleMobilePreview();
    } else {
        togglePreviewPane();
    }
};

/* Mobile preview overlay toggle — called by the bar Preview button on mobile */
var mobilePreviewOpen = false;
window.toggleMobilePreview = function() {
    var pane     = document.getElementById('rxe-preview-pane');
    var barBtn   = document.getElementById('btnTogglePreview');
    var closeBtn = document.getElementById('btnPreviewClose');
    mobilePreviewOpen = !mobilePreviewOpen;
    if (mobilePreviewOpen) {
        // Measure the bar's own height within .rxe-wrap (absolute positioning, so top
        // is relative to the container — not the viewport — and no global navbar offset needed).
        var bar = document.querySelector('.rxe-bar');
        var barH = bar ? Math.ceil(bar.getBoundingClientRect().height) : 44;
        document.documentElement.style.setProperty('--rxe-bar-h', barH + 'px');
        pane.style.top = barH + 'px';
        pane.classList.add('mobile-open');
        // Bar button highlights as active
        if (barBtn) { barBtn.style.borderColor = 'rgba(0,240,255,0.35)'; barBtn.style.color = 'var(--cyan)'; }
        // Show "Edit" close button inside preview header
        if (closeBtn) closeBtn.style.display = '';
        // Force recalc A4 scale after overlay becomes visible
        requestAnimationFrame(function() { updateLivePreview(); });
    } else {
        pane.classList.remove('mobile-open');
        pane.style.top = '';
        if (barBtn) { barBtn.style.borderColor = ''; barBtn.style.color = ''; }
        if (closeBtn) closeBtn.style.display = 'none';
    }
};

function schedulePreviewUpdate() {
    if (!previewVisible) return;
    clearTimeout(previewTimer);
    previewTimer = setTimeout(function() {
        // Silent save then reload preview
        readContactFromDOM();
        readSummaryFromDOM();
        var title = (document.getElementById('resumeTitle').value || 'My Resume').trim();
        fetch('/projects/resumex/edit/' + resumeId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({
                _token: csrfToken,
                title: title,
                template: themeSettings.key || 'ocean-blue',
                resume_data: resumeData,
                theme_settings: themeSettings
            })
        }).then(function() { updateLivePreview(); }).catch(function() { updateLivePreview(); });
    }, 600);
}

function applyPreviewScale() {
    var frame = document.getElementById('rxe-preview-frame');
    if (!frame) return;
    var wrap = document.getElementById('rxe-preview-iframe-wrap');
    // Use the wrap's actual rendered width (works in both desktop pane and mobile overlay)
    var paneW = wrap ? wrap.getBoundingClientRect().width : 0;
    if (!paneW) paneW = window.innerWidth; // fallback for mobile fullscreen
    // A4 page is 794px wide; scale down to fit the available width
    var scale = Math.min(1, (paneW - 8) / 794);
    frame.style.width = '794px';
    frame.style.transformOrigin = 'top left';
    frame.style.transform = 'scale(' + scale + ')';
    // Get the actual rendered content height (same-origin iframe)
    var contentH = 1123; // A4 default
    try {
        var doc = frame.contentDocument || (frame.contentWindow && frame.contentWindow.document);
        if (doc && doc.documentElement) {
            var h = doc.documentElement.scrollHeight || doc.body.scrollHeight;
            if (h > 50) contentH = h;
        }
    } catch(e) { /* cross-origin guard */ }
    frame.style.height = contentH + 'px';
    // Shrink the wrapper height so scroll tracks visual (scaled) height only
    if (wrap) {
        wrap.style.height = Math.ceil(contentH * scale) + 'px';
        wrap.style.overflow = 'hidden'; // no extra scroll beyond scaled content
    }
}

window.updateLivePreview = function() {
    var frame = document.getElementById('rxe-preview-frame');
    if (!frame) return;
    // Apply scale immediately with current height (avoids flash)
    applyPreviewScale();
    // Load the actual PHP preview in embed mode (no toolbar, pure A4 resume)
    var newSrc = '/projects/resumex/preview/' + resumeId + '?embed=1&_t=' + Date.now();
    // After iframe loads, recalculate height based on actual content
    frame.onload = function() { applyPreviewScale(); };
    frame.src = newSrc;
};


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
    document.querySelectorAll('.rxe-mobile-nav-btn').forEach(function (b) { b.classList.remove('active'); });
    var panel = document.getElementById('panel-' + name);
    var btn   = document.querySelector('[data-section="' + name + '"]');
    if (panel) panel.classList.add('active');
    if (btn)   btn.classList.add('active');
    // Also close mobile preview when navigating form sections
    if (mobilePreviewOpen) { window.toggleMobilePreview(); }
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
            template: themeSettings.key || 'ocean-blue',
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
            updateLivePreview();
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

/* ── Download as PDF ─────────────────────────────────────────── */
window.downloadResume = function() {
    var btn = document.getElementById('btnDownload');
    if (btn) { btn.style.opacity = '0.6'; btn.style.pointerEvents = 'none'; }
    showToast('Preparing download…', '');
    // Save latest content first, then try to download
    readContactFromDOM();
    readSummaryFromDOM();
    var title = (document.getElementById('resumeTitle').value || 'My Resume').trim();
    var safeTitle = title.replace(/[^a-zA-Z0-9_\-]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '') || 'resume';

    function restoreBtn() {
        if (btn) { btn.style.opacity = ''; btn.style.pointerEvents = ''; }
    }

    function triggerBlobDownload(blob, filename) {
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        setTimeout(function() { URL.revokeObjectURL(url); document.body.removeChild(a); }, 1000);
    }

    /* Client-side PDF using html2pdf.js — loaded lazily from CDN */
    function clientSidePdf() {
        showToast('Generating PDF…', '');

        function generate() {
            var iframe = document.createElement('iframe');
            iframe.style.cssText = 'position:fixed;left:-9999px;top:-9999px;width:794px;height:1px;opacity:0;pointer-events:none;border:none;';
            document.body.appendChild(iframe);

            iframe.onload = function() {
                // Allow 600ms for fonts and images to settle before rendering
                setTimeout(function() {
                    var paper = iframe.contentDocument && (iframe.contentDocument.querySelector('.rp-a4') || iframe.contentDocument.body);
                    if (!paper) {
                        document.body.removeChild(iframe);
                        showToast('Could not find resume content for PDF export.', 'error');
                        restoreBtn();
                        return;
                    }
                    html2pdf().from(paper).set({
                        margin: 0,
                        filename: safeTitle + '.pdf',
                        image: { type: 'jpeg', quality: 0.98 },
                        html2canvas: { scale: 2, useCORS: true, logging: false },
                        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                    }).save().then(function() {
                        document.body.removeChild(iframe);
                        showToast('Download started', 'success');
                        restoreBtn();
                    }).catch(function() {
                        document.body.removeChild(iframe);
                        showToast('PDF generation failed.', 'error');
                        restoreBtn();
                    });
                }, 600);
            };

            iframe.src = '/projects/resumex/preview/' + resumeId + '?embed=1&pdf=1';
        }

        if (typeof html2pdf !== 'undefined') {
            generate();
        } else {
            var script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
            script.crossOrigin = 'anonymous';
            script.onload = generate;
            script.onerror = function() {
                showToast('PDF library failed to load.', 'error');
                restoreBtn();
            };
            document.head.appendChild(script);
        }
    }

    // Step 1: Save resume data
    fetch('/projects/resumex/edit/' + resumeId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({
            _token: csrfToken,
            title: title,
            template: themeSettings.key || 'ocean-blue',
            resume_data: resumeData,
            theme_settings: themeSettings
        })
    }).then(function() {
        // Step 2: Try server-side PDF (Chromium)
        return fetch('/projects/resumex/download/' + resumeId);
    }).then(function(response) {
        var contentType = response.headers.get('Content-Type') || '';
        if (contentType.indexOf('application/pdf') !== -1) {
            // Server generated a real PDF — download it as a blob
            return response.blob().then(function(blob) {
                triggerBlobDownload(blob, safeTitle + '.pdf');
                showToast('Download started', 'success');
                restoreBtn();
            });
        } else {
            // Server returned HTML (no Chromium) — fall back to client-side PDF
            clientSidePdf();
        }
    }).catch(function() {
        // Network error — fall back to client-side PDF
        clientSidePdf();
    });
};

/* ── Print Resume ────────────────────────────────────────────── */
window.printResume = function() {
    readContactFromDOM();
    readSummaryFromDOM();
    var title = (document.getElementById('resumeTitle').value || 'My Resume').trim();
    var printUrl = '/projects/resumex/preview/' + resumeId + '?autoprint=1';
    showToast('Saving before print…', '');
    fetch('/projects/resumex/edit/' + resumeId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({
            _token: csrfToken,
            title: title,
            template: themeSettings.key || 'ocean-blue',
            resume_data: resumeData,
            theme_settings: themeSettings
        })
    }).finally(function() {
        window.open(printUrl, '_blank');
    });
};

/* ══════════════════════════════════════════════════════════════
   CONTACT
══════════════════════════════════════════════════════════════ */
function initContact() {
    var c = resumeData.contact || {};
    var fields = ['name','job_title','email','phone','location','website','linkedin','github','photo'];
    fields.forEach(function (f) {
        var el = document.getElementById('c_' + f);
        if (!el) return;
        el.value = val(c, f);
        el.addEventListener('input', function () { markDirty(); });
    });
}
function readContactFromDOM() {
    if (!resumeData.contact) resumeData.contact = {};
    var fields = ['name','job_title','email','phone','location','website','linkedin','github','photo'];
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
        // company is pre-escaped by esc() here; used directly (without another esc()) below in the header
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
                    '<div class="rxe-item-head-title">' + esc(title) + company + '</div>' +
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
    var box = document.getElementById('aiBullets' + i);
    if (!exp.title || !exp.title.trim()) {
        box.innerHTML = '<div style="font-size:0.8rem;color:var(--text-secondary);padding:8px 0;">Please type a <strong>Job Title</strong> above first, then click AI Bullet Suggestions.</div>';
        box.classList.add('open');
        return;
    }
    box.innerHTML = '<div style="font-size:0.8rem;color:var(--text-secondary);padding:8px 0;">⏳ Generating suggestions…</div>';
    box.classList.add('open');
    var fd = new FormData();
    fd.append('_token', csrfToken);
    fd.append('job_title', exp.title || '');
    fd.append('company', exp.company || '');
    fetch('/projects/resumex/ai/suggest-bullets', { method: 'POST', body: fd })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!data.success) {
            box.innerHTML = '<div style="font-size:0.8rem;color:var(--red);padding:8px 0;">' + esc(data.message || 'Could not generate suggestions. Please try again.') + '</div>';
            return;
        }
        box.innerHTML = '<div style="font-size:0.75rem;font-weight:700;color:var(--text-secondary);margin-bottom:8px;">Click to add a bullet:</div>' +
            data.bullets.map(function (b) {
                return '<div class="rxe-ai-suggestion-item" onclick="addBulletFromAI(' + i + ',this.textContent)">' + esc(b) + '</div>';
            }).join('');
    })
    .catch(function () {
        box.innerHTML = '<div style="font-size:0.8rem;color:var(--red);padding:8px 0;">Network error. Please check your connection and try again.</div>';
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
        var activePrimary = (themeSettings.key === t.key) ? themeSettings.primaryColor : t.primaryColor;
        return '<div class="rxe-theme-card ' + active + '" onclick="selectTheme(\'' + esc(t.key) + '\')">' +
            '<div class="rxe-theme-preview" style="background:' + esc(t.backgroundColor) + '">' +
                '<div>' +
                    '<div class="rxe-theme-preview-line" style="background:' + esc(activePrimary) + '"></div>' +
                    '<div class="rxe-theme-preview-line short" style="background:' + esc(t.secondaryColor) + '; opacity:0.7"></div>' +
                    '<div class="rxe-theme-preview-line" style="background:' + esc(t.textColor) + '; opacity:0.35; width:80%"></div>' +
                    '<div class="rxe-theme-preview-line short" style="background:' + esc(t.textColor) + '; opacity:0.2"></div>' +
                '</div>' +
            '</div>' +
            '<div class="rxe-theme-name" style="background:' + esc(t.surfaceColor) + '; color:' + esc(t.textColor) + '">' + esc(t.name) + '</div>' +
        '</div>';
    }).join('');
    renderColourPalette();
}

function renderColourPalette() {
    var palette = document.getElementById('rxe-colour-palette');
    if (!palette) return;
    var t = allThemes[themeSettings.key];
    if (!t || !t.colorVariants) { palette.innerHTML = ''; return; }
    palette.innerHTML = t.colorVariants.map(function (v, vi) {
        var isActive = (themeSettings.primaryColor === v.primary);
        return '<div class="rxe-colour-swatch' + (isActive ? ' active' : '') + '" onclick="applyColorVariant(\'' + esc(t.key) + '\',' + vi + ')" title="' + esc(v.label) + '">' +
            '<div class="rxe-colour-swatch-dot" style="background:' + esc(v.primary) + '"></div>' +
            '<div class="rxe-colour-swatch-label">' + esc(v.label) + '</div>' +
        '</div>';
    }).join('');
}

window.selectTheme = function (key) {
    if (!allThemes[key]) return;
    themeSettings = JSON.parse(JSON.stringify(allThemes[key]));
    renderThemeGrid();
    markDirty();
};
window.applyColorVariant = function (key, variantIndex) {
    if (!allThemes[key]) return;
    var t = allThemes[key];
    if (!t.colorVariants || !t.colorVariants[variantIndex]) return;
    var variant = t.colorVariants[variantIndex];
    // Apply or switch to this theme with the variant colors
    if (themeSettings.key !== key) {
        themeSettings = JSON.parse(JSON.stringify(t));
    }
    themeSettings.primaryColor   = variant.primary;
    themeSettings.secondaryColor = variant.secondary;
    renderColourPalette();
    renderThemeGrid();
    markDirty();
};

/* ══════════════════════════════════════════════════════════════
   TEMPLATE PICKER MODAL
══════════════════════════════════════════════════════════════ */
(function () {
    var modal = document.getElementById('rxe-tpl-modal');
    var grid  = document.getElementById('rxe-tpl-grid');
    var currentFilter = 'all';

    var layoutFilterMap = {
        'sidebar-left': 'sidebar', 'sidebar-dark': 'sidebar',
        'full-header': 'professional', 'banner': 'creative',
        'timeline': 'timeline', 'minimal': 'minimal',
        'developer': 'dark', 'academic': 'professional',
        'single': 'professional',
    };

    function getFilterGroup(t) {
        var cat = (t.category || '').toLowerCase();
        var ls  = t.layoutStyle || 'single';
        if (ls === 'sidebar-left' || ls === 'sidebar-dark') return 'sidebar';
        if (ls === 'timeline') return 'timeline';
        if (ls === 'minimal') return 'minimal';
        if (ls === 'developer') return 'dark';
        if (ls === 'banner') return 'creative';
        if (cat === 'creative' || cat === 'tech' || cat === 'bold') return 'creative';
        if (cat === 'dark') return 'dark';
        if (cat === 'minimal') return 'minimal';
        return 'professional';
    }

    function renderThumbSvg(t) {
        var bg   = t.backgroundColor || '#0a0a0f';
        var surf = t.surfaceColor    || '#12121e';
        var pri  = t.primaryColor    || '#00f0ff';
        var sec  = t.secondaryColor  || '#9945ff';
        var tx   = t.textColor       || '#e0e6ff';
        var ls   = t.layoutStyle     || 'single';

        var svg = '';
        if (ls === 'sidebar-left' || ls === 'sidebar-dark') {
            svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 120" style="width:100%;height:100%;display:block;">'
                + '<rect width="170" height="120" fill="' + bg + '"/>'
                // sidebar
                + '<rect x="0" y="0" width="52" height="120" fill="' + surf + '"/>'
                // photo circle
                + '<circle cx="26" cy="22" r="12" fill="' + pri + '44" stroke="' + pri + '" stroke-width="1.5"/>'
                // name lines
                + '<rect x="8" y="38" width="36" height="4" rx="2" fill="' + pri + 'cc"/>'
                + '<rect x="12" y="45" width="28" height="2.5" rx="1.5" fill="' + pri + '66"/>'
                // sidebar section dots
                + '<rect x="8" y="56" width="20" height="2" rx="1" fill="' + pri + '88"/>'
                + '<rect x="8" y="62" width="36" height="2" rx="1" fill="' + tx + '33"/>'
                + '<rect x="8" y="67" width="30" height="2" rx="1" fill="' + tx + '22"/>'
                + '<rect x="8" y="74" width="20" height="2" rx="1" fill="' + sec + '88"/>'
                + '<rect x="8" y="80" width="34" height="2" rx="1" fill="' + tx + '33"/>'
                + '<rect x="8" y="85" width="26" height="2" rx="1" fill="' + tx + '22"/>'
                // main area header
                + '<rect x="60" y="10" width="90" height="5" rx="2" fill="' + pri + '"/>'
                + '<rect x="60" y="18" width="60" height="3" rx="1.5" fill="' + tx + '66"/>'
                + '<rect x="60" y="28" width="100" height="2" rx="1" fill="' + pri + '44"/>'
                // main body lines
                + '<rect x="60" y="35" width="65" height="2" rx="1" fill="' + tx + '44"/>'
                + '<rect x="60" y="40" width="80" height="2" rx="1" fill="' + tx + '33"/>'
                + '<rect x="60" y="45" width="55" height="2" rx="1" fill="' + tx + '22"/>'
                + '<rect x="60" y="55" width="90" height="2" rx="1" fill="' + pri + '66"/>'
                + '<rect x="60" y="61" width="70" height="2" rx="1" fill="' + tx + '33"/>'
                + '<rect x="60" y="66" width="80" height="2" rx="1" fill="' + tx + '22"/>'
                + '<rect x="60" y="71" width="50" height="2" rx="1" fill="' + tx + '33"/>'
                + '</svg>';
        } else if (ls === 'banner') {
            svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 120" style="width:100%;height:100%;display:block;">'
                + '<rect width="170" height="120" fill="' + bg + '"/>'
                // banner header
                + '<rect x="0" y="0" width="170" height="40" fill="' + pri + '22"/>'
                + '<rect x="0" y="37" width="170" height="3" fill="' + pri + '"/>'
                // name
                + '<rect x="12" y="8" width="75" height="7" rx="3" fill="' + pri + '"/>'
                + '<rect x="12" y="18" width="50" height="4" rx="2" fill="' + tx + '88"/>'
                // photo circle top-right
                + '<circle cx="148" cy="18" r="14" fill="' + pri + '33" stroke="' + pri + '" stroke-width="1.5"/>'
                // skill chips
                + '<rect x="12" y="28" width="22" height="5" rx="3" fill="' + pri + '33" stroke="' + pri + '" stroke-width="0.5"/>'
                + '<rect x="38" y="28" width="28" height="5" rx="3" fill="' + pri + '33" stroke="' + pri + '" stroke-width="0.5"/>'
                // body lines
                + '<rect x="12" y="52" width="50" height="3" rx="1.5" fill="' + pri + '88"/>'
                + '<rect x="12" y="58" width="146" height="2" rx="1" fill="' + tx + '44"/>'
                + '<rect x="12" y="63" width="120" height="2" rx="1" fill="' + tx + '33"/>'
                + '<rect x="12" y="72" width="50" height="3" rx="1.5" fill="' + sec + '88"/>'
                + '<rect x="12" y="78" width="140" height="2" rx="1" fill="' + tx + '33"/>'
                + '<rect x="12" y="83" width="100" height="2" rx="1" fill="' + tx + '22"/>'
                + '</svg>';
        } else if (ls === 'full-header') {
            svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 120" style="width:100%;height:100%;display:block;">'
                + '<rect width="170" height="120" fill="' + bg + '"/>'
                // full header band
                + '<rect x="0" y="0" width="170" height="36" fill="' + surf + '"/>'
                + '<rect x="0" y="33" width="170" height="3" fill="' + pri + '"/>'
                // photo in header
                + '<circle cx="20" cy="18" r="11" fill="' + pri + '44" stroke="' + pri + '" stroke-width="1.5"/>'
                // name
                + '<rect x="36" y="8" width="70" height="6" rx="2" fill="' + pri + '"/>'
                + '<rect x="36" y="17" width="45" height="3.5" rx="1.5" fill="' + tx + '66"/>'
                + '<rect x="36" y="23" width="95" height="2" rx="1" fill="' + tx + '33"/>'
                // two column body
                + '<rect x="0" y="39" width="60" height="78" fill="' + surf + '11"/>'
                + '<rect x="60" y="39" width="1.5" height="78" fill="' + pri + '22"/>'
                // left col
                + '<rect x="8" y="46" width="28" height="2.5" rx="1.5" fill="' + pri + '88"/>'
                + '<rect x="8" y="52" width="44" height="2" rx="1" fill="' + tx + '33"/>'
                + '<rect x="8" y="57" width="36" height="2" rx="1" fill="' + tx + '22"/>'
                + '<rect x="8" y="68" width="28" height="2.5" rx="1.5" fill="' + sec + '88"/>'
                + '<rect x="8" y="74" width="44" height="2" rx="1" fill="' + tx + '33"/>'
                // right col
                + '<rect x="68" y="46" width="50" height="2.5" rx="1.5" fill="' + pri + '88"/>'
                + '<rect x="68" y="52" width="90" height="2" rx="1" fill="' + tx + '44"/>'
                + '<rect x="68" y="57" width="80" height="2" rx="1" fill="' + tx + '33"/>'
                + '<rect x="68" y="62" width="60" height="2" rx="1" fill="' + tx + '22"/>'
                + '<rect x="68" y="72" width="50" height="2.5" rx="1.5" fill="' + sec + '88"/>'
                + '<rect x="68" y="78" width="85" height="2" rx="1" fill="' + tx + '33"/>'
                + '</svg>';
        } else if (ls === 'timeline') {
            svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 120" style="width:100%;height:100%;display:block;">'
                + '<rect width="170" height="120" fill="' + bg + '"/>'
                + '<rect x="0" y="0" width="170" height="30" fill="' + surf + '"/>'
                + '<rect x="0" y="27" width="170" height="3" fill="' + pri + '"/>'
                + '<rect x="12" y="7" width="60" height="6" rx="2" fill="' + pri + '"/>'
                + '<rect x="12" y="16" width="40" height="3" rx="1.5" fill="' + tx + '66"/>'
                // timeline line
                + '<line x1="24" y1="38" x2="24" y2="115" stroke="' + pri + '44" stroke-width="2"/>'
                // dots and content
                + '<circle cx="24" cy="44" r="4" fill="' + pri + '"/>'
                + '<rect x="34" y="41" width="60" height="3.5" rx="1.5" fill="' + tx + 'cc"/>'
                + '<rect x="34" y="47" width="45" height="2.5" rx="1" fill="' + pri + '99"/>'
                + '<rect x="34" y="52" width="90" height="2" rx="1" fill="' + tx + '33"/>'
                + '<circle cx="24" cy="65" r="4" fill="' + pri + '"/>'
                + '<rect x="34" y="62" width="55" height="3.5" rx="1.5" fill="' + tx + 'cc"/>'
                + '<rect x="34" y="68" width="40" height="2.5" rx="1" fill="' + pri + '99"/>'
                + '<rect x="34" y="73" width="80" height="2" rx="1" fill="' + tx + '33"/>'
                + '<circle cx="24" cy="86" r="4" fill="' + sec + '"/>'
                + '<rect x="34" y="83" width="50" height="3.5" rx="1.5" fill="' + tx + 'cc"/>'
                + '<rect x="34" y="89" width="38" height="2.5" rx="1" fill="' + sec + '99"/>'
                + '</svg>';
        } else if (ls === 'minimal') {
            svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 120" style="width:100%;height:100%;display:block;">'
                + '<rect width="170" height="120" fill="' + bg + '"/>'
                + '<rect x="12" y="12" width="90" height="8" rx="2" fill="' + pri + '"/>'
                + '<rect x="12" y="23" width="55" height="4" rx="2" fill="' + tx + '88"/>'
                + '<rect x="12" y="30" width="146" height="1.5" rx="1" fill="' + pri + '66"/>'
                + '<rect x="12" y="34" width="100" height="2.5" rx="1" fill="' + tx + '44"/>'
                + '<rect x="12" y="40" width="80" height="2" rx="1" fill="' + tx + '33"/>'
                // section title minimal
                + '<rect x="12" y="52" width="40" height="2.5" rx="1" fill="' + pri + 'cc"/>'
                + '<rect x="12" y="58" width="130" height="2" rx="1" fill="' + tx + '44"/>'
                + '<rect x="12" y="63" width="110" height="2" rx="1" fill="' + tx + '33"/>'
                + '<rect x="12" y="68" width="120" height="2" rx="1" fill="' + tx + '22"/>'
                + '<rect x="12" y="78" width="40" height="2.5" rx="1" fill="' + sec + 'cc"/>'
                + '<rect x="12" y="84" width="120" height="2" rx="1" fill="' + tx + '33"/>'
                + '<rect x="12" y="89" width="90" height="2" rx="1" fill="' + tx + '22"/>'
                + '</svg>';
        } else if (ls === 'developer') {
            svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 120" style="width:100%;height:100%;display:block;">'
                + '<rect width="170" height="120" fill="' + bg + '"/>'
                + '<rect x="0" y="0" width="170" height="34" fill="' + surf + '"/>'
                + '<rect x="0" y="31" width="170" height="3" fill="' + pri + '44"/>'
                // $ name
                + '<rect x="10" y="6" width="10" height="5" rx="1" fill="' + pri + '44"/>'
                + '<rect x="22" y="6" width="65" height="8" rx="2" fill="' + pri + '"/>'
                + '<rect x="10" y="17" width="14" height="3" rx="1" fill="' + tx + '44"/>'
                + '<rect x="26" y="17" width="50" height="3" rx="1" fill="' + tx + '66"/>'
                // code lines
                + '<rect x="10" y="10" width="1.5" height="60" fill="' + pri + '66"/>'
                + '<rect x="18" y="40" width="30" height="3.5" rx="1" fill="' + pri + '88"/>'
                + '<rect x="18" y="46" width="130" height="2" rx="1" fill="' + tx + '33"/>'
                + '<rect x="18" y="51" width="100" height="2" rx="1" fill="' + tx + '22"/>'
                + '<rect x="18" y="60" width="30" height="3.5" rx="1" fill="' + sec + '88"/>'
                + '<rect x="18" y="66" width="120" height="2" rx="1" fill="' + tx + '33"/>'
                + '<rect x="18" y="71" width="90" height="2" rx="1" fill="' + tx + '22"/>'
                + '</svg>';
        } else if (ls === 'academic') {
            svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 120" style="width:100%;height:100%;display:block;">'
                + '<rect width="170" height="120" fill="' + bg + '"/>'
                // centred header
                + '<rect x="40" y="8" width="90" height="8" rx="2" fill="' + tx + 'dd"/>'
                + '<rect x="60" y="19" width="50" height="4" rx="2" fill="' + pri + '99"/>'
                + '<rect x="30" y="26" width="110" height="2" rx="1" fill="' + tx + '44"/>'
                + '<rect x="0" y="30" width="170" height="3" fill="' + pri + '"/>'
                + '<rect x="50" y="35" width="70" height="1.5" fill="' + pri + '22"/>'
                // sections
                + '<rect x="12" y="44" width="50" height="3" rx="1.5" fill="' + pri + 'aa"/>'
                + '<rect x="12" y="48" width="146" height="1.5" fill="' + pri + '33"/>'
                + '<rect x="12" y="53" width="130" height="2" rx="1" fill="' + tx + '44"/>'
                + '<rect x="12" y="58" width="110" height="2" rx="1" fill="' + tx + '33"/>'
                + '<rect x="12" y="70" width="50" height="3" rx="1.5" fill="' + sec + 'aa"/>'
                + '<rect x="12" y="74" width="146" height="1.5" fill="' + sec + '33"/>'
                + '<rect x="12" y="79" width="120" height="2" rx="1" fill="' + tx + '33"/>'
                + '</svg>';
        } else {
            // single / default
            svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 120" style="width:100%;height:100%;display:block;">'
                + '<rect width="170" height="120" fill="' + bg + '"/>'
                + '<rect x="0" y="0" width="170" height="36" fill="' + surf + '"/>'
                + '<rect x="0" y="33" width="170" height="3" fill="' + pri + '"/>'
                + '<circle cx="20" cy="18" r="11" fill="' + pri + '33" stroke="' + pri + '" stroke-width="1.5"/>'
                + '<rect x="36" y="8" width="65" height="7" rx="2" fill="' + pri + '"/>'
                + '<rect x="36" y="18" width="45" height="3.5" rx="1.5" fill="' + tx + '66"/>'
                + '<rect x="36" y="24" width="80" height="2" rx="1" fill="' + tx + '33"/>'
                // body
                + '<rect x="12" y="44" width="50" height="3" rx="1.5" fill="' + pri + '88"/>'
                + '<rect x="12" y="48" width="146" height="1.5" fill="' + pri + '33"/>'
                + '<rect x="12" y="53" width="130" height="2" rx="1" fill="' + tx + '44"/>'
                + '<rect x="12" y="58" width="110" height="2" rx="1" fill="' + tx + '33"/>'
                + '<rect x="12" y="63" width="120" height="2" rx="1" fill="' + tx + '22"/>'
                + '<rect x="12" y="73" width="50" height="3" rx="1.5" fill="' + sec + '88"/>'
                + '<rect x="12" y="77" width="146" height="1.5" fill="' + sec + '22"/>'
                + '<rect x="12" y="82" width="120" height="2" rx="1" fill="' + tx + '33"/>'
                + '<rect x="12" y="87" width="90" height="2" rx="1" fill="' + tx + '22"/>'
                + '</svg>';
        }
        return svg;
    }

    function renderGrid(filter) {
        currentFilter = filter || 'all';
        var html = '';
        Object.values(allThemes).forEach(function (t) {
            var fg = getFilterGroup(t);
            if (currentFilter !== 'all' && fg !== currentFilter) return;
            var isCurrent = (themeSettings.key === t.key);
            var ls = t.layoutStyle || 'single';
            var layoutLabel = ls.replace('-', ' ');
            html += '<div class="rxe-tpl-card' + (isCurrent ? ' active-tpl' : '') + '" onclick="window.selectTplFromPicker(\'' + esc(t.key) + '\')">'
                + '<div class="rxe-tpl-thumb">' + renderThumbSvg(t) + '</div>'
                + '<div class="rxe-tpl-info">'
                + '<div class="rxe-tpl-name">' + esc(t.name) + '</div>'
                + '<div class="rxe-tpl-cat">' + esc(t.category || 'general') + '</div>'
                + (isCurrent ? '<span class="rxe-tpl-badge">&#10003; Active</span>' : '')
                + '<span class="rxe-tpl-layout-tag">' + esc(layoutLabel) + '</span>'
                + '</div></div>';
        });
        grid.innerHTML = html || '<div style="padding:24px;color:var(--text-secondary);grid-column:1/-1;">No templates found.</div>';
    }

    document.getElementById('rxe-tpl-filters').addEventListener('click', function (e) {
        var btn = e.target.closest('.rxe-tpl-filter-btn');
        if (!btn) return;
        document.querySelectorAll('.rxe-tpl-filter-btn').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        renderGrid(btn.dataset.filter);
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeTemplatePicker();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('open')) closeTemplatePicker();
    });

    window.openTemplatePicker = function () {
        renderGrid(currentFilter);
        modal.classList.add('open');
    };
    window.closeTemplatePicker = function () {
        modal.classList.remove('open');
    };
    window.selectTplFromPicker = function (key) {
        window.selectTheme(key);
        closeTemplatePicker();
        schedulePreviewUpdate();
        showToast('Template changed to "' + (allThemes[key] ? allThemes[key].name : key) + '"', 'success');
    };
}());

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
   GLOBAL EXPORTS FOR INLINE EVENT HANDLERS
   resumeData, markDirty and renderExperience are declared
   inside this IIFE so inline on* attributes (which run in the
   global scope) can't reach them unless we expose them on window.
══════════════════════════════════════════════════════════════ */
window.resumeData       = resumeData;
window.markDirty        = markDirty;
window.renderExperience = renderExperience;

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

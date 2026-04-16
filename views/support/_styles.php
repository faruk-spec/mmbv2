<?php
/**
 * Support Portal — shared CSS styles
 * Include inside a View::section('styles') block in every support view.
 */
?>
<style>
/* ── Support portal layout ───────────────────────────────────────────── */
.dashboard-main-content { padding: 0 !important; }

/* ── Gradient buttons ────────────────────────────────────────────────── */
.sp-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 18px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: .875rem;
    cursor: pointer;
    text-decoration: none;
    transition: opacity .15s, box-shadow .15s;
}
.sp-btn:hover { opacity: .88; }
.sp-btn-primary {
    background: linear-gradient(135deg, var(--cyan), var(--magenta));
    color: #fff !important;
}
.sp-btn-sm { padding: 5px 12px; font-size: .78rem; border-radius: 6px; }
.sp-btn-outline {
    background: transparent;
    border: 1px solid var(--border-color);
    color: var(--text-secondary) !important;
}
.sp-btn-outline:hover { border-color: var(--cyan); color: var(--cyan) !important; }

/* ── Status / Priority badges ────────────────────────────────────────── */
.sp-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: .72rem;
    font-weight: 600;
}
.sp-badge-open   { color: var(--cyan);    background: color-mix(in srgb, var(--cyan)    12%, transparent); }
.sp-badge-prog   { color: var(--orange);  background: color-mix(in srgb, var(--orange)  12%, transparent); }
.sp-badge-wait   { color: var(--purple);  background: color-mix(in srgb, var(--purple)  12%, transparent); }
.sp-badge-done   { color: var(--green);   background: color-mix(in srgb, var(--green)   12%, transparent); }
.sp-badge-closed { color: var(--text-secondary); background: color-mix(in srgb, var(--text-secondary) 12%, transparent); }
.sp-badge-low    { color: var(--text-secondary); background: color-mix(in srgb, var(--text-secondary) 12%, transparent); }
.sp-badge-medium { color: var(--cyan);    background: color-mix(in srgb, var(--cyan)    12%, transparent); }
.sp-badge-high   { color: var(--orange);  background: color-mix(in srgb, var(--orange)  12%, transparent); }
.sp-badge-urgent { color: var(--red);     background: color-mix(in srgb, var(--red)     12%, transparent); }

/* ── Cards / panels ──────────────────────────────────────────────────── */
.sp-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
}
.sp-card-header {
    border-bottom: 1px solid var(--border-color);
    padding: 14px 18px;
}

/* ── Message bubbles ─────────────────────────────────────────────────── */
.sp-msg-user {
    background: color-mix(in srgb, var(--cyan) 8%, var(--bg-card));
    border: 1px solid color-mix(in srgb, var(--cyan) 20%, transparent);
}
.sp-msg-agent {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
}
.sp-msg-internal {
    background: color-mix(in srgb, var(--orange) 6%, var(--bg-card));
    border: 1px dashed color-mix(in srgb, var(--orange) 30%, transparent);
}

/* ── Filter tab links ────────────────────────────────────────────────── */
.sp-filter-tab {
    padding: 5px 14px;
    border-radius: 6px;
    font-size: .78rem;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid var(--border-color);
    background: var(--bg-card);
    color: var(--text-secondary);
    transition: all .15s;
}
.sp-filter-tab:hover { border-color: var(--cyan); color: var(--cyan); }
.sp-filter-tab.active {
    background: color-mix(in srgb, var(--cyan) 15%, transparent);
    color: var(--cyan);
    border-color: color-mix(in srgb, var(--cyan) 35%, transparent);
}

/* ── Action link (View / Manage) ─────────────────────────────────────── */
.sp-action-view   { background: color-mix(in srgb, var(--cyan)   10%, transparent); color: var(--cyan) !important; }
.sp-action-manage { background: color-mix(in srgb, var(--purple) 10%, transparent); color: var(--purple) !important; }
.sp-action-view,
.sp-action-manage {
    padding: 4px 10px;
    border-radius: 5px;
    text-decoration: none;
    font-size: .76rem;
    font-weight: 500;
    transition: opacity .15s;
}
.sp-action-view:hover,
.sp-action-manage:hover { opacity: .75; }

/* ── Table rows ──────────────────────────────────────────────────────── */
.sp-tr:hover { background: color-mix(in srgb, var(--cyan) 3%, transparent) !important; }

/* ── Stat accent bar ─────────────────────────────────────────────────── */
.sp-stat-bar {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
}

/* ── Section header text ─────────────────────────────────────────────── */
.sp-section-heading {
    color: var(--text-primary);
    font-size: .95rem;
    font-weight: 600;
}
.sp-accent-icon { color: var(--cyan); }

/* ── Textarea / form controls ────────────────────────────────────────── */
.sp-textarea {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: .9rem;
    outline: none;
    resize: vertical;
    box-sizing: border-box;
    transition: border-color .15s;
}
.sp-textarea:focus { border-color: var(--cyan); }
.sp-select {
    padding: 7px 12px;
    border: 1px solid var(--border-color);
    border-radius: 7px;
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: .875rem;
    outline: none;
    cursor: pointer;
}
.sp-select:focus { border-color: var(--cyan); }

/* ── Light-theme specific overrides ─────────────────────────────────── */
[data-theme="light"] .sp-card { box-shadow: 0 1px 6px rgba(0,0,0,.07); }
[data-theme="light"] .sp-msg-user { border-color: rgba(3,105,161,.2); }
[data-theme="light"] .sp-textarea { background: #fff; }
[data-theme="light"] .sp-select { background: #fff; }

/* ── Responsive layout ───────────────────────────────────────────────── */
.sp-layout {
    display: flex;
    min-height: calc(100vh - 64px);
    align-items: stretch;
}
.sp-sidebar {
    width: 240px;
    min-width: 240px;
    background: var(--bg-primary, #08080f);
    border-right: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 64px);
    position: sticky;
    top: 64px;
    height: calc(100vh - 64px);
    overflow-y: auto;
    z-index: 10;
    transition: transform .25s ease;
    flex-shrink: 0;
}
.sp-main {
    flex: 1;
    padding: 24px 28px;
    min-width: 0;
    overflow: auto;
}
.sp-sidebar-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .55);
    z-index: 98;
    cursor: pointer;
}
.sp-menu-btn {
    display: none;
    align-items: center;
    gap: 8px;
    padding: 7px 12px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-secondary);
    cursor: pointer;
    font-size: .82rem;
    margin-bottom: 16px;
    width: fit-content;
    transition: color .15s, border-color .15s;
}
.sp-live-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

@media (max-width: 768px) {
    .sp-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        transform: translateX(-100%);
        z-index: 99;
        min-height: 100vh;
    }
    .sp-sidebar.sp-open {
        transform: translateX(0);
    }
    .sp-sidebar-overlay.sp-open {
        display: block;
    }
    .sp-main {
        padding: 16px;
        width: 100%;
    }
    .sp-menu-btn {
        display: inline-flex;
    }
    .sp-live-grid {
        grid-template-columns: 1fr;
    }
}
</style>

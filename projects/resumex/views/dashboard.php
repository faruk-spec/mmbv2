<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<style>
    .rx-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 32px 24px;
    }

    /* ── Header ─────────────────────────────────────────────── */
    .rx-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 36px;
        flex-wrap: wrap;
    }
    .rx-header-left h1 {
        font-size: 2.2rem;
        font-weight: 800;
        letter-spacing: -0.5px;
        line-height: 1.1;
        background: linear-gradient(135deg, var(--cyan) 0%, var(--purple) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0 0 6px;
    }
    .rx-header-left p {
        color: var(--text-secondary);
        font-size: 0.95rem;
        margin: 0;
    }
    .rx-btn-create {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 22px;
        background: linear-gradient(135deg, var(--cyan), var(--purple));
        color: #06060a;
        font-weight: 700;
        font-size: 0.9rem;
        border-radius: 10px;
        text-decoration: none;
        transition: var(--transition);
        white-space: nowrap;
        box-shadow: 0 4px 20px rgba(0, 240, 255, 0.25);
        flex-shrink: 0;
    }
    .rx-btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 240, 255, 0.4);
        color: #06060a;
        text-decoration: none;
    }

    /* ── Stats Row ───────────────────────────────────────────── */
    .rx-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 36px;
    }
    .rx-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 22px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }
    .rx-stat-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 12px;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .rx-stat-card:hover::before { opacity: 1; }
    .rx-stat-card:hover { transform: translateY(-2px); }

    .rx-stat-card.cyan { border-color: rgba(0, 240, 255, 0.25); }
    .rx-stat-card.cyan::before { background: rgba(0, 240, 255, 0.04); }
    .rx-stat-card.cyan:hover { box-shadow: 0 6px 24px rgba(0, 240, 255, 0.2); }

    .rx-stat-card.purple { border-color: rgba(153, 69, 255, 0.25); }
    .rx-stat-card.purple::before { background: rgba(153, 69, 255, 0.04); }
    .rx-stat-card.purple:hover { box-shadow: 0 6px 24px rgba(153, 69, 255, 0.2); }

    .rx-stat-card.green { border-color: rgba(0, 255, 136, 0.25); }
    .rx-stat-card.green::before { background: rgba(0, 255, 136, 0.04); }
    .rx-stat-card.green:hover { box-shadow: 0 6px 24px rgba(0, 255, 136, 0.2); }

    .rx-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .rx-stat-icon.cyan { background: rgba(0, 240, 255, 0.12); color: var(--cyan); }
    .rx-stat-icon.purple { background: rgba(153, 69, 255, 0.12); color: var(--purple); }
    .rx-stat-icon.green { background: rgba(0, 255, 136, 0.12); color: var(--green); }

    .rx-stat-val {
        font-size: 1.9rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }
    .rx-stat-card.cyan .rx-stat-val { color: var(--cyan); }
    .rx-stat-card.purple .rx-stat-val { color: var(--purple); }
    .rx-stat-card.green .rx-stat-val { color: var(--green); }

    .rx-stat-label {
        font-size: 0.82rem;
        color: var(--text-secondary);
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    /* ── Section title ───────────────────────────────────────── */
    .rx-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .rx-section-head h2 {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }
    .rx-count-badge {
        background: rgba(0, 240, 255, 0.1);
        color: var(--cyan);
        border: 1px solid rgba(0, 240, 255, 0.2);
        border-radius: 20px;
        padding: 3px 12px;
        font-size: 0.78rem;
        font-weight: 600;
    }

    /* ── Resume Grid ─────────────────────────────────────────── */
    .rx-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    /* ── Resume Card ─────────────────────────────────────────── */
    .rx-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 22px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        transition: var(--transition);
        position: relative;
    }
    .rx-card:hover {
        border-color: rgba(0, 240, 255, 0.3);
        box-shadow: 0 8px 32px rgba(0, 240, 255, 0.1);
        transform: translateY(-3px);
    }

    .rx-card-accent {
        position: absolute;
        top: 0;
        left: 22px;
        right: 22px;
        height: 2px;
        border-radius: 0 0 4px 4px;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .rx-card:hover .rx-card-accent { opacity: 1; }

    .rx-card-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rx-card-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .rx-theme-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
        box-shadow: 0 0 6px currentColor;
    }
    .rx-card-meta-text {
        font-size: 0.82rem;
        color: var(--text-secondary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .rx-card-meta-sep {
        color: var(--border-color);
        font-size: 0.75rem;
    }

    .rx-card-date {
        font-size: 0.78rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .rx-card-date svg { opacity: 0.5; flex-shrink: 0; }

    .rx-card-actions {
        display: flex;
        gap: 7px;
        margin-top: auto;
        flex-wrap: wrap;
    }
    .rx-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 7px 13px;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        border: 1px solid transparent;
        transition: var(--transition);
        background: none;
        line-height: 1;
        white-space: nowrap;
    }
    .rx-action-btn.edit {
        background: rgba(0, 240, 255, 0.1);
        border-color: rgba(0, 240, 255, 0.25);
        color: var(--cyan);
    }
    .rx-action-btn.edit:hover {
        background: rgba(0, 240, 255, 0.2);
        box-shadow: 0 0 12px rgba(0, 240, 255, 0.25);
        text-decoration: none;
        color: var(--cyan);
    }
    .rx-action-btn.preview {
        background: rgba(153, 69, 255, 0.1);
        border-color: rgba(153, 69, 255, 0.25);
        color: var(--purple);
    }
    .rx-action-btn.preview:hover {
        background: rgba(153, 69, 255, 0.2);
        box-shadow: 0 0 12px rgba(153, 69, 255, 0.2);
        text-decoration: none;
        color: var(--purple);
    }
    .rx-action-btn.duplicate {
        background: rgba(0, 255, 136, 0.08);
        border-color: rgba(0, 255, 136, 0.2);
        color: var(--green);
    }
    .rx-action-btn.duplicate:hover {
        background: rgba(0, 255, 136, 0.16);
        box-shadow: 0 0 12px rgba(0, 255, 136, 0.2);
        text-decoration: none;
        color: var(--green);
    }
    .rx-action-btn.delete {
        background: rgba(255, 107, 107, 0.08);
        border-color: rgba(255, 107, 107, 0.2);
        color: var(--red);
    }
    .rx-action-btn.delete:hover {
        background: rgba(255, 107, 107, 0.18);
        box-shadow: 0 0 12px rgba(255, 107, 107, 0.2);
        text-decoration: none;
        color: var(--red);
    }

    /* ── Empty State ─────────────────────────────────────────── */
    .rx-empty {
        text-align: center;
        padding: 72px 24px;
        background: var(--bg-card);
        border: 1px dashed rgba(0, 240, 255, 0.2);
        border-radius: 16px;
    }
    .rx-empty-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 24px;
        background: rgba(0, 240, 255, 0.07);
        border: 1px solid rgba(0, 240, 255, 0.15);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--cyan);
    }
    .rx-empty h3 {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0 0 10px;
        color: var(--text-primary);
    }
    .rx-empty p {
        color: var(--text-secondary);
        font-size: 0.93rem;
        margin: 0 0 28px;
        max-width: 340px;
        margin-left: auto;
        margin-right: auto;
    }

    /* ── Modal ───────────────────────────────────────────────── */
    .rx-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(6, 6, 10, 0.85);
        backdrop-filter: blur(4px);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s;
    }
    .rx-modal-overlay.open {
        opacity: 1;
        pointer-events: all;
    }
    .rx-modal {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 32px;
        max-width: 420px;
        width: 100%;
        transform: translateY(16px) scale(0.97);
        transition: transform 0.25s;
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.6);
    }
    .rx-modal-overlay.open .rx-modal {
        transform: translateY(0) scale(1);
    }
    .rx-modal-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    .rx-modal-icon.danger {
        background: rgba(255, 107, 107, 0.1);
        border: 1px solid rgba(255, 107, 107, 0.25);
        color: var(--red);
    }
    .rx-modal-icon.success {
        background: rgba(0, 255, 136, 0.1);
        border: 1px solid rgba(0, 255, 136, 0.25);
        color: var(--green);
    }
    .rx-modal h3 {
        font-size: 1.2rem;
        font-weight: 700;
        text-align: center;
        margin: 0 0 10px;
    }
    .rx-modal p {
        color: var(--text-secondary);
        font-size: 0.9rem;
        text-align: center;
        margin: 0 0 24px;
        line-height: 1.6;
    }
    .rx-modal-title-preview {
        display: block;
        font-weight: 600;
        color: var(--text-primary);
    }
    .rx-modal-actions {
        display: flex;
        gap: 10px;
    }
    .rx-modal-actions .btn {
        flex: 1;
        justify-content: center;
    }

    /* ── Flash alert ─────────────────────────────────────────── */
    .rx-flash {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 18px;
        border-radius: 10px;
        margin-bottom: 28px;
        font-size: 0.9rem;
        font-weight: 500;
        background: rgba(0, 255, 136, 0.08);
        border: 1px solid rgba(0, 255, 136, 0.25);
        color: var(--green);
        animation: rx-fadein 0.4s ease;
    }
    @keyframes rx-fadein {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes rx-slidein {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes rx-scalein {
        from { opacity: 0; transform: scale(0.95); }
        to   { opacity: 1; transform: scale(1); }
    }
    .rx-header { animation: rx-slidein 0.45s ease both; }
    .rx-stats   { animation: rx-slidein 0.5s 0.08s ease both; }
    .rx-section-head { animation: rx-slidein 0.5s 0.14s ease both; }
    .rx-grid    { animation: rx-slidein 0.5s 0.18s ease both; }
    .rx-stat-card { transition: var(--transition), box-shadow 0.25s; }
    .rx-card    { animation: rx-scalein 0.4s 0.22s ease both; }

    /* ── Responsive ──────────────────────────────────────────── */
    @media (max-width: 1024px) {
        .rx-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .rx-wrap { padding: 16px 14px; }
        .rx-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 14px;
            margin-bottom: 24px;
        }
        .rx-header-left h1 { font-size: 1.8rem; }
        .rx-header-left p { font-size: 0.88rem; }
        /* All 3 stat cards in one scrollable row */
        .rx-stats {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 24px;
        }
        .rx-stat-card {
            padding: 12px 10px;
            gap: 8px;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .rx-stat-icon { width: 36px; height: 36px; }
        .rx-stat-icon svg { width: 18px; height: 18px; }
        .rx-stat-val { font-size: 1.3rem; }
        .rx-stat-label { font-size: 0.72rem; }
        .rx-grid { grid-template-columns: 1fr; }
        .rx-card-actions { gap: 6px; }
        .rx-action-btn { padding: 7px 10px; font-size: 0.74rem; }
        .rx-empty { padding: 48px 16px; }
        .rx-modal { padding: 24px 20px; }
        .rx-modal-actions { flex-direction: column; }
    }
    @media (max-width: 480px) {
        .rx-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
        .rx-stat-card { padding: 10px 6px; gap: 6px; }
        .rx-stat-val { font-size: 1.15rem; }
        .rx-stat-label { font-size: 0.67rem; letter-spacing: 0; }
    }
</style>

<div class="rx-wrap">

    <?php if (isset($_GET['saved']) && $_GET['saved'] == '1'): ?>
        <div class="rx-flash" role="alert">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            Resume saved successfully.
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="rx-header">
        <div class="rx-header-left">
            <h1>ResumeX</h1>
            <p>Build, customize, and export beautiful resumes in minutes.</p>
        </div>
        <a href="/projects/resumex/create" class="rx-btn-create">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Create New Resume
        </a>
    </div>

    <!-- Stats -->
    <div class="rx-stats">
        <div class="rx-stat-card cyan">
            <div class="rx-stat-icon cyan">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            </div>
            <div>
                <div class="rx-stat-val"><?= (int)($stats['total'] ?? 0) ?></div>
                <div class="rx-stat-label">Total Resumes</div>
            </div>
        </div>
        <div class="rx-stat-card purple">
            <div class="rx-stat-icon purple">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            </div>
            <div>
                <div class="rx-stat-val"><?= (int)($stats['templates_used'] ?? 0) ?></div>
                <div class="rx-stat-label">Templates Used</div>
            </div>
        </div>
        <div class="rx-stat-card green">
            <div class="rx-stat-icon green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <div>
                <?php
                    $lastUpdated = $stats['last_updated'] ?? null;
                    $lastUpdatedStr = $lastUpdated ? date('M j, Y', strtotime($lastUpdated)) : 'Never';
                ?>
                <div class="rx-stat-val" style="font-size: <?= $lastUpdated ? '1.1rem' : '1.4rem' ?>;"><?= htmlspecialchars($lastUpdatedStr) ?></div>
                <div class="rx-stat-label">Last Updated</div>
            </div>
        </div>
    </div>

    <!-- Resume Grid -->
    <div class="rx-section-head">
        <h2>Your Resumes</h2>
        <?php if (!empty($resumes)): ?>
            <span class="rx-count-badge"><?= count($resumes) ?> resume<?= count($resumes) !== 1 ? 's' : '' ?></span>
        <?php endif; ?>
    </div>

    <?php if (empty($resumes)): ?>
        <!-- Empty state -->
        <div class="rx-empty">
            <div class="rx-empty-icon">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
            </div>
            <h3>No resumes yet</h3>
            <p>Start building your professional resume. Choose a template and customize it to stand out.</p>
            <a href="/projects/resumex/create" class="rx-btn-create" style="display: inline-flex;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Create Your First Resume
            </a>
        </div>

    <?php else: ?>
        <div class="rx-grid">
            <?php foreach ($resumes as $resume): ?>
                <?php
                    $primaryColor = htmlspecialchars($resume['primaryColor'] ?? '#00f0ff');
                    $templateLabel = ucwords(str_replace(['-', '_'], ' ', $resume['template'] ?? 'default'));
                    $themeName = htmlspecialchars($resume['theme_name'] ?? 'Default');
                    $updatedAt = $resume['updated_at'] ?? $resume['created_at'] ?? null;
                    $updatedStr = $updatedAt ? date('M j, Y', strtotime($updatedAt)) : '—';
                    $resumeId = (int)$resume['id'];
                    $resumeTitle = htmlspecialchars($resume['title'] ?? 'Untitled');
                ?>
                <div class="rx-card">
                    <div class="rx-card-accent" style="background: linear-gradient(90deg, <?= $primaryColor ?>, transparent);"></div>

                    <div class="rx-card-title" title="<?= $resumeTitle ?>"><?= $resumeTitle ?></div>

                    <div class="rx-card-meta">
                        <span class="rx-theme-dot" style="background: <?= $primaryColor ?>; color: <?= $primaryColor ?>;"></span>
                        <span class="rx-card-meta-text"><?= $themeName ?></span>
                        <span class="rx-card-meta-sep">·</span>
                        <span class="rx-card-meta-text"><?= htmlspecialchars($templateLabel) ?></span>
                    </div>

                    <div class="rx-card-date">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Updated <?= $updatedStr ?>
                    </div>

                    <div class="rx-card-actions">
                        <a href="/projects/resumex/edit/<?= $resumeId ?>" class="rx-action-btn edit">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            Edit
                        </a>
                        <a href="/projects/resumex/preview/<?= $resumeId ?>" class="rx-action-btn preview" target="_blank" rel="noopener">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            Preview
                        </a>
                        <button
                            type="button"
                            class="rx-action-btn duplicate"
                            onclick="openDuplicateModal(<?= $resumeId ?>, '<?= addslashes($resume['title'] ?? 'Untitled') ?>')"
                        >
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            Duplicate
                        </button>
                        <button
                            type="button"
                            class="rx-action-btn delete"
                            onclick="openDeleteModal(<?= $resumeId ?>, '<?= addslashes($resume['title'] ?? 'Untitled') ?>')"
                        >
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg>
                            Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Delete Modal -->
<div id="rx-delete-modal" class="rx-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="rx-delete-title">
    <div class="rx-modal">
        <div class="rx-modal-icon danger">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg>
        </div>
        <h3 id="rx-delete-title">Delete Resume</h3>
        <p>Are you sure you want to delete <span class="rx-modal-title-preview" id="rx-delete-name"></span>? This action cannot be undone.</p>
        <input type="hidden" id="rx-delete-csrf" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" id="rx-delete-id" value="">
            <div class="rx-modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="btn btn-danger" id="rx-delete-confirm-btn">Delete</button>
            </div>
    </div>
</div>

<!-- Duplicate Modal -->
<div id="rx-duplicate-modal" class="rx-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="rx-duplicate-title">
    <div class="rx-modal">
        <div class="rx-modal-icon success">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
        </div>
        <h3 id="rx-duplicate-title">Duplicate Resume</h3>
        <p>Create a copy of <span class="rx-modal-title-preview" id="rx-duplicate-name"></span>? All content and settings will be duplicated.</p>
        <form id="rx-duplicate-form" method="POST" action="/projects/resumex/duplicate">
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="id" id="rx-duplicate-id" value="">
            <div class="rx-modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeDuplicateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Duplicate</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var deleteOverlay    = document.getElementById('rx-delete-modal');
    var deleteIdInput    = document.getElementById('rx-delete-id');
    var deleteNameEl     = document.getElementById('rx-delete-name');
    var deleteBtn        = document.getElementById('rx-delete-confirm-btn');

    var duplicateOverlay = document.getElementById('rx-duplicate-modal');
    var duplicateIdInput = document.getElementById('rx-duplicate-id');
    var duplicateNameEl  = document.getElementById('rx-duplicate-name');

    window.openDeleteModal = function (id, title) {
        deleteIdInput.value = id;
        deleteNameEl.textContent = '\u201c' + title + '\u201d.';
        deleteOverlay.classList.add('open');
        deleteBtn.focus();
    };
    window.closeDeleteModal = function () {
        deleteOverlay.classList.remove('open');
    };

    deleteBtn.addEventListener('click', function () {
        var id    = deleteIdInput.value;
        var token = document.getElementById('rx-delete-csrf').value;
        deleteBtn.disabled = true;
        deleteBtn.textContent = 'Deleting…';
        var fd = new FormData();
        fd.append('_token', token);
        fd.append('id', id);
        fetch('/projects/resumex/delete', { method: 'POST', body: fd })
            .then(function () { window.location.reload(); })
            .catch(function () { window.location.reload(); });
    });

    window.openDuplicateModal = function (id, title) {
        duplicateIdInput.value = id;
        duplicateNameEl.textContent = '\u201c' + title + '\u201d.';
        duplicateOverlay.classList.add('open');
        duplicateOverlay.querySelector('.btn-primary').focus();
    };
    window.closeDuplicateModal = function () {
        duplicateOverlay.classList.remove('open');
    };

    [deleteOverlay, duplicateOverlay].forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                overlay.classList.remove('open');
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            deleteOverlay.classList.remove('open');
            duplicateOverlay.classList.remove('open');
        }
    });
}());
</script>
<?php View::end(); ?>

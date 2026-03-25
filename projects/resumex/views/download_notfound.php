<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<style>
.rx-notfound {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
    text-align: center;
    padding: 48px 20px;
}
.rx-notfound-icon-wrap {
    position: relative;
    margin-bottom: 24px;
}
.rx-notfound-icon {
    width: 96px;
    height: 96px;
    border-radius: 24px;
    background: linear-gradient(135deg, var(--cyan, #00f0ff) 18%, var(--purple, #9945ff) 18%);
    border: 1px solid var(--border-color, #e2e8f0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    line-height: 1;
}
.rx-notfound-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #ef4444;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--bg-main, #0a0a12);
}
.rx-notfound-badge svg { display: block; }
.rx-notfound h1 {
    font-size: 1.7rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 10px;
    letter-spacing: -0.5px;
}
.rx-notfound-desc {
    color: var(--text-secondary);
    font-size: 0.95rem;
    margin-bottom: 8px;
    max-width: 440px;
    line-height: 1.6;
}
.rx-notfound-id {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--bg-card, #13131f);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 0.82rem;
    color: var(--text-secondary);
    font-family: monospace;
    margin-bottom: 28px;
}
.rx-notfound-id span { color: var(--cyan, #00f0ff); font-weight: 700; }
.rx-notfound-btns {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: center;
    margin-bottom: 36px;
}
.rx-notfound-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 11px 22px;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}
.rx-notfound-btn.primary {
    background: linear-gradient(135deg, var(--cyan, #00f0ff), var(--purple, #9945ff));
    color: #06060a;
}
.rx-notfound-btn.secondary {
    background: var(--bg-card, #13131f);
    color: var(--text-primary);
    border: 1px solid var(--border-color, #e2e8f0);
}
.rx-notfound-btn.primary:hover  { opacity: 0.88; text-decoration: none; color: #06060a; }
.rx-notfound-btn.secondary:hover { border-color: var(--cyan, #00f0ff); color: var(--cyan, #00f0ff); text-decoration: none; }
.rx-notfound-help {
    max-width: 440px;
    background: var(--bg-card, #13131f);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 14px;
    padding: 20px 24px;
    text-align: left;
}
.rx-notfound-help-title {
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--text-secondary);
    margin-bottom: 12px;
}
.rx-notfound-help ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.rx-notfound-help li {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 0.875rem;
    color: var(--text-secondary);
    line-height: 1.5;
}
.rx-notfound-help li svg { flex-shrink: 0; margin-top: 2px; color: var(--cyan, #00f0ff); }
</style>

<div class="rx-notfound">
    <div class="rx-notfound-icon-wrap">
        <div class="rx-notfound-icon">📄</div>
        <div class="rx-notfound-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </div>
    </div>

    <h1>Resume Not Found</h1>

    <p class="rx-notfound-desc">
        The resume you're looking for doesn't exist, may have been deleted, or you don't have permission to access it.
    </p>

    <?php if (!empty($id)): ?>
    <div class="rx-notfound-id">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
        Resume ID: <span>#<?= (int)$id ?></span>
    </div>
    <?php endif; ?>

    <div class="rx-notfound-btns">
        <a href="/projects/resumex" class="rx-notfound-btn primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            My Resumes
        </a>
        <a href="/projects/resumex/create" class="rx-notfound-btn secondary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Create New Resume
        </a>
    </div>

    <div class="rx-notfound-help">
        <div class="rx-notfound-help-title">Why might this happen?</div>
        <ul>
            <li>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                The resume was deleted by its owner.
            </li>
            <li>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                The link is incorrect or has expired.
            </li>
            <li>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                You may be logged in with a different account than the resume owner.
            </li>
        </ul>
    </div>
</div>
<?php View::end(); ?>

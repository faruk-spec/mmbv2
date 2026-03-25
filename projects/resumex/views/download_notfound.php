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
    padding: 40px 20px;
}
.rx-notfound-icon {
    font-size: 72px;
    margin-bottom: 20px;
    opacity: 0.5;
}
.rx-notfound h1 {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 10px;
}
.rx-notfound p {
    color: var(--text-secondary);
    font-size: 0.95rem;
    margin-bottom: 28px;
    max-width: 420px;
}
.rx-notfound-btns {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: center;
}
.rx-notfound-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}
.rx-notfound-btn.primary {
    background: linear-gradient(135deg, var(--cyan), var(--purple));
    color: #06060a;
}
.rx-notfound-btn.secondary {
    background: var(--bg-card);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}
.rx-notfound-btn.primary:hover { opacity: 0.9; text-decoration: none; color: #06060a; }
.rx-notfound-btn.secondary:hover { border-color: var(--cyan); color: var(--cyan); text-decoration: none; }
</style>

<div class="rx-notfound">
    <div class="rx-notfound-icon">📄</div>
    <h1>Resume Not Found</h1>
    <p>The resume you're looking for doesn't exist, or you may not have permission to access it. It may have been deleted or the link is incorrect.</p>
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
</div>
<?php View::end(); ?>

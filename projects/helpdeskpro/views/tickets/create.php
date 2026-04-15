<?php $csrfToken = \Core\Security::generateCsrfToken(); ob_start(); ?>
<h1 style="margin:0 0 .25rem;font-size:1.3rem;">Create Support Ticket</h1>
<p style="margin:0 0 1rem;color:var(--text-secondary);font-size:.88rem;">Describe your issue clearly so we can resolve it fast.</p>

<form method="post" action="/projects/helpdeskpro/tickets/create" class="card" style="max-width:58rem;">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div style="margin-bottom:.8rem;">
        <label style="display:block;margin-bottom:.35rem;font-size:.82rem;color:var(--text-secondary);">Subject</label>
        <input type="text" name="subject" maxlength="255" required placeholder="Example: Unable to login after password reset">
    </div>

    <div style="margin-bottom:.8rem;">
        <label style="display:block;margin-bottom:.35rem;font-size:.82rem;color:var(--text-secondary);">Priority</label>
        <select name="priority">
            <?php foreach (($priorities ?? []) as $priority): ?>
                <option value="<?= htmlspecialchars($priority) ?>"><?= htmlspecialchars(strtoupper($priority)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="margin-bottom:1rem;">
        <label for="ticket_description" style="display:block;margin-bottom:.35rem;font-size:.82rem;color:var(--text-secondary);">Description</label>
        <textarea id="ticket_description" name="description" required maxlength="5000" placeholder="Include steps to reproduce, expected result, actual result, and any IDs/screenshots."></textarea>
    </div>

    <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Ticket</button>
        <a class="btn btn-secondary" href="/projects/helpdeskpro/tickets">Cancel</a>
    </div>
</form>
<?php $content = ob_get_clean(); require PROJECT_PATH . '/views/layout.php'; ?>

<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1><i class="fas fa-list" style="color:#6366f1;"></i> CardX — All Cards</h1>
        <p style="color:var(--text-secondary);">Total: <?= number_format($total) ?> card<?= $total !== 1 ? 's' : '' ?></p>
    </div>
    <a href="/admin/projects/idcard" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Overview</a>
</div>

<!-- Filters -->
<form method="GET" action="/admin/projects/idcard/cards" style="margin-bottom:20px;">
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div>
            <label style="font-size:12px;color:var(--text-secondary);display:block;margin-bottom:4px;">Search</label>
            <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>"
                   placeholder="Name, email, card #…"
                   style="padding:7px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:13px;width:200px;">
        </div>
        <div>
            <label style="font-size:12px;color:var(--text-secondary);display:block;margin-bottom:4px;">Template</label>
            <select name="template" style="padding:7px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:13px;">
                <option value="">All Templates</option>
                <?php foreach ($templates as $key => $tpl): ?>
                <option value="<?= htmlspecialchars($key) ?>" <?= $filters['template'] === $key ? 'selected' : '' ?>>
                    <?= htmlspecialchars($tpl['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:12px;color:var(--text-secondary);display:block;margin-bottom:4px;">From</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>"
                   style="padding:7px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:13px;">
        </div>
        <div>
            <label style="font-size:12px;color:var(--text-secondary);display:block;margin-bottom:4px;">To</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>"
                   style="padding:7px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:13px;">
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
        <a href="/admin/projects/idcard/cards" class="btn btn-secondary btn-sm">Reset</a>
    </div>
</form>

<!-- Table -->
<div class="card" style="padding:0;overflow:hidden;">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-color);background:var(--bg-secondary);">
                    <th style="text-align:left;padding:12px 16px;color:var(--text-secondary);font-weight:600;">Card #</th>
                    <th style="text-align:left;padding:12px 16px;color:var(--text-secondary);font-weight:600;">Name</th>
                    <th style="text-align:left;padding:12px 16px;color:var(--text-secondary);font-weight:600;">Template</th>
                    <th style="text-align:left;padding:12px 16px;color:var(--text-secondary);font-weight:600;">User</th>
                    <th style="text-align:left;padding:12px 16px;color:var(--text-secondary);font-weight:600;">Created</th>
                    <th style="text-align:center;padding:12px 16px;color:var(--text-secondary);font-weight:600;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cards)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:40px;color:var(--text-secondary);">
                        <i class="fas fa-id-card" style="font-size:1.5rem;opacity:0.3;display:block;margin-bottom:8px;"></i>
                        No cards found.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($cards as $card): ?>
                <tr style="border-bottom:1px solid var(--border-color);">
                    <td style="padding:10px 16px;font-family:monospace;font-size:11px;color:var(--text-secondary);"><?= htmlspecialchars($card['card_number']) ?></td>
                    <td style="padding:10px 16px;font-weight:500;"><?= htmlspecialchars($card['card_data']['name'] ?? '—') ?></td>
                    <td style="padding:10px 16px;">
                        <?php $tDef = $templates[$card['template_key']] ?? null; ?>
                        <span style="<?= $tDef ? "background:{$tDef['color']}22;color:{$tDef['color']}" : 'background:rgba(99,102,241,0.15);color:#6366f1' ?>;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">
                            <?= htmlspecialchars($tDef['name'] ?? $card['template_key']) ?>
                        </span>
                    </td>
                    <td style="padding:10px 16px;font-size:12px;">
                        <?= htmlspecialchars($card['user_name'] ?? '—') ?><br>
                        <span style="color:var(--text-secondary);font-size:11px;"><?= htmlspecialchars($card['user_email'] ?? '') ?></span>
                    </td>
                    <td style="padding:10px 16px;font-size:12px;color:var(--text-secondary);"><?= date('d M Y', strtotime($card['created_at'])) ?></td>
                    <td style="padding:10px 16px;text-align:center;">
                        <form method="POST" action="/admin/projects/idcard/cards/delete" style="display:inline;"
                              onsubmit="return confirm('Delete this card permanently?')">
                            <?= \Core\Security::csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int)$card['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($pages > 1): ?>
<div style="display:flex;justify-content:center;gap:8px;margin-top:20px;flex-wrap:wrap;">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
    <a href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"
       style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;
              <?= $i === $page ? 'background:#6366f1;color:#fff;' : 'background:var(--bg-secondary);color:var(--text-secondary);border:1px solid var(--border-color);' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php View::endSection(); ?>

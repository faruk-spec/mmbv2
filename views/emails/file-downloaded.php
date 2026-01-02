<h2>Your File Was Downloaded</h2>

<p>Hi <?= htmlspecialchars($name ?? 'there') ?>,</p>

<p>Your shared file <strong><?= htmlspecialchars($file_name ?? 'file') ?></strong> was downloaded.</p>

<h3>Download Details</h3>

<ul>
    <li><strong>Downloaded at:</strong> <?= $downloaded_at ?? date('Y-m-d H:i:s') ?></li>
    <li><strong>Total downloads:</strong> <?= $download_count ?? 1 ?></li>
    <?php if (isset($ip_address)): ?>
    <li><strong>IP Address:</strong> <?= htmlspecialchars($ip_address) ?></li>
    <?php endif; ?>
</ul>

<p style="text-align: center;">
    <a href="<?= $file_url ?? '#' ?>" class="button">View File Details</a>
</p>

<p>Best regards,<br>
The <?= $app_name ?? 'MMB Platform' ?> Team</p>

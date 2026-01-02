<h2>Your OCR Job is Complete</h2>

<p>Hi <?= htmlspecialchars($name ?? 'there') ?>,</p>

<p>Great news! Your OCR processing job for <strong><?= htmlspecialchars($file_name ?? 'your file') ?></strong> is now complete.</p>

<h3>Job Results</h3>

<ul>
    <li><strong>Status:</strong> <?= ucfirst($status ?? 'completed') ?></li>
    <li><strong>Processing time:</strong> <?= $processing_time ?? 'N/A' ?> seconds</li>
    <?php if (isset($confidence)): ?>
    <li><strong>Confidence:</strong> <?= $confidence ?>%</li>
    <?php endif; ?>
    <?php if (isset($pages_processed)): ?>
    <li><strong>Pages processed:</strong> <?= $pages_processed ?></li>
    <?php endif; ?>
</ul>

<?php if (isset($error_message)): ?>
<div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
    <strong>Note:</strong> <?= htmlspecialchars($error_message) ?>
</div>
<?php endif; ?>

<p style="text-align: center;">
    <a href="<?= $result_url ?? '#' ?>" class="button">View Results</a>
</p>

<p>Best regards,<br>
The <?= $app_name ?? 'MMB Platform' ?> Team</p>

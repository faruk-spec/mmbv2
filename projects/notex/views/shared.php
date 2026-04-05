<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($note['title'] ?? 'Shared Note') ?> - NoteX</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #06060a; --card: #0f0f18; --border: rgba(255,255,255,0.1); --accent: #ffd700; --text: #e8eefc; --muted: #8892a6; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: flex-start; justify-content: center; padding: 40px 20px; }
        .note-wrapper { max-width: 800px; width: 100%; }
        .note-header { margin-bottom: 24px; }
        .note-header-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,215,0,0.1); border: 1px solid rgba(255,215,0,0.3); border-radius: 20px; padding: 6px 14px; font-size: 13px; color: var(--accent); margin-bottom: 16px; }
        .note-title { font-size: 2rem; font-weight: 700; line-height: 1.2; margin-bottom: 8px; }
        .note-meta { color: var(--muted); font-size: 13px; }
        .note-content { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 32px; line-height: 1.8; font-size: 15px; white-space: pre-wrap; word-break: break-word; }
        .note-footer { margin-top: 24px; text-align: center; color: var(--muted); font-size: 12px; }
        .note-footer a { color: var(--accent); text-decoration: none; }
    </style>
</head>
<body>
<div class="note-wrapper">
    <div class="note-header">
        <div class="note-header-badge">
            <i>📝</i> Shared via NoteX
        </div>
        <h1 class="note-title"><?= htmlspecialchars($note['title'] ?? 'Note') ?></h1>
        <div class="note-meta">
            Shared on <?= date('M d, Y', strtotime($note['updated_at'] ?? $note['created_at'])) ?>
            <?php if ($note['share_access'] === 'view'): ?> · View only<?php endif; ?>
        </div>
    </div>

    <div class="note-content"><?= htmlspecialchars($note['content'] ?? '') ?></div>

    <div class="note-footer">
        Powered by <a href="/projects/notex">NoteX</a> – Private Cloud Notes
    </div>
</div>
</body>
</html>

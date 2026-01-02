<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .email-body {
            padding: 30px 20px;
        }
        .email-footer {
            background: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e0e0e0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button:hover {
            background: #5568d3;
        }
        a {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1><?= $app_name ?? 'MMB Platform' ?></h1>
        </div>
        <div class="email-body">
            <?= $content ?? '' ?>
        </div>
        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> <?= $app_name ?? 'MMB Platform' ?>. All rights reserved.</p>
            <?php if (isset($unsubscribe_url)): ?>
            <p><a href="<?= $unsubscribe_url ?>">Unsubscribe from these emails</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

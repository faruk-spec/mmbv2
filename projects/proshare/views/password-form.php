<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Required - ProShare</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-primary: #06060a;
            --bg-secondary: #0c0c12;
            --bg-card: #0f0f18;
            --cyan: #00f0ff;
            --magenta: #ff2ec4;
            --green: #00ff88;
            --orange: #ffaa00;
            --red: #ff6b6b;
            --text-primary: #e8eefc;
            --text-secondary: #8892a6;
            --border-color: rgba(255, 255, 255, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(ellipse at 20% 0%, rgba(0, 240, 255, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 100%, rgba(255, 46, 196, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }
        
        .password-container {
            max-width: 450px;
            width: 100%;
            background: rgba(15, 15, 24, 0.8);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
        }
        
        .lock-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--cyan), var(--magenta));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 2rem;
        }
        
        h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: var(--text-primary);
        }
        
        p {
            color: var(--text-secondary);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 500;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            background: rgba(12, 12, 18, 0.5);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        input[type="password"]:focus {
            outline: none;
            border-color: var(--cyan);
            box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 14px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--cyan), var(--magenta));
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 240, 255, 0.3);
        }
        
        .error-message {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid var(--red);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            color: var(--red);
            display: none;
        }
        
        .error-message.show {
            display: block;
        }
        
        .file-info {
            background: rgba(0, 240, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .file-info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            color: var(--text-secondary);
        }
        
        .file-info-item:last-child {
            margin-bottom: 0;
        }
        
        .file-info-item i {
            color: var(--cyan);
            width: 20px;
        }
        
        @media (max-width: 480px) {
            .password-container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="password-container">
        <div class="lock-icon">
            <i class="fas fa-lock"></i>
        </div>
        
        <h1>Password Required</h1>
        <p>This <?= $type ?> is password protected</p>
        
        <?php if (isset($info)): ?>
        <div class="file-info">
            <?php if (isset($info['name'])): ?>
            <div class="file-info-item">
                <i class="fas fa-file"></i>
                <span><?= htmlspecialchars($info['name']) ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (isset($info['size'])): ?>
            <div class="file-info-item">
                <i class="fas fa-hdd"></i>
                <span><?= $info['size'] ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (isset($info['expires'])): ?>
            <div class="file-info-item">
                <i class="fas fa-clock"></i>
                <span>Expires: <?= $info['expires'] ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div id="errorMessage" class="error-message"></div>
        
        <form id="passwordForm" method="POST">
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-key"></i> Enter Password
                </label>
                <input type="password" id="password" name="password" required autofocus placeholder="Enter the password">
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-unlock"></i> Unlock & Continue
            </button>
        </form>
    </div>
    
    <script>
        const form = document.getElementById('passwordForm');
        const errorMessage = document.getElementById('errorMessage');
        
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const shortCode = '<?= $shortCode ?>';
            const endpoint = '<?= $endpoint ?>';
            
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        short_code: shortCode,
                        password: password
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Reload to trigger download/view
                    window.location.reload();
                } else {
                    errorMessage.textContent = data.error || 'Incorrect password';
                    errorMessage.classList.add('show');
                    document.getElementById('password').value = '';
                    document.getElementById('password').focus();
                }
            } catch (error) {
                errorMessage.textContent = 'An error occurred. Please try again.';
                errorMessage.classList.add('show');
            }
        });
    </script>
</body>
</html>

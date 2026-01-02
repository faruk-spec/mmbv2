<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 30px;">
    <a href="/admin/settings" style="color: var(--text-secondary); text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
        <i class="fas fa-arrow-left"></i> Back to Settings
    </a>
    <h1 style="margin-top: 10px;">Maintenance Mode</h1>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="row" style="display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start;">
    <!-- Status Card -->
    <div>
        <div class="card" style="position: sticky; top: 20px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                <div style="width: 48px; height: 48px; background: var(--cyan); background: linear-gradient(135deg, var(--cyan) 0%, #6366f1 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0, 240, 255, 0.3);">
                    <i class="fas fa-info-circle" style="font-size: 24px; color: #fff;"></i>
                </div>
                <h3 style="margin: 0; font-size: 20px;">Current Status</h3>
            </div>
            <div style="text-align: center; padding: 24px 0;">
                <?php if ($maintenanceMode): ?>
                    <div style="width: 100px; height: 100px; background: var(--badge-warning-bg); border-radius: 20px; margin: 0 auto 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(255, 136, 0, 0.2);">
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ff8800;"></i>
                    </div>
                    <div style="background: linear-gradient(135deg, rgba(255, 136, 0, 0.1) 0%, rgba(255, 136, 0, 0.05) 100%); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                        <h2 style="color: #ff8800; margin-bottom: 8px; font-size: 22px;">Maintenance Active</h2>
                        <p style="color: var(--text-secondary); margin: 0; font-size: 14px; line-height: 1.5;">
                            Site is in maintenance mode.<br>Only admins can access.
                        </p>
                    </div>
                <?php else: ?>
                    <div style="width: 100px; height: 100px; background: var(--badge-success-bg); border-radius: 20px; margin: 0 auto 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(0, 255, 136, 0.2);">
                        <i class="fas fa-check-circle" style="font-size: 48px; color: #00ff88;"></i>
                    </div>
                    <div style="background: linear-gradient(135deg, rgba(0, 255, 136, 0.1) 0%, rgba(0, 255, 136, 0.05) 100%); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                        <h2 style="color: #00ff88; margin-bottom: 8px; font-size: 22px;">Site is Live</h2>
                        <p style="color: var(--text-secondary); margin: 0; font-size: 14px; line-height: 1.5;">
                            Site is accessible to all users.
                        </p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="/admin/settings/maintenance">
                    <?= \Core\Security::csrfField() ?>
                    <button type="submit" class="btn <?= $maintenanceMode ? 'btn-primary' : 'btn-secondary' ?>" style="width: 100%; padding: 14px; font-size: 15px; font-weight: 600; <?= !$maintenanceMode ? 'background: linear-gradient(135deg, #ff8800 0%, #ff6600 100%); border: none; box-shadow: 0 4px 12px rgba(255, 136, 0, 0.3);' : 'box-shadow: 0 4px 12px rgba(0, 240, 255, 0.3);' ?>">
                        <i class="fas fa-<?= $maintenanceMode ? 'check' : 'pause' ?>"></i>
                        <?= $maintenanceMode ? 'Disable Maintenance' : 'Enable Maintenance' ?>
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card" style="margin-top: 24px; border: 2px solid var(--cyan); background: linear-gradient(135deg, rgba(0, 240, 255, 0.05) 0%, rgba(99, 102, 241, 0.05) 100%);">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                <i class="fas fa-shield-alt" style="color: var(--cyan); font-size: 20px;"></i>
                <h4 style="margin: 0; font-size: 16px;">Admin Bypass</h4>
            </div>
            <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 12px; line-height: 1.6;">
                Share this link with admins to bypass maintenance mode:
            </p>
            <div style="position: relative;">
                <input type="text" readonly class="form-input" id="bypassUrl" 
                       style="font-size: 12px; font-family: monospace; padding-right: 45px; background: var(--bg-card);" 
                       value="<?= $_SERVER['HTTP_HOST'] ?? 'yoursite.com' ?>/admin?bypass=<?= md5('maintenance_bypass_' . date('Ymd')) ?>">
                <button type="button" onclick="copyBypassUrl()" 
                        style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: var(--cyan); color: #fff; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 600;">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; margin-top: 10px; padding: 8px 12px; background: var(--badge-warning-bg); border-radius: 6px;">
                <i class="fas fa-clock" style="color: #ff8800; font-size: 13px;"></i>
                <small style="color: var(--text-secondary); font-size: 12px; margin: 0;">Token rotates daily for security</small>
            </div>
        </div>
    </div>
    
    <!-- Settings Form -->
    <div>
        <div class="card">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);">
                    <i class="fas fa-cog" style="font-size: 24px; color: #fff;"></i>
                </div>
                <h3 style="margin: 0; font-size: 20px;">Maintenance Page Settings</h3>
            </div>
            
            <!-- Tab Navigation -->
            <div style="border-bottom: 2px solid var(--bg-secondary); margin-bottom: 24px; display: flex; gap: 8px;">
                <button type="button" class="tab-btn active" onclick="switchTab('simple')" id="tab-simple">
                    <i class="fas fa-list"></i> Simple Mode
                </button>
                <button type="button" class="tab-btn" onclick="switchTab('advanced')" id="tab-advanced">
                    <i class="fas fa-code"></i> Custom HTML
                </button>
            </div>
            
            <form method="POST" action="/admin/settings/maintenance/update">
                <?= \Core\Security::csrfField() ?>
                
                <!-- Simple Mode Tab -->
                <div id="content-simple" class="tab-content active">
                    <div class="form-group">
                        <label class="form-label" for="maintenance_title">
                            <i class="fas fa-heading"></i> Page Title
                        </label>
                        <input type="text" id="maintenance_title" name="maintenance_title" 
                               class="form-input" value="<?= View::e($maintenanceTitle) ?>" 
                               placeholder="We'll Be Back Soon!" required>
                        <small style="color: var(--text-secondary); font-size: 13px;">
                            Main heading displayed on the maintenance page.
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="maintenance_message">
                            <i class="fas fa-comment-alt"></i> Message
                        </label>
                        <textarea id="maintenance_message" name="maintenance_message" 
                                  class="form-input" rows="6" 
                                  placeholder="We're currently performing scheduled maintenance..."><?= htmlspecialchars_decode($maintenanceMessage) ?></textarea>
                        <small style="color: var(--text-secondary); font-size: 13px;">
                            Supports HTML: &lt;b&gt;, &lt;i&gt;, &lt;u&gt;, &lt;p&gt;, &lt;br&gt;, &lt;a&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, headings, images, tables, etc.
                        </small>
                    </div>
                    
                    <div style="background: var(--bg-secondary); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                        <strong style="color: var(--text-primary); display: block; margin-bottom: 8px;">
                            <i class="fas fa-code"></i> HTML Examples:
                        </strong>
                        <code style="display: block; background: var(--bg-card); padding: 8px; border-radius: 4px; font-size: 12px; margin-bottom: 5px; color: var(--cyan);">
                            &lt;b&gt;Bold&lt;/b&gt;, &lt;i&gt;Italic&lt;/i&gt;, &lt;u&gt;Underline&lt;/u&gt;
                        </code>
                        <code style="display: block; background: var(--bg-card); padding: 8px; border-radius: 4px; font-size: 12px; margin-bottom: 5px; color: var(--cyan);">
                            &lt;ul&gt;&lt;li&gt;Item 1&lt;/li&gt;&lt;li&gt;Item 2&lt;/li&gt;&lt;/ul&gt;
                        </code>
                        <code style="display: block; background: var(--bg-card); padding: 8px; border-radius: 4px; font-size: 12px; color: var(--cyan);">
                            &lt;a href="https://example.com"&gt;Link&lt;/a&gt;
                        </code>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-checkbox">
                            <input type="checkbox" name="show_countdown" <?= $showCountdown ? 'checked' : '' ?>>
                            <span><i class="fas fa-clock"></i> Show Countdown Timer</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="end_time">
                            <i class="fas fa-calendar-alt"></i> Estimated End Time
                        </label>
                        <input type="datetime-local" id="end_time" name="end_time" 
                               class="form-input" value="<?= View::e($endTime) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="contact_email">
                            <i class="fas fa-envelope"></i> Contact Email
                        </label>
                        <input type="email" id="contact_email" name="contact_email" 
                               class="form-input" value="<?= View::e($contactEmail) ?>" 
                               placeholder="support@example.com">
                        <small style="color: var(--text-secondary); font-size: 13px;">
                            Optional: Display a "Contact Support" button on the maintenance page.
                        </small>
                    </div>
                </div>
                
                <!-- Advanced Mode Tab -->
                <div id="content-advanced" class="tab-content">
                    <div style="background: var(--badge-info-bg); border-left: 4px solid var(--cyan); padding: 12px 16px; margin-bottom: 20px;">
                        <strong style="color: var(--cyan);"><i class="fas fa-info-circle"></i> Custom HTML Template</strong>
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: var(--text-secondary);">
                            Create a fully custom maintenance page using HTML, CSS, and the available template variables below.
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-file-code"></i> HTML Template
                        </label>
                        <textarea id="maintenance_custom_html" name="maintenance_custom_html" 
                                  class="form-input" rows="12" 
                                  style="font-family: 'Courier New', monospace; font-size: 13px;"><?= htmlspecialchars_decode($maintenanceCustomHtml) ?></textarea>
                    </div>
                    
                    <div style="background: var(--bg-secondary); border-radius: 8px; padding: 15px;">
                        <strong style="color: var(--text-primary); display: block; margin-bottom: 12px;">
                            <i class="fas fa-code"></i> Available Template Variables:
                        </strong>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px;">
                            <div>
                                <code style="display: block; background: var(--bg-card); padding: 6px 10px; border-radius: 4px; font-size: 12px; color: var(--cyan); margin-bottom: 3px;">{{TITLE}}</code>
                                <small style="color: var(--text-secondary); font-size: 11px;">Maintenance title</small>
                            </div>
                            <div>
                                <code style="display: block; background: var(--bg-card); padding: 6px 10px; border-radius: 4px; font-size: 12px; color: var(--cyan); margin-bottom: 3px;">{{MESSAGE}}</code>
                                <small style="color: var(--text-secondary); font-size: 11px;">Maintenance message</small>
                            </div>
                            <div>
                                <code style="display: block; background: var(--bg-card); padding: 6px 10px; border-radius: 4px; font-size: 12px; color: var(--cyan); margin-bottom: 3px;">{{COUNTDOWN}}</code>
                                <small style="color: var(--text-secondary); font-size: 11px;">Full countdown HTML</small>
                            </div>
                            <div>
                                <code style="display: block; background: var(--bg-card); padding: 6px 10px; border-radius: 4px; font-size: 12px; color: var(--cyan); margin-bottom: 3px;">{{COUNTDOWN_DAYS}}</code>
                                <small style="color: var(--text-secondary); font-size: 11px;">Days remaining (00)</small>
                            </div>
                            <div>
                                <code style="display: block; background: var(--bg-card); padding: 6px 10px; border-radius: 4px; font-size: 12px; color: var(--cyan); margin-bottom: 3px;">{{COUNTDOWN_HOURS}}</code>
                                <small style="color: var(--text-secondary); font-size: 11px;">Hours remaining (00)</small>
                            </div>
                            <div>
                                <code style="display: block; background: var(--bg-card); padding: 6px 10px; border-radius: 4px; font-size: 12px; color: var(--cyan); margin-bottom: 3px;">{{COUNTDOWN_MINUTES}}</code>
                                <small style="color: var(--text-secondary); font-size: 11px;">Minutes remaining (00)</small>
                            </div>
                            <div>
                                <code style="display: block; background: var(--bg-card); padding: 6px 10px; border-radius: 4px; font-size: 12px; color: var(--cyan); margin-bottom: 3px;">{{COUNTDOWN_SECONDS}}</code>
                                <small style="color: var(--text-secondary); font-size: 11px;">Seconds remaining (00)</small>
                            </div>
                            <div>
                                <code style="display: block; background: var(--bg-card); padding: 6px 10px; border-radius: 4px; font-size: 12px; color: var(--cyan); margin-bottom: 3px;">{{END_TIME}}</code>
                                <small style="color: var(--text-secondary); font-size: 11px;">End time value</small>
                            </div>
                            <div>
                                <code style="display: block; background: var(--bg-card); padding: 6px 10px; border-radius: 4px; font-size: 12px; color: var(--cyan); margin-bottom: 3px;">{{BYPASS_TOKEN}}</code>
                                <small style="color: var(--text-secondary); font-size: 11px;">Admin bypass token</small>
                            </div>
                            <div>
                                <code style="display: block; background: var(--bg-card); padding: 6px 10px; border-radius: 4px; font-size: 12px; color: var(--cyan); margin-bottom: 3px;">{{BYPASS_URL}}</code>
                                <small style="color: var(--text-secondary); font-size: 11px;">Full bypass URL path</small>
                            </div>
                            <div>
                                <code style="display: block; background: var(--bg-card); padding: 6px 10px; border-radius: 4px; font-size: 12px; color: var(--cyan); margin-bottom: 3px;">{{CURRENT_YEAR}}</code>
                                <small style="color: var(--text-secondary); font-size: 11px;">Current year (2026)</small>
                            </div>
                            <div>
                                <code style="display: block; background: var(--bg-card); padding: 6px 10px; border-radius: 4px; font-size: 12px; color: var(--cyan); margin-bottom: 3px;">{{CONTACT_EMAIL}}</code>
                                <small style="color: var(--text-secondary); font-size: 11px;">Contact email address</small>
                            </div>
                        </div>
                        
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--bg-card);">
                            <strong style="color: var(--text-primary); display: block; margin-bottom: 8px; font-size: 13px;">
                                <i class="fas fa-lightbulb"></i> Example Template:
                            </strong>
                            <pre style="background: var(--bg-card); padding: 12px; border-radius: 4px; font-size: 11px; color: var(--cyan); overflow-x: auto; margin: 0;">&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;Maintenance - {{TITLE}}&lt;/title&gt;
    &lt;style&gt;
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
               color: white; text-align: center; padding: 50px; }
        .countdown { font-size: 2rem; margin: 20px 0; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;{{TITLE}}&lt;/h1&gt;
    &lt;p&gt;{{MESSAGE}}&lt;/p&gt;
    &lt;div class="countdown"&gt;
        {{COUNTDOWN_DAYS}}d {{COUNTDOWN_HOURS}}h {{COUNTDOWN_MINUTES}}m {{COUNTDOWN_SECONDS}}s
    &lt;/div&gt;
    &lt;a href="{{BYPASS_URL}}"&gt;Admin Access&lt;/a&gt;
&lt;/body&gt;
&lt;/html&gt;</pre>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 24px; padding-top: 24px; border-top: 2px solid var(--bg-secondary);">
                    <button type="submit" class="btn btn-primary" style="flex: 1; padding: 14px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 12px rgba(0, 240, 255, 0.3);">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                    <button type="button" onclick="window.location.reload()" class="btn btn-secondary" style="padding: 14px 24px; font-size: 15px;">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.tab-btn {
    background: var(--bg-secondary);
    border: none;
    border-radius: 10px 10px 0 0;
    color: var(--text-secondary);
    padding: 12px 24px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
}

.tab-btn:hover {
    color: var(--text-primary);
    background: var(--hover-bg);
}

.tab-btn.active {
    color: var(--cyan);
    background: linear-gradient(135deg, rgba(0, 240, 255, 0.1) 0%, rgba(99, 102, 241, 0.1) 100%);
    box-shadow: inset 0 -3px 0 var(--cyan);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 1024px) {
    .row {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
function switchTab(tab) {
    // Update buttons
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    
    // Update content
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    document.getElementById('content-' + tab).classList.add('active');
}

function copyBypassUrl() {
    const input = document.getElementById('bypassUrl');
    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.style.background = '#00ff88';
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.style.background = '';
        }, 2000);
    } catch (err) {
        alert('Failed to copy. Please select and copy manually.');
    }
}
</script>
<?php View::endSection(); ?>

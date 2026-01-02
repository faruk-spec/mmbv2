<?php use Core\View; ?>
<?php View::extend('imgtxt'); ?>

<?php View::section('content'); ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">OCR Settings</h3>
    </div>
    
    <form id="settingsForm" method="POST" action="/projects/imgtxt/settings/update">
        <div class="card-body">
            <div class="form-group">
                <label for="default_language">Default OCR Language</label>
                <select id="default_language" name="default_language" class="form-control">
                    <option value="eng" <?= ($settings['default_language'] ?? 'eng') === 'eng' ? 'selected' : '' ?>>English</option>
                    <option value="spa" <?= ($settings['default_language'] ?? '') === 'spa' ? 'selected' : '' ?>>Spanish</option>
                    <option value="fra" <?= ($settings['default_language'] ?? '') === 'fra' ? 'selected' : '' ?>>French</option>
                    <option value="deu" <?= ($settings['default_language'] ?? '') === 'deu' ? 'selected' : '' ?>>German</option>
                    <option value="ita" <?= ($settings['default_language'] ?? '') === 'ita' ? 'selected' : '' ?>>Italian</option>
                    <option value="por" <?= ($settings['default_language'] ?? '') === 'por' ? 'selected' : '' ?>>Portuguese</option>
                    <option value="rus" <?= ($settings['default_language'] ?? '') === 'rus' ? 'selected' : '' ?>>Russian</option>
                    <option value="chi_sim" <?= ($settings['default_language'] ?? '') === 'chi_sim' ? 'selected' : '' ?>>Chinese (Simplified)</option>
                    <option value="jpn" <?= ($settings['default_language'] ?? '') === 'jpn' ? 'selected' : '' ?>>Japanese</option>
                    <option value="kor" <?= ($settings['default_language'] ?? '') === 'kor' ? 'selected' : '' ?>>Korean</option>
                </select>
            </div>

            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="auto_download" name="auto_download" <?= !empty($settings['auto_download']) ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="auto_download">Auto-download results after processing</label>
                </div>
            </div>

            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="keep_history" name="keep_history" <?= !empty($settings['keep_history']) ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="keep_history">Keep OCR history</label>
                </div>
            </div>

            <div class="form-group">
                <label for="output_format">Output Format</label>
                <select id="output_format" name="output_format" class="form-control">
                    <option value="txt" <?= ($settings['output_format'] ?? 'txt') === 'txt' ? 'selected' : '' ?>>Plain Text (.txt)</option>
                    <option value="pdf" <?= ($settings['output_format'] ?? '') === 'pdf' ? 'selected' : '' ?>>PDF (.pdf)</option>
                    <option value="hocr" <?= ($settings['output_format'] ?? '') === 'hocr' ? 'selected' : '' ?>>hOCR (.html)</option>
                </select>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
            <a href="/projects/imgtxt" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script>
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const button = this.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-check-circle"></i> Settings saved successfully!
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            `;
            document.querySelector('.card').prepend(alert);
            
            setTimeout(() => alert.remove(), 3000);
        } else {
            throw new Error(data.message || 'Failed to save settings');
        }
    })
    .catch(error => {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i> ${error.message}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        `;
        document.querySelector('.card').prepend(alert);
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
});
</script>
</div>

<?php View::endSection(); ?>

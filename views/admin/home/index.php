<?php use Core\View; use Core\Helpers; use Core\Database; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<?php $db = Database::getInstance(); ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Home Page Management
        </h1>
        <p style="color: var(--text-secondary);">Customize your home page content, projects, and images</p>
    </div>
    <a href="/" target="_blank" class="btn btn-secondary">
        <i class="fas fa-external-link-alt"></i> Preview Home Page
    </a>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<!-- Hero Section -->
<div class="card" style="margin-bottom: 30px;">
    <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <circle cx="12" cy="12" r="6"></circle>
            <circle cx="12" cy="12" r="2"></circle>
        </svg>
        Hero Section
    </h2>
    <form method="POST" action="/admin/home-content/hero" enctype="multipart/form-data">
        <?= \Core\Security::csrfField() ?>
        
        <div class="grid grid-2">
            <div>
                <div class="form-group">
                    <label class="form-label">Hero Title</label>
                    <input type="text" name="title" class="form-input" 
                           value="<?= View::e($heroContent['title'] ?? '') ?>" required>
                    <small style="color: var(--text-secondary);">Main heading displayed at the top</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Hero Subtitle</label>
                    <input type="text" name="subtitle" class="form-input" 
                           value="<?= View::e($heroContent['subtitle'] ?? '') ?>">
                    <small style="color: var(--text-secondary);">Tagline or short description</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Hero Description</label>
                    <textarea name="description" class="form-input" rows="4"><?= View::e($heroContent['description'] ?? '') ?></textarea>
                    <small style="color: var(--text-secondary);">Detailed description of your platform</small>
                </div>
            </div>
            
            <div>
                <div class="form-group">
                    <label class="form-label">Hero Banner Image</label>
                    <?php if (!empty($heroContent['image_url'])): ?>
                        <div style="margin-bottom: 10px; border: 2px solid var(--border-color); border-radius: 8px; overflow: hidden; position: relative;">
                            <img src="<?= View::e($heroContent['image_url']) ?>" alt="Current Hero Image" 
                                 style="width: 100%; height: 200px; object-fit: cover;">
                            <button type="button" class="btn btn-danger" id="removeHeroImageBtn" 
                                    style="position: absolute; top: 10px; right: 10px; padding: 5px 10px; font-size: 12px;">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </div>
                        <input type="hidden" name="remove_hero_image" id="removeHeroImageInput" value="0">
                    <?php endif; ?>
                    <input type="file" name="hero_image" class="form-input" accept="image/*">
                    <input type="hidden" name="current_image_url" value="<?= View::e($heroContent['image_url'] ?? '') ?>">
                    <small style="color: var(--text-secondary);">Upload banner image (max 5MB, JPEG/PNG/GIF/WebP)</small>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Hero Section
        </button>
    </form>
</div>

<!-- Projects Section Title -->
<div class="card" style="margin-bottom: 30px;">
    <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polyline>
        </svg>
        Projects Section
    </h2>
    <form method="POST" action="/admin/home-content/projects-section">
        <?= \Core\Security::csrfField() ?>
        
        <div class="form-group">
            <label class="form-label">Section Title</label>
            <input type="text" name="projects_title" class="form-input" 
                   value="<?= View::e($projectsSection['title'] ?? 'Explore Our Super Fast Products') ?>" required>
            <small style="color: var(--text-secondary);">Title for the projects section on home page</small>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Section Title
        </button>
    </form>
</div>

<!-- Projects Cards -->
<div class="card">
    <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <rect x="7" y="7" width="3" height="9"></rect>
            <rect x="14" y="7" width="3" height="5"></rect>
        </svg>
        Project Cards
    </h2>
    
    <div class="grid grid-2" style="gap: 20px;">
        <?php foreach ($projects as $project): 
            // Sanitize and validate color value for safe CSS output
            $safeColor = View::e($project['color']);
            // Validate hex color format, use default if invalid
            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $safeColor)) {
                $safeColor = '#00f0ff';
            }
        ?>
        <div class="card" style="background: rgba(<?= hexdec(substr($safeColor, 1, 2)) ?>, <?= hexdec(substr($safeColor, 3, 2)) ?>, <?= hexdec(substr($safeColor, 5, 2)) ?>, 0.05); border-color: <?= $safeColor ?>30;">
            <form method="POST" action="/admin/home-content/project" enctype="multipart/form-data">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                    <div style="width: 40px; height: 40px; background: <?= $safeColor ?>; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                        <?= strtoupper(substr($project['name'], 0, 2)) ?>
                    </div>
                    <h3 style="color: <?= $safeColor ?>;"><?= View::e($project['name']) ?></h3>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Project Name</label>
                    <input type="text" name="name" class="form-input" 
                           value="<?= View::e($project['name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input" rows="2"><?= View::e($project['description']) ?></textarea>
                </div>
                
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Color</label>
                        <input type="color" name="color" class="form-input" 
                               value="<?= View::e($project['color']) ?>" style="height: 40px;">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Icon</label>
                        <input type="text" name="icon" class="form-input" 
                               value="<?= View::e($project['icon']) ?>" placeholder="e.g., code, users">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Project Image</label>
                    <?php if (!empty($project['image_url'])): ?>
                        <div style="margin-bottom: 10px; border: 2px solid var(--border-color); border-radius: 8px; overflow: hidden; position: relative;">
                            <img src="<?= View::e($project['image_url']) ?>" alt="Project Image" 
                                 style="width: 100%; height: 150px; object-fit: cover;">
                            <button type="button" class="btn btn-danger remove-project-image" 
                                    data-project-id="<?= $project['id'] ?>"
                                    style="position: absolute; top: 10px; right: 10px; padding: 5px 10px; font-size: 12px;">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </div>
                        <input type="hidden" name="remove_project_image" class="remove-project-image-input-<?= $project['id'] ?>" value="0">
                    <?php else: ?>
                        <div style="margin-bottom: 10px; border: 2px dashed var(--border-color); border-radius: 8px; padding: 40px; text-align: center; color: var(--text-secondary);">
                            <i class="fas fa-image" style="font-size: 2rem; margin-bottom: 10px;"></i>
                            <p>No image uploaded</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="project_image" class="form-input" accept="image/*">
                    <input type="hidden" name="current_project_image_url" value="<?= View::e($project['image_url'] ?? '') ?>">
                    <small style="color: var(--text-secondary);">Upload project image (max 5MB)</small>
                </div>
                
                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="is_enabled" value="1" 
                               <?= $project['is_enabled'] ? 'checked' : '' ?>>
                        <span>Enable this project</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; background: <?= $safeColor ?>; border-color: <?= $safeColor ?>;">
                    <i class="fas fa-save"></i> Update Project
                </button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.form-input[type="color"] {
    cursor: pointer;
    padding: 4px;
}

.form-input[type="file"] {
    padding: 8px;
}
</style>

<script>
// Handle hero image removal
const removeHeroImageBtn = document.getElementById('removeHeroImageBtn');
if (removeHeroImageBtn) {
    removeHeroImageBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to remove the hero banner image?')) {
            document.getElementById('removeHeroImageInput').value = '1';
            this.closest('div').remove();
        }
    });
}

// Handle project image removal
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-project-image') || e.target.closest('.remove-project-image')) {
        const btn = e.target.classList.contains('remove-project-image') ? e.target : e.target.closest('.remove-project-image');
        const projectId = btn.getAttribute('data-project-id');
        
        if (confirm('Are you sure you want to remove this project image?')) {
            const input = document.querySelector('.remove-project-image-input-' + projectId);
            if (input) {
                input.value = '1';
            }
            btn.closest('div').remove();
        }
    }
});
</script>

<!-- Statistics Section -->
<div class="card" style="margin-bottom: 30px;">
    <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="20" x2="18" y2="10"></line>
            <line x1="12" y1="20" x2="12" y2="4"></line>
            <line x1="6" y1="20" x2="6" y2="14"></line>
        </svg>
        Animated Statistics
    </h2>
    <p style="color: var(--text-secondary); margin-bottom: 20px;">Add animated counters to showcase your platform's impact</p>
    
    <?php 
    $stats = $db->fetchAll("SELECT * FROM home_stats ORDER BY sort_order ASC");
    foreach ($stats as $stat): 
    ?>
    <div class="stat-item" style="background: var(--bg-secondary); padding: 20px; border-radius: 10px; margin-bottom: 15px; border-left: 3px solid <?= View::e($stat['color']) ?>;">
        <div class="grid grid-3" style="gap: 15px; align-items: center;">
            <div>
                <div style="font-weight: 600; margin-bottom: 5px;"><?= View::e($stat['label']) ?></div>
                <div style="color: var(--text-secondary); font-size: 0.9rem;">
                    Value: <?= View::e($stat['prefix']) ?><?= View::e($stat['count_value']) ?><?= View::e($stat['suffix']) ?>
                </div>
            </div>
            <div>
                <div style="font-size: 0.85rem; color: var(--text-secondary);">
                    Icon: <?= View::e($stat['icon']) ?> | Sort: <?= $stat['sort_order'] ?><br>
                    Status: <?= $stat['is_active'] ? '<span style="color: var(--green);">Active</span>' : '<span style="color: var(--orange);">Inactive</span>' ?>
                </div>
            </div>
            <div style="text-align: right;">
                <button class="btn btn-secondary btn-sm" onclick="editStat(<?= $stat['id'] ?>)" style="margin-right: 10px;">
                    Edit
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteStat(<?= $stat['id'] ?>)">
                    Delete
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <button class="btn btn-primary" onclick="addStat()" style="margin-top: 15px;">
        <i class="fas fa-plus"></i> Add New Statistic
    </button>
</div>

<!-- Timeline Section -->
<div class="card" style="margin-bottom: 30px;">
    <h2 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path>
            <path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path>
            <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path>
            <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path>
        </svg>
        Timeline
    </h2>
    <p style="color: var(--text-secondary); margin-bottom: 20px;">Showcase your platform's journey and milestones</p>
    
    <?php 
    $timelineItems = $db->fetchAll("SELECT * FROM home_timeline ORDER BY sort_order ASC");
    foreach ($timelineItems as $item): 
    ?>
    <div class="timeline-item" style="background: var(--bg-secondary); padding: 20px; border-radius: 10px; margin-bottom: 15px; border-left: 3px solid <?= View::e($item['color']) ?>;">
        <div class="grid grid-3" style="gap: 15px; align-items: center;">
            <div>
                <div style="font-weight: 600; margin-bottom: 5px;"><?= View::e($item['title']) ?></div>
                <div style="color: var(--text-secondary); font-size: 0.9rem;">
                    <?= View::e($item['date_display']) ?>
                </div>
            </div>
            <div>
                <div style="font-size: 0.85rem; color: var(--text-secondary);">
                    Icon: <?= View::e($item['icon']) ?> | Sort: <?= $item['sort_order'] ?><br>
                    Status: <?= $item['is_active'] ? '<span style="color: var(--green);">Active</span>' : '<span style="color: var(--orange);">Inactive</span>' ?>
                </div>
            </div>
            <div style="text-align: right;">
                <button class="btn btn-secondary btn-sm" onclick="editTimeline(<?= $item['id'] ?>)" style="margin-right: 10px;">
                    Edit
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteTimeline(<?= $item['id'] ?>)">
                    Delete
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <button class="btn btn-primary" onclick="addTimeline()" style="margin-top: 15px;">
        <i class="fas fa-plus"></i> Add New Timeline Item
    </button>
</div>

<!-- Section Headings Management -->
<div class="card" style="margin-bottom: 30px;">
    <h2 style="display: flex; align-items: center; margin-bottom: 25px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 10px;">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
        </svg>
        Section Headings
    </h2>
    <p style="color: var(--text-secondary); margin-bottom: 25px;">
        Customize section titles and subtitles displayed on the home page.
    </p>
    
    <?php 
    $sectionConfigs = [
        'stats' => ['label' => 'Statistics Section', 'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>', 'color' => '#00f0ff'],
        'timeline' => ['label' => 'Timeline Section', 'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path></svg>', 'color' => '#ff2ec4'],
        'features' => ['label' => 'Features Section', 'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>', 'color' => '#00ff88']
    ];
    
    foreach ($sectionConfigs as $key => $config): 
        $section = $sections[$key] ?? ['heading' => '', 'subheading' => '', 'is_active' => 1];
    ?>
        <div class="stat-item" style="margin-bottom: 20px; padding: 20px; background: var(--bg-secondary); border-radius: 10px; border-left: 4px solid <?= $config['color'] ?>;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 1.2rem; margin-right: 10px;"><?= $config['icon'] ?></span>
                        <strong style="font-size: 1.1rem;"><?= $config['label'] ?></strong>
                        <?php if ($section['is_active'] ?? 1): ?>
                            <span class="badge" style="margin-left: 10px; background: #00ff88; color: #000; padding: 3px 8px; border-radius: 5px; font-size: 0.7rem;">Active</span>
                        <?php else: ?>
                            <span class="badge" style="margin-left: 10px; background: #666; color: #fff; padding: 3px 8px; border-radius: 5px; font-size: 0.7rem;">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <div style="color: var(--text-secondary); margin-bottom: 5px;">
                        <strong>Heading:</strong> <?= htmlspecialchars($section['heading'] ?? 'Not set') ?>
                    </div>
                    <div style="color: var(--text-secondary);">
                        <strong>Subheading:</strong> <?= htmlspecialchars($section['subheading'] ?? 'Not set') ?>
                    </div>
                </div>
                <button class="btn btn-secondary btn-sm" onclick="editSection('<?= $key ?>', '<?= addslashes($section['heading'] ?? '') ?>', '<?= addslashes($section['subheading'] ?? '') ?>', <?= $section['is_active'] ?? 1 ?>)">
                    <i class="fas fa-edit"></i> Edit
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Hidden form for delete operations (CSRF token) -->
<form id="deleteForm" style="display: none;">
    <?= \Core\Security::csrfField() ?>
</form>

<!-- Modal for Stat Edit -->
<div id="statModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--card-bg); border-radius: 15px; padding: 30px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-bottom: 20px;" id="statModalTitle">Add Statistic</h3>
        <form id="statForm">
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="stat_id" id="stat_id">
            
            <div class="form-group">
                <label class="form-label">Label</label>
                <input type="text" name="label" id="stat_label" class="form-input" required>
            </div>
            
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Count Value</label>
                    <input type="number" name="count_value" id="stat_count_value" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Icon</label>
                    <select name="icon" id="stat_icon" class="form-input">
                        <option value="users">Users</option>
                        <option value="grid">Grid</option>
                        <option value="check-circle">Check Circle</option>
                        <option value="activity">Activity</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-3">
                <div class="form-group">
                    <label class="form-label">Prefix</label>
                    <input type="text" name="prefix" id="stat_prefix" class="form-input" placeholder="">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Suffix</label>
                    <input type="text" name="suffix" id="stat_suffix" class="form-input" placeholder="+, %, etc">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <input type="color" name="color" id="stat_color" class="form-input" value="#00f0ff">
                </div>
            </div>
            
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" id="stat_sort_order" class="form-input" value="0">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Active</label>
                    <select name="is_active" id="stat_is_active" class="form-input">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeStatModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Timeline Edit -->
<div id="timelineModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--card-bg); border-radius: 15px; padding: 30px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <h3 style="margin-bottom: 20px;" id="timelineModalTitle">Add Timeline Item</h3>
        <form id="timelineForm">
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="timeline_id" id="timeline_id">
            
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="title" id="timeline_title" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" id="timeline_description" class="form-input" rows="3"></textarea>
            </div>
            
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Date Display</label>
                    <input type="text" name="date_display" id="timeline_date_display" class="form-input" placeholder="2024, Q1 2024, etc">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Icon</label>
                    <select name="icon" id="timeline_icon" class="form-input">
                        <option value="rocket">Rocket</option>
                        <option value="grid">Grid</option>
                        <option value="shield">Shield</option>
                        <option value="code">Code</option>
                        <option value="star">Star</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-3">
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <input type="color" name="color" id="timeline_color" class="form-input" value="#00f0ff">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" id="timeline_sort_order" class="form-input" value="0">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Active</label>
                    <select name="is_active" id="timeline_is_active" class="form-input">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeTimelineModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Section Edit -->
<div id="sectionModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--card-bg); border-radius: 15px; padding: 30px; max-width: 600px; width: 90%;">
        <h3 style="margin-bottom: 20px;" id="sectionModalTitle">Edit Section</h3>
        <form id="sectionForm">
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="section_key" id="section_key">
            
            <div class="form-group">
                <label class="form-label">Heading</label>
                <input type="text" name="heading" id="section_heading" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Subheading</label>
                <input type="text" name="subheading" id="section_subheading" class="form-input">
            </div>
            
            <div class="form-group">
                <label class="form-label">Active</label>
                <select name="is_active" id="section_is_active" class="form-input">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeSectionModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Statistics Management
function addStat() {
    document.getElementById('statModalTitle').textContent = 'Add Statistic';
    document.getElementById('statForm').reset();
    document.getElementById('stat_id').value = '';
    document.getElementById('statModal').style.display = 'flex';
}

async function editStat(statId) {
    try {
        // Fetch existing stat data
        const response = await fetch(`/admin/home-content/stat/get/${statId}`);
        const result = await response.json();
        
        if (result.success) {
            const stat = result.data;
            document.getElementById('statModalTitle').textContent = 'Edit Statistic';
            document.getElementById('stat_id').value = statId;
            document.getElementById('stat_label').value = stat.label;
            document.getElementById('stat_count_value').value = stat.count_value;
            document.getElementById('stat_prefix').value = stat.prefix || '';
            document.getElementById('stat_suffix').value = stat.suffix || '';
            document.getElementById('stat_icon').value = stat.icon;
            document.getElementById('stat_color').value = stat.color;
            document.getElementById('stat_is_active').value = stat.is_active ? '1' : '0';
            document.getElementById('stat_sort_order').value = stat.sort_order;
            document.getElementById('statModal').style.display = 'flex';
        } else {
            alert('Failed to load statistic data');
        }
    } catch (error) {
        alert('Failed to load statistic data');
    }
}

function closeStatModal() {
    document.getElementById('statModal').style.display = 'none';
}

document.getElementById('statForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('/admin/home-content/stat', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('Failed to save statistic');
    }
});

async function deleteStat(statId) {
    if (!confirm('Are you sure you want to delete this statistic?')) return;
    
    const formData = new FormData();
    formData.append('stat_id', statId);
    // Get CSRF token from the hidden deleteForm
    const csrfToken = document.querySelector('#deleteForm input[name="_csrf_token"]').value;
    formData.append('_csrf_token', csrfToken);
    
    try {
        const response = await fetch('/admin/home-content/stat/delete', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('Failed to delete statistic');
    }
}

// Timeline Management
function addTimeline() {
    document.getElementById('timelineModalTitle').textContent = 'Add Timeline Item';
    document.getElementById('timelineForm').reset();
    document.getElementById('timeline_id').value = '';
    document.getElementById('timelineModal').style.display = 'flex';
}

async function editTimeline(timelineId) {
    try {
        // Fetch existing timeline data
        const response = await fetch(`/admin/home-content/timeline/get/${timelineId}`);
        const result = await response.json();
        
        if (result.success) {
            const timeline = result.data;
            document.getElementById('timelineModalTitle').textContent = 'Edit Timeline Item';
            document.getElementById('timeline_id').value = timelineId;
            document.getElementById('timeline_title').value = timeline.title;
            document.getElementById('timeline_description').value = timeline.description || '';
            document.getElementById('timeline_date_display').value = timeline.date_display || '';
            document.getElementById('timeline_icon').value = timeline.icon;
            document.getElementById('timeline_color').value = timeline.color;
            document.getElementById('timeline_is_active').value = timeline.is_active ? '1' : '0';
            document.getElementById('timeline_sort_order').value = timeline.sort_order;
            document.getElementById('timelineModal').style.display = 'flex';
        } else {
            alert('Failed to load timeline data');
        }
    } catch (error) {
        alert('Failed to load timeline data');
    }
}

function closeTimelineModal() {
    document.getElementById('timelineModal').style.display = 'none';
}

document.getElementById('timelineForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('/admin/home-content/timeline', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('Failed to save timeline item');
    }
});

async function deleteTimeline(timelineId) {
    if (!confirm('Are you sure you want to delete this timeline item?')) return;
    
    const formData = new FormData();
    formData.append('timeline_id', timelineId);
    // Get CSRF token from the hidden deleteForm
    const csrfToken = document.querySelector('#deleteForm input[name="_csrf_token"]').value;
    formData.append('_csrf_token', csrfToken);
    
    try {
        const response = await fetch('/admin/home-content/timeline/delete', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('Failed to delete timeline item');
    }
}

// Close modals on outside click
window.addEventListener('click', (e) => {
    if (e.target.id === 'statModal') {
        closeStatModal();
    }
    if (e.target.id === 'timelineModal') {
        closeTimelineModal();
    }
    if (e.target.id === 'sectionModal') {
        closeSectionModal();
    }
});

// Section Headings Management
function editSection(sectionKey, heading, subheading, isActive) {
    document.getElementById('sectionModalTitle').textContent = 'Edit Section Heading';
    document.getElementById('section_key').value = sectionKey;
    document.getElementById('section_heading').value = heading;
    document.getElementById('section_subheading').value = subheading;
    document.getElementById('section_is_active').value = isActive;
    document.getElementById('sectionModal').style.display = 'flex';
}

function closeSectionModal() {
    document.getElementById('sectionModal').style.display = 'none';
}

document.getElementById('sectionForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('/admin/home-content/section', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('Failed to save section');
    }
});
</script>
<?php View::endSection(); ?>

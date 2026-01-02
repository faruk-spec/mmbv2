<?php
/**
 * Admin Home Content Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Security;
use Core\Auth;
use Core\Logger;

class HomeContentController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * Home content management page
     */
    public function index(): void
    {
        $db = Database::getInstance();
        
        // Get hero section content
        $heroContent = $db->fetch("SELECT * FROM home_content WHERE section = 'hero'");
        
        // Get projects section content
        $projectsSection = $db->fetch("SELECT * FROM home_content WHERE section = 'projects_section'");
        
        // Get all projects
        $projects = $db->fetchAll("SELECT * FROM home_projects ORDER BY sort_order ASC");
        
        // Get statistics
        $stats = $db->fetchAll("SELECT * FROM home_stats ORDER BY sort_order ASC");
        
        // Get timeline items
        $timelines = $db->fetchAll("SELECT * FROM home_timeline ORDER BY sort_order ASC");
        
        // Get section headings
        $sections = [];
        $sectionRows = $db->fetchAll("SELECT * FROM home_sections ORDER BY sort_order ASC");
        foreach ($sectionRows as $row) {
            $sections[$row['section_key']] = $row;
        }
        
        $this->view('admin/home/index', [
            'title' => 'Home Page Management',
            'heroContent' => $heroContent,
            'projectsSection' => $projectsSection,
            'projects' => $projects,
            'stats' => $stats,
            'timelines' => $timelines,
            'sections' => $sections
        ]);
    }
    
    /**
     * Update hero section
     */
    public function updateHero(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/home-content');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            $title = Security::sanitize($this->input('title'));
            $subtitle = Security::sanitize($this->input('subtitle'));
            $description = Security::sanitize($this->input('description'));
            
            // Handle image upload or removal
            $imageUrl = $this->input('current_image_url', '');
            $removeImage = $this->input('remove_hero_image', '0') === '1';
            
            if ($removeImage) {
                // Delete the old image file if it exists
                if (!empty($imageUrl)) {
                    $oldFilePath = BASE_PATH . $imageUrl;
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
                $imageUrl = '';
            } elseif (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
                // Delete old image if uploading a new one
                if (!empty($imageUrl)) {
                    $oldFilePath = BASE_PATH . $imageUrl;
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
                $imageUrl = $this->handleImageUpload($_FILES['hero_image'], 'hero');
            }
            
            // Check if hero content exists
            $existing = $db->fetch("SELECT id FROM home_content WHERE section = 'hero'");
            
            $data = [
                'title' => $title,
                'subtitle' => $subtitle,
                'description' => $description,
                'image_url' => $imageUrl,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($existing) {
                $db->update('home_content', $data, 'section = ?', ['hero']);
            } else {
                $data['section'] = 'hero';
                $data['created_at'] = date('Y-m-d H:i:s');
                $db->insert('home_content', $data);
            }
            
            Logger::activity(Auth::id(), 'hero_section_updated');
            
            $this->flash('success', 'Hero section updated successfully.');
            
        } catch (\Exception $e) {
            Logger::error('Hero section update error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update hero section: ' . $e->getMessage());
        }
        
        $this->redirect('/admin/home-content');
    }
    
    /**
     * Update projects section
     */
    public function updateProjectsSection(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/home-content');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            $title = Security::sanitize($this->input('projects_title'));
            
            // Check if projects section content exists
            $existing = $db->fetch("SELECT id FROM home_content WHERE section = 'projects_section'");
            
            $data = [
                'title' => $title,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($existing) {
                $db->update('home_content', $data, 'section = ?', ['projects_section']);
            } else {
                $data['section'] = 'projects_section';
                $data['created_at'] = date('Y-m-d H:i:s');
                $db->insert('home_content', $data);
            }
            
            Logger::activity(Auth::id(), 'projects_section_updated');
            
            $this->flash('success', 'Projects section updated successfully.');
            
        } catch (\Exception $e) {
            Logger::error('Projects section update error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update projects section: ' . $e->getMessage());
        }
        
        $this->redirect('/admin/home-content');
    }
    
    /**
     * Update project
     */
    public function updateProject(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/home-content');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            $projectId = $this->input('project_id');
            $name = Security::sanitize($this->input('name'));
            $description = Security::sanitize($this->input('description'));
            $color = Security::sanitize($this->input('color'));
            $icon = Security::sanitize($this->input('icon'));
            $tier = Security::sanitize($this->input('tier', 'free'));
            $isEnabled = $this->input('is_enabled', '0') === '1' ? 1 : 0;
            
            // Process features - convert newline-separated text to JSON array
            $featuresInput = $this->input('features', '');
            $featuresArray = array_filter(array_map('trim', explode("\n", $featuresInput)));
            $featuresArray = array_slice($featuresArray, 0, 5); // Max 5 features
            $featuresJson = !empty($featuresArray) ? json_encode($featuresArray) : null;
            
            // Validate color format
            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
                throw new \Exception('Invalid color format. Use hex format (#RRGGBB).');
            }
            
            // Validate tier
            if (!in_array($tier, ['free', 'freemium', 'enterprise'])) {
                $tier = 'free';
            }
            
            // Handle image upload or removal
            $imageUrl = $this->input('current_project_image_url', '');
            $removeImage = $this->input('remove_project_image', '0') === '1';
            
            if ($removeImage) {
                // Delete the old image file if it exists
                if (!empty($imageUrl)) {
                    $oldFilePath = BASE_PATH . $imageUrl;
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
                $imageUrl = '';
            } elseif (isset($_FILES['project_image']) && $_FILES['project_image']['error'] === UPLOAD_ERR_OK) {
                // Delete old image if uploading a new one
                if (!empty($imageUrl)) {
                    $oldFilePath = BASE_PATH . $imageUrl;
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
                $imageUrl = $this->handleImageUpload($_FILES['project_image'], 'project');
            }
            
            $data = [
                'name' => $name,
                'description' => $description,
                'color' => $color,
                'icon' => $icon,
                'tier' => $tier,
                'features' => $featuresJson,
                'image_url' => $imageUrl,
                'is_enabled' => $isEnabled,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $db->update('home_projects', $data, 'id = ?', [$projectId]);
            
            Logger::activity(Auth::id(), 'project_updated', ['project_id' => $projectId]);
            
            $this->flash('success', 'Project updated successfully.');
            
        } catch (\Exception $e) {
            Logger::error('Project update error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update project: ' . $e->getMessage());
        }
        
        $this->redirect('/admin/home-content');
    }
    
    /**
     * Handle image upload
     */
    private function handleImageUpload(array $file, string $prefix): string
    {
        // Create uploads directory in public folder if it doesn't exist
        $uploadDir = BASE_PATH . '/public/uploads/home';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0775, true)) {
                Logger::error('Failed to create upload directory: ' . $uploadDir);
                throw new \Exception('Failed to create upload directory.');
            }
            chmod($uploadDir, 0775);
        }
        
        // Ensure directory is writable
        if (!is_writable($uploadDir)) {
            chmod($uploadDir, 0775);
            if (!is_writable($uploadDir)) {
                Logger::error('Upload directory is not writable: ' . $uploadDir);
                throw new \Exception('Upload directory is not writable. Please set permissions to 775.');
            }
        }
        
        // Validate file type using MIME type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new \Exception('Invalid image type. Only JPEG, PNG, GIF, and WebP are allowed.');
        }
        
        // Additional validation: check actual file content using getimagesize
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            throw new \Exception('File is not a valid image.');
        }
        
        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \Exception('Image size must be less than 5MB.');
        }
        
        // Validate and sanitize file extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception('Invalid file extension.');
        }
        
        // Generate unique filename with sanitized extension
        $filename = $prefix . '_' . uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $error = error_get_last();
            Logger::error('Failed to move uploaded file: ' . ($error['message'] ?? 'Unknown error'));
            throw new \Exception('Failed to upload image. Please check file permissions.');
        }
        
        // Return relative URL accessible from web
        // Since document root is project root (not public/), include /public/ in path
        return '/public/uploads/home/' . $filename;
    }
    
    /**
     * Update or create statistic
     */
    public function updateStat(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            $statId = $this->input('stat_id');
            
            $data = [
                'label' => Security::sanitize($this->input('label')),
                'count_value' => (int)$this->input('count_value'),
                'prefix' => Security::sanitize($this->input('prefix', '')),
                'suffix' => Security::sanitize($this->input('suffix', '')),
                'icon' => Security::sanitize($this->input('icon', '')),
                'color' => Security::sanitize($this->input('color', '#00f0ff')),
                'is_active' => (int)$this->input('is_active', 1),
                'sort_order' => (int)$this->input('sort_order', 0)
            ];
            
            // Validate color format
            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $data['color'])) {
                $data['color'] = '#00f0ff';
            }
            
            if ($statId) {
                $db->update('home_stats', $data, 'id = ?', [$statId]);
            } else {
                $db->insert('home_stats', $data);
            }
            
            Logger::info('Home page stat updated', ['user_id' => Auth::id(), 'stat_id' => $statId]);
            
            $this->json(['success' => true, 'message' => 'Statistic updated successfully']);
        } catch (\Exception $e) {
            Logger::error('Failed to update stat: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to update statistic']);
        }
    }
    
    /**
     * Delete statistic
     */
    public function deleteStat(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            $statId = $this->input('stat_id');
            
            $db->delete('home_stats', 'id = ?', [$statId]);
            
            Logger::info('Home page stat deleted', ['user_id' => Auth::id(), 'stat_id' => $statId]);
            
            $this->json(['success' => true, 'message' => 'Statistic deleted successfully']);
        } catch (\Exception $e) {
            Logger::error('Failed to delete stat: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to delete statistic']);
        }
    }
    
    /**
     * Update or create timeline item
     */
    public function updateTimeline(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            $timelineId = $this->input('timeline_id');
            
            $data = [
                'title' => Security::sanitize($this->input('title')),
                'description' => Security::sanitize($this->input('description', '')),
                'date_display' => Security::sanitize($this->input('date_display', '')),
                'icon' => Security::sanitize($this->input('icon', '')),
                'color' => Security::sanitize($this->input('color', '#00f0ff')),
                'is_active' => (int)$this->input('is_active', 1),
                'sort_order' => (int)$this->input('sort_order', 0)
            ];
            
            // Validate color format
            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $data['color'])) {
                $data['color'] = '#00f0ff';
            }
            
            if ($timelineId) {
                $db->update('home_timeline', $data, 'id = ?', [$timelineId]);
            } else {
                $db->insert('home_timeline', $data);
            }
            
            Logger::info('Home page timeline updated', ['user_id' => Auth::id(), 'timeline_id' => $timelineId]);
            
            $this->json(['success' => true, 'message' => 'Timeline item updated successfully']);
        } catch (\Exception $e) {
            Logger::error('Failed to update timeline: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to update timeline item']);
        }
    }
    
    /**
     * Delete timeline item
     */
    public function deleteTimeline(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            $timelineId = $this->input('timeline_id');
            
            $db->delete('home_timeline', 'id = ?', [$timelineId]);
            
            Logger::info('Home page timeline deleted', ['user_id' => Auth::id(), 'timeline_id' => $timelineId]);
            
            $this->json(['success' => true, 'message' => 'Timeline item deleted successfully']);
        } catch (\Exception $e) {
            Logger::error('Failed to delete timeline: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to delete timeline item']);
        }
    }
    
    /**
     * Get single statistic for editing
     */
    public function getStat(int $id): void
    {
        try {
            $db = Database::getInstance();
            $stat = $db->fetch("SELECT * FROM home_stats WHERE id = ?", [$id]);
            
            if ($stat) {
                $this->json(['success' => true, 'data' => $stat]);
            } else {
                $this->json(['success' => false, 'message' => 'Statistic not found']);
            }
        } catch (\Exception $e) {
            Logger::error('Failed to get statistic: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to retrieve statistic']);
        }
    }
    
    /**
     * Get single timeline item for editing
     */
    public function getTimeline(int $id): void
    {
        try {
            $db = Database::getInstance();
            $timeline = $db->fetch("SELECT * FROM home_timeline WHERE id = ?", [$id]);
            
            if ($timeline) {
                $this->json(['success' => true, 'data' => $timeline]);
            } else {
                $this->json(['success' => false, 'message' => 'Timeline item not found']);
            }
        } catch (\Exception $e) {
            Logger::error('Failed to get timeline: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to retrieve timeline item']);
        }
    }
    
    /**
     * Update section heading
     */
    public function updateSection(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            $sectionKey = Security::sanitize($this->input('section_key'));
            $heading = Security::sanitize($this->input('heading'));
            $subheading = Security::sanitize($this->input('subheading'));
            $isActive = (int)$this->input('is_active', 1);
            
            // Check if section exists
            $existing = $db->fetch("SELECT * FROM home_sections WHERE section_key = ?", [$sectionKey]);
            
            if ($existing) {
                // Update existing section
                $db->update('home_sections', [
                    'heading' => $heading,
                    'subheading' => $subheading,
                    'is_active' => $isActive
                ], 'section_key = ?', [$sectionKey]);
            } else {
                // Insert new section
                $db->insert('home_sections', [
                    'section_key' => $sectionKey,
                    'heading' => $heading,
                    'subheading' => $subheading,
                    'is_active' => $isActive
                ]);
            }
            
            Logger::info('Section heading updated', ['user_id' => Auth::id(), 'section' => $sectionKey]);
            
            $this->json(['success' => true, 'message' => 'Section updated successfully']);
        } catch (\Exception $e) {
            Logger::error('Failed to update section: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to update section']);
        }
    }
}

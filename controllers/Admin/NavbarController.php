<?php
namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\View;
use Core\Helpers;
use Core\Logger;
use Core\Auth;

class NavbarController extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermission('navbar');
        $this->db = Database::getInstance();
    }

    /**
     * Display navbar settings page
     */
    public function index()
    {
        // Get current navbar settings
        $settings = $this->db->fetch("SELECT * FROM navbar_settings WHERE id = 1");
        
        // If no settings exist, create default
        if (!$settings) {
            $this->db->query("
                INSERT INTO navbar_settings (logo_type, logo_text, show_home_link, show_dashboard_link, show_profile_link, show_admin_link, show_projects_dropdown, show_theme_toggle, default_theme) 
                VALUES ('text', 'MyMultiBranch', 1, 1, 1, 1, 1, 1, 'dark')
            ");
            $settings = $this->db->fetch("SELECT * FROM navbar_settings WHERE id = 1");
        }

        // Decode custom_links JSON
        if (!empty($settings['custom_links'])) {
            $settings['custom_links'] = json_decode($settings['custom_links'], true);
        } else {
            $settings['custom_links'] = [];
        }

        View::render('admin/navbar', [
            'title' => 'Navbar Settings',
            'settings' => $settings
        ]);
    }

    /**
     * Update navbar settings
     */
    public function update()
    {
        try {
            // Validate CSRF token
            if (!$this->validateCsrf()) {
                Helpers::flash('error', 'Invalid security token. Please try again.');
                return $this->redirect('/admin/navbar');
            }

            // Get form data
            $logoType = $_POST['logo_type'] ?? 'text';
            $logoText = $_POST['logo_text'] ?? 'MyMultiBranch';
            $logoImageUrl = $_POST['logo_image_url'] ?? '';
            $showHomeLink = isset($_POST['show_home_link']) ? 1 : 0;
            $showDashboardLink = isset($_POST['show_dashboard_link']) ? 1 : 0;
            $showProfileLink = isset($_POST['show_profile_link']) ? 1 : 0;
            $showAdminLink = isset($_POST['show_admin_link']) ? 1 : 0;
            $showProjectsDropdown = isset($_POST['show_projects_dropdown']) ? 1 : 0;
            $showThemeToggle = isset($_POST['show_theme_toggle']) ? 1 : 0;
            $navbarSticky = isset($_POST['navbar_sticky']) ? 1 : 0;
            $defaultTheme = $_POST['default_theme'] ?? 'dark';
            $navbarBgColor = $_POST['navbar_bg_color'] ?? '#06060a';
            $navbarTextColor = $_POST['navbar_text_color'] ?? '#e8eefc';
            $navbarBorderColor = $_POST['navbar_border_color'] ?? '#1a1a2e';
            $customCss = $_POST['custom_css'] ?? '';

            // Handle main logo image upload
            if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === UPLOAD_ERR_OK) {
                $uploaded = $this->storeUpload($_FILES['logo_image']['name'], $_FILES['logo_image']['tmp_name']);
                if ($uploaded) {
                    $logoImageUrl = $uploaded;
                }
            }

            // Handle custom links
            $customLinks = [];
            if (!empty($_POST['custom_link_title'])) {
                $titles        = $_POST['custom_link_title'];
                $urls          = $_POST['custom_link_url'];
                $icons         = $_POST['custom_link_icon'] ?? [];
                $positions     = $_POST['custom_link_position'] ?? [];
                $logoUrls      = $_POST['custom_link_logo_url'] ?? [];
                // hidden index field that preserves the JS data-index for each link
                $dataIndices   = $_POST['custom_link_idx'] ?? [];
                $isDropdownArr = $_POST['custom_link_is_dropdown'] ?? [];

                foreach ($titles as $i => $title) {
                    if (empty($title) || empty($urls[$i])) {
                        continue;
                    }

                    // The data-index for this link (used to name dropdown-item fields)
                    $idx = isset($dataIndices[$i]) ? (string)$dataIndices[$i] : (string)$i;

                    // Per-link logo file upload (custom_link_logo[])
                    $linkLogoUrl = $logoUrls[$i] ?? '';
                    if (isset($_FILES['custom_link_logo']['error'][$i]) &&
                        $_FILES['custom_link_logo']['error'][$i] === UPLOAD_ERR_OK) {
                        $uploaded = $this->storeUpload(
                            $_FILES['custom_link_logo']['name'][$i],
                            $_FILES['custom_link_logo']['tmp_name'][$i]
                        );
                        if ($uploaded) {
                            $linkLogoUrl = $uploaded;
                        }
                    }

                    $linkData = [
                        'title'          => $title,
                        'url'            => $urls[$i],
                        'icon'           => $icons[$i] ?? '',
                        'logo_url'       => $linkLogoUrl,
                        'position'       => (int)($positions[$i] ?? 0),
                        'is_dropdown'    => in_array($idx, $isDropdownArr),
                        'dropdown_items' => []
                    ];

                    // Handle dropdown items for this link
                    if ($linkData['is_dropdown']) {
                        $diTitles      = $_POST['dropdown_item_title_'       . $idx] ?? [];
                        $diUrls        = $_POST['dropdown_item_url_'         . $idx] ?? [];
                        $diIcons       = $_POST['dropdown_item_icon_'        . $idx] ?? [];
                        $diLogoUrls    = $_POST['dropdown_item_logo_url_'    . $idx] ?? [];
                        $diDescs       = $_POST['dropdown_item_description_' . $idx] ?? [];
                        $diColors      = $_POST['dropdown_item_text_color_'  . $idx] ?? [];
                        $diBolds       = $_POST['dropdown_item_font_bold_'   . $idx] ?? [];
                        $diSizes       = $_POST['dropdown_item_font_size_'   . $idx] ?? [];

                        foreach ($diTitles as $si => $subTitle) {
                            if (empty($subTitle) || empty($diUrls[$si])) {
                                continue;
                            }
                            $linkData['dropdown_items'][] = [
                                'title'       => $subTitle,
                                'url'         => $diUrls[$si],
                                'icon'        => $diIcons[$si] ?? '',
                                'logo_url'    => $diLogoUrls[$si] ?? '',
                                'description' => $diDescs[$si] ?? '',
                                // Validate hex color to prevent CSS injection at render time
                                'text_color'  => preg_match('/^#[0-9A-Fa-f]{6}$/', $diColors[$si] ?? '') ? $diColors[$si] : '',
                                'font_bold'   => ($diBolds[$si] ?? 'normal') === 'bold',
                                // Clamp font size to safe range (matches HTML min/max attributes)
                                'font_size'   => min(max((int)($diSizes[$si] ?? 14), 10), 32),
                            ];
                        }
                    }

                    $customLinks[] = $linkData;
                }
            }

            $customLinksJson = json_encode($customLinks);

            // Update database - check which columns exist to avoid errors
            // Build dynamic UPDATE query based on available columns
            $updateFields = [];
            $updateValues = [];
            
            // Core fields that should always exist
            $updateFields[] = "logo_type = ?";
            $updateValues[] = $logoType;
            
            $updateFields[] = "logo_text = ?";
            $updateValues[] = $logoText;
            
            if (!empty($logoImageUrl)) {
                $updateFields[] = "logo_image_url = ?";
                $updateValues[] = $logoImageUrl;
            }
            
            // Check if extended columns exist by trying to describe the table
            $columns = $this->db->fetchAll("DESCRIBE navbar_settings");
            $columnNames = array_column($columns, 'Field');
            
            // Add fields only if they exist in the table
            if (in_array('show_home_link', $columnNames)) {
                $updateFields[] = "show_home_link = ?";
                $updateValues[] = $showHomeLink;
            }
            
            if (in_array('show_dashboard_link', $columnNames)) {
                $updateFields[] = "show_dashboard_link = ?";
                $updateValues[] = $showDashboardLink;
            }
            
            if (in_array('show_profile_link', $columnNames)) {
                $updateFields[] = "show_profile_link = ?";
                $updateValues[] = $showProfileLink;
            }
            
            if (in_array('show_admin_link', $columnNames)) {
                $updateFields[] = "show_admin_link = ?";
                $updateValues[] = $showAdminLink;
            }
            
            if (in_array('show_projects_dropdown', $columnNames)) {
                $updateFields[] = "show_projects_dropdown = ?";
                $updateValues[] = $showProjectsDropdown;
            }
            
            if (in_array('show_theme_toggle', $columnNames)) {
                $updateFields[] = "show_theme_toggle = ?";
                $updateValues[] = $showThemeToggle;
            }
            
            if (in_array('navbar_sticky', $columnNames)) {
                $updateFields[] = "navbar_sticky = ?";
                $updateValues[] = $navbarSticky;
            }
            
            if (in_array('default_theme', $columnNames)) {
                $updateFields[] = "default_theme = ?";
                $updateValues[] = $defaultTheme;
            }
            
            if (in_array('navbar_bg_color', $columnNames)) {
                $updateFields[] = "navbar_bg_color = ?";
                $updateValues[] = $navbarBgColor;
            }
            
            if (in_array('navbar_text_color', $columnNames)) {
                $updateFields[] = "navbar_text_color = ?";
                $updateValues[] = $navbarTextColor;
            }
            
            if (in_array('navbar_border_color', $columnNames)) {
                $updateFields[] = "navbar_border_color = ?";
                $updateValues[] = $navbarBorderColor;
            }
            
            if (in_array('custom_css', $columnNames)) {
                $updateFields[] = "custom_css = ?";
                $updateValues[] = $customCss;
            }
            
            if (in_array('custom_links', $columnNames)) {
                $updateFields[] = "custom_links = ?";
                $updateValues[] = $customLinksJson;
            }
            
            if (in_array('updated_at', $columnNames)) {
                $updateFields[] = "updated_at = NOW()";
            }
            
            $sql = "UPDATE navbar_settings SET " . implode(", ", $updateFields) . " WHERE id = 1";
            $this->db->query($sql, $updateValues);

            // Log activity
            Logger::activity(Auth::id(), 'navbar_settings_updated', [
                'message' => 'Updated navbar settings'
            ]);
            
            Helpers::flash('success', 'Navbar settings updated successfully!');
        } catch (\Exception $e) {
            Logger::error('Navbar update error: ' . $e->getMessage());
            Helpers::flash('error', 'Failed to update navbar settings: ' . $e->getMessage());
        }

        return $this->redirect('/admin/navbar');
    }

    /**
     * Reset navbar settings to default
     */
    public function reset()
    {
        try {
            // Validate CSRF token
            if (!$this->validateCsrf()) {
                Helpers::flash('error', 'Invalid security token. Please try again.');
                return $this->redirect('/admin/navbar');
            }

            $this->db->query("
                UPDATE navbar_settings SET
                    logo_type = 'text',
                    logo_text = 'MyMultiBranch',
                    logo_image_url = NULL,
                    show_home_link = 1,
                    show_dashboard_link = 1,
                    show_profile_link = 1,
                    show_admin_link = 1,
                    show_projects_dropdown = 1,
                    show_theme_toggle = 1,
                    default_theme = 'dark',
                    navbar_bg_color = '#06060a',
                    navbar_text_color = '#e8eefc',
                    navbar_border_color = '#1a1a2e',
                    custom_css = NULL,
                    custom_links = NULL,
                    updated_at = NOW()
                WHERE id = 1
            ");

            // Log activity
            Logger::activity(Auth::id(), 'navbar_settings_reset', [
                'message' => 'Reset navbar settings to default'
            ]);
            
            Helpers::flash('success', 'Navbar settings reset to default!');
        } catch (\Exception $e) {
            Logger::error('Navbar reset error: ' . $e->getMessage());
            Helpers::flash('error', 'Failed to reset navbar settings: ' . $e->getMessage());
        }

        return $this->redirect('/admin/navbar');
    }

    /**
     * Store an uploaded file to the navbar uploads directory.
     * Returns the public URL on success, or null on failure.
     */
    private function storeUpload(string $originalName, string $tmpPath): ?string
    {
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) {
            Logger::error('NavbarController: rejected upload with extension "' . $ext . '"');
            return null;
        }

        $uploadDir = BASE_PATH . '/storage/uploads/navbar/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }
        if (!is_writable($uploadDir)) {
            @chmod($uploadDir, 0775);
        }

        $filename   = 'logo_' . uniqid('', true) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($tmpPath, $targetPath)) {
            return '/uploads/navbar/' . $filename;
        }
        if (@copy($tmpPath, $targetPath)) {
            @unlink($tmpPath);
            return '/uploads/navbar/' . $filename;
        }

        Logger::error('NavbarController: failed to store upload to ' . $targetPath);
        return null;
    }
}

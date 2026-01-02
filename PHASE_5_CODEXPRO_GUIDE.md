# Phase 5: Advanced CodeXPro Features - Implementation Guide

## Overview

This guide covers Phase 5 implementation for CodeXPro, adding professional IDE features including templates, code formatting, multi-file projects, and export capabilities.

## Features Implemented

### 5.1 Advanced Editor Features ✅
- ✅ Code formatting (HTML, CSS, JavaScript)
- ✅ Code validation (syntax checking)
- ✅ Code minification
- ✅ Multi-file project support
- ✅ File tree navigation
- ✅ Search and replace in files

### 5.2 Templates & Snippets ✅
- ✅ Starter templates (HTML5, React, Vue, Bootstrap)
- ✅ Code snippets library
- ✅ Custom template creation
- ✅ Template marketplace structure

### 5.3 Export & Deployment ✅
- ✅ Export project as ZIP
- ✅ Export formatted code
- ✅ Export minified code

## Files Added

### Core Classes

#### 1. `/core/CodeXPro/TemplateManager.php`
Manages code templates and snippets.

**Built-in Starter Templates**:
- **HTML5 Boilerplate**: Basic HTML5 structure with linked CSS/JS
- **React App**: React application with hooks and state management
- **Bootstrap 5**: Responsive template with navbar
- **Vue.js App**: Vue 3 application with reactive data

**Code Snippets Library**:
- **JavaScript**: Fetch API, Async/Await, Event Listeners
- **CSS**: Flexbox, Grid Layout, Animations
- **HTML**: Forms, Tables

**Features**:
```php
// Get all templates
$templates = TemplateManager::getStarterTemplates();

// Get specific template
$template = TemplateManager::getStarterTemplate('react-app');

// Create project from template
foreach ($template['files'] as $filename => $content) {
    file_put_contents($filename, $content);
}

// Get snippets
$jsSnippets = TemplateManager::getSnippets('javascript');
$cssSnippets = TemplateManager::getSnippets('css');

// Search snippets
$results = TemplateManager::searchSnippets('fetch');

// Save custom template
TemplateManager::saveUserTemplate(
    $userId,
    'My Template',
    'Custom project template',
    $files,
    'Custom'
);

// Export as ZIP
$zipFile = TemplateManager::exportAsZip('MyProject', $files);
```

#### 2. `/core/CodeXPro/CodeFormatter.php`
Code formatting and validation.

**Features**:
```php
// Format code
$formattedHTML = CodeFormatter::formatHTML($htmlCode);
$formattedCSS = CodeFormatter::formatCSS($cssCode);
$formattedJS = CodeFormatter::formatJavaScript($jsCode);

// Minify code
$minifiedHTML = CodeFormatter::minifyHTML($htmlCode);
$minifiedCSS = CodeFormatter::minifyCSS($cssCode);
$minifiedJS = CodeFormatter::minifyJavaScript($jsCode);

// Validate code
$htmlValidation = CodeFormatter::validateHTML($htmlCode);
if (!$htmlValidation['valid']) {
    foreach ($htmlValidation['errors'] as $error) {
        echo $error;
    }
}

$cssValidation = CodeFormatter::validateCSS($cssCode);
$jsValidation = CodeFormatter::validateJavaScript($jsCode);
```

**Supported Operations**:
- **Format HTML**: Auto-indent, newlines after tags
- **Format CSS**: Beautify selectors and properties
- **Format JavaScript**: Indent blocks and statements
- **Minify**: Remove whitespace and comments
- **Validate**: Check for syntax errors

#### 3. `/core/CodeXPro/FileTreeManager.php`
Multi-file project management.

**Features**:
```php
// Create file tree from project files
$files = [
    'index.html' => '<html>...</html>',
    'css/style.css' => 'body { ... }',
    'js/app.js' => 'console.log(...)'
];

$tree = FileTreeManager::createTree($files);
$html = FileTreeManager::renderTree($tree);

// File operations
FileTreeManager::createFile($projectId, 'newfile.js', '// New file');
FileTreeManager::createFolder($projectId, 'components');
FileTreeManager::rename($projectId, 'old.js', 'new.js');
FileTreeManager::delete($projectId, 'unused.js');

// Search files
$results = FileTreeManager::searchFiles($projectId, 'function');

// Get all project files
$files = FileTreeManager::getProjectFiles($projectId);
```

**File Tree Structure**:
```
📁 project/
  📄 index.html
  📁 css/
    🎨 style.css
    🎨 responsive.css
  📁 js/
    ⚙️ app.js
    ⚙️ utils.js
  📁 images/
    🖼️ logo.png
```

## Database Schema

Add these tables to the CodeXPro database:

```sql
-- Multi-file project support
CREATE TABLE project_files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_content LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project_id (project_id),
    INDEX idx_file_path (project_id, file_path)
);

-- Project folders
CREATE TABLE project_folders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    folder_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    UNIQUE KEY idx_project_folder (project_id, folder_path)
);

-- User templates
CREATE TABLE user_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    is_public TINYINT(1) DEFAULT 0,
    downloads INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_category (category)
);

-- Template files
CREATE TABLE template_files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_id INT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_content LONGTEXT NOT NULL,
    FOREIGN KEY (template_id) REFERENCES user_templates(id) ON DELETE CASCADE
);
```

## Usage Examples

### 1. Create Project from Template

```php
<?php
require_once 'core/CodeXPro/TemplateManager.php';

// Get React template
$template = TemplateManager::getStarterTemplate('react-app');

// Create new project
$projectId = createNewProject($userId, $template['name']);

// Add files to project
foreach ($template['files'] as $filename => $content) {
    FileTreeManager::createFile($projectId, $filename, $content);
}

echo "Project created with " . count($template['files']) . " files";
?>
```

### 2. Format and Validate Code

```php
<?php
require_once 'core/CodeXPro/CodeFormatter.php';

// User's HTML code
$html = '<div><p>Hello</p><span>World';

// Validate
$validation = CodeFormatter::validateHTML($html);
if (!$validation['valid']) {
    echo "Errors found:\n";
    foreach ($validation['errors'] as $error) {
        echo "- $error\n";
    }
}

// Format
$formatted = CodeFormatter::formatHTML($html);
echo "Formatted HTML:\n$formatted";
?>
```

### 3. Export Project

```php
<?php
require_once 'core/CodeXPro/TemplateManager.php';
require_once 'core/CodeXPro/FileTreeManager.php';

// Get all project files
$files = FileTreeManager::getProjectFiles($projectId);

// Export as ZIP
$zipFile = TemplateManager::exportAsZip('MyProject', $files);

// Download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="MyProject.zip"');
header('Content-Length: ' . filesize($zipFile));
readfile($zipFile);
unlink($zipFile);
?>
```

### 4. Code Snippets Panel

```php
<?php
require_once 'core/CodeXPro/TemplateManager.php';

// Get all JavaScript snippets
$snippets = TemplateManager::getSnippets('javascript');

// Display in sidebar
echo '<div class="snippets-panel">';
echo '<h3>JavaScript Snippets</h3>';

foreach ($snippets as $key => $snippet) {
    echo '<div class="snippet" data-key="' . $key . '">';
    echo '<h4>' . htmlspecialchars($snippet['name']) . '</h4>';
    echo '<pre><code>' . htmlspecialchars($snippet['code']) . '</code></pre>';
    echo '<button onclick="insertSnippet(\'' . $key . '\')">Insert</button>';
    echo '</div>';
}

echo '</div>';
?>
```

### 5. File Tree Navigation

```php
<?php
require_once 'core/CodeXPro/FileTreeManager.php';

// Get project files
$files = FileTreeManager::getProjectFiles($projectId);

// Create tree structure
$tree = FileTreeManager::createTree($files);

// Render as HTML
echo '<div class="file-tree">';
echo FileTreeManager::renderTree($tree);
echo '</div>';
?>

<style>
.file-tree {
    font-family: monospace;
    padding: 10px;
}

.folder {
    margin-left: 20px;
}

.folder-name {
    cursor: pointer;
    font-weight: bold;
}

.file {
    margin-left: 20px;
    padding: 5px;
    cursor: pointer;
}

.file:hover {
    background-color: #f0f0f0;
}

.file-size {
    float: right;
    color: #666;
    font-size: 0.9em;
}
</style>

<script>
// Click handler for files
document.querySelectorAll('.file').forEach(file => {
    file.addEventListener('click', function() {
        const path = this.dataset.path;
        loadFile(path);
    });
});

// Toggle folders
document.querySelectorAll('.folder-name').forEach(folder => {
    folder.addEventListener('click', function() {
        const contents = this.nextElementSibling;
        contents.style.display = contents.style.display === 'none' ? 'block' : 'none';
    });
});
</script>
```

### 6. Search and Replace

```php
<?php
require_once 'core/CodeXPro/FileTreeManager.php';

// Search for text
$query = $_GET['query'];
$results = FileTreeManager::searchFiles($projectId, $query);

echo '<h3>Search Results for "' . htmlspecialchars($query) . '"</h3>';
echo '<ul>';

foreach ($results as $result) {
    $path = $result['file_path'];
    $content = $result['file_content'];
    
    // Highlight search term
    $highlighted = str_replace(
        $query,
        '<mark>' . htmlspecialchars($query) . '</mark>',
        htmlspecialchars($content)
    );
    
    echo '<li>';
    echo '<strong>' . htmlspecialchars($path) . '</strong><br>';
    echo '<div class="preview">' . substr($highlighted, 0, 200) . '...</div>';
    echo '</li>';
}

echo '</ul>';
?>
```

## Client-Side Integration

### Template Selector

```html
<div class="template-selector">
    <h2>Start from Template</h2>
    <div class="template-grid">
        <?php
        $templates = TemplateManager::getStarterTemplates();
        foreach ($templates as $key => $template):
        ?>
        <div class="template-card" onclick="createFromTemplate('<?php echo $key; ?>')">
            <h3><?php echo htmlspecialchars($template['name']); ?></h3>
            <p><?php echo htmlspecialchars($template['description']); ?></p>
            <span class="category"><?php echo $template['category']; ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function createFromTemplate(templateKey) {
    fetch('/api/projects/create-from-template', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ template: templateKey })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/codexpro/editor/' + data.project_id;
        }
    });
}
</script>
```

### Code Formatter Toolbar

```html
<div class="editor-toolbar">
    <button onclick="formatCode()" title="Format Code (Alt+Shift+F)">
        <i class="icon-format"></i> Format
    </button>
    <button onclick="validateCode()" title="Validate Code">
        <i class="icon-check"></i> Validate
    </button>
    <button onclick="minifyCode()" title="Minify Code">
        <i class="icon-compress"></i> Minify
    </button>
    <button onclick="exportProject()" title="Export as ZIP">
        <i class="icon-download"></i> Export
    </button>
</div>

<script>
function formatCode() {
    const code = editor.getValue();
    const language = editor.getMode();
    
    fetch('/api/codexpro/format', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code, language })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            editor.setValue(data.formatted);
        }
    });
}

function validateCode() {
    const code = editor.getValue();
    const language = editor.getMode();
    
    fetch('/api/codexpro/validate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code, language })
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            showNotification('Code is valid!', 'success');
        } else {
            showErrors(data.errors);
        }
    });
}

function exportProject() {
    window.location.href = '/api/codexpro/export/' + projectId;
}
</script>
```

### Snippets Panel

```html
<div class="snippets-sidebar">
    <input type="text" id="snippet-search" placeholder="Search snippets..." 
           onkeyup="searchSnippets(this.value)">
    
    <div class="snippet-categories">
        <button onclick="filterSnippets('javascript')">JavaScript</button>
        <button onclick="filterSnippets('css')">CSS</button>
        <button onclick="filterSnippets('html')">HTML</button>
    </div>
    
    <div id="snippets-list"></div>
</div>

<script>
function loadSnippets(language = null) {
    const url = language 
        ? `/api/codexpro/snippets?language=${language}`
        : '/api/codexpro/snippets';
    
    fetch(url)
        .then(response => response.json())
        .then(snippets => {
            const list = document.getElementById('snippets-list');
            list.innerHTML = '';
            
            for (const [key, snippet] of Object.entries(snippets)) {
                const div = document.createElement('div');
                div.className = 'snippet-item';
                div.innerHTML = `
                    <h4>${snippet.name}</h4>
                    <pre><code>${escapeHtml(snippet.code)}</code></pre>
                    <button onclick="insertSnippet('${key}')">Insert</button>
                `;
                list.appendChild(div);
            }
        });
}

function insertSnippet(key) {
    fetch(`/api/codexpro/snippets/${key}`)
        .then(response => response.json())
        .then(snippet => {
            editor.replaceSelection(snippet.code);
        });
}

function searchSnippets(query) {
    fetch(`/api/codexpro/snippets/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(results => {
            // Display search results
        });
}
</script>
```

## Database Configuration

✅ **No Hardcoded Database Names**

All database operations use the configuration from `/projects/codexpro/config.php`:

```php
// Example: projects/codexpro/config.php
return [
    'db_host' => 'localhost',
    'db_name' => 'codexpro',  // Can be any name set in admin panel
    'db_user' => 'codexpro',  // Configured during setup
    'db_pass' => 'codexpro'   // Configured during setup
];
```

The `FileTreeManager` reads this configuration dynamically:

```php
private static function getProjectDatabase()
{
    $config = require __DIR__ . '/../../projects/codexpro/config.php';
    
    return new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}",
        $config['db_user'],
        $config['db_pass']
    );
}
```

## Key Features Summary

### 1. **Templates & Snippets**
- 4 built-in starter templates (HTML5, React, Vue, Bootstrap)
- 8+ code snippets (JavaScript, CSS, HTML)
- Custom template creation and sharing
- Template search and filtering

### 2. **Code Formatting**
- Auto-indent and beautify code
- Format HTML, CSS, JavaScript
- Minify for production
- Validate syntax

### 3. **Multi-File Projects**
- File tree navigation with icons
- Create/rename/delete files and folders
- Search across all files
- Export entire project as ZIP

### 4. **Developer Experience**
- Quick template selection
- One-click code formatting
- Snippet insertion
- Project export

## Next Steps

### Integration Tasks
- [ ] Add format button to CodeXPro editor toolbar
- [ ] Create template selector modal
- [ ] Add snippets sidebar panel
- [ ] Implement file tree UI
- [ ] Add search and replace dialog
- [ ] Create export functionality

### Future Enhancements
- [ ] More starter templates (Angular, Svelte, Tailwind)
- [ ] Advanced snippets (loops, conditionals)
- [ ] Code completion (IntelliSense)
- [ ] Real-time collaboration on multi-file projects
- [ ] Git integration for version control
- [ ] Deploy to GitHub Pages/Vercel

## Testing

Test the features:

```bash
# Test template manager
php -r "
require 'core/CodeXPro/TemplateManager.php';
\$templates = TemplateManager::getStarterTemplates();
echo 'Templates: ' . count(\$templates) . PHP_EOL;
"

# Test code formatter
php -r "
require 'core/CodeXPro/CodeFormatter.php';
\$html = '<div><p>Test</p></div>';
\$formatted = CodeFormatter::formatHTML(\$html);
echo \$formatted . PHP_EOL;
"

# Test file tree
php -r "
require 'core/CodeXPro/FileTreeManager.php';
\$files = ['index.html' => 'test', 'css/style.css' => 'body{}'];
\$tree = FileTreeManager::createTree(\$files);
print_r(\$tree);
"
```

## Conclusion

Phase 5 adds professional IDE features to CodeXPro including:
- ✅ 4 starter templates + snippet library
- ✅ Code formatting and validation
- ✅ Multi-file project management
- ✅ Project export as ZIP
- ✅ Database-agnostic implementation

All features maintain the no-hardcoded-credentials approach and integrate seamlessly with the existing CodeXPro platform.

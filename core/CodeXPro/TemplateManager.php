<?php

/**
 * TemplateManager - Code template and snippet management
 * 
 * Manages reusable code templates and snippets for CodeXPro
 */
class TemplateManager
{
    private static $templatesDir = __DIR__ . '/../../storage/templates/';
    
    /**
     * Built-in starter templates
     */
    private static $starterTemplates = [
        'html5' => [
            'name' => 'HTML5 Boilerplate',
            'description' => 'Basic HTML5 structure',
            'category' => 'HTML',
            'files' => [
                'index.html' => '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Hello World</h1>
    <script src="script.js"></script>
</body>
</html>',
                'style.css' => '* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    padding: 20px;
}',
                'script.js' => 'console.log("Hello World!");'
            ]
        ],
        'react-app' => [
            'name' => 'React App',
            'description' => 'Basic React application setup',
            'category' => 'React',
            'files' => [
                'index.html' => '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>React App</title>
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
</head>
<body>
    <div id="root"></div>
    <script type="text/babel" src="app.js"></script>
</body>
</html>',
                'app.js' => 'function App() {
    const [count, setCount] = React.useState(0);
    
    return (
        <div style={{ padding: "20px" }}>
            <h1>React Counter</h1>
            <p>Count: {count}</p>
            <button onClick={() => setCount(count + 1)}>
                Increment
            </button>
        </div>
    );
}

ReactDOM.render(<App />, document.getElementById("root"));'
            ]
        ],
        'bootstrap5' => [
            'name' => 'Bootstrap 5 Template',
            'description' => 'Bootstrap 5 starter with navbar',
            'category' => 'Bootstrap',
            'files' => [
                'index.html' => '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap 5 Template</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">My Site</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-5">
        <h1>Welcome to Bootstrap 5</h1>
        <p class="lead">Start building your responsive website.</p>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>'
            ]
        ],
        'vue-app' => [
            'name' => 'Vue.js App',
            'description' => 'Basic Vue.js application',
            'category' => 'Vue',
            'files' => [
                'index.html' => '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vue App</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
</head>
<body>
    <div id="app"></div>
    <script src="app.js"></script>
</body>
</html>',
                'app.js' => 'const { createApp } = Vue;

createApp({
    data() {
        return {
            message: "Hello Vue!",
            count: 0
        }
    },
    methods: {
        increment() {
            this.count++;
        }
    },
    template: `
        <div style="padding: 20px">
            <h1>{{ message }}</h1>
            <p>Count: {{ count }}</p>
            <button @click="increment">Increment</button>
        </div>
    `
}).mount("#app");'
            ]
        ]
    ];
    
    /**
     * Code snippets library
     */
    private static $snippets = [
        // JavaScript snippets
        'js_fetch' => [
            'name' => 'Fetch API Request',
            'language' => 'javascript',
            'category' => 'JavaScript',
            'code' => 'fetch("/api/endpoint")
    .then(response => response.json())
    .then(data => console.log(data))
    .catch(error => console.error("Error:", error));'
        ],
        'js_async' => [
            'name' => 'Async/Await Function',
            'language' => 'javascript',
            'category' => 'JavaScript',
            'code' => 'async function fetchData() {
    try {
        const response = await fetch("/api/endpoint");
        const data = await response.json();
        return data;
    } catch (error) {
        console.error("Error:", error);
    }
}'
        ],
        'js_event_listener' => [
            'name' => 'Event Listener',
            'language' => 'javascript',
            'category' => 'JavaScript',
            'code' => 'document.getElementById("myButton").addEventListener("click", function(event) {
    event.preventDefault();
    console.log("Button clicked!");
});'
        ],
        // CSS snippets
        'css_flexbox' => [
            'name' => 'Flexbox Container',
            'language' => 'css',
            'category' => 'CSS',
            'code' => '.flex-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}'
        ],
        'css_grid' => [
            'name' => 'CSS Grid Layout',
            'language' => 'css',
            'category' => 'CSS',
            'code' => '.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}'
        ],
        'css_animation' => [
            'name' => 'CSS Animation',
            'language' => 'css',
            'category' => 'CSS',
            'code' => '@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.animate {
    animation: fadeIn 1s ease-in;
}'
        ],
        // HTML snippets
        'html_form' => [
            'name' => 'HTML Form',
            'language' => 'html',
            'category' => 'HTML',
            'code' => '<form action="/submit" method="POST">
    <div>
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
    </div>
    <div>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    <button type="submit">Submit</button>
</form>'
        ],
        'html_table' => [
            'name' => 'HTML Table',
            'language' => 'html',
            'category' => 'HTML',
            'code' => '<table>
    <thead>
        <tr>
            <th>Header 1</th>
            <th>Header 2</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Cell 1</td>
            <td>Cell 2</td>
        </tr>
    </tbody>
</table>'
        ]
    ];
    
    /**
     * Get all starter templates
     */
    public static function getStarterTemplates()
    {
        return self::$starterTemplates;
    }
    
    /**
     * Get a specific starter template
     */
    public static function getStarterTemplate($key)
    {
        return self::$starterTemplates[$key] ?? null;
    }
    
    /**
     * Get all snippets
     */
    public static function getSnippets($language = null, $category = null)
    {
        $snippets = self::$snippets;
        
        if ($language) {
            $snippets = array_filter($snippets, function($snippet) use ($language) {
                return $snippet['language'] === $language;
            });
        }
        
        if ($category) {
            $snippets = array_filter($snippets, function($snippet) use ($category) {
                return $snippet['category'] === $category;
            });
        }
        
        return $snippets;
    }
    
    /**
     * Get all snippets (alias for getSnippets with no filters)
     */
    public static function getAllSnippets()
    {
        return self::$snippets;
    }
    
    /**
     * Get a specific snippet
     */
    public static function getSnippet($key)
    {
        return self::$snippets[$key] ?? null;
    }
    
    /**
     * Save user template
     */
    public static function saveUserTemplate($userId, $name, $description, $files, $category = 'Custom')
    {
        if (!file_exists(self::$templatesDir)) {
            mkdir(self::$templatesDir, 0755, true);
        }
        
        $template = [
            'name' => $name,
            'description' => $description,
            'category' => $category,
            'files' => $files,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $filename = self::$templatesDir . $userId . '_' . preg_replace('/[^a-z0-9]/i', '_', strtolower($name)) . '.json';
        file_put_contents($filename, json_encode($template, JSON_PRETTY_PRINT));
        
        return true;
    }
    
    /**
     * Get user templates
     */
    public static function getUserTemplates($userId)
    {
        if (!file_exists(self::$templatesDir)) {
            return [];
        }
        
        $templates = [];
        $files = glob(self::$templatesDir . $userId . '_*.json');
        
        foreach ($files as $file) {
            $template = json_decode(file_get_contents($file), true);
            if ($template) {
                $templates[basename($file, '.json')] = $template;
            }
        }
        
        return $templates;
    }
    
    /**
     * Delete user template
     */
    public static function deleteUserTemplate($userId, $templateKey)
    {
        $filename = self::$templatesDir . $templateKey . '.json';
        if (file_exists($filename)) {
            $template = json_decode(file_get_contents($filename), true);
            if ($template && $template['user_id'] == $userId) {
                unlink($filename);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Export project as ZIP
     */
    public static function exportAsZip($projectName, $files)
    {
        $zipFile = tempnam(sys_get_temp_dir(), 'codexpro_') . '.zip';
        $zip = new ZipArchive();
        
        if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
            return false;
        }
        
        foreach ($files as $filename => $content) {
            $zip->addFromString($filename, $content);
        }
        
        $zip->close();
        
        return $zipFile;
    }
    
    /**
     * Search snippets
     */
    public static function searchSnippets($query)
    {
        $query = strtolower($query);
        $results = [];
        
        foreach (self::$snippets as $key => $snippet) {
            if (stripos($snippet['name'], $query) !== false || 
                stripos($snippet['code'], $query) !== false ||
                stripos($snippet['category'], $query) !== false) {
                $results[$key] = $snippet;
            }
        }
        
        return $results;
    }
}

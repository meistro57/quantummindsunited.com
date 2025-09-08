<?php
// Enhanced PDF Library with thumbnails, page counts, and content extraction
require_once 'vendor/autoload.php'; // Composer autoload for libraries

// Check if required libraries are available
$hasPdfParser = class_exists('Smalot\PdfParser\Parser');
$hasImagick = extension_loaded('imagick');
$hasGhostscript = shell_exec('which gs') !== null;

// Configuration
$thumbnailDir = 'thumbnails/';
$cacheDir = 'cache/';

// Create directories if they don't exist
if (!is_dir($thumbnailDir)) {
    mkdir($thumbnailDir, 0755, true);
}
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// Function to generate PDF thumbnail
function generateThumbnail($pdfPath, $thumbnailPath) {
    global $hasImagick, $hasGhostscript;
    
    // Method 1: Using Imagick (preferred)
    if ($hasImagick) {
        try {
            $imagick = new Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImage($pdfPath . '[0]'); // Read first page
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompressionQuality(80);
            $imagick->thumbnailImage(200, 260, true);
            $imagick->writeImage($thumbnailPath);
            $imagick->clear();
            return true;
        } catch (Exception $e) {
            error_log("Imagick thumbnail generation failed: " . $e->getMessage());
        }
    }
    
    // Method 2: Using Ghostscript as fallback
    if ($hasGhostscript) {
        $command = sprintf(
            'gs -dNOPAUSE -dBATCH -sDEVICE=jpeg -dFirstPage=1 -dLastPage=1 -r150 -dGraphicsAlphaBits=4 -sOutputFile=%s %s 2>/dev/null',
            escapeshellarg($thumbnailPath),
            escapeshellarg($pdfPath)
        );
        
        exec($command, $output, $returnCode);
        return $returnCode === 0 && file_exists($thumbnailPath);
    }
    
    return false;
}

// Function to extract PDF metadata and content
function extractPdfInfo($pdfPath) {
    global $hasPdfParser, $cacheDir;
    
    $cacheFile = $cacheDir . md5($pdfPath . filemtime($pdfPath)) . '.json';
    
    // Check cache first
    if (file_exists($cacheFile)) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    
    $info = [
        'pages' => 0,
        'excerpt' => '',
        'title' => '',
        'author' => '',
        'subject' => ''
    ];
    
    if ($hasPdfParser) {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdfPath);
            
            // Get page count
            $pages = $pdf->getPages();
            $info['pages'] = count($pages);
            
            // Get metadata
            $details = $pdf->getDetails();
            $info['title'] = isset($details['Title']) ? trim($details['Title']) : '';
            $info['author'] = isset($details['Author']) ? trim($details['Author']) : '';
            $info['subject'] = isset($details['Subject']) ? trim($details['Subject']) : '';
            
            // Extract text excerpt from first page
            if (!empty($pages)) {
                $firstPage = reset($pages);
                $text = $firstPage->getText();
                $text = preg_replace('/\s+/', ' ', trim($text));
                $info['excerpt'] = substr($text, 0, 200) . '...';
            }
            
        } catch (Exception $e) {
            error_log("PDF parsing failed for {$pdfPath}: " . $e->getMessage());
        }
    }
    
    // Alternative method using pdfinfo command line tool
    if ($info['pages'] === 0 && shell_exec('which pdfinfo') !== null) {
        $output = shell_exec('pdfinfo ' . escapeshellarg($pdfPath));
        if (preg_match('/Pages:\s+(\d+)/', $output, $matches)) {
            $info['pages'] = (int)$matches[1];
        }
    }
    
    // Cache the results
    file_put_contents($cacheFile, json_encode($info));
    
    return $info;
}

// Scan directory for PDF files
$pdfFiles = [];
$directory = __DIR__;

if ($handle = opendir($directory)) {
    while (false !== ($file = readdir($handle))) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
            $filePath = $directory . '/' . $file;
            
            // Generate thumbnail if it doesn't exist
            $thumbnailFile = $thumbnailDir . md5($file) . '.jpg';
            if (!file_exists($thumbnailFile) && file_exists($filePath)) {
                generateThumbnail($filePath, $thumbnailFile);
            }
            
            // Extract PDF information
            $pdfInfo = extractPdfInfo($filePath);
            
            $pdfFiles[] = [
                'filename' => $file,
                'filepath' => $filePath,
                'thumbnail' => file_exists($thumbnailFile) ? $thumbnailFile : null,
                'size' => file_exists($filePath) ? filesize($filePath) : 0,
                'modified' => file_exists($filePath) ? filemtime($filePath) : 0,
                'pages' => $pdfInfo['pages'],
                'excerpt' => $pdfInfo['excerpt'],
                'title' => $pdfInfo['title'],
                'author' => $pdfInfo['author'],
                'subject' => $pdfInfo['subject']
            ];
        }
    }
    closedir($handle);
}

// Sort files alphabetically
usort($pdfFiles, function($a, $b) {
    return strcmp($a['filename'], $b['filename']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meistros Library</title>
    <style>
        :root {
            --primary-bg: #0a0f1c;
            --secondary-bg: #1a1f35;
            --card-bg: rgba(26, 31, 53, 0.6);
            --card-border: rgba(139, 143, 164, 0.2);
            --accent-blue: #4a9eff;
            --text-primary: #ffffff;
            --text-secondary: #8b8fa4;
            --text-muted: #6b7280;
            --gradient-primary: linear-gradient(135deg, #4a9eff, #0066cc);
            --gradient-card: linear-gradient(135deg, rgba(74, 158, 255, 0.1), rgba(0, 102, 204, 0.05));
            --shadow-glow: 0 4px 20px rgba(74, 158, 255, 0.2);
            --shadow-card: 0 4px 16px rgba(0, 0, 0, 0.3);
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Inter', sans-serif;
            background: var(--primary-bg);
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(74, 158, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(74, 158, 255, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(74, 158, 255, 0.03) 0%, transparent 50%);
            min-height: 100vh;
            color: var(--text-primary);
            line-height: 1.6;
            position: relative;
        }
        
        /* Subtle animated background particles */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(2px 2px at 20px 30px, rgba(255,255,255,0.1), transparent),
                radial-gradient(2px 2px at 40px 70px, rgba(74, 158, 255, 0.2), transparent),
                radial-gradient(1px 1px at 90px 40px, rgba(255,255,255,0.05), transparent),
                radial-gradient(1px 1px at 130px 80px, rgba(74, 158, 255, 0.1), transparent);
            background-repeat: repeat;
            background-size: 200px 150px;
            animation: sparkle 20s linear infinite;
            pointer-events: none;
            z-index: 0;
        }
        
        @keyframes sparkle {
            from { transform: translateY(0); }
            to { transform: translateY(-200px); }
        }
        
        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%); }
            50% { transform: translateX(100%); }
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 40px 30px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--gradient-card);
            animation: shimmer 3s ease-in-out infinite;
            opacity: 0.5;
        }
        
        .header-content {
            position: relative;
            z-index: 2;
        }
        
        .header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            letter-spacing: -0.02em;
            text-shadow: 0 0 30px rgba(74, 158, 255, 0.3);
        }
        
        .header .subtitle {
            color: var(--text-secondary);
            font-size: 1.3rem;
            font-weight: 500;
        }
        
        .setup-notice {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #fbbf24;
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }
        
        .setup-notice h4 {
            font-weight: 600;
            margin-bottom: 10px;
            color: #f59e0b;
        }
        
        .setup-notice ul {
            margin-left: 20px;
            color: #d97706;
        }
        
        .setup-notice li {
            margin-bottom: 5px;
        }
        
        .setup-notice code {
            background: rgba(0, 0, 0, 0.3);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Monaco', 'Menlo', monospace;
        }
        
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(10px);
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .library-stats {
            display: flex;
            gap: 25px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .stat-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 12px 20px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-card);
            min-width: 80px;
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-glow);
            border-color: rgba(74, 158, 255, 0.4);
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        
        .view-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .view-btn {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            color: var(--text-secondary);
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 14px;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }
        
        .view-btn:hover, .view-btn.active {
            background: var(--gradient-primary);
            color: white;
            border-color: var(--accent-blue);
            box-shadow: var(--shadow-glow);
        }
        
        .search-box {
            position: relative;
            margin: 0 15px;
        }
        
        .search-input {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            color: var(--text-primary);
            padding: 10px 40px 10px 15px;
            border-radius: 25px;
            width: 250px;
            transition: var(--transition);
            font-size: 14px;
            backdrop-filter: blur(10px);
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(74, 158, 255, 0.1);
        }
        
        .search-input::placeholder {
            color: var(--text-muted);
        }
        
        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }
        
        .pdf-grid {
            display: grid;
            gap: 25px;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            transition: var(--transition);
        }
        
        .pdf-grid.list-view {
            grid-template-columns: 1fr;
        }
        
        .pdf-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            transition: var(--transition);
            overflow: hidden;
            position: relative;
            backdrop-filter: blur(10px);
        }
        
        .pdf-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gradient-card);
            opacity: 0;
            transition: var(--transition);
            pointer-events: none;
        }
        
        .pdf-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-glow);
            border-color: rgba(74, 158, 255, 0.4);
        }
        
        .pdf-card:hover::before {
            opacity: 1;
        }
        
        .pdf-preview {
            height: 220px;
            background: var(--secondary-bg);
            background-image: 
                radial-gradient(circle at 30% 70%, rgba(74, 158, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 70% 30%, rgba(74, 158, 255, 0.05) 0%, transparent 50%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .pdf-thumbnail {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
        }
        
        .pdf-icon {
            font-size: 4rem;
            color: var(--accent-blue);
            opacity: 0.8;
        }
        
        .pdf-pages {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 6px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .loading-thumbnail {
            position: absolute;
            top: 10px;
            left: 15px;
            background: rgba(74, 158, 255, 0.2);
            color: var(--accent-blue);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            animation: pulse 2s infinite;
        }
        
        .pdf-info {
            padding: 25px;
            position: relative;
            z-index: 2;
        }
        
        .pdf-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .pdf-excerpt {
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 60px;
        }
        
        .pdf-metadata {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
            gap: 12px;
            margin: 15px 0;
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px 8px;
            background: rgba(139, 143, 164, 0.1);
            border-radius: 8px;
            font-size: 12px;
        }
        
        .meta-icon {
            font-size: 16px;
            margin-bottom: 4px;
            color: var(--accent-blue);
        }
        
        .meta-value {
            font-weight: 600;
            color: var(--text-primary);
            text-align: center;
        }
        
        .meta-label {
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 10px;
            margin-top: 2px;
        }
        
        .pdf-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }
        
        .action-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--shadow-glow);
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(74, 158, 255, 0.4);
        }
        
        .btn-secondary {
            background: var(--card-bg);
            color: var(--text-secondary);
            border: 1px solid var(--card-border);
        }
        
        .btn-secondary:hover {
            background: var(--secondary-bg);
            color: var(--text-primary);
            border-color: var(--accent-blue);
        }
        
        .list-view .pdf-card {
            display: flex;
            align-items: flex-start;
            padding: 25px;
        }
        
        .list-view .pdf-preview {
            width: 120px;
            height: 150px;
            margin-right: 25px;
            flex-shrink: 0;
        }
        
        .list-view .pdf-info {
            flex: 1;
            padding: 0;
        }
        
        .list-view .pdf-excerpt {
            -webkit-line-clamp: 2;
            min-height: 40px;
        }
        
        .list-view .pdf-metadata {
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            margin: 10px 0;
        }
        
        .no-files {
            text-align: center;
            padding: 80px 30px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(10px);
        }
        
        .no-files-icon {
            font-size: 5rem;
            color: var(--text-muted);
            margin-bottom: 20px;
        }
        
        .no-files h3 {
            font-size: 2rem;
            color: var(--text-primary);
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .no-files p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        
        .filter-tags {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .filter-tag {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            color: var(--text-secondary);
            padding: 6px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: var(--transition);
            backdrop-filter: blur(10px);
        }
        
        .filter-tag:hover, .filter-tag.active {
            background: var(--gradient-primary);
            color: white;
            border-color: var(--accent-blue);
            box-shadow: var(--shadow-glow);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 2.5rem;
            }
            
            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .library-stats {
                justify-content: center;
            }
            
            .search-input {
                width: 100%;
            }
            
            .pdf-grid {
                grid-template-columns: 1fr;
            }
            
            .list-view .pdf-card {
                flex-direction: column;
                text-align: center;
            }
            
            .list-view .pdf-preview {
                margin: 0 0 15px 0;
                width: 100px;
                height: 130px;
                align-self: center;
            }
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .pdf-card {
            animation: fadeIn 0.5s ease-out;
        }
        
        .loading-thumbnail {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1>📄 Meistros Library</h1>
                <p class="subtitle">Meistros Document Collection</p>
            </div>
        </div>
        
        <?php if (!$hasPdfParser && !$hasImagick): ?>
            <div class="setup-notice">
                <h4>⚠️ Enhanced Features Setup Required</h4>
                <p>To enable PDF thumbnails, page counts, and content extraction, please install:</p>
                <ul>
                    <li><strong>PDF Parser:</strong> <code>composer require smalot/pdfparser</code></li>
                    <li><strong>ImageMagick:</strong> PHP extension for thumbnail generation</li>
                    <li><strong>Ghostscript:</strong> Alternative for PDF thumbnail generation</li>
                </ul>
                <p>The library will work with basic features until these are installed.</p>
            </div>
        <?php endif; ?>
        
        <?php if (empty($pdfFiles)): ?>
            <div class="no-files">
                <div class="no-files-icon">📚</div>
                <h3>No PDF Documents Found</h3>
                <p>Upload your PDF files to this directory to build your digital library.</p>
                <p><strong>Current directory:</strong> <?php echo realpath($directory); ?></p>
            </div>
        <?php else: ?>
            <?php 
                $totalFiles = count($pdfFiles);
                $totalSize = array_sum(array_column($pdfFiles, 'size'));
                $totalPages = array_sum(array_column($pdfFiles, 'pages'));
                
                $totalSizeGB = round($totalSize / (1024 * 1024 * 1024), 2);
                $totalSizeMB = round($totalSize / (1024 * 1024), 1);
            ?>
            
            <div class="toolbar">
                <div class="library-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $totalFiles; ?></div>
                        <div class="stat-label">Documents</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $totalSizeGB > 0 ? $totalSizeGB . 'GB' : $totalSizeMB . 'MB'; ?></div>
                        <div class="stat-label">Total Size</div>
                    </div>
                    <?php if ($totalPages > 0): ?>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo number_format($totalPages); ?></div>
                        <div class="stat-label">Total Pages</div>
                    </div>
                    <?php endif; ?>
                    <div class="stat-card">
                        <div class="stat-number">PDF</div>
                        <div class="stat-label">Format</div>
                    </div>
                </div>
                
                <div class="search-box">
                    <input type="text" class="search-input" placeholder="Search documents..." id="searchInput">
                    <span class="search-icon">🔍</span>
                </div>
                
                <div class="view-controls">
                    <button class="view-btn active" onclick="toggleView('grid')" id="gridBtn">⊞ Grid</button>
                    <button class="view-btn" onclick="toggleView('list')" id="listBtn">☰ List</button>
                </div>
            </div>
            
            <div class="filter-tags">
                <div class="filter-tag active" onclick="filterBySize('all')">All Sizes</div>
                <div class="filter-tag" onclick="filterBySize('small')">< 1MB</div>
                <div class="filter-tag" onclick="filterBySize('medium')">1-10MB</div>
                <div class="filter-tag" onclick="filterBySize('large')">10MB+</div>
            </div>
            
            <div class="pdf-grid" id="pdfGrid">
                <?php foreach ($pdfFiles as $index => $pdf): ?>
                    <?php 
                        // Clean up filename for display
                        $displayName = $pdf['title'] ?: pathinfo($pdf['filename'], PATHINFO_FILENAME);
                        $displayName = preg_replace('/[_-]/', ' ', $displayName);
                        $displayName = preg_replace('/\s+/', ' ', trim($displayName));
                        $displayName = ucwords(strtolower($displayName));
                        
                        // Get file info
                        $fileSizeMB = round($pdf['size'] / (1024 * 1024), 2);
                        $fileDate = date('M j, Y', $pdf['modified']);
                        
                        // Determine size category
                        $sizeCategory = $fileSizeMB < 1 ? 'small' : ($fileSizeMB < 10 ? 'medium' : 'large');
                    ?>
                    <div class="pdf-card" data-filename="<?php echo strtolower($pdf['filename']); ?>" data-title="<?php echo strtolower($displayName); ?>" data-size="<?php echo $sizeCategory; ?>">
                        <div class="pdf-preview">
                            <?php if ($pdf['thumbnail'] && file_exists($pdf['thumbnail'])): ?>
                                <img src="<?php echo $pdf['thumbnail']; ?>" alt="PDF Preview" class="pdf-thumbnail" loading="lazy">
                            <?php else: ?>
                                <div class="pdf-icon">📄</div>
                                <?php if ($hasImagick || $hasGhostscript): ?>
                                    <div class="loading-thumbnail">Generating...</div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if ($pdf['pages'] > 0): ?>
                                <div class="pdf-pages">
                                    📄 <?php echo $pdf['pages']; ?> page<?php echo $pdf['pages'] !== 1 ? 's' : ''; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="pdf-info">
                            <div class="pdf-title"><?php echo htmlspecialchars($displayName); ?></div>
                            
                            <?php if ($pdf['excerpt']): ?>
                                <div class="pdf-excerpt"><?php echo htmlspecialchars($pdf['excerpt']); ?></div>
                            <?php endif; ?>
                            
                            <div class="pdf-metadata">
                                <div class="meta-item">
                                    <div class="meta-icon">💾</div>
                                    <div class="meta-value"><?php echo $fileSizeMB; ?>MB</div>
                                    <div class="meta-label">Size</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-icon">📅</div>
                                    <div class="meta-value"><?php echo date('M j', $pdf['modified']); ?></div>
                                    <div class="meta-label">Modified</div>
                                </div>
                                <?php if ($pdf['pages'] > 0): ?>
                                <div class="meta-item">
                                    <div class="meta-icon">📄</div>
                                    <div class="meta-value"><?php echo $pdf['pages']; ?></div>
                                    <div class="meta-label">Pages</div>
                                </div>
                                <?php endif; ?>
                                <?php if ($pdf['author']): ?>
                                <div class="meta-item">
                                    <div class="meta-icon">👤</div>
                                    <div class="meta-value" title="<?php echo htmlspecialchars($pdf['author']); ?>">
                                        <?php echo htmlspecialchars(substr($pdf['author'], 0, 15) . (strlen($pdf['author']) > 15 ? '...' : '')); ?>
                                    </div>
                                    <div class="meta-label">Author</div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="pdf-actions">
                                <button class="action-btn btn-primary" onclick="openPDF('<?php echo htmlspecialchars($pdf['filename']); ?>')">
                                    👁️ View
                                </button>
                                <button class="action-btn btn-secondary" onclick="downloadPDF('<?php echo htmlspecialchars($pdf['filename']); ?>')">
                                    ⬇️ Download
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        let currentView = 'grid';
        let currentFilter = 'all';
        
        // Enhanced search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.pdf-card');
            
            cards.forEach(card => {
                const title = card.dataset.title;
                const filename = card.dataset.filename;
                const excerpt = card.querySelector('.pdf-excerpt')?.textContent.toLowerCase() || '';
                
                if (title.includes(searchTerm) || filename.includes(searchTerm) || excerpt.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // View toggle
        function toggleView(view) {
            const grid = document.getElementById('pdfGrid');
            const gridBtn = document.getElementById('gridBtn');
            const listBtn = document.getElementById('listBtn');
            
            currentView = view;
            
            if (view === 'list') {
                grid.classList.add('list-view');
                listBtn.classList.add('active');
                gridBtn.classList.remove('active');
            } else {
                grid.classList.remove('list-view');
                gridBtn.classList.add('active');
                listBtn.classList.remove('active');
            }
        }
        
        // Filter by size
        function filterBySize(size) {
            const cards = document.querySelectorAll('.pdf-card');
            const filterTags = document.querySelectorAll('.filter-tag');
            
            currentFilter = size;
            
            // Update active filter tag
            filterTags.forEach(tag => tag.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter cards
            cards.forEach(card => {
                if (size === 'all' || card.dataset.size === size) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // PDF actions
        function openPDF(filename) {
            window.open(filename, '_blank');
        }
        
        function downloadPDF(filename) {
            const link = document.createElement('a');
            link.href = filename;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Auto-generate thumbnails for missing ones
        function refreshThumbnails() {
            const missingThumbnails = document.querySelectorAll('.loading-thumbnail');
            if (missingThumbnails.length > 0) {
                setTimeout(() => {
                    window.location.reload();
                }, 5000);
            }
        }
        
        // Lazy load thumbnails
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                            observer.unobserve(img);
                        }
                    }
                });
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName.toLowerCase() !== 'input') {
                switch(e.code) {
                    case 'KeyG':
                        if (e.ctrlKey || e.metaKey) {
                            e.preventDefault();
                            toggleView('grid');
                        }
                        break;
                    case 'KeyL':
                        if (e.ctrlKey || e.metaKey) {
                            e.preventDefault();
                            toggleView('list');
                        }
                        break;
                    case 'KeyF':
                        if (e.ctrlKey || e.metaKey) {
                            e.preventDefault();
                            document.getElementById('searchInput').focus();
                        }
                        break;
                    case 'KeyR':
                        if (e.ctrlKey || e.metaKey) {
                            e.preventDefault();
                            refreshThumbnails();
                        }
                        break;
                }
            }
        });
        
        // Add stagger animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.pdf-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
            
            // Check for missing thumbnails
            refreshThumbnails();
        });
        
        // Auto-focus search on page load
        window.addEventListener('load', function() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput && window.innerWidth > 768) {
                setTimeout(() => searchInput.focus(), 500);
            }
        });
        
        // Enhanced tooltip functionality
        function showTooltip(element, text) {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = text;
            tooltip.style.cssText = `
                position: absolute;
                background: rgba(0, 0, 0, 0.9);
                color: white;
                padding: 8px 12px;
                border-radius: 6px;
                font-size: 12px;
                pointer-events: none;
                z-index: 1000;
                opacity: 0;
                transition: opacity 0.3s;
                max-width: 200px;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = element.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
            
            setTimeout(() => tooltip.style.opacity = '1', 10);
            
            const hideTooltip = () => {
                tooltip.style.opacity = '0';
                setTimeout(() => document.body.removeChild(tooltip), 300);
                element.removeEventListener('mouseleave', hideTooltip);
            };
            
            element.addEventListener('mouseleave', hideTooltip);
        }
        
        // Add tooltips to truncated text
        document.querySelectorAll('.meta-value[title]').forEach(element => {
            element.addEventListener('mouseenter', function() {
                showTooltip(this, this.getAttribute('title'));
            });
        });
    </script>
</body>
</html>

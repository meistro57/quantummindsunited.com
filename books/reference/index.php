<?php
// Scan directory for PDF files
$pdfFiles = [];
$directory = __DIR__;

if ($handle = opendir($directory)) {
    while (false !== ($file = readdir($handle))) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
            $pdfFiles[] = $file;
        }
    }
    closedir($handle);
}

// Sort files alphabetically
sort($pdfFiles);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Document Library</title>
    <style>
        :root {
            --primary-color: #0f172a;
            --secondary-color: #1e293b;
            --accent-color: #3b82f6;
            --accent-light: #60a5fa;
            --text-dark: #0f172a;
            --text-light: #64748b;
            --text-muted: #94a3b8;
            --bg-light: #f8fafc;
            --white: #ffffff;
            --red: #ef4444;
            --green: #10b981;
            --orange: #f59e0b;
            --shadow: 0 10px 25px rgba(15, 23, 42, 0.1);
            --shadow-hover: 0 20px 40px rgba(15, 23, 42, 0.15);
            --border-radius: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Inter', sans-serif;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 50%, #cbd5e1 100%);
            min-height: 100vh;
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 40px 30px;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-color), var(--accent-light), var(--accent-color));
        }
        
        .header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 10px;
            letter-spacing: -0.02em;
        }
        
        .header .subtitle {
            color: var(--text-light);
            font-size: 1.3rem;
            font-weight: 500;
        }
        
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
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
            background: linear-gradient(135deg, var(--bg-light), var(--white));
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            min-width: 80px;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--accent-color);
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
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
            background: var(--bg-light);
            border: 1px solid #e2e8f0;
            color: var(--text-light);
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 14px;
            font-weight: 500;
        }
        
        .view-btn:hover, .view-btn.active {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }
        
        .search-box {
            position: relative;
            margin: 0 15px;
        }
        
        .search-input {
            background: var(--bg-light);
            border: 1px solid #e2e8f0;
            padding: 10px 40px 10px 15px;
            border-radius: 25px;
            width: 250px;
            transition: var(--transition);
            font-size: 14px;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--accent-color);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }
        
        .pdf-grid {
            display: grid;
            gap: 25px;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            transition: var(--transition);
        }
        
        .pdf-grid.list-view {
            grid-template-columns: 1fr;
        }
        
        .pdf-card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
            overflow: hidden;
            position: relative;
            border: 1px solid #f1f5f9;
        }
        
        .pdf-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
            border-color: var(--accent-light);
        }
        
        .pdf-preview {
            height: 200px;
            background: linear-gradient(145deg, #f8fafc, #e2e8f0);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .pdf-icon {
            font-size: 4rem;
            color: var(--red);
            opacity: 0.8;
        }
        
        .pdf-pages {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .pdf-info {
            padding: 25px;
        }
        
        .pdf-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .pdf-metadata {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 12px;
            margin: 15px 0;
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px;
            background: var(--bg-light);
            border-radius: 8px;
            font-size: 12px;
        }
        
        .meta-icon {
            font-size: 16px;
            margin-bottom: 4px;
            color: var(--accent-color);
        }
        
        .meta-value {
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .meta-label {
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 10px;
        }
        
        .pdf-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }
        
        .action-btn {
            flex: 1;
            padding: 10px;
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
            background: var(--accent-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-color);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: var(--bg-light);
            color: var(--text-light);
            border: 1px solid #e2e8f0;
        }
        
        .btn-secondary:hover {
            background: var(--white);
            color: var(--text-dark);
            border-color: var(--accent-color);
        }
        
        .list-view .pdf-card {
            display: flex;
            align-items: center;
            padding: 20px;
        }
        
        .list-view .pdf-preview {
            width: 80px;
            height: 100px;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .list-view .pdf-icon {
            font-size: 2.5rem;
        }
        
        .list-view .pdf-info {
            flex: 1;
            padding: 0;
        }
        
        .list-view .pdf-metadata {
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            margin: 10px 0;
        }
        
        .no-files {
            text-align: center;
            padding: 80px 30px;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        
        .no-files-icon {
            font-size: 5rem;
            color: var(--text-muted);
            margin-bottom: 20px;
        }
        
        .no-files h3 {
            font-size: 2rem;
            color: var(--text-dark);
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .no-files p {
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        
        .upload-zone {
            border: 2px dashed var(--accent-light);
            border-radius: 12px;
            padding: 30px;
            margin-top: 20px;
            background: rgba(59, 130, 246, 0.05);
            transition: var(--transition);
        }
        
        .upload-zone:hover {
            border-color: var(--accent-color);
            background: rgba(59, 130, 246, 0.1);
        }
        
        .filter-tags {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .filter-tag {
            background: var(--white);
            border: 1px solid #e2e8f0;
            color: var(--text-light);
            padding: 6px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .filter-tag:hover, .filter-tag.active {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
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
            }
        }
        
        @media (max-width: 480px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .pdf-metadata {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .pdf-card {
            animation: fadeIn 0.5s ease-out;
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--bg-light);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--text-muted);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--accent-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 Reference Library</h1>
            <p class="subtitle">Reference Materials Collection</p>
        </div>
        
        <?php if (empty($pdfFiles)): ?>
            <div class="no-files">
                <div class="no-files-icon">📚</div>
                <h3>No PDF Documents Found</h3>
                <p>Upload your PDF files to this directory to build your digital library.</p>
                <p><strong>Current directory:</strong> <?php echo realpath($directory); ?></p>
                
                <div class="upload-zone">
                    <div style="font-size: 2rem; margin-bottom: 10px;">📁</div>
                    <p><strong>Drop PDF files here</strong></p>
                    <p style="font-size: 14px; color: var(--text-muted);">Supported format: PDF documents</p>
                </div>
            </div>
        <?php else: ?>
            <?php 
                $totalFiles = count($pdfFiles);
                $totalSize = 0;
                $totalPages = 0;
                
                foreach($pdfFiles as $file) {
                    if (file_exists($file)) {
                        $totalSize += filesize($file);
                    }
                }
                
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
                <?php foreach ($pdfFiles as $index => $filename): ?>
                    <?php 
                        // Clean up filename for display
                        $displayName = pathinfo($filename, PATHINFO_FILENAME);
                        $displayName = preg_replace('/[_-]/', ' ', $displayName);
                        $displayName = preg_replace('/\s+/', ' ', trim($displayName));
                        $displayName = ucwords(strtolower($displayName));
                        
                        // Get file info
                        $fileSize = file_exists($filename) ? filesize($filename) : 0;
                        $fileSizeMB = round($fileSize / (1024 * 1024), 2);
                        $fileDate = file_exists($filename) ? date('M j, Y', filemtime($filename)) : 'Unknown';
                        
                        // Determine size category
                        $sizeCategory = $fileSizeMB < 1 ? 'small' : ($fileSizeMB < 10 ? 'medium' : 'large');
                    ?>
                    <div class="pdf-card" data-filename="<?php echo strtolower($filename); ?>" data-title="<?php echo strtolower($displayName); ?>" data-size="<?php echo $sizeCategory; ?>">
                        <div class="pdf-preview">
                            <div class="pdf-icon">📄</div>
                            <div class="pdf-pages">PDF</div>
                        </div>
                        
                        <div class="pdf-info">
                            <div class="pdf-title"><?php echo htmlspecialchars($displayName); ?></div>
                            
                            <div class="pdf-metadata">
                                <div class="meta-item">
                                    <div class="meta-icon">💾</div>
                                    <div class="meta-value"><?php echo $fileSizeMB; ?>MB</div>
                                    <div class="meta-label">Size</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-icon">📅</div>
                                    <div class="meta-value"><?php echo date('M j', filemtime($filename)); ?></div>
                                    <div class="meta-label">Modified</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-icon">📄</div>
                                    <div class="meta-value">PDF</div>
                                    <div class="meta-label">Type</div>
                                </div>
                            </div>
                            
                            <div class="pdf-actions">
                                <button class="action-btn btn-primary" onclick="openPDF('<?php echo htmlspecialchars($filename); ?>')">
                                    👁️ View
                                </button>
                                <button class="action-btn btn-secondary" onclick="downloadPDF('<?php echo htmlspecialchars($filename); ?>')">
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
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.pdf-card');
            
            cards.forEach(card => {
                const title = card.dataset.title;
                const filename = card.dataset.filename;
                
                if (title.includes(searchTerm) || filename.includes(searchTerm)) {
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
            // Open in new tab/window
            window.open(filename, '_blank');
        }
        
        function downloadPDF(filename) {
            // Create download link
            const link = document.createElement('a');
            link.href = filename;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
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
                }
            }
        });
        
        // Add stagger animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.pdf-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
        
        // Auto-focus search on page load
        window.addEventListener('load', function() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput && window.innerWidth > 768) {
                // Only auto-focus on desktop
                setTimeout(() => searchInput.focus(), 500);
            }
        });
    </script>
</body>
</html>

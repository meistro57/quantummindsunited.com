<?php
// Scan directory for MP4 files
$videoFiles = [];
$directory = __DIR__; // Current directory
$files = scandir($directory);

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'mp4') {
        $videoFiles[] = $file;
    }
}

// Sort files alphabetically
sort($videoFiles);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NotebookLM Video Library - Quantum Minds United</title>
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
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
        
        /* Header matching main site */
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
            z-index: 1;
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
            font-size: 3rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            text-shadow: 0 0 30px rgba(74, 158, 255, 0.3);
        }
        
        .header .subtitle {
            color: var(--text-secondary);
            font-size: 1.2rem;
            font-weight: 400;
            margin-bottom: 20px;
        }
        
        /* Navigation breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        
        .breadcrumb a {
            color: var(--accent-blue);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .breadcrumb a:hover {
            color: var(--text-primary);
            text-shadow: 0 0 10px rgba(74, 158, 255, 0.5);
        }
        
        /* Stats section with sort button */
        .library-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 30px 0;
            flex-wrap: wrap;
            z-index: 1;
            position: relative;
        }
        
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            padding: 20px;
            border-radius: var(--border-radius);
            text-align: center;
            min-width: 140px;
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(10px);
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-glow);
            border-color: rgba(74, 158, 255, 0.4);
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-label {
            color: var(--text-muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }
        
        .control-btn {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            font-size: 14px;
            box-shadow: var(--shadow-glow);
            position: relative;
            overflow: hidden;
            width: 100%;
        }
        
        .control-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .control-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(74, 158, 255, 0.4);
        }
        
        .control-btn:hover::before {
            left: 100%;
        }
        
        .control-btn:active {
            transform: translateY(0);
        }
        
        /* Video grid */
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
            z-index: 1;
            position: relative;
            align-items: stretch;
        }
        
        .video-item {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            transition: var(--transition);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            backdrop-filter: blur(10px);
            position: relative;
        }
        
        .video-item::before {
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
            z-index: 1;
        }
        
        .video-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-glow);
            border-color: rgba(74, 158, 255, 0.4);
        }
        
        .video-item:hover::before {
            opacity: 1;
        }
        
        .video-container {
            position: relative;
            width: 100%;
            background: #000;
            aspect-ratio: 16/9;
            overflow: hidden;
            z-index: 2;
        }
        
        .video-player {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #000;
            display: block;
            position: relative;
            z-index: 8;
        }
        
        .video-overlay {
            position: absolute;
            bottom: 50px;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 15px 20px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            pointer-events: none;
            z-index: 5;
        }
        
        .video-item:hover .video-overlay {
            transform: translateY(0);
        }
        
        .fullscreen-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px;
            cursor: pointer;
            font-size: 16px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 10;
        }
        
        .video-item:hover .fullscreen-btn {
            opacity: 1;
        }
        
        .fullscreen-btn:hover {
            background: rgba(74, 158, 255, 0.8);
        }
        
        /* Video info section */
        .video-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 2;
        }
        
        .video-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            line-height: 1.3;
            flex-grow: 1;
        }
        
        .video-icon {
            margin-right: 10px;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .video-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-top: auto;
            margin-bottom: 15px;
        }
        
        .detail-item {
            background: rgba(139, 143, 164, 0.1);
            border: 1px solid rgba(139, 143, 164, 0.2);
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            color: var(--text-secondary);
        }
        
        .detail-icon {
            font-size: 14px;
        }
        
        /* Action buttons */
        .video-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }
        
        .action-btn {
            flex: 1;
            padding: 12px 16px;
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
            position: relative;
            overflow: hidden;
        }
        
        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--shadow-glow);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(74, 158, 255, 0.4);
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-secondary {
            background: var(--card-bg);
            color: var(--text-secondary);
            border: 1px solid var(--card-border);
        }
        
        .btn-secondary:hover {
            background: rgba(74, 158, 255, 0.1);
            color: var(--text-primary);
            border-color: var(--accent-blue);
            transform: translateY(-1px);
        }
        
        /* No videos state */
        .no-videos {
            text-align: center;
            padding: 60px 30px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
        }
        
        .no-videos h3 {
            font-size: 2rem;
            color: var(--text-primary);
            margin-bottom: 15px;
        }
        
        .no-videos p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        
        /* Toast notifications */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            color: var(--text-primary);
            padding: 15px 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-glow);
            backdrop-filter: blur(10px);
            transform: translateX(100%);
            transition: var(--transition);
            z-index: 1000;
            max-width: 300px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.success {
            border-left: 4px solid #10b981;
        }
        
        .toast.error {
            border-left: 4px solid #ef4444;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 2.5rem;
            }
            
            .video-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .library-stats {
                gap: 15px;
            }
            
            .stat-card {
                min-width: 100px;
                padding: 15px;
            }
            
            .video-container {
                aspect-ratio: 16/9;
            }
        }
        
        @media (max-width: 500px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .video-details {
                grid-template-columns: 1fr;
            }
            
            .video-grid {
                gap: 15px;
            }
            
            .video-container {
                aspect-ratio: 4/3;
            }
        }
        
        @media (min-width: 1200px) {
            .video-grid {
                grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="breadcrumb">
                <a href="/">🏠 Quantum Minds United</a>
                <span>→</span>
                <span>NotebookLM Video Library</span>
            </div>
            <h1>🎬 NotebookLM Video Library</h1>
            <p class="subtitle">Quantum Minds Collection</p>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 10px;">
                Short, potent video explainers. Watch, share, download.
            </p>
        </div>
    </div>
    
    <?php if (empty($videoFiles)): ?>
        <div class="no-videos">
            <h3>🎥 No Videos Found</h3>
            <p>Drop your .mp4 video files in this directory and refresh the page.</p>
            <p style="font-size: 14px; margin-top: 20px; color: var(--text-muted);">
                Supported format: MP4 files
            </p>
        </div>
    <?php else: ?>
        <?php 
            $totalFiles = count($videoFiles);
            $totalSize = 0;
            foreach($videoFiles as $file) {
                $totalSize += filesize($file);
            }
            $totalSizeGB = round($totalSize / (1024 * 1024 * 1024), 2);
            $totalSizeMB = round($totalSize / (1024 * 1024), 1);
        ?>
        
        <div class="library-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalFiles; ?></div>
                <div class="stat-label">Videos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalSizeGB > 0 ? $totalSizeGB . ' GB' : $totalSizeMB . ' MB'; ?></div>
                <div class="stat-label">Total Size</div>
            </div>
            <div class="stat-card">
                <button class="control-btn" id="sortBtn" onclick="sortByDate()">📅 Sort by Date</button>
            </div>
        </div>
        
        <div class="video-grid">
            <?php foreach ($videoFiles as $index => $filename): ?>
                <?php 
                    // Clean up filename for display
                    $displayName = pathinfo($filename, PATHINFO_FILENAME);
                    $displayName = preg_replace('/[\s\-_]+/', ' ', $displayName);
                    $displayName = preg_replace('/\s+/', ' ', trim($displayName));
                    $displayName = ucwords(strtolower($displayName));
                    
                    // Get file info
                    $fileSize = round(filesize($filename) / (1024 * 1024), 1);
                    $fileDate = date('M j, Y', filemtime($filename));
                    
                    // Generate unique ID
                    $videoId = 'video_' . $index;
                ?>
                <div class="video-item">
                    <div class="video-container">
                        <video 
                            id="<?php echo $videoId; ?>" 
                            class="video-player" 
                            controls 
                            preload="metadata"
                            poster=""
                        >
                            <source src="<?php echo htmlspecialchars($filename); ?>" type="video/mp4">
                            <p>Your browser doesn't support HTML5 video. 
                               <a href="<?php echo htmlspecialchars($filename); ?>">Download the video</a> instead.</p>
                        </video>
                        
                        <button class="fullscreen-btn" onclick="toggleFullscreen('<?php echo $videoId; ?>')">
                            ⛶
                        </button>
                        
                        <div class="video-overlay">
                            <div style="font-weight: bold; margin-bottom: 5px;">
                                <?php echo htmlspecialchars($displayName); ?>
                            </div>
                            <div style="font-size: 12px; opacity: 0.9;">
                                <?php echo $fileSize; ?> MB • <?php echo $fileDate; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="video-info">
                        <div class="video-title">
                            <span class="video-icon">🎥</span>
                            <?php echo htmlspecialchars($displayName); ?>
                        </div>
                        
                        <div class="video-details">
                            <div class="detail-item">
                                <span class="detail-icon">💾</span>
                                <span><?php echo $fileSize; ?> MB</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-icon">📅</span>
                                <span><?php echo $fileDate; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-icon">📄</span>
                                <span>MP4</span>
                            </div>
                        </div>
                        
                        <div class="video-actions">
                            <button class="action-btn btn-primary" onclick="copyVideoLink('<?php echo htmlspecialchars($filename); ?>', '<?php echo htmlspecialchars($displayName); ?>')">
                                🔗 Copy Link
                            </button>
                            <button class="action-btn btn-secondary" onclick="downloadVideo('<?php echo htmlspecialchars($filename); ?>')">
                                ⬇️ Download
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <script>
        let sortedByDate = false;
        let originalOrder = [];
        
        // Enhanced video controls with responsive handling
        document.addEventListener('DOMContentLoaded', function() {
            const videos = document.getElementsByTagName('video');
            
            // Store original order for sorting
            const videoGrid = document.querySelector('.video-grid');
            originalOrder = Array.from(videoGrid.children);
            
            // Auto-pause other videos when one starts playing
            document.addEventListener('play', function(e) {
                for(let i = 0; i < videos.length; i++) {
                    if(videos[i] != e.target) {
                        videos[i].pause();
                    }
                }
            }, true);
            
            // Handle video aspect ratio dynamically
            Array.from(videos).forEach(video => {
                video.addEventListener('loadedmetadata', function() {
                    const container = this.closest('.video-container');
                    const aspectRatio = this.videoWidth / this.videoHeight;
                    
                    // Adjust container aspect ratio based on video
                    if (aspectRatio > 2.5) {
                        // Ultra-wide video
                        container.style.aspectRatio = '21/9';
                    } else if (aspectRatio < 1.2) {
                        // Portrait or square video
                        container.style.aspectRatio = '4/5';
                    } else {
                        // Standard video, keep 16:9
                        container.style.aspectRatio = '16/9';
                    }
                });
                
                video.addEventListener('loadeddata', function() {
                    // Set current time to 10% of video length for better poster frame
                    if (this.duration) {
                        this.currentTime = this.duration * 0.1;
                    }
                });
                
                video.addEventListener('seeked', function() {
                    // Create canvas to capture frame
                    if (!this.hasAttribute('data-poster-generated')) {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        canvas.width = this.videoWidth;
                        canvas.height = this.videoHeight;
                        ctx.drawImage(this, 0, 0);
                        
                        try {
                            this.poster = canvas.toDataURL();
                            this.setAttribute('data-poster-generated', 'true');
                            this.currentTime = 0; // Reset to beginning
                        } catch (e) {
                            // Handle CORS or other issues silently
                        }
                    }
                });
            });
            
            // Handle window resize for responsive adjustments
            let resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(function() {
                    // Recalculate video container sizes if needed
                    Array.from(videos).forEach(video => {
                        if (video.readyState >= 1) {
                            video.dispatchEvent(new Event('loadedmetadata'));
                        }
                    });
                }, 250);
            });
        });
        
        function sortByDate() {
            const videoGrid = document.querySelector('.video-grid');
            const button = document.getElementById('sortBtn');
            sortedByDate = !sortedByDate;
            
            if (sortedByDate) {
                // Sort by date - newest first
                const items = Array.from(videoGrid.children);
                
                // Extract dates from meta items and sort
                items.sort((a, b) => {
                    const dateA = a.querySelector('.detail-item:nth-child(2) span:last-child').textContent;
                    const dateB = b.querySelector('.detail-item:nth-child(2) span:last-child').textContent;
                    return new Date(dateB) - new Date(dateA); // Newest first
                });
                
                // Clear grid and append sorted items
                videoGrid.innerHTML = '';
                items.forEach(item => videoGrid.appendChild(item));
                
                // Update button appearance
                button.textContent = '📅 Newest First';
                button.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                
                showToast('✅ Sorted by date - newest first!', 'success');
            } else {
                // Reset to original alphabetical order
                videoGrid.innerHTML = '';
                originalOrder.forEach(item => videoGrid.appendChild(item));
                
                // Reset button appearance
                button.textContent = '📅 Sort by Date';
                button.style.background = 'var(--gradient-primary)';
                
                showToast('✅ Restored alphabetical order!', 'success');
            }
        }
        
        // Fullscreen functionality
        function toggleFullscreen(videoId) {
            const video = document.getElementById(videoId);
            
            if (video.requestFullscreen) {
                video.requestFullscreen();
            } else if (video.webkitRequestFullscreen) {
                video.webkitRequestFullscreen();
            } else if (video.mozRequestFullScreen) {
                video.mozRequestFullScreen();
            } else if (video.msRequestFullscreen) {
                video.msRequestFullscreen();
            }
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            const activeVideo = document.querySelector('video:not([paused])');
            if (!activeVideo) return;
            
            switch(e.key) {
                case ' ':
                    e.preventDefault();
                    if (activeVideo.paused) {
                        activeVideo.play();
                    } else {
                        activeVideo.pause();
                    }
                    break;
                case 'ArrowLeft':
                    activeVideo.currentTime -= 10;
                    break;
                case 'ArrowRight':
                    activeVideo.currentTime += 10;
                    break;
                case 'f':
                    toggleFullscreen(activeVideo.id);
                    break;
            }
        });
        
        // Video link and download functions
        function copyVideoLink(filename, displayName) {
            const currentUrl = window.location.href;
            const baseUrl = currentUrl.substring(0, currentUrl.lastIndexOf('/') + 1);
            const videoUrl = baseUrl + encodeURIComponent(filename);
            
            // Try to copy to clipboard
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(videoUrl).then(function() {
                    showToast(`✅ Link copied for "${displayName}"!`, 'success');
                }).catch(function() {
                    fallbackCopyTextToClipboard(videoUrl, displayName);
                });
            } else {
                fallbackCopyTextToClipboard(videoUrl, displayName);
            }
        }
        
        function fallbackCopyTextToClipboard(text, displayName) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            textArea.style.top = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                showToast(`✅ Link copied for "${displayName}"!`, 'success');
            } catch (err) {
                showToast(`❌ Failed to copy link. Please copy manually: ${text}`, 'error');
                console.error('Fallback: Oops, unable to copy', err);
            }
            
            document.body.removeChild(textArea);
        }
        
        function downloadVideo(filename) {
            const link = document.createElement('a');
            link.href = filename;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showToast(`⬇️ Download started for "${filename}"`, 'success');
        }
        
        function showToast(message, type = 'success') {
            // Remove existing toast if any
            const existingToast = document.querySelector('.toast');
            if (existingToast) {
                existingToast.remove();
            }
            
            // Create new toast
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            // Show toast
            setTimeout(() => toast.classList.add('show'), 100);
            
            // Hide toast after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>

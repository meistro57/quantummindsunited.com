<?php
// Simple MP3 file scanner
$audioFiles = [];
$directory = '.';

if ($handle = opendir($directory)) {
    while (false !== ($file = readdir($handle))) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'm4a') {
            $audioFiles[] = $file;
        }
    }
    closedir($handle);
}

sort($audioFiles);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quantum Deep Dives - Quantum Minds United</title>
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
            margin-bottom: 10px;
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
        
        /* Controls bar with stats and sort */
        .controls-bar {
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
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: var(--text-primary);
            padding: 8px 12px;
            background: rgba(74, 158, 255, 0.1);
            border-radius: 8px;
            border: 1px solid rgba(74, 158, 255, 0.2);
        }
        
        .stat-icon {
            font-size: 1.2rem;
        }
        
        .playback-controls {
            display: flex;
            gap: 10px;
            align-items: center;
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
        
        /* Audiobook grid */
        .audiobook-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: 1fr;
        }
        
        .audiobook {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            transition: var(--transition);
            overflow: hidden;
            position: relative;
            backdrop-filter: blur(10px);
        }
        
        .audiobook::before {
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
        
        .audiobook:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-glow);
            border-color: rgba(74, 158, 255, 0.4);
        }
        
        .audiobook:hover::before {
            opacity: 1;
        }
        
        .audiobook.playing {
            background: rgba(74, 158, 255, 0.1);
            border-color: var(--accent-blue);
            box-shadow: 0 0 30px rgba(74, 158, 255, 0.3);
        }
        
        .audiobook-header {
            padding: 25px;
            position: relative;
            z-index: 2;
        }
        
        .title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 12px;
            line-height: 1.3;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .book-icon {
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .info {
            display: flex;
            gap: 20px;
            color: var(--text-secondary);
            font-size: 0.9rem;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            background: rgba(139, 143, 164, 0.1);
            border-radius: 6px;
            font-size: 0.85rem;
        }
        
        /* Custom audio player styling */
        .player-container {
            position: relative;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }
        
        .audio-player {
            width: 100%;
            height: 60px;
            border: none;
            outline: none;
            background: transparent;
            filter: invert(1) hue-rotate(180deg);
        }
        
        .progress-indicator {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: var(--gradient-primary);
            transition: width 0.1s ease;
            border-radius: 0 0 0 var(--border-radius);
            box-shadow: 0 0 10px rgba(74, 158, 255, 0.5);
        }
        
        /* No files state */
        .no-files {
            text-align: center;
            padding: 60px 30px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(10px);
        }
        
        .no-files h3 {
            font-size: 2rem;
            color: var(--text-primary);
            margin-bottom: 15px;
        }
        
        .no-files p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        
        /* Sticky playlist controls */
        .playlist-controls {
            position: sticky;
            bottom: 20px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            padding: 15px 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-glow);
            backdrop-filter: blur(10px);
            margin-top: 20px;
            display: none;
            z-index: 10;
        }
        
        .playlist-controls.active {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .now-reading {
            font-weight: 600;
            color: var(--text-primary);
            text-shadow: 0 0 10px rgba(74, 158, 255, 0.3);
        }
        
        .playlist-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--accent-blue);
            transition: var(--transition);
            padding: 8px;
            border-radius: 6px;
        }
        
        .playlist-btn:hover {
            transform: scale(1.1);
            text-shadow: 0 0 15px rgba(74, 158, 255, 0.6);
            background: rgba(74, 158, 255, 0.1);
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
            .container {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 2.5rem;
            }
            
            .controls-bar {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
            
            .info {
                flex-direction: column;
                gap: 8px;
            }
            
            .audiobook-header {
                padding: 20px;
            }
            
            .playback-controls {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .library-stats {
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }
            
            .stat-item {
                justify-content: center;
            }
            
            .title {
                font-size: 1.2rem;
            }
            
            .control-btn {
                flex: 1;
                min-width: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="breadcrumb">
                    <a href="/">🏠 Quantum Minds United</a>
                    <span>→</span>
                    <span>Audio Library</span>
                </div>
                <h1>📚 Deep Dive Audio Collection</h1>
                <p class="subtitle">Short, potent audio podcasts. Listen, share, download.</p>
                <p class="subtitle">Audio Generated From NotebookLM</p>
            </div>
        </div>

        <?php if (empty($audioFiles)): ?>
            <div class="no-files">
                <h3>📚 No Audio Files Found</h3>
                <p>Make sure you have .m4a files in this directory to start your reading journey.</p>
                <p><strong>Current directory:</strong> <?php echo realpath('.'); ?></p>
                <p style="font-size: 14px; margin-top: 20px; color: var(--text-muted);">
                    Supported format: M4A files
                </p>
            </div>
        <?php else: ?>
            <?php 
                $totalFiles = count($audioFiles);
                $totalSize = 0;
                foreach($audioFiles as $file) {
                    if (file_exists($file)) {
                        $totalSize += filesize($file);
                    }
                }
                $totalSizeGB = round($totalSize / (1024 * 1024 * 1024), 2);
                $totalSizeMB = round($totalSize / (1024 * 1024), 1);
            ?>
            
            <div class="controls-bar">
                <div class="library-stats">
                    <div class="stat-item">
                        <span class="stat-icon">📖</span>
                        <span><?php echo $totalFiles; ?> Audiobook<?php echo $totalFiles !== 1 ? 's' : ''; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-icon">💾</span>
                        <span><?php echo $totalSizeGB > 0 ? $totalSizeGB . ' GB' : $totalSizeMB . ' MB'; ?></span>
                    </div>
                </div>
                <div class="playback-controls">
                    <button class="control-btn" onclick="playAll()">▶️ Start Reading</button>
                    <button class="control-btn" onclick="pauseAll()">⏸️ Pause All</button>
                    <button class="control-btn" onclick="shuffleLibrary()">🔀 Random Book</button>
                </div>
            </div>
            
            <div class="audiobook-grid" id="audiobookGrid">
                <?php foreach ($audioFiles as $index => $filename): ?>
                    <?php 
                        $displayName = pathinfo($filename, PATHINFO_FILENAME);
                        $displayName = str_replace(['_', '-'], ' ', $displayName);
                        $displayName = preg_replace('/\s+/', ' ', trim($displayName));
                        $displayName = ucwords(strtolower($displayName));
                        
                        $fileSize = file_exists($filename) ? round(filesize($filename) / (1024 * 1024), 1) : 'Unknown';
                        $fileDate = file_exists($filename) ? date('M j, Y', filemtime($filename)) : 'Unknown';
                        $audioId = 'audiobook_' . $index;
                    ?>
                    <div class="audiobook" data-index="<?php echo $index; ?>">
                        <div class="audiobook-header">
                            <div class="title">
                                <span class="book-icon">📖</span>
                                <?php echo htmlspecialchars($displayName); ?>
                            </div>
                            <div class="info">
                                <div class="info-item">
                                    <span>📄</span>
                                    <span><?php echo htmlspecialchars($filename); ?></span>
                                </div>
                                <div class="info-item">
                                    <span>💾</span>
                                    <span><?php echo $fileSize; ?> MB</span>
                                </div>
                                <div class="info-item">
                                    <span>📅</span>
                                    <span><?php echo $fileDate; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="player-container">
                            <audio 
                                id="<?php echo $audioId; ?>" 
                                class="audio-player" 
                                controls 
                                preload="metadata"
                                data-title="<?php echo htmlspecialchars($displayName); ?>"
                            >
                                <source src="<?php echo htmlspecialchars($filename); ?>" type="audio/mpeg">
                                <p>Your browser doesn't support HTML5 audio. 
                                   <a href="<?php echo htmlspecialchars($filename); ?>">Download the audiobook</a> instead.</p>
                            </audio>
                            <div class="progress-indicator" style="width: 0%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="playlist-controls" id="playlistControls">
                <div class="now-reading" id="nowReading">Now Reading: Book Title</div>
                <div>
                    <button class="playlist-btn" onclick="previousBook()">⏮️</button>
                    <button class="playlist-btn" id="playPauseBtn" onclick="togglePlayPause()">⏸️</button>
                    <button class="playlist-btn" onclick="nextBook()">⏭️</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        let currentBook = -1;
        let isReading = false;
        let library = [];
        let shuffled = false;
        let sortedByDate = false;
        let originalOrder = [];
        
        document.addEventListener('DOMContentLoaded', function() {
            const audios = document.querySelectorAll('audio');
            
            // Store original order for sorting
            const audiobookGrid = document.getElementById('audiobookGrid');
            originalOrder = Array.from(audiobookGrid.children);
            
            // Initialize library
            audios.forEach((audio, index) => {
                library.push(index);
                
                // Add event listeners
                audio.addEventListener('play', function() {
                    handleBookPlay(this, index);
                });
                
                audio.addEventListener('pause', function() {
                    handleBookPause(this, index);
                });
                
                audio.addEventListener('ended', function() {
                    handleBookEnd(index);
                });
                
                audio.addEventListener('timeupdate', function() {
                    updateProgress(this, index);
                });
            });
        });
        
        function handleBookPlay(audio, index) {
            // Pause all other audio
            const audios = document.querySelectorAll('audio');
            audios.forEach((a, i) => {
                if (i !== index && !a.paused) {
                    a.pause();
                }
            });
            
            // Update UI
            document.querySelectorAll('.audiobook').forEach(item => item.classList.remove('playing'));
            document.querySelector(`[data-index="${index}"]`).classList.add('playing');
            
            // Update controls
            currentBook = index;
            isReading = true;
            updatePlaylistControls(audio.dataset.title);
            document.getElementById('playPauseBtn').textContent = '⏸️';
        }
        
        function handleBookPause(audio, index) {
            isReading = false;
            document.getElementById('playPauseBtn').textContent = '▶️';
            if (currentBook === index) {
                document.querySelector(`[data-index="${index}"]`).classList.remove('playing');
            }
        }
        
        function handleBookEnd(index) {
            if (currentBook === index) {
                nextBook();
            }
        }
        
        function updateProgress(audio, index) {
            const progress = (audio.currentTime / audio.duration) * 100;
            
            // Find the audiobook container that matches this audio element
            const audiobook = audio.closest('.audiobook');
            if (audiobook) {
                const progressBar = audiobook.querySelector('.progress-indicator');
                if (progressBar) {
                    progressBar.style.width = progress + '%';
                }
            }
        }
        
        function updatePlaylistControls(title) {
            const controls = document.getElementById('playlistControls');
            const nowReading = document.getElementById('nowReading');
            controls.classList.add('active');
            nowReading.textContent = `Now Reading: ${title}`;
        }
        
        function playAll() {
            const audiobookItems = document.querySelectorAll('.audiobook');
            if (audiobookItems.length > 0) {
                const firstIndex = parseInt(audiobookItems[0].dataset.index);
                const firstBook = document.getElementById(`audiobook_${firstIndex}`);
                if (firstBook) {
                    firstBook.play();
                }
            }
        }
        
        function pauseAll() {
            document.querySelectorAll('audio').forEach(audio => audio.pause());
            document.getElementById('playlistControls').classList.remove('active');
        }
        
        function shuffleLibrary() {
            const audiobookItems = document.querySelectorAll('.audiobook');
            if (audiobookItems.length > 0) {
                const randomAudiobook = audiobookItems[Math.floor(Math.random() * audiobookItems.length)];
                const randomIndex = parseInt(randomAudiobook.dataset.index);
                const randomBook = document.getElementById(`audiobook_${randomIndex}`);
                if (randomBook) {
                    randomBook.play();
                }
            }
        }
        
        function sortByDate() {
            const audiobookGrid = document.getElementById('audiobookGrid');
            const button = document.getElementById('sortBtn');
            sortedByDate = !sortedByDate;
            
            if (sortedByDate) {
                // Sort by date - newest first
                const items = Array.from(audiobookGrid.children);
                
                // Extract dates from info items and sort
                items.sort((a, b) => {
                    const dateA = a.querySelector('.info-item:last-child span:last-child').textContent;
                    const dateB = b.querySelector('.info-item:last-child span:last-child').textContent;
                    return new Date(dateB) - new Date(dateA); // Newest first
                });
                
                // Clear grid and append sorted items
                audiobookGrid.innerHTML = '';
                items.forEach(item => audiobookGrid.appendChild(item));
                
                // Re-attach event listeners after DOM manipulation
                attachEventListeners();
                
                // Update button appearance
                button.textContent = '📅 Newest First';
                button.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                
                showToast('✅ Sorted by date - newest first!', 'success');
            } else {
                // Reset to original alphabetical order
                audiobookGrid.innerHTML = '';
                originalOrder.forEach(item => audiobookGrid.appendChild(item));
                
                // Re-attach event listeners after DOM manipulation
                attachEventListeners();
                
                // Reset button appearance
                button.textContent = '📅 Sort by Date';
                button.style.background = 'var(--gradient-primary)';
                
                showToast('✅ Restored alphabetical order!', 'success');
            }
        }
        
        function previousBook() {
            if (currentBook >= 0) {
                // Find current audiobook's position in the visual order
                const audiobookItems = document.querySelectorAll('.audiobook');
                let currentVisualIndex = -1;
                
                for (let i = 0; i < audiobookItems.length; i++) {
                    if (parseInt(audiobookItems[i].dataset.index) === currentBook) {
                        currentVisualIndex = i;
                        break;
                    }
                }
                
                if (currentVisualIndex > 0) {
                    const prevIndex = parseInt(audiobookItems[currentVisualIndex - 1].dataset.index);
                    const prevBook = document.getElementById(`audiobook_${prevIndex}`);
                    if (prevBook) {
                        prevBook.play();
                    }
                }
            }
        }
        
        function nextBook() {
            if (currentBook >= 0) {
                // Find current audiobook's position in the visual order
                const audiobookItems = document.querySelectorAll('.audiobook');
                let currentVisualIndex = -1;
                
                for (let i = 0; i < audiobookItems.length; i++) {
                    if (parseInt(audiobookItems[i].dataset.index) === currentBook) {
                        currentVisualIndex = i;
                        break;
                    }
                }
                
                if (currentVisualIndex >= 0 && currentVisualIndex < audiobookItems.length - 1) {
                    const nextIndex = parseInt(audiobookItems[currentVisualIndex + 1].dataset.index);
                    const nextBook = document.getElementById(`audiobook_${nextIndex}`);
                    if (nextBook) {
                        nextBook.play();
                    }
                } else {
                    // End of library
                    pauseAll();
                }
            }
        }
        
        function togglePlayPause() {
            if (currentBook >= 0) {
                const audio = document.getElementById(`audiobook_${currentBook}`);
                if (audio.paused) {
                    audio.play();
                } else {
                    audio.pause();
                }
            }
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
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName.toLowerCase() !== 'input') {
                switch(e.code) {
                    case 'Space':
                        e.preventDefault();
                        togglePlayPause();
                        break;
                    case 'ArrowLeft':
                        previousBook();
                        break;
                    case 'ArrowRight':
                        nextBook();
                        break;
                    case 'KeyR':
                        if (e.ctrlKey || e.metaKey) {
                            e.preventDefault();
                            shuffleLibrary();
                        }
                        break;
                }
            }
        });
    </script>
</body>
</html>

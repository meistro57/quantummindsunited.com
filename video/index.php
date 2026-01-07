<?php
// SCANNER: Read the timeline
$videoFiles = [];
$directory = __DIR__;
$files = scandir($directory);

// CONFIG: How many hours counts as "New"?
$newHours = 72; // 3 Days

// SMART CATEGORIZER
function getCategory($filename) {
    $name = strtolower($filename);
    if (preg_match('/(adhd|neuro|brain|broken|misfit)/', $name)) return 'neuro';
    if (preg_match('/(ai |artificial|code|gpt|digital|robot|tech|machine)/', $name)) return 'ai';
    if (preg_match('/(prophecy|nostradamus|future|timeline|asteroid|world|shift|war)/', $name)) return 'prophecy';
    if (preg_match('/(god|jesus|spirit|soul|conscious|awakening|echoes|healing|temple)/', $name)) return 'spirit';
    if (preg_match('/(quantum|physics|dimension|time|alien|ufo|galactic|starseed)/', $name)) return 'cosmic';
    return 'general';
}

$categories = [
    'all' => ['icon' => '♾️', 'label' => 'All Timelines'],
    'ai' => ['icon' => '🤖', 'label' => 'Artificial Intelligence'],
    'neuro' => ['icon' => '🧠', 'label' => 'Neurodivergence'],
    'prophecy' => ['icon' => '🔮', 'label' => 'Prophecy'],
    'spirit' => ['icon' => '✨', 'label' => 'Spirituality'],
    'cosmic' => ['icon' => '👽', 'label' => 'Cosmic'],
    'general' => ['icon' => '📂', 'label' => 'Archives']
];

$totalSize = 0;

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'mp4') {
        $cleanName = pathinfo($file, PATHINFO_FILENAME);
        $cleanName = preg_replace('/[\s\-_]+/', ' ', $cleanName);
        $cleanName = ucwords(strtolower(trim($cleanName)));
        
        $modifiedTime = filemtime($file);
        $fileSize = filesize($file);
        $totalSize += $fileSize;
        $isNew = (time() - $modifiedTime) < ($newHours * 3600);

        $videoFiles[] = [
            'filename' => $file,
            'cleanName' => $cleanName,
            'modified' => $modifiedTime,
            'size' => $fileSize,
            'category' => getCategory($file),
            'isNew' => $isNew
        ];
    }
}

// Sort: Newest First
usort($videoFiles, function($a, $b) {
    return $b['modified'] - $a['modified'];
});

// Format Total Size
$totalSizeGB = round($totalSize / (1024 * 1024 * 1024), 2);
$totalCount = count($videoFiles);

$playlistJson = json_encode($videoFiles);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script defer src="https://analytics.quantummindsunited.com/script.js" data-website-id="dbf19e80-6774-4172-9dff-ccd67c7d7b84"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quantum Cinema ULTRA</title>
    <style>
        :root {
            --bg-deep: #050a14;
            --glass: rgba(20, 30, 50, 0.6);
            --glass-border: rgba(100, 200, 255, 0.1);
            --accent: #00f2ff; /* Cyan Neon */
            --accent-glow: rgba(0, 242, 255, 0.5);
            --alert: #ff0055; /* Hot Pink/Red for NEW items */
            --text-main: #e0e6ed;
            --text-dim: #7a8c9e;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; outline: none; }

        body {
            font-family: 'Segoe UI', 'Inter', sans-serif;
            background-color: var(--bg-deep);
            background-image: 
                linear-gradient(rgba(0, 242, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 242, 255, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Ambient Glow Spot */
        body::before {
            content: '';
            position: fixed;
            top: -20%; left: 20%;
            width: 60%; height: 60%;
            background: radial-gradient(circle, rgba(0, 100, 255, 0.15), transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* --- CONTROL DECK (Header) --- */
        .control-deck {
            position: sticky;
            top: 20px;
            z-index: 100;
            width: 95%;
            max-width: 1400px;
            margin: 0 auto 30px auto;
            background: rgba(10, 15, 30, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 15px 25px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
            justify-content: space-between;
        }

        .brand-cluster {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .brand {
            font-weight: 800;
            font-size: 1.4rem;
            letter-spacing: -1px;
            background: linear-gradient(90deg, #fff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* --- NEW STATS BAR --- */
        .stats-bar {
            display: flex;
            gap: 15px;
            font-size: 0.75rem;
            font-family: 'Courier New', monospace;
            color: var(--accent);
            opacity: 0.8;
        }

        .stat-item {
            background: rgba(0, 242, 255, 0.1);
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid rgba(0, 242, 255, 0.2);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .search-module {
            flex-grow: 1;
            max-width: 300px;
            position: relative;
        }

        .search-input {
            width: 100%;
            background: rgba(0,0,0,0.3);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            padding: 10px 15px 10px 40px;
            color: white;
            font-family: inherit;
            transition: 0.3s;
        }

        .search-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.2);
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.5;
        }

        .action-cluster {
            display: flex;
            gap: 10px;
        }

        .btn-glitch {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            color: var(--accent);
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-glitch:hover {
            background: var(--accent);
            color: #000;
            box-shadow: 0 0 20px var(--accent-glow);
        }

        /* --- FILTERS --- */
        .filter-bar {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 30px;
            position: relative;
            z-index: 10;
        }

        .filter-chip {
            background: transparent;
            border: 1px solid transparent;
            color: var(--text-dim);
            padding: 6px 14px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: 0.3s;
        }

        .filter-chip:hover, .filter-chip.active {
            color: white;
            background: rgba(255,255,255,0.05);
            border-color: var(--glass-border);
            text-shadow: 0 0 10px rgba(255,255,255,0.5);
        }

        /* --- HOLOGRAPHIC GRID (TILT REMOVED) --- */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px 50px 20px;
        }

        .holo-card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            position: relative;
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .holo-card::after {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            border-radius: 12px;
            box-shadow: 0 0 0 0 rgba(0, 242, 255, 0);
            transition: box-shadow 0.4s ease;
            z-index: -1;
        }

        .holo-card:hover {
            transform: translateY(-8px);
            border-color: rgba(0, 242, 255, 0.4);
        }

        .holo-card:hover::after {
            box-shadow: 0 12px 40px rgba(0, 242, 255, 0.2);
        }

        /* --- NEW BADGE --- */
        .new-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--alert);
            color: white;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 20px;
            z-index: 10;
            box-shadow: 0 0 10px var(--alert);
            animation: pulse-alert 2s infinite;
            letter-spacing: 1px;
            pointer-events: none;
        }

        @keyframes pulse-alert {
            0% { box-shadow: 0 0 0 0 rgba(255, 0, 85, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 0, 85, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 0, 85, 0); }
        }

        /* --- SHARE BUTTONS (CARD) --- */
        .share-cluster {
            position: absolute;
            top: 10px;
            left: 10px;
            display: flex;
            gap: 8px;
            z-index: 20;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s;
        }

        .share-btn-card {
            background: rgba(0,0,0,0.6);
            border: 1px solid var(--accent);
            color: var(--accent);
            width: 30px; height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center; justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .share-btn-card:hover {
            background: var(--accent);
            color: #000;
            box-shadow: 0 0 15px var(--accent-glow);
        }

        .holo-card:hover .share-cluster {
            opacity: 1;
            transform: translateY(0);
        }

        /* Mobile: Always show share buttons */
        @media (max-width: 768px) {
            .share-cluster {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- QR CODE MODAL --- */
        .qr-modal {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .qr-modal.active {
            opacity: 1;
            pointer-events: all;
        }

        .qr-content {
            background: var(--glass);
            border: 1px solid var(--accent);
            border-radius: 16px;
            padding: 30px;
            max-width: 90%;
            text-align: center;
            position: relative;
            box-shadow: 0 0 50px rgba(0, 242, 255, 0.3);
        }

        .qr-close {
            position: absolute;
            top: 10px;
            right: 15px;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            opacity: 0.5;
            transition: 0.3s;
        }

        .qr-close:hover {
            opacity: 1;
            color: var(--accent);
        }

        .qr-title {
            font-size: 1rem;
            margin-bottom: 20px;
            color: var(--accent);
            font-weight: 600;
        }

        #qrCodeCanvas {
            background: white;
            padding: 10px;
            border-radius: 8px;
            margin: 0 auto 15px auto;
            display: block;
        }

        .qr-url {
            font-size: 0.75rem;
            color: var(--text-dim);
            word-break: break-all;
            margin-top: 10px;
            font-family: 'Courier New', monospace;
        }

        .thumb-box {
            position: relative;
            aspect-ratio: 16/9;
            background: #000;
            overflow: hidden;
            border-bottom: 1px solid var(--glass-border);
        }

        .thumb-box video {
            width: 100%; height: 100%;
            object-fit: cover;
            opacity: 0.7;
            transition: opacity 0.3s, transform 5s;
        }
        
        .holo-card:hover .thumb-box video {
            opacity: 1;
            transform: scale(1.1);
        }

        .card-body {
            padding: 15px;
            background: linear-gradient(180deg, rgba(20,30,50,0) 0%, rgba(10,15,25,0.8) 100%);
        }

        .video-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .video-meta {
            font-size: 0.8rem;
            color: var(--text-dim);
            display: flex;
            justify-content: space-between;
        }

        /* --- CINEMA MODE --- */
        .cinema-layer {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.95);
            z-index: 999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .cinema-layer.active {
            opacity: 1;
            pointer-events: all;
        }

        .cinema-wrapper {
            width: 90%;
            max-width: 1200px;
            aspect-ratio: 16/9;
            box-shadow: 0 0 100px rgba(0, 242, 255, 0.1);
            position: relative;
        }

        .cinema-wrapper video {
            width: 100%; height: 100%;
            border-radius: 8px;
            background: #000;
        }

        .close-cinema {
            position: absolute;
            top: 20px; right: 30px;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            opacity: 0.5;
            transition: 0.3s;
        }
        .close-cinema:hover { opacity: 1; color: var(--accent); }

        .cinema-ui {
            margin-top: 20px;
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* --- TOAST NOTIFICATION --- */
        .sci-toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: rgba(0, 10, 20, 0.9);
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 12px 25px;
            border-radius: 30px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            box-shadow: 0 0 20px var(--accent-glow);
            z-index: 2000;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sci-toast.active {
            transform: translateX(-50%) translateY(0);
        }

        @media (max-width: 768px) {
            .control-deck { flex-direction: column; align-items: stretch; }
            .brand-cluster { align-items: center; }
            .stats-bar { justify-content: center; }
            .search-module { max-width: 100%; }
            .grid-container { grid-template-columns: 1fr; }
        }

    </style>
</head>
<body>

    <div class="control-deck">
        <div class="brand-cluster">
            <div class="brand">
                <a href="/" style="text-decoration:none; color:inherit; font-size:1.2rem;">⚡</a>
                <span>QUANTUM LIBRARY</span>
            </div>
            <div class="stats-bar">
                <div class="stat-item">
                    <span>🎬</span> <?php echo $totalCount; ?> FILES
                </div>
                <div class="stat-item">
                    <span>💾</span> <?php echo $totalSizeGB; ?> GB
                </div>
                <div class="stat-item">
                    <span>⏳</span> <span id="totalRuntime">SCANNING...</span>
                </div>
            </div>
        </div>
        
        <div class="search-module">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchInput" class="search-input" placeholder="Search the archives..." onkeyup="filterSystem()">
        </div>

        <div class="action-cluster">
            <button class="btn-glitch" onclick="oracleMode()">
                🔮 Oracle
            </button>
            <button class="btn-glitch" onclick="playAll()">
                ▶ Binge
            </button>
        </div>
    </div>

    <div class="filter-bar">
        <?php foreach($categories as $key => $cat): ?>
            <div class="filter-chip <?php echo $key === 'all' ? 'active' : ''; ?>" 
                 onclick="setCategory('<?php echo $key; ?>', this)">
                <?php echo $cat['icon'] . ' ' . $cat['label']; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="grid-container" id="videoGrid">
        <?php foreach ($videoFiles as $index => $video): ?>
            <div class="holo-card" 
                 data-category="<?php echo $video['category']; ?>"
                 data-title="<?php echo strtolower($video['cleanName']); ?>"
                 data-index="<?php echo $index; ?>"
                 onclick="openCinema(<?php echo $index; ?>)">
                
                <?php if($video['isNew']): ?>
                    <div class="new-badge">NEW</div>
                <?php endif; ?>

                <div class="share-cluster">
                    <div class="share-btn-card" 
                         onclick="event.stopPropagation(); shareLink('<?php echo htmlspecialchars($video['filename']); ?>')">
                        🔗
                    </div>
                    <div class="share-btn-card" 
                         onclick="event.stopPropagation(); showQR('<?php echo htmlspecialchars($video['filename']); ?>', '<?php echo htmlspecialchars($video['cleanName']); ?>')">
                        📱
                    </div>
                </div>
                
                <div class="thumb-box">
                    <video 
                        class="grid-video"
                        preload="metadata"
                        muted loop playsinline 
                        onmouseenter="this.play()" 
                        onmouseleave="this.pause(); this.currentTime=0;"
                    >
                        <source src="<?php echo htmlspecialchars($video['filename']); ?>#t=2.0" type="video/mp4">
                    </video>
                </div>
                
                <div class="card-body">
                    <div class="video-title"><?php echo $video['cleanName']; ?></div>
                    <div class="video-meta">
                        <span><?php echo $categories[$video['category']]['label']; ?></span>
                        <span><?php echo round($video['size'] / 1048576, 1); ?> MB</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="cinemaLayer" class="cinema-layer">
        <div class="close-cinema" onclick="closeCinema()">×</div>
        <div class="cinema-wrapper">
            <video id="mainPlayer" controls controlsList="nodownload">
                <source src="" type="video/mp4">
            </video>
        </div>
        <div class="cinema-ui">
            <button class="btn-glitch" onclick="prevVideo()">⮜ Prev</button>
            <div style="display:flex; flex-direction:column; align-items:center;">
                <span id="nowPlayingText" style="color:var(--text-dim); margin-bottom: 5px;">Select a video</span>
                <button class="btn-glitch" id="shareCurrentBtn" style="font-size:0.7rem; padding: 4px 10px;">🔗 COPY LINK</button>
            </div>
            <button class="btn-glitch" onclick="nextVideo()">Next ⮞</button>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div id="qrModal" class="qr-modal">
        <div class="qr-content">
            <div class="qr-close" onclick="closeQR()">×</div>
            <div class="qr-title" id="qrTitle">QUANTUM TRANSMISSION</div>
            <canvas id="qrCodeCanvas"></canvas>
            <div class="qr-url" id="qrUrl"></div>
        </div>
    </div>

    <div id="toast" class="sci-toast">
        <span>⚡</span> // TRANSMISSION LINK COPIED
    </div>

    <script>
        const LIBRARY = <?php echo $playlistJson; ?>;
        let currentIndex = 0;
        let currentCategory = 'all';

        // --- 1. RUNTIME CALCULATOR (THE "QUANTUM SCAN") ---
        let totalSeconds = 0;
        let videosProcessed = 0;
        
        function updateRuntimeDisplay() {
            const h = Math.floor(totalSeconds / 3600);
            const m = Math.floor((totalSeconds % 3600) / 60);
            const s = Math.floor(totalSeconds % 60);
            
            const pad = (num) => num.toString().padStart(2, '0');
            document.getElementById('totalRuntime').innerText = `${pad(h)}h ${pad(m)}m ${pad(s)}s`;
        }

        // Attach listeners to all grid videos
        const gridVideos = document.querySelectorAll('.grid-video');
        gridVideos.forEach(v => {
            if (v.readyState >= 1) {
                if(!v.dataset.scanned) {
                    totalSeconds += v.duration;
                    v.dataset.scanned = "true";
                    updateRuntimeDisplay();
                }
            } else {
                v.addEventListener('loadedmetadata', () => {
                    if(!v.dataset.scanned && isFinite(v.duration)) {
                        totalSeconds += v.duration;
                        v.dataset.scanned = "true";
                        updateRuntimeDisplay();
                    }
                });
            }
        });

        // --- 2. DEEP LINKING ---
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const watchFile = urlParams.get('watch');

            if(watchFile) {
                const index = LIBRARY.findIndex(v => v.filename === watchFile);
                if(index !== -1) {
                    openCinema(index);
                }
            }
        });

        // --- 3. SHARE SYSTEM ---
        function shareLink(filename) {
            const baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            const shareUrl = baseUrl + "?watch=" + encodeURIComponent(filename);
            
            navigator.clipboard.writeText(shareUrl).then(() => {
                showToast();
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }

        function showToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('active');
            setTimeout(() => {
                toast.classList.remove('active');
            }, 3000);
        }

        document.getElementById('shareCurrentBtn').addEventListener('click', () => {
             shareLink(LIBRARY[currentIndex].filename);
        });

        // --- 4. QR CODE SYSTEM ---
        function generateQRCode(text, size) {
            const canvas = document.getElementById('qrCodeCanvas');
            const ctx = canvas.getContext('2d');
            
            canvas.width = size;
            canvas.height = size;
            
            const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(text)}`;
            
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = function() {
                ctx.drawImage(img, 0, 0, size, size);
            };
            img.onerror = function() {
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, size, size);
                ctx.fillStyle = '#000000';
                ctx.font = '14px monospace';
                ctx.textAlign = 'center';
                ctx.fillText('QR CODE', size/2, size/2 - 10);
                ctx.fillText('GENERATION', size/2, size/2 + 10);
                ctx.fillText('ERROR', size/2, size/2 + 30);
            };
            img.src = qrApiUrl;
        }

        function showQR(filename, title) {
            const baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            const shareUrl = baseUrl + "?watch=" + encodeURIComponent(filename);
            
            const modal = document.getElementById('qrModal');
            const titleEl = document.getElementById('qrTitle');
            const urlEl = document.getElementById('qrUrl');

            titleEl.textContent = title;
            urlEl.textContent = shareUrl;

            generateQRCode(shareUrl, 256);

            modal.classList.add('active');
        }

        function closeQR() {
            document.getElementById('qrModal').classList.remove('active');
        }

        // --- 5. SEARCH & FILTER SYSTEM ---
        function filterSystem() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.holo-card');

            cards.forEach(card => {
                const title = card.getAttribute('data-title');
                const category = card.getAttribute('data-category');
                
                const matchesSearch = title.includes(query);
                const matchesCat = currentCategory === 'all' || category === currentCategory;

                if (matchesSearch && matchesCat) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function setCategory(cat, element) {
            currentCategory = cat;
            document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
            element.classList.add('active');
            filterSystem();
        }

        // --- 6. CINEMA LOGIC ---
        const player = document.getElementById('mainPlayer');
        const layer = document.getElementById('cinemaLayer');

        function openCinema(index) {
            currentIndex = index;
            const video = LIBRARY[index];
            player.src = video.filename;
            document.getElementById('nowPlayingText').innerText = video.cleanName;
            layer.classList.add('active');
            player.play();

            const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?watch=' + encodeURIComponent(video.filename);
            window.history.pushState({path: newUrl}, '', newUrl);
        }

        function closeCinema() {
            layer.classList.remove('active');
            player.pause();
            const baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.pushState({path: baseUrl}, '', baseUrl);
        }

        function nextVideo() {
            if(currentIndex < LIBRARY.length - 1) openCinema(currentIndex + 1);
        }

        function prevVideo() {
            if(currentIndex > 0) openCinema(currentIndex - 1);
        }

        player.addEventListener('ended', nextVideo);

        // --- 7. ORACLE MODE ---
        function oracleMode() {
            const cards = Array.from(document.querySelectorAll('.holo-card')).filter(c => c.style.display !== 'none');
            if(cards.length === 0) return;

            const randomCard = cards[Math.floor(Math.random() * cards.length)];
            const index = randomCard.getAttribute('data-index');
            
            randomCard.style.transition = '0.2s';
            randomCard.style.transform = 'scale(1.1)';
            randomCard.style.boxShadow = '0 0 50px var(--accent)';
            
            setTimeout(() => {
                openCinema(parseInt(index));
                randomCard.style.transform = '';
                randomCard.style.boxShadow = '';
            }, 600);
        }

        function playAll() {
            openCinema(0);
        }

        document.addEventListener('keydown', (e) => {
            if(e.key === 'Escape') {
                closeCinema();
                closeQR();
            }
        });
    </script>
</body>
</html>

<?php
/**
 * 🌌 Quantum Music Library - Ultimate Edition (Flux Updated)
 * Features: Sticky Player, JSON Cache, 3D Card Effects, Audio Visualizer
 */

// --- CONFIGURATION ---
$cacheFile = __DIR__ . '/music_cache.json';
$cacheDuration = 3600; // Cache duration in seconds (1 hour)
$directory = __DIR__;

// --- HELPER FUNCTIONS ---

function getAudioDuration($filepath) {
    if (class_exists('getID3')) {
        $getID3 = new getID3();
        $fileInfo = $getID3->analyze($filepath);
        if (isset($fileInfo['playtime_seconds'])) return round($fileInfo['playtime_seconds']);
    }
    
    $ffprobe = shell_exec("ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($filepath) . " 2>&1");
    if ($ffprobe && is_numeric(trim($ffprobe))) return round(floatval(trim($ffprobe)));
    
    if (pathinfo($filepath, PATHINFO_EXTENSION) === 'mp3') {
        $mp3info = shell_exec("mp3info -p \"%S\" " . escapeshellarg($filepath) . " 2>&1");
        if ($mp3info && is_numeric(trim($mp3info))) return intval(trim($mp3info));
    }
    return null;
}

function formatDuration($seconds) {
    if ($seconds === null) return null;
    $mins = floor($seconds / 60);
    $secs = $seconds % 60;
    return sprintf("%d:%02d", $mins, $secs);
}

function formatTrackTitle($filename) {
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $name = preg_replace('/[\s\-_]+/', ' ', $name);
    $name = ucwords(strtolower(trim($name)));
    // Polish: Force specific words to uppercase
    $replacements = ['Ai' => 'AI', 'Bpm' => 'BPM', 'Id' => 'ID', 'Feat' => 'ft.'];
    return strtr($name, $replacements);
}

// --- MAIN LOGIC & CACHING ---

$musicFiles = [];
$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === 'true';

if (!$forceRefresh && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheDuration)) {
    $musicFiles = json_decode(file_get_contents($cacheFile), true);
} else {
    $supportedFormats = ['mp3', 'flac', 'wav', 'ogg', 'm4a', 'aac', 'wma'];
    $artFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $availableImages = [];

    foreach (scandir($directory) as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, $artFormats)) {
            $basename = pathinfo($file, PATHINFO_FILENAME);
            $availableImages[$basename] = $file;
        }
    }

    $files = scandir($directory);
    $mimeTypes = [
        'mp3' => 'audio/mpeg', 'flac' => 'audio/flac', 'wav' => 'audio/wav',
        'ogg' => 'audio/ogg', 'm4a' => 'audio/mp4', 'aac' => 'audio/aac', 'wma' => 'audio/x-ms-wma'
    ];

    foreach ($files as $file) {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($extension, $supportedFormats)) {
            $basename = pathinfo($file, PATHINFO_FILENAME);
            $albumArt = $availableImages[$basename] ?? null;
            $filepath = $directory . '/' . $file;
            $duration = getAudioDuration($filepath);
            $title = formatTrackTitle($file);
            
            $musicFiles[] = [
                'filename' => $file,
                'title' => $title,
                'extension' => $extension,
                'mime' => $mimeTypes[$extension] ?? 'audio/mpeg',
                'size' => filesize($file),
                'size_mb' => round(filesize($file) / (1024 * 1024), 1),
                'artwork' => $albumArt,
                'duration' => $duration,
                'duration_formatted' => formatDuration($duration),
                'search_string' => strtolower($title . ' ' . $file)
            ];
        }
    }

    usort($musicFiles, function($a, $b) {
        return strcmp($a['title'], $b['title']);
    });

    file_put_contents($cacheFile, json_encode($musicFiles));
}

$totalFiles = count($musicFiles);
$totalSize = array_sum(array_column($musicFiles, 'size'));
$totalSizeGB = round($totalSize / (1024 * 1024 * 1024), 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script defer src="https://analytics.quantummindsunited.com/script.js" data-website-id="b26d8edf-4746-4a0f-9b6a-b3019df2388e"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌌 Quantum Music Library</title>
    <style>
        /* CSS VARIABLES & RESET */
        :root {
            --primary: #4a9eff;
            --secondary: #a78bfa;
            --bg-dark: #0a0f1c;
            --glass-bg: rgba(10, 15, 28, 0.6);
            --glass-border: rgba(74, 158, 255, 0.3);
            --text-main: #fff;
            --text-dim: rgba(255, 255, 255, 0.6);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            background: #000;
            color: var(--text-main);
            overflow-x: hidden;
            min-height: 100vh;
            padding-bottom: 120px;
        }
        
        /* COSMIC BACKGROUND */
        .cosmic-bg { position: fixed; inset: 0; background: radial-gradient(circle at 50% 50%, var(--bg-dark) 0%, #000 100%); z-index: -3; }
        .stars { position: fixed; inset: 0; pointer-events: none; z-index: -2; }
        .star { position: absolute; width: 2px; height: 2px; background: white; border-radius: 50%; animation: twinkle 3s ease-in-out infinite; }
        #particles { position: fixed; inset: 0; pointer-events: none; z-index: -1; }
        @keyframes twinkle { 0%, 100% { opacity: 0.3; } 50% { opacity: 1; transform: scale(1.5); } }

        /* LAYOUT */
        .container { max-width: 1400px; margin: 0 auto; padding: 40px 20px; position: relative; z-index: 10; }
        
        /* HEADER */
        .header {
            text-align: center; margin-bottom: 40px; padding: 40px 30px;
            background: var(--glass-bg); border: 1px solid var(--glass-border);
            border-radius: 30px; backdrop-filter: blur(20px);
            box-shadow: 0 0 60px rgba(74, 158, 255, 0.1);
        }
        .header h1 {
            font-size: 3.5rem; font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        .breadcrumb a { color: var(--primary); text-decoration: none; margin-right: 10px; }
        .refresh-link { font-size: 0.8rem; color: var(--text-dim); text-decoration: none; margin-top: 10px; display: inline-block; opacity: 0.5; transition: opacity 0.3s; }
        .refresh-link:hover { opacity: 1; color: var(--primary); }

        /* CONTROLS */
        .controls-section { display: grid; grid-template-columns: 1fr auto; gap: 15px; margin-bottom: 30px; }
        .search-box { position: relative; }
        .search-input {
            width: 100%; background: rgba(74, 158, 255, 0.1);
            border: 1px solid var(--glass-border); border-radius: 12px;
            padding: 15px 20px; color: #fff; font-size: 16px; outline: none;
            transition: all 0.3s;
        }
        .search-input:focus { border-color: var(--primary); background: rgba(74, 158, 255, 0.2); box-shadow: 0 0 20px rgba(74, 158, 255, 0.2); }
        
        .controls-bar { display: flex; gap: 10px; flex-wrap: wrap; }
        .control-btn {
            background: rgba(74, 158, 255, 0.2); border: 1px solid rgba(74, 158, 255, 0.4);
            color: white; padding: 12px 20px; border-radius: 12px; cursor: pointer;
            font-weight: 600; transition: all 0.3s;
        }
        .control-btn:hover { background: rgba(74, 158, 255, 0.3); transform: translateY(-2px); }

        /* --- UPDATED GRID & 3D EFFECTS --- */
        .music-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 25px; 
            perspective: 1000px; /* Essential for 3D pop */
        }
        
        .music-item {
            background: var(--glass-bg); 
            border: 1px solid var(--glass-border);
            border-radius: 20px; 
            overflow: hidden; 
            backdrop-filter: blur(10px);
            cursor: pointer; 
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            transform-style: preserve-3d;
        }

        .music-item:hover { 
            transform: translateY(-5px) scale(1.02); 
            border-color: var(--primary); 
            box-shadow: 0 0 30px rgba(74, 158, 255, 0.3); 
        }

        /* ACTIVE "POP" STATE */
        .music-item.playing {
            transform: scale(1.05) translateY(-10px) translateZ(20px);
            z-index: 100;
            border: double 2px transparent;
            background-image: linear-gradient(var(--glass-bg), var(--glass-bg)), linear-gradient(135deg, var(--primary), #fff, var(--secondary));
            background-origin: border-box;
            background-clip: padding-box, border-box;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 30px rgba(74, 158, 255, 0.4);
        }

        /* DIM OTHERS ON HOVER (FOCUS EFFECT) */
        .music-grid:hover .music-item:not(:hover):not(.playing) {
            opacity: 0.7;
            filter: grayscale(0.6);
            transform: scale(0.98);
        }
        
        /* ARTWORK & VISUALIZER */
        .artwork-container { 
            aspect-ratio: 1/1; 
            position: relative; 
            overflow: hidden; 
            background: rgba(74,158,255,0.1); 
        }
        
        .artwork-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
        .music-item:hover .artwork-img { transform: scale(1.1); }
        .no-artwork { display: flex; align-items: center; justify-content: center; height: 100%; font-size: 3rem; color: var(--text-dim); }
        
        /* EQ BARS ANIMATION */
        .music-item.playing .artwork-container::after {
            content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 50%); z-index: 2;
        }
        
        .music-item.playing .artwork-container::before {
            content: ''; position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%);
            width: 60%; height: 40px; z-index: 3; display: flex; align-items: flex-end;
            background-image: 
                linear-gradient(to top, var(--primary), transparent),
                linear-gradient(to top, var(--secondary), transparent),
                linear-gradient(to top, var(--primary), transparent),
                linear-gradient(to top, var(--secondary), transparent),
                linear-gradient(to top, var(--primary), transparent);
            background-size: 15% 100%;
            background-position: 0% 100%, 25% 100%, 50% 100%, 75% 100%, 100% 100%;
            background-repeat: no-repeat;
            animation: equalizer 1s infinite alternate;
            opacity: 0.9;
        }

        @keyframes equalizer {
            0% { background-size: 15% 10%, 15% 30%, 15% 60%, 15% 30%, 15% 10%; }
            50% { background-size: 15% 50%, 15% 20%, 15% 90%, 15% 50%, 15% 40%; }
            100% { background-size: 15% 90%, 15% 60%, 15% 30%, 15% 80%, 15% 50%; }
        }
        
        .track-info { padding: 20px; }
        .track-title { font-weight: 700; font-size: 1.1rem; margin-bottom: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .track-meta { display: flex; gap: 10px; font-size: 0.8rem; color: var(--text-dim); }
        
        .playing-badge { 
            position: absolute; top: 10px; right: 10px; 
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 5px 10px; border-radius: 10px; font-size: 0.7rem; font-weight: bold;
            display: none; box-shadow: 0 0 10px rgba(0,0,0,0.5); z-index: 5;
        }
        .music-item.playing .playing-badge { display: block; animation: pulse 2s infinite; }

        /* FIXED PLAYER BAR */
        .mini-player {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: rgba(10, 15, 28, 0.95); border-top: 1px solid var(--glass-border);
            padding: 15px 20px; backdrop-filter: blur(20px); z-index: 1000;
            box-shadow: 0 -10px 40px rgba(0,0,0,0.5);
            transform: translateY(0);
        }
        
        .player-content { max-width: 1400px; margin: 0 auto; display: flex; align-items: center; gap: 20px; justify-content: space-between; }
        
        .p-info { display: flex; align-items: center; gap: 15px; width: 30%; }
        .p-art { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; background: #222; }
        .p-text { overflow: hidden; }
        .p-title { font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .p-sub { font-size: 0.8rem; color: var(--text-dim); }
        
        .p-controls { display: flex; align-items: center; gap: 20px; }
        .p-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-dim); transition: 0.2s; }
        .p-btn:hover { color: #fff; transform: scale(1.1); }
        .p-btn.main { font-size: 2.5rem; color: #fff; }
        
        .p-progress-container { flex: 1; max-width: 600px; display: flex; align-items: center; gap: 10px; font-size: 0.8rem; color: var(--text-dim); }
        .progress-bar-bg { flex: 1; height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; cursor: pointer; position: relative; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, var(--primary), var(--secondary)); width: 0%; border-radius: 2px; position: relative; }
        .progress-fill::after { content: ''; position: absolute; right: -4px; top: -3px; width: 10px; height: 10px; background: #fff; border-radius: 50%; box-shadow: 0 0 10px var(--primary); opacity: 0; transition: opacity 0.2s; }
        .progress-bar-bg:hover .progress-fill::after { opacity: 1; }

        @keyframes pulse { 0% { opacity: 0.8; } 50% { opacity: 1; transform: scale(1.05); } 100% { opacity: 0.8; } }
        
        @media (max-width: 768px) {
            .header h1 { font-size: 2.2rem; }
            .player-content { flex-direction: column; gap: 10px; padding-bottom: 10px; }
            .p-info { width: 100%; }
            .p-progress-container { width: 100%; order: -1; }
        }
        
        .toast {
            position: fixed; top: 20px; right: 20px; background: rgba(10,15,28,0.9);
            border: 1px solid var(--primary); padding: 15px 25px; border-radius: 10px;
            color: #fff; transform: translateX(150%); transition: 0.3s; z-index: 2000;
        }
        .toast.show { transform: translateX(0); }
    </style>
</head>
<body>
    <div class="cosmic-bg"></div>
    <div class="stars" id="stars"></div>
    <canvas id="particles"></canvas>
    
    <div class="container">
        <div class="header">
            <div class="breadcrumb">
                <a href="/">🏠 Quantum Minds United</a>
                <span style="opacity:0.5">→</span>
                <span style="opacity:0.8">Music Library</span>
            </div>
            <h1>🌌 Quantum Music Library</h1>
            <p style="opacity: 0.7; letter-spacing: 2px;">YOUR PERSONAL SONIC UNIVERSE</p>
            <a href="?refresh=true" class="refresh-link">🔄 Refresh Library Cache</a>
        </div>

        <div class="controls-section">
            <div class="search-box">
                <input type="text" class="search-input" id="searchInput" placeholder="🔍 Search <?php echo $totalFiles; ?> tracks..." autocomplete="off">
            </div>
            <div class="controls-bar">
                <button class="control-btn" onclick="playAll()">▶️ Play All</button>
                <button class="control-btn" onclick="shufflePlay()">🔀 Shuffle</button>
                <div style="margin-left:auto; display:flex; align-items:center; gap:10px; color:rgba(255,255,255,0.6); font-size:0.9rem;">
                    <span>💾 <?php echo $totalSizeGB; ?> GB</span>
                </div>
            </div>
        </div>

        <div class="music-grid" id="musicGrid">
            </div>
    </div>

    <div class="mini-player">
        <div class="player-content">
            <div class="p-info">
                <img id="pArt" src="" class="p-art" style="display:none">
                <div class="p-text">
                    <div class="p-title" id="pTitle">Select a track</div>
                    <div class="p-sub" id="pSub">Ready to explore cosmos</div>
                </div>
            </div>
            <div class="p-controls">
                <button class="p-btn" onclick="prevTrack()">⏮️</button>
                <button class="p-btn main" id="playPauseBtn" onclick="togglePlayPause()">▶️</button>
                <button class="p-btn" onclick="nextTrack()">⏭️</button>
            </div>
            <div class="p-progress-container">
                <span id="currTime">0:00</span>
                <div class="progress-bar-bg" id="progressBar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <span id="durTime">0:00</span>
            </div>
        </div>
        <audio id="audioPlayer" preload="auto"></audio>
    </div>

    <script id="music-data" type="application/json">
        <?php echo json_encode($musicFiles); ?>
    </script>

    <script>
        // --- DATA INITIALIZATION ---
        let tracks = [];
        try {
            tracks = JSON.parse(document.getElementById('music-data').textContent);
        } catch(e) { console.error("Failed to load tracks", e); }
        
        let currentTrackIndex = -1;
        let queue = [];
        const audio = document.getElementById('audioPlayer');
        const grid = document.getElementById('musicGrid');

        // --- RENDER GRID ---
        function renderGrid(filterText = '') {
            grid.innerHTML = '';
            filterText = filterText.toLowerCase();

            tracks.forEach((track, index) => {
                if (filterText && !track.search_string.includes(filterText)) return;

                const card = document.createElement('div');
                card.className = `music-item ${currentTrackIndex === index ? 'playing' : ''}`;
                card.onclick = () => playTrack(index);
                
                const artHtml = track.artwork 
                    ? `<img src="${track.artwork}" class="artwork-img" loading="lazy" alt="Art">` 
                    : `<div class="no-artwork">🎵</div>`;

                card.innerHTML = `
                    <span class="playing-badge">PLAYING</span>
                    <div class="artwork-container">${artHtml}</div>
                    <div class="track-info">
                        <div class="track-title">${track.title}</div>
                        <div class="track-meta">
                            <span>${track.duration_formatted || ''}</span>
                            <span>• ${track.extension.toUpperCase()}</span>
                            <span>• ${track.size_mb} MB</span>
                        </div>
                    </div>
                `;
                grid.appendChild(card);
            });
        }

        // --- PLAYER LOGIC ---
        function playTrack(index) {
            if (index < 0 || index >= tracks.length) return;
            
            currentTrackIndex = index;
            const track = tracks[index];

            audio.src = track.filename;
            audio.play().catch(e => console.log("Auto-play prevented", e));

            updatePlayerUI(track);
            renderGrid(document.getElementById('searchInput').value); // Update visual state
            
            // SMOOTH SCROLL TO STAGE
            setTimeout(() => {
                const activeCard = document.querySelector('.music-item.playing');
                if(activeCard) {
                    activeCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 100);
        }

        function updatePlayerUI(track) {
            document.getElementById('pTitle').textContent = track.title;
            document.getElementById('pSub').textContent = `Now Playing • ${track.extension.toUpperCase()}`;
            
            const artImg = document.getElementById('pArt');
            if (track.artwork) {
                artImg.src = track.artwork;
                artImg.style.display = 'block';
                if ('mediaSession' in navigator) {
                    navigator.mediaSession.metadata = new MediaMetadata({
                        title: track.title,
                        artist: 'Quantum Library',
                        artwork: [{ src: track.artwork, sizes: '512x512', type: 'image/jpeg' }]
                    });
                }
            } else {
                artImg.style.display = 'none';
            }
            document.getElementById('playPauseBtn').textContent = '⏸️';
        }

        function togglePlayPause() {
            if (audio.paused && audio.src) {
                audio.play();
                document.getElementById('playPauseBtn').textContent = '⏸️';
            } else if (audio.src) {
                audio.pause();
                document.getElementById('playPauseBtn').textContent = '▶️';
            } else if (tracks.length > 0) {
                playTrack(0);
            }
        }

        function nextTrack() {
            let nextIndex = currentTrackIndex + 1;
            if (nextIndex >= tracks.length) nextIndex = 0;
            playTrack(nextIndex);
        }

        function prevTrack() {
            let prevIndex = currentTrackIndex - 1;
            if (prevIndex < 0) prevIndex = tracks.length - 1;
            playTrack(prevIndex);
        }

        function playAll() { playTrack(0); showToast("Playing all tracks"); }

        function shufflePlay() {
            const randomIndex = Math.floor(Math.random() * tracks.length);
            playTrack(randomIndex);
            showToast("Shuffling cosmos...");
        }

        // --- EVENTS ---
        audio.addEventListener('timeupdate', () => {
            if (!isNaN(audio.duration)) {
                const percent = (audio.currentTime / audio.duration) * 100;
                document.getElementById('progressFill').style.width = percent + '%';
                document.getElementById('currTime').textContent = formatTime(audio.currentTime);
                document.getElementById('durTime').textContent = formatTime(audio.duration);
            }
        });

        audio.addEventListener('ended', nextTrack);

        document.getElementById('progressBar').addEventListener('click', (e) => {
            if (!audio.src) return;
            const width = e.currentTarget.clientWidth;
            const clickX = e.offsetX;
            const duration = audio.duration;
            audio.currentTime = (clickX / width) * duration;
        });

        document.getElementById('searchInput').addEventListener('input', (e) => {
            renderGrid(e.target.value);
        });

        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT') return;
            if (e.code === 'Space') { e.preventDefault(); togglePlayPause(); }
            if (e.code === 'ArrowRight') { if(e.ctrlKey) nextTrack(); else audio.currentTime += 5; }
            if (e.code === 'ArrowLeft') { if(e.ctrlKey) prevTrack(); else audio.currentTime -= 5; }
        });

        // --- UTILS ---
        function formatTime(s) {
            const m = Math.floor(s / 60);
            const sec = Math.floor(s % 60);
            return `${m}:${sec.toString().padStart(2, '0')}`;
        }

        function showToast(msg) {
            const t = document.createElement('div');
            t.className = 'toast show';
            t.textContent = msg;
            document.body.appendChild(t);
            setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3000);
        }

        // --- PARTICLES ---
        const canvas = document.getElementById('particles');
        const ctx = canvas.getContext('2d');
        let particles = [];
        
        function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
        window.addEventListener('resize', resize);
        resize();

        class Particle {
            constructor() { this.reset(); }
            reset() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.vx = (Math.random() - 0.5) * 0.5;
                this.vy = (Math.random() - 0.5) * 0.5;
                this.size = Math.random() * 2;
                this.alpha = Math.random() * 0.5;
            }
            update() {
                this.x += this.vx; this.y += this.vy;
                if (this.x < 0 || this.x > canvas.width) this.vx *= -1;
                if (this.y < 0 || this.y > canvas.height) this.vy *= -1;
            }
            draw() {
                ctx.fillStyle = `rgba(74, 158, 255, ${this.alpha})`;
                ctx.beginPath(); ctx.arc(this.x, this.y, this.size, 0, Math.PI*2); ctx.fill();
            }
        }

        const starContainer = document.getElementById('stars');
        for(let i=0; i<100; i++) {
            const s = document.createElement('div');
            s.className = 'star';
            s.style.left = Math.random()*100+'%'; s.style.top = Math.random()*100+'%';
            s.style.animationDelay = Math.random()*3+'s';
            starContainer.appendChild(s);
        }

        for(let i=0; i<50; i++) particles.push(new Particle());
        
        function animate() {
            ctx.clearRect(0,0,canvas.width,canvas.height);
            particles.forEach(p => { p.update(); p.draw(); });
            particles.forEach((p1, i) => {
                particles.slice(i+1).forEach(p2 => {
                    const d = Math.hypot(p1.x-p2.x, p1.y-p2.y);
                    if(d<150) {
                        ctx.strokeStyle = `rgba(74,158,255,${0.1 * (1 - d/150)})`;
                        ctx.beginPath(); ctx.moveTo(p1.x,p1.y); ctx.lineTo(p2.x,p2.y); ctx.stroke();
                    }
                });
            });
            requestAnimationFrame(animate);
        }
        animate();

        renderGrid();
    </script>
</body>
</html>


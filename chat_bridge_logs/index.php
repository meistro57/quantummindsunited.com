<?php
// Chat Bridge Archives - Auto-indexing version
// Scans .md files and maintains a lightweight logging.md index

$directory = __DIR__;
$loggingFile = $directory . '/logging.md';
$transcripts = [];

// Function to parse a transcript file
function parseTranscript($content, $filename) {
    $data = [
        'filename' => $filename,
        'sessionId' => '',
        'started' => '',
        'starter' => '',
        'agentA' => ['provider' => '', 'model' => '', 'persona' => '', 'temp' => 0.7],
        'agentB' => ['provider' => '', 'model' => '', 'persona' => '', 'temp' => 0.7],
        'maxRounds' => '',
        'memoryRounds' => '',
        'messageCount' => 0,
        'size' => strlen($content)
    ];
    
    $lines = explode("\n", $content);
    
    foreach ($lines as $line) {
        if (strpos($line, '**Session ID:**') !== false) {
            $data['sessionId'] = trim(explode('**Session ID:**', $line)[1]);
        } elseif (strpos($line, '**Started:**') !== false) {
            $data['started'] = trim(explode('**Started:**', $line)[1]);
        } elseif (strpos($line, '**Conversation Starter:**') !== false) {
            $data['starter'] = trim(explode('**Conversation Starter:**', $line)[1]);
        } elseif (strpos($line, '**Agent A Provider:**') !== false) {
            $data['agentA']['provider'] = trim(explode('**Agent A Provider:**', $line)[1]);
        } elseif (strpos($line, '**Agent A Model:**') !== false) {
            $data['agentA']['model'] = trim(explode('**Agent A Model:**', $line)[1]);
        } elseif (strpos($line, '**Agent A Persona:**') !== false) {
            $data['agentA']['persona'] = trim(explode('**Agent A Persona:**', $line)[1]);
        } elseif (strpos($line, '**Agent A Temperature:**') !== false) {
            $data['agentA']['temp'] = floatval(trim(explode('**Agent A Temperature:**', $line)[1]));
        } elseif (strpos($line, '**Agent B Provider:**') !== false) {
            $data['agentB']['provider'] = trim(explode('**Agent B Provider:**', $line)[1]);
        } elseif (strpos($line, '**Agent B Model:**') !== false) {
            $data['agentB']['model'] = trim(explode('**Agent B Model:**', $line)[1]);
        } elseif (strpos($line, '**Agent B Persona:**') !== false) {
            $data['agentB']['persona'] = trim(explode('**Agent B Persona:**', $line)[1]);
        } elseif (strpos($line, '**Agent B Temperature:**') !== false) {
            $data['agentB']['temp'] = floatval(trim(explode('**Agent B Temperature:**', $line)[1]));
        } elseif (strpos($line, '**Max Rounds:**') !== false) {
            $data['maxRounds'] = trim(explode('**Max Rounds:**', $line)[1]);
        } elseif (strpos($line, '**Memory Rounds:**') !== false) {
            $data['memoryRounds'] = trim(explode('**Memory Rounds:**', $line)[1]);
        } elseif (preg_match('/^### (Human|Agent A|Agent B) \(/', $line)) {
            $data['messageCount']++;
        }
    }
    
    return $data;
}

// Load existing index from logging.md
$indexedFiles = [];
if (file_exists($loggingFile)) {
    $loggingContent = file_get_contents($loggingFile);
    $loggingLines = explode("\n", $loggingContent);
    
    foreach ($loggingLines as $line) {
        if (preg_match('/^\| `([^`]+)` \|/', $line, $matches)) {
            $indexedFiles[] = $matches[1];
        }
    }
}

// Scan directory for .md files
$newEntries = [];
if ($handle = opendir($directory)) {
    while (false !== ($file = readdir($handle))) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'md' && 
            $file !== 'index.md' && 
            $file !== 'logging.md' &&
            !in_array($file, $indexedFiles)) {
            
            $filePath = $directory . '/' . $file;
            $content = file_get_contents($filePath);
            $parsed = parseTranscript($content, $file);
            
            // Add to new entries for appending to logging.md
            $newEntries[] = $parsed;
        }
    }
    closedir($handle);
}

// Append new entries to logging.md
if (!empty($newEntries)) {
    $needsHeader = !file_exists($loggingFile);
    $fp = fopen($loggingFile, 'a');
    
    if ($needsHeader) {
        fwrite($fp, "# Chat Bridge Archive Index\n\n");
        fwrite($fp, "| Filename | Session ID | Started | Topic | Agent A | Agent B | Messages | Size |\n");
        fwrite($fp, "|----------|------------|---------|-------|---------|---------|----------|------|\n");
    }
    
    foreach ($newEntries as $entry) {
        $line = sprintf(
            "| `%s` | %s | %s | %s | %s (%s) | %s (%s) | %d | %s |\n",
            $entry['filename'],
            $entry['sessionId'],
            $entry['started'],
            substr($entry['starter'], 0, 50) . (strlen($entry['starter']) > 50 ? '...' : ''),
            $entry['agentA']['persona'],
            $entry['agentA']['provider'],
            $entry['agentB']['persona'],
            $entry['agentB']['provider'],
            $entry['messageCount'],
            formatSize($entry['size'])
        );
        fwrite($fp, $line);
    }
    
    fclose($fp);
}

// Now load ALL entries from logging.md for display
if (file_exists($loggingFile)) {
    $loggingContent = file_get_contents($loggingFile);
    $loggingLines = explode("\n", $loggingContent);
    
    foreach ($loggingLines as $line) {
        if (preg_match('/^\| `([^`]+)` \| ([^|]+) \| ([^|]+) \| ([^|]+) \| ([^(]+)\(([^)]+)\) \| ([^(]+)\(([^)]+)\) \| (\d+) \| ([^|]+) \|/', $line, $matches)) {
            $transcripts[] = [
                'filename' => trim($matches[1]),
                'sessionId' => trim($matches[2]),
                'started' => trim($matches[3]),
                'starter' => trim($matches[4]),
                'agentA' => [
                    'persona' => trim($matches[5]),
                    'provider' => trim($matches[6])
                ],
                'agentB' => [
                    'persona' => trim($matches[7]),
                    'provider' => trim($matches[8])
                ],
                'messageCount' => intval($matches[9]),
                'size' => trim($matches[10])
            ];
        }
    }
}

// Sort by started date (newest first)
usort($transcripts, function($a, $b) {
    return strtotime($b['started']) - strtotime($a['started']);
});

$totalFiles = count($transcripts);
$totalMessages = array_sum(array_column($transcripts, 'messageCount'));

function formatSize($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1024 * 1024) return round($bytes / 1024, 2) . ' KB';
    return round($bytes / (1024 * 1024), 2) . ' MB';
}

function formatDate($dateString) {
    $timestamp = strtotime($dateString);
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return $months[date('n', $timestamp) - 1] . ' ' . date('j', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌉 Chat Bridge Archives</title>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1a1f35;
            min-height: 100vh;
            padding: 2rem;
            color: #e2e8f0;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: rgba(15, 23, 42, 0.6);
            border-radius: 15px;
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
        
        .header h1 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header p {
            color: #94a3b8;
            font-size: 1.2rem;
        }
        
        .stats-bar {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            gap: 2rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .stat {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            color: #cbd5e1;
        }
        
        .stat-value {
            font-weight: bold;
            color: #60a5fa;
        }
        
        .process-btn {
            margin-left: auto;
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(96, 165, 250, 0.3);
        }
        
        .process-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(96, 165, 250, 0.4);
        }
        
        .process-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .process-status {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: rgba(15, 23, 42, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 10px;
            padding: 1rem 1.5rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
            max-width: 400px;
        }
        
        .process-status.show {
            display: block;
            animation: slideIn 0.3s ease;
        }
        
        .process-status.success {
            border-left: 4px solid #4ade80;
        }
        
        .process-status.error {
            border-left: 4px solid #ef4444;
        }
        
        .process-status.processing {
            border-left: 4px solid #60a5fa;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .search-box {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .search-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #0f172a;
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 10px;
            font-size: 1rem;
            color: #e2e8f0;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #60a5fa;
        }
        
        .search-input::placeholder {
            color: #64748b;
        }
        
        .library-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        
        .card {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(96, 165, 250, 0.2);
            border-color: rgba(96, 165, 250, 0.3);
        }
        
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 1rem;
            line-height: 1.4;
        }
        
        .agents-section {
            display: flex;
            gap: 0.5rem;
            margin: 1rem 0;
            flex-wrap: wrap;
        }
        
        .agent {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #0f172a;
            border: 1px solid rgba(148, 163, 184, 0.1);
            padding: 0.4rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .agent-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        
        .agent-dot.cyan {
            background: #22d3ee;
            box-shadow: 0 0 8px rgba(34, 211, 238, 0.5);
        }
        
        .agent-dot.green {
            background: #4ade80;
            box-shadow: 0 0 8px rgba(74, 222, 128, 0.5);
        }
        
        .agent-name {
            font-weight: 600;
            color: #cbd5e1;
        }
        
        .agent-provider {
            color: #64748b;
            font-size: 0.8rem;
        }
        
        .card-meta {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(148, 163, 184, 0.1);
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
        }
        
        .meta-icon {
            font-size: 1.2rem;
            opacity: 0.7;
        }
        
        .meta-value {
            font-weight: bold;
            color: #60a5fa;
            font-size: 0.9rem;
        }
        
        .meta-label {
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.5px;
        }
        
        .empty-state {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 15px;
            padding: 4rem 2rem;
            text-align: center;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-state h2 {
            color: #e2e8f0;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            color: #94a3b8;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            animation: fadeIn 0.3s ease;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: #1a1f35;
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 15px;
            width: 100%;
            max-width: 1200px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: zoomIn 0.3s ease;
        }
        
        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(15, 23, 42, 0.8);
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #60a5fa;
        }
        
        .modal-close {
            background: rgba(148, 163, 184, 0.1);
            border: 1px solid rgba(148, 163, 184, 0.2);
            color: #cbd5e1;
            border-radius: 8px;
            width: 36px;
            height: 36px;
            font-size: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .modal-close:hover {
            background: rgba(148, 163, 184, 0.2);
            border-color: #60a5fa;
        }
        
        .modal-body {
            padding: 2rem;
            overflow-y: auto;
            flex: 1;
        }
        
        .modal-body::-webkit-scrollbar {
            width: 8px;
        }
        
        .modal-body::-webkit-scrollbar-track {
            background: #0f172a;
        }
        
        .modal-body::-webkit-scrollbar-thumb {
            background: #60a5fa;
            border-radius: 4px;
        }
        
        .markdown-content {
            line-height: 1.7;
            color: #cbd5e1;
        }
        
        .markdown-content h1,
        .markdown-content h2,
        .markdown-content h3 {
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            color: #60a5fa;
        }
        
        .markdown-content h1 {
            font-size: 2rem;
            border-bottom: 2px solid rgba(96, 165, 250, 0.3);
            padding-bottom: 0.5rem;
        }
        
        .markdown-content h2 {
            font-size: 1.5rem;
        }
        
        .markdown-content h3 {
            font-size: 1.2rem;
        }
        
        .markdown-content p {
            margin-bottom: 1rem;
        }
        
        .markdown-content strong {
            color: #60a5fa;
            font-weight: 600;
        }
        
        .markdown-content ul,
        .markdown-content ol {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .markdown-content code {
            background: #0f172a;
            color: #22d3ee;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
        
        .markdown-content pre {
            background: #0f172a;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            margin-bottom: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
        
        .markdown-content pre code {
            border: none;
            padding: 0;
        }
        
        .markdown-content blockquote {
            border-left: 4px solid #60a5fa;
            padding-left: 1rem;
            margin: 1rem 0;
            color: #94a3b8;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes zoomIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .library-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .modal {
                padding: 1rem;
            }
            
            .modal-body {
                padding: 1rem;
            }
            
            .card-meta {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌉 Chat Bridge Archives</h1>
            <p>AI Conversation Library</p>
        </div>

        <?php if (empty($transcripts)): ?>
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h2>No Conversations Yet</h2>
                <p>Upload .md transcript files to this directory to browse your AI conversation archives</p>
            </div>
        <?php else: ?>
            <div class="stats-bar">
                <div class="stat">
                    <span>📄</span>
                    <span><span class="stat-value"><?php echo $totalFiles; ?></span> Conversations</span>
                </div>
                <div class="stat">
                    <span>💬</span>
                    <span><span class="stat-value"><?php echo $totalMessages; ?></span> Messages</span>
                </div>
                <?php if (!empty($newEntries)): ?>
                <div class="stat">
                    <span>✨</span>
                    <span><span class="stat-value"><?php echo count($newEntries); ?></span> New Indexed</span>
                </div>
                <?php endif; ?>
                <button id="processBtn" class="process-btn" onclick="processToDatabase()">
                    <span id="processBtnText">📊 Process to Database</span>
                </button>
            </div>

            <div class="search-box">
                <input
                    type="text"
                    id="searchInput"
                    class="search-input"
                    placeholder="🔍 Search conversations..."
                />
            </div>

            <div class="library-grid" id="libraryGrid">
                <?php foreach ($transcripts as $index => $transcript): ?>
                    <div 
                        class="card"
                        data-filename="<?php echo htmlspecialchars($transcript['filename']); ?>"
                        data-title="<?php echo strtolower($transcript['starter']); ?>"
                        data-agent-a="<?php echo strtolower($transcript['agentA']['persona']); ?>"
                        data-agent-b="<?php echo strtolower($transcript['agentB']['persona']); ?>"
                        onclick="openModal('<?php echo htmlspecialchars($transcript['filename']); ?>', '<?php echo htmlspecialchars(addslashes($transcript['starter'])); ?>')"
                    >
                        <div class="card-title">
                            <?php echo htmlspecialchars($transcript['starter']); ?>
                        </div>

                        <div class="agents-section">
                            <div class="agent">
                                <span class="agent-dot cyan"></span>
                                <span class="agent-name"><?php echo htmlspecialchars($transcript['agentA']['persona']); ?></span>
                                <span class="agent-provider">(<?php echo htmlspecialchars($transcript['agentA']['provider']); ?>)</span>
                            </div>
                            <div class="agent">
                                <span class="agent-dot green"></span>
                                <span class="agent-name"><?php echo htmlspecialchars($transcript['agentB']['persona']); ?></span>
                                <span class="agent-provider">(<?php echo htmlspecialchars($transcript['agentB']['provider']); ?>)</span>
                            </div>
                        </div>

                        <div class="card-meta">
                            <div class="meta-item">
                                <div class="meta-icon">📅</div>
                                <div class="meta-value"><?php echo formatDate($transcript['started']); ?></div>
                                <div class="meta-label">Date</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-icon">💬</div>
                                <div class="meta-value"><?php echo $transcript['messageCount']; ?></div>
                                <div class="meta-label">Messages</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-icon">💾</div>
                                <div class="meta-value"><?php echo $transcript['size']; ?></div>
                                <div class="meta-label">Size</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal" onclick="closeModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <div class="modal-title" id="modalTitle"></div>
                <button class="modal-close" onclick="closeModal()">×</button>
            </div>
            <div class="modal-body">
                <div id="modalContent" class="markdown-content">Loading...</div>
            </div>
        </div>
    </div>

    <!-- Process Status Toast -->
    <div id="processStatus" class="process-status"></div>

    <script>
        function processToDatabase() {
            const btn = document.getElementById('processBtn');
            const btnText = document.getElementById('processBtnText');
            const status = document.getElementById('processStatus');
            
            // Disable button
            btn.disabled = true;
            btnText.textContent = '⏳ Processing...';
            
            // Show processing status
            showStatus('Processing logs to database...', 'processing');
            
            // Call the processing endpoint
            fetch('process_trigger.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showStatus('✅ Successfully processed to database!', 'success');
                        btnText.textContent = '✅ Processed';
                        
                        setTimeout(() => {
                            btnText.textContent = '📊 Process to Database';
                            btn.disabled = false;
                        }, 3000);
                    } else {
                        showStatus('❌ Error: ' + data.error, 'error');
                        btnText.textContent = '📊 Process to Database';
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    showStatus('❌ Error: ' + error.message, 'error');
                    btnText.textContent = '📊 Process to Database';
                    btn.disabled = false;
                });
        }
        
        function showStatus(message, type) {
            const status = document.getElementById('processStatus');
            status.textContent = message;
            status.className = 'process-status show ' + type;
            
            setTimeout(() => {
                status.classList.remove('show');
            }, 5000);
        }
        function openModal(filename, title) {
            const modal = document.getElementById('modal');
            const modalTitle = document.getElementById('modalTitle');
            const modalContent = document.getElementById('modalContent');
            
            modalTitle.textContent = title;
            modalContent.innerHTML = 'Loading...';
            
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Fetch the actual file content
            fetch(filename)
                .then(response => response.text())
                .then(content => {
                    modalContent.innerHTML = marked.parse(content);
                })
                .catch(error => {
                    modalContent.innerHTML = '<p style="color: #ef4444;">Error loading transcript</p>';
                });
        }
        
        function closeModal(event) {
            if (event && event.target.closest('.modal-content')) {
                return;
            }
            
            const modal = document.getElementById('modal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        document.getElementById('searchInput')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.card');
            
            cards.forEach(card => {
                const title = card.dataset.title || '';
                const agentA = card.dataset.agentA || '';
                const agentB = card.dataset.agentB || '';
                
                if (title.includes(searchTerm) || agentA.includes(searchTerm) || agentB.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Load database configuration from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return [];
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, '"\'');
        $env[$key] = $value;
    }
    
    return $env;
}

$env = loadEnv(__DIR__ . '/.envp');

$db_host = $env['DB_HOST'] ?? 'localhost';
$db_user = $env['DB_USER'] ?? 'root';
$db_pass = $env['DB_PASSWORD'] ?? '';
$db_name = $env['DB_NAME'] ?? 'logger_db';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
        $action = $_GET['action'];
        
        // Get list of tables
        if ($action === 'tables') {
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode(['tables' => $tables]);
            exit;
        }
        
        // Get table structure
        if ($action === 'describe' && isset($_GET['table'])) {
            $table = $_GET['table'];
            $stmt = $pdo->query("DESCRIBE `$table`");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['columns' => $columns]);
            exit;
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $query = $input['query'] ?? '';
        
        if (empty($query)) {
            throw new Exception('Query is required');
        }
        
        $startTime = microtime(true);
        $stmt = $pdo->query($query);
        $executionTime = round((microtime(true) - $startTime) * 1000, 2) . 'ms';
        
        // Check if it's a SELECT query
        if (stripos(trim($query), 'SELECT') === 0 || stripos(trim($query), 'SHOW') === 0 || stripos(trim($query), 'DESCRIBE') === 0) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $results = ['affected_rows' => $stmt->rowCount()];
        }
        
        echo json_encode([
            'success' => true,
            'results' => $results,
            'executionTime' => $executionTime
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>

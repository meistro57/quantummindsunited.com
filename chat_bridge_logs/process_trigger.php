<?php
// process_trigger.php - Triggers Python script to process logs to database

header('Content-Type: application/json');

// Path to your Python script
$pythonPath = '/usr/bin/python3';  // Adjust if needed
$scriptPath = __DIR__ . '/process_logs.py';

// Check if Python script exists
if (!file_exists($scriptPath)) {
    echo json_encode([
        'success' => false,
        'error' => 'Python script not found at: ' . $scriptPath
    ]);
    exit;
}

// Execute Python script and capture output
$command = escapeshellcmd("$pythonPath $scriptPath 2>&1");
$output = shell_exec($command);
$exitCode = 0;

// Check if execution was successful
if ($output === null) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to execute Python script. Check permissions.'
    ]);
    exit;
}

// Return success with output
echo json_encode([
    'success' => true,
    'output' => $output,
    'message' => 'Logs processed to database successfully'
]);
?>
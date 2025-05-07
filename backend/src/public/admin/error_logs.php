<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Console Error Logs</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .log-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .log-content {
            font-family: monospace;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .error-type {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .error {
            background-color: #ffebee;
            border-left: 4px solid #f44336;
        }
        .warn {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
        }
        .info {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
        .timestamp {
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Console Error Logs</h1>
        
        <?php
        $logFiles = glob('logs/console-logs-*.json');
        rsort($logFiles); // Sort by newest first

        if (empty($logFiles)) {
            echo '<div class="alert alert-info">No log files found.</div>';
        } else {
            foreach ($logFiles as $logFile) {
                $logData = json_decode(file_get_contents($logFile), true);
                if (!$logData) continue;

                echo '<div class="log-container">';
                echo '<div class="log-header">';
                echo '<h3>' . basename($logFile) . '</h3>';
                echo '</div>';

                if (isset($logData['logs']) && is_array($logData['logs'])) {
                    foreach ($logData['logs'] as $log) {
                        $typeClass = strtolower($log['type']);
                        echo '<div class="log-content mb-3 p-3 ' . $typeClass . '">';
                        echo '<div class="d-flex justify-content-between mb-2">';
                        echo '<span class="error-type ' . $typeClass . '">' . strtoupper($log['type']) . '</span>';
                        echo '<span class="timestamp">' . date('Y-m-d H:i:s', strtotime($log['timestamp'])) . '</span>';
                        echo '</div>';
                        
                        if (is_array($log['message'])) {
                            echo '<pre>' . htmlspecialchars(print_r($log['message'], true)) . '</pre>';
                        } else {
                            echo '<pre>' . htmlspecialchars($log['message']) . '</pre>';
                        }
                        
                        if (!empty($log['stack'])) {
                            echo '<div class="mt-2"><strong>Stack Trace:</strong></div>';
                            echo '<pre class="mt-1">' . htmlspecialchars($log['stack']) . '</pre>';
                        }
                        echo '</div>';
                    }
                }
                echo '</div>';
            }
        }
        ?>
    </div>
</body>
</html> 
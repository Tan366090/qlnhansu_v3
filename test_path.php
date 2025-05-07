<?php
header('Content-Type: text/plain');

echo "Current directory: " . __DIR__ . "\n\n";
echo "Server information:\n";
print_r($_SERVER);

echo "\n\nDirectory contents:\n";
$files = scandir(__DIR__);
print_r($files);

echo "\n\nAPI directory contents:\n";
$api_files = scandir(__DIR__ . '/api');
print_r($api_files);
?> 
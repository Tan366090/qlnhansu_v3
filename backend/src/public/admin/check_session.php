<?php
require_once 'session_auth.php';

header('Content-Type: application/json');

$response = [
    'isLoggedIn' => SessionAuth::isLoggedIn(),
    'user' => SessionAuth::getCurrentUser()
];

echo json_encode($response);
?> 
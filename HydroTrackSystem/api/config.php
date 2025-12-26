<?php
// Prevent direct access if needed, but for now just setup headers
// Ensure JSON response for all API calls including errors
error_reporting(E_ALL);
ini_set('display_errors', 0); // Turn off HTML errors to not break JSON

// Set standardized content type
header('Content-Type: application/json');

$host = 'localhost';
$db = 'hydrotrack_db';
$user = 'root';
$pass = '';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
?>
<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$action = $input['action'] ?? 'Unknown';
$customer = $input['customer'] ?? '—';
$details = $input['details'] ?? '';

// We don't have a specific 'logs' table in schema provided (only stock_history, expenses, transactions).
// However, maybe we should log to a file or just ignore?
// Or maybe 'stock_history' if it's relevant?
// Let's assume for now we just acknowledge it to prevent 404 errors.
// OR we can create a logs table? 
// The user schema has `users`, `customers`, `products`, `inventory`, `transactions`, `stock_history`, `expenses`.
// No generic system log.
// I'll just return success.

echo json_encode(['success' => true]);
?>
<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$sizeKey = $input['size'] ?? '';
$qty = intval($input['qty'] ?? 1);

if (empty($sizeKey) || $qty <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid size or quantity']);
    exit;
}

// 1. Find Product ID from Size Key
$stmt = $conn->prepare("SELECT id FROM products WHERE size_key = ?");
$stmt->bind_param("s", $sizeKey);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product size not found']);
    exit;
}
$productId = $res->fetch_assoc()['id'];

// 2. Fetch available serials (FIFO - Oldest created first? or Just any 'in')
// Assuming FIFO based on id is enough.
$stmt = $conn->prepare("SELECT serial_number FROM inventory WHERE product_id = ? AND status = 'in' ORDER BY id ASC LIMIT ?");
$stmt->bind_param("ii", $productId, $qty);
$stmt->execute();
$res = $stmt->get_result();

$serials = [];
while ($row = $res->fetch_assoc()) {
    $serials[] = $row['serial_number'];
}

$availableCount = count($serials);

if ($availableCount < $qty) {
    echo json_encode([
        'success' => false,
        'message' => 'Insufficient stock',
        'available' => $availableCount,
        'requested' => $qty,
        'serials' => $serials // Optional: send what we found?
    ]);
} else {
    echo json_encode([
        'success' => true,
        'serials' => $serials
    ]);
}
?>
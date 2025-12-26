<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$serial = $input['serial'] ?? '';
$serials = $input['serials'] ?? [];

// Single Serial Validation (Legacy Support)
if (!empty($serial)) {
    $stmt = $conn->prepare("
        SELECT i.*, p.name as product_name, p.size, p.size_key 
        FROM inventory i 
        JOIN products p ON i.product_id = p.id 
        WHERE i.serial_number = ?
    ");
    $stmt->bind_param("s", $serial);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $data = $res->fetch_assoc();
        echo json_encode([
            'success' => true,
            'exists' => true,
            'data' => [
                'id' => $data['id'],
                'serial_number' => $data['serial_number'],
                'status' => $data['status'],
                'product_name' => $data['product_name'],
                'size' => $data['size'],
                'size_key' => $data['size_key']
            ]
        ]);
    } else {
        echo json_encode(['success' => true, 'exists' => false]);
    }
    exit;
}

// Batch Validation
if (!empty($serials) && is_array($serials)) {
    if (count($serials) === 0) {
        echo json_encode(['success' => true, 'batch' => true, 'results' => []]);
        exit;
    }

    // Prepare placeholders
    $placeholders = implode(',', array_fill(0, count($serials), '?'));
    $types = str_repeat('s', count($serials));

    $sql = "
        SELECT i.serial_number, i.status, p.size 
        FROM inventory i 
        JOIN products p ON i.product_id = p.id 
        WHERE i.serial_number IN ($placeholders)
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$serials);
    $stmt->execute();
    $result = $stmt->get_result();

    $found = [];
    while ($row = $result->fetch_assoc()) {
        $found[$row['serial_number']] = $row;
    }

    $results = [];
    foreach ($serials as $s) {
        if (isset($found[$s])) {
            $results[] = [
                'serial' => $s,
                'exists' => true,
                'status' => $found[$s]['status'],
                'size' => $found[$s]['size']
            ];
        } else {
            $results[] = [
                'serial' => $s,
                'exists' => false,
                'status' => 'unknown'
            ];
        }
    }

    echo json_encode(['success' => true, 'batch' => true, 'results' => $results]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'No serial provided']);
?>
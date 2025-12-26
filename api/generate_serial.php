<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$typeCode = $input['typeCode'] ?? null; // e.g. "20S", "20R"

if (!$typeCode) {
    echo json_encode(['success' => false, 'message' => 'Missing type code']);
    exit;
}

// Map short codes to database keys and prefixes
$map = [
    '20S' => ['key' => '20LiterSlim', 'prefix' => 'SLM'],
    '20R' => ['key' => '20LiterRound', 'prefix' => 'RND'],
    '10L' => ['key' => '10Liter', 'prefix' => '10L'],
    '05L' => ['key' => '5Liter', 'prefix' => '05L']
];

if (!isset($map[$typeCode])) {
    echo json_encode(['success' => false, 'message' => 'Invalid type code']);
    exit;
}

$sizeKey = $map[$typeCode]['key'];
$prefix = $map[$typeCode]['prefix'];

// Get Product ID
$stmt = $conn->prepare("SELECT id, name, size FROM products WHERE size_key = ?");
$stmt->bind_param("s", $sizeKey);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found in database']);
    exit;
}

$product = $res->fetch_assoc();
$productId = $product['id'];

// Generate Serial Number
date_default_timezone_set('Asia/Manila');
$dateStr = date('Ymd');
$searchPattern = "{$prefix}-{$dateStr}-%";

// Set starting sequence
$startFrom = $input['seq'] ?? null;
$force = $input['force'] ?? false;
$nextSeq = null;

if ($startFrom === null) {
    // Standard logic: Find last serial for today and increment
    $stmt = $conn->prepare("SELECT serial_number FROM inventory WHERE serial_number LIKE ? ORDER BY serial_number DESC LIMIT 1");
    $stmt->bind_param("s", $searchPattern);
    $stmt->execute();
    $lastRes = $stmt->get_result();

    $nextSeq = 1;
    if ($lastRes->num_rows > 0) {
        $lastSerial = $lastRes->fetch_assoc()['serial_number'];
        $parts = explode('-', $lastSerial);
        $lastSeq = end($parts);
        $nextSeq = intval($lastSeq) + 1;
    }
} else {
    // User requested a specific sequence
    if ($force) {
        // Force the requested sequence, ignoring conflicts
        $nextSeq = intval($startFrom);
    } else {
        // Conflict-aware logic: Find the first available sequence starting from $startFrom
        $checkSeq = intval($startFrom);
        $maxAttempts = 10000; // Safety cap
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $seqStr = str_pad($checkSeq, 4, '0', STR_PAD_LEFT);
            $checkSerial = "{$prefix}-{$dateStr}-{$seqStr}";

            $stmt = $conn->prepare("SELECT id FROM inventory WHERE serial_number = ? LIMIT 1");
            $stmt->bind_param("s", $checkSerial);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                $nextSeq = $checkSeq;
                break;
            }
            $checkSeq++;
            $attempts++;
        }

        if ($nextSeq === null) {
            echo json_encode(['success' => false, 'message' => "Could not find an available serial number after $maxAttempts attempts."]);
            exit;
        }
    }
}

$seqStr = str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
$newSerial = "{$prefix}-{$dateStr}-{$seqStr}";

// Return the generated serial without inserting into database
echo json_encode([
    'success' => true,
    'serial' => $newSerial,
    'seq' => $nextSeq,
    'size' => $sizeKey,
    'type' => 'Gallon', // For QR payload compatibility
    'product' => $product
]);
exit;

?>
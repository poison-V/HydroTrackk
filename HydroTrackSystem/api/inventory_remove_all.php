<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$conn->begin_transaction();

try {
    // 1. Get counts of 'in' status per product before updating (for history logging)
    $stmt = $conn->query("SELECT product_id, COUNT(*) as qty FROM inventory WHERE status = 'in' GROUP BY product_id");
    $toUpdate = [];
    while ($row = $stmt->fetch_assoc()) {
        $toUpdate[] = $row;
    }

    // 2. Mark all items as 'out'
    $conn->query("UPDATE inventory SET status = 'out', customer_id = NULL WHERE status != 'out'");

    // 3. Log bulk action in stock_history for each affected product
    $histStmt = $conn->prepare("INSERT INTO stock_history (product_id, action, quantity, previous_stock, new_stock, notes) VALUES (?, 'bulk_remove', ?, ?, 0, ?)");
    $notes = "Bulk Remove All Stock Action triggered";

    foreach ($toUpdate as $item) {
        $pid = $item['product_id'];
        $qty = $item['qty'];
        $delta = -$qty; // Quantity removed
        $prev = $qty;   // Previous available stock

        $histStmt->bind_param("iiis", $pid, $delta, $prev, $notes);
        $histStmt->execute();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'All stock items marked as OUT.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?>
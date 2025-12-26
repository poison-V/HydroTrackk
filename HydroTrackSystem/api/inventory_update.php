<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

// Support both single 'serial' and array 'serials'
$serials = [];
if (!empty($input['serial'])) {
    $serials[] = $input['serial'];
}
if (!empty($input['serials']) && is_array($input['serials'])) {
    $serials = array_merge($serials, $input['serials']);
}
$serials = array_unique($serials);

if (empty($serials) || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'Missing serials or action']);
    exit;
}

$conn->begin_transaction();

try {
    $updatedCount = 0;
    $errors = [];

    // Attempt to lookup customer ID if customer name is provided
    $customerName = $input['customer'] ?? '';
    $customerId = null;
    if ($customerName) {
        $cStmt = $conn->prepare("SELECT id FROM customers WHERE full_name = ? LIMIT 1");
        $cStmt->bind_param("s", $customerName);
        $cStmt->execute();
        $cRes = $cStmt->get_result();
        if ($row = $cRes->fetch_assoc()) {
            $customerId = $row['id'];
        }
    }

    foreach ($serials as $serial) {
        // 1. Get current status
        $stmt = $conn->prepare("SELECT id, product_id, status FROM inventory WHERE serial_number = ?");
        $stmt->bind_param("s", $serial);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            // New serial creation logic if action is 'in'
            if ($action === 'in') {
                // Determine product type from prefix
                $prefix = substr($serial, 0, 3);
                $sizeKey = null;
                if ($prefix === 'SLM')
                    $sizeKey = '20LiterSlim';
                elseif ($prefix === 'RND')
                    $sizeKey = '20LiterRound';
                elseif ($prefix === '10L')
                    $sizeKey = '10Liter';
                elseif ($prefix === '05L')
                    $sizeKey = '5Liter';

                if ($sizeKey) {
                    $pStmt = $conn->prepare("SELECT id FROM products WHERE size_key = ?");
                    $pStmt->bind_param("s", $sizeKey);
                    $pStmt->execute();
                    $pRes = $pStmt->get_result();
                    if ($pRow = $pRes->fetch_assoc()) {
                        $newProductId = $pRow['id'];
                        // Create the new entry in inventory with 'out' as initial status 
                        // so the subsequent code marks it as 'in' properly
                        $createStmt = $conn->prepare("INSERT INTO inventory (product_id, serial_number, status) VALUES (?, ?, 'out')");
                        $createStmt->bind_param("is", $newProductId, $serial);
                        $createStmt->execute();

                        // Refetch to continue standard flow
                        $stmt->execute();
                        $res = $stmt->get_result();
                    } else {
                        $errors[] = "$serial: Product mapping failed";
                        continue;
                    }
                } else {
                    $errors[] = "$serial: Unknown prefix, cannot auto-register";
                    continue;
                }
            } else {
                $errors[] = "$serial: Not found";
                continue;
            }
        }

        $item = $res->fetch_assoc();
        $currentStatus = $item['status'];
        $productId = $item['product_id'];


        // 2. Validate Action
        $newStatus = '';
        $newCustId = "NULL"; // String for query construction if null
        // Bind param approach is better
        $dbCustId = null;

        if ($action === 'in') {
            if ($currentStatus === 'in') {
                $errors[] = "$serial: Already IN stock";
                continue;
            }
            $newStatus = 'in';
            $dbCustId = null; // Returning, so no customer holding it
        } elseif ($action === 'out') {
            if ($currentStatus === 'out' || $currentStatus === 'borrowed') {
                $errors[] = "$serial: Already OUT/BORROWED";
                continue;
            }
            // If Borrow, status could be 'borrowed'. If Buy, maybe 'out'?
            // User request: "Borrowed gallons: Deduct... Bought gallons: Deduct..."
            // System distinguishes 'borrowed' vs 'out'.
            // Let's assume input 'action' might contain 'borrow'? 
            // Or we map 'out' to 'out'/'borrowed' based on input context?
            // Currently home.php sends 'out' for both.
            // Let's stick to 'out' for simplicity unless we want to distinguish.
            // Actually, if we want to track borrowers, we should probably set 'borrowed' if applicable.
            // BUT home.php sends 'action: out'.
            // Let's treat 'out' as generic out-of-stock. 
            // If we have customer ID, maybe it implies borrowed? 
            // For now, let's just set to 'out' or 'borrowed' if passed.
            // Let's check if input has 'status_override'? No.
            // Just use 'out'. 
            // If we want 'borrowed', we should update home.php to send action 'borrow'.
            // Let's keep it simple: 'out' means not in stock.

            // Wait, if it's "Borrowed", the status SHOULD be 'borrowed' so we know to expect it back.
            // If "Bought", it's 'out' (sold forever).
            // This distinction is important for "Returned gallons".
            // If I bought it, I don't return it (usually).
            // So, let's try to infer or accept explicit status?
            // home.php sends `action: 'out'`.
            // Let's assume 'out' for now.

            $newStatus = 'out';
            // Only assign customer if it's a Borrow/tracking scenario? 
            // If bought, we might still track who has it?
            $dbCustId = $customerId;
        } else {
            throw new Exception("Invalid action");
        }

        // Use 'borrowed' if specified in optional 'status' field?
        if (!empty($input['status'])) {
            $newStatus = $input['status'];
        }

        // 3. Update Database
        $updateStmt = $conn->prepare("UPDATE inventory SET status = ?, customer_id = ?, last_updated = CURRENT_TIMESTAMP WHERE id = ?");
        $updateStmt->bind_param("sii", $newStatus, $dbCustId, $item['id']);
        $updateStmt->execute();

        // 4. Log History
        $delta = ($newStatus === 'in') ? 1 : -1;
        // If status changed from out->borrowed, delta is 0?
        // if prev was 'in' and new is 'out', delta -1.
        // if prev was 'in' and new is 'borrowed', delta -1 so it's not available.
        // if prev was 'out' and new is 'in', delta +1.

        // Correct logic:
        $isAvailBefore = ($currentStatus === 'in');
        $isAvailAfter = ($newStatus === 'in');

        $delta = 0;
        if ($isAvailBefore && !$isAvailAfter)
            $delta = -1;
        if (!$isAvailBefore && $isAvailAfter)
            $delta = 1;

        $historyAction = $action;
        $notes = "Scanner " . strtoupper($action) . " - " . $serial;
        if ($customerName)
            $notes .= " (Cust: $customerName)";

        // Quick stock calc
        $countStmt = $conn->prepare("SELECT COUNT(*) as c FROM inventory WHERE product_id = ? AND status = 'in'");
        $countStmt->bind_param("i", $productId);
        $countStmt->execute();
        $newStockCount = $countStmt->get_result()->fetch_assoc()['c'];

        // If delta is 0 (e.g. out -> borrowed), stock count didn't change (avail is still 0), so prev = new.
        // If delta is -1 (in -> out), new is count. Prev was count+1.
        // If delta is 1 (out -> in), new is count. Prev was count-1.
        $prevStockCount = $newStockCount - $delta;

        if ($delta != 0) {
            $histStmt = $conn->prepare("INSERT INTO stock_history (product_id, action, quantity, previous_stock, new_stock, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $histStmt->bind_param("isiiis", $productId, $historyAction, $delta, $prevStockCount, $newStockCount, $notes);
            $histStmt->execute();
        }

        $updatedCount++;
    }

    if ($updatedCount > 0) {
        $conn->commit();
        echo json_encode(['success' => true, 'updated_count' => $updatedCount, 'errors' => $errors]);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'No items updated', 'errors' => $errors]);
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?>
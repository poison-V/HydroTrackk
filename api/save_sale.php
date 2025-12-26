<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$invoice = $input['invoice'] ?? '';
$type = $input['type'] ?? '';
$custName = $input['customer'] ?? '';
$phon = $input['phone'] ?? ''; // Fixed variable name conflict
$addr = $input['address'] ?? '';
$size = $input['size'] ?? '';
$qty = $input['qty'] ?? 0;
$unit = $input['unit'] ?? 0;
$total = $input['total'] ?? 0;
$payMethod = $input['paymentMethod'] ?? 'Cash';
$paid = $input['amountPaid'] ?? 0;
$change = $input['change'] ?? 0;
$delivery = $input['deliveryOption'] ?? 'Pickup';
$cashier = $input['cashier'] ?? '';
$date = $input['date'] ?? date('Y-m-d H:i:s');
$serials = json_encode($input['serials'] ?? []);

// Lookup Product ID
$prodId = null;
if ($size) {
    $stmt = $conn->prepare("SELECT id FROM products WHERE size_key = ?");
    $stmt->bind_param("s", $size);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($r = $res->fetch_assoc())
        $prodId = $r['id'];
}

/* Lookup Customer Key (Optional, logic to find ID by name if needed, but schema allows name text) */

$stmt = $conn->prepare("INSERT INTO transactions 
    (invoice_number, transaction_type, customer_name, customer_phone, customer_address, 
     product_id, product_size, quantity, unit_price, total_amount, 
     payment_method, amount_paid, change_amount, delivery_type, cashier_name, transaction_date, serials) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "sssssisiddssdssss",
    $invoice,
    $type,
    $custName,
    $phon,
    $addr,
    $prodId,
    $size,
    $qty,
    $unit,
    $total,
    $payMethod,
    $paid,
    $change,
    $delivery,
    $cashier,
    $date,
    $serials
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
?>
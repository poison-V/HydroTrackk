<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    // Fetch all inventory items with their status and product size
    $sql = "SELECT i.serial_number, i.status, p.size_key, p.name as product_name
            FROM inventory i 
            JOIN products p ON i.product_id = p.id 
            ORDER BY i.serial_number ASC";

    $result = $conn->query($sql);

    $data = [];
    $summary = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $key = $row['size_key'];
            if (!isset($data[$key])) {
                $data[$key] = [];
            }
            if (!isset($summary[$key])) {
                $summary[$key] = ['in' => 0, 'out' => 0, 'borrowed' => 0];
            }

            // Add to list
            $data[$key][] = [
                'serial' => $row['serial_number'],
                'status' => $row['status']
            ];

            // Update summary count
            $status = strtolower($row['status']);
            if ($status === 'in') {
                $summary[$key]['in']++;
            } elseif ($status === 'out') {
                $summary[$key]['out']++;
            } elseif ($status === 'borrowed') {
                $summary[$key]['borrowed']++;
            }
        }
    }

    echo json_encode(['success' => true, 'details' => $data, 'summary' => $summary]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
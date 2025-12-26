<?php
require_once 'config.php';

$sql = "
    SELECT 
        p.size_key, 
        COUNT(CASE WHEN i.status = 'in' THEN 1 END) as in_stock,
        COUNT(CASE WHEN i.status = 'out' THEN 1 END) as out_stock,
        COUNT(CASE WHEN i.status = 'borrowed' THEN 1 END) as borrowed
    FROM products p
    LEFT JOIN inventory i ON p.id = i.product_id
    GROUP BY p.id, p.size_key
";

$result = $conn->query($sql);
$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[$row['size_key']] = [
            'in' => (int) $row['in_stock'],
            'out' => (int) $row['out_stock'],
            'borrowed' => (int) $row['borrowed']
        ];
    }
}

echo json_encode([
    'success' => true,
    'data' => $data
]);
?>
<?php
require_once __DIR__ . '/../config/init.php';
try {
    $pdo = Database::getInstance()->getConnection();
    $start = '2025-01-03 00:00:00';
    $end = '2025-09-30 23:59:59';
    $sql = 'SELECT c.id,c.empresa,COALESCE(SUM(e.quantidade),0) as total FROM clients c LEFT JOIN envase_data e ON e.empresa=c.empresa AND e.data_upload BETWEEN ? AND ? GROUP BY c.id,c.empresa ORDER BY total DESC LIMIT 100';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$start, $end]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo $r['id'] . " | " . ($r['empresa'] ?: '(null)') . " | total=" . $r['total'] . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

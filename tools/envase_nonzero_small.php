<?php
require_once __DIR__ . '/../config/init.php';
try {
    $pdo = Database::getInstance()->getConnection();
    $start = '2025-01-01 00:00:00';
    $end = '2025-09-30 23:59:59';
    $sql = "SELECT c.id,c.empresa,COALESCE(SUM(e.quantidade),0) as total FROM clients c LEFT JOIN envase_data e ON e.empresa=c.empresa AND e.data_upload BETWEEN ? AND ? GROUP BY c.id,c.empresa HAVING total > 0 AND total < 500 ORDER BY total ASC LIMIT 200";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$start, $end]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo "No non-zero clients with total between 1 and 499 in the range\n";
    } else {
        foreach ($rows as $r) echo $r['id'] . ' | ' . $r['empresa'] . ' | total=' . $r['total'] . "\n";
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}

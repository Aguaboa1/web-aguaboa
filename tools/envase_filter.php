<?php
require_once __DIR__ . '/../config/init.php';

$start = $argv[1] ?? '2025-01-03';
$end = $argv[2] ?? '2025-09-30';
$threshold = isset($argv[3]) ? (float)$argv[3] : 500.0;

function months_diff($startDate, $endDate) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $end->modify('+1 day');
    $diff = $start->diff($end);
    return ($diff->y * 12) + $diff->m + ($diff->d / 30.0);
}

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    echo "DB error: " . $e->getMessage() . PHP_EOL; exit(1);
}

$months = months_diff($start, $end);
if ($months <= 0) $months = 1;

$sql = "SELECT c.id as client_id, c.cliente, c.empresa, COALESCE(SUM(e.quantidade),0) as total_in_range
        FROM clients c
        LEFT JOIN envase_data e ON e.empresa = c.empresa AND STR_TO_DATE(CONCAT(e.ano,'-',LPAD(e.mes,2,'0'),'-',LPAD(e.dia,2,'0')), '%Y-%m-%d') BETWEEN :start AND :end
    GROUP BY c.id, c.cliente, c.empresa
    ORDER BY total_in_range DESC";

$stmt = $db->prepare($sql);
$stmt->execute(['start' => $start . ' 00:00:00', 'end' => $end . ' 23:59:59']);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$found = [];
foreach ($rows as $r) {
    $avg = $r['total_in_range'] / $months;
    if ($avg < $threshold) {
        $found[] = [
            'id' => $r['client_id'],
            'empresa' => $r['empresa'],
            'total' => (int)$r['total_in_range'],
            'avg' => round($avg,2)
        ];
    }
}

echo "Range: $start to $end (months approx: $months) threshold: $threshold\n";
if (empty($found)) {
    echo "No clients found with avg < $threshold\n";
} else {
    foreach ($found as $f) {
        echo "{$f['id']} | {$f['empresa']} | total={$f['total']} | avg={$f['avg']}\n";
    }
}

?>
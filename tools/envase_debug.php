<?php
// Debug helper: inspect envase_data and clients, and their matching
require_once __DIR__ . '/../config/init.php';

$db = null;
try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    echo "ERROR: could not connect to database: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

$start = $argv[1] ?? '2024-01-01';
$end = $argv[2] ?? '2026-12-31';

echo "Using date range: $start to $end\n\n";

function run($db, $sql, $params = []) {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Counts
$c = run($db, 'SELECT COUNT(*) as cnt FROM envase_data');
$envase_count = $c->fetchColumn();
$d = run($db, 'SELECT COUNT(*) as cnt FROM clients');
$clients_count = $d->fetchColumn();

echo "envase_data rows: $envase_count\n";
echo "clients rows: $clients_count\n\n";

// Sample envase_data
echo "--- Sample envase_data (first 20) ---\n";
$stmt = run($db, 'SELECT id, empresa, produto, quantidade, data_upload FROM envase_data ORDER BY data_upload DESC LIMIT 20');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) {
    echo "(no rows)\n";
} else {
    foreach ($rows as $r) {
        echo sprintf("%s | %s | %s | %s | %s\n", $r['id'], $r['empresa'], $r['produto'], $r['quantidade'], $r['data_upload']);
    }
}

echo "\n--- Top empresas by total envase ---\n";
$stmt = run($db, 'SELECT empresa, COUNT(*) as cnt, SUM(quantidade) as total, MIN(data_upload) as first, MAX(data_upload) as last FROM envase_data GROUP BY empresa ORDER BY total DESC LIMIT 20');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) { echo "(no rows)\n"; } else { foreach ($rows as $r) { echo sprintf("%s | rows=%s total=%s first=%s last=%s\n", $r['empresa'] ?? '(null)', $r['cnt'], $r['total'], $r['first'], $r['last']); } }

echo "\n--- Sample clients (first 20) ---\n";
$stmt = run($db, 'SELECT id, cliente, empresa FROM clients LIMIT 20');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) { echo "(no client rows)\n"; } else { foreach ($rows as $r) { echo sprintf("%s | %s | %s\n", $r['id'], $r['cliente'], $r['empresa']); } }

// Empresas in envase_data not present in clients
echo "\n--- Empresas in envase_data NOT in clients (top 20) ---\n";
$sql = 'SELECT e.empresa, COUNT(*) as cnt, SUM(e.quantidade) as total FROM envase_data e LEFT JOIN clients c ON e.empresa = c.empresa WHERE c.id IS NULL GROUP BY e.empresa ORDER BY cnt DESC LIMIT 20';
$stmt = run($db, $sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) { echo "(no unmatched empresas)\n"; } else { foreach ($rows as $r) { echo sprintf("%s | rows=%s total=%s\n", $r['empresa'] ?? '(null)', $r['cnt'], $r['total']); } }

// Aggregation for given date range: top clients by total in range
echo "\n--- Aggregation per client for range ($start to $end) ---\n";
$sql = 'SELECT c.id as client_id, c.cliente, c.empresa, COALESCE(SUM(e.quantidade),0) as total_in_range
        FROM clients c
        LEFT JOIN envase_data e ON e.empresa = c.empresa AND e.data_upload BETWEEN :start AND :end
        GROUP BY c.id, c.cliente, c.empresa
        ORDER BY total_in_range DESC
        LIMIT 50';
$stmt = run($db, $sql, ['start' => $start . ' 00:00:00', 'end' => $end . ' 23:59:59']);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) { echo "(no rows)\n"; } else { foreach ($rows as $r) { echo sprintf("%s | %s | total=%s\n", $r['client_id'], $r['empresa'] ?? '(null)', $r['total_in_range']); } }

// Also show companies that have envase in range but no client match
echo "\n--- Empresas with envase in range but no client match ---\n";
$sql = 'SELECT e.empresa, COUNT(*) as cnt, SUM(e.quantidade) as total FROM envase_data e LEFT JOIN clients c ON e.empresa = c.empresa WHERE c.id IS NULL AND e.data_upload BETWEEN :start AND :end GROUP BY e.empresa ORDER BY cnt DESC LIMIT 50';
$stmt = run($db, $sql, ['start' => $start . ' 00:00:00', 'end' => $end . ' 23:59:59']);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) { echo "(none)\n"; } else { foreach ($rows as $r) { echo sprintf("%s | rows=%s total=%s\n", $r['empresa'] ?? '(null)', $r['cnt'], $r['total']); } }

echo "\nDone.\n";

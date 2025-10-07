<?php
require_once __DIR__ . '/../config/init.php';
try {
    $db = Database::getInstance()->getConnection();
    echo "COLUMNS in envase_data:\n";
    $cols = $db->query("SHOW COLUMNS FROM envase_data")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo $c['Field'] . " \t " . $c['Type'] . "\n";
    }
    echo "\nSAMPLE rows (20):\n";
    $stmt = $db->query("SELECT * FROM envase_data LIMIT 20");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $i => $r) {
        echo "-- row " . ($i+1) . " --\n";
        foreach ($r as $k=>$v) {
            echo $k . ': ' . (is_null($v)?'(null)':$v) . "\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}

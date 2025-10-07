<?php
require_once 'config/init.php';

echo "🔍 Buscando ação com 'teste ok'...\n\n";

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM actions WHERE descricao LIKE '%teste%' OR descricao LIKE '%ok%'");
$actions = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($actions as $action) {
    echo "ID: {$action['id']}\n";
    echo "Descrição: {$action['descricao']}\n";
    echo "Arquivo: " . ($action['arquivo'] ?: 'NENHUM') . "\n";
    echo "Data: {$action['data_acao']}\n";
    
    if ($action['arquivo']) {
        $filePath = UPLOAD_DIR . 'actions/' . $action['arquivo'];
        echo "Caminho: $filePath\n";
        echo "Existe: " . (file_exists($filePath) ? "SIM" : "NÃO") . "\n";
        echo "URL: http://localhost:8080/uploads/actions/{$action['arquivo']}\n";
    }
    echo "\n" . str_repeat('-', 50) . "\n\n";
}
?>
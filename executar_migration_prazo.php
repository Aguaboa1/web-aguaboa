<?php
// Script para adicionar a coluna prazo_conclusao
require_once 'config/init.php';

try {
    // Verificar se a coluna já existe
    $stmt = $pdo->query("SHOW COLUMNS FROM actions LIKE 'prazo_conclusao'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        echo "🔧 Adicionando coluna prazo_conclusao na tabela actions...\n";
        
        $sql = "ALTER TABLE actions ADD COLUMN prazo_conclusao DATE AFTER data_acao";
        $pdo->exec($sql);
        
        echo "✅ Coluna prazo_conclusao adicionada com sucesso!\n";
    } else {
        echo "ℹ️ Coluna prazo_conclusao já existe.\n";
    }
    
    // Verificar a estrutura da tabela
    echo "\n📋 Estrutura atual da tabela actions:\n";
    $stmt = $pdo->query("DESCRIBE actions");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Key']}\n";
    }
    
    echo "\n✅ Script executado com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
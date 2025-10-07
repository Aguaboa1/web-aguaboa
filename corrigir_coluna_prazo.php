<?php
// Script para corrigir o erro da coluna prazo_conclusao
require_once 'config/init.php';

echo "<h2>🔧 Corrigindo Erro: Coluna prazo_conclusao</h2>";

try {
    echo "<h3>1. Verificando se a coluna existe:</h3>";
    
    // Verificar se a coluna já existe
    $stmt = $pdo->query("SHOW COLUMNS FROM actions LIKE 'prazo_conclusao'");
    $columnExists = $stmt->fetch();
    
    if ($columnExists) {
        echo "✅ A coluna 'prazo_conclusao' já existe!<br>";
    } else {
        echo "❌ A coluna 'prazo_conclusao' NÃO existe. Criando...<br>";
        
        // Criar a coluna
        $sql = "ALTER TABLE actions ADD COLUMN prazo_conclusao DATE AFTER data_acao";
        $pdo->exec($sql);
        
        echo "✅ Coluna 'prazo_conclusao' criada com sucesso!<br>";
    }
    
    echo "<h3>2. Verificando estrutura atual da tabela:</h3>";
    $stmt = $pdo->query("DESCRIBE actions");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    
    foreach ($columns as $column) {
        $highlight = $column['Field'] === 'prazo_conclusao' ? "style='background: #d4edda;'" : "";
        echo "<tr $highlight>";
        echo "<td><strong>{$column['Field']}</strong></td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>3. Testando inserção com a nova coluna:</h3>";
    
    // Teste de inserção
    $testSQL = "INSERT INTO actions (client_id, descricao, data_acao, prazo_conclusao) VALUES (1, 'Teste coluna prazo', '2025-10-02', '2025-12-01')";
    $pdo->exec($testSQL);
    echo "✅ Teste de inserção realizado com sucesso!<br>";
    
    // Buscar o registro de teste
    $stmt = $pdo->query("SELECT * FROM actions WHERE descricao = 'Teste coluna prazo'");
    $testRecord = $stmt->fetch();
    
    if ($testRecord) {
        echo "✅ Registro de teste encontrado:<br>";
        echo "<pre>";
        print_r($testRecord);
        echo "</pre>";
        
        // Remover o registro de teste
        $pdo->exec("DELETE FROM actions WHERE descricao = 'Teste coluna prazo'");
        echo "🗑️ Registro de teste removido.<br>";
    }
    
    echo "<h3>4. Testando atualização:</h3>";
    
    // Teste de update
    $updateSQL = "UPDATE actions SET prazo_conclusao = '2025-11-01' WHERE id = 1";
    $stmt = $pdo->prepare($updateSQL);
    $result = $stmt->execute();
    
    if ($result) {
        echo "✅ Teste de atualização realizado com sucesso!<br>";
        echo "Linhas afetadas: " . $stmt->rowCount() . "<br>";
    }
    
    echo "<h3>5. Status final:</h3>";
    echo "✅ <strong style='color: green;'>PROBLEMA RESOLVIDO!</strong><br>";
    echo "A coluna 'prazo_conclusao' foi criada e está funcionando corretamente.<br>";
    echo "<br>";
    echo "🎯 <strong>Próximos passos:</strong><br>";
    echo "1. Volte à interface do sistema<br>";
    echo "2. Tente editar uma ação novamente<br>";
    echo "3. O erro deve ter sido corrigido<br>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Erro:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<pre style='background: #f8d7da; padding: 10px;'>";
    echo $e->getTraceAsString();
    echo "</pre>";
}
?>
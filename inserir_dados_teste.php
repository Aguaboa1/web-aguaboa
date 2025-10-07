<?php
/**
 * Script para inserir dados de teste diretamente no banco
 */

require_once 'config/init.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h2>🧪 Inserindo Dados de Teste</h2>";
    
    // Primeiro, vamos inserir alguns clientes
    $clientes = [
        ['Aguaboa Distribuidora', 'São Paulo'],
        ['Distribuidora ABC', 'Rio de Janeiro'],
        ['Empresa XYZ', 'Belo Horizonte']
    ];
    
    foreach ($clientes as $cliente) {
        $sql = "INSERT IGNORE INTO clients (cliente, empresa, cidade, tipo_cliente, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([$cliente[0], $cliente[0], $cliente[1], 'envase']);
        echo "<p>✅ Cliente inserido: {$cliente[0]}</p>";
    }
    
    // Agora vamos inserir dados de envase
    $envases = [
        ['Aguaboa Distribuidora', 'São Paulo', 'Água 500ml', 2025, 10, 1, 1200],
        ['Aguaboa Distribuidora', 'São Paulo', 'Água 1L', 2025, 10, 1, 800],
        ['Aguaboa Distribuidora', 'São Paulo', 'Água 5L', 2025, 10, 1, 300],
        ['Distribuidora ABC', 'Rio de Janeiro', 'Água 500ml', 2025, 10, 2, 950],
        ['Distribuidora ABC', 'Rio de Janeiro', 'Água 1L', 2025, 10, 2, 600],
        ['Empresa XYZ', 'Belo Horizonte', 'Água 500ml', 2025, 10, 3, 750],
    ];
    
    foreach ($envases as $envase) {
        $sql = "INSERT INTO envase_data (empresa, cidade, produto, ano, mes, dia, quantidade, arquivo_origem, data_upload) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $envase[0], $envase[1], $envase[2], $envase[3], 
            $envase[4], $envase[5], $envase[6], 'teste_manual'
        ]);
        echo "<p>✅ Envase inserido: {$envase[0]} - {$envase[2]} - {$envase[6]} unidades</p>";
    }
    
    // Inserir alguns históricos de upload
    $sql = "INSERT INTO upload_history (nome_arquivo, status, registros_processados, usuario_id, data_upload) 
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = $db->prepare($sql);
    $stmt->execute(['teste_manual.csv', 'concluido', 6, 1]);
    echo "<p>✅ Histórico de upload inserido</p>";
    
    echo "<hr>";
    echo "<h3>📊 Verificação dos Dados:</h3>";
    
    // Contar registros
    $stmt = $db->query("SELECT COUNT(*) as total FROM clients");
    $totalClientes = $stmt->fetch()['total'];
    echo "<p>👥 Total de clientes: $totalClientes</p>";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM envase_data");
    $totalEnvase = $stmt->fetch()['total'];
    echo "<p>📦 Total de registros de envase: $totalEnvase</p>";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM upload_history");
    $totalUploads = $stmt->fetch()['total'];
    echo "<p>📤 Total de uploads: $totalUploads</p>";
    
    echo "<p>✅ <strong>Dados de teste inseridos com sucesso!</strong></p>";
    echo "<p><a href='public/'>👉 Ir para o sistema e testar</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}
?>
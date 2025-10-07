<?php
require_once 'config/init.php';

echo "<h2>🔍 Diagnóstico Completo do CRM</h2>";

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "<h3>1. Verificação direta das tabelas</h3>";
    
    // Verificar tabela clients
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clients");
    $total_clients = $stmt->fetch()['total'];
    echo "📊 Registros na tabela 'clients': {$total_clients}<br>";
    
    if ($total_clients > 0) {
        $stmt = $pdo->query("SELECT * FROM clients LIMIT 5");
        $clients = $stmt->fetchAll();
        echo "<h4>Primeiros 5 clientes encontrados:</h4>";
        echo "<table border='1' style='border-collapse: collapse; font-size: 11px;'>";
        echo "<tr><th>ID</th><th>Cliente</th><th>Empresa</th><th>Cidade</th><th>Created At</th></tr>";
        foreach ($clients as $client) {
            echo "<tr>";
            echo "<td>{$client['id']}</td>";
            echo "<td>" . htmlspecialchars($client['cliente']) . "</td>";
            echo "<td>" . htmlspecialchars($client['empresa']) . "</td>";
            echo "<td>" . htmlspecialchars($client['cidade']) . "</td>";
            echo "<td>{$client['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    // Verificar se há dados sendo gerados automaticamente
    echo "<h3>2. Testando modelo Client</h3>";
    $clientModel = new Client();
    
    $stats = $clientModel->getStats();
    echo "Stats do modelo Client:<br>";
    echo "<pre>" . print_r($stats, true) . "</pre>";
    
    $clients_from_model = $clientModel->findAllWithEnvaseStats('', '', 1, 10);
    echo "Clientes do modelo (findAllWithEnvaseStats):<br>";
    echo "Total encontrado: " . count($clients_from_model) . "<br>";
    
    if (count($clients_from_model) > 0) {
        echo "<table border='1' style='border-collapse: collapse; font-size: 11px;'>";
        echo "<tr><th>ID</th><th>Cliente</th><th>Empresa</th><th>Envases</th></tr>";
        foreach (array_slice($clients_from_model, 0, 5) as $client) {
            echo "<tr>";
            echo "<td>{$client['id']}</td>";
            echo "<td>" . htmlspecialchars($client['cliente']) . "</td>";
            echo "<td>" . htmlspecialchars($client['empresa']) . "</td>";
            echo "<td>" . ($client['total_envases'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    // Verificar se há clientes sendo criados automaticamente a partir de envase_data
    echo "<h3>3. Verificando criação automática de clientes</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM envase_data");
    $total_envase = $stmt->fetch()['total'];
    echo "📊 Registros na tabela 'envase_data': {$total_envase}<br>";
    
    if ($total_envase > 0) {
        // Verificar empresas únicas em envase_data
        $stmt = $pdo->query("SELECT DISTINCT empresa, cidade FROM envase_data LIMIT 10");
        $empresas_envase = $stmt->fetchAll();
        
        echo "<h4>Empresas em envase_data que deveriam virar clientes:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Empresa</th><th>Cidade</th><th>Existe como cliente?</th></tr>";
        
        foreach ($empresas_envase as $empresa) {
            $stmt_check = $pdo->prepare("SELECT COUNT(*) as existe FROM clients WHERE empresa = ?");
            $stmt_check->execute([$empresa['empresa']]);
            $existe = $stmt_check->fetch()['existe'] > 0 ? "✅ SIM" : "❌ NÃO";
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($empresa['empresa']) . "</td>";
            echo "<td>" . htmlspecialchars($empresa['cidade']) . "</td>";
            echo "<td>$existe</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>";
        echo "<h4>⚠️ PROBLEMA IDENTIFICADO</h4>";
        echo "Há dados de envase ({$total_envase} registros) mas não há clientes correspondentes.<br>";
        echo "Os clientes devem ser criados automaticamente a partir dos dados de envase.";
        echo "</div>";
        
    } else {
        echo "✅ Não há dados de envase, portanto não há clientes para criar.<br>";
    }
    
    // Forçar limpeza completa se necessário
    if ($total_clients > 0) {
        echo "<h3>4. Executando limpeza forçada</h3>";
        
        $pdo->query("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->query("DELETE FROM client_infos");
        $pdo->query("DELETE FROM actions");
        $pdo->query("DELETE FROM clients");
        $pdo->query("ALTER TABLE clients AUTO_INCREMENT = 1");
        $pdo->query("ALTER TABLE actions AUTO_INCREMENT = 1");
        $pdo->query("ALTER TABLE client_infos AUTO_INCREMENT = 1");
        $pdo->query("SET FOREIGN_KEY_CHECKS = 1");
        
        echo "✅ Limpeza forçada executada<br>";
        
        // Verificar novamente
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM clients");
        $total_final = $stmt->fetch()['total'];
        echo "📊 Clientes após limpeza forçada: {$total_final}<br>";
        
        if ($total_final == 0) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;'>";
            echo "<h4>🎉 LIMPEZA CONCLUÍDA!</h4>";
            echo "Todos os clientes foram removidos com sucesso.";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545;'>";
    echo "<h3>❌ ERRO</h3>";
    echo "Erro: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<br><br>";
echo "<div style='text-align: center;'>";
echo "<a href='public/index.php?path=/crm' style='background: #007fa3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>🔄 VERIFICAR CRM NOVAMENTE</a>";
echo "</div>";
?>
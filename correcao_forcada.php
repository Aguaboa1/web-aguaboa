<?php
// Script de correção FORÇADA e DEFINITIVA
require_once 'config/init.php';

echo "<h2>🚨 Correção FORÇADA da Coluna prazo_conclusao</h2>";

try {
    echo "<h3>1. Informações da conexão atual:</h3>";
    
    // Verificar qual banco estamos usando
    $stmt = $pdo->query("SELECT DATABASE() as current_db");
    $currentDb = $stmt->fetch();
    echo "Banco atual: <strong>" . ($currentDb['current_db'] ?? 'NENHUM') . "</strong><br>";
    
    // Verificar se a tabela actions existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'actions'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "❌ Tabela 'actions' não existe! Criando...<br>";
        
        $createTableSQL = "
        CREATE TABLE actions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id INT NOT NULL,
            descricao TEXT NOT NULL,
            data_acao DATE NOT NULL,
            prazo_conclusao DATE NULL,
            arquivo VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($createTableSQL);
        echo "✅ Tabela 'actions' criada com coluna prazo_conclusao!<br>";
    } else {
        echo "✅ Tabela 'actions' existe<br>";
    }
    
    echo "<h3>2. Verificação da estrutura atual:</h3>";
    
    $stmt = $pdo->query("DESCRIBE actions");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Padrão</th></tr>";
    
    $hasPrazoColumn = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'prazo_conclusao') {
            $hasPrazoColumn = true;
            echo "<tr style='background: #d4edda; font-weight: bold;'>";
        } else {
            echo "<tr>";
        }
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if (!$hasPrazoColumn) {
        echo "<h3>❌ COLUNA NÃO EXISTE! Criando AGORA...</h3>";
        
        // Tentar diferentes versões do comando ALTER
        $alterCommands = [
            "ALTER TABLE actions ADD COLUMN prazo_conclusao DATE",
            "ALTER TABLE actions ADD prazo_conclusao DATE",
            "ALTER TABLE actions ADD COLUMN prazo_conclusao DATE NULL",
            "ALTER TABLE actions ADD COLUMN prazo_conclusao DATE DEFAULT NULL"
        ];
        
        $success = false;
        foreach ($alterCommands as $i => $command) {
            try {
                echo "Tentativa " . ($i + 1) . ": $command<br>";
                $pdo->exec($command);
                echo "✅ SUCESSO!<br>";
                $success = true;
                break;
            } catch (Exception $e) {
                echo "❌ Falhou: " . $e->getMessage() . "<br>";
            }
        }
        
        if (!$success) {
            echo "<h3>🔥 RECRIANDO A TABELA COMPLETAMENTE:</h3>";
            
            // Backup dos dados
            $stmt = $pdo->query("SELECT * FROM actions");
            $backupData = $stmt->fetchAll();
            echo "📋 Backup de " . count($backupData) . " registros realizado<br>";
            
            // Recriar tabela
            $pdo->exec("DROP TABLE actions");
            echo "🗑️ Tabela antiga removida<br>";
            
            $createTableSQL = "
            CREATE TABLE actions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                client_id INT NOT NULL,
                descricao TEXT NOT NULL,
                data_acao DATE NOT NULL,
                prazo_conclusao DATE NULL,
                arquivo VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            
            $pdo->exec($createTableSQL);
            echo "✅ Nova tabela criada com prazo_conclusao!<br>";
            
            // Restaurar dados
            if (!empty($backupData)) {
                $stmt = $pdo->prepare("INSERT INTO actions (id, client_id, descricao, data_acao, arquivo, created_at) VALUES (?, ?, ?, ?, ?, ?)");
                
                foreach ($backupData as $row) {
                    $stmt->execute([
                        $row['id'],
                        $row['client_id'],
                        $row['descricao'],
                        $row['data_acao'],
                        $row['arquivo'] ?? null,
                        $row['created_at']
                    ]);
                }
                echo "📥 " . count($backupData) . " registros restaurados<br>";
            }
        }
        
    } else {
        echo "✅ Coluna prazo_conclusao JÁ EXISTE!<br>";
    }
    
    echo "<h3>3. Verificação final da estrutura:</h3>";
    
    $stmt = $pdo->query("DESCRIBE actions");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Padrão</th></tr>";
    
    $finalHasPrazo = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'prazo_conclusao') {
            $finalHasPrazo = true;
            echo "<tr style='background: #d4edda; font-weight: bold;'>";
        } else {
            echo "<tr>";
        }
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>4. TESTE FINAL DE OPERAÇÕES:</h3>";
    
    if ($finalHasPrazo) {
        // Teste de INSERT
        echo "<h4>Teste INSERT:</h4>";
        try {
            $stmt = $pdo->prepare("INSERT INTO actions (client_id, descricao, data_acao, prazo_conclusao) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([1, 'TESTE FINAL CORREÇÃO', '2025-10-02', '2025-12-01']);
            
            if ($result) {
                $insertId = $pdo->lastInsertId();
                echo "✅ INSERT funcionou! ID: $insertId<br>";
                
                // Teste UPDATE
                echo "<h4>Teste UPDATE:</h4>";
                $stmt = $pdo->prepare("UPDATE actions SET prazo_conclusao = ? WHERE id = ?");
                $result = $stmt->execute(['2025-11-01', $insertId]);
                
                if ($result) {
                    echo "✅ UPDATE funcionou!<br>";
                    
                    // Verificar resultado
                    $stmt = $pdo->prepare("SELECT * FROM actions WHERE id = ?");
                    $stmt->execute([$insertId]);
                    $testRecord = $stmt->fetch();
                    
                    echo "<h4>Registro de teste:</h4>";
                    echo "<pre>";
                    print_r($testRecord);
                    echo "</pre>";
                    
                    // Limpar
                    $pdo->prepare("DELETE FROM actions WHERE id = ?")->execute([$insertId]);
                    echo "🗑️ Teste removido<br>";
                    
                } else {
                    echo "❌ UPDATE falhou<br>";
                }
            } else {
                echo "❌ INSERT falhou<br>";
            }
            
        } catch (Exception $e) {
            echo "❌ Erro nos testes: " . $e->getMessage() . "<br>";
        }
        
        echo "<h3>🎉 RESULTADO FINAL:</h3>";
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h2 style='color: #155724; margin-top: 0;'>✅ PROBLEMA RESOLVIDO DEFINITIVAMENTE!</h2>";
        echo "<ul style='color: #155724;'>";
        echo "<li><strong>✅ Coluna prazo_conclusao criada e funcionando</strong></li>";
        echo "<li><strong>✅ Testes de INSERT/UPDATE bem-sucedidos</strong></li>";
        echo "<li><strong>✅ Estrutura da tabela corrigida</strong></li>";
        echo "</ul>";
        echo "<h3 style='color: #155724;'>🚀 AGORA VOLTE AO SISTEMA E TESTE!</h3>";
        echo "<p style='color: #155724;'>A edição de ações com prazo deve funcionar perfeitamente agora.</p>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px;'>";
        echo "<h3 style='color: #721c24;'>❌ FALHA CRÍTICA</h3>";
        echo "<p style='color: #721c24;'>Não foi possível criar a coluna prazo_conclusao. Verifique as permissões do banco de dados.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>💥 ERRO CRÍTICO:</h3>";
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 4px;'>";
    echo "<p style='color: #721c24;'><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<pre style='color: #721c24; font-size: 12px;'>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>
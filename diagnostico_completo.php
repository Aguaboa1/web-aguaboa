<?php
// Diagnóstico completo do problema
require_once 'config/init.php';

echo "<h2>🔍 Diagnóstico Completo do Erro</h2>";

try {
    echo "<h3>1. Verificação da conexão com o banco:</h3>";
    echo "Host: " . (defined('DB_HOST') ? DB_HOST : 'não definido') . "<br>";
    echo "Database: " . (defined('DB_NAME') ? DB_NAME : 'não definido') . "<br>";
    echo "Conexão: " . ($pdo ? "✅ Ativa" : "❌ Falhou") . "<br>";
    
    echo "<h3>2. Verificação da tabela actions:</h3>";
    
    // Listar todas as tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tabelas no banco: " . implode(', ', $tables) . "<br>";
    
    if (in_array('actions', $tables)) {
        echo "✅ Tabela 'actions' existe<br>";
        
        // Mostrar estrutura completa da tabela
        echo "<h4>Estrutura da tabela actions:</h4>";
        $stmt = $pdo->query("DESCRIBE actions");
        $columns = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
        
        $hasPrazoColumn = false;
        foreach ($columns as $column) {
            if ($column['Field'] === 'prazo_conclusao') {
                $hasPrazoColumn = true;
                echo "<tr style='background: #d4edda;'>";
            } else {
                echo "<tr>";
            }
            echo "<td><strong>{$column['Field']}</strong></td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if ($hasPrazoColumn) {
            echo "✅ Coluna 'prazo_conclusao' está presente na tabela<br>";
        } else {
            echo "❌ Coluna 'prazo_conclusao' NÃO está presente na tabela<br>";
            echo "🔧 Tentando criar a coluna...<br>";
            
            try {
                $pdo->exec("ALTER TABLE actions ADD COLUMN prazo_conclusao DATE AFTER data_acao");
                echo "✅ Coluna criada com sucesso!<br>";
            } catch (Exception $e) {
                echo "❌ Erro ao criar coluna: " . $e->getMessage() . "<br>";
            }
        }
        
    } else {
        echo "❌ Tabela 'actions' NÃO existe!<br>";
        
        echo "<h4>Criando tabela actions:</h4>";
        $createTableSQL = "
        CREATE TABLE actions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id INT NOT NULL,
            descricao TEXT NOT NULL,
            data_acao DATE NOT NULL,
            prazo_conclusao DATE,
            arquivo VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
        )";
        
        try {
            $pdo->exec($createTableSQL);
            echo "✅ Tabela 'actions' criada com sucesso!<br>";
        } catch (Exception $e) {
            echo "❌ Erro ao criar tabela: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<h3>3. Teste de operações SQL:</h3>";
    
    // Teste de SELECT
    echo "<h4>Teste SELECT:</h4>";
    try {
        $stmt = $pdo->query("SELECT id, descricao, data_acao, prazo_conclusao FROM actions LIMIT 1");
        $result = $stmt->fetch();
        if ($result) {
            echo "✅ SELECT funcionou:<br>";
            echo "<pre>";
            print_r($result);
            echo "</pre>";
        } else {
            echo "⚠️ SELECT funcionou mas não retornou dados<br>";
        }
    } catch (Exception $e) {
        echo "❌ Erro no SELECT: " . $e->getMessage() . "<br>";
    }
    
    // Teste de INSERT
    echo "<h4>Teste INSERT:</h4>";
    try {
        $stmt = $pdo->prepare("INSERT INTO actions (client_id, descricao, data_acao, prazo_conclusao) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([1, 'Teste diagnóstico', '2025-10-02', '2025-12-01']);
        
        if ($result) {
            echo "✅ INSERT funcionou!<br>";
            $insertId = $pdo->lastInsertId();
            echo "ID inserido: $insertId<br>";
            
            // Remover o teste
            $pdo->exec("DELETE FROM actions WHERE id = $insertId");
            echo "🗑️ Registro de teste removido<br>";
        } else {
            echo "❌ INSERT falhou<br>";
        }
    } catch (Exception $e) {
        echo "❌ Erro no INSERT: " . $e->getMessage() . "<br>";
    }
    
    // Teste de UPDATE
    echo "<h4>Teste UPDATE:</h4>";
    try {
        $stmt = $pdo->prepare("UPDATE actions SET prazo_conclusao = ? WHERE id = 1");
        $result = $stmt->execute(['2025-12-01']);
        
        if ($result) {
            echo "✅ UPDATE funcionou!<br>";
            echo "Linhas afetadas: " . $stmt->rowCount() . "<br>";
        } else {
            echo "❌ UPDATE falhou<br>";
        }
    } catch (Exception $e) {
        echo "❌ Erro no UPDATE: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>4. Verificação do arquivo config/database.php:</h3>";
    if (file_exists('config/database.php')) {
        echo "✅ Arquivo config/database.php existe<br>";
        $configContent = file_get_contents('config/database.php');
        if (strpos($configContent, 'DB_NAME') !== false) {
            echo "✅ Configurações encontradas no arquivo<br>";
        } else {
            echo "⚠️ Configurações podem estar incompletas<br>";
        }
    } else {
        echo "❌ Arquivo config/database.php não encontrado<br>";
    }
    
    echo "<h3>5. Teste da classe Action:</h3>";
    try {
        require_once 'src/models/Action.php';
        $actionModel = new Action();
        echo "✅ Classe Action carregada com sucesso<br>";
        
        // Teste do método findById
        try {
            $action = $actionModel->findById(1);
            if ($action) {
                echo "✅ Método findById funcionou<br>";
                echo "<pre>";
                print_r($action);
                echo "</pre>";
            } else {
                echo "⚠️ Método findById funcionou mas não encontrou registro<br>";
            }
        } catch (Exception $e) {
            echo "❌ Erro no método findById: " . $e->getMessage() . "<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Erro ao carregar classe Action: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>6. Última verificação - estrutura atual:</h3>";
    $stmt = $pdo->query("SHOW CREATE TABLE actions");
    $createTable = $stmt->fetch();
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px;'>";
    echo htmlspecialchars($createTable['Create Table']);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Erro Fatal:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<pre style='background: #f8d7da; padding: 10px;'>";
    echo $e->getTraceAsString();
    echo "</pre>";
}

echo "<h3>✅ Diagnóstico concluído</h3>";
?>
<?php
/**
 * Script de debug para testar upload de envase
 */

require_once '../config/init.php';

// Simular sessão
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

$envaseController = new EnvaseController();

// Testar dados de exemplo
echo "<h2>🧪 Teste de Upload de Envase</h2>";

// Criar arquivo CSV de teste
$csvContent = "Empresa,Cidade,Produto,Ano,Mes,Dia,Quantidade\n";
$csvContent .= "Aguaboa Distribuidora,São Paulo,Água 500ml,2025,10,1,1200\n";
$csvContent .= "Aguaboa Distribuidora,São Paulo,Água 1L,2025,10,1,800\n";
$csvContent .= "Distribuidora ABC,Rio de Janeiro,Água 500ml,2025,10,2,950\n";
$csvContent .= "Empresa XYZ,Belo Horizonte,Água 500ml,2025,10,3,750\n";

$testFile = '../public/uploads/teste_envase.csv';
file_put_contents($testFile, $csvContent);

echo "<p>✅ Arquivo CSV criado: $testFile</p>";

// Verificar se o arquivo existe
if (file_exists($testFile)) {
    echo "<p>✅ Arquivo existe</p>";
    
    // Tentar ler o arquivo
    try {
        $reflection = new ReflectionClass($envaseController);
        $method = $reflection->getMethod('lerPlanilhaSimples');
        $method->setAccessible(true);
        
        $dados = $method->invoke($envaseController, $testFile);
        
        echo "<h3>📋 Dados lidos do arquivo:</h3>";
        echo "<pre>" . print_r($dados, true) . "</pre>";
        
        if (!empty($dados)) {
            echo "<p>✅ " . count($dados) . " registros encontrados</p>";
            
            // Tentar processar os dados
            foreach ($dados as $index => $registro) {
                echo "<h4>Processando registro " . ($index + 1) . ":</h4>";
                echo "<pre>" . print_r($registro, true) . "</pre>";
                
                // Verificar se cliente existe
                $clientModel = new Client();
                $clienteExistente = $clientModel->findByName($registro['empresa']);
                
                if ($clienteExistente) {
                    echo "<p>✅ Cliente já existe: " . $clienteExistente['cliente'] . "</p>";
                } else {
                    echo "<p>⚠️ Cliente não existe, seria criado: " . $registro['empresa'] . "</p>";
                    
                    // Criar cliente
                    try {
                        $clientModel->createFromEnvase($registro['empresa'], $registro['cidade']);
                        echo "<p>✅ Cliente criado com sucesso!</p>";
                    } catch (Exception $e) {
                        echo "<p>❌ Erro ao criar cliente: " . $e->getMessage() . "</p>";
                    }
                }
                
                // Tentar inserir dados de envase
                $envaseModel = new Envase();
                try {
                    if ($envaseModel->upsert($registro)) {
                        echo "<p>✅ Dados de envase inseridos com sucesso!</p>";
                    } else {
                        echo "<p>❌ Erro ao inserir dados de envase</p>";
                    }
                } catch (Exception $e) {
                    echo "<p>❌ Erro ao inserir envase: " . $e->getMessage() . "</p>";
                }
                
                echo "<hr>";
            }
        } else {
            echo "<p>❌ Nenhum dado encontrado</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Erro ao processar: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ Arquivo não encontrado</p>";
}

// Verificar dados no banco
echo "<h3>🗄️ Dados no Banco de Dados:</h3>";

try {
    $db = Database::getInstance()->getConnection();
    
    // Contar clientes
    $stmt = $db->query("SELECT COUNT(*) as total FROM clients");
    $totalClientes = $stmt->fetch()['total'];
    echo "<p>📊 Total de clientes: $totalClientes</p>";
    
    // Contar dados de envase
    $stmt = $db->query("SELECT COUNT(*) as total FROM envase_data");
    $totalEnvase = $stmt->fetch()['total'];
    echo "<p>📊 Total de registros de envase: $totalEnvase</p>";
    
    // Mostrar últimos clientes
    $stmt = $db->query("SELECT * FROM clients ORDER BY created_at DESC LIMIT 5");
    $clientes = $stmt->fetchAll();
    
    if ($clientes) {
        echo "<h4>👥 Últimos clientes cadastrados:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Cliente</th><th>Empresa</th><th>Cidade</th><th>Tipo</th><th>Data</th></tr>";
        foreach ($clientes as $cliente) {
            echo "<tr>";
            echo "<td>" . $cliente['id'] . "</td>";
            echo "<td>" . htmlspecialchars($cliente['cliente']) . "</td>";
            echo "<td>" . htmlspecialchars($cliente['empresa']) . "</td>";
            echo "<td>" . htmlspecialchars($cliente['cidade']) . "</td>";
            echo "<td>" . htmlspecialchars($cliente['tipo_cliente']) . "</td>";
            echo "<td>" . $cliente['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Mostrar dados de envase
    $stmt = $db->query("SELECT * FROM envase_data ORDER BY data_upload DESC LIMIT 5");
    $envases = $stmt->fetchAll();
    
    if ($envases) {
        echo "<h4>📦 Últimos dados de envase:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Empresa</th><th>Produto</th><th>Data</th><th>Quantidade</th><th>Criado em</th></tr>";
        foreach ($envases as $envase) {
            echo "<tr>";
            echo "<td>" . $envase['id'] . "</td>";
            echo "<td>" . htmlspecialchars($envase['empresa']) . "</td>";
            echo "<td>" . htmlspecialchars($envase['produto']) . "</td>";
            echo "<td>" . sprintf('%02d/%02d/%d', $envase['dia'], $envase['mes'], $envase['ano']) . "</td>";
            echo "<td>" . number_format($envase['quantidade']) . "</td>";
            echo "<td>" . $envase['data_upload'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro ao consultar banco: " . $e->getMessage() . "</p>";
}

echo "<p><a href='../public/'>← Voltar ao sistema</a></p>";
?>
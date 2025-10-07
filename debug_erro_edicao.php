<?php
// Debug específico para o erro de edição
require_once 'config/init.php';

// Simular sessão
session_start();
$_SESSION['user_id'] = 1;

echo "<h2>🔍 Debug Erro de Edição</h2>";

// Simular exatamente os dados do formulário
$_POST = [
    'descricao' => '5% de desconto',
    'data_acao' => '2025-10-01',
    'prazo_conclusao' => '2025-12'  // Como vem do formulário
];

echo "<h3>1. Dados recebidos:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// Verificar se a ação existe
echo "<h3>2. Verificando ação ID 1:</h3>";
try {
    require_once 'src/controllers/ActionsController.php';
    $controller = new ActionsController();
    
    $action = $controller->actionModel->findById(1);
    if ($action) {
        echo "✅ Ação encontrada:<br>";
        echo "<pre>";
        print_r($action);
        echo "</pre>";
    } else {
        echo "❌ Ação não encontrada!<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Erro ao buscar ação: " . $e->getMessage() . "<br>";
    exit;
}

// Testar processamento do prazo
echo "<h3>3. Processamento do prazo:</h3>";
$prazoConlusao = $_POST['prazo_conclusao'] ?? null;
echo "Prazo original: " . ($prazoConlusao ?? 'null') . "<br>";

if ($prazoConlusao) {
    $prazoConlusao = $prazoConlusao . '-01';
    echo "Prazo processado: " . $prazoConlusao . "<br>";
} else {
    echo "Sem prazo definido<br>";
}

// Testar update direto no modelo
echo "<h3>4. Teste update no modelo:</h3>";
try {
    $resultado = $controller->actionModel->update(
        1,  // ID
        $_POST['descricao'],
        $_POST['data_acao'],
        null,  // arquivo
        $prazoConlusao
    );
    
    if ($resultado) {
        echo "✅ Update no modelo funcionou!<br>";
        
        // Verificar resultado
        $actionAtualizada = $controller->actionModel->findById(1);
        echo "<h4>Ação após update:</h4>";
        echo "<pre>";
        print_r($actionAtualizada);
        echo "</pre>";
    } else {
        echo "❌ Update no modelo falhou!<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro no update: " . $e->getMessage() . "<br>";
    echo "<pre>Stack trace: " . $e->getTraceAsString() . "</pre>";
}

// Testar método update do controller
echo "<h3>5. Teste método update do controller:</h3>";
ob_start();
try {
    // Simular requisição POST
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Capturar saída
    $controller->update(1);
    $output = ob_get_contents();
} catch (Exception $e) {
    $output = "Erro: " . $e->getMessage();
    echo "<pre>Stack trace: " . $e->getTraceAsString() . "</pre>";
} finally {
    ob_end_clean();
}

echo "<h4>Saída do controller:</h4>";
echo "<pre>$output</pre>";

// Verificar estrutura da tabela
echo "<h3>6. Estrutura da tabela actions:</h3>";
try {
    $stmt = $pdo->query("DESCRIBE actions");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Key']}<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro ao verificar tabela: " . $e->getMessage() . "<br>";
}

echo "<h3>7. Teste SQL direto:</h3>";
try {
    $sql = "UPDATE actions SET descricao = ?, data_acao = ?, prazo_conclusao = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $params = [$_POST['descricao'], $_POST['data_acao'], $prazoConlusao, 1];
    
    echo "SQL: $sql<br>";
    echo "Params: ";
    print_r($params);
    echo "<br>";
    
    $resultado = $stmt->execute($params);
    
    if ($resultado) {
        echo "✅ SQL direto funcionou!<br>";
        echo "Linhas afetadas: " . $stmt->rowCount() . "<br>";
    } else {
        echo "❌ SQL direto falhou!<br>";
        print_r($stmt->errorInfo());
    }
} catch (Exception $e) {
    echo "❌ Erro SQL: " . $e->getMessage() . "<br>";
}

echo "<h3>✅ Debug concluído</h3>";
?>
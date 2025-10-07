<?php
require_once '../config/init.php';
require_once '../src/controllers/ActionsController.php';

session_start();

// Simular sessão de usuário
$_SESSION['user_id'] = 1;

// Verificar se temos o action ID
$actionId = 1;

echo "<h2>🔍 Debug Completo - Edição de Ação</h2>";

// 1. Verificar se a ação existe
echo "<h3>1. Verificando ação existente:</h3>";
$controller = new ActionsController();

try {
    $action = $controller->actionModel->findById($actionId);
    if ($action) {
        echo "<pre>✅ Ação encontrada:\n";
        print_r($action);
        echo "</pre>";
    } else {
        echo "❌ Ação não encontrada!";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Erro ao buscar ação: " . $e->getMessage();
    exit;
}

// 2. Simular dados de update
echo "<h3>2. Simulando update via POST:</h3>";

// Salvar POST original
$originalPost = $_POST;

// Simular dados de POST
$_POST = [
    'descricao' => 'Descrição atualizada via teste - ' . date('Y-m-d H:i:s'),
    'data_acao' => date('Y-m-d')
];

echo "<pre>📤 Dados sendo enviados:\n";
print_r($_POST);
echo "</pre>";

// 3. Testar update direto no modelo
echo "<h3>3. Testando update direto no modelo:</h3>";

try {
    $resultado = $controller->actionModel->update(
        $actionId, 
        $_POST['descricao'], 
        $_POST['data_acao'], 
        null // sem arquivo
    );
    
    if ($resultado) {
        echo "✅ Update no modelo funcionou!<br>";
        
        // Verificar se foi realmente atualizado
        $actionAtualizada = $controller->actionModel->findById($actionId);
        echo "<pre>📋 Ação após update:\n";
        print_r($actionAtualizada);
        echo "</pre>";
    } else {
        echo "❌ Update no modelo falhou!<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro no update do modelo: " . $e->getMessage() . "<br>";
}

// 4. Testar método update do controller (capturando saída)
echo "<h3>4. Testando método update do controller:</h3>";

// Capturar a saída do controller
ob_start();
try {
    $controller->update($actionId);
    $output = ob_get_contents();
} catch (Exception $e) {
    $output = "Erro: " . $e->getMessage();
} finally {
    ob_end_clean();
}

echo "<pre>📤 Saída do controller:\n$output</pre>";

// Restaurar POST original
$_POST = $originalPost;

echo "<h3>5. Resultado Final:</h3>";
echo "<p>✅ Teste concluído. Verifique os resultados acima.</p>";
?>
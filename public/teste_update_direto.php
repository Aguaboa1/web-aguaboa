<?php
// Teste direto da rota de update
require_once '../config/init.php';

// Simular sessão
session_start();
$_SESSION['user_id'] = 1;

echo "<h2>🧪 Teste Direto de Update</h2>";

// Simular dados POST
$_POST = [
    'descricao' => 'Teste direto update - ' . date('H:i:s'),
    'data_acao' => date('Y-m-d')
];

echo "<h3>1. Dados simulados:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>2. Informações da requisição:</h3>";
echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'Não definido') . "<br>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'Não definido') . "<br>";
echo "Session user_id: " . ($_SESSION['user_id'] ?? 'Não definido') . "<br>";

echo "<h3>3. Testando controller diretamente:</h3>";

try {
    require_once '../src/controllers/ActionsController.php';
    $controller = new ActionsController();
    
    echo "✅ Controller instanciado<br>";
    
    // Verificar se a ação existe primeiro
    $action = $controller->actionModel->findById(1);
    if ($action) {
        echo "✅ Ação ID 1 existe<br>";
        echo "<pre>Ação atual: " . print_r($action, true) . "</pre>";
        
        // Tentar o update
        echo "<h4>Executando update...</h4>";
        
        // Capturar saída
        ob_start();
        try {
            $controller->update(1);
        } catch (Exception $e) {
            echo "Erro no controller: " . $e->getMessage();
        }
        $output = ob_get_clean();
        
        echo "<h4>Resultado do update:</h4>";
        echo "<pre>$output</pre>";
        
        // Verificar se foi atualizado
        $actionAtualizada = $controller->actionModel->findById(1);
        echo "<h4>Ação após update:</h4>";
        echo "<pre>" . print_r($actionAtualizada, true) . "</pre>";
        
    } else {
        echo "❌ Ação ID 1 não existe<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>4. Teste concluído</h3>";
?>
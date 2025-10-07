<?php
// Teste direto do EnvaseController

require_once '../config/init.php';

// Simular sessão
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'Branco';
$_SESSION['role'] = 'admin';

echo "=== TESTE ENVASE CONTROLLER ===" . PHP_EOL;

try {
    echo "1. Testando criação do controller..." . PHP_EOL;
    $controller = new EnvaseController();
    echo "✅ Controller criado com sucesso!" . PHP_EOL;
    
    echo "2. Testando método clientData..." . PHP_EOL;
    
    // Capturar saída
    ob_start();
    $controller->clientData(4);
    $output = ob_get_clean();
    
    echo "✅ Método executado!" . PHP_EOL;
    echo "Tamanho da saída: " . strlen($output) . " bytes" . PHP_EOL;
    
    if (strlen($output) > 1000) {
        echo "✅ Página gerada com sucesso!" . PHP_EOL;
    } else {
        echo "⚠️ Saída muito pequena, pode ter erro" . PHP_EOL;
        echo "Primeiros 500 chars: " . substr($output, 0, 500) . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}

echo "=== FIM ===" . PHP_EOL;
?>
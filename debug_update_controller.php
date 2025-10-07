<?php
// Debug específico para o método update
require_once 'config/init.php';
require_once 'src/controllers/ActionsController.php';

// Simular sessão
session_start();
$_SESSION['user_id'] = 1;

// Habilitar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Debug Método Update - ActionsController</h2>";

// Simular dados exatos do formulário
$_POST = [
    'descricao' => '5% de desconto',
    'data_acao' => '2025-10-01',
    'prazo_conclusao' => '2025-12'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

echo "<h3>1. Dados simulados:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>2. Variáveis de servidor:</h3>";
echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'não definido') . "<br>";
echo "user_id da sessão: " . ($_SESSION['user_id'] ?? 'não definido') . "<br>";

echo "<h3>3. Teste da função sanitize:</h3>";
try {
    $descricaoSanitizada = sanitize($_POST['descricao']);
    echo "Descrição original: '{$_POST['descricao']}'<br>";
    echo "Descrição sanitizada: '$descricaoSanitizada'<br>";
} catch (Exception $e) {
    echo "❌ Erro na função sanitize: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Instanciando controller:</h3>";
try {
    $controller = new ActionsController();
    echo "✅ Controller instanciado com sucesso<br>";
} catch (Exception $e) {
    echo "❌ Erro ao instanciar controller: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h3>5. Verificando ação existente:</h3>";
try {
    $action = $controller->actionModel->findById(1);
    if ($action) {
        echo "✅ Ação ID 1 encontrada<br>";
        echo "<pre>";
        print_r($action);
        echo "</pre>";
    } else {
        echo "❌ Ação ID 1 não encontrada<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Erro ao buscar ação: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h3>6. Processamento de dados:</h3>";
$descricao = sanitize($_POST['descricao'] ?? '');
$dataAcao = $_POST['data_acao'] ?? '';
$prazoConlusao = $_POST['prazo_conclusao'] ?? null;

echo "Descrição processada: '$descricao'<br>";
echo "Data processada: '$dataAcao'<br>";
echo "Prazo original: '$prazoConlusao'<br>";

if ($prazoConlusao) {
    $prazoConlusao = $prazoConlusao . '-01';
    echo "Prazo final: '$prazoConlusao'<br>";
}

echo "<h3>7. Teste do método update do modelo:</h3>";
try {
    $resultado = $controller->actionModel->update(1, $descricao, $dataAcao, null, $prazoConlusao);
    
    if ($resultado) {
        echo "✅ Update do modelo funcionou!<br>";
        
        // Verificar resultado
        $actionAtualizada = $controller->actionModel->findById(1);
        echo "<h4>Ação após update:</h4>";
        echo "<pre>";
        print_r($actionAtualizada);
        echo "</pre>";
    } else {
        echo "❌ Update do modelo falhou!<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro no update do modelo: " . $e->getMessage() . "<br>";
    echo "<pre>Stack trace: " . $e->getTraceAsString() . "</pre>";
}

echo "<h3>8. Teste do método update do controller:</h3>";
try {
    // Capturar output
    ob_start();
    
    // Executar método update
    $controller->update(1);
    
    $output = ob_get_clean();
    
    echo "<h4>Output do controller:</h4>";
    echo "<pre>$output</pre>";
    
    // Tentar decodificar como JSON
    $json = json_decode($output, true);
    if ($json) {
        echo "<h4>JSON decodificado:</h4>";
        echo "<pre>";
        print_r($json);
        echo "</pre>";
    } else {
        echo "<h4>❌ Output não é JSON válido</h4>";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ Erro no método update do controller: " . $e->getMessage() . "<br>";
    echo "<pre>Stack trace: " . $e->getTraceAsString() . "</pre>";
}

echo "<h3>✅ Debug concluído</h3>";
?>
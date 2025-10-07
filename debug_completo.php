<?php
/**
 * Debug completo do fluxo de upload
 */

require_once 'config/init.php';

$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

echo "<h2>🔍 DEBUG COMPLETO DO UPLOAD</h2>";

// Verificar arquivos recentes no diretório de uploads
$uploadDir = 'public/uploads/';
$arquivos = glob($uploadDir . '*');

if (!empty($arquivos)) {
    // Ordenar por data de modificação
    usort($arquivos, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    echo "<h3>📁 Últimos arquivos no diretório uploads:</h3>";
    echo "<ul>";
    foreach (array_slice($arquivos, 0, 5) as $arquivo) {
        $tamanho = filesize($arquivo);
        $data = date('d/m/Y H:i:s', filemtime($arquivo));
        echo "<li><strong>" . basename($arquivo) . "</strong> - " . number_format($tamanho) . " bytes - $data</li>";
    }
    echo "</ul>";
}

// Verificar logs de upload do Apache
echo "<h3>📋 Logs recentes do Apache (uploads):</h3>";
$logFile = 'C:\\xampp\\apache\\logs\\error.log';

if (file_exists($logFile)) {
    $linhas = file($logFile);
    $linhasRelevantes = [];
    
    foreach ($linhas as $linha) {
        if (stripos($linha, 'relatorio') !== false || 
            stripos($linha, 'upload') !== false ||
            stripos($linha, 'xls') !== false ||
            stripos($linha, 'processando') !== false) {
            $linhasRelevantes[] = $linha;
        }
    }
    
    echo "<div style='background: #f8f9fa; padding: 1rem; border-radius: 5px; max-height: 300px; overflow-y: auto;'>";
    echo "<pre style='font-size: 0.8rem;'>";
    foreach (array_slice($linhasRelevantes, -20) as $linha) {
        echo htmlspecialchars($linha);
    }
    echo "</pre>";
    echo "</div>";
}

// Simular o processamento exato que acontece no upload
echo "<h3>🧪 Simulação do Processamento Real:</h3>";

try {
    // Criar um arquivo de teste exatamente como seria no upload
    $arquivoTeste = 'public/uploads/RelatorioOLAP_teste.xls';
    
    // Simular conteúdo binário Excel
    $conteudoExcel = pack("H*", "D0CF11E0A1B11AE1"); // Header Excel
    $conteudoExcel .= "Dados simulados do RelatorioOLAP";
    file_put_contents($arquivoTeste, $conteudoExcel);
    
    echo "<p>✅ Arquivo teste criado: " . basename($arquivoTeste) . "</p>";
    
    // Testar o fluxo exato do processarPlanilha
    $envaseController = new EnvaseController();
    $reflection = new ReflectionClass($envaseController);
    
    // 1. Testar lerPlanilhaSimples
    echo "<h4>🔧 Teste 1: lerPlanilhaSimples()</h4>";
    $methodLer = $reflection->getMethod('lerPlanilhaSimples');
    $methodLer->setAccessible(true);
    
    $dadosLidos = $methodLer->invoke($envaseController, $arquivoTeste);
    echo "<p>Resultado lerPlanilhaSimples: " . count($dadosLidos) . " registros</p>";
    
    if (count($dadosLidos) > 0) {
        echo "<p style='color: green;'>✅ lerPlanilhaSimples está funcionando!</p>";
        echo "<details><summary>Ver primeiros 3 registros</summary>";
        echo "<pre>" . print_r(array_slice($dadosLidos, 0, 3), true) . "</pre>";
        echo "</details>";
    } else {
        echo "<p style='color: red;'>❌ lerPlanilhaSimples retornou vazio</p>";
        
        // Testar método obrigatório diretamente
        echo "<h4>🔧 Teste 2: gerarDadosObrigatoriosOLAP()</h4>";
        $methodObrig = $reflection->getMethod('gerarDadosObrigatoriosOLAP');
        $methodObrig->setAccessible(true);
        
        $dadosObrigatorios = $methodObrig->invoke($envaseController, 'RelatorioOLAP_teste.xls');
        echo "<p>Resultado gerarDadosObrigatoriosOLAP: " . count($dadosObrigatorios) . " registros</p>";
        
        if (count($dadosObrigatorios) > 0) {
            echo "<p style='color: green;'>✅ Método obrigatório funciona!</p>";
            echo "<details><summary>Ver amostra</summary>";
            echo "<pre>" . print_r(array_slice($dadosObrigatorios, 0, 2), true) . "</pre>";
            echo "</details>";
        }
    }
    
    // 3. Testar o processarPlanilha completo
    echo "<h4>🔧 Teste 3: processarPlanilha() completo</h4>";
    $methodProcessar = $reflection->getMethod('processarPlanilha');
    $methodProcessar->setAccessible(true);
    
    $resultado = $methodProcessar->invoke($envaseController, $arquivoTeste, 'RelatorioOLAP_teste.xls');
    
    echo "<div style='background: " . ($resultado['sucesso'] ? '#d4edda' : '#f8d7da') . "; padding: 1rem; border-radius: 5px;'>";
    echo "<h5>" . ($resultado['sucesso'] ? '✅ SUCESSO' : '❌ FALHA') . "</h5>";
    echo "<p><strong>Registros:</strong> " . ($resultado['registros'] ?? 0) . "</p>";
    
    if (!$resultado['sucesso']) {
        echo "<p><strong>Erro:</strong> " . $resultado['erro'] . "</p>";
    }
    
    if (!empty($resultado['erros'])) {
        echo "<p><strong>Erros adicionais:</strong> " . count($resultado['erros']) . "</p>";
    }
    echo "</div>";
    
    // Limpar arquivo teste
    unlink($arquivoTeste);
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 1rem; border-radius: 5px;'>";
    echo "<h4>❌ Erro na simulação</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='background: #fff3cd; padding: 1.5rem; border-radius: 8px;'>";
echo "<h3>💡 Próximos Passos:</h3>";
echo "<p>Para resolver definitivamente, preciso ver:</p>";
echo "<ol>";
echo "<li><strong>Código Python:</strong> Como o sistema Python lê o arquivo RelatorioOLAP.xls</li>";
echo "<li><strong>Estrutura real:</strong> Se você pode salvar como CSV para ver a estrutura exata</li>";
echo "<li><strong>Biblioteca usada:</strong> Se usa pandas, openpyxl, xlrd, etc.</li>";
echo "</ol>";
echo "</div>";

echo "<p><strong>🙏 Pode me mostrar o código Python que lê este arquivo?</strong></p>";
?>
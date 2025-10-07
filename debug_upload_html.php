<?php
/**
 * Debug específico para upload HTML
 * Sistema Aguaboa - Gestão Comercial
 */

require_once 'config/init.php';

echo "<h1>🐛 Debug - Upload HTML</h1>";

try {
    echo "<h2>📊 Status do Sistema</h2>";
    
    // Verificar se há dados no banco
    $envaseModel = new Envase();
    $stats = $envaseModel->getStats();
    
    echo "<div style='background: #f8f9fa; padding: 1rem; border-radius: 6px; margin: 1rem 0;'>";
    echo "<h4>📈 Estatísticas Atuais:</h4>";
    echo "<ul>";
    echo "<li><strong>Total de registros:</strong> " . number_format($stats['total_registros']) . "</li>";
    echo "<li><strong>Empresas únicas:</strong> " . number_format($stats['empresas_unicas']) . "</li>";
    echo "<li><strong>Produtos únicos:</strong> " . number_format($stats['produtos_unicos']) . "</li>";
    echo "<li><strong>Total quantidade:</strong> " . number_format($stats['total_quantidade']) . "</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>🧪 Teste de Processamento HTML</h2>";
    
    // Criar arquivo HTML simples para teste
    $htmlTeste = '<!DOCTYPE html>
<html>
<head><title>Teste</title></head>
<body>
<table>
<tr><th>Empresa</th><th>Cidade</th><th>Produto</th><th>Ano</th><th>Mês</th><th>Dia</th><th>Quantidade</th></tr>
<tr><td>TESTE EMPRESA HTML</td><td>SAO PAULO</td><td>AGUABOA 20L</td><td>2025</td><td>10</td><td>1</td><td>100</td></tr>
<tr><td></td><td></td><td>AGUABOA 10L</td><td></td><td></td><td>2</td><td>50</td></tr>
</table>
</body>
</html>';
    
    // Salvar arquivo temporário
    $arquivoTeste = UPLOAD_DIR . 'debug_teste.html';
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    file_put_contents($arquivoTeste, $htmlTeste);
    
    echo "<div style='background: #e7f3ff; padding: 1rem; border-radius: 6px; margin: 1rem 0;'>";
    echo "<h4>📄 Arquivo HTML de Teste Criado:</h4>";
    echo "<p><strong>Localização:</strong> " . htmlspecialchars($arquivoTeste) . "</p>";
    echo "<p><strong>Tamanho:</strong> " . filesize($arquivoTeste) . " bytes</p>";
    echo "</div>";
    
    // Processar com o EnvaseController
    echo "<h3>🔄 Processando HTML...</h3>";
    
    $envaseController = new EnvaseController();
    
    // Usar reflexão para acessar método privado
    $reflection = new ReflectionClass($envaseController);
    $method = $reflection->getMethod('processarPlanilha');
    $method->setAccessible(true);
    
    echo "<div style='background: #fff3cd; padding: 1rem; border-radius: 6px; margin: 1rem 0;'>";
    echo "<h4>⚙️ Iniciando processamento...</h4>";
    echo "</div>";
    
    $resultado = $method->invoke($envaseController, $arquivoTeste, 'debug_teste.html');
    
    echo "<div style='margin: 1rem 0;'>";
    if ($resultado['sucesso']) {
        echo "<div style='background: #d4edda; padding: 1rem; border-radius: 6px; color: #155724;'>";
        echo "<h4>✅ Processamento Concluído!</h4>";
        echo "<ul>";
        echo "<li><strong>Registros processados:</strong> " . $resultado['registros'] . "</li>";
        echo "<li><strong>Erros:</strong> " . count($resultado['erros']) . "</li>";
        echo "</ul>";
        
        if (!empty($resultado['erros'])) {
            echo "<h5>❌ Erros encontrados:</h5>";
            echo "<ul>";
            foreach ($resultado['erros'] as $erro) {
                echo "<li>" . htmlspecialchars($erro) . "</li>";
            }
            echo "</ul>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 1rem; border-radius: 6px; color: #721c24;'>";
        echo "<h4>❌ Erro no Processamento!</h4>";
        echo "<p>" . htmlspecialchars($resultado['erro']) . "</p>";
        echo "</div>";
    }
    echo "</div>";
    
    // Verificar se os dados foram inseridos
    echo "<h3>📊 Verificando Inserção no Banco</h3>";
    
    $statsApos = $envaseModel->getStats();
    
    echo "<div style='background: #f8f9fa; padding: 1rem; border-radius: 6px; margin: 1rem 0;'>";
    echo "<h4>📈 Estatísticas Após Processamento:</h4>";
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #e9ecef;'>";
    echo "<th style='border: 1px solid #ddd; padding: 0.5rem;'>Métrica</th>";
    echo "<th style='border: 1px solid #ddd; padding: 0.5rem;'>Antes</th>";
    echo "<th style='border: 1px solid #ddd; padding: 0.5rem;'>Depois</th>";
    echo "<th style='border: 1px solid #ddd; padding: 0.5rem;'>Diferença</th>";
    echo "</tr>";
    
    $metricas = [
        'total_registros' => 'Total de Registros',
        'empresas_unicas' => 'Empresas Únicas',
        'produtos_unicos' => 'Produtos Únicos',
        'total_quantidade' => 'Total Quantidade'
    ];
    
    foreach ($metricas as $key => $label) {
        $antes = $stats[$key];
        $depois = $statsApos[$key];
        $diff = $depois - $antes;
        $diffStyle = $diff > 0 ? 'color: green; font-weight: bold;' : 'color: gray;';
        
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'>$label</td>";
        echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'>" . number_format($antes) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'>" . number_format($depois) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 0.5rem; $diffStyle'>+" . number_format($diff) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Buscar registros recentes
    echo "<h3>📋 Últimos Registros Inseridos</h3>";
    
    $sql = "SELECT * FROM envase_data ORDER BY id DESC LIMIT 10";
    $stmt = Database::getInstance()->getConnection()->prepare($sql);
    $stmt->execute();
    $ultimosRegistros = $stmt->fetchAll();
    
    if (!empty($ultimosRegistros)) {
        echo "<table style='width: 100%; border-collapse: collapse; margin: 1rem 0;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='border: 1px solid #ddd; padding: 0.5rem;'>ID</th>";
        echo "<th style='border: 1px solid #ddd; padding: 0.5rem;'>Empresa</th>";
        echo "<th style='border: 1px solid #ddd; padding: 0.5rem;'>Produto</th>";
        echo "<th style='border: 1px solid #ddd; padding: 0.5rem;'>Data</th>";
        echo "<th style='border: 1px solid #ddd; padding: 0.5rem;'>Quantidade</th>";
        echo "<th style='border: 1px solid #ddd; padding: 0.5rem;'>Arquivo</th>";
        echo "</tr>";
        
        foreach ($ultimosRegistros as $registro) {
            $destaque = (strpos($registro['empresa'], 'TESTE') !== false) ? 'background: #fff3cd;' : '';
            
            echo "<tr style='$destaque'>";
            echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'>" . $registro['id'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'>" . htmlspecialchars($registro['empresa']) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'>" . htmlspecialchars($registro['produto']) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'>" . sprintf('%04d-%02d-%02d', $registro['ano'], $registro['mes'], $registro['dia']) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'>" . number_format($registro['quantidade']) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'>" . htmlspecialchars($registro['arquivo_origem']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: #999; font-style: italic;'>Nenhum registro encontrado no banco de dados.</p>";
    }
    
    echo "<h3>🔧 Teste Manual do Método lerHTML</h3>";
    
    // Testar método lerHTML diretamente
    $methodLerHTML = $reflection->getMethod('lerHTML');
    $methodLerHTML->setAccessible(true);
    
    $dadosHTML = $methodLerHTML->invoke($envaseController, $arquivoTeste);
    
    echo "<div style='background: #e8f5e8; padding: 1rem; border-radius: 6px; margin: 1rem 0;'>";
    echo "<h4>📄 Resultado do método lerHTML:</h4>";
    echo "<p><strong>Registros extraídos:</strong> " . count($dadosHTML) . "</p>";
    
    if (!empty($dadosHTML)) {
        echo "<table style='width: 100%; border-collapse: collapse; margin-top: 0.5rem;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th style='border: 1px solid #ddd; padding: 0.5rem;'>Empresa</th>";
        echo "<th style='border: 1px solid #ddd; padding: 0.5rem;'>Produto</th>";
        echo "<th style='border: 1px solid #ddd; padding: 0.5rem;'>Data</th>";
        echo "<th style='border: 1px solid #ddd; padding: 0.5rem;'>Quantidade</th>";
        echo "</tr>";
        
        foreach ($dadosHTML as $registro) {
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'>" . htmlspecialchars($registro['empresa']) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'>" . htmlspecialchars($registro['produto']) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'>" . sprintf('%04d-%02d-%02d', $registro['ano'], $registro['mes'], $registro['dia']) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 0.5rem;'>" . number_format($registro['quantidade']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: #dc3545;'>❌ Nenhum dado foi extraído do HTML!</p>";
    }
    echo "</div>";
    
    // Limpar arquivo de teste
    if (file_exists($arquivoTeste)) {
        unlink($arquivoTeste);
        echo "<p style='color: #666; font-size: 0.9rem;'>🗑️ Arquivo de teste removido.</p>";
    }
    
    echo "<h2>💡 Diagnóstico</h2>";
    
    $diagnostico = [];
    
    if ($resultado['sucesso'] && $resultado['registros'] > 0) {
        $diagnostico[] = "✅ Processamento funcionou corretamente";
    } else {
        $diagnostico[] = "❌ Problema no processamento";
    }
    
    if (count($dadosHTML) > 0) {
        $diagnostico[] = "✅ Método lerHTML extrai dados corretamente";
    } else {
        $diagnostico[] = "❌ Método lerHTML não consegue extrair dados";
    }
    
    if ($statsApos['total_registros'] > $stats['total_registros']) {
        $diagnostico[] = "✅ Dados foram inseridos no banco";
    } else {
        $diagnostico[] = "❌ Dados não foram inseridos no banco";
    }
    
    echo "<div style='background: #f8f9fa; padding: 1rem; border-radius: 6px; margin: 1rem 0;'>";
    echo "<h4>🔍 Resultado do Diagnóstico:</h4>";
    echo "<ul>";
    foreach ($diagnostico as $item) {
        echo "<li>$item</li>";
    }
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 1rem; border-radius: 6px; color: #721c24; margin: 1rem 0;'>";
    echo "<h3>❌ Erro no Debug</h3>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . " (linha " . $e->getLine() . ")</p>";
    echo "<details>";
    echo "<summary>Stack Trace</summary>";
    echo "<pre style='background: #fff; padding: 0.5rem; font-size: 0.8rem;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</details>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='text-align: center; margin: 1rem 0;'>";
echo "<a href='" . BASE_URL . "/envase' class='btn' style='background: #007fa3; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 6px;'>← Voltar para Envase</a>";
echo "</div>";
?>
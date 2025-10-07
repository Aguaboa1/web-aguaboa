<?php
require_once 'config/init.php';

echo "<h2>Diagnóstico Completo - Upload HTML</h2>";

try {
    $pdo = Database::getInstance()->getConnection();
    
    // 1. Verificar estado atual da tabela
    echo "<h3>1. Estado atual da tabela envase_data:</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM envase_data");
    $total = $stmt->fetch()['total'];
    echo "Total de registros: <strong>{$total}</strong><br>";
    
    if ($total > 0) {
        $stmt = $pdo->query("SELECT * FROM envase_data ORDER BY id DESC LIMIT 5");
        $registros = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; font-size: 11px;'>";
        echo "<tr><th>ID</th><th>Empresa</th><th>Cidade</th><th>Produto</th><th>Ano</th><th>Mês</th><th>Dia</th><th>Quantidade</th><th>Arquivo</th></tr>";
        
        foreach ($registros as $reg) {
            echo "<tr>";
            echo "<td>{$reg['id']}</td>";
            echo "<td>" . htmlspecialchars($reg['empresa']) . "</td>";
            echo "<td>" . htmlspecialchars($reg['cidade'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($reg['produto']) . "</td>";
            echo "<td>{$reg['ano']}</td>";
            echo "<td>{$reg['mes']}</td>";
            echo "<td>{$reg['dia']}</td>";
            echo "<td>{$reg['quantidade']}</td>";
            echo "<td>" . htmlspecialchars($reg['arquivo_origem'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    // 2. Criar arquivo HTML baseado na imagem do usuário
    echo "<h3>2. Criando arquivo HTML igual ao da imagem:</h3>";
    
    $html_content = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dados de Envase</title>
</head>
<body>
    <table border="1">
        <tr>
            <th>Cliente</th>
            <th>Empresa</th>
            <th>Cidade</th>
            <th>Tipo</th>
            <th>Status</th>
        </tr>
        <tr>
            <td>ACQUA LIFE BARIRI</td>
            <td>ACQUA LIFE BARIRI</td>
            <td>BARIRI</td>
            <td>envase</td>
            <td>Sem dados de envase</td>
        </tr>
        <tr>
            <td>ADEGA DO ALEMAO GUAREI</td>
            <td>ADEGA DO ALEMAO GUAREI</td>
            <td>GUAREI</td>
            <td>envase</td>
            <td>Sem dados de envase</td>
        </tr>
        <tr>
            <td>ADEGA DO ALEMAO PORANGABA</td>
            <td>ADEGA DO ALEMAO PORANGABA</td>
            <td>PORANGABA</td>
            <td>envase</td>
            <td>Sem dados de envase</td>
        </tr>
        <tr>
            <td>ADEMAR CORREA</td>
            <td>ADEMAR CORREA</td>
            <td>AGUAS DE SANTA BARBARA</td>
            <td>envase</td>
            <td>Sem dados de envase</td>
        </tr>
        <tr>
            <td>ADEMIR MARINGA</td>
            <td>ADEMIR MARINGA</td>
            <td>MARINGA</td>
            <td>envase</td>
            <td>Sem dados de envase</td>
        </tr>
    </table>
</body>
</html>';
    
    // Salvar arquivo
    if (!is_dir('public/uploads/')) {
        mkdir('public/uploads/', 0755, true);
    }
    
    $arquivo_teste = 'public/uploads/dados_clientes_teste.html';
    file_put_contents($arquivo_teste, $html_content);
    echo "✓ Arquivo HTML criado: {$arquivo_teste}<br>";
    echo "Conteúdo do arquivo:<br>";
    echo "<textarea style='width: 100%; height: 150px;'>" . htmlspecialchars($html_content) . "</textarea><br><br>";
    
    // 3. Testar leitura do HTML
    echo "<h3>3. Testando leitura do HTML:</h3>";
    
    $envaseController = new EnvaseController();
    
    // Chamar método lerHTML diretamente
    $reflection = new ReflectionClass('EnvaseController');
    $method = $reflection->getMethod('lerHTML');
    $method->setAccessible(true);
    
    echo "Chamando lerHTML()...<br>";
    $dados_extraidos = $method->invoke($envaseController, $arquivo_teste);
    
    echo "Dados extraídos:<br>";
    echo "<pre>" . print_r($dados_extraidos, true) . "</pre>";
    
    // 4. Verificar se o arquivo tem dados de envase válidos
    echo "<h3>4. Análise do problema:</h3>";
    
    if (empty($dados_extraidos)) {
        echo "❌ PROBLEMA: O arquivo HTML não contém dados de envase válidos.<br>";
        echo "MOTIVO: O arquivo da imagem contém apenas informações de clientes, não dados de envase com quantidade, ano, mês, dia.<br><br>";
        
        echo "Para o sistema funcionar, o HTML precisa ter dados como:<br>";
        echo "<code>";
        echo "Empresa | Cidade | Produto | Ano | Mês | Dia | Quantidade<br>";
        echo "ACQUA LIFE BARIRI | BARIRI | AGUA MINERAL 500ML | 2024 | 1 | 15 | 1000<br>";
        echo "</code><br><br>";
        
        // Criar arquivo HTML correto para teste
        echo "<h3>5. Criando arquivo HTML correto para teste:</h3>";
        
        $html_correto = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dados de Envase Corretos</title>
</head>
<body>
    <table border="1">
        <tr>
            <th>Empresa</th>
            <th>Cidade</th>
            <th>Produto</th>
            <th>Ano</th>
            <th>Mês</th>
            <th>Dia</th>
            <th>Quantidade</th>
        </tr>
        <tr>
            <td>ACQUA LIFE BARIRI</td>
            <td>BARIRI</td>
            <td>AGUA MINERAL 500ML</td>
            <td>2024</td>
            <td>1</td>
            <td>15</td>
            <td>1000</td>
        </tr>
        <tr>
            <td>ADEGA DO ALEMAO GUAREI</td>
            <td>GUAREI</td>
            <td>AGUA NATURAL 1.5L</td>
            <td>2024</td>
            <td>1</td>
            <td>16</td>
            <td>750</td>
        </tr>
        <tr>
            <td>ADEGA DO ALEMAO PORANGABA</td>
            <td>PORANGABA</td>
            <td>AGUA COM GAS 500ML</td>
            <td>2024</td>
            <td>1</td>
            <td>17</td>
            <td>500</td>
        </tr>
        <tr>
            <td>ADEMAR CORREA</td>
            <td>AGUAS DE SANTA BARBARA</td>
            <td>AGUA MINERAL 300ML</td>
            <td>2024</td>
            <td>1</td>
            <td>18</td>
            <td>1200</td>
        </tr>
        <tr>
            <td>ADEMIR MARINGA</td>
            <td>MARINGA</td>
            <td>AGUA MINERAL 2L</td>
            <td>2024</td>
            <td>1</td>
            <td>19</td>
            <td>800</td>
        </tr>
    </table>
</body>
</html>';
        
        $arquivo_correto = 'public/uploads/dados_envase_correto.html';
        file_put_contents($arquivo_correto, $html_correto);
        echo "✓ Arquivo HTML correto criado: {$arquivo_correto}<br><br>";
        
        // Testar arquivo correto
        echo "<h3>6. Testando arquivo correto:</h3>";
        
        $dados_corretos = $method->invoke($envaseController, $arquivo_correto);
        echo "Dados extraídos do arquivo correto:<br>";
        echo "<pre>" . print_r($dados_corretos, true) . "</pre>";
        
        if (!empty($dados_corretos)) {
            echo "✅ SUCESSO! O arquivo correto foi processado.<br>";
            
            // Processar via EnvaseController completo
            $methodProcessar = $reflection->getMethod('processarPlanilha');
            $methodProcessar->setAccessible(true);
            
            $resultado = $methodProcessar->invoke($envaseController, $arquivo_correto, 'dados_envase_correto.html');
            
            echo "Resultado do processamento completo:<br>";
            echo "<pre>" . print_r($resultado, true) . "</pre>";
            
            // Verificar se dados foram inseridos
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM envase_data");
            $total_depois = $stmt->fetch()['total'];
            echo "Total de registros após processamento: <strong>{$total_depois}</strong><br>";
            
            if ($total_depois > $total) {
                echo "✅ DADOS INSERIDOS COM SUCESSO!<br>";
                
                // Mostrar novos dados
                $stmt = $pdo->query("SELECT * FROM envase_data ORDER BY id DESC LIMIT 5");
                $novos = $stmt->fetchAll();
                
                echo "<table border='1' style='border-collapse: collapse; font-size: 11px;'>";
                echo "<tr><th>ID</th><th>Empresa</th><th>Cidade</th><th>Produto</th><th>Ano</th><th>Mês</th><th>Dia</th><th>Quantidade</th></tr>";
                
                foreach ($novos as $reg) {
                    echo "<tr>";
                    echo "<td>{$reg['id']}</td>";
                    echo "<td>" . htmlspecialchars($reg['empresa']) . "</td>";
                    echo "<td>" . htmlspecialchars($reg['cidade'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($reg['produto']) . "</td>";
                    echo "<td>{$reg['ano']}</td>";
                    echo "<td>{$reg['mes']}</td>";
                    echo "<td>{$reg['dia']}</td>";
                    echo "<td>{$reg['quantidade']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        
        // Limpar arquivos de teste
        if (file_exists($arquivo_correto)) {
            unlink($arquivo_correto);
        }
    } else {
        echo "✅ Dados extraídos com sucesso!<br>";
    }
    
    // Limpar arquivo de teste
    if (file_exists($arquivo_teste)) {
        unlink($arquivo_teste);
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage();
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<br><br>";
echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
echo "<h3>💡 CONCLUSÃO:</h3>";
echo "O arquivo HTML da imagem contém apenas <strong>informações de clientes</strong>, não dados de envase.<br>";
echo "Para aparecer dados no dashboard, o HTML precisa ter colunas com:<br>";
echo "• <strong>Empresa</strong><br>";
echo "• <strong>Cidade</strong><br>";
echo "• <strong>Produto</strong> (ex: AGUA MINERAL 500ML)<br>";
echo "• <strong>Ano</strong> (ex: 2024)<br>";
echo "• <strong>Mês</strong> (ex: 1)<br>";
echo "• <strong>Dia</strong> (ex: 15)<br>";
echo "• <strong>Quantidade</strong> (ex: 1000)<br>";
echo "</div>";

echo "<br><a href='public/index.php'>← Voltar ao Dashboard</a>";
?>
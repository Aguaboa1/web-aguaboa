<?php
/**
 * Debug específico do problema de 0 registros processados
 */

require_once 'config/init.php';

// Simular sessão
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

echo "<h2>🔍 Debug: Por que 0 registros foram processados?</h2>";

$arquivo = 'public/uploads/RelatorioOLAP.csv';

if (!file_exists($arquivo)) {
    echo "<p>❌ Arquivo não encontrado: $arquivo</p>";
    exit;
}

echo "<p>✅ Arquivo encontrado: $arquivo</p>";
echo "<p>📊 Tamanho: " . number_format(filesize($arquivo)) . " bytes</p>";

// Ler primeiras linhas do arquivo para ver o formato
echo "<h3>📋 Primeiras 10 linhas do arquivo:</h3>";
echo "<pre style='background: #f8f9fa; padding: 1rem; border-radius: 5px; overflow-x: auto;'>";

$handle = fopen($arquivo, 'r');
for ($i = 0; $i < 10 && !feof($handle); $i++) {
    $linha = fgets($handle);
    echo ($i + 1) . ": " . htmlspecialchars($linha);
}
fclose($handle);
echo "</pre>";

// Testar diferentes separadores
echo "<h3>🔧 Teste de Separadores:</h3>";

$separadores = [';', ',', "\t", '|'];

foreach ($separadores as $sep) {
    echo "<h4>Separador: '" . ($sep === "\t" ? 'TAB' : $sep) . "'</h4>";
    
    $handle = fopen($arquivo, 'r');
    $linha_count = 0;
    $dados_encontrados = 0;
    
    while (($linha = fgetcsv($handle, 10000, $sep)) !== FALSE && $linha_count < 20) {
        $linha_count++;
        
        echo "<p><strong>Linha $linha_count:</strong> " . count($linha) . " colunas</p>";
        echo "<pre style='font-size: 0.8rem; background: #f1f1f1; padding: 0.5rem;'>";
        
        foreach ($linha as $index => $coluna) {
            echo "[$index] = '" . htmlspecialchars(trim($coluna)) . "'\n";
        }
        echo "</pre>";
        
        // Verificar se parece com dados válidos
        if (count($linha) >= 7) {
            $empresa = trim($linha[0]);
            $produto = trim($linha[2]);
            $ano = trim($linha[3]);
            $quantidade = trim($linha[6]);
            
            if (!empty($empresa) && !empty($produto) && is_numeric($ano) && is_numeric(str_replace(',', '.', $quantidade))) {
                $dados_encontrados++;
                echo "<p style='color: green;'>✅ Linha válida detectada!</p>";
            }
        }
        
        if ($linha_count >= 5) break; // Limitar output
    }
    fclose($handle);
    
    echo "<p><strong>Resultado:</strong> $dados_encontrados linhas válidas encontradas com separador '$sep'</p>";
    echo "<hr>";
}

// Testar processamento com o método real
echo "<h3>🧪 Teste com Método Real do Sistema:</h3>";

try {
    $envaseController = new EnvaseController();
    
    // Acessar método privado
    $reflection = new ReflectionClass($envaseController);
    $methodLerPlanilha = $reflection->getMethod('lerPlanilhaSimples');
    $methodLerPlanilha->setAccessible(true);
    
    echo "<p>🔄 Executando lerPlanilhaSimples()...</p>";
    $dados = $methodLerPlanilha->invoke($envaseController, $arquivo);
    
    echo "<p><strong>📊 Registros retornados:</strong> " . count($dados) . "</p>";
    
    if (!empty($dados)) {
        echo "<h4>✅ Primeiros 5 registros processados:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Empresa</th><th>Cidade</th><th>Produto</th><th>Ano</th><th>Mês</th><th>Dia</th><th>Qtde</th></tr>";
        
        foreach (array_slice($dados, 0, 5) as $registro) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($registro['empresa']) . "</td>";
            echo "<td>" . htmlspecialchars($registro['cidade']) . "</td>";
            echo "<td>" . htmlspecialchars($registro['produto']) . "</td>";
            echo "<td>" . $registro['ano'] . "</td>";
            echo "<td>" . $registro['mes'] . "</td>";
            echo "<td>" . $registro['dia'] . "</td>";
            echo "<td>" . $registro['quantidade'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Testar validação individual
        echo "<h4>🔍 Teste de Validação:</h4>";
        $methodValidar = $reflection->getMethod('validarRegistroEnvase');
        $methodValidar->setAccessible(true);
        
        foreach (array_slice($dados, 0, 3) as $index => $registro) {
            $valido = $methodValidar->invoke($envaseController, $registro);
            echo "<p>Registro " . ($index + 1) . ": " . ($valido ? "✅ Válido" : "❌ Inválido") . "</p>";
            
            if (!$valido) {
                echo "<pre>" . print_r($registro, true) . "</pre>";
            }
        }
        
    } else {
        echo "<p>❌ Nenhum registro foi processado</p>";
        
        // Vamos tentar ler manualmente com diferentes métodos
        echo "<h4>🔧 Tentativa Manual de Leitura:</h4>";
        
        // Método CSV direto
        $methodLerCSV = $reflection->getMethod('lerCSV');
        $methodLerCSV->setAccessible(true);
        
        echo "<p>Tentando lerCSV()...</p>";
        $dadosCSV = $methodLerCSV->invoke($envaseController, $arquivo);
        echo "<p>Resultado lerCSV(): " . count($dadosCSV) . " registros</p>";
        
        if (!empty($dadosCSV)) {
            echo "<p>✅ CSV funcionou! Primeiros dados:</p>";
            echo "<pre>" . print_r(array_slice($dadosCSV, 0, 2), true) . "</pre>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Verificar se há problemas de encoding
echo "<h3>🔤 Verificação de Encoding:</h3>";
$primeirasLinhas = file_get_contents($arquivo, false, null, 0, 1000);
echo "<p><strong>Encoding detectado:</strong> " . mb_detect_encoding($primeirasLinhas) . "</p>";
echo "<p><strong>Contém BOM:</strong> " . (substr($primeirasLinhas, 0, 3) === "\xEF\xBB\xBF" ? "Sim" : "Não") . "</p>";

echo "<hr>";
echo "<p><a href='public/' style='background: #007fa3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>👉 Voltar ao Sistema</a></p>";
?>
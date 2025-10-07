<?php
require_once 'config/init.php';

echo "<h2>Correção Avançada de Nomes - Banco de Dados</h2>";

try {
    $pdo = Database::getInstance()->getConnection();
    
    // 1. Identificar registros com problemas
    echo "<h3>1. Identificando registros problemáticos</h3>";
    
    $stmt = $pdo->query("
        SELECT id, empresa, cidade, produto 
        FROM envase_data 
        WHERE empresa LIKE '%Ã%' 
           OR empresa LIKE '%?%' 
           OR cidade LIKE '%Ã%' 
           OR cidade LIKE '%?%'
           OR produto LIKE '%Ã%' 
           OR produto LIKE '%?%'
        ORDER BY id
    ");
    
    $registros_problematicos = $stmt->fetchAll();
    echo "Encontrados " . count($registros_problematicos) . " registros com problemas<br><br>";
    
    if (count($registros_problematicos) > 0) {
        // Mostrar alguns exemplos antes da correção
        echo "<h4>Exemplos ANTES da correção:</h4>";
        echo "<table border='1' style='border-collapse: collapse; font-size: 11px;'>";
        echo "<tr><th>ID</th><th>Empresa ANTES</th><th>Cidade ANTES</th><th>Produto ANTES</th></tr>";
        
        foreach (array_slice($registros_problematicos, 0, 10) as $reg) {
            echo "<tr>";
            echo "<td>{$reg['id']}</td>";
            echo "<td>" . htmlspecialchars($reg['empresa']) . "</td>";
            echo "<td>" . htmlspecialchars($reg['cidade']) . "</td>";
            echo "<td>" . htmlspecialchars($reg['produto']) . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        // 2. Aplicar correções
        echo "<h3>2. Aplicando correções</h3>";
        
        $envaseController = new EnvaseController();
        $reflection = new ReflectionClass('EnvaseController');
        $method = $reflection->getMethod('limparTexto');
        $method->setAccessible(true);
        
        $correcoes_feitas = 0;
        
        foreach ($registros_problematicos as $reg) {
            // Aplicar função de limpeza
            $empresa_limpa = $method->invoke($envaseController, $reg['empresa']);
            $cidade_limpa = $method->invoke($envaseController, $reg['cidade']);
            $produto_limpo = $method->invoke($envaseController, $reg['produto']);
            
            // Correções específicas adicionais
            $empresa_limpa = $this->corrigirNomeEspecifico($empresa_limpa);
            $cidade_limpa = $this->corrigirNomeEspecifico($cidade_limpa);
            $produto_limpo = $this->corrigirNomeEspecifico($produto_limpo);
            
            // Atualizar no banco se houve mudança
            if ($empresa_limpa !== $reg['empresa'] || $cidade_limpa !== $reg['cidade'] || $produto_limpo !== $reg['produto']) {
                $stmt_update = $pdo->prepare("
                    UPDATE envase_data 
                    SET empresa = ?, cidade = ?, produto = ? 
                    WHERE id = ?
                ");
                
                if ($stmt_update->execute([$empresa_limpa, $cidade_limpa, $produto_limpo, $reg['id']])) {
                    $correcoes_feitas++;
                }
            }
        }
        
        echo "✅ {$correcoes_feitas} registros corrigidos<br><br>";
        
        // 3. Verificar resultados
        echo "<h3>3. Verificando resultados</h3>";
        
        $stmt = $pdo->query("
            SELECT id, empresa, cidade, produto 
            FROM envase_data 
            WHERE id IN (" . implode(',', array_column($registros_problematicos, 'id')) . ")
            ORDER BY id 
            LIMIT 10
        ");
        
        $registros_corrigidos = $stmt->fetchAll();
        
        echo "<h4>Exemplos DEPOIS da correção:</h4>";
        echo "<table border='1' style='border-collapse: collapse; font-size: 11px;'>";
        echo "<tr><th>ID</th><th>Empresa DEPOIS</th><th>Cidade DEPOIS</th><th>Produto DEPOIS</th></tr>";
        
        foreach ($registros_corrigidos as $reg) {
            echo "<tr>";
            echo "<td>{$reg['id']}</td>";
            echo "<td>" . htmlspecialchars($reg['empresa']) . "</td>";
            echo "<td>" . htmlspecialchars($reg['cidade']) . "</td>";
            echo "<td>" . htmlspecialchars($reg['produto']) . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        // 4. Estatísticas finais
        echo "<h3>4. Estatísticas finais</h3>";
        
        $stmt = $pdo->query("
            SELECT COUNT(*) as total 
            FROM envase_data 
            WHERE empresa LIKE '%Ã%' 
               OR empresa LIKE '%?%' 
               OR cidade LIKE '%Ã%' 
               OR cidade LIKE '%?%'
               OR produto LIKE '%Ã%' 
               OR produto LIKE '%?%'
        ");
        
        $problemas_restantes = $stmt->fetch()['total'];
        
        echo "• Problemas encontrados inicialmente: " . count($registros_problematicos) . "<br>";
        echo "• Correções aplicadas: {$correcoes_feitas}<br>";
        echo "• Problemas restantes: {$problemas_restantes}<br>";
        
        $melhoria = round((($correcoes_feitas / count($registros_problematicos)) * 100), 2);
        echo "• Melhoria: {$melhoria}%<br>";
        
        if ($problemas_restantes < (count($registros_problematicos) * 0.1)) {
            echo "<div style='color: green; font-weight: bold;'>✅ EXCELENTE! Mais de 90% dos problemas foram corrigidos.</div>";
        } elseif ($problemas_restantes < (count($registros_problematicos) * 0.5)) {
            echo "<div style='color: orange; font-weight: bold;'>⚠️ BOM: Mais de 50% dos problemas foram corrigidos.</div>";
        } else {
            echo "<div style='color: red; font-weight: bold;'>❌ Ainda há muitos problemas restantes.</div>";
        }
        
    } else {
        echo "<div style='color: green; font-weight: bold;'>✅ Nenhum problema de encoding encontrado!</div>";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Função auxiliar para correções específicas
function corrigirNomeEspecifico($texto) {
    $correções_específicas = [
        // Padrões específicos observados na imagem
        'AABBA ITAPECERICAA' => 'AABBA ITAPECERICA',
        'AABBÁ ITAPECERICAÁ' => 'AABBÁ ITAPECERICA',
        'SOAÁ PAULO' => 'SÃO PAULO',
        'SAOÁ PAULO' => 'SÃO PAULO',
        'ÁGUASÁ DEÁ SANTAÁ' => 'ÁGUAS DE SANTA',
        'ÁGUASÃ DEÃ SANTAÃ' => 'ÁGUAS DE SANTA',
        'BÁÊRBARA' => 'BÁRBARA',
        'BÃÊRBARA' => 'BÁRBARA',
        
        // Remover padrões específicos problemáticos
        '/\s+Á\s+Á\s+Á.*/' => '',
        '/\s+Ã\s+Ã\s+Ã.*/' => '',
        '/\s+a\?\s+a\?\s+a\?.*/' => '',
    ];
    
    foreach ($correções_específicas as $buscar => $substituir) {
        if (strpos($buscar, '/') === 0) {
            // É uma regex
            $texto = preg_replace($buscar, $substituir, $texto);
        } else {
            $texto = str_replace($buscar, $substituir, $texto);
        }
    }
    
    return trim($texto);
}

echo "<br><br>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;'>";
echo "<h3>🎯 Próximos passos:</h3>";
echo "1. <a href='public/index.php?path=/envase' style='color: #007fa3;'>Verificar dados corrigidos no Dashboard</a><br>";
echo "2. <a href='public/index.php?path=/crm' style='color: #007fa3;'>Ver no CRM se nomes estão melhores</a><br>";
echo "3. <a href='public/index.php?path=/crm/unify-clients' style='color: #007fa3;'>Usar unificação para consolidar variações</a><br>";
echo "</div>";
?>
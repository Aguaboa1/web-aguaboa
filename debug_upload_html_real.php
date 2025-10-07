<?php
require_once 'config/init.php';

echo "<h2>Simulação Completa do Upload de HTML</h2>";

try {
    // Criar arquivo HTML de teste igual ao que o usuário faria upload
    $html_content = '<table>
        <tr><th>Empresa</th><th>Cidade</th><th>Produto</th><th>Ano</th><th>Mês</th><th>Dia</th><th>Quantidade</th></tr>
        <tr><td>ENTREPOSTO SEDE</td><td>São Paulo</td><td>AGUA NATURAL 500ML</td><td>2024</td><td>1</td><td>15</td><td>1000</td></tr>
        <tr><td>ENTREPOSTO FILIAL</td><td>Rio de Janeiro</td><td>AGUA COM GAS 500ML</td><td>2024</td><td>1</td><td>16</td><td>750</td></tr>
        <tr><td>AGUA PURA TATUI</td><td>Tatuí</td><td>AGUA MINERAL 1.5L</td><td>2024</td><td>1</td><td>17</td><td>500</td></tr>
    </table>';
    
    // Salvar arquivo na pasta de uploads
    if (!is_dir('public/uploads/')) {
        mkdir('public/uploads/', 0755, true);
    }
    
    $arquivo_teste = 'public/uploads/teste_upload_simulado.html';
    file_put_contents($arquivo_teste, $html_content);
    echo "✓ Arquivo HTML criado: {$arquivo_teste}<br><br>";
    
    // Contar registros antes
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM envase_data");
    $total_antes = $stmt->fetch()['total'];
    echo "📊 Total de registros ANTES: {$total_antes}<br><br>";
    
    // Simular o processamento exato que o EnvaseController faz
    echo "<h3>🔄 Simulando processamento via EnvaseController</h3>";
    
    $envaseController = new EnvaseController();
    
    // Chamar o método processarPlanilha (igual ao upload real)
    $reflection = new ReflectionClass('EnvaseController');
    $method = $reflection->getMethod('processarPlanilha');
    $method->setAccessible(true);
    
    echo "Chamando processarPlanilha()...<br>";
    $resultado = $method->invoke($envaseController, $arquivo_teste, 'teste_upload_simulado.html');
    
    echo "<h4>Resultado do processamento:</h4>";
    echo "<pre>" . print_r($resultado, true) . "</pre>";
    
    // Contar registros depois
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM envase_data");
    $total_depois = $stmt->fetch()['total'];
    echo "<br>📊 Total de registros DEPOIS: {$total_depois}<br>";
    echo "📈 Novos registros adicionados: " . ($total_depois - $total_antes) . "<br><br>";
    
    // Se dados foram adicionados, mostrar
    if ($total_depois > $total_antes) {
        echo "<h3>✅ Dados inseridos com sucesso!</h3>";
        
        // Mostrar os registros mais recentes
        $stmt = $pdo->query("SELECT * FROM envase_data ORDER BY id DESC LIMIT 5");
        $novos_registros = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Empresa</th><th>Cidade</th><th>Produto</th><th>Ano</th><th>Mês</th><th>Dia</th><th>Quantidade</th><th>Arquivo</th></tr>";
        
        foreach ($novos_registros as $reg) {
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
        echo "</table>";
        
    } else {
        echo "<h3>❌ Nenhum dado foi inserido!</h3>";
        echo "Possíveis problemas:<br>";
        echo "- Arquivo HTML não foi processado corretamente<br>";
        echo "- Erro na extração dos dados da tabela<br>";
        echo "- Erro na inserção no banco de dados<br>";
    }
    
    // Testar estatísticas do dashboard
    echo "<br><h3>📊 Testando estatísticas do dashboard</h3>";
    $envaseModel = new Envase();
    $stats = $envaseModel->getStats();
    
    echo "Estatísticas atuais:<br>";
    echo "- Total de registros: {$stats['total_registros']}<br>";
    echo "- Empresas únicas: {$stats['empresas_unicas']}<br>";
    echo "- Produtos únicos: {$stats['produtos_unicos']}<br>";
    echo "- Total quantidade: {$stats['total_quantidade']}<br>";
    
    // Limpar arquivo de teste
    if (file_exists($arquivo_teste)) {
        unlink($arquivo_teste);
        echo "<br>🗑️ Arquivo de teste removido<br>";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<br><br><a href='public/index.php'>← Voltar ao Dashboard</a>";
?>
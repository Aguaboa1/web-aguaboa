<?php
require_once 'config/init.php';

echo "<h2>🗑️ Limpeza Completa dos Dados de CRM</h2>";

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "<h3>1. Removendo todos os clientes</h3>";
    
    // Contar clientes antes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clients");
    $total_clientes = $stmt->fetch()['total'];
    echo "📊 Clientes antes da limpeza: " . number_format($total_clientes) . "<br>";
    
    // Contar ações antes (serão removidas automaticamente por CASCADE)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM actions");
    $total_acoes = $stmt->fetch()['total'];
    echo "📊 Ações antes da limpeza: " . number_format($total_acoes) . "<br>";
    
    // Contar informações adicionais antes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM client_infos");
    $total_infos = $stmt->fetch()['total'];
    echo "📊 Informações adicionais antes da limpeza: " . number_format($total_infos) . "<br><br>";
    
    // Remover todos os clientes (CASCADE removerá ações e infos automaticamente)
    $stmt = $pdo->query("DELETE FROM clients");
    echo "✅ Todos os clientes removidos<br>";
    echo "✅ Ações dos clientes removidas automaticamente (CASCADE)<br>";
    echo "✅ Informações adicionais removidas automaticamente (CASCADE)<br>";
    
    // Resetar auto_increment das tabelas
    $stmt = $pdo->query("ALTER TABLE clients AUTO_INCREMENT = 1");
    echo "✅ Contador de IDs de clientes resetado<br>";
    
    $stmt = $pdo->query("ALTER TABLE actions AUTO_INCREMENT = 1");
    echo "✅ Contador de IDs de ações resetado<br>";
    
    $stmt = $pdo->query("ALTER TABLE client_infos AUTO_INCREMENT = 1");
    echo "✅ Contador de IDs de informações resetado<br><br>";
    
    echo "<h3>2. Verificando limpeza</h3>";
    
    // Verificar se tudo foi limpo
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clients");
    $clientes_depois = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM actions");
    $acoes_depois = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM client_infos");
    $infos_depois = $stmt->fetch()['total'];
    
    echo "📊 Verificação final:<br>";
    echo "• Clientes: {$clientes_depois} registros<br>";
    echo "• Ações: {$acoes_depois} registros<br>";
    echo "• Informações adicionais: {$infos_depois} registros<br><br>";
    
    if ($clientes_depois == 0 && $acoes_depois == 0 && $infos_depois == 0) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;'>";
        echo "<h3>🎉 LIMPEZA DO CRM CONCLUÍDA COM SUCESSO!</h3>";
        echo "Todos os dados de clientes foram removidos:<br>";
        echo "• {$total_clientes} clientes removidos<br>";
        echo "• {$total_acoes} ações removidas<br>";
        echo "• {$total_infos} informações adicionais removidas<br>";
        echo "<br>O CRM está limpo e pronto para novos dados.";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545;'>";
        echo "<h3>⚠️ ATENÇÃO</h3>";
        echo "Alguns dados podem não ter sido removidos completamente.";
        echo "</div>";
    }
    
    // Log da atividade de limpeza
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            'CRM_CLEANUP',
            "Limpeza completa do CRM - {$total_clientes} clientes, {$total_acoes} ações e {$total_infos} infos removidos",
            $_SERVER['REMOTE_ADDR'] ?? 'localhost'
        ]);
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545;'>";
    echo "<h3>❌ ERRO na limpeza do CRM</h3>";
    echo "Erro: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<br><br>";
echo "<div style='background: #e2f3ff; padding: 20px; border-radius: 5px; text-align: center;'>";
echo "<h3>🚀 CRM Pronto para Novos Dados</h3>";
echo "<p>O sistema CRM foi completamente limpo. Agora você pode:</p>";
echo "<ul style='text-align: left; display: inline-block;'>";
echo "<li>Fazer upload de novos dados de envase</li>";
echo "<li>Os clientes serão criados automaticamente a partir dos dados de envase</li>";
echo "<li>Usar as funcionalidades de unificação com dados limpos</li>";
echo "</ul>";
echo "<br>";
echo "<a href='public/index.php?path=/crm' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;'>👥 IR PARA CRM</a>";
echo "<a href='public/index.php?path=/envase' style='background: #007fa3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>📤 UPLOAD DE ENVASE</a>";
echo "<br><br>";
echo "<a href='public/index.php' style='color: #007fa3; text-decoration: none;'>🏠 Voltar ao Dashboard Principal</a>";
echo "</div>";
?>
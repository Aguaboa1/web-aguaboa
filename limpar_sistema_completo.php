<?php
require_once 'config/init.php';

echo "<h2>🗑️ Limpeza Completa do Sistema</h2>";

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "<h3>1. Removendo todos os dados de envase</h3>";
    
    // Contar registros antes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM envase_data");
    $total_antes = $stmt->fetch()['total'];
    echo "📊 Registros antes da limpeza: " . number_format($total_antes) . "<br>";
    
    // Remover todos os dados de envase
    $stmt = $pdo->query("DELETE FROM envase_data");
    echo "✅ Todos os dados de envase removidos<br>";
    
    // Resetar auto_increment
    $stmt = $pdo->query("ALTER TABLE envase_data AUTO_INCREMENT = 1");
    echo "✅ Contador de ID resetado<br><br>";
    
    echo "<h3>2. Limpando histórico de uploads</h3>";
    
    // Contar uploads antes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM upload_history");
    $uploads_antes = $stmt->fetch()['total'];
    echo "📊 Uploads antes da limpeza: " . number_format($uploads_antes) . "<br>";
    
    // Remover histórico de uploads
    $stmt = $pdo->query("DELETE FROM upload_history");
    echo "✅ Histórico de uploads removido<br>";
    
    // Resetar auto_increment
    $stmt = $pdo->query("ALTER TABLE upload_history AUTO_INCREMENT = 1");
    echo "✅ Contador de uploads resetado<br><br>";
    
    echo "<h3>3. Limpando logs de atividade</h3>";
    
    // Contar logs antes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM activity_log");
    $logs_antes = $stmt->fetch()['total'];
    echo "📊 Logs antes da limpeza: " . number_format($logs_antes) . "<br>";
    
    // Remover logs antigos (manter últimos 50 por segurança)
    $stmt = $pdo->query("DELETE FROM activity_log WHERE id NOT IN (SELECT id FROM (SELECT id FROM activity_log ORDER BY id DESC LIMIT 50) as temp)");
    echo "✅ Logs antigos removidos (mantidos últimos 50)<br><br>";
    
    echo "<h3>4. Limpando arquivos de upload temporários</h3>";
    
    $upload_dir = 'public/uploads/';
    $arquivos_removidos = 0;
    
    if (is_dir($upload_dir)) {
        $arquivos = glob($upload_dir . '*');
        foreach ($arquivos as $arquivo) {
            if (is_file($arquivo) && basename($arquivo) !== '.gitkeep') {
                if (unlink($arquivo)) {
                    $arquivos_removidos++;
                }
            }
        }
    }
    
    echo "✅ {$arquivos_removidos} arquivos temporários removidos<br><br>";
    
    echo "<h3>5. Verificando limpeza</h3>";
    
    // Verificar se tudo foi limpo
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM envase_data");
    $total_depois = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM upload_history");
    $uploads_depois = $stmt->fetch()['total'];
    
    echo "📊 Verificação final:<br>";
    echo "• Dados de envase: {$total_depois} registros<br>";
    echo "• Histórico de uploads: {$uploads_depois} registros<br>";
    echo "• Arquivos removidos: {$arquivos_removidos}<br><br>";
    
    if ($total_depois == 0 && $uploads_depois == 0) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;'>";
        echo "<h3>🎉 LIMPEZA CONCLUÍDA COM SUCESSO!</h3>";
        echo "O sistema está limpo e pronto para receber novos dados.<br>";
        echo "Todos os dados antigos foram removidos.";
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
            'SYSTEM_CLEANUP',
            "Limpeza completa do sistema - {$total_antes} registros de envase e {$uploads_antes} uploads removidos",
            $_SERVER['REMOTE_ADDR'] ?? 'localhost'
        ]);
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545;'>";
    echo "<h3>❌ ERRO na limpeza</h3>";
    echo "Erro: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<br><br>";
echo "<div style='background: #e2f3ff; padding: 20px; border-radius: 5px; text-align: center;'>";
echo "<h3>🚀 Sistema Pronto para Novo Upload</h3>";
echo "<p>O sistema foi completamente limpo. Agora você pode fazer upload dos novos dados.</p>";
echo "<br>";
echo "<a href='public/index.php?path=/envase' style='background: #007fa3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>📤 IR PARA UPLOAD DE ENVASE</a>";
echo "<br><br>";
echo "<a href='public/index.php' style='color: #007fa3; text-decoration: none;'>🏠 Voltar ao Dashboard Principal</a>";
echo "</div>";
?>
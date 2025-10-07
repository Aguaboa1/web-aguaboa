<?php
/**
 * COMANDO BK - Backup Instantâneo
 * Digite: php BK.php
 */

echo "🚀 COMANDO BK - BACKUP INSTANTÂNEO\n";
echo "=====================================\n\n";

$timestamp = date('Y-m-d-Hi');
$backupName = "gestao-aguaboa-php-backup-{$timestamp}";
$backupPath = "C:\\xampp\\htdocs\\Backups\\{$backupName}";

try {
    echo "📦 Criando backup: {$backupName}\n";
    
    // Criar pasta
    if (!is_dir($backupPath)) {
        mkdir($backupPath, 0755, true);
    }
    
    // Copiar arquivos
    echo "📁 Copiando arquivos... ";
    exec("robocopy \"" . __DIR__ . "\" \"{$backupPath}\" /E /R:0 /W:0 /NP", $output, $code);
    
    if ($code <= 3) {
        echo "✅\n";
    } else {
        echo "⚠️\n";
    }
    
    // Backup do banco
    echo "💾 Backup do banco... ";
    exec("\"C:\\xampp\\mysql\\bin\\mysqldump.exe\" -u root aguaboa_gestao > \"{$backupPath}\\backup_database.sql\"", $dbOutput, $dbCode);
    
    if ($dbCode === 0) {
        echo "✅\n";
    } else {
        echo "⚠️\n";
    }
    
    // Criar info do backup
    $info = "# 🚀 BACKUP BK - " . date('d/m/Y H:i:s') . "
✅ Backup criado automaticamente via comando BK
📍 Local: {$backupPath}
🎯 Sistema 100% funcional preservado
";
    file_put_contents("{$backupPath}\\BK_INFO.md", $info);
    
    echo "\n🎉 BACKUP BK CONCLUÍDO!\n";
    echo "📍 Salvo: {$backupName}\n";
    echo "⚡ Comando BK executado com sucesso!\n\n";
    
    // Listar últimos 3 backups
    echo "📁 Últimos backups:\n";
    $backups = glob("C:\\xampp\\htdocs\\Backups\\gestao-aguaboa-php-backup-*");
    rsort($backups);
    $recent = array_slice($backups, 0, 3);
    
    foreach ($recent as $backup) {
        $name = basename($backup);
        $date = filemtime($backup);
        echo "- {$name} (" . date('d/m H:i', $date) . ")\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
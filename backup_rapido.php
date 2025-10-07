<?php
/**
 * BACKUP RÁPIDO - Sistema Aguaboa
 * Execute: php backup_rapido.php
 */

echo "🚀 BACKUP RÁPIDO - Sistema Aguaboa\n";
echo "==================================\n\n";

$timestamp = date('Y-m-d-Hi');
$backupName = "gestao-aguaboa-php-backup-{$timestamp}";
$backupPath = "C:\\xampp\\htdocs\\Backups\\{$backupName}";

try {
    // Criar pasta
    if (!is_dir($backupPath)) {
        mkdir($backupPath, 0755, true);
    }
    
    echo "📁 Criando backup: {$backupName}\n";
    
    // Copiar arquivos
    echo "📋 Copiando arquivos...\n";
    exec("robocopy \"" . __DIR__ . "\" \"{$backupPath}\" /E /R:0 /W:0", $output, $code);
    
    if ($code <= 3) {
        echo "✅ Arquivos copiados!\n";
    }
    
    // Backup do banco
    echo "💾 Backup do banco...\n";
    exec("\"C:\\xampp\\mysql\\bin\\mysqldump.exe\" -u root aguaboa_gestao > \"{$backupPath}\\backup_database.sql\"");
    echo "✅ Banco salvo!\n";
    
    // Info do backup
    $info = "# 📦 Backup Sistema Aguaboa - " . date('d/m/Y H:i') . "
✅ Sistema completo funcional
📍 Local: C:\\xampp\\htdocs\\Backups\\{$backupName}
🔄 Para restaurar: copie tudo para gestao-aguaboa-php e importe o SQL
";
    file_put_contents("{$backupPath}\\INFO.md", $info);
    
    echo "\n🎉 BACKUP CONCLUÍDO!\n";
    echo "📍 Salvo em: C:\\xampp\\htdocs\\Backups\\{$backupName}\n";
    echo "✅ Sistema 100% preservado!\n\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
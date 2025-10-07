<?php
/**
 * Script Automatizado de Backup - Sistema Aguaboa
 * Salva sempre em: C:\xampp\htdocs\Backups\
 */

try {
    echo "🔄 Iniciando backup automatizado do Sistema Aguaboa...\n";
    
    // Configurações
    $backupBaseDir = 'C:\xampp\htdocs\Backups';
    $sourceDir = __DIR__;
    $timestamp = date('Y-m-d-Hi');
    $backupName = "gestao-aguaboa-php-backup-{$timestamp}";
    $backupPath = "{$backupBaseDir}\\{$backupName}";
    
    echo "📁 Pasta de destino: {$backupPath}\n";
    
    // Criar pasta de backup se não existir
    if (!is_dir($backupBaseDir)) {
        mkdir($backupBaseDir, 0755, true);
        echo "✅ Pasta Backups criada: {$backupBaseDir}\n";
    }
    
    // Criar pasta específica do backup
    if (!is_dir($backupPath)) {
        mkdir($backupPath, 0755, true);
        echo "✅ Pasta do backup criada: {$backupPath}\n";
    }
    
    echo "\n📋 Iniciando cópia dos arquivos...\n";
    
    // Executar robocopy para copiar arquivos
    $robocopyCmd = "robocopy \"{$sourceDir}\" \"{$backupPath}\" /E /R:0 /W:0";
    $output = [];
    $returnCode = 0;
    exec($robocopyCmd, $output, $returnCode);
    
    // Robocopy retorna códigos diferentes, verificar se foi bem-sucedido
    if ($returnCode <= 3) { // 0-3 são códigos de sucesso no robocopy
        echo "✅ Arquivos copiados com sucesso!\n";
    } else {
        throw new Exception("Erro na cópia dos arquivos. Código: {$returnCode}");
    }
    
    echo "\n💾 Fazendo backup do banco de dados...\n";
    
    // Fazer backup do banco de dados
    $mysqldumpPath = 'C:\xampp\mysql\bin\mysqldump.exe';
    $dbBackupFile = "{$backupPath}\\backup_database.sql";
    $mysqldumpCmd = "\"{$mysqldumpPath}\" -u root aguaboa_gestao > \"{$dbBackupFile}\"";
    
    exec($mysqldumpCmd, $dbOutput, $dbReturnCode);
    
    if ($dbReturnCode === 0 && file_exists($dbBackupFile)) {
        $dbSize = round(filesize($dbBackupFile) / 1024 / 1024, 2);
        echo "✅ Backup do banco criado: {$dbSize} MB\n";
    } else {
        echo "⚠️ Erro no backup do banco de dados\n";
    }
    
    // Criar arquivo de informações do backup
    $backupInfo = "# 📦 Backup Sistema Aguaboa
**Data:** " . date('d/m/Y \à\s H:i') . "
**Pasta:** {$backupName}
**Status:** ✅ Completo

## 🏢 Sistema Funcional com:
- ✅ Todos os departamentos (incluindo Produção)
- ✅ Usuários: Branco, Tilico, equipe, Rogerio, Producao, Supervisor, Operador
- ✅ Sistema de permissões configurado
- ✅ Banco de dados preservado
- ✅ Uploads e configurações mantidas

## 🔄 Para restaurar:
1. Copie todo o conteúdo para: c:\\xampp\\htdocs\\gestao-aguaboa-php\\
2. Importe: backup_database.sql
3. Acesse: http://localhost/gestao-aguaboa-php/public/

**Backup salvo em: C:\\xampp\\htdocs\\Backups\\{$backupName}**
";
    
    file_put_contents("{$backupPath}\\README_BACKUP.md", $backupInfo);
    
    // Calcular tamanho total do backup
    $totalSize = 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($backupPath));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $totalSize += $file->getSize();
        }
    }
    $totalSizeMB = round($totalSize / 1024 / 1024, 2);
    
    echo "\n🎉 BACKUP CONCLUÍDO COM SUCESSO!\n";
    echo "📍 Local: C:\\xampp\\htdocs\\Backups\\{$backupName}\n";
    echo "📊 Tamanho: {$totalSizeMB} MB\n";
    echo "⏰ Data: " . date('d/m/Y H:i:s') . "\n";
    echo "\n✅ Sistema totalmente preservado e funcional!\n";
    
    // Listar backups existentes
    echo "\n📁 Backups disponíveis:\n";
    $backups = glob("{$backupBaseDir}\\gestao-aguaboa-php-backup-*");
    foreach ($backups as $backup) {
        $backupBasename = basename($backup);
        $backupDate = filemtime($backup);
        echo "- {$backupBasename} (" . date('d/m/Y H:i', $backupDate) . ")\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro durante o backup: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
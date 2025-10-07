 <?php
// Script para corrigir as senhas dos usuários
require_once __DIR__ . '/config/init.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Gerar novos hashes para as senhas
    $hashBranco = password_hash('652409', PASSWORD_DEFAULT);
    $hashEquipe = password_hash('equipe123', PASSWORD_DEFAULT);
    
    echo "Atualizando senhas...\n";
    
    // Atualizar senha do Branco
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE username = 'Branco'");
    $stmt->execute([$hashBranco]);
    
    // Atualizar senha da equipe
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE username = 'equipe'");
    $stmt->execute([$hashEquipe]);
    
    echo "✅ Senhas atualizadas com sucesso!\n";
    echo "📋 Credenciais:\n";
    echo "👤 Admin: Branco / 652409\n";
    echo "👤 Equipe: equipe / equipe123\n";
    echo "\n🌐 Acesse: http://localhost/gestao-aguaboa-php/public/\n";
    
    // Verificar usuários
    $stmt = $db->query("SELECT username, password_plain, role FROM users");
    $users = $stmt->fetchAll();
    
    echo "\n📊 Usuários no banco:\n";
    foreach ($users as $user) {
        echo "- {$user['username']} ({$user['role']}) - Senha: {$user['password_plain']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
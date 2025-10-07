<?php
// Teste simples de login
echo "<h1>🧪 Teste de Login</h1>";

try {
    // Conectar diretamente ao MySQL
    $pdo = new PDO("mysql:host=localhost;dbname=aguaboa_gestao", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✅ Conexão com banco OK</h2>";
    
    // Buscar usuários
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll();
    
    echo "<h2>👥 Usuários no banco:</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Senha Plain</th><th>Role</th><th>Ativo</th><th>Teste Senha</th></tr>";
    
    foreach ($users as $user) {
        // Testar verificação de senha
        $senha652409 = password_verify('652409', $user['password_hash']);
        $senhaEquipe123 = password_verify('equipe123', $user['password_hash']);
        
        $testeSenha = '';
        if ($user['username'] === 'Branco' && $senha652409) {
            $testeSenha = '✅ 652409 OK';
        } elseif ($user['username'] === 'equipe' && $senhaEquipe123) {
            $testeSenha = '✅ equipe123 OK';
        } else {
            $testeSenha = '❌ Senha não confere';
        }
        
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['password_plain']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "<td>" . ($user['is_active'] ? '✅' : '❌') . "</td>";
        echo "<td>$testeSenha</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Corrigir senhas se necessário
    echo "<h2>🔧 Corrigindo senhas...</h2>";
    
    $hashBranco = password_hash('652409', PASSWORD_DEFAULT);
    $hashEquipe = password_hash('equipe123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'Branco'");
    $stmt->execute([$hashBranco]);
    
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'equipe'");
    $stmt->execute([$hashEquipe]);
    
    echo "<p>✅ Senhas corrigidas!</p>";
    
    echo "<h2>🎯 Agora tente fazer login:</h2>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> Usuário: <code>Branco</code> / Senha: <code>652409</code></li>";
    echo "<li><strong>Equipe:</strong> Usuário: <code>equipe</code> / Senha: <code>equipe123</code></li>";
    echo "</ul>";
    
    echo "<p><a href='/gestao-aguaboa-php/public/' style='background: #007fa3; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🚀 Ir para o Sistema</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erro:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
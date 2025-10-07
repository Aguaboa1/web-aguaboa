<?php
// Debug completo do sistema de login
echo "<h1>🔍 Debug do Sistema de Login</h1>";

try {
    // 1. Testar conexão
    $pdo = new PDO("mysql:host=localhost;dbname=aguaboa_gestao", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Conexão com banco OK</p>";
    
    // 2. Buscar usuários
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>✅ Encontrados " . count($users) . " usuários</p>";
    
    // 3. Testar cada usuário
    foreach ($users as $user) {
        echo "<hr>";
        echo "<h2>👤 Testando: {$user['username']}</h2>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> {$user['id']}</li>";
        echo "<li><strong>Username:</strong> {$user['username']}</li>";
        echo "<li><strong>Password Plain:</strong> {$user['password_plain']}</li>";
        echo "<li><strong>Password Hash:</strong> " . substr($user['password_hash'], 0, 20) . "...</li>";
        echo "<li><strong>Role:</strong> {$user['role']}</li>";
        echo "<li><strong>Active:</strong> " . ($user['is_active'] ? 'SIM' : 'NÃO') . "</li>";
        echo "</ul>";
        
        // Testar senha
        $senhaEsperada = ($user['username'] === 'Branco') ? '652409' : 'equipe123';
        $verifica = password_verify($senhaEsperada, $user['password_hash']);
        
        echo "<p><strong>🔑 Teste de senha '$senhaEsperada':</strong> ";
        if ($verifica) {
            echo "<span style='color: green; font-weight: bold;'>✅ SUCESSO</span>";
        } else {
            echo "<span style='color: red; font-weight: bold;'>❌ FALHA</span>";
            
            // Gerar novo hash
            $novoHash = password_hash($senhaEsperada, PASSWORD_DEFAULT);
            echo "<br><strong>🔧 Corrigindo senha...</strong>";
            
            $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $updateStmt->execute([$novoHash, $user['id']]);
            
            // Testar novamente
            $verificaNovo = password_verify($senhaEsperada, $novoHash);
            echo "<br><strong>✅ Nova verificação:</strong> " . ($verificaNovo ? 'SUCESSO' : 'FALHA');
        }
        echo "</p>";
    }
    
    echo "<hr>";
    echo "<h2>🧪 Teste Manual de Login</h2>";
    
    // Simular login
    $testUsers = [
        ['username' => 'Branco', 'password' => '652409'],
        ['username' => 'equipe', 'password' => 'equipe123']
    ];
    
    foreach ($testUsers as $testUser) {
        echo "<h3>Testando: {$testUser['username']} / {$testUser['password']}</h3>";
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$testUser['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "<p>✅ Usuário encontrado</p>";
            
            if ($user['is_active']) {
                echo "<p>✅ Usuário ativo</p>";
                
                if (password_verify($testUser['password'], $user['password_hash'])) {
                    echo "<p style='color: green; font-weight: bold; font-size: 1.2em;'>🎉 LOGIN DEVE FUNCIONAR!</p>";
                } else {
                    echo "<p style='color: red; font-weight: bold;'>❌ Senha incorreta</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Usuário inativo</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Usuário não encontrado</p>";
        }
    }
    
    echo "<hr>";
    echo "<h2>🚀 Tentar Login Agora:</h2>";
    echo "<p><a href='/gestao-aguaboa-php/public/' style='background: #007fa3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 1.1em;'>🌐 Ir para Sistema</a></p>";
    
    echo "<h3>🔐 Credenciais:</h3>";
    echo "<ul style='font-size: 1.1em;'>";
    echo "<li><strong>Admin:</strong> <code>Branco</code> / <code>652409</code></li>";
    echo "<li><strong>Equipe:</strong> <code>equipe</code> / <code>equipe123</code></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ ERRO:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
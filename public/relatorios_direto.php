<?php
/**
 * Acesso Direto aos Relatórios - Auto Login
 * Web Aguaboa - Gestão de Produção
 */

require_once '../config/init.php';

// Fazer login automático se não estiver logado
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Buscar usuário Rogerio
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, username, role FROM users WHERE username = 'Rogerio' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Definir sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        setFlash('success', 'Login automático realizado para acesso aos relatórios');
    } else {
        setFlash('error', 'Usuário Rogerio não encontrado no sistema');
        redirect('/auth/login');
    }
}

// Redirecionar para relatórios
redirect('/relatorios');
?>
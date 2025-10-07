<?php
// Script para fazer login automático e testar ações
session_start();

// Fazer login automático com usuário Rogerio
$_SESSION['user_id'] = 2;
$_SESSION['username'] = 'Rogerio';
$_SESSION['role'] = 'equipe';

echo "✅ Login automático realizado!\n";
echo "Session ID: " . session_id() . "\n";
echo "User ID: " . $_SESSION['user_id'] . "\n";
echo "Username: " . $_SESSION['username'] . "\n";
echo "Role: " . $_SESSION['role'] . "\n\n";

echo "🌐 Agora você pode acessar:\n";
echo "http://localhost/gestao-aguaboa-php/public/producao\n";
echo "http://localhost/gestao-aguaboa-php/public/relatorios\n\n";
?>
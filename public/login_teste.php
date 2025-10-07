<?php
// Script para fazer login automático e testar página de envase

session_start();

// Simular login do usuário admin
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'Branco';
$_SESSION['role'] = 'admin';

echo "Login simulado com sucesso!<br>";
echo "Redirecionando para página de envase cliente...<br>";

// Redirecionar após 2 segundos
echo '<script>setTimeout(function(){ window.location.href = "/gestao-aguaboa-php/public/envase/cliente/4"; }, 2000);</script>';
?>
<?php
echo "<h1>Debug de Rotas</h1>";
echo "<p><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>PATH_INFO:</strong> " . ($_SERVER['PATH_INFO'] ?? 'não definido') . "</p>";

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
echo "<p><strong>PATH parsed:</strong> " . $path . "</p>";

require_once __DIR__ . '/config/init.php';

echo "<p><strong>BASE_URL:</strong> '" . BASE_URL . "'</p>";

// Simular o mesmo processamento do index
$path = str_replace(BASE_URL, '', $path);
echo "<p><strong>PATH após remover BASE_URL:</strong> " . $path . "</p>";

echo "<h2>Sessão:</h2>";
if (isset($_SESSION['user_id'])) {
    echo "<p>Usuário logado: " . $_SESSION['username'] . " (ID: " . $_SESSION['user_id'] . ")</p>";
    echo "<p>Role: " . $_SESSION['role'] . "</p>";
} else {
    echo "<p>Usuário NÃO logado</p>";
}

echo "<h2>Teste de Rotas:</h2>";
echo "<a href='/admin/users'>Teste: /admin/users</a><br>";
echo "<a href='/admin/logs'>Teste: /admin/logs</a><br>";
echo "<a href='/administracao'>Teste: /administracao</a><br>";
?>
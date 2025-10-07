<?php
// Arquivo de diagnóstico rápido
echo "<h2>Teste de DocumentRoot / PHP</h2>";
echo "<p>getcwd(): " . htmlspecialchars(getcwd()) . "</p>";
echo "<p>__FILE__: " . htmlspecialchars(__FILE__) . "</p>";
if (defined('BASE_URL')) {
	echo "<p>BASE_URL: " . htmlspecialchars(BASE_URL) . "</p>";
}
echo "<h3>phpinfo()</h3>";
phpinfo();

?>

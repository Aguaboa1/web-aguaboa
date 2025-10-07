<?php
// Inicializar ambiente (sessão, helpers, constantes)
require_once __DIR__ . '/../config/init.php';

// Garantir que o parâmetro 'id' esteja disponível para a view,
// mesmo que o servidor não tenha populado automaticamente $_GET.
if (!isset($_GET['id']) || $_GET['id'] === '') {
	// Tentar a query string direta
	if (!empty($_SERVER['QUERY_STRING'])) {
		parse_str($_SERVER['QUERY_STRING'], $qs);
		if (isset($qs['id']) && $qs['id'] !== '') {
			$_GET['id'] = $qs['id'];
		}
	}

	// Tentar extrair do REQUEST_URI (em caso de rewrites)
	if ((!isset($_GET['id']) || $_GET['id'] === '') && !empty($_SERVER['REQUEST_URI'])) {
		$parts = parse_url($_SERVER['REQUEST_URI']);
		if (isset($parts['query'])) {
			parse_str($parts['query'], $qs2);
			if (isset($qs2['id']) && $qs2['id'] !== '') {
				$_GET['id'] = $qs2['id'];
			}
		}
	}
}

// Normalizar valor de id em variável $id para compatibilidade
if (isset($_GET['id'])) {
	$id = intval($_GET['id']);
	$_GET['id'] = $id;
}

// Incluir view de edição
require_once __DIR__ . '/../src/views/departments/editar_lancamento_producao.php';

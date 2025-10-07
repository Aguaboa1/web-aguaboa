<?php
session_start();
require_once __DIR__ . '/../src/models/Producao.php';
require_once __DIR__ . '/../config/database.php';

// Validação mínima
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /gestao-aguaboa-php/public/producao_lancamento_editar.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$produto_id = isset($_POST['produto_id']) ? intval($_POST['produto_id']) : 0;
$data_producao = isset($_POST['data_producao']) ? $_POST['data_producao'] : null;
$quantidade_produzida = isset($_POST['quantidade_produzida']) ? intval($_POST['quantidade_produzida']) : 0;
$quantidade_perdida = isset($_POST['quantidade_perdida']) ? intval($_POST['quantidade_perdida']) : 0;
$motivo_perda = isset($_POST['motivo_perda']) ? $_POST['motivo_perda'] : '';
$observacoes = isset($_POST['observacoes']) ? $_POST['observacoes'] : '';
$turno = isset($_POST['turno']) ? $_POST['turno'] : '';

if (!$id || !$produto_id || !$data_producao) {
    // redireciona com erro curto (melhor usar flash)
    header('Location: /gestao-aguaboa-php/public/producao_lancamento_editar.php?id=' . $id);
    exit;
}

$producao = new Producao();
$data = [
    'produto_id' => $produto_id,
    'data_producao' => $data_producao,
    'quantidade_produzida' => $quantidade_produzida,
    'quantidade_perdida' => $quantidade_perdida,
    'motivo_perda' => $motivo_perda,
    'observacoes' => $observacoes,
    'turno' => $turno,
    'operador_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
    'supervisor_id' => null
];

$ok = $producao->updateLancamento($id, $data);

// Detect AJAX
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
         || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($isAjax) {
    header('Content-Type: application/json');
    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Lançamento atualizado com sucesso', 'id' => $id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar lançamento']);
    }
    exit;
} else {
    if ($ok) {
        header('Location: /gestao-aguaboa-php/public/producao_lancamento_editar.php?id=' . $id . '&success=1');
        exit;
    } else {
        header('Location: /gestao-aguaboa-php/public/producao_lancamento_editar.php?id=' . $id . '&error=1');
        exit;
    }
}

<?php
require_once '../src/models/Producao.php';
require_once '../src/models/UserPermission.php';

$userPermission = new UserPermission();
$canEdit = $userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'edit');
if (!$canEdit) {
    setFlash('error', 'Acesso negado.');
    redirect('/producao/lancamentos');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        setFlash('error', 'ID do lançamento não informado.');
        redirect('/producao/lancamentos');
    }
    $producaoModel = new Producao();
    $data = [
        'produto_id' => intval($_POST['produto_id']),
        'data_producao' => $_POST['data_producao'],
        'quantidade_produzida' => floatval($_POST['quantidade_produzida']),
        'quantidade_perdida' => floatval($_POST['quantidade_perdida']),
        'motivo_perda' => $_POST['motivo_perda'] ?? '',
        'observacoes' => $_POST['observacoes'] ?? '',
        'turno' => $_POST['turno'] ?? '',
        'operador_id' => $_SESSION['user_id'],
        'supervisor_id' => null
    ];
    if ($producaoModel->updateLancamento($id, $data)) {
        setFlash('success', 'Lançamento atualizado com sucesso!');
    } else {
        setFlash('error', 'Erro ao atualizar lançamento.');
    }
    redirect('/producao/lancamentos');
} else {
    setFlash('error', 'Requisição inválida.');
    redirect('/producao/lancamentos');
}

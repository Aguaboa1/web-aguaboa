<?php
require_once '../src/models/Producao.php';
require_once '../src/models/UserPermission.php';

$userPermission = new UserPermission();
$canEdit = $userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'edit');
if (!$canEdit) {
    setFlash('error', 'Acesso negado.');
    redirect('/producao/lancamentos');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    setFlash('error', 'ID do lançamento não informado.');
    redirect('/producao/lancamentos');
}

$producaoModel = new Producao();
try {
    if ($producaoModel->deleteLancamento($id)) {
        setFlash('success', 'Lançamento excluído com sucesso!');
    } else {
        $err = $producaoModel->getLastError();
        $msg = $err ? 'Erro ao excluir lançamento: ' . htmlspecialchars($err) : 'Erro ao excluir lançamento.';
        setFlash('error', $msg);
    }
} catch (Exception $e) {
    setFlash('error', 'Exceção ao excluir lançamento: ' . htmlspecialchars($e->getMessage()));
}
redirect('/producao/lancamentos');

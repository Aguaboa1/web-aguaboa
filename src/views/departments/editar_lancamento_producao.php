<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/Producao.php';
require_once __DIR__ . '/../../models/UserPermission.php';
if (file_exists(__DIR__ . '/../../utils/functions.php')) {
    require_once __DIR__ . '/../../utils/functions.php';
}
// (sem debug)

// Verificar permissões do usuário
$userPermission = new UserPermission();
$canEdit = $userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'edit');
if (!isset($_SESSION['user_id'])) {
    if (function_exists('setFlash')) setFlash('error', 'Usuário não autenticado.');
    if (function_exists('redirect')) redirect('/login.php');
    exit;
}

if (!$canEdit) {
    if (function_exists('setFlash')) setFlash('error', 'Acesso negado.');
    if (function_exists('redirect')) redirect('/producao/lancamentos');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    setFlash('error', 'ID do lançamento não informado.');
    redirect('/producao/lancamentos');
}

$producaoModel = new Producao();
$lancamento = $producaoModel->getLancamentoById($id);
// (sem debug)
if (!$lancamento) {
    setFlash('error', 'Lançamento não encontrado.');
    redirect('/producao/lancamentos');
}

$produtos = $producaoModel->getAllProdutos();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Lançamento de Produção</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background:#f5f6fa; color:#333; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:#fff; padding:1.5rem 0; box-shadow:0 2px 10px rgba(0,0,0,0.08); }
        .container { max-width:1200px; margin:0 auto; padding:0 1.5rem; }
        .header h1 { font-size:1.6rem; }
        .nav-links { display:flex; gap:1rem; margin-top:0.5rem; }
        .nav-links a { color:rgba(255,255,255,0.9); text-decoration:none; }
        .main { max-width:1100px; margin:2rem auto; }
        .card { background:#fff; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.06); border:1px solid #e9ecef; overflow:hidden; }
        .card-header { padding:1.25rem 1.5rem; background:linear-gradient(135deg,#f8f9fa,#e9ecef); border-bottom:1px solid #dee2e6; font-weight:600; }
        .card-body { padding:1.5rem; }
        .form-group { margin-bottom:1rem; }
        label { display:block; margin-bottom:0.5rem; color:#495057; font-weight:600; }
        .form-control, select, textarea { width:100%; padding:0.65rem; border:1px solid #ced4da; border-radius:6px; font-size:0.95rem; }
        .form-row { display:grid; grid-template-columns: repeat(2,1fr); gap:1rem; }
        .btn { display:inline-block; padding:0.7rem 1.25rem; border-radius:8px; font-weight:600; text-decoration:none; cursor:pointer; border:none; }
        .btn-primary { background:linear-gradient(135deg,#007fa3,#00a8cc); color:#fff; }
        .btn-secondary { background:#6c757d; color:#fff; }
        .actions { margin-top:1rem; }
        .flash { padding:1rem; border-radius:8px; margin-bottom:1rem; }
        .flash-success { background:#e8f5e8; color:#155724; border-left:4px solid #28a745; }
        .flash-error { background:#ffebee; color:#721c24; border-left:4px solid #dc3545; }
        @media (max-width:768px) { .form-row { grid-template-columns:1fr; } }
    </style>
</head>
<body>
        <div class="header">
            <div class="container">
                <h1>✏️ Editar Lançamento de Produção</h1>
                <div class="nav-links">
                    <a href="<?= BASE_URL ?>/producao/lancamentos" style="color:#fff;">← Voltar aos Lançamentos</a>
                </div>
            </div>
        </div>

    <div class="main container">
        <!-- DEBUG MARKER: garantir visibilidade do formulário -->
        <div style="padding:12px; background:#fff3cd; border-left:4px solid #856404; margin-bottom:12px; border-radius:6px;">DEBUG: formulário carregado abaixo</div>
        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $messages): ?>
                <?php foreach ((array)$messages as $message): ?>
                    <div class="flash flash-<?= $type ?>"><?= htmlspecialchars($message) ?></div>
                <?php endforeach; ?>
            <?php endforeach; unset($_SESSION['flash']); ?>
        <?php endif; ?>

    <div class="card" style="max-width:1000px; margin:0 auto;">
            <div class="card-header">Dados do Lançamento</div>
            <div class="card-body">
                <form method="POST" action="/gestao-aguaboa-php/public/producao_lancamento_editar_processa.php">
                    <input type="hidden" name="id" value="<?= $lancamento['id'] ?>">

                    <div class="form-group">
                        <label>Produto</label>
                        <select name="produto_id" class="form-control" required>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?= $produto['id'] ?>" <?= $produto['id'] == $lancamento['produto_id'] ? 'selected' : '' ?>><?= htmlspecialchars($produto['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Data Produção</label>
                            <input type="datetime-local" name="data_producao" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($lancamento['data_producao'])) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Turno</label>
                            <input type="text" name="turno" class="form-control" value="<?= htmlspecialchars($lancamento['turno']) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Quantidade Produzida</label>
                            <input type="number" name="quantidade_produzida" class="form-control" value="<?= $lancamento['quantidade_produzida'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Quantidade Perdida</label>
                            <input type="number" name="quantidade_perdida" class="form-control" value="<?= $lancamento['quantidade_perdida'] ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Motivo da Perda</label>
                        <input type="text" name="motivo_perda" class="form-control" value="<?= htmlspecialchars($lancamento['motivo_perda']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Observações</label>
                        <textarea name="observacoes" class="form-control" rows="4"><?= htmlspecialchars($lancamento['observacoes']) ?></textarea>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary">💾 Salvar Alterações</button>
                        <a href="<?= BASE_URL ?>/producao/lancamentos" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

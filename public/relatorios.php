<?php
require_once '../config/init.php';
require_once '../src/models/Producao.php';
require_once '../src/models/UserPermission.php';

// Verificar autenticação - melhorada
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Tentar restaurar sessão ou redirecionar para login
    setFlash('info', 'Faça login para acessar os relatórios de produção');
    redirect('/auth/login');
}

// Verificar permissão usando o sistema de permissões por departamento
$userPermission = new UserPermission();
if (!$userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'view')) {
    setFlash('error', 'Acesso negado ao departamento de produção. Entre em contato com o administrador para solicitar permissões.');
    redirect('/departments');
}

// Verificar permissões do usuário
$canEdit = $userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'edit');
$permissionText = $canEdit ? 'Editor (Acesso Total)' : 'Visualizador';

$producaoModel = new Producao();
$produtos = $producaoModel->getAllProdutos();

// Verificar se há dados para exibir
$dadosRelatorio = null;
$totaisRelatorio = null;
$filtros = [
    'data_inicial' => '',
    'data_final' => '',
    'insumo_id' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'gerar_relatorio') {
    $filtros['data_inicial'] = sanitize($_POST['data_inicial']);
    $filtros['data_final'] = sanitize($_POST['data_final']);
    $filtros['insumo_id'] = sanitize($_POST['insumo_id']);
    
    if (!empty($filtros['data_inicial']) && !empty($filtros['data_final'])) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT 
                p.nome as insumo,
                p.codigo,
                DATE(pl.data_producao) as data,
                SUM(pl.quantidade_produzida) as consumido,
                SUM(pl.quantidade_perdida) as perdido,
                COUNT(*) as lancamentos,
                ROUND(AVG(CASE 
                    WHEN pl.quantidade_produzida > 0 
                    THEN ((pl.quantidade_produzida - pl.quantidade_perdida) / pl.quantidade_produzida) * 100 
                    ELSE 0 
                END), 2) as eficiencia_media
            FROM producao_lancamentos pl
            JOIN produtos p ON pl.produto_id = p.id
            WHERE pl.data_producao BETWEEN ? AND ?
        ";
        
        $params = [$filtros['data_inicial'], $filtros['data_final']];
        
        if ($filtros['insumo_id']) {
            $sql .= " AND p.id = ?";
            $params[] = $filtros['insumo_id'];
        }
        
        $sql .= " 
            GROUP BY p.id, DATE(pl.data_producao)
            ORDER BY pl.data_producao DESC, p.nome
        ";
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $dadosRelatorio = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calcular totais
            $totaisRelatorio = [
                'consumido' => 0,
                'perdido' => 0,
                'lancamentos' => 0
            ];
            
            foreach ($dadosRelatorio as $row) {
                $totaisRelatorio['consumido'] += $row['consumido'];
                $totaisRelatorio['perdido'] += $row['perdido'];
                $totaisRelatorio['lancamentos'] += $row['lancamentos'];
            }
        } catch (Exception $e) {
            setFlash('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    } else {
        setFlash('error', 'Período é obrigatório para gerar o relatório');
    }
}

// Definir datas padrão se não informadas
if (empty($filtros['data_inicial'])) {
    $filtros['data_inicial'] = date('Y-m-01'); // Primeiro dia do mês atual
}
if (empty($filtros['data_final'])) {
    $filtros['data_final'] = date('Y-m-d'); // Hoje
}

// Buscar nome do insumo se especificado
$nomeInsumoSelecionado = 'Todos os insumos';
if ($filtros['insumo_id']) {
    foreach ($produtos as $produto) {
        if ($produto['id'] == $filtros['insumo_id']) {
            $nomeInsumoSelecionado = $produto['nome'];
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios de Produção - Web Aguaboa</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f6fa;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .main-content {
            padding: 2rem 0;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            font-size: 1.1rem;
            color: #495057;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .filters-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #495057;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007fa3;
            box-shadow: 0 0 0 3px rgba(0, 127, 163, 0.1);
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007fa3, #00a8cc);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #005f7a, #007fa3);
            transform: translateY(-2px);
        }
        
        .btn-info {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        
        .actions-row {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }
        
        .relatorio-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .relatorio-header {
            background: linear-gradient(135deg, #007fa3, #00a8cc);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .relatorio-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-bottom: 1px solid #dee2e6;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid #f1f3f5;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        
        tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .totals-row {
            background: linear-gradient(135deg, #007fa3, #00a8cc);
            color: white;
            font-weight: bold;
        }
        
        .totals-row td {
            border-bottom: none;
        }
        
        .summary-section {
            padding: 2rem;
            background: #f8f9fa;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        
        .summary-item {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #007fa3;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .summary-value {
            font-size: 2rem;
            font-weight: bold;
            color: #007fa3;
            margin-bottom: 0.5rem;
        }
        
        .summary-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        
        .alert-info {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            color: #1976d2;
        }
        
        .alert-success {
            background: #e8f5e8;
            border: 1px solid #c8e6c9;
            color: #388e3c;
        }
        
        .alert-error {
            background: #ffebee;
            border: 1px solid #ffcdd2;
            color: #d32f2f;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .filters-form {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .actions-row {
                flex-direction: column;
            }
            
            .summary-grid {
                grid-template-columns: 1fr;
            }
            
            th, td {
                padding: 0.75rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="header-content">
                <h1>📊 Relatórios de Produção</h1>
                <div style="display: flex; align-items: center; gap: 2rem;">
                    <!-- Informações do Usuário -->
                    <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.85rem; color: rgba(255,255,255,0.9);">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span>👤</span>
                            <span><strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span><?= $canEdit ? '✏️' : '�️' ?></span>
                            <span><?= $permissionText ?> | Relatórios</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span>📅</span>
                            <span><?= date('d/m/Y H:i') ?></span>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/producao" class="back-btn">
                        ← Voltar à Produção
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container main-content">
        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                <div class="alert alert-<?= $type ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endforeach; ?>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">🔍 Filtros do Relatório</div>
            <div class="card-body">
                <form method="POST" class="filters-form">
                    <input type="hidden" name="action" value="gerar_relatorio">
                    
                    <div class="form-group">
                        <label>Data Inicial:</label>
                        <input type="date" name="data_inicial" class="form-control" value="<?= htmlspecialchars($filtros['data_inicial']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Data Final:</label>
                        <input type="date" name="data_final" class="form-control" value="<?= htmlspecialchars($filtros['data_final']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Insumo:</label>
                        <select name="insumo_id" class="form-control">
                            <option value="">Todos os insumos</option>
                            <?php foreach ($produtos as $produto): ?>
                            <option value="<?= $produto['id'] ?>" <?= $filtros['insumo_id'] == $produto['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($produto['nome']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            📊 Gerar Relatório
                        </button>
                    </div>
                </form>
                
                <?php if ($dadosRelatorio !== null): ?>
                <div class="actions-row">
                    <form method="POST" action="relatorio_consumo.php" target="_blank" style="display: inline;">
                        <input type="hidden" name="data_inicial" value="<?= htmlspecialchars($filtros['data_inicial']) ?>">
                        <input type="hidden" name="data_final" value="<?= htmlspecialchars($filtros['data_final']) ?>">
                        <input type="hidden" name="insumo_id" value="<?= htmlspecialchars($filtros['insumo_id']) ?>">
                        <button type="submit" class="btn btn-info">🔗 Nova Página</button>
                    </form>
                    
                    <form method="POST" action="exportar_relatorio.php" style="display: inline;">
                        <input type="hidden" name="formato" value="pdf">
                        <input type="hidden" name="data_inicial" value="<?= htmlspecialchars($filtros['data_inicial']) ?>">
                        <input type="hidden" name="data_final" value="<?= htmlspecialchars($filtros['data_final']) ?>">
                        <input type="hidden" name="insumo_id" value="<?= htmlspecialchars($filtros['insumo_id']) ?>">
                        <button type="submit" class="btn btn-danger">📄 Exportar PDF</button>
                    </form>
                    
                    <form method="POST" action="exportar_relatorio.php" style="display: inline;">
                        <input type="hidden" name="formato" value="excel">
                        <input type="hidden" name="data_inicial" value="<?= htmlspecialchars($filtros['data_inicial']) ?>">
                        <input type="hidden" name="data_final" value="<?= htmlspecialchars($filtros['data_final']) ?>">
                        <input type="hidden" name="insumo_id" value="<?= htmlspecialchars($filtros['insumo_id']) ?>">
                        <button type="submit" class="btn btn-success">📊 Exportar Excel</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($dadosRelatorio !== null): ?>
        <div class="relatorio-container">
            <div class="relatorio-header">
                <h2>📊 Relatório de Consumo de Insumos</h2>
            </div>
            
            <div class="relatorio-info">
                <strong>Período:</strong> <?= date('d/m/Y', strtotime($filtros['data_inicial'])) ?> até <?= date('d/m/Y', strtotime($filtros['data_final'])) ?> | 
                <strong>Insumo:</strong> <?= htmlspecialchars($nomeInsumoSelecionado) ?> | 
                <strong>Gerado em:</strong> <?= date('d/m/Y H:i:s') ?>
            </div>
            
            <?php if (empty($dadosRelatorio)): ?>
            <div style="padding: 3rem; text-align: center; color: #6c757d;">
                <h3>⚠️ Nenhum dado encontrado</h3>
                <p>Não foram encontrados lançamentos para o período e filtros selecionados.</p>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Insumo</th>
                            <th>Código</th>
                            <th class="text-right">Consumido</th>
                            <th class="text-right">Perdido</th>
                            <th class="text-right">Eficiência</th>
                            <th class="text-center">Lançamentos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dadosRelatorio as $row): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($row['data'])) ?></td>
                            <td><?= htmlspecialchars($row['insumo']) ?></td>
                            <td><code><?= htmlspecialchars($row['codigo']) ?></code></td>
                            <td class="text-right" style="color: #28a745;"><?= number_format($row['consumido']) ?></td>
                            <td class="text-right" style="color: #dc3545;"><?= number_format($row['perdido']) ?></td>
                            <td class="text-right"><?= $row['eficiencia_media'] ?>%</td>
                            <td class="text-center"><?= $row['lancamentos'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="totals-row">
                            <td colspan="3">TOTAIS</td>
                            <td class="text-right"><?= number_format($totaisRelatorio['consumido']) ?></td>
                            <td class="text-right"><?= number_format($totaisRelatorio['perdido']) ?></td>
                            <td class="text-right">
                                <?= $totaisRelatorio['consumido'] > 0 ? round((($totaisRelatorio['consumido'] - $totaisRelatorio['perdido']) / $totaisRelatorio['consumido']) * 100, 2) : 0 ?>%
                            </td>
                            <td class="text-center"><?= $totaisRelatorio['lancamentos'] ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="summary-section">
                <h3>📈 Resumo do Período</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-value"><?= number_format($totaisRelatorio['consumido']) ?></div>
                        <div class="summary-label">Total Consumido</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value" style="color: #dc3545;"><?= number_format($totaisRelatorio['perdido']) ?></div>
                        <div class="summary-label">Total Perdido</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value" style="color: <?= ($totaisRelatorio['consumido'] > 0 ? round((($totaisRelatorio['consumido'] - $totaisRelatorio['perdido']) / $totaisRelatorio['consumido']) * 100, 2) : 0) >= 90 ? '#28a745' : (($totaisRelatorio['consumido'] > 0 ? round((($totaisRelatorio['consumido'] - $totaisRelatorio['perdido']) / $totaisRelatorio['consumido']) * 100, 2) : 0) >= 80 ? '#ffc107' : '#dc3545') ?>;">
                            <?= $totaisRelatorio['consumido'] > 0 ? round((($totaisRelatorio['consumido'] - $totaisRelatorio['perdido']) / $totaisRelatorio['consumido']) * 100, 2) : 0 ?>%
                        </div>
                        <div class="summary-label">Eficiência Geral</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value"><?= $totaisRelatorio['lancamentos'] ?></div>
                        <div class="summary-label">Total Lançamentos</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
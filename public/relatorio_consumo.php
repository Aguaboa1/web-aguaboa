<?php
/**
 * Relatório de Consumo de Insumos
 */

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../src/models/Producao.php';
require_once __DIR__ . '/../src/models/UserPermission.php';

// Auto-login se necessário
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, username, role FROM users WHERE username = 'Rogerio' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
    }
}

// Verificar autenticação
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    die('<div style="padding: 2rem; text-align: center; font-family: Arial; color: #d32f2f;"><h2>Sessão inválida</h2><p>Faça login novamente.</p><a href="../public/login_auto.php" style="color: #007fa3;">Clique aqui para fazer login</a></div>');
}

// Verificar permissão usando sistema moderno
$userPermission = new UserPermission();
if (!$userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'view')) {
    die('<div style="padding: 2rem; text-align: center; font-family: Arial; color: #d32f2f;"><h2>Acesso Negado</h2><p>Você não tem permissão para acessar os relatórios de produção.</p><p><strong>Usuário:</strong> ' . htmlspecialchars($_SESSION['username']) . '</p><p><strong>Perfil:</strong> ' . htmlspecialchars($_SESSION['role']) . '</p><a href="../public/departments" style="color: #007fa3;">Voltar aos Setores</a></div>');
}

$producaoModel = new Producao();

// Receber parâmetros
$dataInicial = $_POST['data_inicial'] ?? date('Y-m-01');
$dataFinal = $_POST['data_final'] ?? date('Y-m-d');
$insumoId = $_POST['insumo_id'] ?? '';

// Buscar nome do insumo se especificado
$nomeInsumo = 'Todos os insumos';
if ($insumoId) {
    $db = Database::getInstance()->getConnection();
    $stmtInsumo = $db->prepare("SELECT nome FROM produtos WHERE id = ?");
    $stmtInsumo->execute([$insumoId]);
    $insumo = $stmtInsumo->fetch(PDO::FETCH_ASSOC);
    if ($insumo) {
        $nomeInsumo = $insumo['nome'];
    }
}

// Buscar dados
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

$params = [$dataInicial, $dataFinal];

if ($insumoId) {
    $sql .= " AND p.id = ?";
    $params[] = $insumoId;
}

$sql .= " 
    GROUP BY p.id, DATE(pl.data_producao)
    ORDER BY pl.data_producao DESC, p.nome
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Consumo - Web Aguaboa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .filters { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #007fa3; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .summary { background: #e9ecef; padding: 15px; border-radius: 8px; margin-top: 20px; }
        .print-btn { background: #007fa3; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 20px; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <div style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
        <button class="print-btn" onclick="window.print()">🖨️ Imprimir</button>
        <button class="print-btn" onclick="window.close()" style="background: #6c757d;">❌ Fechar</button>
        <button class="print-btn" onclick="window.opener.focus(); window.close()" style="background: #007fa3;">↩️ Voltar ao Sistema</button>
    </div>
    
    <div class="header">
        <h1>📊 Relatório de Consumo de Insumos</h1>
        <h2>Web Aguaboa - Gestão de Produção</h2>
    </div>
    
    <div class="filters">
        <strong>Período:</strong> <?= date('d/m/Y', strtotime($dataInicial)) ?> até <?= date('d/m/Y', strtotime($dataFinal)) ?><br>
        <strong>Insumo:</strong> <?= htmlspecialchars($nomeInsumo) ?><br>
        <strong>Gerado em:</strong> <?= date('d/m/Y H:i:s') ?><br>
        <strong>Usuário:</strong> <?= $_SESSION['username'] ?>
    </div>
    
    <?php if (empty($dados)): ?>
    <div class="summary">
        <h3>⚠️ Nenhum dado encontrado</h3>
        <p>Não foram encontrados lançamentos para o período selecionado.</p>
    </div>
    <?php else: ?>
    
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Insumo</th>
                <th>Código</th>
                <th>Consumido</th>
                <th>Perdido</th>
                <th>Eficiência</th>
                <th>Lançamentos</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalConsumido = 0;
            $totalPerdido = 0;
            $totalLancamentos = 0;
            
            foreach ($dados as $row): 
                $totalConsumido += $row['consumido'];
                $totalPerdido += $row['perdido'];
                $totalLancamentos += $row['lancamentos'];
            ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($row['data'])) ?></td>
                <td><?= htmlspecialchars($row['insumo']) ?></td>
                <td><?= htmlspecialchars($row['codigo']) ?></td>
                <td style="text-align: right;"><?= number_format($row['consumido']) ?></td>
                <td style="text-align: right; color: #dc3545;"><?= number_format($row['perdido']) ?></td>
                <td style="text-align: right;"><?= $row['eficiencia_media'] ?>%</td>
                <td style="text-align: center;"><?= $row['lancamentos'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #007fa3; color: white; font-weight: bold;">
                <td colspan="3">TOTAIS</td>
                <td style="text-align: right;"><?= number_format($totalConsumido) ?></td>
                <td style="text-align: right;"><?= number_format($totalPerdido) ?></td>
                <td style="text-align: right;">
                    <?= $totalConsumido > 0 ? round((($totalConsumido - $totalPerdido) / $totalConsumido) * 100, 2) : 0 ?>%
                </td>
                <td style="text-align: center;"><?= $totalLancamentos ?></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="summary">
        <h3>📈 Resumo do Período</h3>
        <p><strong>Total Consumido:</strong> <?= number_format($totalConsumido) ?> unidades</p>
        <p><strong>Total Perdido:</strong> <?= number_format($totalPerdido) ?> unidades</p>
        <p><strong>Eficiência Geral:</strong> 
            <?= $totalConsumido > 0 ? round((($totalConsumido - $totalPerdido) / $totalConsumido) * 100, 2) : 0 ?>%
        </p>
        <p><strong>Total de Lançamentos:</strong> <?= $totalLancamentos ?></p>
    </div>
    
    <?php endif; ?>
</body>
</html>
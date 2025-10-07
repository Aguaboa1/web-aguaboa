<?php
/**
 * Exportar Relatórios em PDF e Excel
 */

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../src/models/Producao.php';
require_once __DIR__ . '/../src/models/UserPermission.php';

// Verificar autenticação
requireAuth();

// Verificar permissão usando sistema moderno de departamentos
$userPermission = new UserPermission();
if (!$userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'view')) {
    die('<div style="padding: 2rem; text-align: center; font-family: Arial; color: #d32f2f;"><h2>Acesso Negado</h2><p>Você não tem permissão para exportar relatórios de produção.</p><p><strong>Usuário:</strong> ' . htmlspecialchars($_SESSION['username']) . '</p><p><strong>Perfil:</strong> ' . htmlspecialchars($_SESSION['role']) . '</p><a href="/gestao-aguaboa-php/public/departments" style="color: #007fa3;">Voltar aos Setores</a></div>');
}

// Verificar se usuário pode editar (editores têm acesso total)
$canEdit = $userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'edit');

$producaoModel = new Producao();

// Receber parâmetros
$formato = $_POST['formato'] ?? '';
$dataInicial = $_POST['data_inicial'] ?? date('Y-m-01');
$dataFinal = $_POST['data_final'] ?? date('Y-m-d');
$insumoId = $_POST['insumo_id'] ?? '';

if (empty($formato) || !in_array($formato, ['pdf', 'excel'])) {
    die('Formato inválido');
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

// Buscar nome do insumo se especificado
$nomeInsumo = 'Todos os insumos';
if ($insumoId) {
    $stmtInsumo = $db->prepare("SELECT nome FROM produtos WHERE id = ?");
    $stmtInsumo->execute([$insumoId]);
    $insumo = $stmtInsumo->fetch(PDO::FETCH_ASSOC);
    if ($insumo) {
        $nomeInsumo = $insumo['nome'];
    }
}

// Calcular totais
$totalConsumido = 0;
$totalPerdido = 0;
$totalLancamentos = 0;

foreach ($dados as $row) {
    $totalConsumido += $row['consumido'];
    $totalPerdido += $row['perdido'];
    $totalLancamentos += $row['lancamentos'];
}

$eficienciaGeral = $totalConsumido > 0 ? 
    round((($totalConsumido - $totalPerdido) / $totalConsumido) * 100, 2) : 0;

if ($formato === 'pdf') {
    // Exportar como PDF (HTML para PDF simples)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="relatorio_consumo_' . date('Y-m-d_H-i-s') . '.pdf"');
    
    // Para uma implementação simples, vamos usar HTML que o navegador pode imprimir como PDF
    header('Content-Type: text/html');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Relatório de Consumo - PDF</title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; }
            .header { text-align: center; margin-bottom: 20px; }
            .info { background: #f5f5f5; padding: 10px; margin-bottom: 15px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #007fa3; color: white; }
            .total { background-color: #007fa3; color: white; font-weight: bold; }
            .summary { background: #f5f5f5; padding: 15px; margin-top: 15px; }
        </style>
        <script>window.onload = function() { window.print(); }</script>
    </head>
    <body>
        <div class="header">
            <h1>📊 Relatório de Consumo de Insumos</h1>
            <h2>Web Aguaboa - Gestão de Produção</h2>
        </div>
        
        <div class="info">
            <strong>Período:</strong> <?= date('d/m/Y', strtotime($dataInicial)) ?> até <?= date('d/m/Y', strtotime($dataFinal)) ?><br>
            <strong>Insumo:</strong> <?= htmlspecialchars($nomeInsumo) ?><br>
            <strong>Gerado em:</strong> <?= date('d/m/Y H:i:s') ?><br>
            <strong>Usuário:</strong> <?= $_SESSION['username'] ?>
        </div>
        
        <?php if (!empty($dados)): ?>
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
                <?php foreach ($dados as $row): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($row['data'])) ?></td>
                    <td><?= htmlspecialchars($row['insumo']) ?></td>
                    <td><?= htmlspecialchars($row['codigo']) ?></td>
                    <td style="text-align: right;"><?= number_format($row['consumido']) ?></td>
                    <td style="text-align: right;"><?= number_format($row['perdido']) ?></td>
                    <td style="text-align: right;"><?= $row['eficiencia_media'] ?>%</td>
                    <td style="text-align: center;"><?= $row['lancamentos'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total">
                    <td colspan="3">TOTAIS</td>
                    <td style="text-align: right;"><?= number_format($totalConsumido) ?></td>
                    <td style="text-align: right;"><?= number_format($totalPerdido) ?></td>
                    <td style="text-align: right;"><?= $eficienciaGeral ?>%</td>
                    <td style="text-align: center;"><?= $totalLancamentos ?></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="summary">
            <h3>📈 Resumo do Período</h3>
            <p><strong>Total Consumido:</strong> <?= number_format($totalConsumido) ?> unidades</p>
            <p><strong>Total Perdido:</strong> <?= number_format($totalPerdido) ?> unidades</p>
            <p><strong>Eficiência Geral:</strong> <?= $eficienciaGeral ?>%</p>
            <p><strong>Total de Lançamentos:</strong> <?= $totalLancamentos ?></p>
        </div>
        <?php endif; ?>
    </body>
    </html>
    <?php
    
} elseif ($formato === 'excel') {
    // Exportar como Excel (CSV com extensão XLS)
    $filename = 'relatorio_consumo_' . date('Y-m-d_H-i-s') . '.xls';
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<html>';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<table border="1">';
    
    // Cabeçalho do relatório
    echo '<tr><td colspan="7" style="text-align: center; font-weight: bold; font-size: 16px;">Relatório de Consumo de Insumos</td></tr>';
    echo '<tr><td colspan="7" style="text-align: center;">Web Aguaboa - Gestão de Produção</td></tr>';
    echo '<tr><td colspan="7"></td></tr>';
    
    // Informações do filtro
    echo '<tr><td><strong>Período:</strong></td><td colspan="6">' . date('d/m/Y', strtotime($dataInicial)) . ' até ' . date('d/m/Y', strtotime($dataFinal)) . '</td></tr>';
    echo '<tr><td><strong>Insumo:</strong></td><td colspan="6">' . htmlspecialchars($nomeInsumo) . '</td></tr>';
    echo '<tr><td><strong>Gerado em:</strong></td><td colspan="6">' . date('d/m/Y H:i:s') . '</td></tr>';
    echo '<tr><td><strong>Usuário:</strong></td><td colspan="6">' . $_SESSION['username'] . '</td></tr>';
    echo '<tr><td colspan="7"></td></tr>';
    
    if (!empty($dados)) {
        // Cabeçalho da tabela
        echo '<tr style="background-color: #007fa3; color: white; font-weight: bold;">';
        echo '<td>Data</td>';
        echo '<td>Insumo</td>';
        echo '<td>Código</td>';
        echo '<td>Consumido</td>';
        echo '<td>Perdido</td>';
        echo '<td>Eficiência</td>';
        echo '<td>Lançamentos</td>';
        echo '</tr>';
        
        // Dados
        foreach ($dados as $row) {
            echo '<tr>';
            echo '<td>' . date('d/m/Y', strtotime($row['data'])) . '</td>';
            echo '<td>' . htmlspecialchars($row['insumo']) . '</td>';
            echo '<td>' . htmlspecialchars($row['codigo']) . '</td>';
            echo '<td style="text-align: right;">' . number_format($row['consumido']) . '</td>';
            echo '<td style="text-align: right;">' . number_format($row['perdido']) . '</td>';
            echo '<td style="text-align: right;">' . $row['eficiencia_media'] . '%</td>';
            echo '<td style="text-align: center;">' . $row['lancamentos'] . '</td>';
            echo '</tr>';
        }
        
        // Totais
        echo '<tr style="background-color: #007fa3; color: white; font-weight: bold;">';
        echo '<td colspan="3">TOTAIS</td>';
        echo '<td style="text-align: right;">' . number_format($totalConsumido) . '</td>';
        echo '<td style="text-align: right;">' . number_format($totalPerdido) . '</td>';
        echo '<td style="text-align: right;">' . $eficienciaGeral . '%</td>';
        echo '<td style="text-align: center;">' . $totalLancamentos . '</td>';
        echo '</tr>';
        
        echo '<tr><td colspan="7"></td></tr>';
        
        // Resumo
        echo '<tr><td colspan="7" style="font-weight: bold;">📈 Resumo do Período</td></tr>';
        echo '<tr><td><strong>Total Consumido:</strong></td><td colspan="6">' . number_format($totalConsumido) . ' unidades</td></tr>';
        echo '<tr><td><strong>Total Perdido:</strong></td><td colspan="6">' . number_format($totalPerdido) . ' unidades</td></tr>';
        echo '<tr><td><strong>Eficiência Geral:</strong></td><td colspan="6">' . $eficienciaGeral . '%</td></tr>';
        echo '<tr><td><strong>Total de Lançamentos:</strong></td><td colspan="6">' . $totalLancamentos . '</td></tr>';
    } else {
        echo '<tr><td colspan="7">Nenhum dado encontrado para o período selecionado.</td></tr>';
    }
    
    echo '</table>';
    echo '</body></html>';
}
?>
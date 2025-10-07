<?php
require_once '../src/models/Producao.php';
require_once '../src/models/UserPermission.php';

// Verificar permissões do usuário
$userPermission = new UserPermission();
$canEdit = $userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'edit');
$permissionText = $canEdit ? 'Editor (Acesso Total)' : 'Visualizador';

$producaoModel = new Producao();
$lancamentos = $producaoModel->getLancamentosRecentes(50);
$produtos = $producaoModel->getAllProdutos();

// Estatísticas dos lançamentos
$estatisticas = [
    'total_lancamentos' => count($lancamentos),
    'hoje' => 0,
    'mes' => 0,
    'total_consumido' => 0
];

$hoje = date('Y-m-d');
$mesAtual = date('Y-m');

foreach ($lancamentos as $lancamento) {
    $dataLancamento = substr($lancamento['data_producao'], 0, 10);
    if ($dataLancamento === $hoje) {
        $estatisticas['hoje']++;
    }
    if (substr($dataLancamento, 0, 7) === $mesAtual) {
        $estatisticas['mes']++;
    }
    $estatisticas['total_consumido'] += $lancamento['quantidade_produzida'];
}
?>

<?php require_once __DIR__ . '/../layout/header.php'; ?>
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

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .actions-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-ok {
            background: #d4edda;
            color: #155724;
        }

        .status-warning {
            background: #fff3cd;
            color: #856404;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .nav-links {
                gap: 0.5rem;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1>📊 Lançamentos de Produção</h1>
                    <div style="display: flex; align-items: center; gap: 2rem; font-size: 0.85rem; margin-top: 0.5rem; color: rgba(255,255,255,0.9);">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span>👤</span>
                            <span><strong>Rogerio</strong></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span>👔</span>
                            <span>Equipe | Lançamentos</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span>📅</span>
                            <span><?= date('d/m/Y H:i') ?></span>
                        </div>
                    </div>
                    <div class="nav-links">
                        <a href="<?= BASE_URL ?>/departments">🏢 Setores</a>
                        <a href="<?= BASE_URL ?>/insumos">📦 Insumos</a>
                        <a href="<?= BASE_URL ?>/producao/lancamentos">📊 Lançamentos</a>
                        <a href="<?= BASE_URL ?>/relatorios">📈 Relatórios</a>
                        <a href="<?= BASE_URL ?>/auth/logout">🚪 Sair</a>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/producao" class="back-btn">
                    ← Voltar à Produção
                </a>
            </div>
        </div>
    </div>

    <div class="container main-container">
        
        <!-- Estatísticas -->
        <div class="stats-container">
            <div class="stat-card total">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <div class="stat-number"><?= $estatisticas['total_lancamentos'] ?></div>
                    <div class="stat-label">Total de Lançamentos</div>
                </div>
            </div>
            <div class="stat-card hoje">
                <div class="stat-icon">📅</div>
                <div class="stat-content">
                    <div class="stat-number"><?= $estatisticas['hoje'] ?></div>
                    <div class="stat-label">Lançamentos Hoje</div>
                </div>
            </div>
            <div class="stat-card mes">
                <div class="stat-icon">📈</div>
                <div class="stat-content">
                    <div class="stat-number"><?= $estatisticas['mes'] ?></div>
                    <div class="stat-label">Lançamentos no Mês</div>
                </div>
            </div>
            <div class="stat-card consumido">
                <div class="stat-icon">⚡</div>
                <div class="stat-content">
                    <div class="stat-number"><?= number_format($estatisticas['total_consumido']) ?></div>
                    <div class="stat-label">Total Consumido</div>
                </div>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="actions-section">
            <h3 style="margin-bottom: 1rem;">⚡ Ações Rápidas</h3>
            <div class="actions-grid">
                <a href="<?= BASE_URL ?>/producao?open_lancamento=1" target="_blank" class="btn btn-primary">
                    ➕ Novo Lançamento
                </a>
                <a href="<?= BASE_URL ?>/insumos" class="btn btn-primary">
                    📦 Gerenciar Insumos
                </a>
                <a href="<?= BASE_URL ?>/relatorios" class="btn btn-primary">
                    📈 Relatórios Detalhados
                </a>
            </div>
        </div>

        <!-- Lista de Lançamentos -->
        <div class="header">
            <div class="container">
                <h1>Produção — Lançamentos</h1>
                <div style="display:flex; align-items:center; gap:12px;">
                    <div class="nav-links">
                        <a href="<?= BASE_URL ?>/departments">🏢 Setores</a>
                        <a href="<?= BASE_URL ?>/insumos">📦 Insumos</a>
                        <a href="<?= BASE_URL ?>/producao">🏭 Produção</a>
                        <a href="<?= BASE_URL ?>/relatorios">📈 Relatórios</a>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="font-weight:600; opacity:0.95">👤 <?= htmlspecialchars($_SESSION['username']) ?></div>
                        <a href="<?= BASE_URL ?>/auth/logout">🚪 Sair</a>
                    </div>
                </div>
            </div>
        </div>
                                    <th>Insumo</th>
                                    <th>Qtd. Consumida</th>
                                    <th>Qtd. Perdida</th>
                                    <th>Eficiência</th>
                                    <th>Observações</th>
                                    <th>Status</th>
                                <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lancamentos as $lancamento): ?>
                                <?php 
                                $eficiencia = $lancamento['quantidade_produzida'] > 0 ? 
                                    round((($lancamento['quantidade_produzida'] - $lancamento['quantidade_perdida']) / $lancamento['quantidade_produzida']) * 100, 1) : 0;
                                $statusClass = $eficiencia >= 95 ? 'status-ok' : 'status-warning';
                                ?>
                                <tr>
                                    <td>
                                        <div><?= date('d/m/Y', strtotime($lancamento['data_producao'])) ?></div>
                                        <small style="color: #6c757d;"><?= date('H:i', strtotime($lancamento['data_producao'])) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($lancamento['produto_nome']) ?></strong>
                                        <br>
                                        <small style="color: #6c757d;"><?= htmlspecialchars($lancamento['produto_codigo']) ?></small>
                                    </td>
                                    <td style="color: #28a745; font-weight: 600;">
                                        <?= number_format($lancamento['quantidade_produzida']) ?>
                                    </td>
                                    <td style="color: #dc3545; font-weight: 600;">
                                        <?= number_format($lancamento['quantidade_perdida']) ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= $eficiencia ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($lancamento['observacoes'] ?? 'Sem observações') ?>
                                    </td>
                                    <td>
                                        <?php if ($eficiencia >= 95): ?>
                                            <span class="status-badge status-ok">✅ Ótimo</span>
                                        <?php elseif ($eficiencia >= 85): ?>
                                            <span class="status-badge status-warning">⚠️ Aceitável</span>
                                        <?php else: ?>
                                            <span class="status-badge status-warning">🔴 Atenção</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($canEdit): ?>
                                        <a href="/gestao-aguaboa-php/public/producao_lancamento_editar.php?id=<?= $lancamento['id'] ?>" class="btn btn-primary btn-sm">✏️ Editar</a>
<a href="/gestao-aguaboa-php/public/producao_lancamento_excluir.php?id=<?= $lancamento['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este lançamento?');">🗑️ Excluir</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (count($lancamentos) >= 50): ?>
                    <div style="text-align: center; margin-top: 1.5rem; padding: 1rem; background: #f8f9fa; border-radius: 6px;">
                        📄 Mostrando os 50 lançamentos mais recentes. 
                        <a href="<?= BASE_URL ?>/relatorios" class="btn btn-primary btn-sm" style="margin-left: 1rem;">
                            Ver Histórico Completo
                        </a>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</body>
</html>
<?php
require_once '../src/models/EstoqueInsumos.php';
require_once '../src/models/UserPermission.php';

<?php require_once __DIR__ . '/../layout/header.php'; ?>
            border-collapse: collapse;
            background: white;
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

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
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
                    <h1>📦 Gestão de Insumos</h1>
                    <div style="display: flex; align-items: center; gap: 2rem; font-size: 0.85rem; margin-top: 0.5rem; color: rgba(255,255,255,0.9);">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span>👤</span>
                            <span><strong>Rogerio</strong></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span>👔</span>
                            <span>Equipe | Insumos</span>
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
                <div class="stat-icon">📦</div>
                <div class="stat-content">
                    <div class="stat-number"><?= $estatisticasInsumos['total_insumos'] ?></div>
                    <div class="stat-label">Total de Insumos</div>
                </div>
            </div>
            <div class="stat-card categorias">
                <div class="stat-icon">💰</div>
                <div class="stat-content">
                    <div class="stat-number">R$ <?= number_format($estatisticasInsumos['valor_total'], 0, ',', '.') ?></div>
                    <div class="stat-label">Valor em Estoque</div>
                </div>
            </div>
            <div class="stat-card estoque">
                <div class="stat-icon">⚠️</div>
                <div class="stat-content">
                    <div class="stat-number"><?= $estatisticasInsumos['estoque_baixo'] ?></div>
                    <div class="stat-label">Estoque Baixo</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <div class="stat-number"><?= $estatisticasInsumos['movimentacoes_hoje'] ?></div>
                    <div class="stat-label">Movimentações Hoje</div>
                </div>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="actions-section">
            <h3 style="margin-bottom: 1rem;">⚡ Ações Rápidas</h3>
            <div class="actions-grid">
                <a href="<?= BASE_URL ?>/estoque" class="btn btn-primary">
                    📦 Gerenciar Estoque
                </a>
                <?php if ($canEdit): ?>
                <button onclick="abrirModalLancamento()" class="btn btn-primary">
                    📊 Registrar Lançamento
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Lista de Insumos -->
        <div class="header">
            <div class="container">
                <h1>Insumos — Produção</h1>
                <div style="display:flex; align-items:center; gap:12px;">
                    <div class="nav-links">
                        <a href="<?= BASE_URL ?>/departments">🏢 Setores</a>
                        <a href="<?= BASE_URL ?>/producao">🏭 Produção</a>
                        <a href="<?= BASE_URL ?>/producao/lancamentos">📊 Lançamentos</a>
                        <a href="<?= BASE_URL ?>/relatorios">📈 Relatórios</a>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="font-weight:600; opacity:0.95">👤 <?= htmlspecialchars($_SESSION['username']) ?></div>
                        <a href="<?= BASE_URL ?>/auth/logout">🚪 Sair</a>
                    </div>
                </div>
            </div>
        </div>
                                    <th>Categoria</th>
                                    <th>Quantidade</th>
                                    <th>Unidade</th>
                                    <th>Custo Unit.</th>
                                    <th>Valor Total</th>
                                    <th>Status</th>
                                    <th>Localização</th>
                                    <?php if ($canEdit): ?>
                                    <th>Ações</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($insumos as $insumo): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($insumo['name']) ?></strong>
                                        <?php if (!empty($insumo['description'])): ?>
                                        <br><small style="color: #6c757d;"><?= htmlspecialchars($insumo['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($insumo['category']) ?></td>
                                    <td>
                                        <strong><?= number_format($insumo['current_quantity'], 0, ',', '.') ?></strong>
                                        <br><small style="color: #6c757d;">Min: <?= number_format($insumo['minimum_stock'], 0, ',', '.') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($insumo['unit']) ?></td>
                                    <td>R$ <?= number_format($insumo['unit_cost'], 2, ',', '.') ?></td>
                                    <td>R$ <?= number_format($insumo['current_quantity'] * $insumo['unit_cost'], 2, ',', '.') ?></td>
                                    <td>
                                        <?php
                                        $status = $insumo['status_estoque'];
                                        $statusColors = [
                                            'baixo' => '#dc3545',
                                            'normal' => '#28a745', 
                                            'alto' => '#ffc107'
                                        ];
                                        $statusIcons = [
                                            'baixo' => '⚠️',
                                            'normal' => '✅',
                                            'alto' => '📈'
                                        ];
                                        ?>
                                        <span style="color: <?= $statusColors[$status] ?>">
                                            <?= $statusIcons[$status] ?> <?= ucfirst($status) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($insumo['location'] ?? 'N/A') ?></td>
                                    <?php if ($canEdit): ?>
                                    <td>
                                        <button onclick="registrarLancamento(<?= $insumo['id'] ?>, '<?= htmlspecialchars($insumo['name']) ?>')" class="btn btn-sm btn-primary">
                                            📊 Lançar
                                        </button>
                                        <a href="<?= BASE_URL ?>/estoque/editar/<?= $insumo['id'] ?>" class="btn btn-sm btn-warning">
                                            ✏️ Editar
                                        </a>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
            </div>
        </div>

        <!-- Categorias -->
        <?php if (!empty($estatisticas['categorias'])): ?>
        <div class="card">
            <div class="card-header">🏷️ Insumos por Categoria</div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <?php foreach ($estatisticas['categorias'] as $categoria => $quantidade): ?>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center; border-left: 4px solid #007fa3;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: #007fa3;"><?= $quantidade ?></div>
                        <div style="color: #6c757d; font-size: 0.9rem;"><?= htmlspecialchars($categoria) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Modal Lançamento de Insumo -->
    <?php if ($canEdit): ?>
    <div id="modalLancamentoInsumo" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📊 Registrar Lançamento de Insumo</h3>
                <span class="close" onclick="fecharModal('modalLancamentoInsumo')">&times;</span>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/processar_lancamento_insumo.php">
                <input type="hidden" name="stock_item_id" id="lancamento_stock_id">
                
                <div class="form-group">
                    <label>Insumo:</label>
                    <input type="text" id="lancamento_insumo_name" class="form-control" readonly style="background: #f8f9fa;">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Data do Lançamento:</label>
                        <input type="date" name="data_lancamento" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Turno:</label>
                        <select name="turno" class="form-control" required>
                            <option value="MANHÃ">🌅 Manhã (06:00 - 14:00)</option>
                            <option value="TARDE">🌤️ Tarde (14:00 - 22:00)</option>
                            <option value="NOITE">🌙 Noite (22:00 - 06:00)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Quantidade Utilizada:</label>
                    <input type="number" name="quantidade_utilizada" class="form-control" min="0" step="0.01" required placeholder="Ex: 100">
                    <small class="form-text">Quantidade do insumo que será consumida/utilizada</small>
                </div>
                
                <div class="form-group">
                    <label>Motivo/Finalidade:</label>
                    <select name="motivo" class="form-control" required>
                        <option value="">Selecione o motivo</option>
                        <option value="Produção de Água">💧 Produção de Água</option>
                        <option value="Manutenção de Equipamentos">🔧 Manutenção de Equipamentos</option>
                        <option value="Limpeza e Higienização">🧼 Limpeza e Higienização</option>
                        <option value="Teste de Qualidade">🔬 Teste de Qualidade</option>
                        <option value="Embalagem de Produtos">📦 Embalagem de Produtos</option>
                        <option value="Outros">❓ Outros</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Observações:</label>
                    <textarea name="observacoes" class="form-control" rows="3" placeholder="Observações sobre o uso do insumo, processo realizado, etc."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="fecharModal('modalLancamentoInsumo')" class="btn btn-secondary">
                        ❌ Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        💾 Registrar Lançamento
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
        }
        
        .close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
        }
        
        .modal form {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
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
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007fa3;
            box-shadow: 0 0 0 3px rgba(0, 127, 163, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-text {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8rem;
        }
    </style>

    <script>
        function abrirModalLancamento() {
            document.getElementById('modalLancamentoInsumo').style.display = 'block';
        }
        
        function registrarLancamento(stockItemId, insumoName) {
            document.getElementById('lancamento_stock_id').value = stockItemId;
            document.getElementById('lancamento_insumo_name').value = insumoName;
            document.getElementById('modalLancamentoInsumo').style.display = 'block';
        }
        
        function fecharModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            // Resetar formulário se existir
            const form = document.querySelector('#' + modalId + ' form');
            if (form) {
                form.reset();
            }
        }
        
        // Fechar modal clicando fora
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
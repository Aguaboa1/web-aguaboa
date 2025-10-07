<?php ob_start(); ?>

<div class="stats">
    <div class="stat-card">
        <div class="stat-number"><?= $stats['total'] ?></div>
        <div class="stat-label">Total de Clientes</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['exclusivos'] ?></div>
        <div class="stat-label">Clientes Exclusivos</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['premium'] ?></div>
        <div class="stat-label">Clientes Premium</div>
    </div>
</div>

<div class="search-box">
    <form method="GET" style="display: flex; gap: 1rem; align-items: center;">
        <input type="text" name="search" placeholder="🔍 Buscar cliente ou empresa..." 
               value="<?= htmlspecialchars($search) ?>" style="flex: 1;">
        <select name="sort" style="width: auto;">
            <option value="">Ordenar por nome</option>
            <option value="empresa" <?= $sortBy === 'empresa' ? 'selected' : '' ?>>Por empresa</option>
            <option value="cidade" <?= $sortBy === 'cidade' ? 'selected' : '' ?>>Por cidade</option>
            <option value="total_envases" <?= $sortBy === 'total_envases' ? 'selected' : '' ?>>📦 Maior volume de envase</option>
            <option value="ultimo_envase" <?= $sortBy === 'ultimo_envase' ? 'selected' : '' ?>>📅 Último envase</option>
            <option value="premium_only" <?= $sortBy === 'premium_only' ? 'selected' : '' ?>>Apenas Premium</option>
            <option value="exclusivo_only" <?= $sortBy === 'exclusivo_only' ? 'selected' : '' ?>>Apenas Exclusivos</option>
        </select>
        <button type="submit" class="btn btn-primary">🔍 Buscar</button>
        <a href="<?= BASE_URL ?>/crm" class="btn btn-secondary">🔄 Limpar</a>
    </form>
</div>

<div class="card">
    <div class="card-header">
        📋 Lista de Clientes
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="header-buttons" style="float: right;">
                <button class="btn btn-success" onclick="openModal('createClientModal')">
                    ➕ Cadastrar Novo Cliente
                </button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body" style="padding: 0;">
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Empresa</th>
                    <th>Cidade</th>
                    <th>Tipo</th>
                    <th>📊 Dados de Envase</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($client['cliente']) ?></strong></td>
                    <td><?= htmlspecialchars($client['empresa'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($client['cidade'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($client['tipo_cliente'] ?: '-') ?></td>
                    <td>
                        <?php if (isset($client['total_envases']) && $client['total_envases'] > 0): ?>
                            <div style="font-size: 0.85rem;">
                                <strong>📦 <?= number_format($client['total_envases']) ?></strong> envases<br>
                                <span style="color: #666;">🏷️ <?= $client['produtos_diferentes'] ?> produtos</span><br>
                                <?php if ($client['ultimo_envase']): ?>
                                    <span style="color: #666;">📅 <?= date('d/m/Y', strtotime($client['ultimo_envase'])) ?></span><br>
                                <?php endif; ?>
                                <?php if ($client['produto_principal']): ?>
                                    <span style="color: #007fa3; font-weight: bold;">⭐ <?= htmlspecialchars($client['produto_principal']) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span style="color: #999; font-style: italic;">Sem dados de envase</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($client['cliente_exclusivo']): ?>
                            <span class="status-badge badge-exclusivo">Exclusivo</span>
                        <?php else: ?>
                            <span class="status-badge" style="background: #6c757d; color: white;">Multimarcas</span>
                        <?php endif; ?>
                        
                        <?php if ($client['cliente_premium']): ?>
                            <span class="status-badge badge-premium">Premium</span>
                        <?php endif; ?>
                        
                        <?php if ($client['tipo_cliente']): ?>
                            <?php if (strtolower($client['tipo_cliente']) === 'master'): ?>
                                <span class="status-badge badge-master">Master</span>
                            <?php else: ?>
                                <span class="status-badge badge-normal">Normal</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= BASE_URL ?>/crm/client/<?= $client['id'] ?>" class="btn btn-primary" title="Ver detalhes">👁️ Ver</a>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <form method="POST" action="<?= BASE_URL ?>/crm/delete-client/<?= $client['id'] ?>" 
                                  style="display: inline;" onsubmit="return confirmDelete('Tem certeza que deseja excluir este cliente?')">
                                <button type="submit" class="btn btn-danger" title="Excluir">🗑️ Excluir</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($pagination['total_pages'] > 1): ?>
<div class="pagination">
    <?php if ($pagination['has_prev']): ?>
        <a href="?page=<?= $pagination['prev_page'] ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sortBy) ?>">« Anterior</a>
    <?php endif; ?>
    
    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
        <?php if ($i == $pagination['current_page']): ?>
            <span class="current"><?= $i ?></span>
        <?php else: ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sortBy) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    
    <?php if ($pagination['has_next']): ?>
        <a href="?page=<?= $pagination['next_page'] ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sortBy) ?>">Próxima »</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($_SESSION['role'] === 'admin'): ?>
<!-- Modal Criar Cliente -->
<div id="createClientModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('createClientModal')">&times;</span>
        <h3>Cadastrar Novo Cliente</h3>
        <form method="POST" action="<?= BASE_URL ?>/crm/create-client">
            <div class="form-group">
                <label>Nome do Cliente:</label>
                <input type="text" name="nome" required>
            </div>
            <div class="form-group">
                <label>Empresa:</label>
                <input type="text" name="empresa">
            </div>
            <div class="form-group">
                <label>Cidade:</label>
                <input type="text" name="cidade">
            </div>
            <div class="form-group">
                <label>Estado:</label>
                <input type="text" name="estado">
            </div>
            <div class="form-group">
                <label>Tipo de Marca:</label>
                <select name="exclusividade">
                    <option value="Exclusivo">Exclusivo</option>
                    <option value="Multimarcas">Multimarcas</option>
                </select>
            </div>
            <div class="form-group">
                <label>Categoria do Cliente:</label>
                <select name="categoria">
                    <option value="Normal">Normal</option>
                    <option value="Master">Master</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tipo de frete:</label>
                <select name="tipo_frete" id="createTipoFrete" onchange="toggleCreateFreteiro()">
                    <option value="Próprio">Próprio</option>
                    <option value="Freteiro">Freteiro</option>
                </select>
                <div id="createFreteiroContainer" style="display: none; margin-top: 0.5rem;">
                    <label>Nome do Freteiro:</label>
                    <input type="text" name="freteiro_nome">
                </div>
            </div>
            <div class="form-group" style="display: flex; align-items: center;">
                <input type="checkbox" name="premium" value="1" id="createPremium" style="margin-right: 8px; width: auto;">
                <label for="createPremium" style="margin-bottom: 0;">Cliente Premium</label>
            </div>
            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-success">Cadastrar Cliente</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('createClientModal')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleCreateFreteiro() {
    const select = document.getElementById('createTipoFrete');
    const container = document.getElementById('createFreteiroContainer');
    container.style.display = (select.value === 'Freteiro') ? 'block' : 'none';
}
</script>
<?php endif; ?>

<?php $content = ob_get_clean(); ?>
<?php include '../src/views/layouts/main.php'; ?>
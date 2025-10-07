<?php
require_once '../src/views/layout/header.php';

// Incluir model de produção
require_once '../src/models/Producao.php';
$producaoModel = new Producao();

// Buscar dados
$estatisticas = $producaoModel->getEstatisticas();
$producaoHoje = $producaoModel->getDashboardHoje();
$produtos = $producaoModel->getAllProdutos();

// Processar formulário de produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_produto') {
        $data = [
            'nome' => sanitize($_POST['nome']),
            'codigo' => sanitize($_POST['codigo']),
            'categoria' => sanitize($_POST['categoria']),
            'unidade_medida' => sanitize($_POST['unidade_medida']),
            'capacidade_litros' => (float)$_POST['capacidade_litros'],
            'descricao' => sanitize($_POST['descricao'])
        ];
        
        if ($producaoModel->createProduto($data)) {
            setFlash('success', 'Produto cadastrado com sucesso!');
            redirect('/departments/producao');
        } else {
            setFlash('error', 'Erro ao cadastrar produto');
        }
    }
    
    if ($_POST['action'] === 'create_lancamento') {
        $data = [
            'produto_id' => (int)$_POST['produto_id'],
            'data_producao' => $_POST['data_producao'],
            'quantidade_produzida' => (int)$_POST['quantidade_produzida'],
            'quantidade_perdida' => (int)($_POST['quantidade_perdida'] ?? 0),
            'motivo_perda' => sanitize($_POST['motivo_perda'] ?? ''),
            'observacoes' => sanitize($_POST['observacoes'] ?? ''),
            'turno' => sanitize($_POST['turno']),
            'operador_id' => $_SESSION['user_id'],
            'supervisor_id' => null
        ];
        
        if ($producaoModel->createLancamento($data)) {
            setFlash('success', 'Lançamento registrado com sucesso!');
            redirect('/departments/producao');
        } else {
            setFlash('error', 'Erro ao registrar lançamento');
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                🏭 Gestão de Produção
            </div>
            <div class="header-buttons">
                <a href="<?= BASE_URL ?>/departments" class="custom-back-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                    <span>Voltar aos Setores</span>
                </a>
            </div>
        </div>
    </div>
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                🏭 Gestão de Produção
            </div>
            <div class="header-buttons">
                <a href="<?= BASE_URL ?>/departments" class="custom-back-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                    <span>Voltar aos Setores</span>
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Dashboard Cards -->
        <div class="stats" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-number"><?= number_format($estatisticas['produzido_hoje']) ?></div>
                <div class="stat-label">Produzido Hoje</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($estatisticas['perdido_hoje']) ?></div>
                <div class="stat-label">Perdido Hoje</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $estatisticas['eficiencia_hoje'] ?>%</div>
                <div class="stat-label">Eficiência Hoje</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($estatisticas['produzido_mes']) ?></div>
                <div class="stat-label">Produzido no Mês</div>
            </div>
        </div>

        <!-- Menu de Opções -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <a href="#" onclick="mostrarFormProduto()" class="menu-card">
                <div class="menu-icon">📦</div>
                <h3>Novo Produto</h3>
                <p>Cadastrar novo produto</p>
            </a>
            
            <a href="#" onclick="mostrarFormLancamento()" class="menu-card">
                <div class="menu-icon">📊</div>
                <h3>Novo Lançamento</h3>
                <p>Registrar produção do dia</p>
            </a>
            
            <a href="#" onclick="mostrarRelatorios()" class="menu-card">
                <div class="menu-icon">📈</div>
                <h3>Relatórios</h3>
                <p>Análises e históricos</p>
            </a>
            
            <a href="#" onclick="mostrarMetas()" class="menu-card">
                <div class="menu-icon">🎯</div>
                <h3>Metas</h3>
                <p>Acompanhar objetivos</p>
            </a>
        </div>

        <!-- Produção de Hoje -->
        <?php if (!empty($producaoHoje)): ?>
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">📊 Produção de Hoje - <?= date('d/m/Y') ?></div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Produzido</th>
                            <th>Perdido</th>
                            <th>Eficiência</th>
                            <th>Lançamentos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($producaoHoje as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['produto']) ?></td>
                            <td><strong><?= number_format($item['total_produzido']) ?></strong></td>
                            <td style="color: #dc3545;"><?= number_format($item['total_perdido']) ?></td>
                            <td>
                                <?php 
                                $eficiencia = $item['total_produzido'] > 0 ? 
                                    round((($item['total_produzido'] - $item['total_perdido']) / $item['total_produzido']) * 100, 1) : 0;
                                $cor = $eficiencia >= 95 ? '#28a745' : ($eficiencia >= 85 ? '#ffc107' : '#dc3545');
                                ?>
                                <span style="color: <?= $cor ?>; font-weight: bold;"><?= $eficiencia ?>%</span>
                            </td>
                            <td><?= $item['total_lancamentos'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            📝 Nenhuma produção registrada hoje. <a href="#" onclick="mostrarFormLancamento()">Criar primeiro lançamento</a>
        </div>
        <?php endif; ?>

        <!-- Lista de Produtos -->
        <div class="card">
            <div class="card-header">📦 Produtos Cadastrados</div>
            <div class="card-body">
                <div style="margin-bottom: 1rem;">
                    <button onclick="mostrarFormProduto()" class="btn btn-primary">➕ Novo Produto</button>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Capacidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $produto): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($produto['codigo']) ?></code></td>
                            <td><?= htmlspecialchars($produto['nome']) ?></td>
                            <td><?= htmlspecialchars($produto['categoria']) ?></td>
                            <td><?= $produto['capacidade_litros'] ?> L</td>
                            <td>
                                <button onclick="verHistoricoProduto(<?= $produto['id'] ?>)" class="btn btn-secondary btn-sm">📈 Histórico</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Produto -->
<div id="modalProduto" class="modal">
    <div class="modal-content">
        <span class="close" onclick="fecharModal('modalProduto')">&times;</span>
        <h2>📦 Novo Produto</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create_produto">
            
            <div class="form-group">
                <label>Nome do Produto *</label>
                <input type="text" name="nome" required placeholder="Ex: Água 500ml">
            </div>
            
            <div class="form-group">
                <label>Código *</label>
                <input type="text" name="codigo" required placeholder="Ex: AGU500">
            </div>
            
            <div class="form-group">
                <label>Categoria</label>
                <select name="categoria">
                    <option value="Água Mineral">Água Mineral</option>
                    <option value="Sucos">Sucos</option>
                    <option value="Refrigerantes">Refrigerantes</option>
                    <option value="Outros">Outros</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Unidade de Medida</label>
                <select name="unidade_medida">
                    <option value="UN">Unidade (UN)</option>
                    <option value="L">Litros (L)</option>
                    <option value="ML">Mililitros (ML)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Capacidade (Litros) *</label>
                <input type="number" name="capacidade_litros" step="0.001" required placeholder="Ex: 0.5">
            </div>
            
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" placeholder="Descrição do produto"></textarea>
            </div>
            
            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-success">💾 Salvar Produto</button>
                <button type="button" onclick="fecharModal('modalProduto')" class="btn btn-secondary">❌ Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Novo Lançamento -->
<div id="modalLancamento" class="modal">
    <div class="modal-content">
        <span class="close" onclick="fecharModal('modalLancamento')">&times;</span>
        <h2>📊 Novo Lançamento de Produção</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create_lancamento">
            
            <div class="form-group">
                <label>Produto *</label>
                <select name="produto_id" required>
                    <option value="">Selecione um produto</option>
                    <?php foreach ($produtos as $produto): ?>
                    <option value="<?= $produto['id'] ?>"><?= $produto['nome'] ?> (<?= $produto['codigo'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Data da Produção *</label>
                <input type="date" name="data_producao" value="<?= date('Y-m-d') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Quantidade Produzida *</label>
                <input type="number" name="quantidade_produzida" required placeholder="Ex: 1000">
            </div>
            
            <div class="form-group">
                <label>Quantidade Perdida</label>
                <input type="number" name="quantidade_perdida" value="0" placeholder="Ex: 50">
            </div>
            
            <div class="form-group">
                <label>Motivo da Perda</label>
                <input type="text" name="motivo_perda" placeholder="Ex: Embalagem defeituosa">
            </div>
            
            <div class="form-group">
                <label>Turno *</label>
                <select name="turno" required>
                    <option value="MANHÃ">Manhã</option>
                    <option value="TARDE">Tarde</option>
                    <option value="NOITE">Noite</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Observações</label>
                <textarea name="observacoes" placeholder="Observações sobre a produção"></textarea>
            </div>
            
            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-success">💾 Registrar Lançamento</button>
                <button type="button" onclick="fecharModal('modalLancamento')" class="btn btn-secondary">❌ Cancelar</button>
            </div>
        </form>
    </div>
</div>

<style>
.menu-card {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    display: block;
}

.menu-card:hover {
    border-color: #007fa3;
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 127, 163, 0.15);
    text-decoration: none;
    color: inherit;
}

.menu-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.menu-card h3 {
    color: #007fa3;
    margin-bottom: 0.5rem;
    font-size: 1.2rem;
}

.menu-card p {
    color: #666;
    margin: 0;
    font-size: 0.9rem;
}

.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    text-align: center;
    border-left: 4px solid #007fa3;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #007fa3;
}

.stat-label {
    color: #666;
    margin-top: 0.5rem;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}

.modal-content {
    background: white;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 10px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: black;
}
</style>

<script>
function mostrarFormProduto() {
    document.getElementById('modalProduto').style.display = 'block';
}

function mostrarFormLancamento() {
    document.getElementById('modalLancamento').style.display = 'block';
}

function fecharModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function mostrarRelatorios() {
    alert('Funcionalidade de relatórios em desenvolvimento');
}

function mostrarMetas() {
    alert('Funcionalidade de metas em desenvolvimento');
}

function verHistoricoProduto(produtoId) {
    alert('Histórico do produto ' + produtoId + ' em desenvolvimento');
}

// Fechar modal clicando fora
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php require_once '../src/views/layout/footer.php'; ?>
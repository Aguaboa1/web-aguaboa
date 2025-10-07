<?php
// Incluir model de produção
require_once '../src/models/Producao.php';
require_once '../src/models/UserPermission.php';
$producaoModel = new Producao();

// Verificar permissões do usuário
$userPermission = new UserPermission();
$canEdit = $userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'edit');
$permissionText = $canEdit ? 'Editor (Acesso Total)' : 'Visualizador';

// Buscar dados
$estatisticas = $producaoModel->getEstatisticas();
$producaoMes = $producaoModel->getDashboardMes();
$produtos = $producaoModel->getAllProdutos();
// Carregar insumos para permitir consumo manual quando não houver receita
require_once '../src/models/EstoqueInsumos.php';
$estoqueModel = new EstoqueInsumos();
$allInsumos = $estoqueModel->getAllItens();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Aguaboa - Gestão de Produção</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f6fa; color: #333; line-height: 1.6; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 2rem; display:flex; justify-content:space-between; align-items:center }
        .header h1 { font-size: 2rem; font-weight: 600; margin: 0; white-space: nowrap; }
        .nav-links { display:flex; gap:1.5rem; align-items:center }
        .nav-links a { color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 8px; transition: background-color 0.3s; font-weight: 500 }
        .nav-links a:hover { background-color: rgba(255,255,255,0.2); }
        .main-container { min-height: calc(100vh - 100px); padding: 2rem; max-width: 1400px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>🏭 Web Aguaboa - Gestão de Produção</h1>
            <div class="nav-links">
                <a href="<?= BASE_URL ?>/crm">🏠 Página Inicial</a>
                <a href="<?= BASE_URL ?>/departments">🏢 Setores</a>
                <a href="<?= BASE_URL ?>/insumos">📦 Insumos</a>
                <a href="<?= BASE_URL ?>/producao/lancamentos">📊 Lançamentos</a>
                <a href="<?= BASE_URL ?>/relatorios">📈 Relatórios</a>
            </div>
            <div style="display:flex; align-items:center; gap:1rem;">
                <div style="font-weight:600; opacity:0.95">👤 <?= htmlspecialchars($_SESSION['username']) ?></div>
                <a href="<?= BASE_URL ?>/auth/logout">🚪 Sair</a>
            </div>
        </div>
    </div>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                🏭 Gestão de Produção
            </div>
            <div class="header-buttons">
                <!-- Removed local back/home buttons per request; navigation contains main links -->
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Dashboard Cards -->
        <div class="stats-container-compact">
            <div class="stat-card produzido">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <div class="stat-number"><?= number_format($estatisticas['produzido_mes']) ?></div>
                    <div class="stat-label">Consumido no Mês</div>
                </div>
            </div>
            <div class="stat-card perdido">
                <div class="stat-icon">⚠️</div>
                <div class="stat-content">
                    <div class="stat-number"><?= number_format($estatisticas['perdido_mes']) ?></div>
                    <div class="stat-label">Perdido no Mês</div>
                </div>
            </div>
            <div class="stat-card eficiencia">
                <div class="stat-icon">🎯</div>
                <div class="stat-content">
                    <div class="stat-number"><?= $estatisticas['eficiencia_mes'] ?>%</div>
                    <div class="stat-label">Eficiência Mensal</div>
                </div>
            </div>
            <div class="stat-card mensal">
                <div class="stat-icon">📈</div>
                <div class="stat-content">
                    <div class="stat-number"><?= number_format($estatisticas['produzido_ano']) ?></div>
                    <div class="stat-label">Consumido no Ano</div>
                </div>
            </div>
        </div>

    <!-- Menu de Opções -->
    <div class="menu-grid">
            <a href="#" onclick="mostrarFormProduto()" class="menu-card">
                <div class="menu-icon">📦</div>
                <h3>Novo Insumo</h3>
                <p>Cadastrar novo insumo</p>
            </a>
            
            <a href="#" onclick="mostrarFormLancamento()" class="menu-card">
                <div class="menu-icon">📊</div>
                <h3>Novo Lançamento</h3>
                <p>Registrar produção do dia</p>
            </a>
            
            <a href="/gestao-aguaboa-php/public/relatorios" class="menu-card">
                <div class="menu-icon">📈</div>
                <h3>Relatórios</h3>
                <p>Análises e históricos</p>
            </a>
            
            <!-- Metas card removed as requested -->
    </div>

        <!-- Produção do Mês -->
        <?php if (!empty($producaoMes)): ?>
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">📊 Produção do Mês - <?= date('m/Y') ?></div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th>Produzido</th>
                            <th>Perdido</th>
                            <th>Eficiência</th>
                            <th>Lançamentos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($producaoMes as $item): ?>
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

        <!-- Insumos gerenciados na página /insumos -->
    </div>
</div>

<!-- Novo Insumo gerenciado na página /insumos -->

<!-- Modal Novo Lançamento -->
<div id="modalLancamento" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="icon">📊</i>
                Registrar Lançamento de Produção
            </h2>
            <span class="close" onclick="fecharModal('modalLancamento')">&times;</span>
        </div>
        
        <div class="modal-body">
            <form method="POST" action="<?= BASE_URL ?>/processar_producao.php" class="lancamento-form">
                <input type="hidden" name="action" value="create_lancamento">
                
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label class="form-label required">Produto</label>
                        <select name="produto_id" class="form-control" required>
                            <option value="">Selecione um produto</option>
                            <?php foreach ($produtos as $produto): ?>
                            <option value="<?= $produto['id'] ?>">
                                <?= $produto['nome'] ?> (<?= $produto['codigo'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label class="form-label required">Data da Produção</label>
                        <input type="date" name="data_producao" class="form-control" 
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="form-label required">Quantidade Produzida</label>
                        <div class="input-group">
                            <input type="number" name="quantidade_produzida" class="form-control" 
                                   required placeholder="1000" min="0">
                            <div class="input-group-append">
                                <span class="input-group-text">UN</span>
                            </div>
                        </div>
                        <small class="form-text">Quantidade total produzida</small>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label class="form-label">Quantidade Perdida</label>
                        <div class="input-group">
                            <input type="number" name="quantidade_perdida" class="form-control" 
                                   value="0" placeholder="0" min="0">
                            <div class="input-group-append">
                                <span class="input-group-text">UN</span>
                            </div>
                        </div>
                        <small class="form-text">Quantidade com defeito/perdida</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="form-label required">Turno</label>
                        <select name="turno" class="form-control" required>
                            <option value="MANHÃ">🌅 Manhã (06:00 - 14:00)</option>
                            <option value="TARDE">🌤️ Tarde (14:00 - 22:00)</option>
                            <option value="NOITE">🌙 Noite (22:00 - 06:00)</option>
                        </select>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label class="form-label">Motivo da Perda</label>
                        <select name="motivo_perda" class="form-control">
                            <option value="">Nenhuma perda</option>
                            <option value="Embalagem defeituosa">📦 Embalagem defeituosa</option>
                            <option value="Problema na máquina">⚙️ Problema na máquina</option>
                            <option value="Qualidade do produto">🔍 Qualidade do produto</option>
                            <option value="Quebra/Vazamento">💧 Quebra/Vazamento</option>
                            <option value="Outros">❓ Outros</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3" 
                              placeholder="Observações sobre a produção, problemas encontrados, melhorias sugeridas..."></textarea>
                </div>
                
                <!-- Seção de Consumo de Insumos -->
                <div id="consumo-insumos" class="consumption-preview" style="display: none;">
                    <h4>📦 Consumo Previsto de Insumos</h4>
                    <div id="insumos-list" class="insumos-grid">
                        <!-- Será preenchido via JavaScript -->
                    </div>
                    <div class="consumption-note">
                        ℹ️ Os insumos serão automaticamente deduzidos do estoque quando o lançamento for registrado.
                    </div>
                </div>

                <!-- Seção de Consumo Manual (quando não houver receita) -->
                <div id="manual-consumo" class="manual-consumption" style="display:none; margin-top:1rem;">
                    <h4>📦 Consumo Manual de Insumos</h4>
                    <div style="margin-bottom:0.5rem; color:#6c757d;">Você pode escolher manualmente quais insumos debitar caso não exista receita configurada para este produto.</div>
                    <button type="button" class="btn btn-info" onclick="adicionarInsumoManual()">+ Adicionar Insumo</button>
                    <div id="manual-items" style="margin-top:0.8rem;"></div>
                </div>
                
                <div class="production-summary">
                    <div class="summary-card">
                        <h4>📊 Resumo da Produção</h4>
                        <div class="summary-item">
                            <span class="label">Eficiência:</span>
                            <span class="value" id="eficiencia">100.0%</span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Produção Líquida:</span>
                            <span class="value" id="producao-liquida">0 UN</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="btn-icon">�</i>
                        Registrar Lançamento
                    </button>
                    <button type="button" onclick="fecharModal('modalLancamento')" class="btn btn-secondary">
                        <i class="btn-icon">❌</i>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Insumo -->
<div id="modalEditarProduto" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="modal-icon">✏️</i>
                Editar Insumo
            </h2>
            <span class="close" onclick="fecharModal('modalEditarProduto')">&times;</span>
        </div>
        
        <div class="modal-body">
            <form method="POST" action="<?= BASE_URL ?>/processar_producao.php" class="edit-produto-form">
                <input type="hidden" name="action" value="update_produto">
                <input type="hidden" name="produto_id" id="edit_produto_id">
                
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label class="form-label required">Nome do Insumo</label>
                        <input type="text" name="nome" id="edit_nome" class="form-control" required 
                               placeholder="Ex: Lacre Aguaboa 500ml, Tampa Rosca 28mm">
                        <small class="form-text">Digite o nome completo do insumo</small>
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label class="form-label required">Código</label>
                        <input type="text" name="codigo" id="edit_codigo" class="form-control" required 
                               placeholder="Ex: LAC500" style="text-transform: uppercase;">
                        <small class="form-text">Código único do insumo</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="form-label">Categoria</label>
                        <select name="categoria" id="edit_categoria" class="form-control">
                            <option value="Água mineral">🧊 Água mineral</option>
                            <option value="Insumo">🔧 Insumo</option>
                            <option value="Produto">📦 Produto</option>
                        </select>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label class="form-label">Unidade de Medida</label>
                        <select name="unidade_medida" id="edit_unidade_medida" class="form-control">
                            <option value="UN">📦 Unidade (UN)</option>
                            <option value="L">🪣 Litros (L)</option>
                            <option value="ML">🥤 Mililitros (ML)</option>
                            <option value="CX">📦 Caixa (CX)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao" id="edit_descricao" class="form-control" rows="3" 
                              placeholder="Descrição detalhada do insumo, características, especificações técnicas..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-warning">
                        <i class="btn-icon">💾</i>
                        Atualizar Insumo
                    </button>
                    <button type="button" onclick="fecharModal('modalEditarProduto')" class="btn btn-secondary">
                        <i class="btn-icon">❌</i>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Edição de insumos feita na página /insumos -->

<style>
/* === CARDS DO MENU === */
.menu-card {
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    border: 2px solid #e9ecef;
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    text-decoration: none;
    color: inherit;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: block;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.menu-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(0, 127, 163, 0.1), transparent);
    transition: left 0.6s;
}

.menu-card:hover {
    border-color: #007fa3;
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0, 127, 163, 0.2);
    text-decoration: none;
    color: inherit;
}

.menu-card:hover::before {
    left: 100%;
}

.menu-icon {
    font-size: 3rem;
    margin-bottom: 1.5rem;
    display: block;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.menu-card h3 {
    color: #007fa3;
    margin-bottom: 0.8rem;
    font-size: 1.4rem;
    font-weight: 600;
}

.menu-card p {
    color: #6c757d;
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.5;
}

/* === ESTATÍSTICAS COMPACTAS === */
.stats-container-compact {
    display: grid;
    grid-template-columns: repeat(4, 220px);
    gap: 1.5rem;
    margin: 1.25rem auto 1.75rem;
    max-width: 1100px;
    justify-content: center;
    align-items: start;
}

.stat-card {
    background: linear-gradient(135deg, #ffffff, #f8fffe);
    padding: 1.25rem 1rem;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.6rem;
    border-top: 4px solid #007fa3;
    transition: transform 0.2s ease;
    position: relative;
    overflow: hidden;
    min-height: 110px;
    text-align: center;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 8px;
    right: 8px;
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, rgba(0, 127, 163, 0.06), transparent);
    border-radius: 8px;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-icon {
    font-size: 2rem;
    line-height: 1;
    margin-bottom: 6px;
}

.stat-content {
    flex: 1;
}

    .stat-number {
    font-size: 1.45rem;
    font-weight: 800;
    color: #007fa3;
    margin-bottom: 0.25rem;
    line-height: 1;
}

.stat-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    line-height: 1.2;
}

/* Responsividade para cards compactos */
@media (max-width: 1024px) {
    .stats-container-compact {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .stats-container-compact {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        min-height: 70px;
        padding: 0.75rem;
    }
    
    .stat-number {
        font-size: 1.3rem;
    }
    
    .stat-label {
        font-size: 0.7rem;
    }
}

/* === MODAIS PROFISSIONAIS === */
.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: #ffffff;
    margin: 3% auto;
    border-radius: 20px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes slideIn {
    from { 
        opacity: 0;
        transform: translateY(-50px) scale(0.95);
    }
    to { 
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* === MENU GRID (centering) === */
.menu-grid {
    display: grid;
    /* three equal cards in a row */
    grid-template-columns: repeat(3, minmax(260px, 340px));
    gap: 1.5rem;
    justify-content: center;
    align-items: stretch;
    margin: 1.25rem auto 2rem;
    max-width: 1150px;
}

@media (max-width: 1024px) {
    .stats-container-compact { grid-template-columns: repeat(2, 1fr); max-width: 700px; }
    .menu-grid { grid-template-columns: repeat(2, 1fr); max-width: 720px; }
}

@media (max-width: 600px) {
    .stats-container-compact { grid-template-columns: 1fr; max-width: 420px; }
    .menu-grid { grid-template-columns: 1fr; max-width: 420px; }
    .menu-grid a:nth-child(3) { grid-column: auto; }
}

.menu-card { display:flex; flex-direction:column; justify-content:center; align-items:center; min-height:120px }
.menu-card .menu-icon { font-size: 2.6rem; }

.modal-header {
    background: linear-gradient(135deg, #007fa3, #00a8cc);
    color: white;
    padding: 2rem;
    position: relative;
    border-radius: 20px 20px 0 0;
}

.modal-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.modal-title .icon {
    font-size: 1.8rem;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}

.close {
    position: absolute;
    right: 2rem;
    top: 50%;
    transform: translateY(-50%);
    color: white;
    font-size: 2rem;
    font-weight: 300;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-50%) rotate(90deg);
}

.modal-body {
    padding: 2.5rem;
    max-height: 70vh;
    overflow-y: auto;
}

/* === FORMULÁRIOS === */
.form-row {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    flex: 1;
    margin-bottom: 1.5rem;
}

.col-md-4 { flex: 0 0 33.333%; }
.col-md-6 { flex: 0 0 50%; }
.col-md-8 { flex: 0 0 66.666%; }

.form-label {
    display: block;
    margin-bottom: 0.8rem;
    font-weight: 600;
    color: #495057;
    font-size: 0.95rem;
}

.form-label.required::after {
    content: " *";
    color: #dc3545;
    font-weight: bold;
}

.form-control {
    width: 100%;
    padding: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #ffffff;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #007fa3;
    box-shadow: 0 0 0 3px rgba(0, 127, 163, 0.1);
    background: #ffffff;
}

.form-control::placeholder {
    color: #adb5bd;
    font-style: italic;
}

.form-text {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.5rem;
    font-style: italic;
}

.input-group {
    display: flex;
    border-radius: 12px;
    overflow: hidden;
}

.input-group .form-control {
    border-radius: 0;
    border-right: none;
}

.input-group-append {
    display: flex;
}

.input-group-text {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-left: none;
    padding: 1rem;
    color: #6c757d;
    font-weight: 600;
    min-width: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* === RESUMO DE PRODUÇÃO === */
.production-summary {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-radius: 16px;
    padding: 1.5rem;
    margin: 2rem 0;
    border: 2px solid #e9ecef;
}

/* Seção de Consumo de Insumos */
.consumption-preview {
    background: linear-gradient(135deg, #fff3cd, #fff8e1);
    border-radius: 12px;
    padding: 1.5rem;
    margin: 1.5rem 0;
    border: 2px solid #ffc107;
    border-left: 6px solid #ff8f00;
}

.consumption-preview h4 {
    color: #856404;
    margin-bottom: 1rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.insumos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.insumo-item {
    background: rgba(255, 255, 255, 0.8);
    padding: 0.75rem;
    border-radius: 8px;
    border: 1px solid #ffecb3;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.insumo-nome {
    font-weight: 500;
    color: #495057;
    flex: 1;
}

.insumo-quantidade {
    color: #856404;
    font-weight: 600;
    font-size: 0.9rem;
}

.insumo-status {
    margin-left: 0.5rem;
    font-size: 0.8rem;
}

.insumo-status.suficiente {
    color: #28a745;
}

.insumo-status.insuficiente {
    color: #dc3545;
}

.consumption-note {
    font-size: 0.85rem;
    color: #856404;
    font-style: italic;
    text-align: center;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #ffecb3;
}

.summary-card h4 {
    color: #007fa3;
    margin-bottom: 1rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item .label {
    color: #6c757d;
    font-weight: 500;
}

.summary-item .value {
    color: #007fa3;
    font-weight: 700;
    font-size: 1.1rem;
}

/* === BOTÕES === */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 2rem;
    border-top: 2px solid #f8f9fa;
    margin-top: 2rem;
}

.btn {
    padding: 1rem 2rem;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn:hover::before {
    left: 100%;
}

.btn-primary {
    background: linear-gradient(135deg, #007fa3, #00a8cc);
    color: white;
    box-shadow: 0 4px 15px rgba(0, 127, 163, 0.3);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #005f7a, #007fa3);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 127, 163, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #8d959e);
    color: white;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #545b62, #6c757d);
    transform: translateY(-2px);
}

.btn-icon {
    font-size: 1.1rem;
}

/* === RESPONSIVIDADE === */
@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .col-md-4, .col-md-6, .col-md-8 {
        flex: none;
    }
    
    .modal-content {
        margin: 5% auto;
        width: 95%;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}

/* === ANIMAÇÕES === */
.form-control:focus {
    animation: inputFocus 0.3s ease;
}

@keyframes inputFocus {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

/* === BOTÕES PERSONALIZADOS === */
.btn-danger {
    background: linear-gradient(135deg, #ff6b6b, #ff5252) !important;
    border-color: #ff6b6b !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3) !important;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #ff5252, #f44336) !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 82, 82, 0.4) !important;
}

/* === MELHORIAS PROFISSIONAIS === */
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

/* Stats Cards Melhoradas */
.stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    grid-template-rows: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
    max-width: 600px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.25rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    text-align: center;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(135deg, #007fa3, #00a8cc);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.stat-number {
    font-size: 1.75rem;
    font-weight: 700;
    color: #007fa3;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.stat-label {
    color: #6c757d;
    font-size: 0.8rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* Tabelas Profissionais */
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

table thead {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

table th {
    padding: 1rem 1.5rem;
    text-align: left;
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #dee2e6;
}

table td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
}

table tr:hover {
    background-color: #f8f9fa;
}

table tr:last-child td {
    border-bottom: none;
}

/* Botões Melhorados */
.btn {
    border: none;
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    cursor: pointer;
    font-size: 0.9rem;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
}

.btn:hover {
    transform: translateY(-2px);
}

/* Menu Cards Melhorados */
.menu-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1.5rem;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    display: block;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    position: relative;
    overflow: hidden;
}

.menu-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(135deg, #007fa3, #00a8cc);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.menu-card:hover::before {
    transform: scaleX(1);
}

.menu-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border-color: #007fa3;
}

.menu-icon {
    font-size: 2rem;
    margin-bottom: 0.75rem;
}

.menu-card h3 {
    color: #007fa3;
    margin-bottom: 0.25rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.menu-card p {
    color: #6c757d;
    font-size: 0.85rem;
    line-height: 1.4;
}

/* Responsividade Melhorada */
@media (max-width: 768px) {
    .stats {
        grid-template-columns: 1fr;
        grid-template-rows: repeat(4, 1fr);
        gap: 0.75rem;
        max-width: 300px;
        margin: 0 auto 1.5rem auto;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .stat-label {
        font-size: 0.75rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .menu-card {
        padding: 1.25rem;
    }
    
    .menu-icon {
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
    }
    
    .menu-card h3 {
        font-size: 1rem;
    }
    
    .menu-card p {
        font-size: 0.8rem;
    }
    
    table th,
    table td {
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .stats {
        max-width: 250px;
    }
    
    .stat-card {
        padding: 0.85rem;
    }
    
    .stat-number {
        font-size: 1.3rem;
    }
    
    .stat-label {
        font-size: 0.7rem;
    }
    
    .summary-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .summary-item {
        padding: 1rem;
    }
    
    .summary-value {
        font-size: 1.5rem;
    }
}

/* === MODAL RELATÓRIOS === */
.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.report-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.report-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(135deg, #007fa3, #00a8cc);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.report-card:hover::before {
    transform: scaleX(1);
}

.report-card:hover {
    border-color: #007fa3;
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 127, 163, 0.15);
}

.report-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.report-card h3 {
    color: #007fa3;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.report-card p {
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.4;
}

.filters-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.filters-section h4 {
    color: #495057;
    margin-bottom: 1rem;
    font-size: 1rem;
    font-weight: 600;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-weight: 500;
    color: #495057;
    font-size: 0.9rem;
}

.report-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
}

.btn-success:hover {
    background: linear-gradient(135deg, #218838, #1fa67a);
    transform: translateY(-2px);
}

.btn-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    border: none;
}

.btn-info:hover {
    background: linear-gradient(135deg, #138496, #117a8b);
    transform: translateY(-2px);
}

</style>

<script>
// Função mostrarFormProduto removida — criar/editar insumos agora em /insumos

function mostrarFormLancamento() {
    document.getElementById('modalLancamento').style.display = 'block';
    // Auto-focus no select de produto
    setTimeout(() => {
        document.querySelector('#modalLancamento select[name="produto_id"]').focus();
    }, 100);
}

function fecharModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'none';
    
    // Resetar formulário
    const form = modal.querySelector('form');
    if (form) {
        form.reset();
        // Resetar resumo de produção se existir
        atualizarResumoProducao();
    }
}

function abrirRelatorios() {
    window.location.href = '/relatorios';
}

// Função para navegação
function definirDatasPadrao() {
    const hoje = new Date();
    const primeiroDiaMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
    return {
        inicial: primeiroDiaMes.toISOString().split('T')[0],
        final: hoje.toISOString().split('T')[0]
    };
}

function exportarRelatorio(formato) {
    const dados = definirDatasPadrao();
    const params = new URLSearchParams({
        formato: formato,
        data_inicial: dados.inicial,
        data_final: dados.final
    });
    
    window.open(`/exportar_relatorio?${params.toString()}`, '_blank');
}

// Navegação
function formatarData(data) {
    return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR');
}

function mostrarMetas() {
    alert('🎯 Funcionalidade de metas em desenvolvimento.\n\nEm breve você terá acesso a:\n• Definição de metas mensais\n• Acompanhamento de progresso\n• Alertas de desempenho\n• Histórico de metas');
}

function verHistoricoProduto(produtoId) {
    alert('📊 Histórico do produto ' + produtoId + ' em desenvolvimento.\n\nEm breve você verá:\n• Produção diária dos últimos 30 dias\n• Gráficos de tendência\n• Análise de perdas\n• Comparativo com metas');
}

// Cálculos automáticos no formulário de lançamento
function atualizarResumoProducao() {
    const produzida = parseFloat(document.querySelector('input[name="quantidade_produzida"]')?.value) || 0;
    const perdida = parseFloat(document.querySelector('input[name="quantidade_perdida"]')?.value) || 0;
    const produtoId = document.querySelector('select[name="produto_id"]')?.value || 0;
    
    const producaoLiquida = produzida - perdida;
    const eficiencia = produzida > 0 ? ((producaoLiquida / produzida) * 100) : 100;
    
    // Atualizar elementos do resumo
    const eficienciaEl = document.getElementById('eficiencia');
    const producaoLiquidaEl = document.getElementById('producao-liquida');
    
    if (eficienciaEl) {
        eficienciaEl.textContent = eficiencia.toFixed(1) + '%';
        eficienciaEl.style.color = eficiencia >= 95 ? '#28a745' : eficiencia >= 85 ? '#ffc107' : '#dc3545';
    }
    
    if (producaoLiquidaEl) {
        producaoLiquidaEl.textContent = producaoLiquida.toLocaleString() + ' UN';
        producaoLiquidaEl.style.color = '#007fa3';
    }
    
    // Atualizar consumo de insumos
    if (produtoId && produzida > 0) {
        buscarConsumoInsumos(produtoId, produzida);
    } else {
        ocultarConsumoInsumos();
    }
}

// Função para buscar consumo de insumos via API
async function buscarConsumoInsumos(productId, quantity) {
    try {
        const response = await fetch(`<?= BASE_URL ?>/api_consumo_insumos.php?product_id=${productId}&quantity=${quantity}`);
        const data = await response.json();
        
        if (data.error) {
            console.error('Erro na API:', data.error);
            ocultarConsumoInsumos();
            return;
        }
        
        if (!data.has_recipe) {
                // Sem receita: ocultar preview automático e exibir área manual
                ocultarConsumoInsumos();
                mostrarManualConsumo();
                return;
            }
        
        exibirConsumoInsumos(data.ingredients);
        
    } catch (error) {
        console.error('Erro ao buscar consumo de insumos:', error);
        ocultarConsumoInsumos();
    }
}

// Função para exibir o consumo de insumos
function exibirConsumoInsumos(ingredients) {
    const consumoDiv = document.getElementById('consumo-insumos');
    const insumosList = document.getElementById('insumos-list');
    
    if (!consumoDiv || !insumosList) return;
    
    // Limpar lista anterior
    insumosList.innerHTML = '';
    
    // Adicionar cada ingrediente
    ingredients.forEach(ingredient => {
        const div = document.createElement('div');
        div.className = 'insumo-item';
        
        const statusIcon = ingredient.sufficient ? '✅' : '⚠️';
        const statusClass = ingredient.sufficient ? 'suficiente' : 'insuficiente';
        const statusText = ingredient.sufficient ? 'OK' : `Falta: ${ingredient.shortage}`;
        
        div.innerHTML = `
            <div class="insumo-nome">${ingredient.name}</div>
            <div class="insumo-quantidade">${ingredient.required_quantity} ${ingredient.unit}</div>
            <div class="insumo-status ${statusClass}" title="Disponível: ${ingredient.available_quantity} ${ingredient.unit}">
                ${statusIcon} ${statusText}
            </div>
        `;
        
        insumosList.appendChild(div);
    });
    
    // Mostrar a seção
    consumoDiv.style.display = 'block';
}

// Função para ocultar o consumo de insumos
function ocultarConsumoInsumos() {
    const consumoDiv = document.getElementById('consumo-insumos');
    if (consumoDiv) {
        consumoDiv.style.display = 'none';
    }
    // ocultar manual também
    const manualDiv = document.getElementById('manual-consumo');
    if (manualDiv) manualDiv.style.display = 'none';
}

// Mostrar área de consumo manual
function mostrarManualConsumo() {
    const manualDiv = document.getElementById('manual-consumo');
    if (!manualDiv) return;
    manualDiv.style.display = 'block';
}

// Adicionar uma linha para consumo manual
function adicionarInsumoManual(itemId = '', quantidade = '') {
    const container = document.getElementById('manual-items');
    if (!container) return;

    const row = document.createElement('div');
    row.className = 'manual-row';
    row.style.display = 'flex';
    row.style.gap = '8px';
    row.style.alignItems = 'center';
    row.style.marginBottom = '8px';

    // Select de insumos preenchido via PHP
    const select = document.createElement('select');
    select.name = 'manual_item_id[]';
    select.className = 'form-control';
    select.style.minWidth = '320px';
    select.required = true;
    select.innerHTML = `
        <option value="">Selecione um insumo</option>
        <?php foreach ($allInsumos as $ins): ?>
            <option value="<?= $ins['id'] ?>"><?= htmlspecialchars($ins['name']) ?> (<?= $ins['unit'] ?>) - Disponível: <?= number_format($ins['current_quantity'],2,',','.') ?></option>
        <?php endforeach; ?>
    `;
    if (itemId) select.value = itemId;

    const qty = document.createElement('input');
    qty.type = 'number';
    qty.name = 'manual_item_quantity[]';
    qty.value = quantidade || '';
    qty.step = 'any';
    qty.min = '0';
    qty.placeholder = 'Quantidade a debitar';
    qty.className = 'form-control';
    qty.style.width = '150px';
    qty.required = true;

    const btnRemove = document.createElement('button');
    btnRemove.type = 'button';
    btnRemove.className = 'btn btn-warning';
    btnRemove.textContent = 'Remover';
    btnRemove.onclick = function() { container.removeChild(row); };

    row.appendChild(select);
    row.appendChild(qty);
    row.appendChild(btnRemove);

    container.appendChild(row);
}

// Validação do formulário de lançamento: inclui checagem de consumos manuais
function validarFormularioLancamento() {
    const produtoId = document.querySelector('select[name="produto_id"]').value;
    const dataProducao = document.querySelector('input[name="data_producao"]').value;
    const quantidadeProduzida = parseFloat(document.querySelector('input[name="quantidade_produzida"]').value);
    
    let valido = true;
    let mensagens = [];
    
    if (!produtoId) {
        mensagens.push('• Selecione um produto');
        valido = false;
    }
    
    if (!dataProducao) {
        mensagens.push('• Data da produção é obrigatória');
        valido = false;
    }
    
    if (!quantidadeProduzida || quantidadeProduzida <= 0) {
        mensagens.push('• Quantidade produzida deve ser maior que zero');
        valido = false;
    }

    // Se houver itens manuais adicionados, validar quantidades
    const manualIds = document.getElementsByName('manual_item_id[]');
    const manualQtys = document.getElementsByName('manual_item_quantity[]');
    if (manualIds && manualIds.length) {
        for (let i=0;i<manualIds.length;i++) {
            const idv = manualIds[i].value;
            const qv = parseFloat(manualQtys[i].value) || 0;
            if (!idv) { mensagens.push('• Insumo manual não selecionado (linha '+(i+1)+')'); valido = false; }
            if (qv <= 0) { mensagens.push('• Quantidade manual deve ser maior que zero (linha '+(i+1)+')'); valido = false; }
        }
    }

    if (!valido) {
        alert('❌ Corrija os seguintes erros:\n\n' + mensagens.join('\n'));
        return false;
    }

    return true;
}

// Validações em tempo real
function validarFormularioProduto() {
    const nome = document.querySelector('input[name="nome"]').value.trim();
    const codigo = document.querySelector('input[name="codigo"]').value.trim();
    
    let valido = true;
    let mensagens = [];
    
    if (!nome) {
        mensagens.push('• Nome do insumo é obrigatório');
        valido = false;
    }
    
    if (!codigo) {
        mensagens.push('• Código do produto é obrigatório');
        valido = false;
    }
    
    if (!valido) {
        alert('❌ Corrija os seguintes erros:\n\n' + mensagens.join('\n'));
        return false;
    }
    
    return true;
}

function validarFormularioLancamento() {
    const produtoId = document.querySelector('select[name="produto_id"]').value;
    const dataProducao = document.querySelector('input[name="data_producao"]').value;
    const quantidadeProduzida = parseFloat(document.querySelector('input[name="quantidade_produzida"]').value);
    
    let valido = true;
    let mensagens = [];
    
    if (!produtoId) {
        mensagens.push('• Selecione um produto');
        valido = false;
    }
    
    if (!dataProducao) {
        mensagens.push('• Data da produção é obrigatória');
        valido = false;
    }
    
    if (!quantidadeProduzida || quantidadeProduzida <= 0) {
        mensagens.push('• Quantidade produzida deve ser maior que zero');
        valido = false;
    }
    
    if (!valido) {
        alert('❌ Corrija os seguintes erros:\n\n' + mensagens.join('\n'));
        return false;
    }
    
    return true;
}

// Event listeners quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    // Auto-uppercase no código do produto
    const codigoInput = document.querySelector('input[name="codigo"]');
    if (codigoInput) {
        codigoInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
    
    // Cálculos automáticos no formulário de lançamento
    const quantidadeProduzidaInput = document.querySelector('input[name="quantidade_produzida"]');
    const quantidadePerdidaInput = document.querySelector('input[name="quantidade_perdida"]');
    const produtoSelect = document.querySelector('select[name="produto_id"]');
    
    if (quantidadeProduzidaInput) {
        quantidadeProduzidaInput.addEventListener('input', atualizarResumoProducao);
    }
    
    if (quantidadePerdidaInput) {
        quantidadePerdidaInput.addEventListener('input', atualizarResumoProducao);
    }
    
    if (produtoSelect) {
        produtoSelect.addEventListener('change', atualizarResumoProducao);
    }
    
    // Validação antes do submit
    const formProduto = document.querySelector('#modalProduto form');
    if (formProduto) {
        formProduto.addEventListener('submit', function(e) {
            if (!validarFormularioProduto()) {
                e.preventDefault();
            }
        });
    }
    
    const formLancamento = document.querySelector('#modalLancamento form');
    if (formLancamento) {
        formLancamento.addEventListener('submit', function(e) {
            if (!validarFormularioLancamento()) {
                e.preventDefault();
            }
        });
    }
});

// Fechar modal clicando fora
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Atalhos de teclado
document.addEventListener('keydown', function(e) {
    // ESC para fechar modal
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (modal.style.display === 'block') {
                modal.style.display = 'none';
            }
        });
    }
    
    // Ctrl+N para novo produto
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        mostrarFormProduto();
    }
    
    // Ctrl+L para novo lançamento
    if (e.ctrlKey && e.key === 'l') {
        e.preventDefault();
        mostrarFormLancamento();
    }
});

// Função editarProduto removida — edição de insumos acontece em /insumos

// Função excluirProduto removida — exclusão via /insumos

// Função excluirTodosProdutos removida — exclusão em massa via /insumos
// Se a página foi aberta com ?open_lancamento=1, abrir o modal de lançamento automaticamente
<?php if (!empty($_GET['open_lancamento'])): ?>
document.addEventListener('DOMContentLoaded', function(){
    try { mostrarFormLancamento(); } catch(e) { console.warn('mostrarFormLancamento not available', e); }
});
<?php endif; ?>
</script>

    </div>
</body>
</html>
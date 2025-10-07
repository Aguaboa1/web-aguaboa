<?php ob_start(); ?>

<style>
.client-detail-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 0;
    margin: 0;
}

.client-header {
    background: #17a2b8;
    color: white;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.client-header h1 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: normal;
}

.client-header .btn {
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    padding: 0.5rem 1rem;
    text-decoration: none;
    border-radius: 4px;
}

.client-id-section {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}

.client-id {
    font-size: 3rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 0.5rem;
}

.client-company {
    font-size: 1.2rem;
    color: #666;
    margin-bottom: 1rem;
}

.client-tag {
    display: inline-block;
    background: #6c757d;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 4px;
    font-size: 0.9rem;
}

.main-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.info-section {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}

.section-icon {
    font-size: 1.2rem;
    margin-right: 0.5rem;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #17a2b8;
    margin: 0;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.8rem;
    padding: 0.3rem 0;
}

.info-label {
    font-weight: 500;
    color: #495057;
}

.info-value {
    color: #333;
    text-align: right;
}

.commercial-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    margin-top: 1rem;
}

.commercial-btn {
    background: #17a2b8;
    color: white;
    padding: 1rem 1.5rem;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 600;
    text-align: center;
    transition: background 0.2s;
    border: none;
    cursor: pointer;
}

.commercial-btn:hover {
    background: #138496;
    color: white;
    text-decoration: none;
}

.edit-client-btn {
    background: #17a2b8;
    color: white;
    padding: 0.6rem 1.2rem;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.9rem;
    align-self: flex-start;
    margin-top: 1rem;
}

.edit-client-btn:hover {
    background: #138496;
    color: white;
    text-decoration: none;
}

.actions-section {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.new-action-btn {
    background: #28a745;
    color: white;
    padding: 0.6rem 1.2rem;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    display: inline-block;
}

.new-action-btn:hover {
    background: #218838;
    color: white;
    text-decoration: none;
}

.no-actions {
    text-align: center;
    color: #6c757d;
    font-style: italic;
    padding: 2rem;
}

.back-btn {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: rgba(0,0,0,0.5);
    color: white;
    padding: 0.5rem 1rem;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.9rem;
}
</style>

<div class="client-detail-container">
    <!-- Header -->
    <div class="client-header">
        <h1>Detalhes do Cliente</h1>
        <a href="<?= BASE_URL ?>/auth/logout" class="btn">Sair</a>
    </div>

    <!-- Botão Voltar -->
    <a href="<?= BASE_URL ?>/crm" class="back-btn">← Voltar</a>

    <div style="max-width: 1200px; margin: 0 auto; padding: 0 2rem;">
        <!-- ID e Nome do Cliente -->
        <div class="client-id-section">
            <div class="client-id"><?= htmlspecialchars($client['id']) ?></div>
            <div class="client-company"><?= htmlspecialchars($client['empresa']) ?></div>
            <span class="client-tag">Multimarcas</span>
        </div>

        <!-- Conteúdo Principal -->
        <div class="main-content">
            <!-- Informações Básicas -->
            <div class="info-section">
                <div class="section-header">
                    <span class="section-icon">📍</span>
                    <h3 class="section-title">Informações Básicas</h3>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Nome:</span>
                    <span class="info-value"><?= htmlspecialchars($client['cliente']) ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Empresa:</span>
                    <span class="info-value"><?= htmlspecialchars($client['empresa']) ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Cidade:</span>
                    <span class="info-value"><?= htmlspecialchars($client['cidade'] ?? 'TAGUAI') ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Estado:</span>
                    <span class="info-value"><?= htmlspecialchars($client['estado'] ?? 'SP') ?></span>
                </div>
                
                <a href="<?= BASE_URL ?>/crm/edit-client/<?= $client['id'] ?>" class="edit-client-btn">
                    ✏️ Editar Cliente
                </a>
            </div>

            <!-- Informações Comerciais -->
            <div class="info-section">
                <div class="section-header">
                    <span class="section-icon">📊</span>
                    <h3 class="section-title">Informações Comerciais</h3>
                </div>
                
                <div class="commercial-buttons">
                    <a href="<?= BASE_URL ?>/envase/cliente/<?= $client['id'] ?>" class="commercial-btn">
                        📊 Ver Dados de Envase Detalhados
                    </a>
                    
                    <a href="<?= BASE_URL ?>/envase/charts?client_id=<?= $client['id'] ?>" class="commercial-btn">
                        📈 Evolução
                    </a>
                </div>
            </div>
        </div>

        <!-- Histórico de Ações -->
        <div class="actions-section">
            <div class="section-header">
                <span class="section-icon">📋</span>
                <h3 class="section-title">Histórico de Ações</h3>
            </div>
            
            <button class="btn btn-success" onclick="openActionModal()" style="margin-bottom: 20px;">
                ➕ Nova Ação
            </button>
            
            <div id="actions-list">
                <?php if (!empty($actions)): ?>
                    <table class="actions-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Data</th>
                                <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Descrição</th>
                                <th style="padding: 12px; border: 1px solid #dee2e6; text-align: center;">Prazo</th>
                                <th style="padding: 12px; border: 1px solid #dee2e6; text-align: center;">Arquivo</th>
                                <th style="padding: 12px; border: 1px solid #dee2e6; text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($actions as $action): ?>
                            <tr>
                                <td style="padding: 12px; border: 1px solid #dee2e6;"><?= date('d/m/Y', strtotime($action['data_acao'])) ?></td>
                                <td style="padding: 12px; border: 1px solid #dee2e6;"><?= htmlspecialchars($action['descricao']) ?></td>
                                <td style="padding: 12px; border: 1px solid #dee2e6; text-align: center;">
                                    <?php if (!empty($action['prazo_conclusao'])): ?>
                                        <?php 
                                            $prazo = date('m/Y', strtotime($action['prazo_conclusao']));
                                            $prazoTimestamp = strtotime($action['prazo_conclusao']);
                                            $agora = time();
                                            $isVencido = $prazoTimestamp < $agora;
                                            $isProximo = ($prazoTimestamp - $agora) < (30 * 24 * 60 * 60); // 30 dias
                                        ?>
                                        <span style="
                                            padding: 4px 8px; 
                                            border-radius: 12px; 
                                            font-size: 12px; 
                                            font-weight: bold;
                                            <?php if ($isVencido): ?>
                                                background: #f8d7da; color: #721c24;
                                            <?php elseif ($isProximo): ?>
                                                background: #fff3cd; color: #856404;
                                            <?php else: ?>
                                                background: #d4edda; color: #155724;
                                            <?php endif; ?>
                                        ">
                                            🗓️ <?= $prazo ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #999;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; border: 1px solid #dee2e6; text-align: center;">
                                    <?php if (!empty($action['arquivo'])): ?>
                                        <a href="<?= BASE_URL ?>/uploads/actions/<?= htmlspecialchars($action['arquivo']) ?>" target="_blank" 
                                           class="btn" style="background: #17a2b8; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;">
                                            📎 Ver arquivo
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; border: 1px solid #dee2e6; text-align: center;">
                                    <button class="btn" style="background: #ffc107; color: #212529; padding: 5px 10px; margin-right: 5px;" 
                                            onclick="editAction(<?= $action['id'] ?>)" title="Editar">
                                        ✏️
                                    </button>
                                    <button class="btn btn-danger" style="padding: 5px 10px;" 
                                            onclick="deleteAction(<?= $action['id'] ?>)" title="Excluir">
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-actions" style="text-align: center; padding: 40px; color: #666; background: #f8f9fa; border-radius: 5px;">
                        <p>📋 Nenhuma ação registrada para este cliente.</p>
                        <p>Clique em "Nova Ação" para adicionar a primeira ação.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal Nova Ação -->
        <div id="actionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 6px; padding: 0; width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <div style="padding: 15px 20px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 600;">Nova Ação</h3>
                    <button onclick="closeActionModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #666; padding: 0; width: 20px; height: 20px;">×</button>
                </div>
                
                <form id="actionForm" enctype="multipart/form-data" style="padding: 20px;">
                    <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-size: 14px; color: #333;">Data:</label>
                        <input type="date" name="data_acao" value="<?= date('Y-m-d') ?>" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-size: 14px; color: #333;">Descrição:</label>
                        <textarea name="descricao" rows="3" required placeholder="Descreva a ação realizada..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; resize: vertical; box-sizing: border-box; font-family: inherit;"></textarea>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: flex; align-items: center; font-weight: bold; cursor: pointer;">
                            <input type="checkbox" id="temPrazo" onchange="togglePrazoField()" style="margin-right: 8px;">
                            <span>📅 Esta ação tem prazo de conclusão</span>
                        </label>
                    </div>
                    
                    <div id="prazoField" style="margin-bottom: 15px; display: none;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Prazo de Conclusão:</label>
                        <input type="month" name="prazo_conclusao" id="prazoInput" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                        <small style="color: #666; font-size: 12px;">Escolha o mês e ano limite para conclusão</small>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-size: 14px; color: #333;">Arquivo/Foto (opcional):</label>
                        <input type="file" name="arquivo" id="fileInput" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                        <small style="display: block; margin-top: 3px; font-size: 12px; color: #6c757d;">Formatos aceitos: imagens, PDF, Word</small>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" onclick="closeActionModal()" style="padding: 8px 16px; border: none; border-radius: 4px; font-size: 14px; cursor: pointer; background: #6c757d; color: white;">Cancelar</button>
                        <button type="submit" style="padding: 8px 16px; border: none; border-radius: 4px; font-size: 14px; cursor: pointer; background: #28a745; color: white;">Salvar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Editar Ação -->
        <div id="editActionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 6px; padding: 0; width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <div style="padding: 15px 20px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 600;">Editar Ação</h3>
                    <button onclick="closeEditActionModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #666; padding: 0; width: 20px; height: 20px;">×</button>
                </div>
                
                <form id="editActionForm" enctype="multipart/form-data" style="padding: 20px;">
                    <input type="hidden" name="action_id" id="editActionId">
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-size: 14px; color: #333;">Data:</label>
                        <input type="date" name="data_acao" id="editDataAcao" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Descrição:</label>
                        <textarea name="descricao" id="editDescricao" rows="3" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; resize: vertical; box-sizing: border-box; font-family: inherit;"></textarea>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: flex; align-items: center; font-weight: bold; cursor: pointer;">
                            <input type="checkbox" id="editTemPrazo" onchange="toggleEditPrazoField()" style="margin-right: 8px;">
                            <span>📅 Esta ação tem prazo de conclusão</span>
                        </label>
                    </div>
                    
                    <div id="editPrazoField" style="margin-bottom: 15px; display: none;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Prazo de Conclusão:</label>
                        <input type="month" name="prazo_conclusao" id="editPrazoInput" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                        <small style="color: #666; font-size: 12px;">Escolha o mês e ano limite para conclusão</small>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-size: 14px; color: #333;">Arquivo atual:</label>
                        <div id="currentFile" style="padding: 5px 0; font-size: 14px; color: #666;"></div>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-size: 14px; color: #333;">Novo arquivo (opcional):</label>
                        <input type="file" name="arquivo" id="editFileInput" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                        <small style="display: block; margin-top: 3px; font-size: 12px; color: #6c757d;">Formatos aceitos: imagens, PDF, Word</small>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" onclick="closeEditActionModal()" style="padding: 8px 16px; border: none; border-radius: 4px; font-size: 14px; cursor: pointer; background: #6c757d; color: white;">Cancelar</button>
                        <button type="submit" style="padding: 8px 16px; border: none; border-radius: 4px; font-size: 14px; cursor: pointer; background: #ffc107; color: #212529;">Atualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openActionModal() {
    document.getElementById('actionModal').style.display = 'block';
    document.getElementById('actionForm').reset();
}

function closeActionModal() {
    document.getElementById('actionModal').style.display = 'none';
}

function togglePrazoField() {
    const checkbox = document.getElementById('temPrazo');
    const prazoField = document.getElementById('prazoField');
    const prazoInput = document.getElementById('prazoInput');
    
    if (checkbox.checked) {
        prazoField.style.display = 'block';
        prazoInput.required = true;
        // Definir valor padrão como próximo mês
        const hoje = new Date();
        const proximoMes = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 1);
        const anoMes = proximoMes.getFullYear() + '-' + String(proximoMes.getMonth() + 1).padStart(2, '0');
        prazoInput.value = anoMes;
    } else {
        prazoField.style.display = 'none';
        prazoInput.required = false;
        prazoInput.value = '';
    }
}

function togglePrazoField() {
    const checkbox = document.getElementById('temPrazo');
    const prazoField = document.getElementById('prazoField');
    const prazoInput = document.getElementById('prazoInput');
    
    if (checkbox.checked) {
        prazoField.style.display = 'block';
        prazoInput.required = true;
    } else {
        prazoField.style.display = 'none';
        prazoInput.required = false;
        prazoInput.value = '';
    }
}

function toggleEditPrazoField() {
    const checkbox = document.getElementById('editTemPrazo');
    const prazoField = document.getElementById('editPrazoField');
    const prazoInput = document.getElementById('editPrazoInput');
    
    if (checkbox.checked) {
        prazoField.style.display = 'block';
        prazoInput.required = true;
    } else {
        prazoField.style.display = 'none';
        prazoInput.required = false;
        prazoInput.value = '';
    }
}

function openEditActionModal(actionId) {
    // Buscar dados da ação
    fetch('<?= BASE_URL ?>/action/' + actionId)
    .then(response => response.json())
    .then(action => {
        document.getElementById('editActionId').value = action.id;
        document.getElementById('editDataAcao').value = action.data_acao;
        document.getElementById('editDescricao').value = action.descricao;
        
        // Configurar prazo de conclusão
        const temPrazoCheckbox = document.getElementById('editTemPrazo');
        const prazoInput = document.getElementById('editPrazoInput');
        
        if (action.prazo_conclusao) {
            temPrazoCheckbox.checked = true;
            // Converter data para formato YYYY-MM
            const prazoDate = new Date(action.prazo_conclusao + 'T00:00:00');
            const anoMes = prazoDate.getFullYear() + '-' + String(prazoDate.getMonth() + 1).padStart(2, '0');
            prazoInput.value = anoMes;
            toggleEditPrazoField();
        } else {
            temPrazoCheckbox.checked = false;
            prazoInput.value = '';
            toggleEditPrazoField();
        }
        
        // Mostrar arquivo atual
        const currentFileDiv = document.getElementById('currentFile');
        if (action.arquivo) {
            currentFileDiv.innerHTML = '<a href="<?= BASE_URL ?>/uploads/actions/' + action.arquivo + '" target="_blank" style="color: #17a2b8; text-decoration: none;">📎 ' + action.arquivo + '</a>';
        } else {
            currentFileDiv.textContent = 'Nenhum arquivo anexado';
        }
        
        document.getElementById('editActionModal').style.display = 'block';
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('❌ Erro ao carregar dados da ação');
    });
}

function closeEditActionModal() {
    document.getElementById('editActionModal').style.display = 'none';
    document.getElementById('editActionForm').reset();
}

// Formulário de nova ação
document.getElementById('actionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.textContent = 'Salvando...';
    submitBtn.disabled = true;
    
    fetch('<?= BASE_URL ?>/action', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Ação criada com sucesso!');
            closeActionModal();
            location.reload();
        } else {
            alert('❌ Erro: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        alert('❌ Erro na requisição: ' + error.message);
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

// Formulário de edição de ação
document.getElementById('editActionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const actionId = document.getElementById('editActionId').value;
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.textContent = 'Atualizando...';
    submitBtn.disabled = true;
    
    fetch('<?= BASE_URL ?>/action/' + actionId, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Ação atualizada com sucesso!');
            closeEditActionModal();
            location.reload();
        } else {
            alert('❌ Erro: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('❌ Erro na requisição');
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

function editAction(actionId) {
    openEditActionModal(actionId);
}

function deleteAction(actionId) {
    if (confirm('Tem certeza que deseja excluir esta ação?')) {
        fetch('<?= BASE_URL ?>/action/' + actionId, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Ação excluída com sucesso!');
                location.reload();
            } else {
                alert('❌ Erro: ' + (data.message || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            alert('❌ Erro na requisição: ' + error.message);
        });
    }
}
</script>

<?php $content = ob_get_clean(); ?>
<?php $pageTitle = 'Cliente - Web Aguaboa'; $flashMessages = getFlashMessages(); include '../src/views/layouts/main.php'; ?>
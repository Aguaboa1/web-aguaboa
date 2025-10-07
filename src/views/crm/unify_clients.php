<?php ob_start(); ?>

<div class="card">
    <div class="card-header">
        🔗 Unificar Clientes Duplicados
        <a href="<?= BASE_URL ?>/crm" class="btn btn-secondary" style="float: right;">
            ← Voltar para Página Inicial
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($duplicates)): ?>
            <div class="alert alert-info">
                <h4>✅ Nenhum cliente duplicado encontrado!</h4>
                <p>Todos os clientes do sistema já estão únicos. Não há necessidade de unificação no momento.</p>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <h4>⚠️ Clientes Duplicados Detectados</h4>
                <p>O sistema identificou <strong><?= count($duplicates) ?> grupo(s)</strong> de clientes que parecem ser duplicados. 
                Revise cada grupo e selecione quais clientes devem ser unificados.</p>
                
                <div style="margin-top: 1rem; padding: 1rem; background: #fff3cd; border-radius: 6px; border-left: 4px solid #ffc107;">
                    <h5 style="margin: 0 0 0.5rem 0; color: #856404;">🤖 Unificação Automática</h5>
                    <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem;">Quer unificar todos os grupos automaticamente? O sistema manterá o cliente com maior volume de envase em cada grupo.</p>
                    <a href="<?= BASE_URL ?>/crm/unify-clients?auto=1" 
                       onclick="return confirm('⚠️ ATENÇÃO!\n\nEsta ação irá unificar TODOS os grupos automaticamente.\n\nO cliente com maior volume de envase será mantido em cada grupo.\n\nEsta operação NÃO pode ser desfeita!\n\nDeseja continuar?')" 
                       class="btn" style="background: #28a745; color: white; font-weight: bold;">
                        🤖 Unificar Tudo Automaticamente
                    </a>
                </div>
            </div>
            
            <form method="POST" action="<?= BASE_URL ?>/crm/unify-clients" onsubmit="return confirmUnification()">
                <?php foreach ($duplicates as $groupIndex => $group): ?>
                    <div class="duplicate-group">
                        <h4 style="color: #007fa3; margin-bottom: 1rem;">
                            📋 Grupo <?= $groupIndex + 1 ?> - Clientes Similares
                        </h4>
                        
                        <div class="clients-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                            <?php foreach ($group as $clientIndex => $client): ?>
                                <div class="client-card <?= $clientIndex === 0 ? 'master' : '' ?>">
                                    <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                                        <input type="radio" 
                                               name="master_client_group_<?= $groupIndex ?>" 
                                               value="<?= $client['id'] ?>" 
                                               id="master_<?= $groupIndex ?>_<?= $client['id'] ?>"
                                               <?= $clientIndex === 0 ? 'checked' : '' ?>
                                               style="margin-right: 0.5rem;">
                                        <label for="master_<?= $groupIndex ?>_<?= $client['id'] ?>" style="font-weight: bold; margin: 0;">
                                            <?= $clientIndex === 0 ? '👑 Cliente Principal' : '🔗 Unificar com' ?>
                                        </label>
                                    </div>
                                    
                                    <div style="margin-left: 1.5rem;">
                                        <strong style="color: #007fa3;"><?= htmlspecialchars($client['cliente']) ?></strong><br>
                                        
                                        <?php if ($client['empresa'] && $client['empresa'] !== $client['cliente']): ?>
                                            <span style="color: #666;">🏢 <?= htmlspecialchars($client['empresa']) ?></span><br>
                                        <?php endif; ?>
                                        
                                        <?php if ($client['cidade']): ?>
                                            <span style="color: #666;">📍 <?= htmlspecialchars($client['cidade']) ?></span><br>
                                        <?php endif; ?>
                                        
                                        <span style="color: #28a745; font-weight: bold;">
                                            📦 <?= number_format($client['total_envases']) ?> envases
                                        </span>
                                        
                                        <?php if ($clientIndex > 0): ?>
                                            <div style="margin-top: 0.5rem;">
                                                <input type="checkbox" 
                                                       name="clients_to_merge[]" 
                                                       value="<?= $client['id'] ?>" 
                                                       id="merge_<?= $client['id'] ?>"
                                                       checked
                                                       style="margin-right: 0.5rem;">
                                                <label for="merge_<?= $client['id'] ?>" style="font-size: 0.9rem; margin: 0;">
                                                    Incluir na unificação
                                                </label>
                                            </div>
                                        <?php else: ?>
                                            <!-- Cliente principal do grupo - sempre será o master quando este grupo for processado -->
                                            <div style="margin-top: 0.5rem; color: #28a745; font-size: 0.9rem;">
                                                ✓ Cliente que permanecerá após unificação
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="margin-top: 1rem; padding: 0.75rem; background: #fff3cd; border-radius: 4px; font-size: 0.9rem;">
                            <strong>ℹ️ Como funciona a unificação:</strong><br>
                            • O cliente principal (👑) será mantido e receberá todos os dados<br>
                            • Os clientes marcados serão unidos ao principal e depois removidos<br>
                            • Todos os dados de envase e ações serão transferidos<br>
                            • Esta ação não pode ser desfeita
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 2px solid #dee2e6;">
                    <button type="submit" class="btn btn-warning" style="padding: 0.75rem 2rem; font-size: 1.1rem;">
                        🔗 Unificar Clientes Selecionados
                    </button>
                    <a href="<?= BASE_URL ?>/crm" class="btn btn-secondary" style="margin-left: 1rem;">
                        ❌ Cancelar (Página Inicial)
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmUnification() {
    const checkedClients = document.querySelectorAll('input[name="clients_to_merge[]"]:checked');
    
    if (checkedClients.length === 0) {
        alert('Selecione pelo menos um cliente para unificar!');
        return false;
    }
    
    const clientNames = Array.from(checkedClients).map(checkbox => {
        const card = checkbox.closest('.client-card');
        const name = card.querySelector('strong').textContent;
        return name;
    });
    
    const message = `⚠️ ATENÇÃO: Esta ação não pode ser desfeita!\n\n` +
                   `Você está prestes a unificar ${checkedClients.length} cliente(s):\n` +
                   clientNames.map(name => `• ${name}`).join('\n') + '\n\n' +
                   `Todos os dados serão transferidos para o cliente principal e os demais serão removidos.\n\n` +
                   `Deseja continuar?`;
    
    return confirm(message);
}

// Atualizar master client quando radio é alterado
document.querySelectorAll('input[type="radio"][name^="master_client_group_"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const groupIndex = this.name.split('_')[3];
        
        // Encontrar ou criar o hidden input para este grupo
        let masterInput = document.querySelector(`input[name="master_client_id_${groupIndex}"]`);
        if (!masterInput) {
            masterInput = document.createElement('input');
            masterInput.type = 'hidden';
            masterInput.name = 'master_client_id';
            this.closest('form').appendChild(masterInput);
        }
        masterInput.value = this.value;
        
        // Desmarcar o checkbox do cliente principal (ele não pode ser mesclado consigo mesmo)
        const groupContainer = this.closest('.duplicate-group');
        const checkboxes = groupContainer.querySelectorAll('input[name="clients_to_merge[]"]');
        
        checkboxes.forEach(checkbox => {
            if (checkbox.value === this.value) {
                checkbox.checked = false;
                checkbox.disabled = true;
            } else {
                checkbox.disabled = false;
            }
        });
    });
});

// Inicializar estado dos checkboxes
document.querySelectorAll('input[type="radio"][name^="master_client_group_"]:checked').forEach(radio => {
    radio.dispatchEvent(new Event('change'));
});
</script>

<style>
.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}

.alert-info {
    background-color: #e7f3ff;
    border-left: 4px solid #007fa3;
    color: #0c5460;
}

.alert-warning {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
    color: #856404;
}
</style>

<?php $content = ob_get_clean(); ?>
<?php include '../src/views/layouts/main.php'; ?>
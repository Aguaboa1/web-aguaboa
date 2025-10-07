<?php
require_once '../src/views/layout/header.php';
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                👤 Gerenciar Permissões de Usuários
            </div>
            <div class="header-buttons">
                <a href="<?= BASE_URL ?>/admin/users" class="custom-back-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                    <span>Voltar aos Usuários</span>
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div style="margin-bottom: 2rem;">
            <h3>Usuário: <?= htmlspecialchars($user['username']) ?></h3>
            <p style="color: #666;">Role: <?= $user['role'] === 'admin' ? 'Administrador' : 'Equipe' ?></p>
        </div>

        <form method="POST" id="permissionsForm">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            
            <div style="margin-bottom: 2rem;">
                <h4 style="margin-bottom: 1rem; color: #007fa3;">Permissões por Departamento</h4>
                
                <div style="display: grid; gap: 1.5rem;">
                    <?php foreach ($allDepartments as $deptKey => $deptName): ?>
                        <div class="department-permission" style="
                            border: 1px solid #e9ecef;
                            border-radius: 8px;
                            padding: 1.5rem;
                            background: #f8f9fa;
                        ">
                            <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                                <span style="font-size: 1.5rem; margin-right: 1rem;">
                                    <?php 
                                    $icons = [
                                        'comercial' => '👥',
                                        'financeiro' => '💰',
                                        'rh' => '👨‍💼',
                                        'qualidade' => '⭐',
                                        'atendimento' => '📞',
                                        'producao' => '🏭'
                                    ];
                                    echo $icons[$deptKey] ?? '📋';
                                    ?>
                                </span>
                                <h5 style="margin: 0; color: #333;"><?= htmlspecialchars($deptName) ?></h5>
                            </div>
                            
                            <div style="display: flex; gap: 2rem;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="checkbox" 
                                           name="permissions[<?= $deptKey ?>][can_view]" 
                                           value="1"
                                           <?= isset($userPermissions[$deptKey]) && $userPermissions[$deptKey]['can_view'] ? 'checked' : '' ?>
                                           onchange="handleViewPermission('<?= $deptKey ?>')"
                                           style="margin-right: 8px; transform: scale(1.2);">
                                    <span style="font-weight: 500;">👁️ Pode Visualizar</span>
                                </label>
                                
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="checkbox" 
                                           name="permissions[<?= $deptKey ?>][can_edit]" 
                                           value="1"
                                           id="edit_<?= $deptKey ?>"
                                           <?= isset($userPermissions[$deptKey]) && $userPermissions[$deptKey]['can_edit'] ? 'checked' : '' ?>
                                           style="margin-right: 8px; transform: scale(1.2);">
                                    <span style="font-weight: 500;">✏️ Pode Editar</span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 1rem; padding-top: 2rem; border-top: 1px solid #e9ecef;">
                <a href="<?= BASE_URL ?>/admin/users" class="btn" style="background: #6c757d;">
                    Cancelar
                </a>
                <button type="submit" class="btn" style="background: #28a745;">
                    💾 Salvar Permissões
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function handleViewPermission(department) {
    const viewCheckbox = document.querySelector(`input[name="permissions[${department}][can_view]"]`);
    const editCheckbox = document.getElementById(`edit_${department}`);
    
    if (!viewCheckbox.checked) {
        // Se desmarcar visualização, desmarcar edição também
        editCheckbox.checked = false;
    }
}

// Interceptar mudanças no checkbox de edição
document.querySelectorAll('input[name*="[can_edit]"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        if (this.checked) {
            // Se marcar edição, marcar visualização também
            const department = this.name.match(/\[(.*?)\]/)[1];
            const viewCheckbox = document.querySelector(`input[name="permissions[${department}][can_view]"]`);
            viewCheckbox.checked = true;
        }
    });
});

// Validação do formulário
document.getElementById('permissionsForm').addEventListener('submit', function(e) {
    const hasAnyPermission = Array.from(document.querySelectorAll('input[name*="[can_view]"]')).some(cb => cb.checked);
    
    if (!hasAnyPermission) {
        e.preventDefault();
        alert('O usuário deve ter acesso a pelo menos um departamento.');
        return false;
    }
    
    return true;
});
</script>

<?php require_once '../src/views/layout/footer.php'; ?>
<?php
require_once '../src/views/layout/header.php';
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                🔐 Gerenciar Permissões de Setores
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
        <?php if (!empty($flashMessages)): ?>
            <?php foreach ($flashMessages as $flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>" style="padding: 1rem; margin-bottom: 1rem; border-radius: 6px; 
                     background: <?= $flash['type'] === 'success' ? '#d4edda' : '#f8d7da' ?>; 
                     color: <?= $flash['type'] === 'success' ? '#155724' : '#721c24' ?>;">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div style="margin-bottom: 2rem;">
            <h3 style="color: #007fa3; margin-bottom: 1rem;">
                👤 Usuário: <?= htmlspecialchars($user['username']) ?> 
                (<?= $user['role'] === 'admin' ? 'Administrador' : 'Equipe' ?>)
            </h3>
            <p style="color: #666;">
                Configure quais setores este usuário pode visualizar e editar.
            </p>
        </div>
        
        <form method="POST" style="background: #f8f9fa; padding: 2rem; border-radius: 8px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <?php foreach ($departments as $key => $dept): ?>
                    <div style="background: white; padding: 1.5rem; border-radius: 8px; border: 1px solid #dee2e6;">
                        <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                            <span style="font-size: 2rem; margin-right: 1rem;"><?= $dept['icon'] ?></span>
                            <h4 style="color: #007fa3; margin: 0;"><?= $dept['name'] ?></h4>
                        </div>
                        
                        <p style="color: #666; font-size: 0.9rem; margin-bottom: 1.5rem;">
                            <?= $dept['description'] ?>
                        </p>
                        
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="permissions[<?= $key ?>][can_view]" value="1" 
                                       <?= (isset($userPermissions[$key]) && $userPermissions[$key]['can_view']) ? 'checked' : '' ?>
                                       style="margin-right: 0.5rem;">
                                <span>👁️ Pode visualizar</span>
                            </label>
                            
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="permissions[<?= $key ?>][can_edit]" value="1"
                                       <?= (isset($userPermissions[$key]) && $userPermissions[$key]['can_edit']) ? 'checked' : '' ?>
                                       style="margin-right: 0.5rem;">
                                <span>✏️ Pode editar</span>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #dee2e6;">
                <button type="submit" style="
                    background: linear-gradient(135deg, #28a745, #20c997); 
                    color: white; 
                    border: none; 
                    padding: 1rem 2rem; 
                    border-radius: 8px; 
                    font-size: 1rem; 
                    font-weight: bold; 
                    cursor: pointer;
                    box-shadow: 0 3px 6px rgba(40, 167, 69, 0.3);
                    transition: all 0.3s ease;
                " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(40, 167, 69, 0.4)';"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 6px rgba(40, 167, 69, 0.3)';">
                    💾 Salvar Permissões
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../src/views/layout/footer.php'; ?>
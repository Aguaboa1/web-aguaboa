<?php ob_start(); ?>

<div class="stats">
    <div class="stat-card">
        <div class="stat-number"><?= count($users) ?></div>
        <div class="stat-label">Total de Usuários</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= count(array_filter($users, fn($u) => $u['role'] === 'admin')) ?></div>
        <div class="stat-label">Administradores</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= count(array_filter($users, fn($u) => $u['is_active'])) ?></div>
        <div class="stat-label">Usuários Ativos</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        👥 Gerenciamento de Usuários
        <button class="btn btn-success" onclick="openModal('createUserModal')" style="float: right;">
            ➕ Cadastrar Novo Usuário
        </button>
    </div>
    <div class="card-body" style="padding: 0;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome de Usuário</th>
                    <th>Senha</th>
                    <th>Função</th>
                    <th>Criado em</th>
                    <th>Último Login</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                    <td>
                        <span style="font-family: monospace; background: #f8f9fa; padding: 0.3rem 0.5rem; border-radius: 3px; border: 1px solid #ddd;">
                            <?= htmlspecialchars($user['password_plain'] ?: '****') ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($user['role'] === 'admin'): ?>
                            <span class="status-badge" style="background: #dc3545; color: white;">Administrador</span>
                        <?php else: ?>
                            <span class="status-badge" style="background: #28a745; color: white;">Equipe</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $user['created_at'] ? date('d/m/Y H:i', strtotime($user['created_at'])) : '-' ?></td>
                    <td><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca' ?></td>
                    <td>
                        <span style="color: <?= $user['is_active'] ? 'green' : 'red' ?>; font-weight: bold;">
                            <?= $user['is_active'] ? '✅ Ativo' : '❌ Inativo' ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= BASE_URL ?>/admin/manage-permissions/<?= $user['id'] ?>" 
                           class="btn" style="background: #17a2b8; color: white; margin-right: 5px;">
                            🔐 Permissões
                        </a>
                        <button class="btn btn-warning" 
                                onclick="editUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>', '<?= htmlspecialchars($user['password_plain'] ?: '') ?>', '<?= $user['role'] ?>')">
                            ✏️ Editar
                        </button>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="<?= BASE_URL ?>/admin/toggle-user/<?= $user['id'] ?>" 
                               class="btn <?= $user['is_active'] ? 'btn-danger' : 'btn-success' ?>"
                               onclick="return confirm('Tem certeza que deseja <?= $user['is_active'] ? 'desativar' : 'ativar' ?> este usuário?')">
                                <?= $user['is_active'] ? '❌ Desativar' : '✅ Ativar' ?>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/admin/delete-user/<?= $user['id'] ?>" 
                                  style="display: inline;" onsubmit="return confirmDelete('Tem certeza que deseja excluir este usuário?')">
                                <button type="submit" class="btn btn-danger">🗑️ Excluir</button>
                            </form>
                        <?php else: ?>
                            <span style="color: #666; font-style: italic;">Você mesmo</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Criar Usuário -->
<div id="createUserModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('createUserModal')">&times;</span>
        <h3>Cadastrar Novo Usuário</h3>
        <form method="POST" action="<?= BASE_URL ?>/admin/create-user">
            <div class="form-group">
                <label>Nome de Usuário:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Senha:</label>
                <input type="text" name="password" required minlength="6">
                <small style="color: #666;">Mínimo de 6 caracteres</small>
            </div>
            <div class="form-group">
                <label>Função:</label>
                <select name="role" required>
                    <option value="equipe">Equipe</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-success">Criar Usuário</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('createUserModal')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Usuário -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editUserModal')">&times;</span>
        <h3>Editar Usuário</h3>
        <form method="POST" id="editUserForm">
            <div class="form-group">
                <label>Nome de Usuário:</label>
                <input type="text" name="username" id="editUsername" required>
            </div>
            <div class="form-group">
                <label>Nova Senha (deixe em branco para manter):</label>
                <input type="text" name="password" id="editPassword" minlength="6">
                <small style="color: #666;">Mínimo de 6 caracteres (opcional)</small>
            </div>
            <div class="form-group">
                <label>Função:</label>
                <select name="role" id="editRole" required>
                    <option value="equipe">Equipe</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-success">Salvar Alterações</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editUser(userId, username, password, role) {
    document.getElementById('editUsername').value = username;
    document.getElementById('editPassword').value = password;
    document.getElementById('editRole').value = role;
    document.getElementById('editUserForm').action = '<?= BASE_URL ?>/admin/edit-user/' + userId;
    openModal('editUserModal');
}
</script>

<?php $content = ob_get_clean(); ?>
<?php include '../src/views/layouts/admin.php'; ?>
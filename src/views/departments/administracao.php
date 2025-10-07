<?php
require_once '../config/init.php';
require_once '../src/models/UserPermission.php';

// Verificar autenticação
requireAuth();

// Verificar se é administrador ou tem permissão específica de administração
$userPermission = new UserPermission();
$isAdmin = ($_SESSION['role'] === 'admin');
$canAdminSystem = $userPermission->canAccessDepartment($_SESSION['user_id'], 'administracao', 'view');

if (!$isAdmin && !$canAdminSystem) {
    setFlash('error', 'Acesso negado ao departamento de administração do sistema');
    redirect('/departments');
}

// Verificar permissão de edição
$canEdit = $isAdmin || $userPermission->canAccessDepartment($_SESSION['user_id'], 'administracao', 'edit');
$permissionText = $canEdit ? 'Administrador do Sistema' : 'Visualizador';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração do Sistema - Web Aguaboa</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f6fa;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin: 0;
            white-space: nowrap;
        }
        
        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: background-color 0.3s;
            font-weight: 500;
        }
        
        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .main-container {
            min-height: calc(100vh - 100px);
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .admin-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            overflow: hidden;
            transition: transform 0.2s ease;
        }
        
        .admin-card:hover {
            transform: translateY(-2px);
        }
        
        .admin-card-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            font-size: 1.1rem;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .admin-card-body {
            padding: 2rem;
        }
        
        .admin-card-description {
            color: #6c757d;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #007fa3, #00a8cc);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn:hover {
            background: linear-gradient(135deg, #005f7a, #007fa3);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border-left: 4px solid #007fa3;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #007fa3;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .permission-badge {
            background: #28a745;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .permission-badge.viewer {
            background: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>⚙️ Web Aguaboa - Administração do Sistema</h1>
            <div class="nav-links">
                <a href="<?= BASE_URL ?>/departments">🏢 Setores</a>
                <a href="<?= BASE_URL ?>/admin/users">👤 Usuários</a>
                <a href="<?= BASE_URL ?>/admin/logs">📋 Logs</a>
                <a href="<?= BASE_URL ?>/admin/configuracoes">⚙️ Configurações</a>
                
                <!-- Informações do Usuário -->
                <div style="display: flex; align-items: center; gap: 1rem; margin-left: 2rem; padding-left: 2rem; border-left: 1px solid rgba(255,255,255,0.3); font-size: 0.85rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span>👤</span>
                        <span><strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span><?= $canEdit ? '🔧' : '👁️' ?></span>
                        <span><?= $permissionText ?> | Sistema</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span>📅</span>
                        <span><?= date('d/m/Y H:i') ?></span>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/auth/logout">🚪 Sair</a>
            </div>
        </div>
    </div>
    
    <div class="main-container">
        <!-- Estatísticas do Sistema -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?= $_SESSION['role'] === 'admin' ? '∞' : '1' ?></div>
                <div class="stat-label">Nível de Acesso</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= date('H:i') ?></div>
                <div class="stat-label">Horário Atual</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $_SESSION['user_id'] ?></div>
                <div class="stat-label">ID do Usuário</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= strtoupper($_SESSION['role']) ?></div>
                <div class="stat-label">Tipo de Perfil</div>
            </div>
        </div>
        
        <!-- Grid de Funcionalidades Administrativas -->
        <div class="admin-grid">
            <!-- Gestão de Usuários -->
            <div class="admin-card">
                <div class="admin-card-header">
                    👤 Gestão de Usuários
                    <?php if ($canEdit): ?>
                        <span class="permission-badge">Editor</span>
                    <?php else: ?>
                        <span class="permission-badge viewer">Visualizador</span>
                    <?php endif; ?>
                </div>
                <div class="admin-card-body">
                    <p class="admin-card-description">
                        Gerencie usuários do sistema, permissões de acesso, criação de novos usuários e definição de roles por departamento.
                    </p>
                    <a href="<?= BASE_URL ?>/admin/users" class="btn">
                        👤 Gerenciar Usuários
                    </a>
                </div>
            </div>
            
            <!-- Logs do Sistema -->
            <div class="admin-card">
                <div class="admin-card-header">
                    📋 Logs de Atividade
                    <span class="permission-badge">Visualizador</span>
                </div>
                <div class="admin-card-body">
                    <p class="admin-card-description">
                        Visualize logs de atividades, tentativas de login, ações dos usuários e eventos do sistema para auditoria e segurança.
                    </p>
                    <a href="<?= BASE_URL ?>/admin/logs" class="btn btn-secondary">
                        📋 Ver Logs
                    </a>
                </div>
            </div>
            
            <!-- Configurações Pessoais -->
            <div class="admin-card">
                <div class="admin-card-header">
                    🔒 Configurações Pessoais
                    <span class="permission-badge">Editor</span>
                </div>
                <div class="admin-card-body">
                    <p class="admin-card-description">
                        Altere sua senha de acesso, configure preferências pessoais e gerencie informações da sua conta no sistema.
                    </p>
                    <a href="<?= BASE_URL ?>/auth/change-password" class="btn btn-warning">
                        🔒 Alterar Senha
                    </a>
                </div>
            </div>
            
            <!-- Backup e Manutenção -->
            <?php if ($canEdit): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    💾 Backup e Manutenção
                    <span class="permission-badge">Editor</span>
                </div>
                <div class="admin-card-body">
                    <p class="admin-card-description">
                        Execute backups do sistema, limpeza de logs antigos, otimização do banco de dados e outras tarefas de manutenção.
                    </p>
                    <a href="#" onclick="executarBackup()" class="btn btn-danger">
                        💾 Executar Backup
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function executarBackup() {
            if (confirm('Deseja executar um backup completo do sistema?')) {
                // Aqui você pode implementar a chamada para o script de backup
                alert('Backup iniciado! Você será notificado quando concluído.');
            }
        }
    </script>
</body>
</html>
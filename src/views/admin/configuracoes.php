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
    setFlash('error', 'Acesso negado às configurações do sistema');
    redirect('/departments');
}

// Verificar permissão de edição
$canEdit = $isAdmin || $userPermission->canAccessDepartment($_SESSION['user_id'], 'administracao', 'edit');

// Processar formulários
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'change_password':
            if (isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
                $currentPassword = $_POST['current_password'];
                $newPassword = $_POST['new_password'];
                $confirmPassword = $_POST['confirm_password'];
                
                if ($newPassword !== $confirmPassword) {
                    setFlash('error', 'A confirmação da senha não confere');
                } elseif (strlen($newPassword) < 6) {
                    setFlash('error', 'A nova senha deve ter pelo menos 6 caracteres');
                } else {
                    // Verificar senha atual
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($user && password_verify($currentPassword, $user['password'])) {
                        // Atualizar senha
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                        
                        if ($stmt->execute([$hashedPassword, $_SESSION['user_id']])) {
                            setFlash('success', 'Senha alterada com sucesso!');
                        } else {
                            setFlash('error', 'Erro ao alterar senha. Tente novamente.');
                        }
                    } else {
                        setFlash('error', 'Senha atual incorreta');
                    }
                }
            }
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações do Sistema - Web Aguaboa</title>
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
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-body {
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
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007fa3;
            box-shadow: 0 0 0 3px rgba(0, 127, 163, 0.1);
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
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
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #e8f5e8;
            border: 1px solid #c8e6c9;
            color: #388e3c;
        }
        
        .alert-error {
            background: #ffebee;
            border: 1px solid #ffcdd2;
            color: #d32f2f;
        }
        
        .config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .config-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #007fa3;
        }
        
        .config-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .config-value {
            color: #6c757d;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>⚙️ Configurações do Sistema</h1>
            <div class="nav-links">
                <a href="<?= BASE_URL ?>/administracao">← Voltar à Administração</a>
                <a href="<?= BASE_URL ?>/departments">🏢 Setores</a>
                <a href="<?= BASE_URL ?>/auth/logout">🚪 Sair</a>
            </div>
        </div>
    </div>
    
    <div class="main-container">
        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $messages): ?>
                <?php foreach ((array)$messages as $message): ?>
                    <div class="alert alert-<?= $type ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
        
        <!-- Alterar Senha -->
        <div class="card">
            <div class="card-header">
                🔒 Alterar Senha de Acesso
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label>Senha Atual:</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Nova Senha:</label>
                        <input type="password" name="new_password" class="form-control" minlength="6" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirmar Nova Senha:</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        🔒 Alterar Senha
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Informações do Sistema -->
        <div class="card">
            <div class="card-header">
                📋 Informações do Sistema
            </div>
            <div class="card-body">
                <div class="config-grid">
                    <div class="config-item">
                        <div class="config-label">Usuário Logado</div>
                        <div class="config-value"><?= htmlspecialchars($_SESSION['username']) ?></div>
                    </div>
                    <div class="config-item">
                        <div class="config-label">Tipo de Perfil</div>
                        <div class="config-value"><?= strtoupper($_SESSION['role']) ?></div>
                    </div>
                    <div class="config-item">
                        <div class="config-label">ID do Usuário</div>
                        <div class="config-value"><?= $_SESSION['user_id'] ?></div>
                    </div>
                    <div class="config-item">
                        <div class="config-label">Sessão Iniciada</div>
                        <div class="config-value"><?= isset($_SESSION['login_time']) ? date('d/m/Y H:i:s', $_SESSION['login_time']) : 'N/A' ?></div>
                    </div>
                    <div class="config-item">
                        <div class="config-label">Versão do Sistema</div>
                        <div class="config-value">Web Aguaboa v1.0</div>
                    </div>
                    <div class="config-item">
                        <div class="config-label">Servidor</div>
                        <div class="config-value"><?= $_SERVER['SERVER_NAME'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($canEdit): ?>
        <!-- Configurações Avançadas (apenas para editores) -->
        <div class="card">
            <div class="card-header">
                🔧 Configurações Avançadas
            </div>
            <div class="card-body">
                <p style="color: #6c757d; margin-bottom: 1.5rem;">
                    Funcionalidades avançadas de configuração do sistema estarão disponíveis em futuras versões.
                </p>
                <a href="<?= BASE_URL ?>/admin/users" class="btn btn-secondary">
                    👤 Gerenciar Usuários
                </a>
                <a href="<?= BASE_URL ?>/admin/logs" class="btn btn-secondary" style="margin-left: 1rem;">
                    📋 Ver Logs do Sistema
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
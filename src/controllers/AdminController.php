<?php
/**
 * Controller Admin
 * Sistema Aguaboa - Gestão Comercial
 */

class AdminController {
    private $userModel;
    private $activityLog;
    private $userPermission;
    
    public function __construct() {
        $this->userModel = new User();
        $this->activityLog = new ActivityLog();
        $this->userPermission = new UserPermission();
    }
    
    /**
     * Página de gerenciamento de usuários
     */
    public function users() {
        requireAuth();
        
        // Verificar se é admin ou tem permissão de administração
        if ($_SESSION['role'] !== 'admin' && !$this->userPermission->canAccessDepartment($_SESSION['user_id'], 'administracao', 'view')) {
            setFlash('error', 'Acesso negado à administração');
            redirect('/departments');
            return;
        }
        
        $users = $this->userModel->findAll();
        
        $pageTitle = 'Gerenciar Usuários - Sistema Aguaboa';
        $flashMessages = getFlashMessages();
        
        include '../src/views/admin/users.php';
    }
    
    /**
     * Página de logs de atividade
     */
    public function logs() {
        requireAuth();
        
        // Verificar se é admin ou tem permissão de administração
        if ($_SESSION['role'] !== 'admin' && !$this->userPermission->canAccessDepartment($_SESSION['user_id'], 'administracao', 'view')) {
            setFlash('error', 'Acesso negado à administração');
            redirect('/departments');
            return;
        }
        
        $logs = $this->activityLog->getRecentLogs(100);
        
        $pageTitle = 'Logs de Atividade - Sistema Aguaboa';
        $flashMessages = getFlashMessages();
        
        include '../src/views/admin/logs.php';
    }
    
    /**
     * Criar novo usuário
     */
    public function createUser() {
        requireAuth();
        
        // Verificar se é admin ou tem permissão de administração
        if ($_SESSION['role'] !== 'admin' && !$this->userPermission->canAccessDepartment($_SESSION['user_id'], 'administracao', 'edit')) {
            setFlash('error', 'Acesso negado à administração');
            redirect('/departments');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize($_POST['username']);
            $password = $_POST['password'];
            $role = sanitize($_POST['role']);
            
            if (empty($username) || empty($password)) {
                setFlash('error', 'Nome de usuário e senha são obrigatórios!');
                redirect('/admin/users');
            }
            
            if ($this->userModel->usernameExists($username)) {
                setFlash('error', 'Nome de usuário já existe!');
                redirect('/admin/users');
            }
            
            if (strlen($password) < 6) {
                setFlash('error', 'A senha deve ter pelo menos 6 caracteres!');
                redirect('/admin/users');
            }
            
            if ($this->userModel->create($username, $password, $role)) {
                $this->activityLog->log(
                    $_SESSION['user_id'],
                    'CREATE_USER',
                    "Usuário criado: $username",
                    $_SERVER['REMOTE_ADDR']
                );
                
                setFlash('success', "Usuário $username criado com sucesso!");
            } else {
                setFlash('error', 'Erro ao criar usuário!');
            }
        }
        
        redirect('/admin/users');
    }
    
    /**
     * Editar usuário
     */
    public function editUser($userId) {
        requireAuth();
        
        // Verificar se é admin ou tem permissão de administração
        if ($_SESSION['role'] !== 'admin' && !$this->userPermission->canAccessDepartment($_SESSION['user_id'], 'administracao', 'edit')) {
            setFlash('error', 'Acesso negado à administração');
            redirect('/departments');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->userModel->findById($userId);
            if (!$user) {
                setFlash('error', 'Usuário não encontrado!');
                redirect('/admin/users');
            }
            
            $username = sanitize($_POST['username']);
            $password = $_POST['password'] ?? '';
            $role = sanitize($_POST['role']);
            
            if (empty($username)) {
                setFlash('error', 'Nome de usuário é obrigatório!');
                redirect('/admin/users');
            }
            
            if ($this->userModel->usernameExists($username, $userId)) {
                setFlash('error', 'Nome de usuário já existe!');
                redirect('/admin/users');
            }
            
            $data = [
                'username' => $username,
                'role' => $role
            ];
            
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    setFlash('error', 'A senha deve ter pelo menos 6 caracteres!');
                    redirect('/admin/users');
                }
                $this->userModel->updatePassword($userId, $password);
            }
            
            if ($this->userModel->update($userId, $data)) {
                $this->activityLog->log(
                    $_SESSION['user_id'],
                    'EDIT_USER',
                    "Usuário editado: $username",
                    $_SERVER['REMOTE_ADDR']
                );
                
                setFlash('success', "Usuário $username atualizado com sucesso!");
            } else {
                setFlash('error', 'Erro ao atualizar usuário!');
            }
        }
        
        redirect('/admin/users');
    }
    
    /**
     * Deletar usuário
     */
    public function deleteUser($userId) {
        requireAuth();
        
        // Verificar se é admin ou tem permissão de administração
        if ($_SESSION['role'] !== 'admin' && !$this->userPermission->canAccessDepartment($_SESSION['user_id'], 'administracao', 'edit')) {
            setFlash('error', 'Acesso negado à administração');
            redirect('/departments');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Não permitir deletar próprio usuário
            if ($userId == $_SESSION['user_id']) {
                setFlash('error', 'Você não pode excluir sua própria conta!');
                redirect('/admin/users');
            }
            
            $user = $this->userModel->findById($userId);
            if (!$user) {
                setFlash('error', 'Usuário não encontrado!');
                redirect('/admin/users');
            }
            
            if ($this->userModel->delete($userId)) {
                $this->activityLog->log(
                    $_SESSION['user_id'],
                    'DELETE_USER',
                    "Usuário excluído: {$user['username']}",
                    $_SERVER['REMOTE_ADDR']
                );
                
                setFlash('success', "Usuário {$user['username']} excluído com sucesso!");
            } else {
                setFlash('error', 'Erro ao excluir usuário!');
            }
        }
        
        redirect('/admin/users');
    }
    
    /**
     * Ativar/Desativar usuário
     */
    public function toggleUser($userId) {
        requireAuth();
        
        // Verificar se é admin ou tem permissão de administração
        if ($_SESSION['role'] !== 'admin' && !$this->userPermission->canAccessDepartment($_SESSION['user_id'], 'administracao', 'edit')) {
            setFlash('error', 'Acesso negado à administração');
            redirect('/departments');
            return;
        }
        
        // Não permitir desativar próprio usuário
        if ($userId == $_SESSION['user_id']) {
            setFlash('error', 'Você não pode desativar sua própria conta!');
            redirect('/admin/users');
        }
        
        $user = $this->userModel->findById($userId);
        if (!$user) {
            setFlash('error', 'Usuário não encontrado!');
            redirect('/admin/users');
        }
        
        if ($this->userModel->toggleActive($userId)) {
            $status = $user['is_active'] ? 'desativado' : 'ativado';
            
            $this->activityLog->log(
                $_SESSION['user_id'],
                'TOGGLE_USER',
                "Usuário {$user['username']} $status",
                $_SERVER['REMOTE_ADDR']
            );
            
            setFlash('success', "Usuário {$user['username']} $status com sucesso!");
        } else {
            setFlash('error', 'Erro ao alterar status do usuário!');
        }
        
        redirect('/admin/users');
    }
    
    /**
     * Gerenciar permissões de departamentos do usuário
     */
    public function managePermissions($userId) {
        requireAuth();
        
        // Verificar se é admin ou tem permissão de administração
        if ($_SESSION['role'] !== 'admin' && !$this->userPermission->canAccessDepartment($_SESSION['user_id'], 'administracao', 'edit')) {
            setFlash('error', 'Acesso negado à administração');
            redirect('/departments');
            return;
        }
        
        $user = $this->userModel->findById($userId);
        if (!$user) {
            setFlash('error', 'Usuário não encontrado!');
            redirect('/admin/users');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $permissions = $_POST['permissions'] ?? [];
            
            // Processar permissões para formato correto
            $processedPermissions = [];
            foreach ($permissions as $dept => $perms) {
                $processedPermissions[$dept] = [
                    'can_view' => isset($perms['can_view']) && $perms['can_view'] === '1',
                    'can_edit' => isset($perms['can_edit']) && $perms['can_edit'] === '1'
                ];
            }
            
            if ($this->userPermission->setUserPermissions($userId, $processedPermissions)) {
                $this->activityLog->log(
                    $_SESSION['user_id'],
                    'UPDATE_PERMISSIONS',
                    "Permissões atualizadas para usuário: {$user['username']}",
                    $_SERVER['REMOTE_ADDR']
                );
                
                setFlash('success', 'Permissões atualizadas com sucesso!');
            } else {
                setFlash('error', 'Erro ao atualizar permissões!');
            }
            
            redirect("/admin/manage-permissions/$userId");
        }
        
        $departments = $this->userPermission->getAvailableDepartments();
        $userPermissions = $this->userPermission->getUserPermissions($userId);
        
        $pageTitle = 'Gerenciar Permissões - Sistema Aguaboa';
        $flashMessages = getFlashMessages();
        
        include '../src/views/admin/manage_permissions.php';
    }
}
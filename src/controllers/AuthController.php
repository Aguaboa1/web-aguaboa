<?php
/**
 * Controller de Autenticação
 * Web Aguaboa - Gestão Comercial
 */

class AuthController {
    private $userModel;
    private $activityLog;
    
    public function __construct() {
        $this->userModel = new User();
        $this->activityLog = new ActivityLog();
    }
    
    /**
     * Página de login
     */
    public function login() {
        // Se já está logado, redirecionar
        if (isset($_SESSION['user_id'])) {
            redirect('/departments');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                setFlash('error', 'Por favor, preencha usuário e senha.');
                $this->showLoginPage();
                return;
            }
            
            $user = $this->userModel->findByUsername($username);
            
            if ($user && $user['is_active'] && $this->userModel->verifyPassword($password, $user['password_hash'])) {
                // Login bem-sucedido
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Atualizar último login
                $this->userModel->updateLastLogin($user['id']);
                
                // Log da atividade
                $this->activityLog->log(
                    $user['id'], 
                    'LOGIN', 
                    'Login realizado com sucesso',
                    $_SERVER['REMOTE_ADDR']
                );
                
                redirect('/departments');
            } else {
                // Login falhou
                if ($user && !$user['is_active']) {
                    setFlash('error', 'Usuário desativado. Entre em contato com o administrador.');
                } else {
                    setFlash('error', 'Usuário ou senha incorretos.');
                }
                
                // Log da tentativa de login
                if ($user) {
                    $this->activityLog->log(
                        $user['id'], 
                        'LOGIN_FAILED', 
                        'Tentativa de login com senha incorreta',
                        $_SERVER['REMOTE_ADDR']
                    );
                }
            }
        }
        
        $this->showLoginPage();
    }
    
    /**
     * Logout
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            // Log da atividade
            $this->activityLog->log(
                $_SESSION['user_id'], 
                'LOGOUT', 
                'Logout realizado',
                $_SERVER['REMOTE_ADDR']
            );
        }
        
        // Destruir sessão
        session_destroy();
        redirect('/auth/login');
    }
    
    /**
     * Alterar senha
     */
    public function changePassword() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            $user = $this->userModel->findById($_SESSION['user_id']);
            
            if (!$this->userModel->verifyPassword($currentPassword, $user['password_hash'])) {
                setFlash('error', 'Senha atual incorreta.');
            } elseif (strlen($newPassword) < 6) {
                setFlash('error', 'A nova senha deve ter pelo menos 6 caracteres.');
            } elseif ($newPassword !== $confirmPassword) {
                setFlash('error', 'As senhas não coincidem.');
            } else {
                // Atualizar senha
                if ($this->userModel->updatePassword($_SESSION['user_id'], $newPassword)) {
                    $this->activityLog->log(
                        $_SESSION['user_id'], 
                        'PASSWORD_CHANGE', 
                        'Senha alterada pelo usuário',
                        $_SERVER['REMOTE_ADDR']
                    );
                    
                    setFlash('success', 'Senha alterada com sucesso!');
                    redirect('/crm');
                } else {
                    setFlash('error', 'Erro ao alterar senha. Tente novamente.');
                }
            }
        }
        
        $this->showChangePasswordPage();
    }
    
    /**
     * Exibir página de login
     */
    private function showLoginPage() {
        $pageTitle = 'Login - Web Aguaboa';
        $flashMessages = getFlashMessages();
        
        include '../src/views/auth/login.php';
    }
    
    /**
     * Exibir página de alteração de senha
     */
    private function showChangePasswordPage() {
        $pageTitle = 'Alterar Senha - Sistema Aguaboa';
        $flashMessages = getFlashMessages();
        $user = $this->userModel->findById($_SESSION['user_id']);
        
        include '../src/views/auth/change_password.php';
    }
}
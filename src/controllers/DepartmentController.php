<?php
/**
 * Controller de Departamentos
 * Sistema Web Aguaboa - Gestão Empresarial
 */

class DepartmentController {
    private $userPermission;
    
    public function __construct() {
        $this->userPermission = new UserPermission();
    }
    
    /**
     * Verificar se usuário tem acesso ao departamento
     */
    private function checkDepartmentAccess($department, $action = 'view') {
        if (!$this->userPermission->canAccessDepartment($_SESSION['user_id'], $department, $action)) {
            setFlash('error', 'Você não tem permissão para acessar este departamento.');
            redirect('/departments');
        }
    }
    
    /**
     * Página de seleção de departamentos
     */
    public function select() {
        requireAuth();
        
        // Obter apenas departamentos que o usuário pode acessar
        $accessibleDepartments = $this->userPermission->getUserAccessibleDepartments($_SESSION['user_id']);
        
        $pageTitle = 'Selecionar Departamento - Web Aguaboa';
        $flashMessages = getFlashMessages();
        
        include '../src/views/departments/select.php';
    }
    
    /**
     * Página de Gestão Financeira
     */
    public function financeiro() {
        requireAuth();
        $this->checkDepartmentAccess('financeiro');
        
        $pageTitle = 'Gestão Financeira - Web Aguaboa';
        $flashMessages = getFlashMessages();
        
        include '../src/views/departments/financeiro.php';
    }
    
    /**
     * Página de Recursos Humanos
     */
    public function rh() {
        requireAuth();
        $this->checkDepartmentAccess('rh');
        
        $pageTitle = 'Recursos Humanos - Web Aguaboa';
        $flashMessages = getFlashMessages();
        
        include '../src/views/departments/rh.php';
    }
    
    /**
     * Página de Gestão de Qualidade
     */
    public function qualidade() {
        requireAuth();
        $this->checkDepartmentAccess('qualidade');
        
        $pageTitle = 'Gestão de Qualidade - Web Aguaboa';
        $flashMessages = getFlashMessages();
        
        include '../src/views/departments/qualidade.php';
    }
    
    /**
     * Página de Gestão de Atendimento
     */
    public function atendimento() {
        requireAuth();
        $this->checkDepartmentAccess('atendimento');
        
        $pageTitle = 'Gestão de Atendimento - Web Aguaboa';
        $flashMessages = getFlashMessages();
        
        include '../src/views/departments/atendimento.php';
    }
    
    /**
     * Página de Gestão de Produção
     */
    public function producao() {
        requireAuth();
        $this->checkDepartmentAccess('producao');
        
        $pageTitle = 'Gestão de Produção - Web Aguaboa';
        $flashMessages = getFlashMessages();
        
        include '../src/views/departments/producao.php';
    }
    
    /**
     * Página de Insumos de Produção
     */
    public function producaoInsumos() {
        requireAuth();
        $this->checkDepartmentAccess('producao');
        
        $pageTitle = 'Insumos de Produção - Web Aguaboa';
        $flashMessages = getFlashMessages();
        
        include '../src/views/departments/producao_insumos.php';
    }
    
    /**
     * Página de Lançamentos de Produção
     */
    public function producaoLancamentos() {
        requireAuth();
        $this->checkDepartmentAccess('producao');
        
        $pageTitle = 'Lançamentos de Produção - Web Aguaboa';
        $flashMessages = getFlashMessages();
        
        include '../src/views/departments/producao_lancamentos.php';
    }
    
    /**
     * Página de Metas de Produção
     */
    public function producaoMetas() {
        requireAuth();
        $this->checkDepartmentAccess('producao');
        
        $pageTitle = 'Metas de Produção - Web Aguaboa';
        $flashMessages = getFlashMessages();
        
        include '../src/views/departments/producao_metas.php';
    }
}
?>
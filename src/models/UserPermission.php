<?php
/**
 * Modelo de Permissões de Departamentos
 * Sistema Web Aguaboa - Gestão Empresarial
 */

class UserPermission {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obter permissões de um usuário
     */
    public function getUserPermissions($userId) {
        $stmt = $this->db->prepare("
            SELECT department, can_view, can_edit 
            FROM user_department_permissions 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        
        $permissions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $permissions[$row['department']] = [
                'can_view' => (bool)$row['can_view'],
                'can_edit' => (bool)$row['can_edit']
            ];
        }
        
        return $permissions;
    }
    
    /**
     * Verificar se usuário pode acessar departamento
     */
    public function canAccessDepartment($userId, $department, $action = 'view') {
        $stmt = $this->db->prepare("
            SELECT can_view, can_edit 
            FROM user_department_permissions 
            WHERE user_id = ? AND department = ?
        ");
        $stmt->execute([$userId, $department]);
        $permission = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$permission) {
            return false;
        }
        
        if ($action === 'edit') {
            return (bool)$permission['can_edit'];
        }
        
        return (bool)$permission['can_view'];
    }
    
    /**
     * Definir permissões de um usuário
     */
    public function setUserPermissions($userId, $permissions) {
        $this->db->beginTransaction();
        
        try {
            // Remover permissões existentes
            $stmt = $this->db->prepare("DELETE FROM user_department_permissions WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Inserir novas permissões
            $stmt = $this->db->prepare("
                INSERT INTO user_department_permissions (user_id, department, can_view, can_edit) 
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($permissions as $department => $perms) {
                $stmt->execute([
                    $userId,
                    $department,
                    $perms['can_view'] ?? false,
                    $perms['can_edit'] ?? false
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Obter todos os departamentos disponíveis
     */
    public function getAvailableDepartments() {
        return [
            'comercial' => [
                'name' => 'Gestão Comercial',
                'icon' => '👥',
                'description' => 'CRM, vendas e relacionamento com clientes'
            ],
            'financeiro' => [
                'name' => 'Gestão Financeira',
                'icon' => '💰',
                'description' => 'Contas, fluxo de caixa e relatórios financeiros'
            ],
            'rh' => [
                'name' => 'Recursos Humanos',
                'icon' => '👨‍💼',
                'description' => 'Funcionários, folha e benefícios'
            ],
            'qualidade' => [
                'name' => 'Gestão de Qualidade',
                'icon' => '⭐',
                'description' => 'Análises laboratoriais e certificações'
            ],
            'atendimento' => [
                'name' => 'Gestão de Atendimento',
                'icon' => '📞',
                'description' => 'SAC, suporte e atendimento ao cliente'
            ],
            'producao' => [
                'name' => 'Gestão de Produção',
                'icon' => '🏭',
                'description' => 'Produção, envase e logística'
            ],
            'administracao' => [
                'name' => 'Administração do Sistema',
                'icon' => '⚙️',
                'description' => 'Usuários, logs, configurações e manutenção'
            ]
        ];
    }
    
    /**
     * Obter departamentos que o usuário pode visualizar
     */
    public function getUserAccessibleDepartments($userId) {
        $permissions = $this->getUserPermissions($userId);
        $departments = $this->getAvailableDepartments();
        $accessible = [];
        
        foreach ($departments as $key => $dept) {
            if (isset($permissions[$key]) && $permissions[$key]['can_view']) {
                $accessible[$key] = array_merge($dept, $permissions[$key]);
            }
        }
        
        return $accessible;
    }
}
?>
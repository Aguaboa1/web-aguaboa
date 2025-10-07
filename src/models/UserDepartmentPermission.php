<?php
/**
 * Model para Permissões de Departamentos
 * Sistema Web Aguaboa - Gestão Empresarial
 */

class UserDepartmentPermission {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obter permissões de um usuário
     */
    public function getUserPermissions($userId) {
        $sql = "SELECT department, can_view, can_edit 
                FROM user_department_permissions 
                WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
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
     * Verificar se usuário pode visualizar departamento
     */
    public function canView($userId, $department) {
        $sql = "SELECT can_view FROM user_department_permissions 
                WHERE user_id = :user_id AND department = :department";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':department', $department);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (bool)$result['can_view'] : false;
    }
    
    /**
     * Verificar se usuário pode editar no departamento
     */
    public function canEdit($userId, $department) {
        $sql = "SELECT can_edit FROM user_department_permissions 
                WHERE user_id = :user_id AND department = :department";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':department', $department);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (bool)$result['can_edit'] : false;
    }
    
    /**
     * Atualizar permissões de um usuário
     */
    public function updateUserPermissions($userId, $permissions) {
        $this->db->beginTransaction();
        
        try {
            // Primeiro, remover todas as permissões existentes
            $sql = "DELETE FROM user_department_permissions WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Inserir novas permissões
            $sql = "INSERT INTO user_department_permissions (user_id, department, can_view, can_edit) 
                    VALUES (:user_id, :department, :can_view, :can_edit)";
            $stmt = $this->db->prepare($sql);
            
            foreach ($permissions as $department => $perms) {
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':department', $department);
                $stmt->bindParam(':can_view', $perms['can_view'], PDO::PARAM_BOOL);
                $stmt->bindParam(':can_edit', $perms['can_edit'], PDO::PARAM_BOOL);
                $stmt->execute();
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Erro ao atualizar permissões: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obter todos os departamentos disponíveis
     */
    public function getAllDepartments() {
        return [
            'comercial' => 'Gestão Comercial',
            'financeiro' => 'Gestão Financeira',
            'rh' => 'Recursos Humanos',
            'qualidade' => 'Gestão de Qualidade',
            'atendimento' => 'Gestão de Atendimento',
            'producao' => 'Gestão de Produção'
        ];
    }
    
    /**
     * Obter departamentos que o usuário pode visualizar
     */
    public function getUserAccessibleDepartments($userId) {
        $sql = "SELECT department, can_view, can_edit 
                FROM user_department_permissions 
                WHERE user_id = :user_id AND can_view = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $departments = [];
        $allDepartments = $this->getAllDepartments();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $departments[$row['department']] = [
                'name' => $allDepartments[$row['department']] ?? $row['department'],
                'can_edit' => (bool)$row['can_edit']
            ];
        }
        
        return $departments;
    }
}
?>
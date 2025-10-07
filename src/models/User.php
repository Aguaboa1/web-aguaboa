<?php
/**
 * Model User
 * Web Aguaboa - Gestão Comercial
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Criar novo usuário
     */
    public function create($username, $password, $role = 'equipe', $email = null) {
        $sql = "INSERT INTO users (username, password_hash, password_plain, role, email) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        return $stmt->execute([$username, $passwordHash, $password, $role, $email]);
    }
    
    /**
     * Buscar usuário por username
     */
    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        
        return $stmt->fetch();
    }
    
    /**
     * Buscar usuário por ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }
    
    /**
     * Buscar todos os usuários
     */
    public function findAll() {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Verificar senha
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Atualizar último login
     */
    public function updateLastLogin($id) {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$id]);
    }
    
    /**
     * Atualizar senha
     */
    public function updatePassword($id, $newPassword) {
        $sql = "UPDATE users SET password_hash = ?, password_plain = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        return $stmt->execute([$passwordHash, $newPassword, $id]);
    }
    
    /**
     * Atualizar usuário
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $fields[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($values);
    }
    
    /**
     * Deletar usuário
     */
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$id]);
    }
    
    /**
     * Ativar/Desativar usuário
     */
    public function toggleActive($id) {
        $sql = "UPDATE users SET is_active = NOT is_active WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$id]);
    }
    
    /**
     * Verificar se username já existe
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM users WHERE username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }
}
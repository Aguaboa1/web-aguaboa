<?php
/**
 * Model ActivityLog
 * Web Aguaboa - Gestão Comercial
 */

class ActivityLog {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Registrar atividade
     */
    public function log($userId, $action, $description = null, $ipAddress = null, $extraData = null) {
        $sql = "INSERT INTO activity_log (user_id, action, description, ip_address, extra_data) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $extraDataJson = $extraData ? json_encode($extraData) : null;
        
        return $stmt->execute([
            $userId,
            $action,
            $description ?: $action,
            $ipAddress,
            $extraDataJson
        ]);
    }
    
    /**
     * Buscar logs com informações do usuário
     */
    public function getRecentLogs($limit = 100) {
        $sql = "SELECT al.*, u.username, u.role 
                FROM activity_log al 
                LEFT JOIN users u ON al.user_id = u.id 
                ORDER BY al.timestamp DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar logs por usuário
     */
    public function getLogsByUser($userId, $limit = 50) {
        $sql = "SELECT * FROM activity_log WHERE user_id = ? ORDER BY timestamp DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar logs por ação
     */
    public function getLogsByAction($action, $limit = 50) {
        $sql = "SELECT al.*, u.username 
                FROM activity_log al 
                LEFT JOIN users u ON al.user_id = u.id 
                WHERE al.action = ? 
                ORDER BY al.timestamp DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $action, PDO::PARAM_STR);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Limpar logs antigos
     */
    public function cleanOldLogs($days = 90) {
        $sql = "DELETE FROM activity_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$days]);
    }
    
    /**
     * Contar total de logs
     */
    public function countLogs() {
        $sql = "SELECT COUNT(*) FROM activity_log";
        $stmt = $this->db->query($sql);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Estatísticas de atividades
     */
    public function getActivityStats($days = 30) {
        $sql = "SELECT 
                    action,
                    COUNT(*) as count,
                    DATE(timestamp) as date
                FROM activity_log 
                WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY action, DATE(timestamp)
                ORDER BY date DESC, count DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days]);
        
        return $stmt->fetchAll();
    }
}
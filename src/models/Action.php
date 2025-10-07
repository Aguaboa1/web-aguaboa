<?php
/**
 * Model Action
 * Web Aguaboa - Gestão Comercial
 */

class Action {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Criar nova ação
     */
    public function create($clientId, $descricao, $dataAcao, $arquivo = null, $prazoConlusao = null) {
        $sql = "INSERT INTO actions (client_id, descricao, data_acao, arquivo, prazo_conclusao) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$clientId, $descricao, $dataAcao, $arquivo, $prazoConlusao]);
    }
    
    /**
     * Buscar ação por ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM actions WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }
    
    /**
     * Atualizar ação
     */
    public function update($id, $descricao, $dataAcao, $arquivo = null, $prazoConlusao = null) {
        if ($arquivo) {
            $sql = "UPDATE actions SET descricao = ?, data_acao = ?, arquivo = ?, prazo_conclusao = ? WHERE id = ?";
            $params = [$descricao, $dataAcao, $arquivo, $prazoConlusao, $id];
        } else {
            $sql = "UPDATE actions SET descricao = ?, data_acao = ?, prazo_conclusao = ? WHERE id = ?";
            $params = [$descricao, $dataAcao, $prazoConlusao, $id];
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Deletar ação
     */
    public function delete($id) {
        // Primeiro, buscar o arquivo para deletar fisicamente
        $action = $this->findById($id);
        if ($action && $action['arquivo']) {
            $filepath = UPLOAD_DIR . 'actions/' . $action['arquivo'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        
        $sql = "DELETE FROM actions WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$id]);
    }
    
    /**
     * Buscar ações por cliente
     */
    public function findByClient($clientId) {
        $sql = "SELECT * FROM actions WHERE client_id = ? ORDER BY data_acao DESC, created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clientId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Contar ações por cliente
     */
    public function countByClient($clientId) {
        $sql = "SELECT COUNT(*) FROM actions WHERE client_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clientId]);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Buscar ações recentes
     */
    public function getRecentActions($limit = 50) {
        $sql = "SELECT a.*, c.cliente, c.empresa 
                FROM actions a 
                LEFT JOIN clients c ON a.client_id = c.id 
                ORDER BY a.created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar ações vigentes (com prazo de conclusão)
     */
    public function findAcoesVigentes() {
        $sql = "SELECT a.*, c.cliente, c.empresa, c.cidade 
                FROM actions a 
                LEFT JOIN clients c ON a.client_id = c.id 
                WHERE a.prazo_conclusao IS NOT NULL 
                ORDER BY a.prazo_conclusao ASC, a.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Estatísticas das ações vigentes
     */
    public function getStatsAcoesVigentes() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN prazo_conclusao < CURDATE() THEN 1 END) as vencidas,
                    COUNT(CASE WHEN prazo_conclusao >= CURDATE() AND prazo_conclusao <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as proximas,
                    COUNT(CASE WHEN prazo_conclusao > DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as futuras
                FROM actions 
                WHERE prazo_conclusao IS NOT NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}
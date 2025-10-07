<?php
/**
 * Model UploadHistory
 * Web Aguaboa - Gestão Comercial
 */

class UploadHistory {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Criar novo registro de upload
     */
    public function create($nomeArquivo, $usuarioId, $totalRegistros = null) {
        $sql = "INSERT INTO upload_history (nome_arquivo, usuario_id, total_registros, status) VALUES (?, ?, ?, 'processando')";
        $stmt = $this->db->prepare($sql);
        
        $stmt->execute([$nomeArquivo, $usuarioId, $totalRegistros]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualizar status do upload
     */
    public function updateStatus($id, $status, $registrosProcessados = null, $mensagemErro = null) {
        $sql = "UPDATE upload_history SET status = ?, registros_processados = ?, mensagem_erro = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$status, $registrosProcessados, $mensagemErro, $id]);
    }
    
    /**
     * Buscar histórico recente
     */
    public function getRecent($limit = 20) {
        $sql = "SELECT uh.*, u.username 
                FROM upload_history uh 
                LEFT JOIN users u ON uh.usuario_id = u.id 
                ORDER BY uh.data_upload DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
<?php
/**
 * Model Envase
 * Web Aguaboa - Gestão Comercial
 */

class Envase {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Criar nova entrada de envase
     */
    public function create($data) {
        $sql = "INSERT INTO envase_data (empresa, cidade, produto, ano, mes, dia, quantidade, arquivo_origem) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $data['empresa'],
            $data['cidade'] ?? null,
            $data['produto'],
            $data['ano'],
            $data['mes'],
            $data['dia'],
            $data['quantidade'],
            $data['arquivo_origem'] ?? null
        ]);
    }
    
    /**
     * Buscar dados de envase por empresa
     */
    public function findByEmpresa($empresa) {
        $sql = "SELECT * FROM envase_data WHERE LOWER(empresa) = LOWER(?) ORDER BY ano DESC, mes DESC, dia DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresa]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar dados de envase por cliente ID
     */
    public function findByClient($clientId, $limit = null) {
        try {
            // Primeiro buscar o cliente para pegar a empresa
            $sqlClient = "SELECT empresa FROM clients WHERE id = ?";
            $stmtClient = $this->db->prepare($sqlClient);
            $stmtClient->execute([$clientId]);
            $client = $stmtClient->fetch(PDO::FETCH_ASSOC);
            
            if (!$client || !$client['empresa']) {
                return [];
            }
            
            // Buscar dados de envase da empresa
            $sql = "SELECT * FROM envase_data WHERE LOWER(empresa) = LOWER(?) ORDER BY ano DESC, mes DESC, dia DESC";
            if ($limit) {
                $sql .= " LIMIT " . intval($limit);
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$client['empresa']]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erro ao buscar dados de envase por cliente: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Estatísticas gerais de envase
     */
    public function getStats() {
        $stats = [];
        
        // Total de registros
        $stmt = $this->db->query("SELECT COUNT(*) FROM envase_data");
        $stats['total_registros'] = $stmt->fetchColumn();
        
        // Empresas únicas
        $stmt = $this->db->query("SELECT COUNT(DISTINCT empresa) FROM envase_data");
        $stats['empresas_unicas'] = $stmt->fetchColumn();
        
        // Produtos únicos
        $stmt = $this->db->query("SELECT COUNT(DISTINCT produto) FROM envase_data");
        $stats['produtos_unicos'] = $stmt->fetchColumn();
        
        // Anos disponíveis
        $stmt = $this->db->query("SELECT DISTINCT ano FROM envase_data ORDER BY ano DESC");
        $stats['anos_disponiveis'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Total quantidade
        $stmt = $this->db->query("SELECT SUM(quantidade) FROM envase_data");
        $stats['total_quantidade'] = $stmt->fetchColumn() ?: 0;
        
        return $stats;
    }
    
    /**
     * Resumo por mês para uma empresa
     */
    public function getSummaryByMonth($empresa) {
        $sql = "SELECT ano, mes, SUM(quantidade) as quantidade 
                FROM envase_data 
                WHERE LOWER(empresa) = LOWER(?) 
                GROUP BY ano, mes 
                ORDER BY ano DESC, mes DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresa]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar por ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM envase_data WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }
    
    /**
     * Atualizar registro
     */
    public function update($id, $data) {
        $sql = "UPDATE envase_data SET 
                empresa = ?, cidade = ?, produto = ?, ano = ?, mes = ?, dia = ?, quantidade = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $data['empresa'],
            $data['cidade'] ?? null,
            $data['produto'],
            $data['ano'],
            $data['mes'],
            $data['dia'],
            $data['quantidade'],
            $id
        ]);
    }
    
    /**
     * Deletar registro
     */
    public function delete($id) {
        $sql = "DELETE FROM envase_data WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$id]);
    }
    
    /**
     * Verificar se registro já existe
     */
    public function recordExists($empresa, $produto, $ano, $mes, $dia) {
        $sql = "SELECT id FROM envase_data WHERE empresa = ? AND produto = ? AND ano = ? AND mes = ? AND dia = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresa, $produto, $ano, $mes, $dia]);
        
        return $stmt->fetch();
    }
    
    /**
     * Atualizar ou inserir (upsert)
     */
    public function upsert($data) {
        $existing = $this->recordExists(
            $data['empresa'],
            $data['produto'],
            $data['ano'],
            $data['mes'],
            $data['dia']
        );
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->create($data);
        }
    }
    
    /**
     * Buscar anos disponíveis
     */
    public function getAnosDisponiveis() {
        $stmt = $this->db->query("SELECT DISTINCT ano FROM envase_data ORDER BY ano DESC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Resumo geral das estatísticas
     */
    public function getResumoGeral($ano = null) {
        $where = $ano ? "WHERE ano = :ano" : "";
        
        $sql = "SELECT 
                    COUNT(*) as total_registros,
                    SUM(quantidade) as total_quantidade,
                    COUNT(DISTINCT empresa) as empresas_ativas,
                    COUNT(DISTINCT produto) as produtos_diferentes
                FROM envase_data $where";
        
        $stmt = $this->db->prepare($sql);
        if ($ano) {
            $stmt->bindParam(':ano', $ano, PDO::PARAM_INT);
        }
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Dados agrupados por mês
     */
    public function getDadosPorMes($ano = null) {
        $where = $ano ? "WHERE ano = :ano" : "";
        
        $sql = "SELECT 
                    mes,
                    SUM(quantidade) as total
                FROM envase_data 
                $where
                GROUP BY mes 
                ORDER BY mes";
        
        $stmt = $this->db->prepare($sql);
        if ($ano) {
            $stmt->bindParam(':ano', $ano, PDO::PARAM_INT);
        }
        $stmt->execute();
        
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Converter número do mês para nome
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        
        foreach ($dados as &$item) {
            $item['label'] = $meses[$item['mes']] ?? 'Mês ' . $item['mes'];
        }
        
        return $dados;
    }
    
    /**
     * Dados agrupados por produto
     */
    public function getDadosPorProduto($ano = null) {
        $where = $ano ? "WHERE ano = :ano" : "";
        
        $sql = "SELECT 
                    produto as label,
                    SUM(quantidade) as total
                FROM envase_data 
                $where
                GROUP BY produto 
                ORDER BY total DESC
                LIMIT 20";
        
        $stmt = $this->db->prepare($sql);
        if ($ano) {
            $stmt->bindParam(':ano', $ano, PDO::PARAM_INT);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Dados agrupados por empresa
     */
    public function getDadosPorEmpresa($ano = null) {
        $where = $ano ? "WHERE ano = :ano" : "";
        
        $sql = "SELECT 
                    empresa as label,
                    SUM(quantidade) as total
                FROM envase_data 
                $where
                GROUP BY empresa 
                ORDER BY total DESC
                LIMIT 20";
        
        $stmt = $this->db->prepare($sql);
        if ($ano) {
            $stmt->bindParam(':ano', $ano, PDO::PARAM_INT);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Excluir todos os dados de envase
     */
    public function deleteAll() {
        try {
            // Contar registros antes de excluir
            $sqlCount = "SELECT COUNT(*) as total FROM envase_data";
            $stmtCount = $this->db->prepare($sqlCount);
            $stmtCount->execute();
            $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
            
            if ($total === 0) {
                return 0;
            }
            
            // Excluir todos os registros
            $sql = "DELETE FROM envase_data";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            // Resetar auto_increment (opcional)
            $sqlReset = "ALTER TABLE envase_data AUTO_INCREMENT = 1";
            $stmtReset = $this->db->prepare($sqlReset);
            $stmtReset->execute();
            
            return $total;
            
        } catch (Exception $e) {
            error_log("Erro ao excluir todos os dados de envase: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Estatísticas de envase por cliente específico
     */
    public function getStatsByClient($clientId) {
        try {
            // Buscar primeiro o cliente para pegar empresa
            $sqlClient = "SELECT * FROM clients WHERE id = ?";
            $stmtClient = $this->db->prepare($sqlClient);
            $stmtClient->execute([$clientId]);
            $client = $stmtClient->fetch(PDO::FETCH_ASSOC);
            
            if (!$client) {
                throw new Exception("Cliente não encontrado");
            }
            
            $empresa = $client['empresa'];
            
            $stats = [];
            
            // Total de registros para este cliente
            $sql = "SELECT COUNT(*) as total FROM envase_data WHERE LOWER(empresa) = LOWER(?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$empresa]);
            $stats['total_registros'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Total de quantidade
            $sql = "SELECT COALESCE(SUM(quantidade), 0) as total FROM envase_data WHERE LOWER(empresa) = LOWER(?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$empresa]);
            $stats['total_quantidade'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Produtos diferentes
            $sql = "SELECT COUNT(DISTINCT produto) as total FROM envase_data WHERE LOWER(empresa) = LOWER(?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$empresa]);
            $stats['produtos_diferentes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Anos de dados
            $sql = "SELECT COUNT(DISTINCT ano) as total FROM envase_data WHERE LOWER(empresa) = LOWER(?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$empresa]);
            $stats['anos_dados'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar estatísticas do cliente: " . $e->getMessage());
            return [
                'total_registros' => 0,
                'total_quantidade' => 0,
                'produtos_diferentes' => 0,
                'anos_dados' => 0
            ];
        }
    }
}
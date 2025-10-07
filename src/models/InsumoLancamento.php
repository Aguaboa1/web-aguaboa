<?php
/**
 * Modelo para lançamentos de insumos que dão baixa no estoque
 */
class InsumoLancamento {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Criar lançamento de insumo e dar baixa no estoque
     */
    public function criarLancamento($data) {
        try {
            $this->db->beginTransaction();
            // 1. Registrar lançamento na tabela producao_lancamentos
            $stmt = $this->db->prepare("
                INSERT INTO producao_lancamentos 
                (produto_id, data_producao, quantidade_produzida, quantidade_perdida, motivo_perda, observacoes, turno, operador_id, supervisor_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['produto_id'],
                $data['data_lancamento'],
                $data['quantidade_utilizada'],
                0,
                $data['motivo'] ?? '',
                $data['observacoes'] ?? '',
                $data['turno'] ?? 'MANHÃ',
                $_SESSION['user_id'],
                null
            ]);
            $lancamentoId = $this->db->lastInsertId();

            // 2. Dar baixa no estoque para todos os produtos
            require_once 'EstoqueInsumos.php';
            $estoqueModel = new EstoqueInsumos();
            $movimentacaoData = [
                'item_id' => $data['stock_item_id'],
                'type' => 'saida',
                'quantity' => $data['quantidade_utilizada'],
                'reason' => $data['motivo'] ?? 'Uso na Produção',
                'notes' => $data['observacoes'] ?? "Lançamento de insumo - ID #{$lancamentoId}",
                'reference_document' => "INSUMO-{$lancamentoId}"
            ];
            // Se o produto não existir no estoque, lançar erro claro
            try {
                $estoqueModel->addMovimentacao($movimentacaoData);
            } catch (Exception $e) {
                throw new Exception('Erro ao dar baixa no estoque: ' . $e->getMessage());
            }

            // 3. Registrar na tabela de consumo de produção
            $stmt = $this->db->prepare("
                INSERT INTO production_stock_consumption 
                (lancamento_id, stock_item_id, quantity_consumed, unit, cost_per_unit, total_cost) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            // Buscar custo do item
            $stmt_cost = $this->db->prepare("SELECT unit_cost, unit FROM stock_items WHERE id = ?");
            $stmt_cost->execute([$data['stock_item_id']]);
            $stockItem = $stmt_cost->fetch(PDO::FETCH_ASSOC);
            $unitCost = $stockItem['unit_cost'] ?? 0;
            $totalCost = $data['quantidade_utilizada'] * $unitCost;
            $stmt->execute([
                $lancamentoId,
                $data['stock_item_id'],
                $data['quantidade_utilizada'],
                $stockItem['unit'] ?? 'UN',
                $unitCost,
                $totalCost
            ]);
            $this->db->commit();
            return $lancamentoId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Buscar lançamentos de insumos
     */
    public function getLancamentos($limit = 50) {
        $stmt = $this->db->prepare("
            SELECT 
                pl.*,
                si.name as insumo_name,
                si.unit,
                u.username as operador_name,
                psc.cost_per_unit,
                psc.total_cost
            FROM producao_lancamentos pl
            LEFT JOIN migration_mapping mm ON pl.produto_id = mm.old_product_id
            LEFT JOIN stock_items si ON mm.new_stock_id = si.id
            LEFT JOIN users u ON pl.operador_id = u.id
            LEFT JOIN production_stock_consumption psc ON pl.id = psc.lancamento_id
            WHERE si.id IS NOT NULL
            ORDER BY pl.data_producao DESC, pl.created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Estatísticas de lançamentos
     */
    public function getEstatisticas() {
        $stats = [];
        
        // Lançamentos hoje
        $stmt = $this->db->query("
            SELECT COUNT(*) as total 
            FROM producao_lancamentos pl
            LEFT JOIN migration_mapping mm ON pl.produto_id = mm.old_product_id
            WHERE mm.new_stock_id IS NOT NULL 
            AND DATE(pl.data_producao) = CURDATE()
        ");
        $stats['lancamentos_hoje'] = $stmt->fetchColumn();
        
        // Quantidade total consumida hoje
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(pl.quantidade_produzida), 0) as total
            FROM producao_lancamentos pl
            LEFT JOIN migration_mapping mm ON pl.produto_id = mm.old_product_id
            WHERE mm.new_stock_id IS NOT NULL 
            AND DATE(pl.data_producao) = CURDATE()
        ");
        $stats['quantidade_hoje'] = $stmt->fetchColumn();
        
        // Custo total dos insumos consumidos hoje
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(psc.total_cost), 0) as total
            FROM production_stock_consumption psc
            JOIN producao_lancamentos pl ON psc.lancamento_id = pl.id
            WHERE DATE(pl.data_producao) = CURDATE()
        ");
        $stats['custo_hoje'] = $stmt->fetchColumn();
        
        return $stats;
    }
    
    /**
     * Verificar se é possível fazer lançamento
     */
    public function podeUtilizar($stockItemId, $quantidade) {
        $stmt = $this->db->prepare("SELECT current_quantity FROM stock_items WHERE id = ?");
        $stmt->execute([$stockItemId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$item) {
            return false;
        }
        
        return $item['current_quantity'] >= $quantidade;
    }
}
?>
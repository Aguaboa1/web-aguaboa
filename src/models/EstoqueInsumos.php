<?php
/**
 * Modelo para controle de estoque de insumos
 */
class EstoqueInsumos {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Listar todos os itens do estoque
     */
    public function getAllItens() {
        $stmt = $this->db->query("
            SELECT 
                si.*,
                CASE 
                    WHEN si.current_quantity <= si.minimum_stock THEN 'baixo'
                    WHEN si.current_quantity >= si.maximum_stock THEN 'alto'
                    ELSE 'normal'
                END as status_estoque,
                COUNT(sm.id) as total_movimentacoes
            FROM stock_items si
            LEFT JOIN stock_movements sm ON si.id = sm.item_id
            GROUP BY si.id
            ORDER BY si.name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar item por ID
     */
    public function getItemById($id) {
        $stmt = $this->db->prepare("SELECT * FROM stock_items WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criar novo item de estoque
     */
    public function createItem($data) {
        $stmt = $this->db->prepare("
            INSERT INTO stock_items (
                name, description, category, unit, unit_cost, 
                current_quantity, minimum_stock, maximum_stock, location
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? '',
            $data['category'] ?? 'Insumo',
            $data['unit'] ?? 'UN',
            $data['unit_cost'] ?? 0,
            $data['current_quantity'] ?? 0,
            $data['minimum_stock'] ?? 0,
            $data['maximum_stock'] ?? 0,
            $data['location'] ?? ''
        ]);
    }
    
    /**
     * Atualizar item de estoque
     */
    public function updateItem($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE stock_items SET 
                name = ?, description = ?, category = ?, unit = ?, 
                unit_cost = ?, current_quantity = ?, location = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['category'],
            $data['unit'],
            $data['unit_cost'],
            $data['current_quantity'],
            $data['location'],
            $id
        ]);
    }
    
    /**
     * Registrar movimentação de estoque
     */
    public function addMovimentacao($data) {
        try {
            $internalTransaction = false;
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
                $internalTransaction = true;
            }
            
            // Buscar quantidade atual
            $stmt = $this->db->prepare("SELECT current_quantity FROM stock_items WHERE id = ?");
            $stmt->execute([$data['item_id']]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$item) {
                throw new Exception('Item não encontrado');
            }
            
            $quantidadeAnterior = $item['current_quantity'];
            $quantidade = $data['quantity'];
            
            // Calcular nova quantidade baseada no tipo
            switch ($data['type']) {
                case 'entrada':
                    $novaQuantidade = $quantidadeAnterior + $quantidade;
                    break;
                case 'saida':
                    $novaQuantidade = $quantidadeAnterior - $quantidade;
                    if ($novaQuantidade < 0) {
                        throw new Exception('Quantidade insuficiente em estoque');
                    }
                    break;
                case 'ajuste':
                    $novaQuantidade = $quantidade; // Quantidade absoluta
                    break;
                default:
                    throw new Exception('Tipo de movimentação inválido');
            }
            
            // Registrar movimentação
            $stmt = $this->db->prepare("
                INSERT INTO stock_movements (
                    item_id, type, quantity, reason, notes, 
                    previous_quantity, new_quantity, reference_document, user_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['item_id'],
                $data['type'],
                $quantidade,
                $data['reason'] ?? '',
                $data['notes'] ?? '',
                $quantidadeAnterior,
                $novaQuantidade,
                $data['reference_document'] ?? '',
                $_SESSION['user_id']
            ]);
            
            // Atualizar quantidade no estoque
            $stmt = $this->db->prepare("
                UPDATE stock_items SET 
                    current_quantity = ?,
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $stmt->execute([$novaQuantidade, $data['item_id']]);
            
            if ($internalTransaction) {
                $this->db->commit();
            }
            return true;
            
        } catch (Exception $e) {
            if ($internalTransaction) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
    
    /**
     * Buscar movimentações de um item
     */
    public function getMovimentacoes($itemId = null, $limit = 50) {
        $sql = "
            SELECT 
                sm.*,
                si.name as item_name,
                si.unit,
                u.username
            FROM stock_movements sm
            JOIN stock_items si ON sm.item_id = si.id
            LEFT JOIN users u ON sm.user_id = u.id
        ";
        
        $params = [];
        if ($itemId) {
            $sql .= " WHERE sm.item_id = ?";
            $params[] = $itemId;
        }
        
        $sql .= " ORDER BY sm.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Estatísticas do estoque
     */
    public function getEstatisticas() {
        $stats = [];
        
        // Total de itens
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM stock_items");
        $stats['total_itens'] = $stmt->fetchColumn();
        
        // Itens com estoque baixo
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM stock_items WHERE current_quantity <= minimum_stock");
        $stats['estoque_baixo'] = $stmt->fetchColumn();
        
        // Valor total do estoque
        $stmt = $this->db->query("SELECT SUM(current_quantity * unit_cost) as total FROM stock_items");
        $stats['valor_total'] = $stmt->fetchColumn() ?? 0;
        
        // Movimentações hoje
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM stock_movements WHERE DATE(created_at) = CURDATE()");
        $stats['movimentacoes_hoje'] = $stmt->fetchColumn();
        
        return $stats;
    }
    
    /**
     * Itens com estoque baixo
     */
    public function getItensBaixoEstoque() {
        $stmt = $this->db->query("
            SELECT * FROM stock_items 
            WHERE current_quantity <= minimum_stock 
            ORDER BY (current_quantity / minimum_stock) ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Excluir item do estoque
     */
    public function deleteItem($id) {
        $stmt = $this->db->prepare("DELETE FROM stock_items WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
<?php
/**
 * Modelo para receitas/fórmulas de produtos
 */
class ProductRecipe {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Criar nova receita para um produto
     */
    public function createRecipe($data) {
        $stmt = $this->db->prepare("
            INSERT INTO product_recipes (product_id, name, description, yield_quantity, yield_unit) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $data['product_id'],
            $data['name'],
            $data['description'] ?? '',
            $data['yield_quantity'] ?? 1000,
            $data['yield_unit'] ?? 'UN'
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Adicionar ingrediente à receita
     */
    public function addIngredient($recipeId, $data) {
        $stmt = $this->db->prepare("
            INSERT INTO recipe_ingredients (recipe_id, stock_item_id, quantity, unit, notes) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $recipeId,
            $data['stock_item_id'],
            $data['quantity'],
            $data['unit'],
            $data['notes'] ?? ''
        ]);
    }
    
    /**
     * Buscar receita por produto
     */
    public function getRecipeByProduct($productId) {
        $stmt = $this->db->prepare("
            SELECT pr.*, p.nome as product_name 
            FROM product_recipes pr 
            JOIN produtos p ON pr.product_id = p.id 
            WHERE pr.product_id = ?
        ");
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar ingredientes da receita
     */
    public function getRecipeIngredients($recipeId) {
        $stmt = $this->db->prepare("
            SELECT ri.*, si.name as ingredient_name, si.unit as stock_unit, si.current_quantity 
            FROM recipe_ingredients ri 
            JOIN stock_items si ON ri.stock_item_id = si.id 
            WHERE ri.recipe_id = ? 
            ORDER BY si.name
        ");
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calcular consumo de insumos para uma quantidade de produção
     */
    public function calculateConsumption($productId, $quantityProduced) {
        $recipe = $this->getRecipeByProduct($productId);
        if (!$recipe) {
            return [];
        }
        
        $ingredients = $this->getRecipeIngredients($recipe['id']);
        $consumption = [];
        
        $yieldRatio = $quantityProduced / $recipe['yield_quantity'];
        
        foreach ($ingredients as $ingredient) {
            $requiredQuantity = $ingredient['quantity'] * $yieldRatio;
            
            $consumption[] = [
                'stock_item_id' => $ingredient['stock_item_id'],
                'ingredient_name' => $ingredient['ingredient_name'],
                'required_quantity' => $requiredQuantity,
                'unit' => $ingredient['unit'],
                'available_quantity' => $ingredient['current_quantity'],
                'sufficient' => $ingredient['current_quantity'] >= $requiredQuantity,
                'shortage' => max(0, $requiredQuantity - $ingredient['current_quantity'])
            ];
        }
        
        return $consumption;
    }
    
    /**
     * Consumir insumos do estoque para produção
     */
    public function consumeIngredients($lancamentoId, $productId, $quantityProduced, $userId, $useTransaction = true) {
        try {
            if ($useTransaction) $this->db->beginTransaction();
            
            $consumption = $this->calculateConsumption($productId, $quantityProduced);
            
            if (empty($consumption)) {
                throw new Exception('Nenhuma receita encontrada para este produto');
            }
            
            // Verificar se há estoque suficiente
            foreach ($consumption as $item) {
                if (!$item['sufficient']) {
                    throw new Exception("Estoque insuficiente de {$item['ingredient_name']}. Necessário: {$item['required_quantity']} {$item['unit']}, Disponível: {$item['available_quantity']} {$item['unit']}");
                }
            }
            
            $estoqueModel = new EstoqueInsumos();
            
            // Consumir cada ingrediente
            foreach ($consumption as $item) {
                // Log attempt to consume this ingredient for traceability
                error_log("ProductRecipe::consumeIngredients - attempting to consume stock_item_id={$item['stock_item_id']} required={$item['required_quantity']} unit={$item['unit']} for lancamento {$lancamentoId}");
                // Buscar custo atual do item
                $stmt = $this->db->prepare("SELECT unit_cost FROM stock_items WHERE id = ?");
                $stmt->execute([$item['stock_item_id']]);
                $stockItem = $stmt->fetch(PDO::FETCH_ASSOC);
                $unitCost = $stockItem['unit_cost'] ?? 0;
                
                // Registrar saída no estoque
                $movimentacaoData = [
                    'item_id' => $item['stock_item_id'],
                    'type' => 'saida',
                    'quantity' => $item['required_quantity'],
                    'reason' => 'Produção',
                    'notes' => "Consumo para produção - Lançamento #{$lancamentoId}",
                    'reference_document' => "PROD-{$lancamentoId}"
                ];
                
                // Registrar movimentação de saída no estoque
                try {
                    $estoqueModel->addMovimentacao($movimentacaoData);
                    error_log("ProductRecipe::consumeIngredients - addMovimentacao success stock_item_id={$item['stock_item_id']} quantity={$item['required_quantity']}");
                } catch (Exception $e) {
                    error_log("ProductRecipe::consumeIngredients - addMovimentacao FAILED stock_item_id={$item['stock_item_id']} : " . $e->getMessage());
                    throw $e;
                }
                
                // Registrar consumo na tabela de controle
                $stmt = $this->db->prepare("
                    INSERT INTO production_stock_consumption 
                    (lancamento_id, stock_item_id, quantity_consumed, unit, cost_per_unit, total_cost) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $totalCost = $item['required_quantity'] * $unitCost;
                $stmt->execute([
                    $lancamentoId,
                    $item['stock_item_id'],
                    $item['required_quantity'],
                    $item['unit'],
                    $unitCost,
                    $totalCost
                ]);
            }
            
            if ($useTransaction) $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            if ($useTransaction) $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Verificar se é possível produzir uma quantidade
     */
    public function canProduce($productId, $quantityToProduce) {
        $consumption = $this->calculateConsumption($productId, $quantityToProduce);
        
        foreach ($consumption as $item) {
            if (!$item['sufficient']) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Listar todas as receitas
     */
    public function getAllRecipes() {
        $stmt = $this->db->query("
            SELECT pr.*, p.nome as product_name, p.codigo as product_code,
                   COUNT(ri.id) as ingredient_count
            FROM product_recipes pr 
            JOIN produtos p ON pr.product_id = p.id 
            LEFT JOIN recipe_ingredients ri ON pr.id = ri.recipe_id
            GROUP BY pr.id
            ORDER BY p.nome
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar custo de produção
     */
    public function getProductionCost($productId, $quantity) {
        $consumption = $this->calculateConsumption($productId, $quantity);
        $totalCost = 0;
        
        foreach ($consumption as $item) {
            $stmt = $this->db->prepare("SELECT unit_cost FROM stock_items WHERE id = ?");
            $stmt->execute([$item['stock_item_id']]);
            $stockItem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $totalCost += $item['required_quantity'] * ($stockItem['unit_cost'] ?? 0);
        }
        
        return $totalCost;
    }
    
    /**
     * Remover ingrediente da receita
     */
    public function removeIngredient($ingredientId) {
        $stmt = $this->db->prepare("DELETE FROM recipe_ingredients WHERE id = ?");
        return $stmt->execute([$ingredientId]);
    }
    
    /**
     * Atualizar receita
     */
    public function updateRecipe($recipeId, $data) {
        $stmt = $this->db->prepare("
            UPDATE product_recipes 
            SET name = ?, description = ?, yield_quantity = ?, yield_unit = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['yield_quantity'],
            $data['yield_unit'],
            $recipeId
        ]);
    }
}
?>
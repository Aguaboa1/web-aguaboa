<?php
/**
 * Model de Produção
 * Web Aguaboa - Gestão de Produção
 */

class Producao {
    private $db;
    private $lastError = null;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna a última mensagem de erro ocorrida no model
     */
    public function getLastError() {
        return $this->lastError;
    }
    
    // ==================== PRODUTOS ====================
    
    /**
     * Listar todos os produtos
     */
    public function getAllProdutos($ativo = true) {
        $sql = "SELECT * FROM produtos WHERE ativo = ? ORDER BY categoria, nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ativo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar produto por ID
     */
    public function getProdutoById($id) {
        $sql = "SELECT * FROM produtos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criar novo produto
     */
    public function createProduto($data) {
        $sql = "INSERT INTO produtos (nome, codigo, categoria, unidade_medida, capacidade_litros, descricao) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nome'],
            $data['codigo'],
            $data['categoria'],
            $data['unidade_medida'],
            $data['capacidade_litros'],
            $data['descricao']
        ]);
    }
    
    /**
     * Atualizar produto
     */
    public function updateProduto($id, $data) {
        $sql = "UPDATE produtos SET nome = ?, codigo = ?, categoria = ?, descricao = ? 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nome'],
            $data['codigo'],
            $data['categoria'],
            $data['descricao'],
            $id
        ]);
    }
    
    // ==================== LANÇAMENTOS ====================
    
    /**
     * Criar lançamento de produção
     */
    public function createLancamento($data) {
        $sql = "INSERT INTO producao_lancamentos 
                (produto_id, data_producao, quantidade_produzida, quantidade_perdida, 
                 motivo_perda, observacoes, turno, operador_id, supervisor_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            $data['produto_id'],
            $data['data_producao'],
            $data['quantidade_produzida'],
            $data['quantidade_perdida'],
            $data['motivo_perda'],
            $data['observacoes'],
            $data['turno'],
            $data['operador_id'],
            $data['supervisor_id']
        ]);

        // Retornar o ID do lançamento recém-criado quando bem-sucedido
        if ($ok) {
            return (int)$this->db->lastInsertId();
        }

        return false;
    }
    
    /**
     * Atualizar lançamento
     */
    public function updateLancamento($id, $data) {
        // Buscar dados antigos
        $old = $this->getLancamentoById($id);

        if (!$old) return false;

        $produtoChanged = ((int)$old['produto_id'] !== (int)$data['produto_id']);
        $quantidadeChanged = ((int)$old['quantidade_produzida'] !== (int)$data['quantidade_produzida']);

        // Se produto ou quantidade mudou, validar que a nova produção é possível antes de alterar
        if ($produtoChanged || $quantidadeChanged) {
            require_once __DIR__ . '/ProductRecipe.php';
            $recipeModel = new ProductRecipe();

            $consumption = $recipeModel->calculateConsumption($data['produto_id'], $data['quantidade_produzida']);

            // Se existe receita e não há estoque suficiente, bloquear a atualização
            if (!empty($consumption)) {
                foreach ($consumption as $item) {
                    if (!$item['sufficient']) {
                        // Não permitir atualização que deixaria estoque negativo
                        return false;
                    }
                }
            }

            // Reverter consumo anterior (se houver)
            try {
                $this->reverseConsumption($id);
            } catch (Exception $e) {
                // Se não for possível reverter, abortar
                return false;
            }
        }

        // Atualizar o lançamento
        $sql = "UPDATE producao_lancamentos SET 
                produto_id = ?, data_producao = ?, quantidade_produzida = ?, 
                quantidade_perdida = ?, motivo_perda = ?, observacoes = ?, 
                turno = ?, operador_id = ?, supervisor_id = ? 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            $data['produto_id'],
            $data['data_producao'],
            $data['quantidade_produzida'],
            $data['quantidade_perdida'],
            $data['motivo_perda'],
            $data['observacoes'],
            $data['turno'],
            $data['operador_id'],
            $data['supervisor_id'],
            $id
        ]);

        if (!$ok) return false;

        // Se produto/quantidade mudou e existe receita, consumir os novos insumos
        if (($produtoChanged || $quantidadeChanged)) {
            require_once __DIR__ . '/ProductRecipe.php';
            $recipeModel = new ProductRecipe();
            $consumption = $recipeModel->calculateConsumption($data['produto_id'], $data['quantidade_produzida']);

            if (!empty($consumption)) {
                try {
                    $recipeModel->consumeIngredients($id, $data['produto_id'], $data['quantidade_produzida'], $_SESSION['user_id'] ?? null);
                } catch (Exception $e) {
                    // Em caso de falha ao consumir os novos insumos, tentar restaurar o consumo antigo falhando graciosamente
                    // (não ideal em condições de corrida, mas evita perdas imediatas)
                    // Não temos os dados antigos aqui para re-aplicar; registrar erro e retornar false
                    return false;
                }
            }
        }

        return true;
    }
    
    /**
     * Buscar lançamentos por período
     */
    public function getLancamentosPorPeriodo($dataInicio, $dataFim, $produtoId = null) {
        $sql = "SELECT l.*, p.nome as produto_nome, p.codigo as produto_codigo,
                       u1.username as operador_nome, u2.username as supervisor_nome
                FROM producao_lancamentos l
                JOIN produtos p ON l.produto_id = p.id
                LEFT JOIN users u1 ON l.operador_id = u1.id
                LEFT JOIN users u2 ON l.supervisor_id = u2.id
                WHERE l.data_producao BETWEEN ? AND ?";
        
        $params = [$dataInicio, $dataFim];
        
        if ($produtoId) {
            $sql .= " AND l.produto_id = ?";
            $params[] = $produtoId;
        }
        
        $sql .= " ORDER BY l.data_producao DESC, p.nome, l.turno";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar lançamento por ID
     */
    public function getLancamentoById($id) {
        $sql = "SELECT l.*, p.nome as produto_nome, p.codigo as produto_codigo
                FROM producao_lancamentos l
                JOIN produtos p ON l.produto_id = p.id
                WHERE l.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Deletar lançamento
     */
    public function deleteLancamento($id, $userId = null) {
        try {
            // Primeiro reverter qualquer consumo de insumos associado a este lançamento
            $this->reverseConsumption($id);

            // Em seguida remover o lançamento
            $sql = "DELETE FROM producao_lancamentos WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Producao::deleteLancamento error for id {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reverter consumo de insumos registrado para um lançamento (restaura estoque)
     */
    public function reverseConsumption($lancamentoId) {
        // Buscar registros de consumo vinculados
        $stmt = $this->db->prepare("SELECT * FROM production_stock_consumption WHERE lancamento_id = ?");
        $stmt->execute([$lancamentoId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) return true;

        // Para cada consumo, registrar movimentação de entrada que restaura a quantidade
        require_once __DIR__ . '/EstoqueInsumos.php';
        $estoqueModel = new EstoqueInsumos();

        foreach ($rows as $row) {
            $movData = [
                'item_id' => $row['stock_item_id'],
                'type' => 'entrada',
                'quantity' => $row['quantity_consumed'],
                'reason' => 'Reversão de consumo',
                'notes' => "Reversão do consumo para lançamento #{$lancamentoId}",
                'reference_document' => "REV-PROD-{$lancamentoId}"
            ];

            // addMovimentacao cuidará de validações e do registro
            $estoqueModel->addMovimentacao($movData);
        }

        // Remover os registros de controle após restaurar estoque
        $del = $this->db->prepare("DELETE FROM production_stock_consumption WHERE lancamento_id = ?");
        $del->execute([$lancamentoId]);

        return true;
    }
    
    // ==================== RELATÓRIOS ====================
    
    /**
     * Dashboard - Resumo do dia atual
     */
    public function getDashboardHoje() {
        $hoje = date('Y-m-d');
        
        $sql = "SELECT 
                    p.nome as produto,
                    SUM(l.quantidade_produzida) as total_produzido,
                    SUM(l.quantidade_perdida) as total_perdido,
                    COUNT(l.id) as total_lancamentos
                FROM producao_lancamentos l
                JOIN produtos p ON l.produto_id = p.id
                WHERE l.data_producao = ?
                GROUP BY p.id, p.nome
                ORDER BY total_produzido DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hoje]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Dashboard - Resumo do mês atual
     */
    public function getDashboardMes() {
        $mesAtual = date('Y-m');
        
        $sql = "SELECT 
                    p.nome as produto,
                    SUM(l.quantidade_produzida) as total_produzido,
                    SUM(l.quantidade_perdida) as total_perdido,
                    COUNT(l.id) as total_lancamentos
                FROM producao_lancamentos l
                JOIN produtos p ON l.produto_id = p.id
                WHERE DATE_FORMAT(l.data_producao, '%Y-%m') = ?
                GROUP BY p.id, p.nome
                ORDER BY total_produzido DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$mesAtual]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Relatório por período (dias, semanas, meses)
     */
    public function getRelatorioPorPeriodo($tipo = 'dia', $limite = 30) {
        $formatoData = match($tipo) {
            'dia' => '%Y-%m-%d',
            'semana' => '%Y-%u',
            'mes' => '%Y-%m',
            'ano' => '%Y',
            default => '%Y-%m-%d'
        };
        
        $sql = "SELECT 
                    DATE_FORMAT(l.data_producao, '{$formatoData}') as periodo,
                    p.nome as produto,
                    SUM(l.quantidade_produzida) as total_produzido,
                    SUM(l.quantidade_perdida) as total_perdido,
                    COUNT(l.id) as total_lancamentos
                FROM producao_lancamentos l
                JOIN produtos p ON l.produto_id = p.id
                WHERE l.data_producao >= DATE_SUB(CURDATE(), INTERVAL {$limite} DAY)
                GROUP BY periodo, p.id, p.nome
                ORDER BY periodo DESC, total_produzido DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Estatísticas gerais
     */
    public function getEstatisticas() {
        // Total de produtos
        $stmt = $this->db->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1");
        $totalProdutos = $stmt->fetchColumn();
        
        // Total produzido no mês atual
        $mesAtual = date('Y-m');
        $stmt = $this->db->prepare("SELECT SUM(quantidade_produzida) FROM producao_lancamentos WHERE DATE_FORMAT(data_producao, '%Y-%m') = ?");
        $stmt->execute([$mesAtual]);
        $produzidoMes = $stmt->fetchColumn() ?: 0;
        
        // Total perdido no mês atual
        $stmt = $this->db->prepare("SELECT SUM(quantidade_perdida) FROM producao_lancamentos WHERE DATE_FORMAT(data_producao, '%Y-%m') = ?");
        $stmt->execute([$mesAtual]);
        $perdidoMes = $stmt->fetchColumn() ?: 0;
        
        // Total produzido no ano atual
        $anoAtual = date('Y');
        $stmt = $this->db->prepare("SELECT SUM(quantidade_produzida) FROM producao_lancamentos WHERE DATE_FORMAT(data_producao, '%Y') = ?");
        $stmt->execute([$anoAtual]);
        $produzidoAno = $stmt->fetchColumn() ?: 0;
        
        // Produto mais produzido no mês
        $stmt = $this->db->prepare("
            SELECT p.nome, SUM(l.quantidade_produzida) as total
            FROM producao_lancamentos l
            JOIN produtos p ON l.produto_id = p.id
            WHERE DATE_FORMAT(l.data_producao, '%Y-%m') = ?
            GROUP BY p.id, p.nome
            ORDER BY total DESC
            LIMIT 1
        ");
        $stmt->execute([$mesAtual]);
        $produtoTop = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total_produtos' => $totalProdutos,
            'produzido_mes' => $produzidoMes,
            'perdido_mes' => $perdidoMes,
            'produzido_ano' => $produzidoAno,
            'produto_top_mes' => $produtoTop ? $produtoTop['nome'] : 'Nenhum',
            'eficiencia_mes' => $produzidoMes > 0 ? round((($produzidoMes - $perdidoMes) / $produzidoMes) * 100, 2) : 0
        ];
    }
    
    /**
     * Histórico de produção por produto
     */
    public function getHistoricoProduto($produtoId, $dias = 30) {
        $sql = "SELECT 
                    data_producao,
                    SUM(quantidade_produzida) as produzido,
                    SUM(quantidade_perdida) as perdido,
                    COUNT(*) as lancamentos
                FROM producao_lancamentos
                WHERE produto_id = ? 
                AND data_producao >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY data_producao
                ORDER BY data_producao DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$produtoId, $dias]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ==================== METAS ====================
    
    /**
     * Buscar meta do mês
     */
    public function getMetaMes($produtoId, $mes = null, $ano = null) {
        $mes = $mes ?: date('n');
        $ano = $ano ?: date('Y');
        
        $sql = "SELECT * FROM producao_metas WHERE produto_id = ? AND mes = ? AND ano = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$produtoId, $mes, $ano]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criar ou atualizar meta
     */
    public function setMeta($produtoId, $mes, $ano, $metaMensal, $metaDiaria = null) {
        $sql = "INSERT INTO producao_metas (produto_id, mes, ano, meta_mensal, meta_diaria) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                meta_mensal = VALUES(meta_mensal), 
                meta_diaria = VALUES(meta_diaria)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$produtoId, $mes, $ano, $metaMensal, $metaDiaria]);
    }
    
    /**
     * Progresso da meta mensal
     */
    public function getProgressoMeta($produtoId, $mes = null, $ano = null) {
        $mes = $mes ?: date('n');
        $ano = $ano ?: date('Y');
        
        // Buscar meta
        $meta = $this->getMetaMes($produtoId, $mes, $ano);
        if (!$meta) {
            return null;
        }
        
        // Calcular produção do mês
        $sql = "SELECT SUM(quantidade_produzida) as produzido
                FROM producao_lancamentos
                WHERE produto_id = ? 
                AND MONTH(data_producao) = ? 
                AND YEAR(data_producao) = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$produtoId, $mes, $ano]);
        $produzido = $stmt->fetchColumn() ?: 0;
        
        $percentual = $meta['meta_mensal'] > 0 ? round(($produzido / $meta['meta_mensal']) * 100, 2) : 0;
        
        return [
            'meta_mensal' => $meta['meta_mensal'],
            'meta_diaria' => $meta['meta_diaria'],
            'produzido' => $produzido,
            'restante' => $meta['meta_mensal'] - $produzido,
            'percentual' => $percentual,
            'status' => $percentual >= 100 ? 'CONCLUÍDA' : ($percentual >= 80 ? 'NO_PRAZO' : 'ATRASADA')
        ];
    }
    
    /**
     * Excluir um produto específico
     */
    public function deleteProduto($produto_id) {
        try {
            $this->db->beginTransaction();
            
            // Excluir lançamentos relacionados
            $stmt = $this->db->prepare("DELETE FROM producao_lancamentos WHERE produto_id = ?");
            $stmt->execute([$produto_id]);
            
            // Excluir metas relacionadas
            $stmt = $this->db->prepare("DELETE FROM producao_metas WHERE produto_id = ?");
            $stmt->execute([$produto_id]);
            
            // Excluir o produto
            $stmt = $this->db->prepare("DELETE FROM produtos WHERE id = ?");
            $stmt->execute([$produto_id]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Excluir todos os produtos
     */
    public function deleteAllProdutos() {
        try {
            $this->db->beginTransaction();
            
            // Excluir todos os lançamentos
            $this->db->exec("DELETE FROM producao_lancamentos");
            
            // Excluir todas as metas
            $this->db->exec("DELETE FROM producao_metas");
            
            // Excluir todos os produtos
            $this->db->exec("DELETE FROM produtos");
            
            // Resetar auto-increment
            $this->db->exec("ALTER TABLE produtos AUTO_INCREMENT = 1");
            $this->db->exec("ALTER TABLE producao_lancamentos AUTO_INCREMENT = 1");
            $this->db->exec("ALTER TABLE producao_metas AUTO_INCREMENT = 1");
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Obter total de produtos
     */
    public function getTotalProdutos() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM produtos");
        return $stmt->fetchColumn();
    }
    
    /**
     * Obter lançamentos recentes
     */
    public function getLancamentosRecentes($limit = 50) {
        $sql = "SELECT 
                    l.*,
                    p.nome as produto_nome,
                    p.codigo as produto_codigo,
                    p.categoria as produto_categoria
                FROM producao_lancamentos l
                JOIN produtos p ON l.produto_id = p.id
                ORDER BY l.data_producao DESC, l.created_at DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter metas por período
     */
    public function getMetasPorPeriodo($dataInicio, $dataFim) {
        $sql = "SELECT 
                    m.*,
                    p.nome as produto_nome,
                    p.codigo as produto_codigo
                FROM producao_metas m
                LEFT JOIN produtos p ON m.produto_id = p.id
                WHERE m.data_inicio <= ? AND m.data_fim >= ?
                ORDER BY m.data_inicio DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dataFim, $dataInicio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
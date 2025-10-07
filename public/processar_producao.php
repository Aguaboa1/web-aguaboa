<?php
/**
 * Processador de formulários da produção
 */

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../src/models/UserPermission.php';

// Verificar autenticação
requireAuth();

// Verificar permissão usando sistema moderno de departamentos
$userPermission = new UserPermission();
if (!$userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'edit')) {
    setFlash('error', 'Acesso negado. Você precisa de permissão de edição no departamento de produção para realizar esta ação.');
    redirect('/departments');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../src/models/Producao.php';
    require_once __DIR__ . '/../src/models/ActivityLog.php';
    
    $producaoModel = new Producao();
    $activityLog = new ActivityLog();
    
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create_produto':
                $data = [
                    'nome' => sanitize($_POST['nome']),
                    'codigo' => sanitize($_POST['codigo']),
                    'categoria' => sanitize($_POST['categoria']),
                    'unidade_medida' => sanitize($_POST['unidade_medida']),
                    'capacidade_litros' => (float)$_POST['capacidade_litros'],
                    'descricao' => sanitize($_POST['descricao'])
                ];
                
                if (empty($data['nome']) || empty($data['codigo'])) {
                    setFlash('error', 'Nome e código são obrigatórios');
                    break;
                }
                
                if ($producaoModel->createProduto($data)) {
                    $activityLog->log(
                        $_SESSION['user_id'],
                        'CREATE_PRODUTO',
                        "Produto criado: {$data['nome']} ({$data['codigo']})",
                        $_SERVER['REMOTE_ADDR']
                    );
                    
                    setFlash('success', 'Produto cadastrado com sucesso!');
                } else {
                    setFlash('error', 'Erro ao cadastrar produto. Verifique se o código não está duplicado.');
                }
                break;
                
            case 'update_produto':
                $produto_id = (int)$_POST['produto_id'];
                $data = [
                    'nome' => sanitize($_POST['nome']),
                    'codigo' => sanitize($_POST['codigo']),
                    'categoria' => sanitize($_POST['categoria']),
                    'descricao' => sanitize($_POST['descricao'])
                ];
                
                if (empty($produto_id) || empty($data['nome']) || empty($data['codigo'])) {
                    setFlash('error', 'ID, nome e código são obrigatórios');
                    break;
                }
                
                // Buscar dados antigos para log
                $produtoAntigo = $producaoModel->getProdutoById($produto_id);
                
                if ($producaoModel->updateProduto($produto_id, $data)) {
                    $activityLog->log(
                        $_SESSION['user_id'],
                        'UPDATE_PRODUTO',
                        "Produto atualizado: {$produtoAntigo['nome']} → {$data['nome']} ({$data['codigo']})",
                        $_SERVER['REMOTE_ADDR']
                    );
                    
                    setFlash('success', 'Produto atualizado com sucesso!');
                } else {
                    setFlash('error', 'Erro ao atualizar produto');
                }
                break;
                
            case 'create_lancamento':
                require_once __DIR__ . '/../src/models/ProductRecipe.php';
                require_once __DIR__ . '/../src/models/EstoqueInsumos.php';
                
                $data = [
                    'produto_id' => (int)$_POST['produto_id'],
                    'data_producao' => $_POST['data_producao'],
                    'quantidade_produzida' => (int)$_POST['quantidade_produzida'],
                    'quantidade_perdida' => (int)($_POST['quantidade_perdida'] ?? 0),
                    'motivo_perda' => sanitize($_POST['motivo_perda'] ?? ''),
                    'observacoes' => sanitize($_POST['observacoes'] ?? ''),
                    'turno' => sanitize($_POST['turno']),
                    'operador_id' => $_SESSION['user_id'],
                    'supervisor_id' => null
                ];
                
                if (empty($data['produto_id']) || empty($data['data_producao']) || empty($data['quantidade_produzida'])) {
                    setFlash('error', 'Produto, data e quantidade produzida são obrigatórios');
                    break;
                }
                
                // Detectar override manual (post vindo da página de Insumos)
                $force_manual = !empty($_POST['force_manual']);

                // Verificar se existe receita e se há estoque suficiente (pulamos se for override manual)
                $recipeModel = new ProductRecipe();
                if (!$force_manual) {
                    if (!$recipeModel->canProduce($data['produto_id'], $data['quantidade_produzida'])) {
                        $consumption = $recipeModel->calculateConsumption($data['produto_id'], $data['quantidade_produzida']);
                        
                        if (empty($consumption)) {
                            setFlash('warning', 'Receita não encontrada para este produto. O lançamento foi registrado, mas os insumos não foram consumidos automaticamente.');
                        } else {
                            $shortages = [];
                            foreach ($consumption as $item) {
                                if (!$item['sufficient']) {
                                    $shortages[] = "{$item['ingredient_name']}: necessário {$item['required_quantity']} {$item['unit']}, disponível {$item['available_quantity']} {$item['unit']}";
                                }
                            }
                            
                            if (!empty($shortages)) {
                                setFlash('error', 'Estoque insuficiente para produção:<br>' . implode('<br>', $shortages));
                                break;
                            }
                        }
                    }
                }
                
                // Criar o lançamento e, se existir receita, consumir insumos em transação única
                $db = Database::getInstance()->getConnection();
                $consumption = $recipeModel->calculateConsumption($data['produto_id'], $data['quantidade_produzida']);

                if (!empty($consumption)) {
                    // Tentaremos efetuar criação + consumo de forma atômica
                    try {
                        $db->beginTransaction();
                        $lancamentoId = $producaoModel->createLancamento($data);
                        if (!$lancamentoId) {
                            throw new Exception('Erro ao criar lançamento');
                        }

                        // Consumir insumos sem abrir nova transação (já estamos em uma)
                        $recipeModel->consumeIngredients($lancamentoId, $data['produto_id'], $data['quantidade_produzida'], $_SESSION['user_id'], false);

                        // Commit da transação global
                        $db->commit();

                        $produto = $producaoModel->getProdutoById($data['produto_id']);
                        $consumedItems = array_map(function($item) {
                            return "{$item['ingredient_name']}: {$item['required_quantity']} {$item['unit']}";
                        }, $consumption);
                        setFlash('success', "Lançamento registrado com sucesso!<br>Insumos consumidos:<br>" . implode('<br>', $consumedItems));

                        $activityLog->log(
                            $_SESSION['user_id'],
                            'CREATE_LANCAMENTO',
                            "Lançamento criado: {$produto['nome']} - {$data['quantidade_produzida']} unidades",
                            $_SERVER['REMOTE_ADDR']
                        );

                    } catch (Exception $e) {
                        // Garantir rollback
                        if ($db->inTransaction()) $db->rollBack();
                        error_log('Erro ao registrar lançamento e consumir insumos: ' . $e->getMessage());
                        setFlash('error', 'Erro ao registrar lançamento e consumir insumos: ' . $e->getMessage());
                    }
                } else {
                    // Sem receita: verificar se foi enviado consumo manual
                    $manualIds = $_POST['manual_item_id'] ?? [];
                    $manualQtys = $_POST['manual_item_quantity'] ?? [];

                    if (!empty($manualIds) && count($manualIds) === count($manualQtys)) {
                        // Realizar criação + consumo manual em transação atômica
                        $db = Database::getInstance()->getConnection();
                        try {
                            $db->beginTransaction();

                            $lancamentoId = $producaoModel->createLancamento($data);
                            if (!$lancamentoId) throw new Exception('Erro ao criar lançamento');

                            $estoqueModel = new EstoqueInsumos();

                            $consumedItems = [];
                            for ($i = 0; $i < count($manualIds); $i++) {
                                $itemId = (int)$manualIds[$i];
                                $qty = floatval($manualQtys[$i]);
                                if ($qty <= 0) continue;

                                // Registrar movimentação de saída
                                $movData = [
                                    'item_id' => $itemId,
                                    'type' => 'saida',
                                    'quantity' => $qty,
                                    'reason' => 'Produção (manual)',
                                    'notes' => "Consumo manual para produção - Lançamento #{$lancamentoId}",
                                    'reference_document' => "PROD-{$lancamentoId}"
                                ];

                                $estoqueModel->addMovimentacao($movData);

                                // Registrar controle de consumo
                                $stmt = $db->prepare("SELECT unit_cost, unit FROM stock_items WHERE id = ?");
                                $stmt->execute([$itemId]);
                                $stock = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['unit_cost' => 0, 'unit' => 'UN'];

                                $totalCost = $qty * ($stock['unit_cost'] ?? 0);
                                $insStmt = $db->prepare("INSERT INTO production_stock_consumption (lancamento_id, stock_item_id, quantity_consumed, unit, cost_per_unit, total_cost) VALUES (?, ?, ?, ?, ?, ?)");
                                $insStmt->execute([$lancamentoId, $itemId, $qty, $stock['unit'] ?? 'UN', $stock['unit_cost'] ?? 0, $totalCost]);

                                $consumedItems[] = "{$stock['unit']} #{$itemId}: {$qty} ({$stock['unit_cost']} c/u)";
                            }

                            $db->commit();

                            $produto = $producaoModel->getProdutoById($data['produto_id']);
                            setFlash('success', 'Lançamento registrado com sucesso!<br>Insumos consumidos manualmente:<br>' . implode('<br>', $consumedItems));
                            $activityLog->log(
                                $_SESSION['user_id'],
                                'CREATE_LANCAMENTO',
                                "Lançamento criado (consumo manual): {$produto['nome']} - {$data['quantidade_produzida']} unidades",
                                $_SERVER['REMOTE_ADDR']
                            );

                        } catch (Exception $e) {
                            if ($db->inTransaction()) $db->rollBack();
                            setFlash('error', 'Erro ao registrar lançamento com consumo manual: ' . $e->getMessage());
                        }
                    } else {
                        // Sem receita e sem consumo manual: registrar somente o lançamento
                        $lancamentoId = $producaoModel->createLancamento($data);
                        if ($lancamentoId) {
                            $produto = $producaoModel->getProdutoById($data['produto_id']);
                            setFlash('success', 'Lançamento registrado com sucesso! (Receita não configurada - insumos não foram consumidos automaticamente)');
                            $activityLog->log(
                                $_SESSION['user_id'],
                                'CREATE_LANCAMENTO',
                                "Lançamento criado: {$produto['nome']} - {$data['quantidade_produzida']} unidades",
                                $_SERVER['REMOTE_ADDR']
                            );
                        } else {
                            setFlash('error', 'Erro ao registrar lançamento');
                        }
                    }
                }
                break;
                
            case 'delete_produto':
                $produto_id = (int)$_POST['produto_id'];
                
                if (empty($produto_id)) {
                    setFlash('error', 'ID do produto é obrigatório');
                    break;
                }
                
                // Buscar dados do produto antes de excluir
                $produto = $producaoModel->getProdutoById($produto_id);
                if (!$produto) {
                    setFlash('error', 'Produto não encontrado');
                    break;
                }
                
                if ($producaoModel->deleteProduto($produto_id)) {
                    $activityLog->log(
                        $_SESSION['user_id'],
                        'DELETE_PRODUTO',
                        "Produto excluído: {$produto['nome']} ({$produto['codigo']})",
                        $_SERVER['REMOTE_ADDR']
                    );
                    
                    setFlash('success', "Produto '{$produto['nome']}' excluído com sucesso!");
                } else {
                    setFlash('error', 'Erro ao excluir produto');
                }
                break;
                
            case 'delete_all_produtos':
                // Verificar se é admin
                if ($_SESSION['role'] !== 'admin') {
                    setFlash('error', 'Apenas administradores podem excluir todos os produtos');
                    break;
                }
                
                $total = $producaoModel->getTotalProdutos();
                
                if ($producaoModel->deleteAllProdutos()) {
                    $activityLog->log(
                        $_SESSION['user_id'],
                        'DELETE_ALL_PRODUTOS',
                        "EXCLUSÃO EM MASSA: {$total} produtos excluídos",
                        $_SERVER['REMOTE_ADDR']
                    );
                    
                    setFlash('success', "Todos os produtos ({$total}) foram excluídos com sucesso!");
                } else {
                    setFlash('error', 'Erro ao excluir produtos');
                }
                break;
                
            case 'relatorio_ajax':
                header('Content-Type: application/json');
                
                $dataInicial = sanitize($_POST['data_inicial']);
                $dataFinal = sanitize($_POST['data_final']);
                $insumoId = sanitize($_POST['insumo_id']);
                
                if (empty($dataInicial) || empty($dataFinal)) {
                    echo json_encode(['success' => false, 'message' => 'Período é obrigatório']);
                    exit;
                }
                
                try {
                    $db = Database::getInstance()->getConnection();
                    
                    $sql = "
                        SELECT 
                            p.nome as insumo,
                            p.codigo,
                            DATE(pl.data_producao) as data,
                            SUM(pl.quantidade_produzida) as consumido,
                            SUM(pl.quantidade_perdida) as perdido,
                            COUNT(*) as lancamentos,
                            ROUND(AVG(CASE 
                                WHEN pl.quantidade_produzida > 0 
                                THEN ((pl.quantidade_produzida - pl.quantidade_perdida) / pl.quantidade_produzida) * 100 
                                ELSE 0 
                            END), 2) as eficiencia_media
                        FROM producao_lancamentos pl
                        JOIN produtos p ON pl.produto_id = p.id
                        WHERE pl.data_producao BETWEEN ? AND ?
                    ";
                    
                    $params = [$dataInicial, $dataFinal];
                    
                    if ($insumoId) {
                        $sql .= " AND p.id = ?";
                        $params[] = $insumoId;
                    }
                    
                    $sql .= " 
                        GROUP BY p.id, DATE(pl.data_producao)
                        ORDER BY pl.data_producao DESC, p.nome
                    ";
                    
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Calcular totais
                    $totais = [
                        'consumido' => 0,
                        'perdido' => 0,
                        'lancamentos' => 0
                    ];
                    
                    foreach ($dados as $row) {
                        $totais['consumido'] += $row['consumido'];
                        $totais['perdido'] += $row['perdido'];
                        $totais['lancamentos'] += $row['lancamentos'];
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'dados' => $dados,
                        'totais' => $totais
                    ]);
                    exit;
                    
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                    exit;
                }
                break;
                
            default:
                setFlash('error', 'Ação não reconhecida');
        }
        
    } catch (Exception $e) {
        setFlash('error', 'Erro: ' . $e->getMessage());
    }
}

// Redirecionar de volta para a produção
redirect('/producao');
?>
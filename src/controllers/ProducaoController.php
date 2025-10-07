<?php
/**
 * Controller de Produção
 * Web Aguaboa - Gestão de Produção
 */

class ProducaoController {
    private $producaoModel;
    private $activityLog;
    
    public function __construct() {
        $this->producaoModel = new Producao();
        $this->activityLog = new ActivityLog();
    }
    
    /**
     * Dashboard de produção
     */
    public function dashboard() {
        requireAuth();
        
        // Verificar permissão
        if (!in_array($_SESSION['role'], ['admin', 'producao'])) {
            setFlash('error', 'Acesso negado ao departamento de produção');
            redirect('/departments');
        }
        
        // Buscar dados do dashboard
        $estatisticas = $this->producaoModel->getEstatisticas();
        $producaoHoje = $this->producaoModel->getDashboardHoje();
        $produtos = $this->producaoModel->getAllProdutos();
        
        // Buscar relatório dos últimos 7 dias
        $relatorio7dias = $this->producaoModel->getRelatorioPorPeriodo('dia', 7);
        
        $pageTitle = 'Gestão de Produção - Dashboard';
        $flashMessages = getFlashMessages();
        
        include '../src/views/producao/dashboard.php';
    }
    
    /**
     * Lista de produtos
     */
    public function produtos() {
        requireAuth();
        
        if (!in_array($_SESSION['role'], ['admin', 'producao'])) {
            setFlash('error', 'Acesso negado');
            redirect('/departments');
        }
        
        $produtos = $this->producaoModel->getAllProdutos();
        
        $pageTitle = 'Produtos - Gestão de Produção';
        $flashMessages = getFlashMessages();
        
        include '../src/views/producao/produtos.php';
    }
    
    /**
     * Criar produto
     */
    public function createProduto() {
        requireAuth();
        
        if (!in_array($_SESSION['role'], ['admin', 'producao'])) {
            setFlash('error', 'Acesso negado');
            redirect('/departments');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nome' => sanitize($_POST['nome']),
                'codigo' => sanitize($_POST['codigo']),
                'categoria' => sanitize($_POST['categoria']),
                'unidade_medida' => sanitize($_POST['unidade_medida']),
                'capacidade_litros' => (float)$_POST['capacidade_litros'],
                'descricao' => sanitize($_POST['descricao'])
            ];
            
            if ($this->producaoModel->createProduto($data)) {
                $this->activityLog->log(
                    $_SESSION['user_id'],
                    'CREATE_PRODUTO',
                    "Produto criado: {$data['nome']} ({$data['codigo']})",
                    $_SERVER['REMOTE_ADDR']
                );
                
                setFlash('success', 'Produto criado com sucesso!');
                redirect('/producao/produtos');
            } else {
                setFlash('error', 'Erro ao criar produto');
                redirect('/producao/produtos');
            }
        }
        
        $pageTitle = 'Novo Produto - Gestão de Produção';
        $flashMessages = getFlashMessages();
        
        include '../src/views/producao/create_produto.php';
    }
    
    /**
     * Lançamentos de produção
     */
    public function lancamentos() {
        requireAuth();
        
        if (!in_array($_SESSION['role'], ['admin', 'producao'])) {
            setFlash('error', 'Acesso negado');
            redirect('/departments');
        }
        
        // Filtros
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-7 days'));
        $dataFim = $_GET['data_fim'] ?? date('Y-m-d');
        $produtoId = $_GET['produto_id'] ?? null;
        
        $lancamentos = $this->producaoModel->getLancamentosPorPeriodo($dataInicio, $dataFim, $produtoId);
        $produtos = $this->producaoModel->getAllProdutos();
        
        $pageTitle = 'Lançamentos de Produção';
        $flashMessages = getFlashMessages();
        
        include '../src/views/producao/lancamentos.php';
    }
    
    /**
     * Criar lançamento
     */
    public function createLancamento() {
        requireAuth();
        
        if (!in_array($_SESSION['role'], ['admin', 'producao'])) {
            setFlash('error', 'Acesso negado');
            redirect('/departments');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'produto_id' => (int)$_POST['produto_id'],
                'data_producao' => $_POST['data_producao'],
                'quantidade_produzida' => (int)$_POST['quantidade_produzida'],
                'quantidade_perdida' => (int)($_POST['quantidade_perdida'] ?? 0),
                'motivo_perda' => sanitize($_POST['motivo_perda'] ?? ''),
                'observacoes' => sanitize($_POST['observacoes'] ?? ''),
                'turno' => sanitize($_POST['turno']),
                'operador_id' => $_POST['operador_id'] ? (int)$_POST['operador_id'] : null,
                'supervisor_id' => $_POST['supervisor_id'] ? (int)$_POST['supervisor_id'] : null
            ];
            
            if ($this->producaoModel->createLancamento($data)) {
                $produto = $this->producaoModel->getProdutoById($data['produto_id']);
                
                $this->activityLog->log(
                    $_SESSION['user_id'],
                    'CREATE_LANCAMENTO',
                    "Lançamento criado: {$produto['nome']} - {$data['quantidade_produzida']} unidades",
                    $_SERVER['REMOTE_ADDR']
                );
                
                setFlash('success', 'Lançamento criado com sucesso!');
                redirect('/producao/lancamentos');
            } else {
                setFlash('error', 'Erro ao criar lançamento');
                redirect('/producao/lancamentos');
            }
        }
        
        $produtos = $this->producaoModel->getAllProdutos();
        
        // Buscar usuários de produção
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT id, username FROM users WHERE role IN ('producao', 'admin') ORDER BY username");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $pageTitle = 'Novo Lançamento - Gestão de Produção';
        $flashMessages = getFlashMessages();
        
        include '../src/views/producao/create_lancamento.php';
    }
    
    /**
     * Editar lançamento
     */
    public function editLancamento($id) {
        requireAuth();
        
        if (!in_array($_SESSION['role'], ['admin', 'producao'])) {
            setFlash('error', 'Acesso negado');
            redirect('/departments');
        }
        
        $lancamento = $this->producaoModel->getLancamentoById($id);
        if (!$lancamento) {
            setFlash('error', 'Lançamento não encontrado');
            redirect('/producao/lancamentos');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'produto_id' => (int)$_POST['produto_id'],
                'data_producao' => $_POST['data_producao'],
                'quantidade_produzida' => (int)$_POST['quantidade_produzida'],
                'quantidade_perdida' => (int)($_POST['quantidade_perdida'] ?? 0),
                'motivo_perda' => sanitize($_POST['motivo_perda'] ?? ''),
                'observacoes' => sanitize($_POST['observacoes'] ?? ''),
                'turno' => sanitize($_POST['turno']),
                'operador_id' => $_POST['operador_id'] ? (int)$_POST['operador_id'] : null,
                'supervisor_id' => $_POST['supervisor_id'] ? (int)$_POST['supervisor_id'] : null
            ];
            
            if ($this->producaoModel->updateLancamento($id, $data)) {
                $produto = $this->producaoModel->getProdutoById($data['produto_id']);
                
                $this->activityLog->log(
                    $_SESSION['user_id'],
                    'UPDATE_LANCAMENTO',
                    "Lançamento editado: {$produto['nome']} - {$data['quantidade_produzida']} unidades",
                    $_SERVER['REMOTE_ADDR']
                );
                
                setFlash('success', 'Lançamento atualizado com sucesso!');
                redirect('/producao/lancamentos');
            } else {
                setFlash('error', 'Erro ao atualizar lançamento');
            }
        }
        
        $produtos = $this->producaoModel->getAllProdutos();
        
        // Buscar usuários de produção
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT id, username FROM users WHERE role IN ('producao', 'admin') ORDER BY username");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $pageTitle = 'Editar Lançamento - Gestão de Produção';
        $flashMessages = getFlashMessages();
        
    include '../src/views/departments/editar_lancamento_producao.php';
    }
    
    /**
     * Deletar lançamento
     */
    public function deleteLancamento($id) {
        requireAuth();
        
        if (!in_array($_SESSION['role'], ['admin', 'producao'])) {
            setFlash('error', 'Acesso negado');
            redirect('/departments');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lancamento = $this->producaoModel->getLancamentoById($id);
            if (!$lancamento) {
                setFlash('error', 'Lançamento não encontrado');
                redirect('/producao/lancamentos');
            }
            
                if ($this->producaoModel->deleteLancamento($id, $_SESSION['user_id'] ?? null)) {
                $this->activityLog->log(
                    $_SESSION['user_id'],
                    'DELETE_LANCAMENTO',
                    "Lançamento excluído: {$lancamento['produto_nome']}",
                    $_SERVER['REMOTE_ADDR']
                );
                
                setFlash('success', 'Lançamento excluído com sucesso!');
            } else {
                setFlash('error', 'Erro ao excluir lançamento');
            }
        }
        
        redirect('/producao/lancamentos');
    }
    
    /**
     * Relatórios
     */
    public function relatorios() {
        requireAuth();
        
        if (!in_array($_SESSION['role'], ['admin', 'producao'])) {
            setFlash('error', 'Acesso negado');
            redirect('/departments');
        }
        
        $tipo = $_GET['tipo'] ?? 'dia';
        $limite = (int)($_GET['limite'] ?? 30);
        $produtoId = $_GET['produto_id'] ?? null;
        
        $relatorio = $this->producaoModel->getRelatorioPorPeriodo($tipo, $limite);
        $produtos = $this->producaoModel->getAllProdutos();
        
        // Se produto específico selecionado, buscar histórico
        $historico = null;
        if ($produtoId) {
            $historico = $this->producaoModel->getHistoricoProduto($produtoId, $limite);
        }
        
        $pageTitle = 'Relatórios de Produção';
        $flashMessages = getFlashMessages();
        
        include '../src/views/producao/relatorios.php';
    }
    
    /**
     * API - Buscar dados para gráficos
     */
    public function apiDados() {
                    // Antes de atualizar, validar se a nova produção é possível (estoque suficiente)
                    require_once __DIR__ . '/../models/ProductRecipe.php';
                    $recipeModel = new ProductRecipe();
                    $consumptionCheck = $recipeModel->calculateConsumption($data['produto_id'], $data['quantidade_produzida']);

                    if (!empty($consumptionCheck)) {
                        $shortages = [];
                        foreach ($consumptionCheck as $item) {
                            if (!$item['sufficient']) {
                                $shortages[] = "{$item['ingredient_name']}: necessário {$item['required_quantity']} {$item['unit']}, disponível {$item['available_quantity']} {$item['unit']}";
                            }
                        }

                        if (!empty($shortages)) {
                            setFlash('error', 'Estoque insuficiente para a nova configuração:<br>' . implode('<br>', $shortages));
                            redirect('/producao/lancamentos');
                        }
                    }
        requireAuth();
        
        header('Content-Type: application/json');
        
        $tipo = $_GET['tipo'] ?? 'dashboard';
        
        switch ($tipo) {
            case 'dashboard':
                $dados = $this->producaoModel->getDashboardHoje();
                break;
                
            case 'historico':
                $produtoId = $_GET['produto_id'] ?? null;
                $dias = $_GET['dias'] ?? 7;
                $dados = $this->producaoModel->getHistoricoProduto($produtoId, $dias);
                break;
                
            case 'relatorio':
                $periodo = $_GET['periodo'] ?? 'dia';
                $limite = $_GET['limite'] ?? 30;
                $dados = $this->producaoModel->getRelatorioPorPeriodo($periodo, $limite);
                break;
                
            default:
                $dados = [];
        }
        
        echo json_encode($dados);
        exit;
    }
}
?>
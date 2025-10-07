<?php
/**
 * Controller CRM
 * Web Aguaboa - Gestão Comercial
 */

class CrmController {
    private $clientModel;
    private $actionModel;
    private $activityLog;
    
    public function __construct() {
        $this->clientModel = new Client();
        $this->actionModel = new Action();
        $this->activityLog = new ActivityLog();
    }
    
    /**
     * Página principal do CRM
     */
    public function index() {
        requireAuth();
        
        // Parâmetros de paginação e busca
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;
        $search = sanitize($_GET['search'] ?? '');
        $sortBy = sanitize($_GET['sort'] ?? '');
        
        // Buscar clientes com estatísticas de envase
        $clients = $this->clientModel->findAllWithEnvaseStats($search, $sortBy, $page, $perPage);
        $totalClients = $this->clientModel->countAll($search, $sortBy);
        $totalPages = ceil($totalClients / $perPage);
        
        // Estatísticas
        $stats = $this->clientModel->getStats();
        
        // Log da atividade
        $this->activityLog->log(
            $_SESSION['user_id'],
            'VIEW_CLIENTS',
            "Visualizou página $page de clientes",
            $_SERVER['REMOTE_ADDR']
        );
        
        // Paginação
        $pagination = [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalClients,
            'per_page' => $perPage,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
            'prev_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < $totalPages ? $page + 1 : null
        ];
        
    $pageTitle = 'Página Inicial - Web Aguaboa';
        $flashMessages = getFlashMessages();
        
        include '../src/views/crm/index.php';
    }
    
    /**
     * Detalhes do cliente
     */
    public function clientDetail($clientId) {
        requireAuth();
        
        $client = $this->clientModel->findById($clientId);
        if (!$client) {
            setFlash('error', 'Cliente não encontrado!');
            redirect('/crm');
        }
        
        $actions = $this->actionModel->findByClient($clientId);
        $infos = $this->clientModel->getInfos($clientId);
        
        // Log da atividade
        $this->activityLog->log(
            $_SESSION['user_id'],
            'VIEW_CLIENT',
            "Visualizou cliente: {$client['cliente']}",
            $_SERVER['REMOTE_ADDR']
        );
        
        $pageTitle = "Cliente: {$client['cliente']} - Web Aguaboa";
        $flashMessages = getFlashMessages();
        
        include '../src/views/crm/client_detail.php';
    }
    
    /**
     * Criar novo cliente
     */
    public function createClient() {
        requireAuth();
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'cliente' => sanitize($_POST['nome']),
                'empresa' => sanitize($_POST['empresa'] ?? ''),
                'cidade' => sanitize($_POST['cidade'] ?? ''),
                'estado' => sanitize($_POST['estado'] ?? ''),
                'tipo_cliente' => sanitize($_POST['categoria'] ?? 'Normal'),
                'cliente_exclusivo' => ($_POST['exclusividade'] ?? '') === 'Exclusivo',
                'cliente_premium' => isset($_POST['premium']),
                'tipo_frete' => sanitize($_POST['tipo_frete'] ?? 'Próprio'),
                'freteiro_nome' => sanitize($_POST['freteiro_nome'] ?? '')
            ];
            
            if (empty($data['cliente'])) {
                setFlash('error', 'Nome do cliente é obrigatório!');
                redirect('/crm');
            }
            
            if ($this->clientModel->clienteExists($data['cliente'])) {
                setFlash('error', 'Cliente com este nome já existe!');
                redirect('/crm');
            }
            
            if ($this->clientModel->create($data)) {
                $this->activityLog->log(
                    $_SESSION['user_id'],
                    'CREATE_CLIENT',
                    "Cliente criado: {$data['cliente']}",
                    $_SERVER['REMOTE_ADDR']
                );
                
                setFlash('success', 'Cliente cadastrado com sucesso!');
            } else {
                setFlash('error', 'Erro ao cadastrar cliente!');
            }
        }
        
        redirect('/crm');
    }
    
    /**
     * Editar cliente
     */
    public function editClient($clientId) {
        requireAuth();
        requireAdmin();
        
        $client = $this->clientModel->findById($clientId);
        if (!$client) {
            setFlash('error', 'Cliente não encontrado!');
            redirect('/crm');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'cliente' => sanitize($_POST['nome']),
                'empresa' => sanitize($_POST['empresa'] ?? ''),
                'cidade' => sanitize($_POST['cidade'] ?? ''),
                'estado' => sanitize($_POST['estado'] ?? ''),
                'tipo_cliente' => sanitize($_POST['categoria'] ?? 'Normal'),
                'cliente_exclusivo' => ($_POST['exclusividade'] ?? '') === 'Exclusivo',
                'cliente_premium' => isset($_POST['premium']),
                'tipo_frete' => sanitize($_POST['tipo_frete'] ?? 'Próprio'),
                'freteiro_nome' => sanitize($_POST['freteiro_nome'] ?? '')
            ];
            
            if (empty($data['cliente'])) {
                setFlash('error', 'Nome do cliente é obrigatório!');
                redirect("/crm/edit-client/$clientId");
            }
            
            if ($this->clientModel->clienteExists($data['cliente'], $clientId)) {
                setFlash('error', 'Cliente com este nome já existe!');
                redirect("/crm/edit-client/$clientId");
            }
            
            if ($this->clientModel->update($clientId, $data)) {
                $this->activityLog->log(
                    $_SESSION['user_id'],
                    'EDIT_CLIENT',
                    "Cliente editado: {$data['cliente']}",
                    $_SERVER['REMOTE_ADDR']
                );
                
                setFlash('success', 'Cliente atualizado com sucesso!');
                redirect("/crm/client/$clientId");
            } else {
                setFlash('error', 'Erro ao atualizar cliente!');
            }
        }
        
        $pageTitle = "Editar Cliente: {$client['cliente']} - Web Aguaboa";
        $flashMessages = getFlashMessages();
        
        include '../src/views/crm/edit_client.php';
    }
    
    /**
     * Deletar cliente
     */
    public function deleteClient($clientId) {
        requireAuth();
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client = $this->clientModel->findById($clientId);
            if (!$client) {
                setFlash('error', 'Cliente não encontrado!');
                redirect('/crm');
            }
            
            if ($this->clientModel->delete($clientId)) {
                $this->activityLog->log(
                    $_SESSION['user_id'],
                    'DELETE_CLIENT',
                    "Cliente excluído: {$client['cliente']}",
                    $_SERVER['REMOTE_ADDR']
                );
                
                setFlash('success', 'Cliente excluído com sucesso!');
            } else {
                setFlash('error', 'Erro ao excluir cliente!');
            }
        }
        
        redirect('/crm');
    }
    
    /**
     * Identificar clientes duplicados/similares
     */
    public function unifyClients() {
        requireAuth();
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Verificar se é uma unificação automática
            if (isset($_GET['auto']) && $_GET['auto'] === '1') {
                $result = $this->clientModel->unifyAllDuplicates();
                
                if ($result['unified_count'] > 0) {
                    setFlash('success', "Unificação automática concluída! {$result['unified_count']} cliente(s) unificado(s) em {$result['groups_processed']} grupo(s).");
                } else {
                    setFlash('info', 'Nenhum cliente duplicado encontrado para unificação automática.');
                }
                
                if (!empty($result['errors'])) {
                    foreach ($result['errors'] as $error) {
                        setFlash('error', $error);
                    }
                }
                
                $this->activityLog->log(
                    $_SESSION['user_id'],
                    'AUTO_UNIFY_CLIENTS',
                    "Unificação automática: {$result['unified_count']} clientes unificados",
                    $_SERVER['REMOTE_ADDR']
                );
                
                redirect('/crm');
                return;
            }
            
            // Buscar clientes duplicados
            $duplicates = $this->clientModel->findDuplicateClients();
            
            $pageTitle = 'Unificar Clientes - Web Aguaboa';
            $flashMessages = getFlashMessages();
            
            include '../src/views/crm/unify_clients.php';
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clientsToMerge = $_POST['clients_to_merge'] ?? [];
            
            if (empty($clientsToMerge)) {
                setFlash('error', 'Nenhum cliente selecionado para unificação!');
                redirect('/crm/unify-clients');
            }
            
            // Buscar novamente os grupos duplicados para determinar o master de cada grupo
            $duplicates = $this->clientModel->findDuplicateClients();
            $merged = 0;
            $errors = [];
            
            // Processar cada grupo de duplicados
            foreach ($duplicates as $group) {
                // Encontrar qual cliente do grupo deve ser o master (primeiro do grupo = maior volume)
                $masterClient = $group[0];
                $masterClientId = $masterClient['id'];
                
                // Verificar quais clientes deste grupo foram selecionados para merge
                $groupClientsToMerge = [];
                foreach ($clientsToMerge as $clientId) {
                    foreach ($group as $client) {
                        if ($client['id'] == $clientId && $client['id'] != $masterClientId) {
                            $groupClientsToMerge[] = $clientId;
                            break;
                        }
                    }
                }
                
                // Processar unificação deste grupo
                foreach ($groupClientsToMerge as $clientId) {
                    $client = $this->clientModel->findById($clientId);
                    if (!$client) continue;
                    
                    // Unificar dados de envase
                    $this->clientModel->mergeEnvaseData($clientId, $masterClientId);
                    
                    // Unificar ações
                    $this->clientModel->mergeActions($clientId, $masterClientId);
                    
                    // Deletar cliente duplicado
                    if ($this->clientModel->delete($clientId)) {
                        $merged++;
                        
                        $this->activityLog->log(
                            $_SESSION['user_id'],
                            'UNIFY_CLIENTS',
                            "Cliente '{$client['cliente']}' unificado com '{$masterClient['cliente']}'",
                            $_SERVER['REMOTE_ADDR']
                        );
                    } else {
                        $errors[] = "Erro ao unificar cliente: {$client['cliente']}";
                    }
                }
            }
            
            if ($merged > 0) {
                setFlash('success', "$merged cliente(s) unificado(s) com sucesso!");
            }
            
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    setFlash('error', $error);
                }
            }
            
            if ($merged === 0 && empty($errors)) {
                setFlash('info', 'Nenhuma unificação foi processada.');
            }
            
            redirect('/crm');
        }
    }
    
    /**
     * Página de ações vigentes (com prazo de conclusão)
     */
    public function acoesVigentes() {
        requireAuth();
        
        // Buscar todas as ações que têm prazo de conclusão definido
        $acoesVigentes = $this->actionModel->findAcoesVigentes();
        
        // Buscar estatísticas das ações
        $stats = $this->actionModel->getStatsAcoesVigentes();
        
    $pageTitle = 'Ações Vigentes - Web Aguaboa';
    $flashMessages = getFlashMessages();
        
    // Render the CRM view into the main layout for consistent header/footer
    include '../src/views/crm/acoes_vigentes.php';
    $content = ob_get_clean() ?: null;
    include '../src/views/layouts/main.php';
    }
}
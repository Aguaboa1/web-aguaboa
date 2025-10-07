<?php
/**
 * Controller Actions
 * Web Aguaboa - Gestão Comercial
 */

class ActionsController {
    private $actionModel;
    private $clientModel;
    private $activityLog;
    
    public function __construct() {
        $this->actionModel = new Action();
        $this->clientModel = new Client();
        $this->activityLog = new ActivityLog();
    }
    
    /**
     * Criar nova ação
     */
    public function create() {
        requireAuth();
        
        // Definir header JSON apenas se não foi enviado ainda
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $clientId = (int)$_POST['client_id'];
                $descricao = sanitize($_POST['descricao']);
                $dataAcao = $_POST['data_acao'];
                $prazoConlusao = $_POST['prazo_conclusao'] ?? null;
                $arquivo = null;
                
                // Processar prazo de conclusão (converter de YYYY-MM para YYYY-MM-01)
                if ($prazoConlusao) {
                    $prazoConlusao = $prazoConlusao . '-01';
                }
                
                // Verificar se cliente existe
                $client = $this->clientModel->findById($clientId);
                if (!$client) {
                    echo json_encode(['success' => false, 'message' => 'Cliente não encontrado']);
                    return;
                }
                
                // Upload de arquivo se fornecido
                if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
                    $arquivo = $this->handleFileUpload($_FILES['arquivo']);
                    if (!$arquivo) {
                        echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo']);
                        return;
                    }
                }
                
                if ($this->actionModel->create($clientId, $descricao, $dataAcao, $arquivo, $prazoConlusao)) {
                    $this->activityLog->log(
                        $_SESSION['user_id'],
                        'CREATE_ACTION',
                        "Nova ação criada para cliente: {$client['cliente']}",
                        $_SERVER['REMOTE_ADDR']
                    );
                    
                    echo json_encode(['success' => true, 'message' => 'Ação criada com sucesso']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao criar ação no banco de dados']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
        }
    }
    
    /**
     * Buscar ação por ID
     */
    public function get($actionId) {
        // Limpar qualquer output anterior
        ob_clean();
        
        // Verificar autenticação
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Não autenticado']);
            exit;
        }
        
        // Definir header JSON apenas se não foi enviado ainda
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        
        $action = $this->actionModel->findById($actionId);
        if ($action) {
            echo json_encode($action);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Ação não encontrada']);
        }
        exit;
    }
    
    /**
     * Atualizar ação
     */
    public function update($actionId) {
        try {
            // Limpar qualquer output anterior
            if (ob_get_level()) {
                ob_clean();
            }
            
            // Verificar autenticação
            if (!isset($_SESSION['user_id'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }
            
            // Definir header JSON
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            
            // Verificar se a ação existe
            $action = $this->actionModel->findById($actionId);
            if (!$action) {
                echo json_encode(['success' => false, 'message' => 'Ação não encontrada']);
                exit;
            }
            
            // Validar dados obrigatórios
            if (empty($_POST['descricao'])) {
                echo json_encode(['success' => false, 'message' => 'Descrição é obrigatória']);
                exit;
            }
            
            if (empty($_POST['data_acao'])) {
                echo json_encode(['success' => false, 'message' => 'Data da ação é obrigatória']);
                exit;
            }
            
            // Processar dados
            $descricao = sanitize($_POST['descricao']);
            $dataAcao = $_POST['data_acao'];
            $prazoConlusao = $_POST['prazo_conclusao'] ?? null;
            $arquivo = null;
            
            // Processar prazo de conclusão (converter de YYYY-MM para YYYY-MM-01)
            if (!empty($prazoConlusao)) {
                // Validar formato do prazo
                if (preg_match('/^\d{4}-\d{2}$/', $prazoConlusao)) {
                    $prazoConlusao = $prazoConlusao . '-01';
                } else {
                    echo json_encode(['success' => false, 'message' => 'Formato de prazo inválido']);
                    exit;
                }
            }
            
            // Upload de arquivo se fornecido
            if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
                $arquivo = $this->handleFileUpload($_FILES['arquivo']);
                if (!$arquivo) {
                    echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo']);
                    exit;
                }
                
                // Se há um novo arquivo, deletar o antigo
                if ($action['arquivo']) {
                    $oldFilepath = UPLOAD_DIR . 'actions/' . $action['arquivo'];
                    if (file_exists($oldFilepath)) {
                        unlink($oldFilepath);
                    }
                }
            }
            
            // Executar update
            $resultado = $this->actionModel->update($actionId, $descricao, $dataAcao, $arquivo, $prazoConlusao);
            
            if ($resultado) {
                // Log da atividade
                $client = $this->clientModel->findById($action['client_id']);
                $this->activityLog->log(
                    $_SESSION['user_id'],
                    'UPDATE_ACTION',
                    "Ação atualizada para cliente: {$client['cliente']}",
                    $_SERVER['REMOTE_ADDR'] ?? 'localhost'
                );
                
                echo json_encode(['success' => true, 'message' => 'Ação atualizada com sucesso']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar ação no banco de dados']);
            }
            
        } catch (Exception $e) {
            // Log do erro para debug
            error_log("Erro no update da ação: " . $e->getMessage());
            
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
        
        exit;
    }
    
    /**
     * Deletar ação
     */
    public function delete($actionId) {
        requireAuth();
        
        // Definir header JSON apenas se não foi enviado ainda
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        
        try {
            $action = $this->actionModel->findById($actionId);
            if (!$action) {
                echo json_encode(['success' => false, 'message' => 'Ação não encontrada']);
                return;
            }
            
            if ($this->actionModel->delete($actionId)) {
                $client = $this->clientModel->findById($action['client_id']);
                $this->activityLog->log(
                    $_SESSION['user_id'],
                    'DELETE_ACTION',
                    "Ação deletada para cliente: {$client['cliente']}",
                    $_SERVER['REMOTE_ADDR']
                );
                
                echo json_encode(['success' => true, 'message' => 'Ação excluída com sucesso']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao deletar ação']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Handle file upload
     */
    private function handleFileUpload($file) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 
                        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }
        
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            return false;
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $uploadDir = UPLOAD_DIR . 'actions/';
        $filepath = $uploadDir . $filename;
        
        // Criar diretório se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filename;
        }
        
        return false;
    }
}
<?php
/**
 * API para operações de produção
 */

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../src/models/UserPermission.php';

// Verificar autenticação
requireAuth();

// Verificar permissão usando sistema moderno de departamentos
$userPermission = new UserPermission();
if (!$userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'edit')) {
    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Acesso negado. Você precisa de permissão de edição no departamento de produção.',
        'user' => $_SESSION['username'] ?? 'Desconhecido',
        'role' => $_SESSION['role'] ?? 'N/A'
    ]);
    exit;
}

require_once __DIR__ . '/../src/models/Producao.php';
$producaoModel = new Producao();

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_produto':
            $id = (int)($_GET['id'] ?? 0);
            
            if (empty($id)) {
                throw new Exception('ID do produto é obrigatório');
            }
            
            $produto = $producaoModel->getProdutoById($id);
            
            if (!$produto) {
                throw new Exception('Produto não encontrado');
            }
            
            echo json_encode([
                'success' => true,
                'produto' => $produto
            ]);
            break;
            
        default:
            throw new Exception('Ação não reconhecida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
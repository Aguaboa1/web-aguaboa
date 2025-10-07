<?php
/**
 * API para calcular consumo de insumos
 */

require_once '../config/init.php';
require_once '../src/models/ProductRecipe.php';
require_once '../src/models/UserPermission.php';

header('Content-Type: application/json');

// Verificar autenticação
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

// Verificar permissões
$userPermission = new UserPermission();
if (!$userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'view')) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

$productId = $_GET['product_id'] ?? 0;
$quantity = $_GET['quantity'] ?? 0;

if (!$productId || !$quantity) {
    echo json_encode(['error' => 'Parâmetros product_id e quantity são obrigatórios']);
    exit;
}

try {
    $recipeModel = new ProductRecipe();
    $consumption = $recipeModel->calculateConsumption($productId, $quantity);
    
    if (empty($consumption)) {
        echo json_encode([
            'has_recipe' => false,
            'message' => 'Nenhuma receita encontrada para este produto'
        ]);
        exit;
    }
    
    // Formatar dados para JSON
    $result = [
        'has_recipe' => true,
        'ingredients' => []
    ];
    
    foreach ($consumption as $item) {
        $result['ingredients'][] = [
            'name' => $item['ingredient_name'],
            'required_quantity' => number_format($item['required_quantity'], 2, ',', '.'),
            'unit' => $item['unit'],
            'available_quantity' => number_format($item['available_quantity'], 2, ',', '.'),
            'sufficient' => $item['sufficient'],
            'shortage' => $item['sufficient'] ? 0 : number_format($item['shortage'], 2, ',', '.')
        ];
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
<?php
require_once __DIR__ . '/../models/EstoqueInsumos.php';
require_once __DIR__ . '/../models/UserPermission.php';

class InsumosController {
    public function index() {
        requireAuth();
        $userPermission = new UserPermission();
        if (!$userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'view')) {
            setFlash('error', 'Acesso negado aos insumos');
            redirect('/departments');
        }

        $estoque = new EstoqueInsumos();
        $itens = $estoque->getAllItens();
        $estatisticas = $estoque->getEstatisticas();
        $flashMessages = $_SESSION['flash'] ?? [];
        require_once __DIR__ . '/../views/estoque_insumos.php';
    }

    public function create() {
        requireAuth();
        $userPermission = new UserPermission();
        if (!$userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'edit')) {
            setFlash('error', 'Acesso negado. Permissão de edição necessária.');
            redirect('/insumos');
        }

        $flashMessages = $_SESSION['flash'] ?? [];
        require_once __DIR__ . '/../views/insumos/create.php';
    }

    public function store() {
        requireAuth();
        $userPermission = new UserPermission();
        if (!$userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'edit')) {
            setFlash('error', 'Acesso negado. Permissão de edição necessária.');
            redirect('/insumos');
        }

        $estoque = new EstoqueInsumos();

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'category' => sanitize($_POST['category'] ?? 'Insumo'),
            'unit' => sanitize($_POST['unit'] ?? 'UN'),
            'unit_cost' => floatval($_POST['unit_cost'] ?? 0),
            'current_quantity' => floatval($_POST['current_quantity'] ?? 0),
            'minimum_stock' => floatval($_POST['minimum_stock'] ?? 0),
            'maximum_stock' => floatval($_POST['maximum_stock'] ?? 0),
            'location' => sanitize($_POST['location'] ?? '')
        ];

        try {
            if (empty($data['name'])) {
                throw new Exception('Nome do item é obrigatório');
            }

            if ($estoque->createItem($data)) {
                setFlash('success', 'Item criado com sucesso!');
            } else {
                setFlash('error', 'Falha ao criar item.');
            }
        } catch (Exception $e) {
            setFlash('error', $e->getMessage());
        }

        redirect('/insumos');
    }

    public function edit($id) {
        requireAuth();
        $userPermission = new UserPermission();
        if (!$userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'edit')) {
            setFlash('error', 'Acesso negado.');
            redirect('/insumos');
        }

        // reuse existing edit view
        $_GET['id'] = intval($id);
        require_once __DIR__ . '/../views/editar_item_estoque.php';
    }

    public function update($id) {
        requireAuth();
        $userPermission = new UserPermission();
        if (!$userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'edit')) {
            setFlash('error', 'Acesso negado.');
            redirect('/insumos');
        }

        $estoque = new EstoqueInsumos();

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'category' => sanitize($_POST['category'] ?? 'Insumo'),
            'unit' => sanitize($_POST['unit'] ?? 'UN'),
            'unit_cost' => floatval($_POST['unit_cost'] ?? 0),
            'current_quantity' => floatval($_POST['current_quantity'] ?? 0),
            'location' => sanitize($_POST['location'] ?? '')
        ];

        try {
            if (empty($data['name'])) throw new Exception('Nome do item é obrigatório');
            if ($estoque->updateItem(intval($id), $data)) {
                setFlash('success', 'Item atualizado com sucesso!');
            } else {
                setFlash('error', 'Erro ao atualizar item');
            }
        } catch (Exception $e) {
            setFlash('error', $e->getMessage());
        }

        redirect('/insumos');
    }

    public function delete($id) {
        requireAuth();
        $userPermission = new UserPermission();
        if (!$userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'edit')) {
            setFlash('error', 'Acesso negado.');
            redirect('/insumos');
        }

        $estoque = new EstoqueInsumos();
        if ($estoque->deleteItem(intval($id))) {
            setFlash('success', 'Item excluído com sucesso');
        } else {
            setFlash('error', 'Erro ao excluir item');
        }

        redirect('/insumos');
    }
}

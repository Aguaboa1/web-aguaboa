<?php
/**
 * Arquivo principal de entrada
 * Web Aguaboa - Gestão Comercial
 */

require_once __DIR__ . '/../config/init.php';

// Router simples
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Remover base URL
$path = str_replace(BASE_URL, '', $path);

// Suportar alias amigável: /comercial -> /crm (mantém a estrutura interna)
if (strpos($path, '/comercial') === 0) {
    // substitui apenas o prefixo /comercial para /crm, preservando possíveis subpaths
    $path = preg_replace('#^/comercial#', '/crm', $path);
}

// Rotas
try {
    switch (true) {
        // Autenticação
        case $path === '/' || $path === '':
            if (isset($_SESSION['user_id'])) {
                redirect('/departments');
            } else {
                redirect('/auth/login');
            }
            break;
            
        case $path === '/auth/login':
            $controller = new AuthController();
            $controller->login();
            break;
            
        case $path === '/auth/logout':
            $controller = new AuthController();
            $controller->logout();
            break;
            
        case $path === '/auth/change-password':
            $controller = new AuthController();
            $controller->changePassword();
            break;
            
        // Departamentos
        case $path === '/departments' || $path === '/departments/':
            $controller = new DepartmentController();
            $controller->select();
            break;
            
        case $path === '/financeiro' || $path === '/financeiro/':
            $controller = new DepartmentController();
            $controller->financeiro();
            break;
            
        case $path === '/rh' || $path === '/rh/':
            $controller = new DepartmentController();
            $controller->rh();
            break;
            
        case $path === '/qualidade' || $path === '/qualidade/':
            $controller = new DepartmentController();
            $controller->qualidade();
            break;
            
        case $path === '/atendimento' || $path === '/atendimento/':
            $controller = new DepartmentController();
            $controller->atendimento();
            break;
            
        case $path === '/producao' || $path === '/producao/':
            $controller = new DepartmentController();
            $controller->producao();
            break;
            
        // Controle de Estoque de Insumos
        case $path === '/estoque' || $path === '/estoque/':
            require_once __DIR__ . '/../src/views/estoque_insumos.php';
            break;
            
        case $path === '/estoque/processar':
            require_once __DIR__ . '/../processar_estoque.php';
            break;
            
        case preg_match('/^\/estoque\/editar\/(\d+)$/', $path, $matches):
            $_GET['id'] = $matches[1];
            require_once __DIR__ . '/../src/views/editar_item_estoque.php';
            break;
            
        // Receitas de Produção - recurso desativado (redireciona para Produção)
        case $path === '/receitas' || $path === '/receitas/':
            redirect('/producao');
            break;
            
        case $path === '/receitas/processar':
            // Processamento de receitas desativado
            redirect('/producao');
            break;
            
        // Administração do Sistema
        case $path === '/administracao' || $path === '/administracao/':
            require_once __DIR__ . '/../src/views/departments/administracao.php';
            break;
            
        // Funcionalidades específicas da administração removidas (usar rotas diretas /admin/*)
            
        // Acesso direto à administração com auto-login
        case $path === '/admin-direto' || $path === '/admin-direto/':
            // Fazer login automático se necessário
            if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT id, username, role FROM users WHERE username = 'Rogerio' LIMIT 1");
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_time'] = time();
                }
            }
            require_once __DIR__ . '/../src/views/departments/administracao.php';
            break;
            
        // Páginas de Produção
        case $path === '/producao/insumos' || $path === '/producao/insumos/':
            // Legacy produção insumos URL — redirect to canonical /insumos admin page
            redirect('/insumos');
            break;

        // Gestão de Insumos (CRUD)
        case $path === '/insumos' || $path === '/insumos/':
            $controller = new InsumosController();
            $controller->index();
            break;

        case $path === '/insumos/create':
            $controller = new InsumosController();
            $controller->create();
            break;

        case $path === '/insumos/store':
            $controller = new InsumosController();
            $controller->store();
            break;

        case preg_match('/^\/insumos\/edit\/(\d+)$/', $path, $matches):
            $controller = new InsumosController();
            $controller->edit($matches[1]);
            break;

        case preg_match('/^\/insumos\/update\/(\d+)$/', $path, $matches):
            $controller = new InsumosController();
            $controller->update($matches[1]);
            break;

        case preg_match('/^\/insumos\/delete\/(\d+)$/', $path, $matches):
            $controller = new InsumosController();
            $controller->delete($matches[1]);
            break;
            
        case $path === '/producao/lancamentos' || $path === '/producao/lancamentos/':
            $controller = new DepartmentController();
            $controller->producaoLancamentos();
            break;

        

        // Edição de lançamento via controller (substitui o antigo endpoint público)
        case preg_match('/^\/producao\/lancamento\/editar\/(\d+)$/', $path, $matches):
            $controller = new ProducaoController();
            $controller->editLancamento($matches[1]);
            break;
            
        case $path === '/producao/metas' || $path === '/producao/metas/':
            $controller = new DepartmentController();
            $controller->producaoMetas();
            break;
            
        // Relatórios de Produção
        case $path === '/relatorios' || $path === '/relatorios/':
            require_once __DIR__ . '/relatorios.php';
            break;
            
        // Acesso direto aos relatórios com auto-login
        case $path === '/relatorios-direto' || $path === '/relatorios-direto/':
            // Fazer login automático se necessário
            if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT id, username, role FROM users WHERE username = 'Rogerio' LIMIT 1");
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_time'] = time();
                }
            }
            require_once __DIR__ . '/relatorios.php';
            break;
            
        // Acesso direto ao relatório de consumo com auto-login
        case $path === '/relatorio-consumo-direto' || $path === '/relatorio-consumo-direto/':
            // Fazer login automático se necessário
            if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT id, username, role FROM users WHERE username = 'Rogerio' LIMIT 1");
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['login_time'] = time();
                }
            }
            require_once __DIR__ . '/relatorio_consumo.php';
            break;
            
        // Exportar Relatórios
        case $path === '/exportar_relatorio' || $path === '/exportar_relatorio/':
            require_once __DIR__ . '/exportar_relatorio.php';
            break;
            
        // CRM
        case $path === '/crm' || $path === '/crm/':
            $controller = new CrmController();
            $controller->index();
            break;
            
        case preg_match('/^\/crm\/client\/(\d+)$/', $path, $matches):
            $controller = new CrmController();
            $controller->clientDetail($matches[1]);
            break;
            
        case $path === '/crm/create-client':
            $controller = new CrmController();
            $controller->createClient();
            break;
            
        case preg_match('/^\/crm\/edit-client\/(\d+)$/', $path, $matches):
            $controller = new CrmController();
            $controller->editClient($matches[1]);
            break;
            
        case preg_match('/^\/crm\/delete-client\/(\d+)$/', $path, $matches):
            $controller = new CrmController();
            $controller->deleteClient($matches[1]);
            break;
            
        case $path === '/crm/unify-clients':
            $controller = new CrmController();
            $controller->unifyClients();
            break;
            
        case $path === '/crm/acoes-vigentes':
            $controller = new CrmController();
            $controller->acoesVigentes();
            break;

        case $path === '/crm/search-envase':
            // Page to search envase averages per client between date ranges
            include __DIR__ . '/../src/views/crm/search_envase.php';
            break;
            
        // Admin
        case $path === '/admin/users':
            $controller = new AdminController();
            $controller->users();
            break;
            
        case $path === '/admin/logs':
            $controller = new AdminController();
            $controller->logs();
            break;
            
        case $path === '/admin/configuracoes':
            include ROOT_PATH . '/src/views/admin/configuracoes.php';
            break;
            
        case $path === '/admin/create-user':
            $controller = new AdminController();
            $controller->createUser();
            break;
            
        case preg_match('/^\/admin\/edit-user\/(\d+)$/', $path, $matches):
            $controller = new AdminController();
            $controller->editUser($matches[1]);
            break;
            
        case preg_match('/^\/admin\/delete-user\/(\d+)$/', $path, $matches):
            $controller = new AdminController();
            $controller->deleteUser($matches[1]);
            break;
            
        case preg_match('/^\/admin\/toggle-user\/(\d+)$/', $path, $matches):
            $controller = new AdminController();
            $controller->toggleUser($matches[1]);
            break;
            
        case preg_match('/^\/admin\/manage-permissions\/(\d+)$/', $path, $matches):
            $controller = new AdminController();
            $controller->managePermissions($matches[1]);
            break;
            
        // Envase
        case $path === '/envase' || $path === '/envase/':
            $controller = new EnvaseController();
            $controller->dashboard();
            break;
            
        case $path === '/envase/upload':
            $controller = new EnvaseController();
            $controller->upload();
            break;
            
        case preg_match('/^\/envase\/cliente\/(\d+)$/', $path, $matches):
            try {
                $controller = new EnvaseController();
                $controller->clientData($matches[1]);
            } catch (Exception $e) {
                error_log("Erro no EnvaseController::clientData: " . $e->getMessage());
                echo "<h1>Erro</h1><p>Erro interno: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            break;
            
        case $path === '/envase/charts':
            $controller = new EnvaseController();
            $controller->charts();
            break;
            
        case preg_match('/^\/envase\/edit\/(\d+)$/', $path, $matches):
            $controller = new EnvaseController();
            $controller->editRecord($matches[1]);
            break;
            
        case preg_match('/^\/envase\/delete\/(\d+)$/', $path, $matches):
            $controller = new EnvaseController();
            $controller->deleteRecord($matches[1]);
            break;
            
        case $path === '/envase/limpar-dados':
            $controller = new EnvaseController();
            $controller->limparDados();
            break;
            
        case $path === '/envase/limpar-tudo':
            $controller = new EnvaseController();
            $controller->limparTudo();
            break;
            
        // Actions (ações do cliente)
        case $path === '/action':
            $controller = new ActionsController();
            $controller->create();
            break;
            
        case preg_match('/^\/action\/(\d+)$/', $path, $matches):
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $controller = new ActionsController();
                $controller->get($matches[1]);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller = new ActionsController();
                $controller->update($matches[1]);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                $controller = new ActionsController();
                $controller->update($matches[1]);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                $controller = new ActionsController();
                $controller->delete($matches[1]);
            }
            break;
            
        // Uploads
        case preg_match('/^\/uploads\/(.+)$/', $path, $matches):
            $filename = $matches[1];
            
            // Determinar o caminho correto baseado na estrutura
            if (strpos($filename, 'actions/') === 0) {
                // Arquivo de ação: uploads/actions/arquivo.ext
                $filepath = UPLOAD_DIR . $filename;
            } else {
                // Arquivo geral: uploads/arquivo.ext
                $filepath = UPLOAD_DIR . $filename;
            }
            
            if (file_exists($filepath)) {
                $mimeType = mime_content_type($filepath);
                header('Content-Type: ' . $mimeType);
                header('Content-Disposition: inline; filename="' . basename($filename) . '"');
                readfile($filepath);
            } else {
                http_response_code(404);
                echo 'Arquivo não encontrado: ' . $filepath;
            }
            break;
            
        // 404
        default:
            http_response_code(404);
            echo '<h1>404 - Página não encontrada</h1>';
            break;
    }
} catch (Exception $e) {
    error_log("Erro na aplicação: " . $e->getMessage());
    http_response_code(500);
    echo '<h1>500 - Erro interno do servidor</h1>';
    if (ini_get('display_errors')) {
        echo '<pre>' . $e->getMessage() . '</pre>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    }
}
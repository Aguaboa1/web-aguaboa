<?php
/**
 * Autoloader e configurações globais
 * Sistema Aguaboa - Gestão Comercial
 */

// Configurações de sessão
ini_set('session.gc_maxlifetime', 86400); // 24 horas
session_start();

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de erro
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoloader simples para classes
spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/../src/';
    
    // Mapear namespaces para diretórios
    $paths = [
        'models/' . $class . '.php',
        'controllers/' . $class . '.php',
        'utils/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        $file = $baseDir . $path;
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Incluir configuração do banco
require_once __DIR__ . '/../config/database.php';

// Definir constantes baseadas no servidor
if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] === 'localhost' && isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '8080') {
    // Servidor PHP embutido
    define('BASE_URL', '');
} else {
    // Apache/XAMPP
    define('BASE_URL', '/gestao-aguaboa-php/public');
}
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024); // 50MB

// Configurações globais
$GLOBALS['config'] = [
    'app_name' => 'Sistema Aguaboa - Gestão Comercial',
    'version' => '1.0',
    'secret_key' => 'aguaboa_crm_secret_key_2025_secure'
];

/**
 * Helper para redirecionamento
 */
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

/**
 * Helper para verificar autenticação
 */
function requireAuth() {
    // Verificar se existe sessão ativa
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        // Limpar qualquer sessão corrompida
        session_destroy();
        session_start();
        
        setFlash('info', 'Sessão expirada ou inválida. Faça login novamente.');
        redirect('/auth/login');
    }
    
    // Verificar se a sessão não expirou (24 horas)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 86400) {
        session_destroy();
        session_start();
        
        setFlash('warning', 'Sessão expirada por tempo. Faça login novamente.');
        redirect('/auth/login');
    }
}

/**
 * Helper para verificar se é admin
 */
function requireAdmin() {
    requireAuth();
    if ($_SESSION['role'] !== 'admin') {
        redirect('/departments');
    }
}

/**
 * Helper para flash messages
 */
function setFlash($type, $message) {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function getFlashMessages() {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/**
 * Helper para sanitizar dados
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Helper para verificar upload de arquivos
 */
function isAllowedFile($filename, $allowedExtensions = ['xls', 'xlsx', 'csv', 'html', 'htm']) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $allowedExtensions);
}

/**
 * Conexão global PDO para compatibilidade
 */
try {
    $pdo = Database::getInstance()->getConnection();
} catch (Exception $e) {
    // Conexão será criada quando necessário
    $pdo = null;
}
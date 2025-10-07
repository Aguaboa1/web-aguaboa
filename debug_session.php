<?php
require_once 'config/init.php';
require_once 'src/models/UserPermission.php';

echo "<h2>Debug - Sessão e Permissões</h2>";

echo "<h3>Dados da Sessão:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['user_id'])) {
    echo "<h3>Verificando Permissões:</h3>";
    $userPermission = new UserPermission();
    
    $canView = $userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'view');
    $canEdit = $userPermission->canAccessDepartment($_SESSION['user_id'], 'producao', 'edit');
    
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Username: " . ($_SESSION['username'] ?? 'N/A') . "<br>";
    echo "Role: " . ($_SESSION['role'] ?? 'N/A') . "<br>";
    echo "Pode visualizar produção: " . ($canView ? 'SIM' : 'NÃO') . "<br>";
    echo "Pode editar produção: " . ($canEdit ? 'SIM' : 'NÃO') . "<br>";
    
    echo "<h3>Todas as Permissões do Usuário:</h3>";
    $allPermissions = $userPermission->getUserPermissions($_SESSION['user_id']);
    echo "<pre>";
    print_r($allPermissions);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>Usuário não está logado!</p>";
    echo "<a href='public/login_auto.php'>Login automático como Rogerio</a>";
}
?>
<?php
require_once __DIR__ . '/config/init.php';
require_once __DIR__ . '/src/models/UserPermission.php';

requireAuth();

echo "<h1>Debug de Permissões</h1>";

echo "<h2>Usuário Atual:</h2>";
echo "<p><strong>ID:</strong> " . $_SESSION['user_id'] . "</p>";
echo "<p><strong>Username:</strong> " . $_SESSION['username'] . "</p>";
echo "<p><strong>Role:</strong> " . $_SESSION['role'] . "</p>";

echo "<h2>Teste de Permissões:</h2>";
$userPermission = new UserPermission();

$departments = ['administracao', 'producao', 'crm', 'envase'];
$permissions = ['view', 'edit'];

foreach ($departments as $dept) {
    echo "<h3>Departamento: $dept</h3>";
    foreach ($permissions as $perm) {
        $hasPermission = $userPermission->canAccessDepartment($_SESSION['user_id'], $dept, $perm);
        $status = $hasPermission ? '✅ SIM' : '❌ NÃO';
        echo "<p>$perm: $status</p>";
    }
}

echo "<h2>Permissões no Banco:</h2>";
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT d.name as department, udp.permission_level FROM user_department_permissions udp 
                      JOIN departments d ON udp.department_id = d.id 
                      WHERE udp.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($permissions)) {
    echo "<p>❌ Nenhuma permissão encontrada no banco!</p>";
} else {
    foreach ($permissions as $perm) {
        echo "<p><strong>{$perm['department']}:</strong> {$perm['permission_level']}</p>";
    }
}

?>
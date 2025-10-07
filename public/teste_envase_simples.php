<!DOCTYPE html>
<html>
<head>
    <title>Teste Envase Cliente</title>
</head>
<body>
    <h1>Teste Página Envase Cliente</h1>
    <p>Cliente ID: 4</p>
    <p>Esta é uma página de teste simplificada.</p>
    
    <?php
    // Testar dados básicos
    require_once '../config/init.php';
    
    $clientModel = new Client();
    $client = $clientModel->findById(4);
    
    if ($client) {
        echo "<h2>Cliente Encontrado:</h2>";
        echo "<p>Nome: " . htmlspecialchars($client['cliente']) . "</p>";
        echo "<p>Empresa: " . htmlspecialchars($client['empresa']) . "</p>";
        
        $envaseModel = new Envase();
        $envaseData = $envaseModel->findByEmpresa($client['empresa']);
        
        echo "<h2>Dados de Envase:</h2>";
        echo "<p>Registros encontrados: " . count($envaseData) . "</p>";
        
        if (!empty($envaseData)) {
            echo "<h3>Primeiros 5 registros:</h3>";
            echo "<table border='1'>";
            echo "<tr><th>Produto</th><th>Data</th><th>Quantidade</th></tr>";
            
            for ($i = 0; $i < min(5, count($envaseData)); $i++) {
                $record = $envaseData[$i];
                echo "<tr>";
                echo "<td>" . htmlspecialchars($record['produto']) . "</td>";
                echo "<td>" . sprintf('%02d/%02d/%d', $record['dia'], $record['mes'], $record['ano']) . "</td>";
                echo "<td>" . number_format($record['quantidade']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<p>Cliente não encontrado!</p>";
    }
    ?>
</body>
</html>
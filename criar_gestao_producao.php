<?php
/**
 * Criação das tabelas para Gestão de Produção
 * Sistema Aguaboa - Departamento de Produção
 */

require_once __DIR__ . '/config/init.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "🏭 CRIANDO SISTEMA DE GESTÃO DE PRODUÇÃO\n";
    echo "========================================\n\n";
    
    // 1. Tabela de produtos
    echo "1. 📦 Criando tabela de produtos...\n";
    $sql = "CREATE TABLE IF NOT EXISTS produtos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        codigo VARCHAR(50) UNIQUE,
        categoria VARCHAR(100),
        unidade_medida VARCHAR(20) DEFAULT 'UN',
        capacidade_litros DECIMAL(10,3),
        descricao TEXT,
        ativo BOOLEAN DEFAULT TRUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_codigo (codigo),
        INDEX idx_categoria (categoria),
        INDEX idx_ativo (ativo)
    )";
    $db->exec($sql);
    echo "   ✅ Tabela produtos criada\n";
    
    // 2. Tabela de lançamentos de produção
    echo "\n2. 📊 Criando tabela de lançamentos de produção...\n";
    $sql = "CREATE TABLE IF NOT EXISTS producao_lancamentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        produto_id INT NOT NULL,
        data_producao DATE NOT NULL,
        quantidade_produzida INT DEFAULT 0,
        quantidade_perdida INT DEFAULT 0,
        motivo_perda TEXT,
        observacoes TEXT,
        turno ENUM('MANHÃ', 'TARDE', 'NOITE') DEFAULT 'MANHÃ',
        operador_id INT,
        supervisor_id INT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
        FOREIGN KEY (operador_id) REFERENCES users(id),
        FOREIGN KEY (supervisor_id) REFERENCES users(id),
        INDEX idx_data_producao (data_producao),
        INDEX idx_produto_data (produto_id, data_producao),
        INDEX idx_turno (turno)
    )";
    $db->exec($sql);
    echo "   ✅ Tabela producao_lancamentos criada\n";
    
    // 3. Tabela de metas de produção
    echo "\n3. 🎯 Criando tabela de metas de produção...\n";
    $sql = "CREATE TABLE IF NOT EXISTS producao_metas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        produto_id INT NOT NULL,
        mes INT NOT NULL,
        ano INT NOT NULL,
        meta_mensal INT NOT NULL,
        meta_diaria INT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
        UNIQUE KEY unique_produto_mes_ano (produto_id, mes, ano),
        INDEX idx_mes_ano (mes, ano)
    )";
    $db->exec($sql);
    echo "   ✅ Tabela producao_metas criada\n";
    
    // 4. Inserir produtos padrão
    echo "\n4. 📦 Inserindo produtos padrão...\n";
    $produtos = [
        ['Água 500ml', 'AGU500', 'Água Mineral', 'UN', 0.5, 'Água mineral natural 500ml'],
        ['Água 1L', 'AGU1000', 'Água Mineral', 'UN', 1.0, 'Água mineral natural 1 litro'],
        ['Água 5L', 'AGU5000', 'Água Mineral', 'UN', 5.0, 'Água mineral natural 5 litros'],
        ['Água 10L', 'AGU10000', 'Água Mineral', 'UN', 10.0, 'Água mineral natural 10 litros'],
        ['Água 20L', 'AGU20000', 'Água Mineral', 'UN', 20.0, 'Água mineral natural 20 litros - Galão'],
    ];
    
    $stmt = $db->prepare("
        INSERT IGNORE INTO produtos (nome, codigo, categoria, unidade_medida, capacidade_litros, descricao) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($produtos as $produto) {
        $stmt->execute($produto);
        echo "   ✅ Produto: {$produto[0]} ({$produto[1]})\n";
    }
    
    // 5. Verificar se existe usuário de produção
    echo "\n5. 👥 Verificando usuários de produção...\n";
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'producao'");
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "   ✅ {$count} usuários de produção encontrados\n";
    } else {
        echo "   ⚠️ Nenhum usuário de produção encontrado\n";
    }
    
    // 6. Inserir dados de exemplo
    echo "\n6. 📊 Inserindo dados de exemplo (últimos 7 dias)...\n";
    $stmt = $db->prepare("
        INSERT IGNORE INTO producao_lancamentos 
        (produto_id, data_producao, quantidade_produzida, quantidade_perdida, turno, observacoes) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    for ($i = 6; $i >= 0; $i--) {
        $data = date('Y-m-d', strtotime("-{$i} days"));
        
        // Água 500ml
        $stmt->execute([1, $data, rand(800, 1200), rand(0, 50), 'MANHÃ', 'Produção normal']);
        $stmt->execute([1, $data, rand(700, 1000), rand(0, 30), 'TARDE', 'Produção normal']);
        
        // Água 1L
        $stmt->execute([2, $data, rand(500, 800), rand(0, 40), 'MANHÃ', 'Produção normal']);
        
        // Água 20L
        $stmt->execute([5, $data, rand(200, 400), rand(0, 20), 'MANHÃ', 'Produção galões']);
        
        echo "   ✅ Dados inseridos para {$data}\n";
    }
    
    // 7. Criar metas exemplo
    echo "\n7. 🎯 Inserindo metas de produção...\n";
    $mesAtual = (int)date('m');
    $anoAtual = (int)date('Y');
    
    $metas = [
        [1, $mesAtual, $anoAtual, 25000, 1000], // Água 500ml
        [2, $mesAtual, $anoAtual, 15000, 600],  // Água 1L
        [3, $mesAtual, $anoAtual, 5000, 200],   // Água 5L
        [4, $mesAtual, $anoAtual, 3000, 120],   // Água 10L
        [5, $mesAtual, $anoAtual, 8000, 320],   // Água 20L
    ];
    
    $stmt = $db->prepare("
        INSERT IGNORE INTO producao_metas (produto_id, mes, ano, meta_mensal, meta_diaria) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach ($metas as $meta) {
        $stmt->execute($meta);
    }
    echo "   ✅ Metas criadas para {$mesAtual}/{$anoAtual}\n";
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 SISTEMA DE GESTÃO DE PRODUÇÃO CRIADO!\n";
    echo "✅ Tabelas: produtos, producao_lancamentos, producao_metas\n";
    echo "✅ Produtos padrão cadastrados\n";
    echo "✅ Dados de exemplo inseridos\n";
    echo "✅ Metas configuradas\n";
    echo "\n🌐 Acesse: http://localhost/gestao-aguaboa-php/public/\n";
    echo "👤 Use usuários de produção: Producao, Supervisor, Operador\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
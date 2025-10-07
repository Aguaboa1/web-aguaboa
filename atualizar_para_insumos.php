<?php
/**
 * Atualizar produtos para insumos de produção
 * Sistema Aguaboa - Gestão de Produção
 */

require_once __DIR__ . '/config/init.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "🔄 ATUALIZANDO PRODUTOS PARA INSUMOS DE PRODUÇÃO\n";
    echo "================================================\n\n";
    
    // 1. Limpar produtos antigos (de água)
    echo "1. 🗑️ Removendo produtos antigos...\n";
    $db->exec("DELETE FROM produtos WHERE categoria = 'Água Mineral'");
    echo "   ✅ Produtos de água removidos\n";
    
    // 2. Inserir insumos reais
    echo "\n2. 📦 Inserindo insumos de produção...\n";
    $insumos = [
        // Lacres e tampas
        ['Lacre Aguaboa 500ml', 'LAC500', 'Lacres e Tampas', 'UN', 0.001, 'Lacre padrão para garrafas de 500ml'],
        ['Lacre Aguaboa 1L', 'LAC1000', 'Lacres e Tampas', 'UN', 0.001, 'Lacre padrão para garrafas de 1 litro'],
        ['Tampa Rosca 28mm', 'TAMPA28', 'Lacres e Tampas', 'UN', 0.001, 'Tampa de rosca 28mm para garrafas'],
        ['Tampa Rosca 38mm', 'TAMPA38', 'Lacres e Tampas', 'UN', 0.001, 'Tampa de rosca 38mm para galões'],
        
        // Embalagens
        ['Garrafa PET 500ml', 'GAR500', 'Embalagens', 'UN', 0.025, 'Garrafa PET transparente 500ml'],
        ['Garrafa PET 1L', 'GAR1000', 'Embalagens', 'UN', 0.040, 'Garrafa PET transparente 1 litro'],
        ['Garrafa PET 5L', 'GAR5000', 'Embalagens', 'UN', 0.120, 'Garrafa PET 5 litros'],
        ['Galão PC 20L', 'GAL20', 'Embalagens', 'UN', 0.850, 'Galão policarbonato 20 litros retornável'],
        
        // Rótulos e etiquetas
        ['Rótulo Aguaboa 500ml', 'ROT500', 'Rótulos', 'UN', 0.001, 'Rótulo adesivo para garrafa 500ml'],
        ['Rótulo Aguaboa 1L', 'ROT1000', 'Rótulos', 'UN', 0.001, 'Rótulo adesivo para garrafa 1L'],
        ['Etiqueta Galão 20L', 'ETQ20', 'Rótulos', 'UN', 0.002, 'Etiqueta para galão 20 litros'],
        
        // Materiais auxiliares
        ['Filme Plástico', 'FILME', 'Embalagem Secundária', 'M', 0.100, 'Filme plástico para paletização'],
        ['Caixa Papelão 12x500ml', 'CX12-500', 'Embalagem Secundária', 'UN', 0.150, 'Caixa para 12 garrafas de 500ml'],
        ['Caixa Papelão 6x1L', 'CX6-1000', 'Embalagem Secundária', 'UN', 0.200, 'Caixa para 6 garrafas de 1L'],
        
        // Químicos e tratamento
        ['Hipoclorito de Sódio', 'HIPO', 'Químicos', 'L', 1.000, 'Desinfetante para linha de produção'],
        ['Detergente Alcalino', 'DETALC', 'Químicos', 'L', 1.000, 'Detergente para limpeza de equipamentos'],
        ['Soda Cáustica', 'SODA', 'Químicos', 'KG', 1.000, 'Hidróxido de sódio para limpeza'],
    ];
    
    $stmt = $db->prepare("
        INSERT INTO produtos (nome, codigo, categoria, unidade_medida, capacidade_litros, descricao) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($insumos as $insumo) {
        $stmt->execute($insumo);
        echo "   ✅ {$insumo[0]} ({$insumo[1]})\n";
    }
    
    // 3. Atualizar lançamentos existentes para refletir uso de insumos
    echo "\n3. 📊 Limpando lançamentos antigos...\n";
    $db->exec("DELETE FROM producao_lancamentos");
    echo "   ✅ Lançamentos antigos removidos\n";
    
    // 4. Inserir exemplos de consumo de insumos
    echo "\n4. 📋 Inserindo exemplos de consumo de insumos...\n";
    
    // Buscar IDs dos insumos
    $stmt = $db->query("SELECT id, nome, codigo FROM produtos ORDER BY id");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($produtos)) {
        $stmt = $db->prepare("
            INSERT INTO producao_lancamentos 
            (produto_id, data_producao, quantidade_produzida, quantidade_perdida, turno, observacoes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        // Exemplos para os últimos 3 dias
        for ($i = 2; $i >= 0; $i--) {
            $data = date('Y-m-d', strtotime("-{$i} days"));
            
            // Lacres (produção de 1000 garrafas de 500ml)
            $stmt->execute([1, $data, 1000, 15, 'MANHÃ', 'Consumo para produção 500ml']);
            
            // Garrafas 500ml
            $stmt->execute([5, $data, 1000, 20, 'MANHÃ', 'Garrafas utilizadas na produção']);
            
            // Rótulos 500ml
            $stmt->execute([9, $data, 1000, 10, 'MANHÃ', 'Rótulos aplicados']);
            
            // Caixas (1000 garrafas = 83 caixas aproximadamente)
            $stmt->execute([13, $data, 83, 2, 'TARDE', 'Embalagem secundária']);
            
            echo "   ✅ Lançamentos criados para {$data}\n";
        }
    }
    
    // 5. Atualizar metas para insumos
    echo "\n5. 🎯 Atualizando metas para insumos...\n";
    $db->exec("DELETE FROM producao_metas");
    
    $mesAtual = (int)date('m');
    $anoAtual = (int)date('Y');
    
    $metas = [
        [1, $mesAtual, $anoAtual, 25000, 1000], // Lacres 500ml
        [5, $mesAtual, $anoAtual, 25000, 1000], // Garrafas 500ml
        [9, $mesAtual, $anoAtual, 25000, 1000], // Rótulos 500ml
        [13, $mesAtual, $anoAtual, 2100, 85],   // Caixas
    ];
    
    $stmt = $db->prepare("
        INSERT INTO producao_metas (produto_id, mes, ano, meta_mensal, meta_diaria) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach ($metas as $meta) {
        $stmt->execute($meta);
    }
    echo "   ✅ Metas de consumo atualizadas\n";
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 SISTEMA ATUALIZADO PARA INSUMOS DE PRODUÇÃO!\n";
    echo "✅ " . count($insumos) . " insumos cadastrados\n";
    echo "✅ Categorias: Lacres e Tampas, Embalagens, Rótulos, Químicos\n";
    echo "✅ Lançamentos de exemplo criados\n";
    echo "✅ Metas de consumo configuradas\n";
    echo "\n📋 Agora o sistema gerencia:\n";
    echo "• Consumo de lacres e tampas\n";
    echo "• Uso de embalagens (garrafas, galões)\n";
    echo "• Aplicação de rótulos\n";
    echo "• Consumo de materiais auxiliares\n";
    echo "• Uso de produtos químicos\n";
    echo "\n🌐 Acesse: http://localhost/gestao-aguaboa-php/public/\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
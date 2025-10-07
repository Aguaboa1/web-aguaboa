<?php
/**
 * Corrigir lançamentos de insumos
 */

require_once __DIR__ . '/config/init.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "🔧 CORRIGINDO LANÇAMENTOS DE INSUMOS\n";
    echo "====================================\n\n";
    
    // Buscar os produtos cadastrados
    $stmt = $db->query("SELECT id, nome, codigo FROM produtos ORDER BY id");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📦 Produtos disponíveis:\n";
    foreach ($produtos as $produto) {
        echo "   ID {$produto['id']}: {$produto['nome']} ({$produto['codigo']})\n";
    }
    
    if (!empty($produtos)) {
        echo "\n📊 Criando lançamentos de exemplo...\n";
        
        $stmt = $db->prepare("
            INSERT INTO producao_lancamentos 
            (produto_id, data_producao, quantidade_produzida, quantidade_perdida, turno, observacoes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        // Usar os IDs reais dos produtos
        $lacreId = null;
        $garrafaId = null;
        $rotuloId = null;
        $caixaId = null;
        
        foreach ($produtos as $produto) {
            if (strpos($produto['codigo'], 'LAC500') !== false) $lacreId = $produto['id'];
            if (strpos($produto['codigo'], 'GAR500') !== false) $garrafaId = $produto['id'];
            if (strpos($produto['codigo'], 'ROT500') !== false) $rotuloId = $produto['id'];
            if (strpos($produto['codigo'], 'CX12-500') !== false) $caixaId = $produto['id'];
        }
        
        // Criar lançamentos para os últimos 3 dias
        for ($i = 2; $i >= 0; $i--) {
            $data = date('Y-m-d', strtotime("-{$i} days"));
            
            if ($lacreId) {
                $stmt->execute([$lacreId, $data, 1000, 15, 'MANHÃ', 'Consumo para produção 500ml']);
                echo "   ✅ Lacres: 1000 consumidos, 15 perdidos em {$data}\n";
            }
            
            if ($garrafaId) {
                $stmt->execute([$garrafaId, $data, 1000, 20, 'MANHÃ', 'Garrafas utilizadas na produção']);
                echo "   ✅ Garrafas: 1000 consumidas, 20 perdidas em {$data}\n";
            }
            
            if ($rotuloId) {
                $stmt->execute([$rotuloId, $data, 1000, 10, 'MANHÃ', 'Rótulos aplicados']);
                echo "   ✅ Rótulos: 1000 aplicados, 10 perdidos em {$data}\n";
            }
            
            if ($caixaId) {
                $stmt->execute([$caixaId, $data, 83, 2, 'TARDE', 'Embalagem secundária']);
                echo "   ✅ Caixas: 83 consumidas, 2 perdidas em {$data}\n";
            }
        }
        
        echo "\n🎯 Atualizando metas com IDs corretos...\n";
        $db->exec("DELETE FROM producao_metas");
        
        $mesAtual = (int)date('m');
        $anoAtual = (int)date('Y');
        
        $metaStmt = $db->prepare("
            INSERT INTO producao_metas (produto_id, mes, ano, meta_mensal, meta_diaria) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if ($lacreId) {
            $metaStmt->execute([$lacreId, $mesAtual, $anoAtual, 25000, 1000]);
            echo "   ✅ Meta para lacres: 25.000/mês\n";
        }
        
        if ($garrafaId) {
            $metaStmt->execute([$garrafaId, $mesAtual, $anoAtual, 25000, 1000]);
            echo "   ✅ Meta para garrafas: 25.000/mês\n";
        }
        
        if ($rotuloId) {
            $metaStmt->execute([$rotuloId, $mesAtual, $anoAtual, 25000, 1000]);
            echo "   ✅ Meta para rótulos: 25.000/mês\n";
        }
        
        if ($caixaId) {
            $metaStmt->execute([$caixaId, $mesAtual, $anoAtual, 2100, 85]);
            echo "   ✅ Meta para caixas: 2.100/mês\n";
        }
    }
    
    echo "\n✅ SISTEMA DE INSUMOS CONFIGURADO!\n";
    echo "📋 Agora o sistema gerencia consumo de insumos de produção\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
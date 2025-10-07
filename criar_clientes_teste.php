<?php
/**
 * Script para criar clientes de teste para funcionalidade de unificação
 * Sistema Aguaboa - Gestão Comercial
 */

require_once 'config/init.php';

echo "<h1>🧪 Criar Clientes de Teste para Unificação</h1>";

try {
    $clientModel = new Client();
    
    echo "<h2>📝 Criando clientes de exemplo...</h2>";
    
    // Clientes de teste que deveriam ser detectados como duplicados
    $testClients = [
        // Grupo EMBU DISTR
        [
            'cliente' => 'EMBU DISTR (ADEGA TROPICAL)',
            'empresa' => 'EMBU DISTR (ADEGA TROPICAL)',
            'cidade' => 'SAO PAULO',
            'tipo_cliente' => 'Normal'
        ],
        [
            'cliente' => 'EMBU DISTR (BELLA FONTE)',
            'empresa' => 'EMBU DISTR (BELLA FONTE)',
            'cidade' => 'OSASCO',
            'tipo_cliente' => 'Normal'
        ],
        [
            'cliente' => 'EMBU DISTR (CACH DO MANDAQUI)',
            'empresa' => 'EMBU DISTR (CACH DO MANDAQUI)',
            'cidade' => 'SAO PAULO',
            'tipo_cliente' => 'Normal'
        ],
        [
            'cliente' => 'EMBU DISTR (CASA DA AGUA)',
            'empresa' => 'EMBU DISTR (CASA DA AGUA)',
            'cidade' => 'OSASCO',
            'tipo_cliente' => 'Normal'
        ],
        // Grupo ENTREPOSTO
        [
            'cliente' => 'ENTREPOSTO (MA AGUA M S VICENTE)',
            'empresa' => 'ENTREPOSTO (MA AGUA M S VICENTE)',
            'cidade' => 'SAO PAULO',
            'tipo_cliente' => 'Normal'
        ],
        [
            'cliente' => 'ENTREPOSTO (VITORIA AGUAS)',
            'empresa' => 'ENTREPOSTO (VITORIA AGUAS)',
            'cidade' => 'SAO PAULO',
            'tipo_cliente' => 'Normal'
        ],
        // Duplicados simples
        [
            'cliente' => 'AGUA PURA TATUI',
            'empresa' => 'AGUA PURA TATUI',
            'cidade' => 'TATUI',
            'tipo_cliente' => 'Normal'
        ],
        [
            'cliente' => 'AGUA PURA TATUI 2',
            'empresa' => 'AGUA PURA TATUI',
            'cidade' => 'TATUI',
            'tipo_cliente' => 'Normal'
        ],
        [
            'cliente' => 'DISTRIBUIDORA CENTRO',
            'empresa' => 'DISTRIBUIDORA CENTRO LTDA',
            'cidade' => 'SAO PAULO',
            'tipo_cliente' => 'Normal'
        ],
        [
            'cliente' => 'DISTRIBUIDORA CENTRO LTDA',
            'empresa' => 'DISTRIBUIDORA CENTRO LTDA',
            'cidade' => 'SAO PAULO',
            'tipo_cliente' => 'Normal'
        ]
    ];
    
    $created = 0;
    $skipped = 0;
    
    echo "<div style='background: #f8f9fa; padding: 1rem; border-radius: 6px;'>";
    echo "<h4>Status da criação:</h4>";
    echo "<ul>";
    
    foreach ($testClients as $clientData) {
        // Verificar se cliente já existe
        if ($clientModel->clienteExists($clientData['cliente'])) {
            echo "<li>⏭️ <strong>" . htmlspecialchars($clientData['cliente']) . "</strong> - já existe</li>";
            $skipped++;
            continue;
        }
        
        // Criar cliente
        $data = [
            'cliente' => $clientData['cliente'],
            'empresa' => $clientData['empresa'],
            'cidade' => $clientData['cidade'],
            'estado' => 'SP',
            'tipo_cliente' => $clientData['tipo_cliente'],
            'cliente_exclusivo' => false,
            'cliente_premium' => false,
            'tipo_frete' => 'Próprio',
            'freteiro_nome' => null
        ];
        
        if ($clientModel->create($data)) {
            echo "<li>✅ <strong>" . htmlspecialchars($clientData['cliente']) . "</strong> - criado com sucesso</li>";
            $created++;
        } else {
            echo "<li>❌ <strong>" . htmlspecialchars($clientData['cliente']) . "</strong> - erro ao criar</li>";
        }
    }
    
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #e7f3ff; padding: 1rem; border-radius: 6px; margin: 1rem 0; border-left: 4px solid #007fa3;'>";
    echo "<h3>📊 Resumo</h3>";
    echo "<ul>";
    echo "<li><strong>Clientes criados:</strong> $created</li>";
    echo "<li><strong>Clientes já existentes:</strong> $skipped</li>";
    echo "<li><strong>Total de clientes de teste:</strong> " . count($testClients) . "</li>";
    echo "</ul>";
    echo "</div>";
    
    if ($created > 0) {
        echo "<div style='background: #d1ecf1; padding: 1rem; border-radius: 6px; margin: 1rem 0; border-left: 4px solid #bee5eb;'>";
        echo "<h4>✅ Clientes de teste criados com sucesso!</h4>";
        echo "<p>Agora você pode testar a funcionalidade de unificação:</p>";
        echo "<ol>";
        echo "<li>Acesse a página de <a href='" . BASE_URL . "/teste_unificacao_avancado.php'>Teste Avançado</a></li>";
        echo "<li>Ou vá direto para <a href='" . BASE_URL . "/crm/unify-clients'>Unificar Clientes</a></li>";
        echo "</ol>";
        echo "</div>";
    }
    
    // Testar detecção de duplicados
    echo "<h2>🔍 Testando detecção de duplicados...</h2>";
    
    $duplicates = $clientModel->findDuplicateClients();
    
    if (empty($duplicates)) {
        echo "<div style='background: #fff3cd; padding: 1rem; border-radius: 6px; color: #856404;'>";
        echo "<h4>⚠️ Nenhum duplicado detectado</h4>";
        echo "<p>Isso pode significar que:</p>";
        echo "<ul>";
        echo "<li>Os clientes já foram unificados anteriormente</li>";
        echo "<li>O algoritmo precisa ser ajustado</li>";
        echo "<li>Não há clientes similares suficientes</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 1rem; border-radius: 6px; color: #155724;'>";
        echo "<h4>✅ Duplicados detectados!</h4>";
        echo "<p>Foram encontrados <strong>" . count($duplicates) . " grupo(s)</strong> de clientes similares.</p>";
        echo "<p><a href='" . BASE_URL . "/teste_unificacao_avancado.php' class='btn' style='background: #28a745; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px;'>📊 Ver Análise Completa</a></p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 1rem; border-radius: 6px; color: #721c24;'>";
    echo "<h3>❌ Erro</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='" . BASE_URL . "/crm'>← Voltar para o CRM</a></p>";
?>
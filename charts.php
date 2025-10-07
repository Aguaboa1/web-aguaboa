<?php ob_start(); ?>

<style>
.evolution-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 0;
    margin: 0;
}

.evolution-header {
    background: #17a2b8;
    color: white;
    padding: 1rem 2rem;
    margin-bottom: 2rem;
}

.evolution-header h1 {
    margin: 0 0 1rem 0;
    font-size: 1.5rem;
    font-weight: normal;
}

.header-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.header-btn {
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    padding: 0.5rem 1rem;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.9rem;
}

.header-btn:hover {
    background: rgba(255,255,255,0.3);
    color: white;
    text-decoration: none;
}

.filter-section {
    background: white;
    padding: 1.5rem 2rem;
    margin-bottom: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filter-section h3 {
    color: #17a2b8;
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #495057;
}

.form-group select,
.form-group input {
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.9rem;
}

.btn-calculate {
    background: #17a2b8;
    color: white;
    border: none;
    padding: 0.6rem 1.5rem;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
}

.btn-calculate:hover {
    background: #138496;
}

.results-section {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.results-header {
    background: #f8f9fa;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e9ecef;
    border-radius: 8px 8px 0 0;
}

.results-title {
    color: #17a2b8;
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.results-content {
    padding: 1.5rem;
}

.results-description {
    margin-bottom: 1.5rem;
    color: #666;
    font-size: 0.95rem;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    margin-bottom: 2rem;
}

.data-table th {
    background: #f8f9fa;
    padding: 0.8rem;
    text-align: left;
    border-bottom: 2px solid #e9ecef;
    font-weight: 600;
    color: #495057;
}

.data-table td {
    padding: 0.8rem;
    border-bottom: 1px solid #e9ecef;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.evolution-positive {
    color: #28a745;
    font-weight: bold;
}

.evolution-negative {
    color: #dc3545;
    font-weight: bold;
}

.chart-container {
    background: linear-gradient(145deg, #ffffff, #f0f0f0);
    padding: 2rem;
    border-radius: 15px;
    margin-top: 2rem;
    box-shadow: 
        0 10px 25px rgba(0,0,0,0.1),
        inset 0 1px 0 rgba(255,255,255,0.6);
    border: 1px solid rgba(255,255,255,0.2);
}

.chart-bars {
    display: flex;
    align-items: end;
    justify-content: center;
    height: 350px;
    padding: 40px 20px 60px 20px;
    border-bottom: 3px solid #ddd;
    position: relative;
    gap: 30px;
    background: linear-gradient(to bottom, rgba(23,162,184,0.05) 0%, transparent 100%);
}

.chart-bar {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    min-width: 80px;
    transition: transform 0.3s ease;
}

.chart-bar:hover {
    transform: translateY(-5px);
}

.bar-group {
    display: flex;
    align-items: end;
    gap: 8px;
    height: 280px;
}

.bar-envase {
    background: linear-gradient(145deg, #17a2b8, #138496);
    width: 35px;
    border-radius: 8px 8px 0 0;
    position: relative;
    min-height: 5px;
    transition: all 0.3s ease;
    cursor: pointer;
    box-shadow: 
        0 4px 8px rgba(23,162,184,0.3),
        inset 0 1px 0 rgba(255,255,255,0.3),
        inset 0 -1px 0 rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.2);
}

.bar-envase:hover {
    background: linear-gradient(145deg, #138496, #0f6674);
    box-shadow: 
        0 8px 16px rgba(23,162,184,0.4),
        inset 0 1px 0 rgba(255,255,255,0.4),
        inset 0 -1px 0 rgba(0,0,0,0.2);
    transform: scale(1.05);
}

.bar-media {
    background: linear-gradient(145deg, #ffc107, #e0a800);
    width: 25px;
    border-radius: 8px 8px 0 0;
    position: relative;
    min-height: 5px;
    transition: all 0.3s ease;
    cursor: pointer;
    box-shadow: 
        0 4px 8px rgba(255,193,7,0.3),
        inset 0 1px 0 rgba(255,255,255,0.3),
        inset 0 -1px 0 rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.2);
}

.bar-media:hover {
    background: linear-gradient(145deg, #e0a800, #c69500);
    box-shadow: 
        0 8px 16px rgba(255,193,7,0.4),
        inset 0 1px 0 rgba(255,255,255,0.4),
        inset 0 -1px 0 rgba(0,0,0,0.2);
    transform: scale(1.05);
}

.bar-value {
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 10px;
    font-weight: bold;
    color: #333;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.bar-envase:hover .bar-value,
.bar-media:hover .bar-value {
    opacity: 1;
}

.bar-label {
    margin-top: 15px;
    font-size: 12px;
    text-align: center;
    font-weight: bold;
    color: #495057;
    transform: rotate(-20deg);
    transform-origin: center;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.chart-legend {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-top: 2rem;
    font-size: 0.9rem;
    padding: 1rem;
    background: rgba(255,255,255,0.5);
    border-radius: 10px;
    backdrop-filter: blur(10px);
}

/* Tooltip personalizado */
.custom-tooltip {
    position: absolute;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 13px;
    line-height: 1.4;
    box-shadow: 
        0 10px 40px rgba(0, 0, 0, 0.2),
        0 5px 20px rgba(102, 126, 234, 0.3),
        inset 0 1px 2px rgba(255, 255, 255, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.2);
    z-index: 1000;
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    min-width: 200px;
    text-align: left;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    display: none;
    pointer-events: none;
}

.custom-tooltip::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-top: 8px solid #667eea;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.custom-tooltip.show {
    opacity: 1;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 2px;
}
</style>

<div class="evolution-container">
    <!-- Header -->
    <div class="evolution-header">
        <h1>📈 Evolução de Envase<?php echo $client ? ' - ' . htmlspecialchars($client['cliente']) : ''; ?></h1>
        <div class="header-buttons">
            <a href="<?php echo $client ? BASE_URL . '/crm/client/' . $client['id'] : BASE_URL . '/envase'; ?>" class="header-btn">
                ← Voltar
            </a>
            <a href="<?= BASE_URL ?>/envase" class="header-btn">
                📊 Relatórios
            </a>
            <a href="<?= BASE_URL ?>/crm" class="header-btn">
                👥 Por Empresas
            </a>
        </div>
    </div>

    <div style="max-width: 1400px; margin: 0 auto; padding: 0 2rem;">
        <!-- Filtros -->
        <div class="filter-section">
            <h3>🔧 Selecionar Período para Comparar</h3>
            
            <form method="GET" class="filter-form">
                <?php if ($client): ?>
                    <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Mês Inicial</label>
                    <select name="mes_inicial">
                        <option value="1" <?= ($_GET['mes_inicial'] ?? '1') == '1' ? 'selected' : '' ?>>Janeiro</option>
                        <option value="2" <?= ($_GET['mes_inicial'] ?? '1') == '2' ? 'selected' : '' ?>>Fevereiro</option>
                        <option value="3" <?= ($_GET['mes_inicial'] ?? '1') == '3' ? 'selected' : '' ?>>Março</option>
                        <option value="4" <?= ($_GET['mes_inicial'] ?? '1') == '4' ? 'selected' : '' ?>>Abril</option>
                        <option value="5" <?= ($_GET['mes_inicial'] ?? '1') == '5' ? 'selected' : '' ?>>Maio</option>
                        <option value="6" <?= ($_GET['mes_inicial'] ?? '1') == '6' ? 'selected' : '' ?>>Junho</option>
                        <option value="7" <?= ($_GET['mes_inicial'] ?? '1') == '7' ? 'selected' : '' ?>>Julho</option>
                        <option value="8" <?= ($_GET['mes_inicial'] ?? '1') == '8' ? 'selected' : '' ?>>Agosto</option>
                        <option value="9" <?= ($_GET['mes_inicial'] ?? '1') == '9' ? 'selected' : '' ?>>Setembro</option>
                        <option value="10" <?= ($_GET['mes_inicial'] ?? '1') == '10' ? 'selected' : '' ?>>Outubro</option>
                        <option value="11" <?= ($_GET['mes_inicial'] ?? '1') == '11' ? 'selected' : '' ?>>Novembro</option>
                        <option value="12" <?= ($_GET['mes_inicial'] ?? '1') == '12' ? 'selected' : '' ?>>Dezembro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Mês Final</label>
                    <select name="mes_final">
                        <option value="1" <?= ($_GET['mes_final'] ?? '8') == '1' ? 'selected' : '' ?>>Janeiro</option>
                        <option value="2" <?= ($_GET['mes_final'] ?? '8') == '2' ? 'selected' : '' ?>>Fevereiro</option>
                        <option value="3" <?= ($_GET['mes_final'] ?? '8') == '3' ? 'selected' : '' ?>>Março</option>
                        <option value="4" <?= ($_GET['mes_final'] ?? '8') == '4' ? 'selected' : '' ?>>Abril</option>
                        <option value="5" <?= ($_GET['mes_final'] ?? '8') == '5' ? 'selected' : '' ?>>Maio</option>
                        <option value="6" <?= ($_GET['mes_final'] ?? '8') == '6' ? 'selected' : '' ?>>Junho</option>
                        <option value="7" <?= ($_GET['mes_final'] ?? '8') == '7' ? 'selected' : '' ?>>Julho</option>
                        <option value="8" <?= ($_GET['mes_final'] ?? '8') == '8' ? 'selected' : '' ?>>Agosto</option>
                        <option value="9" <?= ($_GET['mes_final'] ?? '8') == '9' ? 'selected' : '' ?>>Setembro</option>
                        <option value="10" <?= ($_GET['mes_final'] ?? '8') == '10' ? 'selected' : '' ?>>Outubro</option>
                        <option value="11" <?= ($_GET['mes_final'] ?? '8') == '11' ? 'selected' : '' ?>>Novembro</option>
                        <option value="12" <?= ($_GET['mes_final'] ?? '8') == '12' ? 'selected' : '' ?>>Dezembro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Ano Inicial</label>
                    <input type="number" name="ano_inicial" value="<?= $_GET['ano_inicial'] ?? '2020' ?>" min="2015" max="2030">
                </div>
                
                <div class="form-group">
                    <label>Ano Final</label>
                    <input type="number" name="ano_final" value="<?= $_GET['ano_final'] ?? '2025' ?>" min="2015" max="2030">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-calculate">Calcular Evolução</button>
                </div>
            </form>
        </div>

        <!-- Resultados -->
        <?php 
        $mes_inicial = $_GET['mes_inicial'] ?? 1;
        $mes_final = $_GET['mes_final'] ?? 8;
        $ano_inicial = $_GET['ano_inicial'] ?? 2020;
        $ano_final = $_GET['ano_final'] ?? 2025;
        
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        
        $empresa_nome = $client ? $client['empresa'] : 'GAS DO LU PARANA';
        ?>
        
        <div class="results-section">
            <div class="results-header">
                <h3 class="results-title">📊 Resultados</h3>
            </div>
            <div class="results-content">
                <div class="results-description">
                    Comparando os meses de <?= $meses[$mes_inicial] ?> a <?= $meses[$mes_final] ?> para os anos de <?= $ano_inicial ?> a <?= $ano_final ?> da empresa <strong><?= strtoupper($empresa_nome) ?></strong>.
                    
                    <!-- Debug info -->
                    <?php if (isset($summary) && !empty($summary)): ?>
                        <br><small style="color: #666;">
                            Debug: <?= count($summary['yearly_evolution'] ?? []) ?> anos de dados encontrados.
                        </small>
                    <?php else: ?>
                        <br><small style="color: #dc3545;">
                            Debug: Nenhum dado de summary encontrado.
                        </small>
                    <?php endif; ?>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ano</th>
                            <th style="text-align: right;">Total de Envase</th>
                            <th style="text-align: right;">Média por Mês</th>
                            <th style="text-align: right;">Variação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (isset($summary) && !empty($summary['yearly_evolution'])):
                            $evolution_data = [];
                            foreach ($summary['yearly_evolution'] as $year_data):
                                if ($year_data['ano'] >= $ano_inicial && $year_data['ano'] <= $ano_final):
                                    // Filtrar registros por mês
                                    $filtered_quantity = 0;
                                    $months_count = 0;
                                    
                                    foreach ($year_data['registros'] as $registro) {
                                        if ($registro['mes'] >= $mes_inicial && $registro['mes'] <= $mes_final) {
                                            $filtered_quantity += $registro['quantidade'];
                                        }
                                    }
                                    
                                    $unique_months = array_unique(array_map(function($r) use ($mes_inicial, $mes_final) {
                                        return ($r['mes'] >= $mes_inicial && $r['mes'] <= $mes_final) ? $r['mes'] : null;
                                    }, $year_data['registros']));
                                    $months_count = count(array_filter($unique_months));
                                    
                                    $media = $months_count > 0 ? round($filtered_quantity / $months_count) : 0;
                                    
                                    $evolution_data[] = [
                                        'ano' => $year_data['ano'],
                                        'total' => $filtered_quantity,
                                        'media' => $media,
                                        'months' => $months_count
                                    ];
                                endif;
                            endforeach;
                            
                            // Calcular variações
                            for ($i = 0; $i < count($evolution_data); $i++):
                                $current = $evolution_data[$i];
                                $variation = null;
                                
                                if ($i > 0) {
                                    $previous = $evolution_data[$i-1];
                                    if ($previous['total'] > 0) {
                                        $variation = (($current['total'] - $previous['total']) / $previous['total']) * 100;
                                    }
                                }
                        ?>
                        <tr>
                            <td><strong><?= $current['ano'] ?></strong></td>
                            <td style="text-align: right;"><?= number_format($current['total']) ?></td>
                            <td style="text-align: right;"><?= number_format($current['media']) ?></td>
                            <td style="text-align: right;">
                                <?php if ($variation !== null): ?>
                                    <span class="<?= $variation >= 0 ? 'evolution-positive' : 'evolution-negative' ?>">
                                        <?= $variation >= 0 ? '▲' : '▼' ?>
                                        <?= number_format(abs($variation), 1) ?>%
                                    </span>
                                <?php else: ?>
                                    <span style="color: #6c757d;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endfor;
                        else:
                        ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #666; padding: 2rem;">
                                Nenhum dado encontrado para o período selecionado.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Gráfico de Barras -->
                <?php if (isset($evolution_data) && !empty($evolution_data)): ?>
                <div class="chart-container">
                    <h4 style="text-align: center; margin-bottom: 2rem; color: #495057; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">Gráfico Comparativo</h4>
                    
                    <!-- Tooltip -->
                    <div id="chart-tooltip" class="custom-tooltip"></div>
                    
                    <div class="chart-bars" id="chart-bars">
                        <?php 
                        $max_total = max(array_column($evolution_data, 'total'));
                        $max_media = max(array_column($evolution_data, 'media'));
                        
                        // Garantir valores mínimos para evitar divisão por zero
                        $max_total = $max_total > 0 ? $max_total : 1;
                        $max_media = $max_media > 0 ? $max_media : 1;
                        
                        foreach ($evolution_data as $index => $data): 
                            $altura_total = ($data['total'] / $max_total) * 250;
                            $altura_media = ($data['media'] / $max_media) * 200;
                            
                            // Garantir altura mínima
                            $altura_total = max($altura_total, 10);
                            $altura_media = max($altura_media, 8);
                        ?>
                        <div class="chart-bar" data-year="<?= $data['ano'] ?>">
                            <div class="bar-group">
                                <div class="bar-envase" 
                                     style="height: <?= $altura_total ?>px;" 
                                     data-type="envase"
                                     data-value="<?= number_format($data['total']) ?>"
                                     data-year="<?= $data['ano'] ?>"
                                     data-raw-value="<?= $data['total'] ?>">
                                    <div class="bar-value"><?= number_format($data['total']) ?></div>
                                </div>
                                <div class="bar-media" 
                                     style="height: <?= $altura_media ?>px;" 
                                     data-type="media"
                                     data-value="<?= number_format($data['media']) ?>"
                                     data-year="<?= $data['ano'] ?>"
                                     data-raw-value="<?= $data['media'] ?>">
                                    <div class="bar-value"><?= number_format($data['media']) ?></div>
                                </div>
                            </div>
                            <div class="bar-label"><?= $data['ano'] ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: linear-gradient(145deg, #17a2b8, #138496); border-radius: 3px; box-shadow: 0 2px 4px rgba(23,162,184,0.3);"></div>
                            <span>Total de Envase</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: linear-gradient(145deg, #ffc107, #e0a800); border-radius: 3px; box-shadow: 0 2px 4px rgba(255,193,7,0.3);"></div>
                            <span>Média por Mês</span>
                        </div>
                    </div>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const tooltip = document.getElementById('chart-tooltip');
                    const bars = document.querySelectorAll('.bar-envase, .bar-media');
                    
                    bars.forEach(bar => {
                        bar.addEventListener('mouseenter', function(e) {
                            const type = this.dataset.type;
                            const value = this.dataset.value;
                            const year = this.dataset.year;
                            const rawValue = parseInt(this.dataset.rawValue);
                            
                            const title = type === 'envase' ? 'Total de Envase' : 'Média por Mês';
                            const percentage = type === 'envase' ? 
                                ((rawValue / <?= $max_total ?>) * 100).toFixed(1) :
                                ((rawValue / <?= $max_media ?>) * 100).toFixed(1);
                            
                            tooltip.innerHTML = `
                                <div style="font-weight: bold; margin-bottom: 5px;">${title}</div>
                                <div>Ano: ${year}</div>
                                <div>Valor: ${value}</div>
                                <div style="font-size: 11px; opacity: 0.8; margin-top: 3px;">
                                    ${percentage}% do máximo
                                </div>
                            `;
                            
                            // Obter dimensões e posição da barra
                            const rect = this.getBoundingClientRect();
                            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                            const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
                            
                            // Mostrar tooltip para calcular suas dimensões
                            tooltip.style.opacity = '0';
                            tooltip.style.display = 'block';
                            
                            const tooltipRect = tooltip.getBoundingClientRect();
                            
                            // Posicionar acima da barra, centralizado horizontalmente
                            const left = rect.left + scrollLeft + (rect.width / 2) - (tooltipRect.width / 2);
                            const top = rect.top + scrollTop - tooltipRect.height - 15;
                            
                            tooltip.style.position = 'absolute';
                            tooltip.style.left = Math.max(10, left) + 'px';
                            tooltip.style.top = Math.max(10, top) + 'px';
                            tooltip.style.opacity = '1';
                            tooltip.classList.add('show');
                        });
                        
                        bar.addEventListener('mouseleave', function() {
                            tooltip.classList.remove('show');
                            tooltip.style.display = 'none';
                        });
                    });
                    
                    // Adicionar efeito de entrada para as barras
                    bars.forEach((bar, index) => {
                        bar.style.opacity = '0';
                        bar.style.transform = 'translateY(50px)';
                        
                        setTimeout(() => {
                            bar.style.transition = 'all 0.6s ease';
                            bar.style.opacity = '1';
                            bar.style.transform = 'translateY(0)';
                        }, index * 100);
                    });
                });
                </script>
                
                <?php else: ?>
                <div class="chart-container">
                    <h4 style="text-align: center; margin-bottom: 2rem; color: #495057;">Gráfico Comparativo</h4>
                    <div style="text-align: center; padding: 4rem; color: #666;">
                        📊 Nenhum dado disponível para gerar o gráfico.
                        <br><br>
                        <small>Ajuste os filtros ou verifique se há dados de envase para o período selecionado.</small>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include '../src/views/layouts/client_detail.php'; ?>
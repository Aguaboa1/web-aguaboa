<?php ob_start();
/**
 * Página de Ações Vigentes
 * Web Aguaboa - Gestão Comercial
 */
?>

<div class="stats">
    <div class="stat-card">
        <div class="stat-number"><?= $stats['total'] ?></div>
        <div class="stat-label">Total de Ações</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #28a745, #20c997);">
        <div class="stat-number"><?= $stats['futuras'] ?></div>
        <div class="stat-label">No Prazo</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
        <div class="stat-number"><?= $stats['proximas'] ?></div>
        <div class="stat-label">Próximas do Vencimento</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #dc3545, #c82333);">
        <div class="stat-number"><?= $stats['vencidas'] ?></div>
        <div class="stat-label">Vencidas</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                📅 Ações Comerciais Vigentes
            </div>
            <div class="header-buttons">
                    <a href="<?= BASE_URL ?>/crm" class="custom-back-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                    <span>Voltar para Página Inicial</span>
                </a>
            </div>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($acoesVigentes)): ?>
            <div style="padding: 3rem; text-align: center; color: #6c757d;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
                <h3 style="margin-bottom: 1rem; color: #495057;">Nenhuma ação vigente encontrada</h3>
                <p style="margin-bottom: 2rem; font-size: 1.1rem;">
                    Não há ações comerciais com prazo de conclusão definido no momento.
                </p>
                <a href="<?= BASE_URL ?>/crm" class="custom-back-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                    <span>Voltar para Página Inicial</span>
                </a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Empresa</th>
                        <th>Cidade</th>
                        <th>Descrição da Ação</th>
                        <th>Data da Ação</th>
                        <th>Prazo de Conclusão</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($acoesVigentes as $acao): ?>
                        <?php
                            $prazoTimestamp = strtotime($acao['prazo_conclusao']);
                            $agora = time();
                            $isVencida = $prazoTimestamp < $agora;
                            $isProxima = ($prazoTimestamp - $agora) < (30 * 24 * 60 * 60); // 30 dias
                            
                            // Calcular dias restantes
                            $diasRestantes = ceil(($prazoTimestamp - $agora) / (24 * 60 * 60));
                        ?>
                        <tr style="<?= $isVencida ? 'background-color: #fff5f5;' : ($isProxima ? 'background-color: #fffbf0;' : '') ?>">
                            <td>
                                <a href="<?= BASE_URL ?>/crm/client/<?= $acao['client_id'] ?>" 
                                   style="color: #007bff; text-decoration: none;">
                                    <?= htmlspecialchars($acao['cliente']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($acao['empresa'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($acao['cidade'] ?? '-') ?></td>
                            <td>
                                <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                    <?= htmlspecialchars($acao['descricao']) ?>
                                </div>
                            </td>
                            <td><?= date('d/m/Y', strtotime($acao['data_acao'])) ?></td>
                            <td><?= date('m/Y', strtotime($acao['prazo_conclusao'])) ?></td>
                            <td style="text-align: center;">
                                <?php if ($isVencida): ?>
                                    <span style="
                                        padding: 4px 8px; 
                                        border-radius: 12px; 
                                        font-size: 12px; 
                                        font-weight: bold;
                                        background: #f8d7da; 
                                        color: #721c24;
                                    ">
                                        🚨 Vencida (<?= abs($diasRestantes) ?> dias)
                                    </span>
                                <?php elseif ($isProxima): ?>
                                    <span style="
                                        padding: 4px 8px; 
                                        border-radius: 12px; 
                                        font-size: 12px; 
                                        font-weight: bold;
                                        background: #fff3cd; 
                                        color: #856404;
                                    ">
                                        ⚠️ Próxima (<?= $diasRestantes ?> dias)
                                    </span>
                                <?php else: ?>
                                    <span style="
                                        padding: 4px 8px; 
                                        border-radius: 12px; 
                                        font-size: 12px; 
                                        font-weight: bold;
                                        background: #d4edda; 
                                        color: #155724;
                                    ">
                                        ✅ No prazo (<?= $diasRestantes ?> dias)
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <a href="<?= BASE_URL ?>/crm/client/<?= $acao['client_id'] ?>" 
                                   class="btn btn-sm btn-primary" title="Ver cliente">
                                    👁️ Ver Cliente
                                </a>
                                <?php if (!empty($acao['arquivo'])): ?>
                                    <a href="<?= BASE_URL ?>/uploads/actions/<?= htmlspecialchars($acao['arquivo']) ?>" 
                                       target="_blank" class="btn btn-sm" 
                                       style="background: #17a2b8; color: white;" title="Ver arquivo">
                                        📎
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
.custom-back-btn {
    display: inline-flex !important;
    align-items: center !important;
    gap: 10px !important;
    padding: 14px 28px !important;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    text-decoration: none !important;
    border-radius: 50px !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    border: none !important;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4) !important;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
    position: relative !important;
    overflow: hidden !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}

.custom-back-btn:before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: -100% !important;
    width: 100% !important;
    height: 100% !important;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent) !important;
    transition: left 0.5s !important;
}

.custom-back-btn:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%) !important;
    transform: translateY(-3px) scale(1.05) !important;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6) !important;
    color: white !important;
    text-decoration: none !important;
}

.custom-back-btn:hover:before {
    left: 100% !important;
}

.custom-back-btn:active {
    transform: translateY(-1px) scale(1.02) !important;
}

.custom-back-btn svg {
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2)) !important;
}

.custom-back-btn:hover svg {
    transform: translateX(-5px) rotate(-5deg) !important;
}

.custom-back-btn span {
    position: relative !important;
    z-index: 2 !important;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
</style>
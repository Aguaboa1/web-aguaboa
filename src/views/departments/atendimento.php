<?php
require_once '../src/views/layout/header.php';
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                📞 Gestão de Atendimento
            </div>
            <div class="header-buttons">
                <a href="<?= BASE_URL ?>/departments" class="custom-back-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                    <span>Voltar aos Setores</span>
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div style="text-align: center; padding: 3rem;">
            <div style="font-size: 4rem; margin-bottom: 2rem;">📞</div>
            <h2 style="color: #007fa3; margin-bottom: 1rem;">Gestão de Atendimento</h2>
            <p style="font-size: 1.2rem; color: #666; margin-bottom: 2rem;">
                Módulo em desenvolvimento
            </p>
            <p style="color: #666;">
                Em breve você terá acesso às funcionalidades de:<br>
                • Central de atendimento ao cliente<br>
                • Suporte técnico<br>
                • SAC (Serviço de Atendimento ao Consumidor)<br>
                • Gestão de chamados<br>
                • Pesquisas de satisfação
            </p>
        </div>
    </div>
</div>

<?php require_once '../src/views/layout/footer.php'; ?>
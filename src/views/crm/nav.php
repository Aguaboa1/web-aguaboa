<?php
// CRM navigation fragment
// Expected to be included from src/views/layout/header.php or layouts/main.php
?>
<div class="nav-links">
    <a href="<?= BASE_URL ?>/crm"><span class="icon icon--indigo" aria-hidden="true">🏠</span><span class="label">Página Inicial</span></a>
    <a href="<?= BASE_URL ?>/envase"><span class="icon icon--amber" aria-hidden="true">📦</span><span class="label">Envase</span></a>
    <a href="<?= BASE_URL ?>/crm/search-envase"><span class="icon icon--green" aria-hidden="true">🔍</span><span class="label">Pesquisar Envase</span></a>
    <a href="<?= BASE_URL ?>/crm/acoes-vigentes"><span class="icon icon--rose" aria-hidden="true">⚠️</span><span class="label">Ações Vigentes</span></a>
    <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="<?= BASE_URL ?>/crm/unify-clients"><span class="icon icon--indigo" aria-hidden="true">🔗</span><span class="label">Unificar Clientes</span></a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/departments"><span class="icon icon--teal" aria-hidden="true">🏢</span><span class="label">Setores</span></a>
</div>

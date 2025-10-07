<?php ob_start(); ?>

<div style="margin-bottom: 1rem;">
    <a href="<?= BASE_URL ?>/crm/client/<?= $client['id'] ?>" class="btn btn-secondary">← Voltar ao Cliente</a>
</div>

<div class="card">
    <div class="card-header">
        ✏️ Editar Cliente: <?= htmlspecialchars($client['cliente']) ?>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-group">
                <label>Nome do Cliente:</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($client['cliente']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Empresa:</label>
                <input type="text" name="empresa" value="<?= htmlspecialchars($client['empresa'] ?: '') ?>">
            </div>
            
            <div class="form-group">
                <label>Cidade:</label>
                <input type="text" name="cidade" value="<?= htmlspecialchars($client['cidade'] ?: '') ?>">
            </div>
            
            <div class="form-group">
                <label>Estado:</label>
                <input type="text" name="estado" value="<?= htmlspecialchars($client['estado'] ?: '') ?>">
            </div>
            
            <div class="form-group">
                <label>Tipo de Marca:</label>
                <select name="exclusividade">
                    <option value="Exclusivo" <?= $client['cliente_exclusivo'] ? 'selected' : '' ?>>Exclusivo</option>
                    <option value="Multimarcas" <?= !$client['cliente_exclusivo'] ? 'selected' : '' ?>>Multimarcas</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Categoria do Cliente:</label>
                <select name="categoria">
                    <option value="Normal" <?= (strtolower($client['tipo_cliente'] ?: '') === 'normal' || !$client['tipo_cliente']) ? 'selected' : '' ?>>Normal</option>
                    <option value="Master" <?= strtolower($client['tipo_cliente'] ?: '') === 'master' ? 'selected' : '' ?>>Master</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Tipo de frete:</label>
                <select name="tipo_frete" id="tipoFrete" onchange="toggleFreteiro()">
                    <option value="Próprio" <?= (strtolower($client['tipo_frete'] ?: '') === 'próprio' || !$client['tipo_frete']) ? 'selected' : '' ?>>Próprio</option>
                    <option value="Freteiro" <?= strtolower($client['tipo_frete'] ?: '') === 'freteiro' ? 'selected' : '' ?>>Freteiro</option>
                </select>
                <div id="freteiroContainer" style="display: <?= strtolower($client['tipo_frete'] ?: '') === 'freteiro' ? 'block' : 'none' ?>; margin-top: 0.5rem;">
                    <label>Nome do Freteiro:</label>
                    <input type="text" name="freteiro_nome" value="<?= htmlspecialchars($client['freteiro_nome'] ?: '') ?>">
                </div>
            </div>
            
            <div class="form-group" style="display: flex; align-items: center;">
                <input type="checkbox" name="premium" value="1" <?= $client['cliente_premium'] ? 'checked' : '' ?> 
                       id="premium" style="margin-right: 8px; width: auto;">
                <label for="premium" style="margin-bottom: 0;">Cliente Premium</label>
            </div>
            
            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-success">💾 Salvar Alterações</button>
                <a href="<?= BASE_URL ?>/crm/client/<?= $client['id'] ?>" class="btn btn-secondary">❌ Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleFreteiro() {
    const select = document.getElementById('tipoFrete');
    const container = document.getElementById('freteiroContainer');
    container.style.display = (select.value === 'Freteiro') ? 'block' : 'none';
}
</script>

<?php $content = ob_get_clean(); ?>
<?php include '../src/views/layouts/main.php'; ?>
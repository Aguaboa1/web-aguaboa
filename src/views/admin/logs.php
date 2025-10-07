<?php ob_start(); ?>

<div class="card">
    <div class="card-header">
        🔍 Logs de Atividade do Sistema
        <div style="font-size: 0.9rem; font-weight: normal;">
            Últimas 100 atividades registradas
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <table>
            <thead>
                <tr>
                    <th>Data/Hora</th>
                    <th>Usuário</th>
                    <th>Ação</th>
                    <th>Descrição</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= date('d/m/Y H:i:s', strtotime($log['timestamp'])) ?></td>
                    <td>
                        <strong><?= htmlspecialchars($log['username'] ?: 'Usuário desconhecido') ?></strong>
                        <?php if ($log['role']): ?>
                            <span style="font-size: 0.8rem; color: #666;">(<?= $log['role'] === 'admin' ? 'Admin' : 'Equipe' ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $actionClass = '';
                        switch(strtolower($log['action'])) {
                            case 'login':
                                $actionClass = 'color: #28a745; font-weight: bold;';
                                break;
                            case 'logout':
                                $actionClass = 'color: #dc3545; font-weight: bold;';
                                break;
                            case 'login_failed':
                                $actionClass = 'color: #dc3545;';
                                break;
                            default:
                                $actionClass = 'color: #007fa3;';
                        }
                        ?>
                        <span style="<?= $actionClass ?>"><?= htmlspecialchars($log['action']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($log['description'] ?: '-') ?></td>
                    <td style="font-family: monospace; font-size: 0.9rem;">
                        <?= htmlspecialchars($log['ip_address'] ?: '-') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (empty($logs)): ?>
        <div style="text-align: center; padding: 2rem; color: #666;">
            📝 Nenhum log de atividade encontrado.
        </div>
        <?php endif; ?>
    </div>
</div>

<div style="margin-top: 2rem;">
    <div class="card">
        <div class="card-header">
            📊 Resumo de Atividades
        </div>
        <div class="card-body">
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?= count($logs) ?></div>
                    <div class="stat-label">Total de Logs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= count(array_filter($logs, fn($l) => strtolower($l['action']) === 'login')) ?></div>
                    <div class="stat-label">Logins</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= count(array_filter($logs, fn($l) => strtolower($l['action']) === 'login_failed')) ?></div>
                    <div class="stat-label">Tentativas Falhadas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= count(array_unique(array_column($logs, 'user_id'))) ?></div>
                    <div class="stat-label">Usuários Ativos</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include '../src/views/layouts/admin.php'; ?>
<?php ob_start();

// Helper: inclusive months between Y-m strings
function months_diff_months($startMonth, $endMonth) {
    $s = DateTime::createFromFormat('!Y-m', $startMonth);
    $e = DateTime::createFromFormat('!Y-m', $endMonth);
    if (!$s || !$e) return 1;
    $startCount = ((int)$s->format('Y')) * 12 + (int)$s->format('n');
    $endCount = ((int)$e->format('Y')) * 12 + (int)$e->format('n');
    $months = $endCount - $startCount + 1;
    return $months > 0 ? $months : 1;
}

$results = [];
$minAvg = 500;
$start_month = '';
$end_month = '';
$only_active = false;
$error = '';
$debug = !empty($_GET['debug']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_month = trim($_POST['start_month'] ?? '');
    $end_month = trim($_POST['end_month'] ?? '');
    $minAvg = isset($_POST['min_avg']) && is_numeric($_POST['min_avg']) ? (float)$_POST['min_avg'] : $minAvg;
    $only_active = !empty($_POST['only_active']);

    if ($start_month === '' || $end_month === '') {
        $error = 'Escolha data inicial e final.';
    } else {
        $start = $start_month . '-01';
        $ed = DateTime::createFromFormat('!Y-m', $end_month);
        if (!$ed) {
            $error = 'Formato de data final inválido.';
        } else {
            $ed->modify('last day of this month');
            $end = $ed->format('Y-m-d');

            try {
                $db = Database::getInstance()->getConnection();

                $sql = "SELECT c.id AS client_id, c.cliente, c.empresa, COALESCE(SUM(e.quantidade),0) AS total_envases
                    FROM clients c
                    LEFT JOIN envase_data e ON e.empresa = c.empresa 
                      AND STR_TO_DATE(CONCAT(e.ano,'-',LPAD(e.mes,2,'0'),'-',LPAD(e.dia,2,'0')), '%Y-%m-%d') BETWEEN :start AND :end
                    GROUP BY c.id, c.cliente, c.empresa";

                if ($only_active) {
                    $sql .= " HAVING COALESCE(SUM(e.quantidade),0) > 0";
                }

                $sql .= " ORDER BY total_envases DESC";

                if ($debug) {
                    echo "<!-- SQL: " . htmlspecialchars($sql) . " -->\n";
                }

                $stmt = $db->prepare($sql);
                $params = ['start' => $start . ' 00:00:00', 'end' => $end . ' 23:59:59'];
                $stmt->execute($params);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $months = months_diff_months($start_month, $end_month);
                foreach ($rows as $r) {
                    $total = (int)($r['total_envases'] ?? 0);
                    $avg = $months > 0 ? ($total / $months) : 0;
                    if ($avg < $minAvg) {
                        $results[] = [
                            'client_id' => $r['client_id'],
                            'cliente' => $r['cliente'],
                            'empresa' => $r['empresa'],
                            'total' => $total,
                            'avg' => round($avg, 2),
                        ];
                    }
                }

            } catch (Exception $e) {
                $error = 'Erro ao consultar dados: ' . $e->getMessage();
            }
        }
    }
}
?>

<style>
/* Polished filter card */
.card{background:#fff;border-radius:12px;box-shadow:0 8px 32px rgba(2,6,23,0.06);border:1px solid #ecf3f6;margin-bottom:18px}
.card-header{padding:14px 18px;background:linear-gradient(90deg, rgba(6,182,212,0.12), rgba(14,165,163,0.06));display:flex;align-items:center;gap:14px;border-bottom:1px solid #e6eef3}
.card-body{padding:18px}
.card-header .icon-circle{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#e6f9ff,#d9f2fb);border:1px solid #cfeff6;font-size:20px}
.form-label{display:block;margin-bottom:8px;font-weight:700;color:#233;font-size:13px}
.form-control{padding:10px 12px;border:1px solid #e2e8f0;border-radius:10px;width:100%;box-sizing:border-box;background:#fff}
.filters-row{display:flex;gap:16px;align-items:flex-end;flex-wrap:wrap}
.filter-field{flex:1;min-width:160px}
.filter-actions{display:flex;align-items:center}
.btn-search{background:linear-gradient(135deg,#06b6d4,#0ea5a3);color:#fff;border:none;padding:11px 18px;border-radius:12px;font-weight:800;box-shadow:0 6px 18px rgba(6,182,212,0.18);display:inline-flex;align-items:center;gap:8px}
.btn-search:hover{transform:translateY(-2px)}
.btn-search .icon-svg{width:18px;height:18px;display:inline-block;vertical-align:middle}
.btn-search.small{padding:6px 10px;font-weight:700;border-radius:8px;font-size:0.95rem}
.table{width:100%;border-collapse:collapse;margin-top:12px}
.table th,.table td{padding:12px;border-bottom:1px solid #f3f6f8;text-align:left}
.table th{font-size:12px;color:#3b4a54;text-transform:uppercase}
.table-container{overflow:auto}
.muted{color:#6b7280}
</style>

<div style="padding:18px">
  <div class="card">
    <div class="card-header">
      <div class="icon-circle" aria-hidden="true">
        <!-- Magnifier SVG with colored stroke to match brand -->
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="22" height="22">
          <circle cx="11" cy="11" r="6" stroke="#06b6d4" stroke-width="1.6" />
          <path d="M21 21l-4.35-4.35" stroke="#06b6d4" stroke-width="1.6" stroke-linecap="round" />
        </svg>
      </div>
      <div style="font-weight:700;color:#223">Pesquisar Envase</div>
    </div>
    <div class="card-body">
      <?php if ($error): ?>
        <div style="color:#b91c1c;margin-bottom:8px"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="filters-row">
          <div class="filter-field">
            <label class="form-label">Data Inicial</label>
            <input type="month" name="start_month" class="form-control" value="<?= htmlspecialchars($start_month) ?>" required>
          </div>

          <div class="filter-field">
            <label class="form-label">Data Final</label>
            <input type="month" name="end_month" class="form-control" value="<?= htmlspecialchars($end_month) ?>" required>
          </div>

          <div class="filter-field" style="max-width:220px;min-width:160px">
            <label class="form-label">Média menor que (por mês)</label>
            <input type="number" name="min_avg" class="form-control" min="0" step="1" value="<?= htmlspecialchars($minAvg) ?>">
          </div>

          <div style="display:flex;align-items:center;gap:8px">
            <label style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="only_active" value="1" <?= $only_active ? 'checked' : '' ?>> <span style="font-weight:700">Apenas clientes ativos</span></label>
          </div>

          <div style="margin-left:auto" class="filter-actions">
            <button class="btn-search" type="submit">
              <svg class="icon-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="6" stroke="rgba(255,255,255,0.95)" stroke-width="1.6"/><path d="M21 21l-4.35-4.35" stroke="rgba(255,255,255,0.95)" stroke-width="1.6" stroke-linecap="round"/></svg>
              <span>Pesquisar</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Resultados — clientes com média mensal inferior a <?= number_format($minAvg, 0, ',', '.') ?> envase(s) por mês</div>
    <div class="card-body">
      <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)): ?>
        <div style="margin-bottom:8px;color:#334">Resultados: <?= count($results) ?></div>
        <?php if ($debug): ?>
          <div style="background:#fff3cd;border:1px solid #ffeeba;padding:10px;margin-bottom:10px">
            <strong>DEBUG</strong><br>
            SQL: <?= htmlspecialchars($sql ?? '') ?><br>
            Params: start=<?= htmlspecialchars($start . ' 00:00:00') ?> end=<?= htmlspecialchars($end . ' 23:59:59') ?><br>
            Months calc: <?= htmlspecialchars((string)months_diff_months($start_month,$end_month)) ?><br>
            Rows fetched: <?= isset($rows) ? count($rows) : 0 ?><br>
            <?php if (!empty($rows)): ?>
              <pre style="background:#f8f9fa;border:1px solid #dee2e6;padding:6px;margin-top:6px"><?= htmlspecialchars(json_encode($rows[0], JSON_UNESCAPED_UNICODE)) ?></pre>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <div class="table-container">
          <table class="table">
            <thead>
              <tr><th>Cliente</th><th>Empresa</th><th>Total</th><th>Média/mês</th><th>Ações</th></tr>
            </thead>
            <tbody>
              <?php if (empty($results)): ?>
                <tr><td colspan="5" style="text-align:center;color:#666">Nenhum registro encontrado</td></tr>
              <?php else: ?>
                <?php foreach ($results as $r): ?>
                  <tr>
                    <td><?= htmlspecialchars($r['cliente']) ?></td>
                    <td><?= htmlspecialchars($r['empresa']) ?></td>
                    <td><?= number_format($r['total']) ?></td>
                    <td><?= number_format($r['avg'],2) ?></td>
                    <td>
                      <a href="<?= BASE_URL ?>/crm/client/<?= $r['client_id'] ?>" class="btn-search small" title="Ver cliente">
                        <svg class="icon-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none">
                          <path d="M2 12s4-8 10-8 10 8 10 8-4 8-10 8S2 12 2 12z" stroke="#06b6d4" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                          <circle cx="12" cy="12" r="3" fill="#06b6d4" />
                        </svg>
                        <span style="margin-left:6px">Ver</span>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Pesquisar Envase - Web Aguaboa';
$flashMessages = getFlashMessages();
include '../src/views/layouts/main.php';
?>

<?php
/**
 * calculate_original.php
 * p-fr-nb plus effect size Calculator — Original Walsh FI
 *
 * Identical output to calculate.php except the Fragility Index (FI) uses
 * the strict original Walsh (2014) definition via FragilityIndexWalsh.
 * GFI/GFQ are not calculated. All other metrics (p, RQ, RR) are unchanged.
 *
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 *
 * License: Creative Commons Attribution 4.0 International (CC BY 4.0)
 * https://creativecommons.org/licenses/by/4.0/
 */

require_once 'FisherExactTest.php';
require_once 'FragilityIndexWalsh.php';
require_once 'RiskQuotient.php';
require_once 'RelativeRisk.php';

$result = null;
$error  = null;
$alpha  = 0.05;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = filter_input(INPUT_POST, 'a', FILTER_VALIDATE_INT);
    $b = filter_input(INPUT_POST, 'b', FILTER_VALIDATE_INT);
    $c = filter_input(INPUT_POST, 'c', FILTER_VALIDATE_INT);
    $d = filter_input(INPUT_POST, 'd', FILTER_VALIDATE_INT);

    if ($a === false || $b === false || $c === false || $d === false
            || $a < 0 || $b < 0 || $c < 0 || $d < 0) {
        $error = "All values must be non-negative integers.";
    } else {
        try {
            $N = $a + $b + $c + $d;

            // p-value
            $p_val = FisherExactTest::calculate($a, $b, $c, $d);

            // Strict Walsh FI
            $fi = FragilityIndexWalsh::calculate($a, $b, $c, $d, $alpha);

            // Robustness (RQ)
            $rq_result = RiskQuotient::calculate($a, $b, $c, $d);

            // Effect size (always 95% CI)
            $rr_result = RelativeRisk::calculate($a, $b, $c, $d);

            // Assemble result
            $result = [
                'input' => ['a' => $a, 'b' => $b, 'c' => $c, 'd' => $d, 'N' => $N],
                'p' => [
                    'value'       => round($p_val, 6),
                    'significant' => ($p_val <= $alpha),
                    'alpha'       => $alpha,
                ],
                'fi'  => $fi,
                'nb'  => ['RQ' => $rq_result['RQ']],
                'effect' => [
                    'RR'         => $rr_result['RR'],
                    'CI_lower'   => $rr_result['CI_lower'],
                    'CI_upper'   => $rr_result['CI_upper'],
                    'CI_level'   => $rr_result['CI_level'],
                    'correction' => $rr_result['correction'],
                    'note'       => $rr_result['note'],
                ],
            ];

        } catch (Exception $e) {
            $error = "Calculation error: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>
<style>
  /* Page and form styles */
  .pfr-page { max-width: 980px; margin: 0 auto; padding: 8px 20px 24px; }
  .pfr-page > :first-child { margin-top: 0; }
  .pfr-page h2 { margin: 0 0 6px; }
  .pfr-page .pfr-lead { margin: 0 0 10px; }
  .pfr-form { margin: 10px 0; padding: 14px; border-radius: 8px; background: #fafafa; }
  .pfr-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 12px; }
  .pfr-buttons { display: flex; gap: 8px; }
  .pfr-label { display: block; margin-bottom: 5px; font-weight: bold; }
  .pfr-input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
  .pfr-submit { background: #007bff; color: white; padding: 6px 15px; border: none; border-radius: 2px; cursor: pointer; font-size: 16px; font-weight: bold; }
  .pfr-reset { background: #6c757d; color: white; padding: 6px 15px; border: none; border-radius: 2px; cursor: pointer; font-size: 16px; font-weight: bold; }
  #pfr-error { background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin: 10px 0; }
  #pfr-result { margin-top: 8px; }
  .pfr-citation { font-size: 14px; color: #666; }
</style>
<div class="pfr-page">
  <h2>p-fr-nb plus effect size Calculator (Original Walsh FI)</h2>
  <p class="pfr-lead">Calculate complete statistical evidence (p-fr-nb triplet plus effect size) for 2×2 independent binary outcome tables. Fragility Index uses the strict original Walsh (2014) definition.</p>
  <?php if ($error): ?>
    <div id="pfr-error">
      <strong>Error:</strong> <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>
  <form id="pfr-form" method="POST" class="pfr-form">
    <div class="pfr-grid">
      <div>
        <label for="a" class="pfr-label">a (Arm A events):</label>
        <input type="number" id="a" name="a" min="0" required class="pfr-input"
               value="<?= isset($_POST['a']) ? htmlspecialchars($_POST['a']) : '' ?>">
      </div>
      <div>
        <label for="b" class="pfr-label">b (Arm A non-events):</label>
        <input type="number" id="b" name="b" min="0" required class="pfr-input"
               value="<?= isset($_POST['b']) ? htmlspecialchars($_POST['b']) : '' ?>">
      </div>
      <div>
        <label for="c" class="pfr-label">c (Arm B events):</label>
        <input type="number" id="c" name="c" min="0" required class="pfr-input"
               value="<?= isset($_POST['c']) ? htmlspecialchars($_POST['c']) : '' ?>">
      </div>
      <div>
        <label for="d" class="pfr-label">d (Arm B non-events):</label>
        <input type="number" id="d" name="d" min="0" required class="pfr-input"
               value="<?= isset($_POST['d']) ? htmlspecialchars($_POST['d']) : '' ?>">
      </div>
    </div>
    <div class="pfr-buttons">
      <button type="submit" class="pfr-submit">Calculate p-fr-nb</button>
      <button type="button" id="pfr-reset" class="pfr-reset">Reset</button>
    </div>
  </form>
  <?php if ($result): ?>
    <?php $fi = $result['fi']; ?>
    <div id="pfr-result">
      <h3>Significance (p)</h3>
      <p>baseline p-value = <?= number_format($result['p']['value'] ?? 0, 6) ?><br>
      [two-sided Fisher's exact test, alpha = <?= number_format($result['p']['alpha'] ?? 0.05, 2) ?>]
      </p>
      <h3>Fragility (fr)</h3>
      <p>FI (Fragility Index) = <?= htmlspecialchars($fi['FI_text'] ?? 'NULL') ?><br>
      <?php if ($fi['FI'] !== null): ?>
      FQ (Fragility Quotient) = <?= number_format($fi['FQ'], 4) ?><br>
      MFQ (Modified-Arm Fragility Quotient) = <?= number_format($fi['MFQ'], 4) ?><br>
      <?php if (!empty($fi['post_FI'])): ?>
      Post-FI table: {<?= (int)$fi['post_FI']['a'] ?>, <?= (int)$fi['post_FI']['b'] ?>, <?= (int)$fi['post_FI']['c'] ?>, <?= (int)$fi['post_FI']['d'] ?>}<br>
      Post-FI p-value = <?= number_format((float)($fi['post_FI_p'] ?? 0), 6) ?><br>
      <?php endif; ?>
      <?php endif; ?>
      </p>
      <h3>Robustness (nb)</h3>
      <p>RQ (Risk Quotient) = <?= number_format($result['nb']['RQ'] ?? 0, 4) ?></p>
      <h3>Effect Size</h3>
      <p>
      <?php
        $eff      = $result['effect'] ?? [];
        $ci_level = number_format($eff['CI_level'] ?? 95, 0);
        if ($eff['RR'] !== null):
      ?>
        RR (Relative Risk) = <?= number_format($eff['RR'], 4) ?><br>
        <?php if ($eff['CI_lower'] !== null && $eff['CI_upper'] !== null): ?>
        <?= $ci_level ?>% CI = (<?= number_format($eff['CI_lower'], 4) ?>, <?= number_format($eff['CI_upper'], 4) ?>)
        <?php else: ?>
        <?= $ci_level ?>% CI = not calculable<?= !empty($eff['note']) ? ' (' . htmlspecialchars($eff['note']) . ')' : '' ?>
        <?php endif; ?>
        <?php if (!empty($eff['correction'])): ?><br>[Haldane-Anscombe continuity correction applied]<?php endif; ?>
      <?php else: ?>
        RR (Relative Risk) = not calculable<?= !empty($eff['note']) ? ' (' . htmlspecialchars($eff['note']) . ')' : '' ?>
      <?php endif; ?>
      </p>
      <hr style="margin: 22px 0; border: 0; border-top: 1px solid #ccc;">
      <p class="pfr-citation">
        <strong>Citation:</strong> Heston TF. Fragility Metrics Calculators. Zenodo. DOI = (pending)
      </p>
    </div>
  <?php endif; ?>
</div>
<script>
  (function () {
    const form = document.getElementById('pfr-form');
    const btn  = document.getElementById('pfr-reset');
    const res  = document.getElementById('pfr-result');
    const err  = document.getElementById('pfr-error');
    if (!btn || !form) return;
    btn.addEventListener('click', function () {
      form.querySelectorAll('input[type="number"]').forEach(i => { i.value = ''; });
      if (res) res.style.display = 'none';
      if (err) err.style.display = 'none';
    });
  })();
</script>
<?php include 'includes/footer.php'; ?>

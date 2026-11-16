<?php
/**
 * calculate.php
 * p-fr-nb plus effect size Calculator
 *
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 *
 * License: Creative Commons Attribution 4.0 International (CC BY 4.0)
 * https://creativecommons.org/licenses/by/4.0/
 */

require_once 'includes/safety_net.php';
require_once 'FragilityCalculator.php';
require_once 'DatabaseManager.php';

fm_install_safety_net('p-fr-nb plus effect size Calculator', 'index.php');

$result = null;
$error = null;
$too_large = false;
$saved_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = filter_input(INPUT_POST, 'a', FILTER_VALIDATE_INT);
    $b = filter_input(INPUT_POST, 'b', FILTER_VALIDATE_INT);
    $c = filter_input(INPUT_POST, 'c', FILTER_VALIDATE_INT);
    $d = filter_input(INPUT_POST, 'd', FILTER_VALIDATE_INT);

    // is_int() rejects both invalid values (false) and missing fields (null).
    if (!is_int($a) || !is_int($b) || !is_int($c) || !is_int($d) || $a < 0 || $b < 0 || $c < 0 || $d < 0) {
        $error = "All values must be non-negative integers.";
    } elseif ($a > FragilityCalculator::MAX_N || $b > FragilityCalculator::MAX_N ||
              $c > FragilityCalculator::MAX_N || $d > FragilityCalculator::MAX_N ||
              $a + $b + $c + $d > FragilityCalculator::MAX_N) {
        $too_large = true;
    } else {
        try {
            $result = FragilityCalculator::calculate($a, $b, $c, $d);
            $saved_id = DatabaseManager::saveCalculation($result);
        } catch (Throwable $e) {
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
  <h2>p-fr-nb plus effect size Calculator</h2>
  <p class="pfr-lead">Calculate complete statistical evidence (p-fr-nb triplet plus effect size) for 2×2 independent binary outcome tables.</p>
  <?php if ($error): ?>
    <div id="pfr-error">
      <strong>Error:</strong> <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>
  <?php if ($too_large): ?>
    <?= fm_colab_notice_html('The web calculator accepts tables with a total N of up to ' . number_format(FragilityCalculator::MAX_N) . '.') ?>
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
    <div id="pfr-result">
      <?php
        // p-fr-nb triplet: GFQ is the recommended fr for 2x2 tables. When the
        // GFI is too large to certify within the web time budget, LinFQ is
        // reported instead; MFQ is the last resort. nb is RQ.
        $gfiOverBudget = is_array($result['gfi'] ?? null) && empty($result['gfi']['verified']);
        $lin = $result['lin'] ?? null;
        $frName = null;
        $frVal = null;
        $frRel = '=';
        if (!$gfiOverBudget && isset($result['gfi']['GFQ'])) {
            $frName = 'GFQ';
            $frVal = $result['gfi']['GFQ'];
        } elseif (is_array($lin) && $lin['LinFQ'] !== null) {
            $frName = 'LinFQ';
            $frVal = $lin['LinFQ'];
            $frRel = $lin['verified'] ? '=' : '≤';
        } elseif (isset($result['fr']['MFQ'])) {
            $frName = 'MFQ';
            $frVal = $result['fr']['MFQ'];
        }
        $nbVal = $result['nb']['RQ'] ?? null;
      ?>
      <?php if ($gfiOverBudget): ?>
        <?= fm_colab_notice_html('The Global Fragility Index (GFI) for this table is too large for the web calculator to certify within its time limit, so fragility is reported as LinFQ, the Lin Fragility Quotient (LinFI / N), instead of GFQ.') ?>
      <?php endif; ?>
      <p><strong>p–fr–nb triplet:</strong> p = <?= number_format($result['p']['value'] ?? 0, 6) ?>,
      fr = <?= $frName !== null ? $frName . ' ' . $frRel . ' ' . number_format($frVal, 4) : 'not defined' ?>,
      nb = <?= $nbVal !== null ? 'RQ = ' . number_format($nbVal, 4) : 'not defined' ?><br>
      <span style="font-size:13px; color:#555;">GFQ is the recommended fragility (fr) metric for 2×2 tables. When the GFI is too large to certify here, LinFQ is reported instead; MFQ is used when neither is available.</span></p>
      <h3>Significance (p)</h3>
      <p>baseline p-value = <?= number_format($result['p']['value'] ?? 0, 6) ?><br>
      [two-sided Fisher's exact test, alpha = <?= number_format($result['p']['alpha'] ?? 0.05, 2) ?>]
      </p>
      <h3>Fragility (fr)</h3>
      <p>FI (Fragility Index) = <?= $result['fr']['FI'] ?? 'NULL' ?><?= ($result['fr']['FI'] === null && !empty($result['fr']['note'])) ? ' (' . htmlspecialchars($result['fr']['note']) . ')' : '' ?><br>
      FQ (Fragility Quotient, legacy) = <?= $result['fr']['FQ'] !== null ? number_format($result['fr']['FQ'], 4) : 'NULL' ?><br>
      MFQ (Modified-Arm Fragility Quotient) = <?= $result['fr']['MFQ'] !== null ? number_format($result['fr']['MFQ'], 4) : 'NULL' ?><br>
      <?php if (!empty($result['fr']['post_FI'])): ?>
      Post-FI table: {<?= (int)($result['fr']['post_FI']['a'] ?? 0) ?>, <?= (int)($result['fr']['post_FI']['b'] ?? 0) ?>, <?= (int)($result['fr']['post_FI']['c'] ?? 0) ?>, <?= (int)($result['fr']['post_FI']['d'] ?? 0) ?>}<br>
      Post-FI p-value = <?= number_format((float)($result['fr']['post_FI_p'] ?? 0), 6) ?><br>
      <?php endif; ?>
      <br>
      <?php if ($gfiOverBudget): ?>
        <?php
          $gLo = $result['gfi']['lower_bound'] ?? null;
          $gHi = $result['gfi']['upper_bound'] ?? null;
          $nTot = (int)($result['input']['N'] ?? 0);
        ?>
        GFI (Global Fragility Index) = not certified within the web time limit<?php if ($gLo !== null && $gHi !== null): ?> (proven bracket: <?= number_format($gLo) ?> ≤ GFI ≤ <?= number_format($gHi) ?>)<?php elseif ($gLo !== null): ?> (proven: GFI ≥ <?= number_format($gLo) ?>)<?php elseif ($gHi !== null): ?> (proven: GFI ≤ <?= number_format($gHi) ?>)<?php endif; ?><br>
        GFQ (Global Fragility Quotient) = not certified<?php if ($gLo !== null && $gHi !== null && $nTot > 0): ?> (<?= number_format($gLo / $nTot, 4) ?> ≤ GFQ ≤ <?= number_format($gHi / $nTot, 4) ?>)<?php elseif ($gHi !== null && $nTot > 0): ?> (GFQ ≤ <?= number_format($gHi / $nTot, 4) ?>)<?php endif; ?><br>
        <br>
        <?php if (is_array($lin) && $lin['LinFI'] !== null): ?>
        LinFI (Lin Fragility Index) <?= $lin['verified'] ? '=' : '≤' ?> <?= number_format($lin['LinFI']) ?><?= $lin['verified'] ? '' : ' (upper bound; scan stopped at the web time limit)' ?><br>
        LinFQ (Lin Fragility Quotient = LinFI / N) <?= $lin['verified'] ? '=' : '≤' ?> <?= number_format($lin['LinFQ'], 4) ?><br>
        [minimum within-arm outcome toggles, in either or both arms and in either direction, that reverse the significance classification; arm sizes fixed; two-sided Fisher's exact]<br>
        Post-LinFI table: {<?= (int)$lin['post_table']['a'] ?>, <?= (int)$lin['post_table']['b'] ?>, <?= (int)$lin['post_table']['c'] ?>, <?= (int)$lin['post_table']['d'] ?>}<br>
        Post-LinFI p-value = <?= number_format((float)$lin['final_p'], 6) ?><br>
        <?php else: ?>
        LinFI (Lin Fragility Index) = NULL<?= (is_array($lin) && !empty($lin['note'])) ? ' (' . htmlspecialchars($lin['note']) . ')' : '' ?><br>
        <?php endif; ?>
      <?php elseif (isset($result['gfi']) && $result['gfi']['GFI'] !== null): ?>
        GFI (Global Fragility Index) = <?= $result['gfi']['GFI'] ?? 'NULL' ?><br>
        GFQ (Global Fragility Quotient) = <?= number_format($result['gfi']['GFQ'] ?? 0, 4) ?> (<?= $result['gfi']['verified'] ? 'Exact' : 'Estimated' ?>)<br>
        [GFI significance test: two-sided Fisher's exact; baseline p = <?= number_format((float)($result['gfi']['baseline_p'] ?? 0), 6) ?>]<br>
        <?php if (!empty($result['gfi']['post_GFI'])): ?>
        Post-GFI table: {<?= (int)($result['gfi']['post_GFI']['a'] ?? 0) ?>, <?= (int)($result['gfi']['post_GFI']['b'] ?? 0) ?>, <?= (int)($result['gfi']['post_GFI']['c'] ?? 0) ?>, <?= (int)($result['gfi']['post_GFI']['d'] ?? 0) ?>}<br>
        Post-GFI p-value = <?= number_format((float)($result['gfi']['post_GFI_p'] ?? 0), 6) ?><br>
        <?php endif; ?>
      <?php else: ?>
      GFI (Global Fragility Index) = NULL (<?= is_array($result['gfi'] ?? []) ? ($result['gfi']['note'] ?? 'Not computed') : 'Not computed' ?>)<br>
      GFQ (Global Fragility Quotient) = NULL<br>
      <?php endif; ?>
      </p>
      <h3>Robustness (nb)</h3>
      <p>
      <?php $ndi = $result['nb']['ndi'] ?? null; ?>
      <?php if (is_array($ndi) && $ndi['NDI'] !== null): ?>
      NDI (Neutrality Distance Index) = <?= (int)$ndi['NDI'] ?><br>
      [coupled fixed-margin moves to the reachable point closest to neutrality (ad − bc = 0); one coupled move relocates two patients, one per arm]<br>
      Cross-product difference (ad − bc) = <?= (int)$ndi['D'] ?><br>
      <?php if ((int)$ndi['NDI'] > 0): ?>
      Post-NDI table: {<?= (int)$ndi['post_table']['a'] ?>, <?= (int)$ndi['post_table']['b'] ?>, <?= (int)$ndi['post_table']['c'] ?>, <?= (int)$ndi['post_table']['d'] ?>}<br>
      Post-NDI cross-product difference = <?= (int)$ndi['residual_D'] ?> (<?= !empty($ndi['exact_neutrality']) ? 'exact neutrality reached' : 'closest reachable approach; exact neutrality not on the integer lattice' ?>)<br>
      <?php elseif (!empty($ndi['exact_neutrality'])): ?>
      Table is at exact neutrality.<br>
      <?php else: ?>
      Table is already at the closest reachable approach to neutrality.<br>
      <?php endif; ?>
      <?php if (!empty($ndi['clamped'])): ?>
      [Result clamped at the reachability window: only <?= (int)$ndi['NDI'] ?> coupled move(s) available in the <?= htmlspecialchars($ndi['direction'] ?? '') ?> direction]<br>
      <?php endif; ?>
      <?php else: ?>
      NDI (Neutrality Distance Index) = NULL<br>
      <?php endif; ?>
      <br>
      RQ (Risk Quotient) = <?= number_format($result['nb']['RQ'] ?? 0, 4) ?></p>
      <h3>Effect Size</h3>
      <p>
      <?php $absEff = $result['effect']['absolute'] ?? null; ?>
      <?php if (is_array($absEff) && $absEff['RD'] !== null): ?>
        Risk in Arm A = <?= number_format($absEff['risk_A'] * 100, 2) ?>%; risk in Arm B = <?= number_format($absEff['risk_B'] * 100, 2) ?>%<br>
        Risk difference (Arm A − Arm B) = <?= number_format($absEff['RD'], 4) ?><br>
        <?= (int)$absEff['CI_level'] ?>% CI = (<?= number_format($absEff['CI_lower'], 4) ?>, <?= number_format($absEff['CI_upper'], 4) ?>) [Newcombe hybrid score]<br>
        <?php if ($absEff['NNT'] !== null): ?>
        NNT (Number Needed to Treat) = <?= (int)$absEff['NNT'] ?> [1 / |risk difference|, rounded up; a benefit or a harm depending on whether the event is desirable]<br>
          <?php if ($absEff['NNT_CI_lower'] !== null): ?>
        <?= (int)$absEff['CI_level'] ?>% CI for NNT = (<?= (int)$absEff['NNT_CI_lower'] ?>, <?= (int)$absEff['NNT_CI_upper'] ?>)<br>
          <?php else: ?>
        [The risk-difference interval includes 0, so the NNT interval is unbounded]<br>
          <?php endif; ?>
        <?php else: ?>
        NNT = not defined (the risks are equal)<br>
        <?php endif; ?>
      <?php else: ?>
        Risk difference = not calculable<?= (is_array($absEff) && !empty($absEff['note'])) ? ' (' . htmlspecialchars($absEff['note']) . ')' : '' ?><br>
      <?php endif; ?>
      <br>
      <?php
        $eff = $result['effect'] ?? [];
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
      <h3>Resampling Fragility</h3>
      <p>
      <?php $rsf = $result['resampling'] ?? null; ?>
      <?php if (is_array($rsf) && $rsf['reversal_probability'] !== null): ?>
      Resampling Fragility (RF) = probability of significance classification reversal with resampling = <?= number_format($rsf['reversal_probability'], 4) ?> (<?= number_format($rsf['reversal_probability'] * 100, 1) ?>%)<br>
      Retention probability = 1 - RF = <?= number_format($rsf['retention_probability'], 4) ?> (<?= number_format($rsf['retention_probability'] * 100, 1) ?>%)<br>
      <?php if ($rsf['reversal_probability'] > 0): ?>
      <?php
        $nnr = 1 / $rsf['reversal_probability'];
        $retentionOdds = $rsf['retention_probability'] / $rsf['reversal_probability'];
      ?>
      Number Needed to Reverse significance classification (NNR) = 1 / RF = <?= number_format($nnr, 1) ?><br>
      An NNR of <?= number_format($nnr, 1) ?> means that out of <?= number_format($nnr, 1) ?> trials of the same size from the same population, 1 trial is expected to reverse the significance classification. This table is <?= $rsf['baseline_significant'] ? 'significant' : 'nonsignificant' ?> at baseline (p <?= $rsf['baseline_significant'] ? '&lt;' : '&ge;' ?> <?= number_format($rsf['alpha'], 2) ?>), so reversal means becoming <?= $rsf['baseline_significant'] ? 'nonsignificant' : 'significant' ?>. Retention of original significance classification-to-reversal odds = <?= number_format($retentionOdds, 1) ?> to 1.<br>
      <?php else: ?>
      Number Needed to Reverse significance classification (NNR) = not defined (RF = 0; no replicate table reverses the classification)<br>
      <?php endif; ?>
      [Estimated probability that the significance classification (baseline: <?= $rsf['baseline_significant'] ? 'significant' : 'nonsignificant' ?>) would reverse in a new sample of the same size. Replication model: independent binomial draws per arm at the observed event rates, arm sizes fixed; two-sided Fisher's exact test recomputed for every replicate table, alpha = <?= number_format($rsf['alpha'], 2) ?>. Computed exactly by summation over all replicate tables, not by simulation.]<br>
      <span style="font-size:13px; color:#555;">Resampling fragility is a form of data fragility in the taxonomy of statistical fragility (analysis, resampling, perturbation, scaling). Its value depends on the stated replication model, so it is reported as a separate category: it is not part of the p–fr–nb triplet, whose fr coordinate is a model-free perturbation-fragility quotient computed from the observed table alone.</span>
      <?php else: ?>
      Resampling fragility = NULL<?= (is_array($rsf) && !empty($rsf['note'])) ? ' (' . htmlspecialchars($rsf['note']) . ')' : '' ?><br>
      <?php endif; ?>
      </p>
      <hr style="margin: 22px 0; border: 0; border-top: 1px solid #ccc;">
      <p class="pfr-citation" style="font-size:13px; color:#555; margin-bottom:6px;">
        <strong>Note:</strong> The FI used here is the Heston Fragility Index: the minimum number of outcome toggles (event ↔ non-event) in the arm with fewer events, or the smaller arm if events are tied, that flips significance in either direction, so it is also defined for nonsignificant results. For the original Walsh 2014 FI definition, see the <a href="http://fragilitymetrics.org/calculate_original.php">Walsh 2014 FI Calculator</a>.
      </p>
      <p class="pfr-citation">
        <strong>Citation:</strong> Heston TF. Fragility metrics toolkit v6.0.0. Zenodo. 2026. DOI: <a href="https://doi.org/10.5281/zenodo.17254763">10.5281/zenodo.17254763</a>
      </p>
    </div>
  <?php endif; ?>
</div>
<script>
  (function () {
    const form = document.getElementById('pfr-form');
    const btn = document.getElementById('pfr-reset');
    const res = document.getElementById('pfr-result');
    const err = document.getElementById('pfr-error');
    if (!btn || !form) return;
    btn.addEventListener('click', function () {
      form.querySelectorAll('input[type="number"]').forEach(i => { i.value = ''; });
      if (res) res.style.display = 'none';
      if (err) err.style.display = 'none';
    });
  })();
</script>
<?php include 'includes/footer.php'; ?>
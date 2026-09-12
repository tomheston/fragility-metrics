<?php
require_once 'FragilityCalculator.php';
require_once 'DatabaseManager.php';

$result = null;
$error = null;
$saved_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = filter_input(INPUT_POST, 'a', FILTER_VALIDATE_INT);
    $b = filter_input(INPUT_POST, 'b', FILTER_VALIDATE_INT);
    $c = filter_input(INPUT_POST, 'c', FILTER_VALIDATE_INT);
    $d = filter_input(INPUT_POST, 'd', FILTER_VALIDATE_INT);

    if ($a === false || $b === false || $c === false || $d === false) {
        $error = "All values must be non-negative integers.";
    } elseif ($a < 0 || $b < 0 || $c < 0 || $d < 0) {
        $error = "All values must be non-negative.";
    } else {
        try {
            $result = FragilityCalculator::calculate($a, $b, $c, $d);
            $saved_id = DatabaseManager::saveCalculation($result);
        } catch (Exception $e) {
            $error = "Calculation error: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<style>
  /* Tighten the vertical rhythm above the form/table */
  .pfr-page { max-width: 980px; margin: 0 auto; padding: 8px 20px 24px; }
  .pfr-page > :first-child { margin-top: 0; }
  .pfr-page h2 { margin: 0 0 6px; }
  .pfr-page .pfr-lead { margin: 0 0 10px; }

  .pfr-form { margin: 10px 0; padding: 14px; border-radius: 8px; background: #fafafa; }
  .pfr-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 12px; }

  .pfr-buttons { display: flex; gap: 8px; }
</style>

<div class="pfr-page">
  <h2>p-fr-nb Calculator</h2>
  <p class="pfr-lead">Calculate complete statistical evidence (p-fr-nb triplet) for 2×2 independent binary outcome tables.</p>

  <?php if ($error): ?>
    <div id="pfr-error" style="background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin: 10px 0;">
      <strong>Error:</strong> <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form id="pfr-form" method="POST" class="pfr-form">
    <div class="pfr-grid">
      <div>
        <label for="a" style="display:block; margin-bottom:5px; font-weight:bold;">a (Arm A events):</label>
        <input type="number" id="a" name="a" min="0" required
               value="<?= isset($_POST['a']) ? htmlspecialchars($_POST['a']) : '' ?>"
               style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
      </div>
      <div>
        <label for="b" style="display:block; margin-bottom:5px; font-weight:bold;">b (Arm A non-events):</label>
        <input type="number" id="b" name="b" min="0" required
               value="<?= isset($_POST['b']) ? htmlspecialchars($_POST['b']) : '' ?>"
               style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
      </div>
      <div>
        <label for="c" style="display:block; margin-bottom:5px; font-weight:bold;">c (Arm B events):</label>
        <input type="number" id="c" name="c" min="0" required
               value="<?= isset($_POST['c']) ? htmlspecialchars($_POST['c']) : '' ?>"
               style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
      </div>
      <div>
        <label for="d" style="display:block; margin-bottom:5px; font-weight:bold;">d (Arm B non-events):</label>
        <input type="number" id="d" name="d" min="0" required
               value="<?= isset($_POST['d']) ? htmlspecialchars($_POST['d']) : '' ?>"
               style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
      </div>
    </div>

    <div class="pfr-buttons">
      <button type="submit"
              style="background:#007bff; color:white; padding:6px 15px; border:none; border-radius:2px; cursor:pointer; font-size:16px; font-weight:bold;">
        Calculate p-fr-nb
      </button>

      <!-- Make Reset actually clear after POST -->
      <button type="button" id="pfr-reset"
              style="background:#6c757d; color:white; padding:6px 15px; border:none; border-radius:2px; cursor:pointer; font-size:16px; font-weight:bold;">
        Reset
      </button>
    </div>
  </form>

  <?php if ($result): ?>
    <div id="pfr-result" style="margin-top: 8px;">
      <h3>Significance (p)</h3>
      <p>baseline p-value = <?= number_format($result['p']['value'], 6) ?>&nbsp;
      <?php if (!empty($result['fr']['post_FI'])): ?>and &nbsp;post-FI p-value = <?= number_format((float)$result['fr']['post_FI_p'], 6) ?><?php endif; ?>
      </p>
      <h3>Fragility (fr)</h3>
      <p>FI (Fragility Index) = <?= $result['fr']['FI'] ?? 'NULL' ?><br/>
      FQ (Fragility Quotient) = <?= $result['fr']['FQ'] !== null ? number_format($result['fr']['FQ'], 6) : 'NULL' ?><br/>
      MFQ (Modified-Arm Fragility Quotient) = <?= $result['fr']['MFQ'] !== null ? number_format($result['fr']['MFQ'], 6) : 'NULL' ?><br/>
      <?php if (!empty($result['fr']['post_FI'])): ?>      
      Post-FI table: {<?= (int)$result['fr']['post_FI']['a'] ?>, <?= (int)$result['fr']['post_FI']['b'] ?>, <?= (int)$result['fr']['post_FI']['c'] ?>, <?= (int)$result['fr']['post_FI']['d'] ?>}<br/>
      <?php endif; ?>
      <?php if (isset($result['gfi']) && $result['gfi']['GFI'] !== null): ?>
        GFI (Global Fragility Index) = <?= $result['gfi']['GFI'] ?><br/>
        GFQ (Global Fragility Quotient) = <?= number_format($result['gfi']['GFQ'], 6) ?> (<?= $result['gfi']['verified'] ? 'Exact' : 'Estimated' ?>)<br/>
	<?php if (!empty($result['gfi']['post_GFI'])): ?>
	    Post-GFI table: {<?= (int)$result['gfi']['post_GFI']['a'] ?>, <?= (int)$result['gfi']['post_GFI']['b'] ?>, <?= (int)$result['gfi']['post_GFI']['c'] ?>, <?= (int)$result['gfi']['post_GFI']['d'] ?>}<br/>
	    Post-GFI p-value = <?= number_format((float)$result['gfi']['post_GFI_p'], 6) ?><br/>
	<?php endif; ?>
      <?php else: ?>
	GFI (Global Fragility Index) = NULL (<?= is_array($result['gfi'] ?? null) ? ($result['gfi']['note'] ?? 'Not computed') : 'Not computed' ?>)<br/>
	GFQ (Global Fragility Quotient) = NULL<br/>
      <?php endif; ?>
      </p>
      <h3>Robustness (nb)</h3>
      <p>RQ (Risk Quotient) = <?= number_format($result['nb']['RQ'], 6) ?></p>

      <hr style="margin: 22px 0; border: 0; border-top: 1px solid #ccc;">

      <p style="font-size: 14px; color: #666;">
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
      // Clear inputs (not "reset to POSTed values")
      form.querySelectorAll('input[type="number"]').forEach(i => { i.value = ''; });

      // Hide server-rendered blocks (result/error) for the current view
      if (res) res.style.display = 'none';
      if (err) err.style.display = 'none';
    });
  })();
</script>

<?php include 'includes/footer.php'; ?>

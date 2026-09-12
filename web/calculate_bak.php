<?php
require_once 'FragilityCalculator.php';
require_once 'DatabaseManager.php';

$result = null;
$error = null;
$saved_id = null;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
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
            // Calculate
            $result = FragilityCalculator::calculate($a, $b, $c, $d);
            
            // Save to database
            $saved_id = DatabaseManager::saveCalculation($result);
            
        } catch (Exception $e) {
            $error = "Calculation error: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<h2>p-fr-nb Calculator</h2>

<p>Calculate complete statistical evidence (p-fr-nb triplet) for 2×2 independent binary outcome tables.</p>


<?php if ($error): ?>
<div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin: 20px 0;">
    <strong>Error:</strong> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form method="POST" style="background: #fafafa; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
        <div>
            <label for="a" style="display: block; margin-bottom: 5px; font-weight: bold;">a (Arm A events):</label>
            <input type="number" id="a" name="a" min="0" required 
                   value="<?= isset($_POST['a']) ? htmlspecialchars($_POST['a']) : '' ?>"
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div>
            <label for="b" style="display: block; margin-bottom: 5px; font-weight: bold;">b (Arm A non-events):</label>
            <input type="number" id="b" name="b" min="0" required 
                   value="<?= isset($_POST['b']) ? htmlspecialchars($_POST['b']) : '' ?>"
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div>
            <label for="c" style="display: block; margin-bottom: 5px; font-weight: bold;">c (Arm B events):</label>
            <input type="number" id="c" name="c" min="0" required 
                   value="<?= isset($_POST['c']) ? htmlspecialchars($_POST['c']) : '' ?>"
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        <div>
            <label for="d" style="display: block; margin-bottom: 5px; font-weight: bold;">d (Arm B non-events):</label>
            <input type="number" id="d" name="d" min="0" required 
                   value="<?= isset($_POST['d']) ? htmlspecialchars($_POST['d']) : '' ?>"
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
    </div>
    
    <div style="display: flex; gap: 2px;">
        <button type="submit" style="background: #007bff; color: white; padding: 6px 15px; border: none; border-radius: 2px; cursor: pointer; font-size: 16px; font-weight: bold;">
            Calculate p-fr-nb
        </button>
        <button type="reset" style="background: #6c757d; color: white; padding: 6px 15px; border: none; border-radius: 2px; cursor: pointer; font-size: 16px; font-weight: bold;">
            Reset
        </button>
    </div>
</form>

<?php if ($result): ?>
<div style="margin-top: 10px;">
    <p><strong>Table:</strong> a=<?= $result['input']['a'] ?>, b=<?= $result['input']['b'] ?>, c=<?= $result['input']['c'] ?>, d=<?= $result['input']['d'] ?> (N=<?= $result['input']['N'] ?>)</p>

    <h3>Significance (p)</h3>
    <p>baseline p-value = <?= number_format($result['p']['value'], 6) ?> (<?= $result['p']['significant'] ? 'significant' : 'non-significant' ?> at α=<?= $result['p']['alpha'] ?>)</p>

    <h3>Fragility (fr)</h3>
    <p>FI (Fragility Index) = <?= $result['fr']['FI'] ?? 'NULL' ?><br/>
    FQ (Fragility Quotient) = <?= $result['fr']['FQ'] !== null ? number_format($result['fr']['FQ'], 6) : 'NULL' ?><br/>
    MFQ (Modified-Arm Fragility Quotient) = <?= $result['fr']['MFQ'] !== null ? number_format($result['fr']['MFQ'], 6) : 'NULL' ?><br/>
    <?php if (isset($result['gfi']) && $result['gfi']['GFI'] !== null): ?>
    GFI (Global Fragility Index) = <?= $result['gfi']['GFI'] ?><br/>
    GFQ (Global Fragility Quotient) = <?= number_format($result['gfi']['GFQ'], 6) ?> (<?= $result['gfi']['verified'] ? 'Exact' : 'Estimated' ?>)<br/>
    <?php else: ?>
    GFI (Global Fragility Index) = NULL (<?= $result['gfi']['note'] ?? 'Not computed' ?>)<br/>
    GFQ (Global Fragility Quotient) = NULL<br/>
    <?php endif; ?>

    <h3>Robustness (nb)</h3>
    <p>RQ (Risk Quotient) = <?= number_format($result['nb']['RQ'], 6) ?></p>

    <hr style="margin: 30px 0; border: 0; border-top: 1px solid #ccc;">

    <p style="font-size: 14px; color: #666;">
        <strong>Citation:</strong> Heston TF. Fragility Metrics Calculators. Zenodo. DOI = (pending)
    </p>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

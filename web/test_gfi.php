<?php
require_once 'GlobalFragilityIndex.php';
require_once 'FragilityIndex.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GFI Calculator - Test Results</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .test { margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #007bff; }
        .pass { border-left-color: #28a745; background: #d4edda; }
        .warning { border-left-color: #ffc107; background: #fff3cd; }
        table { border-collapse: collapse; margin: 10px 0; }
        td, th { border: 1px solid #ddd; padding: 6px 10px; text-align: left; }
        th { background: #f0f0f0; font-weight: bold; }
        .value { font-weight: bold; color: #0066cc; }
    </style>
</head>
<body>
<div class="container">
    <h1>GFI Calculator - Test Results (v2)</h1>
    <p><strong>Goal:</strong> Verify GFI ≤ FI always (mathematical guarantee)</p>
    
    <div class="test">
        <h3>Test 1: Small table (should verify)</h3>
        <?php
        $a = 15; $b = 85; $c = 25; $d = 75;
        $N = $a + $b + $c + $d;
        
        $fi_result = FragilityIndex::calculate($a, $b, $c, $d);
        $gfi_result = GlobalFragilityIndex::calculate($a, $b, $c, $d);
        
        $valid = true;
        if ($gfi_result['GFI'] !== null && $fi_result['FI'] !== null) {
            $valid = ($gfi_result['GFI'] <= $fi_result['FI']);
        }
        ?>
        <p><strong>Table:</strong> {a=<?= $a ?>, b=<?= $b ?>, c=<?= $c ?>, d=<?= $d ?>} (N=<?= $N ?>)</p>
        
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>FI (Walsh)</td><td class="value"><?= $fi_result['FI'] ?? 'NULL' ?></td></tr>
            <tr><td>MFQ</td><td class="value"><?= $fi_result['MFQ'] ?? 'NULL' ?></td></tr>
            <tr><td>GFI (Gold Standard)</td><td class="value"><?= $gfi_result['GFI'] ?? 'NULL' ?></td></tr>
            <tr><td>GFQ</td><td class="value"><?= $gfi_result['GFQ'] ?? 'NULL' ?></td></tr>
            <tr><td>GFI Verified?</td><td><?= $gfi_result['verified'] ? 'YES' : 'NO' ?></td></tr>
            <tr><td>Method</td><td><?= $gfi_result['method'] ?></td></tr>
        </table>
        
        <p><strong>Validation:</strong> GFI ≤ FI? 
            <span style="color: <?= $valid ? '#28a745' : '#dc3545' ?>; font-weight: bold;">
                <?= $valid ? '✓ PASS' : '✗ FAIL' ?>
            </span>
        </p>
        
        <?php if (isset($gfi_result['note'])): ?>
        <p><em><?= htmlspecialchars($gfi_result['note']) ?></em></p>
        <?php endif; ?>
    </div>
    
    <div class="test">
        <h3>Test 2: Very stable result (GFI should be larger)</h3>
        <?php
        $a = 50; $b = 50; $c = 10; $d = 90;
        $N = $a + $b + $c + $d;
        
        $fi_result = FragilityIndex::calculate($a, $b, $c, $d);
        $gfi_result = GlobalFragilityIndex::calculate($a, $b, $c, $d);
        
        $valid = true;
        if ($gfi_result['GFI'] !== null && $fi_result['FI'] !== null) {
            $valid = ($gfi_result['GFI'] <= $fi_result['FI']);
        }
        ?>
        <p><strong>Table:</strong> {a=<?= $a ?>, b=<?= $b ?>, c=<?= $c ?>, d=<?= $d ?>} (N=<?= $N ?>)</p>
        
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>FI</td><td class="value"><?= $fi_result['FI'] ?? 'NULL' ?></td></tr>
            <tr><td>MFQ</td><td class="value"><?= $fi_result['MFQ'] ?? 'NULL' ?></td></tr>
            <tr><td>GFI</td><td class="value"><?= $gfi_result['GFI'] ?? 'NULL' ?></td></tr>
            <tr><td>GFQ</td><td class="value"><?= $gfi_result['GFQ'] ?? 'NULL' ?></td></tr>
            <tr><td>Method</td><td><?= $gfi_result['method'] ?? 'N/A' ?></td></tr>
        </table>
        
        <p><strong>Validation:</strong> GFI ≤ FI? 
            <span style="color: <?= $valid ? '#28a745' : '#dc3545' ?>; font-weight: bold;">
                <?= $valid ? '✓ PASS' : '✗ FAIL' ?>
            </span>
        </p>
    </div>
    
    <div class="test">
        <h3>Test 3: Large N (should skip GFI)</h3>
        <?php
        $a = 500; $b = 4500; $c = 600; $d = 4400;
        $N = $a + $b + $c + $d;
        
        $gfi_result = GlobalFragilityIndex::calculate($a, $b, $c, $d);
        ?>
        <p><strong>Table:</strong> {a=<?= $a ?>, b=<?= $b ?>, c=<?= $c ?>, d=<?= $d ?>} (N=<?= $N ?>)</p>
        
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>GFI</td><td class="value"><?= $gfi_result['GFI'] ?? 'NULL (expected)' ?></td></tr>
            <tr><td>Method</td><td><?= $gfi_result['method'] ?? 'N/A' ?></td></tr>
        </table>
        
        <?php if (isset($gfi_result['note'])): ?>
        <p><em><?= htmlspecialchars($gfi_result['note']) ?></em></p>
        <?php endif; ?>
        <p><strong>Expected:</strong> GFI should be NULL (skipped for large N)</p>
    </div>
    
    <hr>
    <h3>Post-Toggle Comparison</h3>
    <?php
    $a = 15; $b = 85; $c = 25; $d = 75;
    $fi_result = FragilityIndex::calculate($a, $b, $c, $d);
    $gfi_result = GlobalFragilityIndex::calculate($a, $b, $c, $d);
    ?>
    <p><strong>Baseline:</strong> {a=<?= $a ?>, b=<?= $b ?>, c=<?= $c ?>, d=<?= $d ?>}</p>
    
    <table>
        <tr>
            <th>Method</th>
            <th>Toggles</th>
            <th>Post-Toggle Table</th>
            <th>Post p-value</th>
        </tr>
        <tr>
            <td>FI (Walsh)</td>
            <td><?= $fi_result['FI'] ?? 'N/A' ?></td>
            <td>
                <?php if (isset($fi_result['post_toggle']['a'])): ?>
                {a=<?= $fi_result['post_toggle']['a'] ?>, b=<?= $fi_result['post_toggle']['b'] ?>, 
                 c=<?= $fi_result['post_toggle']['c'] ?>, d=<?= $fi_result['post_toggle']['d'] ?>}
                <?php else: ?>
                N/A
                <?php endif; ?>
            </td>
            <td><?= $fi_result['final_p'] ?? 'N/A' ?></td>
        </tr>
        <tr>
            <td>GFI (Global)</td>
            <td><?= $gfi_result['GFI'] ?? 'N/A' ?></td>
            <td>
                <?php if (isset($gfi_result['post_toggle']['a']) && $gfi_result['post_toggle']['a'] !== null): ?>
                {a=<?= $gfi_result['post_toggle']['a'] ?>, b=<?= $gfi_result['post_toggle']['b'] ?>, 
                 c=<?= $gfi_result['post_toggle']['c'] ?>, d=<?= $gfi_result['post_toggle']['d'] ?>}
                <?php else: ?>
                N/A
                <?php endif; ?>
            </td>
            <td><?= $gfi_result['final_p'] ?? 'N/A' ?></td>
        </tr>
    </table>
    
    <hr>
    <p><strong>✓ GFI Calculator Tests Complete</strong></p>
    <p><a href="calculate.php">← Back to Calculator</a></p>
</div>
</body>
</html>

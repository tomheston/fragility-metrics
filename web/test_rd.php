<?php
/**
 * test_rd.php
 *
 * Checks RiskDifference.php: risk difference (Arm A - Arm B), its Newcombe
 * hybrid score 95% CI, and the NNT.
 *
 * The first five cases are worked examples published in Newcombe RG,
 * Stat Med 1998;17:873-890 (Table II, method 10), given there to 4 decimals.
 * The rest are site examples, with values from an independent Python
 * implementation of the same method.
 *
 * Run from the browser, or from the CLI:  php test_rd.php
 */

require_once 'RiskDifference.php';

// a, b, c, d, RD, CI lower, CI upper, tolerance, NNT, NNT CI lower, NNT CI upper
$cases = array(
    array(56, 14, 48, 32,  0.2000,  0.0524,  0.3339, 6e-5, 5, 3, 20),
    array(9, 1, 3, 7,      0.6000,  0.1705,  0.8090, 6e-5, 2, 2, 6),
    array(5, 51, 0, 29,    0.0893, -0.0381,  0.1926, 6e-5, 12, null, null),
    array(0, 10, 0, 20,    0.0000, -0.1611,  0.2775, 6e-5, null, null, null),
    array(10, 0, 0, 20,    1.0000,  0.6791,  1.0000, 6e-5, 1, 1, 2),
    array(15, 85, 25, 75, -0.100000, -0.209085,  0.011441, 2e-6, 10, null, null),
    array(2, 98, 10, 90,  -0.080000, -0.155766, -0.012876, 2e-6, 13, 7, 78),
    array(0, 20, 7, 13,   -0.350000, -0.567146, -0.116639, 2e-6, 3, 2, 9),
    array(5, 5, 5, 5,      0.000000, -0.372514,  0.372514, 2e-6, null, null, null),
    array(10, 4, 10, 22,   0.401786,  0.088750,  0.616436, 2e-6, 3, 2, 12),
);

$cli = (php_sapi_name() === 'cli');

$rows = array();
$pass = 0;

foreach ($cases as $case) {
    list($a, $b, $c, $d, $expRd, $expLo, $expHi, $tol, $expNnt, $expNntLo, $expNntHi) = $case;
    $r = RiskDifference::calculate($a, $b, $c, $d);

    $ok = abs($r['RD'] - $expRd) <= $tol
        && abs($r['CI_lower'] - $expLo) <= $tol
        && abs($r['CI_upper'] - $expHi) <= $tol
        && $r['NNT'] === $expNnt
        && $r['NNT_CI_lower'] === $expNntLo
        && $r['NNT_CI_upper'] === $expNntHi;
    if ($ok) {
        $pass++;
    }

    $rows[] = sprintf("{%d, %d, %d, %d}  RD %.6f  CI (%.6f, %.6f)  NNT %s  NNT CI %s  %s",
        $a, $b, $c, $d, $r['RD'], $r['CI_lower'], $r['CI_upper'],
        $r['NNT'] === null ? 'none' : $r['NNT'],
        $r['NNT_CI_lower'] === null ? 'unbounded' : '(' . $r['NNT_CI_lower'] . ', ' . $r['NNT_CI_upper'] . ')',
        $ok ? 'PASS' : 'FAIL');
}

$allPass = ($pass === count($cases));
$summary = sprintf("%d/%d risk-difference cases passed\nOVERALL: %s", $pass, count($cases), $allPass ? 'PASS' : 'FAIL');

if ($cli) {
    echo "RiskDifference.php checks\n" . str_repeat('=', 78) . "\n";
    echo implode("\n", $rows) . "\n\n" . $summary . "\n";
    exit($allPass ? 0 : 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Risk Difference Test</title>
<style>
  body { font-family: -apple-system, Segoe UI, sans-serif; padding: 24px; background: #f5f5f5; }
  .container { max-width: 1100px; margin: 0 auto; background: #fff; padding: 24px; border-radius: 8px; }
  .banner { padding: 14px 18px; border-radius: 6px; font-weight: bold; font-size: 18px; margin: 16px 0; }
  .banner.pass { background: #d4edda; color: #155724; }
  .banner.fail { background: #f8d7da; color: #721c24; }
  pre { background: #f7f7f7; padding: 12px; overflow-x: auto; font-size: 13px; }
</style>
</head>
<body>
<div class="container">
  <h1>Risk Difference Test</h1>
  <div class="banner <?= $allPass ? 'pass' : 'fail' ?>"><?= $allPass ? 'ALL TESTS PASS' : 'FAILURES DETECTED' ?></div>
  <pre><?= htmlspecialchars(implode("\n", $rows) . "\n\n" . $summary) ?></pre>
  <p><a href="calculate.php">&larr; Back to calculator</a></p>
</div>
</body>
</html>

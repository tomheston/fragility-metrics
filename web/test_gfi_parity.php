<?php
/**
 * test_gfi_parity.php
 *
 * Parity harness for GlobalFragilityIndex.php.
 *
 * Every expected value below was produced by the reference Python
 * implementation (v.14-AUG-2026: exhaustive shell search, two-sided
 * Fisher's exact for ALL significance decisions, alpha = 0.05).
 * A green board means the PHP port agrees with the Python program on GFI,
 * on the exact post-GFI table reached, and on the p-value at the flip.
 *
 * Also checks the structural invariant GFI <= FI (with a single shared
 * significance test, the global search can never need more moves than the
 * within-arm Walsh search), mirroring the regression check in the Python
 * reference.
 *
 * Run from the browser, or from the CLI:  php test_gfi_parity.php
 */

require_once 'GlobalFragilityIndex.php';
require_once 'FragilityIndex.php';
require_once 'FisherExactTest.php';

$cases = array(
    // a, b, c, d, expected GFI, expected post-GFI table, expected final p
    array(15, 85, 25, 75, 2, array(14,84,27,75), 0.036593),
    array(50, 50, 10, 90, 20, array(31,50,30,89), 0.060534),
    array(10, 10, 10, 10, 7, array(9,10,17,4), 0.045702),
    array(1, 10, 9, 2, 3, array(1,10,6,5), 0.063467),
    array(0, 20, 7, 13, 2, array(2,19,6,13), 0.120204),
    array(2, 98, 10, 90, 1, array(2,98,9,91), 0.058221),
    array(5, 95, 15, 85, 1, array(5,95,14,86), 0.051306),
    array(30, 70, 45, 55, 1, array(30,70,44,56), 0.056568),
    array(100, 100, 120, 80, 1, array(99,100,121,80), 0.044292),
    array(3, 3, 3, 3, 4, array(2,3,7,0), 0.045455),
    array(7, 0, 0, 7, 3, array(4,0,3,7), 0.069930),
    // No-flip tables: GFI undefined, final_p = baseline p (reference behavior).
    array(0, 1, 1, 0, null, null, 1.0),
    array(25, 25, 25, 25, 10, array(24,27,33,16), 0.045960),
    array(45, 5, 5, 45, 27, array(44,16,21,19), 0.053262),
    array(90, 10, 10, 90, 59, array(89,36,43,32), 0.063832),
    array(1, 1, 1, 1, null, null, 1.0),
    array(4, 6, 6, 4, 3, array(1,7,8,4), 0.028102),
    array(12, 88, 20, 80, 2, array(10,88,22,80), 0.034005),
    array(200, 300, 250, 250, 18, array(218,299,233,250), 0.056602),
    array(6, 14, 14, 6, 1, array(6,13,14,7), 0.056161),
    array(30, 11, 46, 37, 1, array(30,10,46,38), 0.032473),
    array(19, 12, 46, 26, 7, array(13,19,45,26), 0.034853),
    array(48, 45, 48, 16, 5, array(48,44,44,21), 0.070102),
    array(34, 15, 40, 47, 3, array(33,18,40,45), 0.052271),
    array(31, 22, 26, 33, 3, array(31,19,26,36), 0.038742),
    array(46, 39, 13, 19, 3, array(46,38,11,22), 0.041987),
    array(34, 45, 21, 33, 7, array(35,45,14,39), 0.045922),
    array(4, 46, 49, 13, 26, array(18,21,48,25), 0.068909),
    array(44, 48, 46, 29, 2, array(43,48,48,28), 0.043905),
    array(45, 41, 9, 34, 6, array(44,41,15,29), 0.064270),
    array(13, 26, 3, 49, 3, array(10,26,6,49), 0.050499),
    array(22, 40, 26, 29, 4, array(21,40,30,26), 0.041982),
    array(7, 46, 47, 8, 26, array(16,20,47,25), 0.061619),
    array(48, 20, 24, 21, 1, array(48,19,24,22), 0.046435),
    array(22, 50, 12, 20, 5, array(21,50,17,16), 0.048015),
    array(27, 26, 20, 36, 3, array(27,25,18,39), 0.034557),
    array(13, 26, 14, 13, 2, array(12,26,16,12), 0.046841),
    array(2, 47, 14, 48, 2, array(4,46,13,48), 0.065730),
    array(1, 16, 32, 20, 5, array(6,13,30,20), 0.057701),
    array(49, 36, 45, 26, 7, array(48,43,45,20), 0.047337),
);

// Fisher's exact spot-checks against scipy.stats.fisher_exact.
$fisherCases = array(
    array(15, 85, 25, 75, 0.110880),
    array(10, 10, 10, 10, 1.000000),
    array(50, 10, 10, 50, 0.000000),
    array(20, 63, 9, 26, 1.000000),
    array(6, 39, 22, 109, 0.645748),
    array(12, 98, 21, 103, 0.194912),
    array(35, 16, 75, 61, 0.132778),
    array(2, 98, 10, 90, 0.033040),
    array(1, 10, 9, 2, 0.001905),
);

$cli = (php_sapi_name() === 'cli');

$results = array();
$pass = 0;
$totalTime = 0.0;

foreach ($cases as $case) {
    list($a, $b, $c, $d, $expGfi, $expTable, $expFinalP) = $case;

    $t0 = microtime(true);
    $r = GlobalFragilityIndex::calculate($a, $b, $c, $d, 0.05);
    $elapsed = microtime(true) - $t0;
    $totalTime += $elapsed;

    $gotGfi = $r['GFI'];
    $gotTable = null;
    if ($r['post_toggle']['a'] !== null) {
        $gotTable = array(
            (int)$r['post_toggle']['a'], (int)$r['post_toggle']['b'],
            (int)$r['post_toggle']['c'], (int)$r['post_toggle']['d'],
        );
    }
    $gotFinalP = $r['final_p'];

    // The frozen test must always be Fisher's exact (except empty/oversize
    // tables, which never reach the search).
    $okTest = ($r['test_used'] === 'fisher_exact');

    // Structural invariant: GFI <= FI whenever both are defined.
    $fi = FragilityIndex::calculate($a, $b, $c, $d, 0.05);
    $okInvariant = true;
    if ($gotGfi !== null && $fi['FI'] !== null) {
        $okInvariant = ($gotGfi <= $fi['FI']);
    }

    $okGfi   = ($gotGfi === $expGfi);
    $okTable = ($gotTable === $expTable);
    $okFinalP = ($expFinalP === null)
        ? ($gotFinalP === null)
        : ($gotFinalP !== null && abs($gotFinalP - $expFinalP) < 1e-6);
    $ok = $okGfi && $okTable && $okFinalP && $okTest && $okInvariant;
    if ($ok) { $pass++; }

    $results[] = array(
        'table' => "$a, $b, $c, $d",
        'expGfi' => $expGfi === null ? 'none' : $expGfi,
        'gotGfi' => $gotGfi === null ? 'none' : $gotGfi,
        'expTable' => $expTable === null ? '-' : implode(', ', $expTable),
        'gotTable' => $gotTable === null ? '-' : implode(', ', $gotTable),
        'expFinalP' => $expFinalP === null ? '-' : number_format($expFinalP, 6),
        'gotFinalP' => $gotFinalP === null ? '-' : number_format($gotFinalP, 6),
        'fi' => $fi['FI'] === null ? 'none' : $fi['FI'],
        'ok' => $ok,
        'okGfi' => $okGfi, 'okTable' => $okTable, 'okFinalP' => $okFinalP,
        'okTest' => $okTest, 'okInvariant' => $okInvariant,
        'secs' => $elapsed,
        'evaluated' => $r['candidates_evaluated'],
    );
}

$fisherResults = array();
$fisherPass = 0;
foreach ($fisherCases as $fc) {
    list($a, $b, $c, $d, $expP) = $fc;
    $got = FisherExactTest::calculate($a, $b, $c, $d);
    $ok = (abs($got - $expP) < 1e-6);
    if ($ok) { $fisherPass++; }
    $fisherResults[] = array(
        'table' => "$a, $b, $c, $d",
        'exp' => $expP, 'got' => $got, 'diff' => abs($got - $expP), 'ok' => $ok,
    );
}

$allPass = ($pass === count($cases) && $fisherPass === count($fisherCases));

if ($cli) {
    echo "GFI parity vs reference Python (Fisher's exact only)\n";
    echo str_repeat('=', 78) . "\n";
    foreach ($results as $r) {
        printf("%-20s GFI exp=%-5s got=%-5s FI=%-5s  %s  (%.3fs)\n",
            '{' . $r['table'] . '}', $r['expGfi'], $r['gotGfi'], $r['fi'],
            $r['ok'] ? 'PASS' : 'FAIL', $r['secs']);
        if (!$r['ok']) {
            printf("    table exp={%s} got={%s}  finalP exp=%s got=%s  test=%s invariant=%s\n",
                $r['expTable'], $r['gotTable'], $r['expFinalP'], $r['gotFinalP'],
                $r['okTest'] ? 'ok' : 'BAD', $r['okInvariant'] ? 'ok' : 'VIOLATED');
        }
    }
    printf("\n%d/%d GFI cases passed (%.2fs total)\n", $pass, count($cases), $totalTime);
    echo "\nFisher's exact vs scipy\n";
    echo str_repeat('=', 78) . "\n";
    foreach ($fisherResults as $r) {
        printf("%-20s exp=%.6f got=%.6f diff=%.2e  %s\n",
            '{' . $r['table'] . '}', $r['exp'], $r['got'], $r['diff'],
            $r['ok'] ? 'PASS' : 'FAIL');
    }
    printf("\n%d/%d Fisher cases passed\n", $fisherPass, count($fisherCases));
    printf("\nOVERALL: %s\n", $allPass ? 'PASS' : 'FAIL');
    exit($allPass ? 0 : 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>GFI Parity Test</title>
<style>
  body { font-family: -apple-system, Segoe UI, sans-serif; padding: 24px; background: #f5f5f5; }
  .container { max-width: 1100px; margin: 0 auto; background: #fff; padding: 24px; border-radius: 8px; }
  h1 { margin-top: 0; }
  .banner { padding: 14px 18px; border-radius: 6px; font-weight: bold; font-size: 18px; margin: 16px 0; }
  .banner.pass { background: #d4edda; color: #155724; }
  .banner.fail { background: #f8d7da; color: #721c24; }
  table { border-collapse: collapse; width: 100%; margin: 14px 0; font-size: 13px; font-family: ui-monospace, Consolas, monospace; }
  th, td { border: 1px solid #ddd; padding: 5px 8px; text-align: left; }
  th { background: #f0f0f0; }
  tr.ok td { background: #f6fff6; }
  tr.bad td { background: #fff1f1; }
  .bad-cell { background: #ffd6d6 !important; font-weight: bold; }
  .muted { color: #777; }
</style>
</head>
<body>
<div class="container">
  <h1>GFI Parity Test</h1>
  <p class="muted">
    Expected values come from the reference Python implementation
    (v.14-AUG-2026): exhaustive shell search over all tables at increasing
    move-distance, two-sided Fisher's exact for ALL significance decisions,
    &alpha; = 0.05. Each row also checks the invariant GFI &le; FI.
  </p>

  <div class="banner <?= $allPass ? 'pass' : 'fail' ?>">
    <?= $allPass ? 'ALL TESTS PASS' : 'FAILURES DETECTED' ?>
    &mdash; GFI <?= $pass ?>/<?= count($cases) ?>,
    Fisher <?= $fisherPass ?>/<?= count($fisherCases) ?>
    <span class="muted" style="font-weight:normal">(<?= number_format($totalTime, 2) ?>s)</span>
  </div>

  <h2>GFI cases</h2>
  <table>
    <tr>
      <th>Table {a,b,c,d}</th>
      <th>GFI exp</th><th>GFI got</th>
      <th>FI</th>
      <th>Post-GFI exp</th><th>Post-GFI got</th>
      <th>Flip p exp</th><th>Flip p got</th>
      <th>Candidates</th><th>Time</th><th>Result</th>
    </tr>
    <?php foreach ($results as $r): ?>
    <tr class="<?= $r['ok'] ? 'ok' : 'bad' ?>">
      <td>{<?= htmlspecialchars($r['table']) ?>}</td>
      <td><?= $r['expGfi'] ?></td>
      <td class="<?= $r['okGfi'] ? '' : 'bad-cell' ?>"><?= $r['gotGfi'] ?></td>
      <td class="<?= $r['okInvariant'] ? '' : 'bad-cell' ?>"><?= $r['fi'] ?></td>
      <td><?= htmlspecialchars($r['expTable']) ?></td>
      <td class="<?= $r['okTable'] ? '' : 'bad-cell' ?>"><?= htmlspecialchars($r['gotTable']) ?></td>
      <td><?= $r['expFinalP'] ?></td>
      <td class="<?= $r['okFinalP'] ? '' : 'bad-cell' ?>"><?= $r['gotFinalP'] ?></td>
      <td><?= number_format($r['evaluated']) ?></td>
      <td><?= number_format($r['secs'], 3) ?>s</td>
      <td><?= $r['ok'] ? 'PASS' : 'FAIL' ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <h2>Fisher's exact vs scipy</h2>
  <table>
    <tr><th>Table {a,b,c,d}</th><th>scipy</th><th>PHP</th><th>|diff|</th><th>Result</th></tr>
    <?php foreach ($fisherResults as $r): ?>
    <tr class="<?= $r['ok'] ? 'ok' : 'bad' ?>">
      <td>{<?= htmlspecialchars($r['table']) ?>}</td>
      <td><?= number_format($r['exp'], 6) ?></td>
      <td><?= number_format($r['got'], 6) ?></td>
      <td><?= sprintf('%.2e', $r['diff']) ?></td>
      <td><?= $r['ok'] ? 'PASS' : 'FAIL' ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <p><a href="calculate.php">&larr; Back to calculator</a></p>
</div>
</body>
</html>

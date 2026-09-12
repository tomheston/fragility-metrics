<?php
/**
 * test_ndi.php
 * Verification suite for NeutralityDistanceIndex.php
 *
 * Two layers:
 *   1. Named cases (toolkit example, independence, half-integer tie,
 *      lopsided margins, large table).
 *   2. Exhaustive brute-force check over all tables with cells 0..8
 *      (6561 tables): enumerates every reachable position along the
 *      coupled-move line k ∈ [−min(b,c), +min(a,d)], picks the position
 *      minimizing |ad − bc| with ties broken by fewer moves, and compares
 *      NDI, residual, post-table, and exact_neutrality against the class.
 *
 * Run: php test_ndi.php
 */

require_once 'NeutralityDistanceIndex.php';

$pass = 0;
$fail = 0;

function check($label, $condition, $detail = '') {
    global $pass, $fail;
    if ($condition) {
        $pass++;
        echo "PASS  $label\n";
    } else {
        $fail++;
        echo "FAIL  $label" . ($detail !== '' ? " — $detail" : '') . "\n";
    }
}

echo "NDI verification suite\n";
echo "======================\n\n";

// ---------- Layer 1: named cases ----------

// Toolkit example: {15,85,25,75}, D = -1000, N = 200 → 5 reverse moves to {20,80,20,80}
$r = NeutralityDistanceIndex::calculate(15, 85, 25, 75);
check("toolkit example {15,85,25,75}: NDI = 5", $r['NDI'] === 5, "got {$r['NDI']}");
check("toolkit example: exact neutrality reached", $r['exact_neutrality'] === true);
check("toolkit example: post table {20,80,20,80}",
    $r['post_table'] === ['a' => 20, 'b' => 80, 'c' => 20, 'd' => 80],
    json_encode($r['post_table']));
check("toolkit example: direction reverse", $r['direction'] === 'reverse', "got {$r['direction']}");

// Perfect independence: NDI = 0, already at neutrality
$r = NeutralityDistanceIndex::calculate(10, 10, 10, 10);
check("independence {10,10,10,10}: NDI = 0", $r['NDI'] === 0, "got {$r['NDI']}");
check("independence: exact neutrality", $r['exact_neutrality'] === true);

// Half-integer tie rounds down: {1,0,0,1}, |D|/N = 0.5 → 0 moves
$r = NeutralityDistanceIndex::calculate(1, 0, 0, 1);
check("tie {1,0,0,1}: NDI = 0 (half rounds down)", $r['NDI'] === 0, "got {$r['NDI']}");
check("tie: residual = 1", $r['residual_D'] === 1, "got {$r['residual_D']}");

// Another tie: {5,2,1,4}, D = 18, N = 12, ratio 1.5 → 1 move, residual 6
$r = NeutralityDistanceIndex::calculate(5, 2, 1, 4);
check("tie {5,2,1,4}: NDI = 1", $r['NDI'] === 1, "got {$r['NDI']}");
check("tie {5,2,1,4}: residual = 6", $r['residual_D'] === 6, "got {$r['residual_D']}");

// Lopsided margins: {1,0,0,99}, D = 99, N = 100 → 1 forward move, residual -1
$r = NeutralityDistanceIndex::calculate(1, 0, 0, 99);
check("lopsided {1,0,0,99}: NDI = 1", $r['NDI'] === 1, "got {$r['NDI']}");
check("lopsided: residual = -1", $r['residual_D'] === -1, "got {$r['residual_D']}");
check("lopsided: post table {0,1,1,98}",
    $r['post_table'] === ['a' => 0, 'b' => 1, 'c' => 1, 'd' => 98],
    json_encode($r['post_table']));

// Large table: {5000,3000,2000,4000}, D = 14,000,000, N = 14,000 → 1000 moves to neutrality
$r = NeutralityDistanceIndex::calculate(5000, 3000, 2000, 4000);
check("large {5000,3000,2000,4000}: NDI = 1000", $r['NDI'] === 1000, "got {$r['NDI']}");
check("large: exact neutrality", $r['exact_neutrality'] === true);

// Empty table
$r = NeutralityDistanceIndex::calculate(0, 0, 0, 0);
check("empty table: NDI = NULL with note", $r['NDI'] === null && ($r['note'] ?? '') === 'Empty table');

// Single-margin degenerate rows/columns are still defined
$r = NeutralityDistanceIndex::calculate(5, 0, 3, 0);
check("degenerate column {5,0,3,0}: NDI = 0", $r['NDI'] === 0, "got {$r['NDI']}");

// ---------- Layer 2: exhaustive brute force, cells 0..8 ----------

$tables = 0;
$mismatches = 0;
$firstMismatch = '';

for ($a = 0; $a <= 8; $a++) {
    for ($b = 0; $b <= 8; $b++) {
        for ($c = 0; $c <= 8; $c++) {
            for ($d = 0; $d <= 8; $d++) {
                $N = $a + $b + $c + $d;
                if ($N === 0) continue;
                $tables++;

                $D = $a * $d - $b * $c;

                // Enumerate every reachable position: net k forward moves
                // gives (a-k, b+k, c+k, d-k), valid for k in [-min(b,c), min(a,d)].
                $bestK = 0;
                $bestAbs = abs($D);
                for ($k = -min($b, $c); $k <= min($a, $d); $k++) {
                    $Dk = $D - $k * $N;
                    $absDk = abs($Dk);
                    if ($absDk < $bestAbs || ($absDk === $bestAbs && abs($k) < abs($bestK))) {
                        $bestAbs = $absDk;
                        $bestK = $k;
                    }
                }
                $bfNDI = abs($bestK);
                $bfResidual = $D - $bestK * $N;
                $bfPost = ['a' => $a - $bestK, 'b' => $b + $bestK, 'c' => $c + $bestK, 'd' => $d - $bestK];

                $r = NeutralityDistanceIndex::calculate($a, $b, $c, $d);

                $ok = ($r['NDI'] === $bfNDI)
                    && ($r['residual_D'] === $bfResidual)
                    && ($r['post_table'] === $bfPost)
                    && ($r['exact_neutrality'] === ($bfResidual === 0));

                if (!$ok) {
                    $mismatches++;
                    if ($firstMismatch === '') {
                        $firstMismatch = "{{$a},{$b},{$c},{$d}}: class NDI={$r['NDI']} residual={$r['residual_D']}"
                            . " vs brute NDI=$bfNDI residual=$bfResidual";
                    }
                }
            }
        }
    }
}

check("brute force 0..8 grid ($tables tables): all match definition", $mismatches === 0,
    "$mismatches mismatches; first: $firstMismatch");

echo "\n======================\n";
echo "Passed: $pass   Failed: $fail\n";
exit($fail === 0 ? 0 : 1);
?>

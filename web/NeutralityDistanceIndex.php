<?php
/**
 * NeutralityDistanceIndex.php
 * Robustness (NDI) calculator for 2×2 tables
 *
 * NDI = minimum number of coupled fixed-margin reassignments required to
 * bring the table to the reachable point closest to therapeutic neutrality
 * (ad − bc = 0). Uses the Feinstein/Walter fixed-both-margin move-set:
 * forward move (a−1, b+1, c+1, d−1), reverse move (a+1, b−1, c−1, d+1).
 * Each coupled move shifts the cross-product difference by exactly ∓N, so
 * NDI = round(|ad − bc| / N), clamped to the reachability window
 * (forward moves bounded by min(a,d), reverse moves by min(b,c)).
 *
 * Ties (|ad − bc| / N exactly half-integer) round DOWN: both neighboring
 * reachable points are equidistant from neutrality, and the minimum number
 * of moves wins.
 *
 * NDI is model-free and test-independent: it depends only on ad − bc, not
 * on any significance threshold. Always defined; NDI = 0 means the table
 * is already as close to neutrality as the fixed-margin moves permit.
 *
 * Unit: one coupled move = 1 (relocates two patients, one per arm),
 * matching the published UFI. NDI:RQ parallels the GFI:GFQ Index:Quotient
 * pairing — NDI = round(N·RQ/4) is an exact algebraic identity.
 *
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 *
 * License: Creative Commons Attribution 4.0 International (CC BY 4.0)
 * https://creativecommons.org/licenses/by/4.0/
 */

class NeutralityDistanceIndex {

    /**
     * Calculate Neutrality Distance Index (NDI) for a 2×2 table
     *
     * @param int $a Arm A events
     * @param int $b Arm A non-events
     * @param int $c Arm B events
     * @param int $d Arm B non-events
     * @return array [
     *   'NDI'              => int|null  coupled moves to closest reachable approach,
     *   'D'                => int|null  baseline cross-product difference ad − bc,
     *   'residual_D'       => int|null  ad − bc at closest reachable approach,
     *   'exact_neutrality' => bool|null residual_D === 0,
     *   'direction'        => string|null 'forward' | 'reverse' | null when NDI = 0,
     *   'clamped'          => bool|null  true if the reachability window bound the result,
     *   'post_table'       => array|null closest-approach table ['a','b','c','d'],
     *   'note'             => string    only on empty table
     * ]
     */
    public static function calculate($a, $b, $c, $d) {
        // Validate inputs
        if ($a < 0 || $b < 0 || $c < 0 || $d < 0) {
            throw new InvalidArgumentException("All cells must be non-negative integers");
        }

        $N = $a + $b + $c + $d;

        if ($N == 0) {
            return [
                'NDI' => null,
                'D' => null,
                'residual_D' => null,
                'exact_neutrality' => null,
                'direction' => null,
                'clamped' => null,
                'post_table' => null,
                'note' => 'Empty table'
            ];
        }

        $D = ($a * $d) - ($b * $c);
        $absD = abs($D);

        // Unclamped move count: round(|D| / N) with exact halves rounding
        // down (fewer moves reaches an equally close point). Integer-exact.
        $q = intdiv($absD, $N);
        $r = $absD - ($q * $N);
        $x = $q + ((2 * $r) > $N ? 1 : 0);

        // Reachability window: forward moves (D > 0) need a,d ≥ 1 each,
        // reverse moves (D < 0) need b,c ≥ 1 each.
        if ($D > 0) {
            $bound = min($a, $d);
            $direction = 'forward';
        } elseif ($D < 0) {
            $bound = min($b, $c);
            $direction = 'reverse';
        } else {
            $bound = 0;
            $direction = null;
        }

        $clamped = ($x > $bound);
        $k = $clamped ? $bound : $x;
        if ($k == 0) {
            $direction = null;
        }

        // Apply k coupled moves toward neutrality
        if ($direction === 'forward') {
            $post = ['a' => $a - $k, 'b' => $b + $k, 'c' => $c + $k, 'd' => $d - $k];
            $residual = $D - ($k * $N);
        } elseif ($direction === 'reverse') {
            $post = ['a' => $a + $k, 'b' => $b - $k, 'c' => $c - $k, 'd' => $d + $k];
            $residual = $D + ($k * $N);
        } else {
            $post = ['a' => $a, 'b' => $b, 'c' => $c, 'd' => $d];
            $residual = $D;
        }

        return [
            'NDI' => $k,
            'D' => $D,
            'residual_D' => $residual,
            'exact_neutrality' => ($residual === 0),
            'direction' => $direction,
            'clamped' => $clamped,
            'post_table' => $post
        ];
    }

}

// ========== TESTING ==========

if (php_sapi_name() === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    echo "Testing Neutrality Distance Index Calculator\n";
    echo "=============================================\n\n";

    // Test case 1: Example from toolkit
    $a = 15; $b = 85; $c = 25; $d = 75;
    echo "Test Case 1: {a=$a, b=$b, c=$c, d=$d}\n";
    $result = NeutralityDistanceIndex::calculate($a, $b, $c, $d);
    echo "NDI: " . $result['NDI'] . " (should be 5, exact neutrality at {20,80,20,80})\n\n";

    // Test case 2: Perfect independence (NDI should be 0)
    $a = 10; $b = 10; $c = 10; $d = 10;
    echo "Test Case 2: Perfect independence {a=$a, b=$b, c=$c, d=$d}\n";
    $result = NeutralityDistanceIndex::calculate($a, $b, $c, $d);
    echo "NDI: " . $result['NDI'] . " (should be 0)\n\n";

    // Test case 3: Exact half tie rounds down
    $a = 1; $b = 0; $c = 0; $d = 1;
    echo "Test Case 3: Half-integer tie {a=$a, b=$b, c=$c, d=$d}\n";
    $result = NeutralityDistanceIndex::calculate($a, $b, $c, $d);
    echo "NDI: " . $result['NDI'] . " (should be 0 — equidistant, fewer moves wins)\n\n";

    // Test case 4: Strong association
    $a = 50; $b = 10; $c = 10; $d = 50;
    echo "Test Case 4: Strong association {a=$a, b=$b, c=$c, d=$d}\n";
    $result = NeutralityDistanceIndex::calculate($a, $b, $c, $d);
    echo "NDI: " . $result['NDI'] . " (should be 20, exact neutrality at {30,30,30,30})\n\n";
}
?>

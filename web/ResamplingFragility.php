<?php
/**
 * ResamplingFragility.php
 * Exact resampling fragility for 2×2 independent binary outcome tables.
 *
 * Resampling fragility is the estimated probability that the trial's
 * significance classification would reverse in a new sample of the same
 * size under a specified replication model. It is a form of data
 * fragility in the taxonomy of statistical fragility (analysis,
 * resampling, perturbation, scaling). Because its value depends on the
 * replication model, it sits OUTSIDE the operational p-fr-nb triplet,
 * which admits only model-free measurements of the observed table.
 *
 * Replication model used here (stated per the taxonomy reporting rule):
 * independent binomial draws in each arm at the observed event rates,
 * with arm sizes fixed; the two-sided Fisher's exact test is recomputed
 * for every possible replicate table at the stated alpha. The reversal
 * probability is computed EXACTLY by summing binomial probabilities over
 * all replicate tables - no simulation, no Monte Carlo error.
 *
 * Source definitions:
 * Heston TF. Resampling Fragility, Perturbation Fragility, and Why the
 * Global Fragility Index Is Not a P-Value in Disguise. Internet Med J.
 * 2026;1:e22059146. doi:10.5281/zenodo.22059146
 *
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 *
 * License: Creative Commons Attribution 4.0 International (CC BY 4.0)
 */

require_once 'FisherExactTest.php';

class ResamplingFragility {

    /** Largest N for which the exact summation is attempted. */
    const MAX_N = 3000;

    /** Binomial tail mass (per arm, per side) discarded for speed. */
    const TAIL_EPS = 1e-13;

    /**
     * Exact resampling fragility for a 2×2 table.
     *
     * @param int   $a     Arm A events
     * @param int   $b     Arm A non-events
     * @param int   $c     Arm B events
     * @param int   $d     Arm B non-events
     * @param float $alpha Significance threshold (default 0.05)
     * @return array {
     *   reversal_probability, retention_probability,
     *   baseline_p, baseline_significant, alpha,
     *   method, model, note
     * }
     */
    public static function calculate($a, $b, $c, $d, $alpha = 0.05) {
        if ($a < 0 || $b < 0 || $c < 0 || $d < 0) {
            throw new InvalidArgumentException("All cells must be non-negative integers");
        }

        $n1 = $a + $b;
        $n2 = $c + $d;
        $N  = $n1 + $n2;

        $out = [
            'reversal_probability'  => null,
            'retention_probability' => null,
            'baseline_p'            => null,
            'baseline_significant'  => null,
            'alpha'                 => $alpha,
            'method'                => 'exact binomial summation',
            'model'                 => 'independent binomial draws per arm at the observed event rates, arm sizes fixed; two-sided Fisher\'s exact test recomputed for every replicate table',
            'note'                  => null,
        ];

        if ($n1 === 0 || $n2 === 0) {
            $out['note'] = 'Not defined: at least one arm is empty';
            return $out;
        }
        if ($N > self::MAX_N) {
            $out['note'] = 'Not computed: exact summation is limited to N <= ' . self::MAX_N;
            return $out;
        }

        $baselineP = FisherExactTest::calculate($a, $b, $c, $d);
        $baselineSig = ($baselineP < $alpha);
        $out['baseline_p'] = round($baselineP, 6);
        $out['baseline_significant'] = $baselineSig;

        $p1 = $a / $n1;
        $p2 = $c / $n2;

        // Binomial weights per arm, restricted to a window that keeps all
        // but a negligible tail mass (window bounds are exact cumulative
        // sums, so the discarded mass is accounted for by normalization).
        list($lo1, $hi1, $w1) = self::binomialWindow($n1, $p1);
        list($lo2, $hi2, $w2) = self::binomialWindow($n2, $p2);

        // For each replicate column margin m = e1 + e2, the Fisher two-sided
        // p-value of a replicate table depends only on (e1, m). Computing the
        // whole hypergeometric distribution once per m makes the significance
        // classification of every e1 available in one pass.
        $flip = 0.0;
        $total = 0.0;

        $mLo = $lo1 + $lo2;
        $mHi = $hi1 + $hi2;

        for ($m = $mLo; $m <= $mHi; $m++) {
            $aMin = max(0, $m - $n2);
            $aMax = min($n1, $m);
            if ($aMin > $aMax) {
                continue;
            }

            // The e1 values that can actually occur under the windows:
            $eLo = max($aMin, $lo1, $m - $hi2);
            $eHi = min($aMax, $hi1, $m - $lo2);
            if ($eLo > $eHi) {
                continue;
            }

            $sigByE1 = self::fisherSignificanceByCell($n1, $n2, $m, $aMin, $aMax, $alpha);

            for ($e1 = $eLo; $e1 <= $eHi; $e1++) {
                $e2 = $m - $e1;
                $w = $w1[$e1] * $w2[$e2];
                if ($w <= 0.0) {
                    continue;
                }
                $total += $w;
                if ($sigByE1[$e1] !== $baselineSig) {
                    $flip += $w;
                }
            }
        }

        if ($total <= 0.0) {
            $out['note'] = 'Not defined: replicate probability mass is empty';
            return $out;
        }

        $out['reversal_probability']  = $flip / $total;
        $out['retention_probability'] = 1.0 - ($flip / $total);
        return $out;
    }

    /**
     * Binomial(n, p) probabilities on a window [lo, hi] that carries all
     * but ~TAIL_EPS of the mass on each side.
     *
     * @return array [lo, hi, weights] with weights indexed by count k
     */
    private static function binomialWindow($n, $p) {
        if ($p <= 0.0) {
            return [0, 0, [0 => 1.0]];
        }
        if ($p >= 1.0) {
            return [$n, $n, [$n => 1.0]];
        }

        $logP = log($p);
        $logQ = log(1.0 - $p);

        // Full pmf in log space (n <= MAX_N, so this stays cheap).
        $pmf = [];
        for ($k = 0; $k <= $n; $k++) {
            $pmf[$k] = exp(self::logChoose($n, $k) + $k * $logP + ($n - $k) * $logQ);
        }

        // Trim tails by cumulative mass.
        $lo = 0;
        $acc = 0.0;
        while ($lo < $n && ($acc + $pmf[$lo]) < self::TAIL_EPS) {
            $acc += $pmf[$lo];
            $lo++;
        }
        $hi = $n;
        $acc = 0.0;
        while ($hi > $lo && ($acc + $pmf[$hi]) < self::TAIL_EPS) {
            $acc += $pmf[$hi];
            $hi--;
        }

        $w = [];
        for ($k = $lo; $k <= $hi; $k++) {
            $w[$k] = $pmf[$k];
        }
        return [$lo, $hi, $w];
    }

    /**
     * Significance classification of every table with margins
     * (n1, n2, column-events m) under the two-sided Fisher's exact test,
     * using the same "sum lesser probabilities" rule and the same 1e-7
     * tie tolerance as FisherExactTest, so replicate classifications match
     * FisherExactTest::calculate() table-for-table.
     *
     * @return array e1 => bool (significant at alpha)
     */
    private static function fisherSignificanceByCell($n1, $n2, $m, $aMin, $aMax, $alpha) {
        $count = $aMax - $aMin + 1;

        // Hypergeometric point probabilities across the support.
        $probs = [];
        for ($i = $aMin; $i <= $aMax; $i++) {
            $probs[$i] = exp(
                self::logChoose($m, $i)
                + self::logChoose($n1 + $n2 - $m, $n1 - $i)
                - self::logChoose($n1 + $n2, $n1)
            );
        }

        // Sort ascending once; prefix sums give each table's two-sided p.
        $sorted = $probs;
        asort($sorted);
        $keys = array_keys($sorted);
        $vals = array_values($sorted);
        $prefix = [];
        $run = 0.0;
        for ($j = 0; $j < $count; $j++) {
            $run += $vals[$j];
            $prefix[$j] = $run;
        }

        // For each support point, p = sum of probabilities <= its own
        // probability * (1 + 1e-7). Binary search over the sorted values.
        $sig = [];
        foreach ($probs as $e1 => $p0) {
            $threshold = $p0 * (1.0 + 1.0e-7);
            $loIdx = 0;
            $hiIdx = $count - 1;
            $last = -1;
            while ($loIdx <= $hiIdx) {
                $mid = intdiv($loIdx + $hiIdx, 2);
                if ($vals[$mid] <= $threshold) {
                    $last = $mid;
                    $loIdx = $mid + 1;
                } else {
                    $hiIdx = $mid - 1;
                }
            }
            $pTwo = ($last >= 0) ? min(1.0, $prefix[$last]) : 0.0;
            $sig[$e1] = ($pTwo < $alpha);
        }
        return $sig;
    }

    /**
     * log C(n, k) via the shared exact log-factorial table.
     */
    private static function logChoose($n, $k) {
        if ($k < 0 || $k > $n) {
            return -INF;
        }
        if ($k == 0 || $k == $n) {
            return 0.0;
        }
        return self::logFactorial($n) - self::logFactorial($k) - self::logFactorial($n - $k);
    }

    /**
     * Exact log(n!) with an incrementally grown cache (same approach as
     * FisherExactTest::logFactorial).
     */
    private static function logFactorial($n) {
        if ($n < 0) {
            throw new InvalidArgumentException("Factorial undefined for negative numbers");
        }
        if ($n <= 1) {
            return 0.0;
        }
        static $cache = [0.0, 0.0];
        $have = count($cache) - 1;
        for ($k = $have + 1; $k <= $n; $k++) {
            $cache[$k] = $cache[$k - 1] + log((float)$k);
        }
        return $cache[$n];
    }
}

// ========== TESTING ==========

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    echo "Testing ResamplingFragility (exact binomial summation)\n";
    echo "=======================================================\n\n";

    $cases = [
        // [a, b, c, d, expected reversal probability (scipy reference)]
        [43, 63, 57, 43, 0.382877],  // MUTTON-HF primary endpoint
        [15, 85, 25, 75, 0.358635],  // nonsignificant baseline
        [13, 87, 25, 75, 0.482369],  // FRAGILITY_METRICS.md Quick Start table
        [0, 10, 5, 5, 0.376953],     // zero-cell arm rate
    ];

    foreach ($cases as $t) {
        list($a, $b, $c, $d, $expected) = $t;
        $r = ResamplingFragility::calculate($a, $b, $c, $d);
        $got = $r['reversal_probability'];
        $ok = ($got !== null && abs($got - $expected) < 5e-4);
        printf(
            "{%d, %d, %d, %d}: reversal = %s (expected %.6f, baseline p = %.6f, %s) %s\n",
            $a, $b, $c, $d,
            $got === null ? 'NULL' : number_format($got, 6),
            $expected,
            $r['baseline_p'],
            $r['baseline_significant'] ? 'significant' : 'nonsignificant',
            $ok ? '✓ PASS' : '✗ FAIL'
        );
    }

    echo "\nAll tests completed.\n";
}
?>

<?php
/**
 * FragilityIndexWalsh.php
 * Strict original Walsh (2014) Fragility Index calculator for 2×2 tables
 *
 * Rules (strict Walsh criteria):
 * 1. Baseline table must be significant (p < alpha); if not → "NA: baseline table nonsignificant"
 * 2. Events must not be tied (a != c); if tied → "NA: events tied"
 * 3. Choose arm with fewer events (no tiebreaker — tie is handled by rule 2)
 * 4. Toggles ONLY in one direction: non-event → event in the chosen arm
 * 5. If non-events exhausted before significance flips → "Not Attainable"
 *
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 *
 * License: Creative Commons Attribution 4.0 International (CC BY 4.0)
 * https://creativecommons.org/licenses/by/4.0/
 */

require_once 'FisherExactTest.php';

class FragilityIndexWalsh {

    private const MAX_ITERATIONS = 100000;

    /**
     * Calculate strict Walsh FI for a 2×2 table.
     *
     * @param int   $a     Arm A events
     * @param int   $b     Arm A non-events
     * @param int   $c     Arm B events
     * @param int   $d     Arm B non-events
     * @param float $alpha Significance threshold (default 0.05)
     * @return array
     */
    public static function calculate($a, $b, $c, $d, $alpha = 0.05) {
        if ($a < 0 || $b < 0 || $c < 0 || $d < 0) {
            throw new InvalidArgumentException("All cells must be non-negative integers");
        }

        $N = $a + $b + $c + $d;

        if ($N == 0) {
            return self::naResult('Empty table');
        }

        // Rule 1: Baseline must be significant (p < alpha; p = alpha is nonsignificant)
        $baseline_p = FisherExactTest::calculate($a, $b, $c, $d);
        if ($baseline_p >= $alpha) {
            return self::naResult('NA: baseline table nonsignificant');
        }

        // Rule 2: Events must not be tied
        if ($a == $c) {
            return self::naResult('NA: events tied');
        }

        // Rule 3: Arm with fewer events (tie impossible here due to rule 2)
        $arm  = ($a < $c) ? 'A' : 'B';
        $n_mod = ($arm === 'A') ? ($a + $b) : ($c + $d);

        // Rule 4: Toggle non-event → event only
        $A = $a; $B = $b; $C = $c; $D = $d;

        for ($i = 0; $i < self::MAX_ITERATIONS; $i++) {
            // Attempt one toggle; if no non-events remain it is not attainable
            if ($arm === 'A') {
                if ($B <= 0) return self::naResult('Not Attainable');
                $A++; $B--;
            } else {
                if ($D <= 0) return self::naResult('Not Attainable');
                $C++; $D--;
            }

            $steps = $i + 1;
            $p     = FisherExactTest::calculate($A, $B, $C, $D);

            if ($p >= $alpha) {
                // Significance flipped — FI found (Walsh: p equal to or greater than alpha)
                $FI  = $steps;
                $FQ  = round($FI / $N, 6);
                $MFQ = round($FI / $n_mod, 6);

                return [
                    'FI_text'   => (string)$FI,
                    'FI'        => $FI,
                    'FQ'        => $FQ,
                    'MFQ'       => $MFQ,
                    'arm'       => $arm,
                    'post_FI'   => ['a' => $A, 'b' => $B, 'c' => $C, 'd' => $D],
                    'post_FI_p' => round($p, 6),
                ];
            }
        }

        // Max iterations exhausted
        return self::naResult('Not Attainable');
    }

    /**
     * Build a not-applicable / not-attainable result.
     */
    private static function naResult($label) {
        return [
            'FI_text'   => $label,
            'FI'        => null,
            'FQ'        => null,
            'MFQ'       => null,
            'arm'       => null,
            'post_FI'   => null,
            'post_FI_p' => null,
        ];
    }
}
?>

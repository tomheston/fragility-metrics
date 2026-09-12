<?php
/**
 * RiskDifference.php
 * Absolute risk difference (Arm A - Arm B) with a 95% confidence interval,
 * and the number needed to treat (NNT), for 2×2 tables.
 *
 * RD  = a/(a+b) - c/(c+d)
 * 95% CI uses the Newcombe (1998) hybrid score method (method 10), built from
 *   the Wilson score interval of each arm's risk. It is defined when a cell is
 *   zero, so no continuity correction is needed.
 * NNT = 1 / |RD|, rounded up to a whole patient. Its interval inverts the RD
 *   limits and exists only when the RD interval excludes 0; otherwise the NNT
 *   interval is unbounded and is reported as null.
 *
 * This is the absolute effect size, the fourth element of complete evidence
 * (the p-fr-nb triplet plus effect size).
 *
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 *
 * License: Creative Commons Attribution 4.0 International (CC BY 4.0)
 * https://creativecommons.org/licenses/by/4.0/
 */

class RiskDifference {

    // z for two-tailed 95% CI (matches RelativeRisk)
    const Z95 = 1.959964;

    /**
     * Calculate the risk difference, its 95% CI, and the NNT for a 2×2 table.
     *
     * @param int $a  Arm A events
     * @param int $b  Arm A non-events
     * @param int $c  Arm B events
     * @param int $d  Arm B non-events
     * @return array
     */
    public static function calculate($a, $b, $c, $d) {
        if ($a < 0 || $b < 0 || $c < 0 || $d < 0) {
            throw new InvalidArgumentException("All cells must be non-negative integers");
        }

        $nA = $a + $b;
        $nB = $c + $d;

        // Undefined when either arm is empty
        if ($nA == 0 || $nB == 0) {
            return [
                'risk_A'       => null,
                'risk_B'       => null,
                'RD'           => null,
                'CI_lower'     => null,
                'CI_upper'     => null,
                'CI_level'     => 95,
                'NNT'          => null,
                'NNT_CI_lower' => null,
                'NNT_CI_upper' => null,
                'note'         => 'Undefined: arm with zero participants'
            ];
        }

        $pA = $a / $nA;
        $pB = $c / $nB;
        $RD = $pA - $pB;

        list($l1, $u1) = self::wilson($a, $nA);
        list($l2, $u2) = self::wilson($c, $nB);

        $lower = $RD - sqrt(pow($pA - $l1, 2) + pow($u2 - $pB, 2));
        $upper = $RD + sqrt(pow($u1 - $pA, 2) + pow($pB - $l2, 2));

        $NNT = ($RD == 0) ? null : self::nnt($RD);

        // The NNT interval exists only when the RD interval excludes 0
        $nntLower = null;
        $nntUpper = null;
        if ($lower > 0 || $upper < 0) {
            $nntLower = self::nnt(max(abs($lower), abs($upper)));
            $nntUpper = self::nnt(min(abs($lower), abs($upper)));
        }

        return [
            'risk_A'       => round($pA, 6),
            'risk_B'       => round($pB, 6),
            'RD'           => round($RD, 6),
            'CI_lower'     => round($lower, 6),
            'CI_upper'     => round($upper, 6),
            'CI_level'     => 95,
            'NNT'          => $NNT,
            'NNT_CI_lower' => $nntLower,
            'NNT_CI_upper' => $nntUpper,
            'note'         => null
        ];
    }

    /**
     * Wilson score interval for x events out of n.
     *
     * @return array [lower, upper]
     */
    private static function wilson($x, $n) {
        $z      = self::Z95;
        $p      = $x / $n;
        $den    = 1 + $z * $z / $n;
        $centre = ($p + $z * $z / (2 * $n)) / $den;
        $half   = $z * sqrt($p * (1 - $p) / $n + $z * $z / (4 * $n * $n)) / $den;
        return [max(0.0, $centre - $half), min(1.0, $centre + $half)];
    }

    /**
     * 1 / |rd| rounded up to a whole patient. The inner round() absorbs
     * floating-point noise, e.g. 0.25 - 0.15 = 0.09999999999999998.
     */
    private static function nnt($rd) {
        return (int)ceil(round(1 / abs($rd), 6));
    }
}
?>

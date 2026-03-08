<?php
/**
 * RelativeRisk.php
 * Relative Risk (Risk Ratio) and 95% confidence interval calculator for 2×2 tables
 *
 * RR = [a/(a+b)] / [c/(c+d)]
 * 95% CI uses the log-normal method:
 *   SE  = sqrt(1/a - 1/(a+b) + 1/c - 1/(c+d))
 *   CI  = exp( ln(RR) ± 1.96 * SE )
 *
 * When any cell is zero, the Haldane-Anscombe continuity correction (+0.5 to
 * all cells) is applied before computing RR and CI.
 *
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 *
 * License: Creative Commons Attribution 4.0 International (CC BY 4.0)
 * https://creativecommons.org/licenses/by/4.0/
 */

class RelativeRisk {

    // z for two-tailed 95% CI
    const Z95 = 1.959964;

    /**
     * Calculate Relative Risk and 95% confidence interval for a 2×2 table.
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

        // Undefined when either arm is empty — continuity correction cannot help
        if ($nA == 0 || $nB == 0) {
            return [
                'RR'         => null,
                'CI_lower'   => null,
                'CI_upper'   => null,
                'CI_level'   => 95,
                'correction' => false,
                'note'       => 'Undefined: arm with zero participants'
            ];
        }

        // Haldane-Anscombe continuity correction: add 0.5 to all cells when any is zero
        $correction = false;
        if ($a == 0 || $b == 0 || $c == 0 || $d == 0) {
            $a  += 0.5;  $b  += 0.5;
            $c  += 0.5;  $d  += 0.5;
            $nA += 1.0;  $nB += 1.0;
            $correction = true;
        }

        $riskA = $a / $nA;
        $riskB = $c / $nB;

        // After correction riskB cannot be zero, but guard just in case
        if ($riskB == 0) {
            return [
                'RR'         => null,
                'CI_lower'   => null,
                'CI_upper'   => null,
                'CI_level'   => 95,
                'correction' => $correction,
                'note'       => 'Undefined: Arm B event rate is zero'
            ];
        }

        $RR   = $riskA / $riskB;
        $lnRR = log($RR);
        $SE   = sqrt(1/$a - 1/$nA + 1/$c - 1/$nB);

        return [
            'RR'         => round($RR, 6),
            'CI_lower'   => round(exp($lnRR - self::Z95 * $SE), 6),
            'CI_upper'   => round(exp($lnRR + self::Z95 * $SE), 6),
            'CI_level'   => 95,
            'correction' => $correction,
            'note'       => null
        ];
    }
}
?>

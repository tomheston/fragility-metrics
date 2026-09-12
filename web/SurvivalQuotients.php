<?php
/**
 * SurvivalQuotients.php
 * SFQ (Survival Fragility Quotient) and SRQ (Survival Robustness Quotient)
 * for time-to-event outcomes reported as a Cox hazard ratio with an interval.
 *
 * Model-free: needs only what trial reports already publish.
 *
 *   SE(ln HR) = [ln(hi) - ln(lo)] / (2 * z_level)   z_level = 1.959964 at 95%
 *   z         = ln(HR) / SE(ln HR)
 *   SFQ       = | |z| - 1.96 |  / (1 + | |z| - 1.96 |)     fragility  (fr)
 *   SRQ       = | ln(HR) |      / (1 + | ln(HR) |)         robustness (nb)
 *
 * Two-sided p is recovered from z so it can be checked against the published
 * value. A large discrepancy means the published interval was not a symmetric
 * Wald interval on the log scale (e.g. stratified, exact, or group-sequential),
 * and the inputs need checking.
 *
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 *
 * License: Creative Commons Attribution 4.0 International (CC BY 4.0)
 * https://creativecommons.org/licenses/by/4.0/
 */

class SurvivalQuotients {

    /** two-sided 95% normal quantile */
    const Z95 = 1.959963984540054;

    /** significance boundary used by the p-fr-nb framework */
    const ALPHA_Z = 1.96;

    /** ln(Gamma(0.5)) = ln(sqrt(pi)), used by the erfc routine */
    const LN_GAMMA_HALF = 0.5723649429247001;

    /**
     * Calculate SFQ and SRQ from a published hazard ratio and its interval.
     *
     * @param float $hr      Hazard ratio (point estimate), > 0
     * @param float $lo      Lower confidence limit, > 0
     * @param float $hi      Upper confidence limit, > $lo
     * @param float $ciLevel Interval level as a proportion (0.95 = 95% CI)
     * @return array
     * @throws InvalidArgumentException
     */
    public static function calculate($hr, $lo, $hi, $ciLevel = 0.95) {
        if (!is_numeric($hr) || !is_numeric($lo) || !is_numeric($hi) || !is_numeric($ciLevel)) {
            throw new InvalidArgumentException("The hazard ratio, both confidence limits, and the level must be numeric.");
        }

        $hr = (float)$hr;
        $lo = (float)$lo;
        $hi = (float)$hi;
        $ciLevel = (float)$ciLevel;

        if ($hr <= 0) {
            throw new InvalidArgumentException("The hazard ratio must be greater than 0.");
        }
        if ($lo <= 0) {
            throw new InvalidArgumentException("The lower confidence limit must be greater than 0.");
        }
        if ($hi <= $lo) {
            throw new InvalidArgumentException("The upper confidence limit must be greater than the lower limit.");
        }
        if ($ciLevel <= 0 || $ciLevel >= 1) {
            throw new InvalidArgumentException("The confidence level must be between 0 and 1 (0.95 = 95% CI).");
        }

        $zLevel = self::zForLevel($ciLevel);
        $se     = (log($hi) - log($lo)) / (2 * $zLevel);
        $lnHr   = log($hr);
        $z      = $lnHr / $se;

        $dSig = abs(abs($z) - self::ALPHA_Z);   // distance to the significance boundary
        $dNeu = abs($lnHr);                     // distance to neutrality, HR = 1

        return array(
            'HR'          => $hr,
            'CI_lower'    => $lo,
            'CI_upper'    => $hi,
            'CI_level'    => $ciLevel,
            'z_level'     => $zLevel,
            'ln_HR'       => $lnHr,
            'SE_ln_HR'    => $se,
            'z'           => $z,
            'p_recovered' => 2 * self::normSf(abs($z)),
            'significant' => (abs($z) > self::ALPHA_Z),
            'd_sig'       => $dSig,
            'd_neu'       => $dNeu,
            'SFQ'         => $dSig / (1 + $dSig),
            'SRQ'         => $dNeu / (1 + $dNeu),
            // nominal 95% interval implied by the same SE; unadjusted, see note on the page
            'CI95_lower'  => $hr * exp(-self::Z95 * $se),
            'CI95_upper'  => $hr * exp(self::Z95 * $se),
            'hr_outside_ci' => ($hr < $lo || $hr > $hi),
        );
    }

    /**
     * Two-sided normal quantile for a given interval level.
     * Exact constant at 95%; bisection elsewhere (e.g. 97.73% interim intervals).
     *
     * @param float $level 0.95 = 95% CI
     * @return float
     */
    public static function zForLevel($level) {
        if (abs($level - 0.95) < 1e-12) {
            return self::Z95;
        }
        $target = (1 - $level) / 2;   // upper-tail area
        $lo = 0.0;
        $hi = 40.0;
        for ($i = 0; $i < 200; $i++) {
            $mid = ($lo + $hi) / 2;
            if (self::normSf($mid) > $target) {
                $lo = $mid;
            } else {
                $hi = $mid;
            }
        }
        return ($lo + $hi) / 2;
    }

    /** Upper tail of the standard normal, P(Z > x). */
    public static function normSf($x) {
        return 0.5 * self::erfc($x / M_SQRT2);
    }

    /**
     * Complementary error function via the incomplete gamma function:
     * erfc(x) = Q(1/2, x^2) for x >= 0. Relative accuracy ~1e-14.
     */
    private static function erfc($x) {
        if ($x < 0) {
            return 2.0 - self::erfc(-$x);
        }
        if ($x == 0.0) {
            return 1.0;
        }
        $xx = $x * $x;
        // series below a+1, continued fraction above (a = 0.5)
        return ($xx < 1.5) ? (1.0 - self::gammaSeries($xx)) : self::gammaContinuedFraction($xx);
    }

    /** Series representation of P(1/2, x). */
    private static function gammaSeries($x) {
        $a   = 0.5;
        $ap  = $a;
        $sum = 1.0 / $a;
        $del = $sum;
        for ($n = 1; $n <= 500; $n++) {
            $ap += 1.0;
            $del *= $x / $ap;
            $sum += $del;
            if (abs($del) < abs($sum) * 1e-16) {
                break;
            }
        }
        return $sum * exp(-$x + $a * log($x) - self::LN_GAMMA_HALF);
    }

    /** Continued-fraction representation of Q(1/2, x), modified Lentz method. */
    private static function gammaContinuedFraction($x) {
        $a     = 0.5;
        $FPMIN = 1e-300;
        $b = $x + 1.0 - $a;
        $c = 1.0 / $FPMIN;
        $d = 1.0 / $b;
        $h = $d;
        for ($i = 1; $i <= 500; $i++) {
            $an = -$i * ($i - $a);
            $b += 2.0;
            $d = $an * $d + $b;
            if (abs($d) < $FPMIN) {
                $d = $FPMIN;
            }
            $c = $b + $an / $c;
            if (abs($c) < $FPMIN) {
                $c = $FPMIN;
            }
            $d = 1.0 / $d;
            $del = $d * $c;
            $h *= $del;
            if (abs($del - 1.0) < 1e-16) {
                break;
            }
        }
        return exp(-$x + $a * log($x) - self::LN_GAMMA_HALF) * $h;
    }
}

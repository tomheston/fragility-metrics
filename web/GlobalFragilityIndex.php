<?php
/**
 * GlobalFragilityIndex.php
 * Exact Global Fragility Index (GFI) / Global Fragility Quotient (GFQ) for 2x2 tables.
 *
 * GFI = the minimum number of individuals that must be moved between cells
 * (grand total N held fixed, all cells >= 0) to flip the significance verdict.
 *
 * ALGORITHM - exhaustive shell search
 * ----------------------------------
 * The move-distance between two tables with the same N is exactly half their
 * L1 distance. The search walks outward in shells of exact distance
 * 1, 2, 3, ... and returns the first distance at which any reachable table
 * flips significance. Because shells are disjoint and visited in increasing
 * order, the first hit is the exact minimum - no heuristic, no verification pass.
 *
 * Each shell is generated from the 50 disjoint (gain-set, loss-set) sign
 * patterns over the 4 cells, crossed with the integer compositions of the
 * shell distance into strictly positive parts.
 *
 * SIGNIFICANCE TEST - always Fisher's exact
 * -----------------------------------------
 * Two-sided Fisher's exact is the only test used, for the baseline verdict
 * and for every candidate table in the search. There is no chi-square branch;
 * this matches the reference Python implementation (v.14-AUG-2026), which
 * freezes the test type to Fisher's exact for all significance decisions.
 *
 * Significance is strict: p < alpha.
 *
 * This mirrors the reference Python implementation of the toolkit; see
 * test_gfi_parity.php for the parity fixtures.
 *
 * LARGE N - exact margin-coordinate certification
 * -----------------------------------------------
 * For N > SHELL_MAX_N, or when the shell search exhausts its budget, GFI is
 * computed by the margin-coordinate algorithm of the reference Python
 * implementation (fragility_metrics_r12.py, compute_gfi_gfq):
 *   1. a rigorous upper bound from a real opposite-classification table
 *      (margin collapse or complete separation), tightened by scanning all
 *      12 single-transfer rays exactly;
 *   2. level-ascending certification over every margin pair (u, v) with
 *      max(|u|, |v|) = D < incumbent, using the closed-form L1 sublevel
 *      interval and the unimodality of the Fisher p-value in the
 *      upper-left cell; hits are refined to their exact distance by
 *      bisection.
 * A completed sweep returns the certified exact minimum. A sweep stopped by
 * the work or time budget returns the incumbent labelled as an upper bound
 * (verified = false) with the proven bracket in the note.
 *
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 *
 * License: Creative Commons Attribution 4.0 International (CC BY 4.0)
 */

class GlobalFragilityIndex {

    /** Default significance threshold. */
    const ALPHA = 0.05;

    /**
     * Largest N handled by the exhaustive shell search. Larger tables, and
     * tables whose shell search exhausts its budget, use the exact
     * margin-coordinate certification instead.
     */
    const SHELL_MAX_N = 250000;

    /** Margin-coordinate certification: candidate margin pairs before stopping. */
    const MARGIN_MAX_CANDIDATES = 50000000;

    /** Margin-coordinate certification: wall-clock budget in seconds. */
    const MARGIN_MAX_SECONDS = 90.0;

    /** Candidate rows accumulated per certification batch (reference GROUP_ROWS). */
    const MARGIN_GROUP_ROWS = 1000000;

    /** Probability-ordering tolerance of the reference engine (SciPy rule). */
    const MARGIN_GAMMA = 1.00000000000001;

    /** Work budget: candidate tables examined before the search gives up. */
    const MAX_CANDIDATES = 50000000;

    /** Wall-clock budget in seconds before the search gives up. */
    const MAX_SECONDS = 90.0;

    /** Relative tolerance when summing Fisher point probabilities <= observed. */
    const FISHER_TOL = 1.0e-7;

    private static $signPatterns = null;
    private static $logFact = array(0.0);

    /**
     * Calculate GFI and GFQ for a 2x2 table.
     *
     * @param int   $a     Arm A events
     * @param int   $b     Arm A non-events
     * @param int   $c     Arm B events
     * @param int   $d     Arm B non-events
     * @param float $alpha Significance threshold (default 0.05)
     * @return array
     */
    public static function calculate($a, $b, $c, $d, $alpha = self::ALPHA) {
        if ($a < 0 || $b < 0 || $c < 0 || $d < 0) {
            throw new InvalidArgumentException("All cells must be non-negative integers");
        }

        $a = (int)$a; $b = (int)$b; $c = (int)$c; $d = (int)$d;
        $N = $a + $b + $c + $d;

        if ($N === 0) {
            return self::nullResult(null, null, null, 'Empty table.');
        }

        if ($N > self::SHELL_MAX_N) {
            return self::marginCalculate($a, $b, $c, $d, $alpha);
        }

        self::growLogFactorial($N);
        $fisherCache = array();

        // Baseline verdict, two-sided Fisher's exact (the only test used).
        $baselineP = self::fisherP($N, $a + $b, $a + $c, $a, $fisherCache);
        $baseSig   = ($baselineP < $alpha);

        $patterns = self::signPatterns();
        $origin = array($a, $b, $c, $d);

        $evaluated = 0;
        $started = microtime(true);

        for ($dist = 1; $dist <= $N; $dist++) {

            // Compositions of $dist into 1, 2 or 3 strictly positive parts.
            // Shared by every pattern of the same arity within this shell.
            $comps = array(
                1 => self::compositions($dist, 1),
                2 => self::compositions($dist, 2),
                3 => self::compositions($dist, 3),
            );

            foreach ($patterns as $pattern) {
                $pos = $pattern[0];
                $neg = $pattern[1];
                $np  = count($pos);
                $nn  = count($neg);

                // Cannot remove $dist individuals from cells that hold fewer.
                $capTotal = 0;
                for ($m = 0; $m < $nn; $m++) {
                    $capTotal += $origin[$neg[$m]];
                }
                if ($capTotal < $dist) {
                    continue;
                }

                $adds = $comps[$np];
                if (empty($adds)) {
                    continue;
                }

                // Loss compositions, filtered against per-cell availability.
                $subs = array();
                foreach ($comps[$nn] as $sub) {
                    for ($m = 0; $m < $nn; $m++) {
                        if ($sub[$m] > $origin[$neg[$m]]) {
                            continue 2;
                        }
                    }
                    $subs[] = $sub;
                }
                if (empty($subs)) {
                    continue;
                }

                foreach ($adds as $add) {
                    // Apply the gains once per add-composition.
                    $base0 = $a; $base1 = $b; $base2 = $c; $base3 = $d;
                    for ($m = 0; $m < $np; $m++) {
                        switch ($pos[$m]) {
                            case 0: $base0 += $add[$m]; break;
                            case 1: $base1 += $add[$m]; break;
                            case 2: $base2 += $add[$m]; break;
                            default: $base3 += $add[$m]; break;
                        }
                    }

                    foreach ($subs as $sub) {
                        $ca = $base0; $cb = $base1; $cc = $base2; $cd = $base3;
                        for ($m = 0; $m < $nn; $m++) {
                            switch ($neg[$m]) {
                                case 0: $ca -= $sub[$m]; break;
                                case 1: $cb -= $sub[$m]; break;
                                case 2: $cc -= $sub[$m]; break;
                                default: $cd -= $sub[$m]; break;
                            }
                        }

                        $evaluated++;

                        $pv = self::fisherP($N, $ca + $cb, $ca + $cc, $ca, $fisherCache);
                        $sig = ($pv < $alpha);

                        if ($sig !== $baseSig) {
                            return array(
                                'GFI'        => $dist,
                                'GFQ'        => $dist / $N,
                                'verified'   => true,
                                'method'     => 'exhaustive',
                                'test_used'  => 'fisher_exact',
                                'baseline_p' => $baselineP,
                                'baseline_significant' => $baseSig,
                                'final_p'    => $pv,
                                'post_toggle' => array('a' => $ca, 'b' => $cb, 'c' => $cc, 'd' => $cd),
                                'candidates_evaluated' => $evaluated,
                                'note' => sprintf(
                                    "GFI = %d (exact shell search, Fisher's exact). %s candidate tables examined.",
                                    $dist, number_format($evaluated)
                                ),
                            );
                        }
                    }
                }
            }

            // Budget checks happen between shells so a shell is never half-searched.
            if ($evaluated >= self::MAX_CANDIDATES ||
                (microtime(true) - $started) >= self::MAX_SECONDS) {
                // Shell search exhausted: GFI > $dist is proven. Hand over to
                // the margin-coordinate certification, which scales with GFI^2.
                return self::marginCalculate($a, $b, $c, $d, $alpha);
            }
        }

        // Exhaustive completion with no flip anywhere: the result IS verified
        // (every reachable table was examined), matching the reference, which
        // returns verified=True and final_p = baseline p for this case.
        return array(
            'GFI'        => null,
            'GFQ'        => null,
            'verified'   => true,
            'method'     => 'exhaustive',
            'test_used'  => 'fisher_exact',
            'baseline_p' => $baselineP,
            'baseline_significant' => $baseSig,
            'final_p'    => $baselineP,
            'post_toggle' => array('a' => null, 'b' => null, 'c' => null, 'd' => null),
            'candidates_evaluated' => $evaluated,
            'note'       => 'No significance flip exists under any redistribution of the N observations.',
        );
    }

    /**
     * All 50 disjoint (gain-set, loss-set) index pairs over the 4 cells,
     * generated in the same order as the reference implementation.
     */
    private static function signPatterns() {
        if (self::$signPatterns !== null) {
            return self::$signPatterns;
        }
        $out = array();
        $vals = array(-1, 0, 1);
        foreach ($vals as $s0) {
            foreach ($vals as $s1) {
                foreach ($vals as $s2) {
                    foreach ($vals as $s3) {
                        $signs = array($s0, $s1, $s2, $s3);
                        $pos = array(); $neg = array();
                        foreach ($signs as $i => $s) {
                            if ($s === 1) { $pos[] = $i; }
                            elseif ($s === -1) { $neg[] = $i; }
                        }
                        if (!empty($pos) && !empty($neg)) {
                            $out[] = array($pos, $neg);
                        }
                    }
                }
            }
        }
        self::$signPatterns = $out;
        return $out;
    }

    /** Compositions of $total into exactly $parts strictly positive integers. */
    private static function compositions($total, $parts) {
        if ($parts === 1) {
            return array(array($total));
        }
        if ($parts === 2) {
            if ($total < 2) {
                return array();
            }
            $out = array();
            for ($i = 1; $i < $total; $i++) {
                $out[] = array($i, $total - $i);
            }
            return $out;
        }
        if ($total < 3) {
            return array();
        }
        $out = array();
        $hi = $total - 2;
        for ($i = 1; $i <= $hi; $i++) {
            $jmax = $total - 1 - $i;
            if ($jmax > $hi) { $jmax = $hi; }
            for ($j = 1; $j <= $jmax; $j++) {
                $out[] = array($i, $j, $total - $i - $j);
            }
        }
        return $out;
    }

    /** Extend the cached table of log(n!) up to $n. */
    private static function growLogFactorial($n) {
        $lf = self::$logFact;
        $have = count($lf) - 1;
        for ($k = $have + 1; $k <= $n; $k++) {
            $lf[$k] = $lf[$k - 1] + log((float)$k);
        }
        self::$logFact = $lf;
    }

    /**
     * Two-sided Fisher's exact p-value for a table with grand total $N,
     * row-1 total $r1, column-1 total $c1 and cell a = $a.
     * Memoised on (r1, c1, a).
     */
    private static function fisherP($N, $r1, $c1, $a, &$cache) {
        if ($r1 === 0 || $r1 === $N || $c1 === 0 || $c1 === $N) {
            return 1.0;
        }
        $key = ($r1 * ($N + 1) + $c1) * ($N + 1) + $a;
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $lf = self::$logFact;
        $kmin = max(0, $r1 + $c1 - $N);
        $kmax = min($r1, $c1);
        $anchor = $lf[$N] - $lf[$r1] - $lf[$N - $r1];

        $pmf = array();
        for ($k = $kmin; $k <= $kmax; $k++) {
            $logp = $lf[$c1] - $lf[$k] - $lf[$c1 - $k]
                  + $lf[$N - $c1] - $lf[$r1 - $k] - $lf[$N - $c1 - ($r1 - $k)]
                  - $anchor;
            $pmf[] = exp($logp);
        }

        $cutoff = $pmf[$a - $kmin] * (1.0 + self::FISHER_TOL);
        $p = 0.0;
        foreach ($pmf as $x) {
            if ($x <= $cutoff) {
                $p += $x;
            }
        }
        if ($p > 1.0) {
            $p = 1.0;
        }

        $cache[$key] = $p;
        return $p;
    }

    // ==================================================================
    // Exact margin-coordinate certification (large N)
    // Port of compute_gfi_gfq in fragility_metrics_r12.py.
    // ==================================================================

    /**
     * GFI by fast rigorous upper bound plus level-ascending certification.
     */
    private static function marginCalculate($a, $b, $c, $d, $alpha) {
        $origin = array($a, $b, $c, $d);
        $N = $a + $b + $c + $d;
        $R0 = $a + $b;
        $C0 = $a + $c;
        $started = microtime(true);

        $baselineP = self::mFisherP($a, $R0, $C0, $N);
        $baseSig = ($baselineP < $alpha);

        // ---- 1. rigorous incumbent ----
        $best = self::mInitialIncumbent($origin, $baseSig, $alpha);
        if ($best === null) {
            $bestD = $N + 1;
            $bestT = null;
        } else {
            list($bestD, $bestT) = self::mTightenRays($origin, $best[0], $best[1], $baseSig, $alpha);
        }

        // ---- 2. level-ascending certification ----
        $examined = 0;
        $aborted = false;
        $levelDone = 0;
        $gU = array(); $gV = array();

        $D = 1;
        while ($D < $bestD) {
            $lim = $bestD - 1;
            foreach (self::mLevelPairs($D, $N, $R0, $C0) as $uv) {
                $gU[] = $uv[0];
                $gV[] = $uv[1];
                $examined++;
            }
            if ($examined > self::MARGIN_MAX_CANDIDATES ||
                (microtime(true) - $started) >= self::MARGIN_MAX_SECONDS) {
                $aborted = true;
                break;
            }
            if (count($gU) >= self::MARGIN_GROUP_ROWS || $D + 1 >= $bestD) {
                self::mFlush($origin, $gU, $gV, $bestD, $bestT, $baseSig, $alpha);
                $levelDone = $D;
                $gU = array(); $gV = array();
            } elseif (empty($gU)) {
                $levelDone = $D;
            }
            $D++;
        }
        if (!$aborted && !empty($gU)) {
            self::mFlush($origin, $gU, $gV, $bestD, $bestT, $baseSig, $alpha);
            $levelDone = $D - 1;
        }

        if ($bestT === null) {
            if ($aborted) {
                return self::nullResult('fisher_exact', $baselineP, $baseSig, sprintf(
                    'GFI not computed: certification stopped after %s candidate margin pairs ' .
                    'with no flipping table found. Proven lower bound: GFI >= %d.',
                    number_format($examined), $levelDone + 1), $examined);
            }
            return array(
                'GFI' => null, 'GFQ' => null, 'verified' => true,
                'method' => 'margin_certification', 'test_used' => 'fisher_exact',
                'baseline_p' => $baselineP, 'baseline_significant' => $baseSig,
                'final_p' => $baselineP,
                'post_toggle' => array('a' => null, 'b' => null, 'c' => null, 'd' => null),
                'candidates_evaluated' => $examined,
                'note' => 'No significance flip exists under any redistribution of the N observations. ' .
                          'Certified by complete margin search.',
            );
        }

        // ---- independent verification of the reported witness ----
        $vp = self::mFisherP($bestT[0], $bestT[0] + $bestT[1], $bestT[0] + $bestT[2], $N);
        $dist = (abs($bestT[0] - $a) + abs($bestT[1] - $b) + abs($bestT[2] - $c) + abs($bestT[3] - $d)) / 2;
        if ((($vp < $alpha) === $baseSig) || (int)$dist !== (int)$bestD ||
            array_sum($bestT) !== $N) {
            return self::nullResult('fisher_exact', $baselineP, $baseSig,
                'GFI not computed: witness table failed verification.', $examined);
        }

        $out = array(
            'GFI'        => (int)$bestD,
            'GFQ'        => $bestD / $N,
            'verified'   => !$aborted,
            'method'     => $aborted ? 'margin_upper_bound' : 'margin_certification',
            'test_used'  => 'fisher_exact',
            'baseline_p' => $baselineP,
            'baseline_significant' => $baseSig,
            'final_p'    => $vp,
            'post_toggle' => array('a' => $bestT[0], 'b' => $bestT[1], 'c' => $bestT[2], 'd' => $bestT[3]),
            'candidates_evaluated' => $examined,
        );
        $out['note'] = $aborted
            ? sprintf('GFI <= %d: UPPER BOUND; certification stopped at the work/time budget after %s ' .
                      'candidate margin pairs. Proven bracket: %d <= GFI <= %d.',
                      $bestD, number_format($examined), $levelDone + 1, $bestD)
            : sprintf('GFI = %d (exact margin-coordinate certification, Fisher\'s exact); global fixed-N ' .
                      'minimum certified over %s candidate margin pairs.',
                      $bestD, number_format($examined));
        return $out;
    }

    /** Rigorous starting incumbent: a real opposite-classification table. */
    private static function mInitialIncumbent($origin, $baseSig, $alpha) {
        list($a, $b, $c, $d) = $origin;
        $N = $a + $b + $c + $d;

        if ($baseSig) {
            // A zeroed row or column always has Fisher p = 1.
            $best = null;
            foreach (array(
                array(0, 0, $a + $c, $b + $d),
                array($a + $c, $b + $d, 0, 0),
                array(0, $a + $b, 0, $c + $d),
                array($a + $b, 0, $c + $d, 0),
            ) as $t) {
                if (self::mAccepts($t[0], $t[0] + $t[1], $t[0] + $t[2], $N, $alpha)) {
                    $dist = self::mTransfer($origin, $t);
                    if ($best === null || $dist < $best[0]) {
                        $best = array($dist, $t);
                    }
                }
            }
            return $best;
        }

        // Complete-separation tables, first minimum in enumeration order.
        $best = null;
        foreach (array(0, 1) as $form) {
            for ($k = 0; $k <= $N; $k++) {
                $t = ($form === 0) ? array($k, 0, 0, $N - $k) : array(0, $k, $N - $k, 0);
                $dist = self::mTransfer($origin, $t);
                if ($best !== null && $dist >= $best[0]) {
                    continue;
                }
                if (!self::mAccepts($t[0], $t[0] + $t[1], $t[0] + $t[2], $N, $alpha)) {
                    $best = array($dist, $t);
                }
            }
        }
        return $best;
    }

    /** All 12 single-transfer rays, every integer step evaluated. */
    private static function mTightenRays($origin, $bestD, $bestT, $baseSig, $alpha) {
        $N = array_sum($origin);
        for ($src = 0; $src < 4; $src++) {
            for ($dst = 0; $dst < 4; $dst++) {
                if ($src === $dst) {
                    continue;
                }
                $top = min($origin[$src], $bestD - 1);
                for ($k = 1; $k <= $top; $k++) {
                    $t = $origin;
                    $t[$src] -= $k;
                    $t[$dst] += $k;
                    $sig = !self::mAccepts($t[0], $t[0] + $t[1], $t[0] + $t[2], $N, $alpha);
                    if ($sig !== $baseSig) {
                        $bestD = $k;
                        $bestT = $t;
                        break;
                    }
                }
            }
        }
        return array($bestD, $bestT);
    }

    /** Margin pairs (u, v) at level D = max(|u|, |v|), in reference order. */
    private static function mLevelPairs($D, $N, $R0, $C0) {
        $out = array();
        foreach (array(-$D, $D) as $u) {
            for ($v = -$D; $v <= $D; $v++) {
                if ($R0 + $u >= 0 && $R0 + $u <= $N && $C0 + $v >= 0 && $C0 + $v <= $N) {
                    $out[] = array($u, $v);
                }
            }
        }
        for ($u = -$D + 1; $u <= $D - 1; $u++) {
            foreach (array(-$D, $D) as $v) {
                if ($R0 + $u >= 0 && $R0 + $u <= $N && $C0 + $v >= 0 && $C0 + $v <= $N) {
                    $out[] = array($u, $v);
                }
            }
        }
        return $out;
    }

    /** Process one batch of margin pairs; refine hits tightest-first. */
    private static function mFlush($origin, $U, $V, &$bestD, &$bestT, $baseSig, $alpha) {
        $K = 2 * $bestD - 2;
        $pending = array();
        $n = count($U);
        for ($i = 0; $i < $n; $i++) {
            if (self::mMarginHit($origin, $U[$i], $V[$i], $K, $baseSig, $alpha) !== null) {
                $pending[] = $i;
            }
        }
        while (!empty($pending)) {
            $K = 2 * $bestD - 2;
            $keep = array();
            foreach ($pending as $j) {
                if (2 * max(abs($U[$j]), abs($V[$j])) <= $K &&
                    self::mMarginHit($origin, $U[$j], $V[$j], $K, $baseSig, $alpha) !== null) {
                    $keep[] = $j;
                }
            }
            $pending = $keep;
            if (empty($pending)) {
                break;
            }
            $pos = 0;
            $kloBest = PHP_INT_MAX;
            foreach ($pending as $p => $j) {
                $klo = 2 * max(abs($U[$j]), abs($V[$j]));
                if ($klo < $kloBest) { $kloBest = $klo; $pos = $p; }
            }
            $j = $pending[$pos];
            array_splice($pending, $pos, 1);

            // Bisection: membership is monotone in K.
            $lo = $kloBest; $hi = $K;
            while ($lo < $hi) {
                $mid = intdiv($lo + $hi, 2);
                if (self::mMarginHit($origin, $U[$j], $V[$j], $mid, $baseSig, $alpha) !== null) {
                    $hi = $mid;
                } else {
                    $lo = $mid + 1;
                }
            }
            $x = self::mMarginHit($origin, $U[$j], $V[$j], $lo, $baseSig, $alpha);
            if ($x === null) {
                continue;
            }
            $dNew = intdiv($lo, 2);
            if ($dNew < $bestD) {
                $N = array_sum($origin);
                $R = $origin[0] + $origin[1] + $U[$j];
                $C = $origin[0] + $origin[2] + $V[$j];
                $bestD = $dNew;
                $bestT = array($x, $R - $x, $C - $x, $N - $R - $C + $x);
            }
        }
    }

    /**
     * Does any table with margins (R0+u, C0+v) lie in the target region within
     * L1 distance K of the origin? Returns the witness upper-left cell or null.
     */
    private static function mMarginHit($origin, $u, $v, $K, $baseSig, $alpha) {
        list($a0, $b0, $c0, $d0) = $origin;
        $N = $a0 + $b0 + $c0 + $d0;
        $R = $a0 + $b0 + $u;
        $C = $a0 + $c0 + $v;
        if ($R < 0 || $R > $N || $C < 0 || $C > $N) {
            return null;
        }

        // Closed-form sublevel interval { z : D(z) <= K }, w = 0.
        $P = array(0, $u, $v, $u + $v);
        sort($P);
        $e = $K - (($P[2] + $P[3]) - ($P[0] + $P[1]));
        if ($e < 0) {
            return null;
        }
        $half = intdiv($e, 2);
        $spanR = $P[3] - $P[2];
        $zhi = ($half <= $spanR) ? $P[2] + $half : $P[3] + intdiv(max($e - 2 * $spanR, 0), 4);
        $spanL = $P[1] - $P[0];
        $zlo = ($half <= $spanL) ? $P[1] - $half : $P[0] - intdiv(max($e - 2 * $spanL, 0), 4);

        $slo = max(0, $R + $C - $N);
        $shi = min($R, $C);
        $xlo = max($zlo + $a0, $slo);
        $xhi = min($zhi + $a0, $shi);
        if ($xlo > $xhi) {
            return null;
        }

        if ($baseSig) {
            // Target = acceptance interval, which always contains the mode.
            $m = min(max(intdiv(($C + 1) * ($R + 1), $N + 2), $slo), $shi);
            if ($m >= $xlo && $m <= $xhi) {
                return $m;
            }
            $x = min(max($m, $xlo), $xhi);
            return self::mAccepts($x, $R, $C, $N, $alpha) ? $x : null;
        }
        // Target = the significant tails at the ends of the window.
        if (!self::mAccepts($xlo, $R, $C, $N, $alpha)) {
            return $xlo;
        }
        if (!self::mAccepts($xhi, $R, $C, $N, $alpha)) {
            return $xhi;
        }
        return null;
    }

    private static function mTransfer($o, $t) {
        return intdiv(abs($t[0] - $o[0]) + abs($t[1] - $o[1]) + abs($t[2] - $o[2]) + abs($t[3] - $o[3]), 2);
    }

    // ---- Fisher engine for large N (SciPy probability-ordering rule) ----

    /** log(n!) : exact table below 1024, Stirling series above. */
    private static function mLnFact($n) {
        if ($n < 1024) {
            if (count(self::$logFact) <= $n) {
                self::growLogFactorial(1023);
            }
            return self::$logFact[$n];
        }
        $x = (float)$n;
        $x2 = $x * $x;
        return $x * log($x) - $x + 0.5 * log(2.0 * M_PI * $x)
             + 1.0 / (12.0 * $x) - 1.0 / (360.0 * $x * $x2) + 1.0 / (1260.0 * $x * $x2 * $x2);
    }

    /** Hypergeometric pmf of the upper-left cell x with margins R, C and total N. */
    private static function mPmf($x, $R, $C, $N) {
        if ($x < max(0, $R + $C - $N) || $x > min($R, $C)) {
            return 0.0;
        }
        return exp(self::mLnFact($C) - self::mLnFact($x) - self::mLnFact($C - $x)
                 + self::mLnFact($N - $C) - self::mLnFact($R - $x) - self::mLnFact($N - $C - $R + $x)
                 - self::mLnFact($N) + self::mLnFact($R) + self::mLnFact($N - $R));
    }

    /** P(X <= x). Sums outward from x; terms decrease away from the mode. */
    private static function mCdf($x, $R, $C, $N) {
        $slo = max(0, $R + $C - $N);
        $shi = min($R, $C);
        if ($x < $slo) { return 0.0; }
        if ($x >= $shi) { return 1.0; }
        $mode = intdiv(($C + 1) * ($R + 1), $N + 2);
        if ($x >= $mode) {
            return max(0.0, 1.0 - self::mSf($x, $R, $C, $N));
        }
        $t = self::mPmf($x, $R, $C, $N);
        $s = $t;
        for ($k = $x; $k > $slo && $t > 0.0; $k--) {
            $t *= ($k * ($N - $R - $C + $k)) / (($R - $k + 1) * ($C - $k + 1));
            $s += $t;
            if ($t < $s * 1e-17) { break; }
        }
        return $s;
    }

    /** P(X > x). */
    private static function mSf($x, $R, $C, $N) {
        $slo = max(0, $R + $C - $N);
        $shi = min($R, $C);
        if ($x < $slo) { return 1.0; }
        if ($x >= $shi) { return 0.0; }
        $mode = intdiv(($C + 1) * ($R + 1), $N + 2);
        if ($x + 1 <= $mode) {
            return max(0.0, 1.0 - self::mCdf($x, $R, $C, $N));
        }
        $k = $x + 1;
        $t = self::mPmf($k, $R, $C, $N);
        $s = $t;
        for (; $k < $shi && $t > 0.0; $k++) {
            $t *= (($R - $k) * ($C - $k)) / (($k + 1) * ($N - $R - $C + $k + 1));
            $s += $t;
            if ($t < $s * 1e-17) { break; }
        }
        return $s;
    }

    /** Two-sided Fisher p, SciPy probability-ordering rule (gamma = 1 + 1e-14). */
    private static function mFisherP($x, $R, $C, $N) {
        if ($N <= 0 || $R <= 0 || $R >= $N || $C <= 0 || $C >= $N) {
            return 1.0;
        }
        $mode = intdiv(($C + 1) * ($R + 1), $N + 2);
        $px = self::mPmf($x, $R, $C, $N);
        $pm = self::mPmf($mode, $R, $C, $N);
        $mx = max($px, $pm);
        if (($mx > 0.0 && abs($px - $pm) / $mx <= 1e-14) || ($mx == 0.0 && $x === $mode)) {
            return 1.0;
        }
        $target = $px * self::MARGIN_GAMMA;

        if ($x < $mode) {
            $p = self::mCdf($x, $R, $C, $N);
            if (self::mPmf($C, $R, $C, $N) > $target) {
                return min($p, 1.0);
            }
            // largest i in [mode, C] with pmf(i) >= target
            $lo = $mode; $hi = $C;
            while ($lo < $hi) {
                $mid = $lo + intdiv($hi - $lo + 1, 2);
                if (self::mPmf($mid, $R, $C, $N) >= $target) { $lo = $mid; } else { $hi = $mid - 1; }
            }
            return min($p + self::mSf($lo, $R, $C, $N), 1.0);
        }

        $p = self::mSf($x - 1, $R, $C, $N);
        if (self::mPmf(0, $R, $C, $N) > $target) {
            return min($p, 1.0);
        }
        // largest i in [0, mode] with pmf(i) <= target (lo - 1 if none)
        $lo = 0; $hi = $mode;
        if (self::mPmf(0, $R, $C, $N) > $target) {
            $g = -1;
        } else {
            while ($lo < $hi) {
                $mid = $lo + intdiv($hi - $lo + 1, 2);
                if (self::mPmf($mid, $R, $C, $N) <= $target) { $lo = $mid; } else { $hi = $mid - 1; }
            }
            $g = $lo;
        }
        return min($p + ($g >= 0 ? self::mCdf($g, $R, $C, $N) : 0.0), 1.0);
    }

    /**
     * Decision p >= alpha with the reference pre-filter cascade; rows no
     * filter decides get the full p-value, so the decision always equals
     * mFisherP(...) >= alpha.
     */
    private static function mAccepts($x, $R, $C, $N, $alpha) {
        if ($N <= 0 || $R <= 0 || $R >= $N || $C <= 0 || $C >= $N) {
            return true;
        }
        $mode = intdiv(($C + 1) * ($R + 1), $N + 2);
        $slo = max(0, $R + $C - $N);
        $shi = min($R, $C);
        $nPts = $shi - $slo + 1;
        $below = ($x < $mode);
        $guard = $alpha * (1.0 - 1e-9);

        $pmfX = self::mPmf($x, $R, $C, $N);
        if ($pmfX >= $alpha) {
            return true;
        }
        $tailCount = $below ? ($x - $slo + 1) : ($shi - $x + 1);
        if (($tailCount + $nPts) * $pmfX * self::MARGIN_GAMMA < $guard) {
            return false;
        }
        $tail = $below ? self::mCdf($x, $R, $C, $N) : self::mSf($x - 1, $R, $C, $N);
        if ($tail >= $alpha) {
            return true;
        }
        if ($tail + $nPts * $pmfX * self::MARGIN_GAMMA < $guard) {
            return false;
        }
        return self::mFisherP($x, $R, $C, $N) >= $alpha;
    }

    private static function nullResult($testUsed, $baselineP, $baseSig, $note, $evaluated = 0) {
        return array(
            'GFI'        => null,
            'GFQ'        => null,
            'verified'   => false,
            'method'     => 'skipped',
            'test_used'  => $testUsed,
            'baseline_p' => $baselineP,
            'baseline_significant' => $baseSig,
            'final_p'    => null,
            'post_toggle' => array('a' => null, 'b' => null, 'c' => null, 'd' => null),
            'candidates_evaluated' => $evaluated,
            'note'       => $note,
        );
    }
}

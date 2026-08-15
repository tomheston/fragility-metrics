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
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 *
 * License: Creative Commons Attribution 4.0 International (CC BY 4.0)
 */

class GlobalFragilityIndex {

    /** Default significance threshold. */
    const ALPHA = 0.05;

    /**
     * Tables larger than this are not attempted at all.
     * Matches GFI_THRESHOLD in the reference Python implementation.
     */
    const MAX_N = 250000;

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

        if ($N > self::MAX_N) {
            return self::nullResult(null, null, null,
                "GFI not computed for N > " . self::MAX_N . ". Use MFQ or RQ instead.");
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
                return self::nullResult('fisher_exact', $baselineP, $baseSig, sprintf(
                    'GFI not computed: search budget exhausted after distance %d (%s candidate tables). ' .
                    'GFI is known to exceed %d. Use MFQ or RQ instead.',
                    $dist, number_format($evaluated), $dist
                ), $evaluated);
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

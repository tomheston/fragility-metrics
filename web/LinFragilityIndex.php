<?php
/**
 * LinFragilityIndex.php
 * Lin Fragility Index (LinFI) and Lin Fragility Quotient (LinFQ) for 2x2 tables.
 *
 * LinFI = the minimum total number of within-arm outcome toggles
 * (event <-> non-event), in either or both arms, that flips the significance
 * classification in either direction. Both arm sizes stay fixed.
 * Formally min(|f0| + |f1|), where f0 and f1 are the net event-count changes
 * in Arm A and Arm B (Lin & Chu, PLoS ONE 2022; `fragility` R package,
 * frag.study). LinFQ = LinFI / N.
 *
 * ROLE ON THIS SITE
 * -----------------
 * GFQ is the recommended fr metric for 2x2 tables. The calculator reports
 * LinFQ only when the GFI is too large to certify within the web time
 * budget. Every Lin toggle is also a GFI move, so GFI <= LinFI <= FI under
 * the same test, and LinFI is cheap where GFI is not: with both arm sizes
 * fixed the search is one-dimensional.
 *
 * ALGORITHM - exact, one pass over the event-total change v
 * ----------------------------------------------------------
 * Let x = Arm A events and y = Arm B events after toggling, and v the change
 * in the event total (x + y) - (a + c). For a fixed v the table has fixed
 * margins (row total n_A, column total a + c + v), and its toggle distance
 * from the observed table is
 *     |x - a| + |(a + v) - x| = |v| + 2 * dist(x, [a + min(0,v), a + max(0,v)]).
 * For fixed margins the two-sided Fisher p-value is non-increasing as x
 * moves away from the hypergeometric mode, so the nonsignificant x form one
 * interval [L, U] around the mode and the significant x form the two tails.
 * For each v the nearest table on the other side of alpha is therefore
 * found from L and U alone. Every candidate costs at least |v| toggles, so
 * scanning v = 0, +1, -1, +2, -2, ... and stopping once |v| reaches the best
 * distance found gives the exact minimum. L and U move by about one step as
 * v changes, so they are tracked from the previous v by galloping search.
 *
 * SIGNIFICANCE TEST
 * -----------------
 * Two-sided Fisher's exact (SciPy probability-ordering rule), via the
 * large-N engine in GlobalFragilityIndex.php. Significance is strict: p < alpha.
 *
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 *
 * License: Creative Commons Attribution 4.0 International (CC BY 4.0)
 */

require_once 'GlobalFragilityIndex.php';

class LinFragilityIndex {

    const ALPHA = 0.05;

    /** Wall-clock budget in seconds; a stopped scan reports an upper bound. */
    const MAX_SECONDS = 5.0;

    /**
     * @param int        $a     Arm A events
     * @param int        $b     Arm A non-events
     * @param int        $c     Arm B events
     * @param int        $d     Arm B non-events
     * @param float      $alpha Significance threshold (default 0.05)
     * @param array|null $seed  Optional known flipping table with the same arm
     *                          sizes (e.g. the post-FI table), keys a,b,c,d. It
     *                          is re-verified here and only used as a starting
     *                          upper bound, so LinFI <= FI holds even when the
     *                          scan stops at the time limit.
     * @return array
     */
    public static function calculate($a, $b, $c, $d, $alpha = self::ALPHA, $seed = null) {
        if ($a < 0 || $b < 0 || $c < 0 || $d < 0) {
            throw new InvalidArgumentException("All cells must be non-negative integers");
        }
        $a = (int)$a; $b = (int)$b; $c = (int)$c; $d = (int)$d;
        $nA = $a + $b;
        $nB = $c + $d;
        $N  = $nA + $nB;
        $C0 = $a + $c;

        if ($N === 0) {
            return self::result(null, null, null, null, true, 'Empty table.', $N);
        }

        $baselineP = GlobalFragilityIndex::fisherPMargins($a, $nA, $C0, $N);
        $baseSig = !GlobalFragilityIndex::acceptsMargins($a, $nA, $C0, $N, $alpha);

        // An empty arm makes every reachable table p = 1.
        if ($nA === 0 || $nB === 0) {
            return self::result(null, $baselineP, $baseSig, null, true,
                'LinFI undefined: one arm is empty, so no toggle can change significance.', $N);
        }

        $deadline = microtime(true) + self::MAX_SECONDS;
        $best = PHP_INT_MAX;
        $bestX = null;
        $bestC = null;
        $aborted = false;

        // Starting upper bound from a known flipping table, re-verified.
        if (is_array($seed) && isset($seed['a'], $seed['b'], $seed['c'], $seed['d'])) {
            $sx = (int)$seed['a']; $sy = (int)$seed['c'];
            $sameArms = ($sx >= 0 && $sy >= 0 && $sx <= $nA && $sy <= $nB &&
                         (int)$seed['b'] === $nA - $sx && (int)$seed['d'] === $nB - $sy);
            $seedSig = $sameArms && !GlobalFragilityIndex::acceptsMargins($sx, $nA, $sx + $sy, $N, $alpha);
            if ($sameArms && $seedSig !== $baseSig) {
                $best = abs($sx - $a) + abs($sy - $c);
                $bestX = $sx;
                $bestC = $sx + $sy;
            }
        }

        // Warm-start boundaries, kept separately for v >= 0 and v < 0.
        $warm = array(1 => null, -1 => null);

        $vMin = -$C0;          // event total cannot go below 0
        $vMax = $N - $C0;      // or above N

        for ($k = 0; $k < $best; $k++) {
            if ($k > -$vMin && $k > $vMax) {
                break;
            }
            if (microtime(true) > $deadline) {
                $aborted = true;
                break;
            }
            foreach (($k === 0 ? array(0) : array($k, -$k)) as $v) {
                if ($v < $vMin || $v > $vMax) {
                    continue;
                }
                $C = $C0 + $v;
                $slo = max(0, $C - $nB);
                $shi = min($nA, $C);

                // Toggle cost is |v| + 2 * gap to the zero-extra-cost x interval.
                $p = $a + min(0, $v);
                $q = $a + max(0, $v);

                // Prune: only x within gap gmax of [p, q] can beat the best so
                // far. Acceptance is one interval around the mode, so the
                // window [xl, xr] holds a target table iff (significant
                // baseline) the mode is inside or an end of the window is
                // accepted, or (nonsignificant baseline) an end is rejected.
                if ($best !== PHP_INT_MAX) {
                    $gmax = intdiv($best - abs($v) - 1, 2);
                    if ($gmax < 0) {
                        continue;
                    }
                    $xl = max($p - $gmax, $slo);
                    $xr = min($q + $gmax, $shi);
                    if ($xl > $xr) {
                        continue;
                    }
                    $m = min(max(intdiv(($C + 1) * ($nA + 1), $N + 2), $slo), $shi);
                    if ($baseSig) {
                        $reach = ($m >= $xl && $m <= $xr) ||
                                 ($m < $xl && GlobalFragilityIndex::acceptsMargins($xl, $nA, $C, $N, $alpha)) ||
                                 ($m > $xr && GlobalFragilityIndex::acceptsMargins($xr, $nA, $C, $N, $alpha));
                    } else {
                        $reach = !GlobalFragilityIndex::acceptsMargins($xl, $nA, $C, $N, $alpha) ||
                                 ($xr !== $xl && !GlobalFragilityIndex::acceptsMargins($xr, $nA, $C, $N, $alpha));
                    }
                    if (!$reach) {
                        continue;
                    }
                }

                $side = ($v >= 0) ? 1 : -1;
                list($L, $U) = self::acceptanceInterval($nA, $C, $N, $slo, $shi, $alpha, $warm[$side]);
                $warm[$side] = array($L, $U);

                $cands = array();
                if ($baseSig) {
                    $cands[] = self::nearest($L, $U, $p, $q);
                } else {
                    $cands[] = self::nearest($slo, $L - 1, $p, $q);
                    $cands[] = self::nearest($U + 1, $shi, $p, $q);
                }
                foreach ($cands as $hit) {
                    if ($hit === null) {
                        continue;
                    }
                    $dist = abs($v) + 2 * $hit[1];
                    if ($dist < $best) {
                        $best = $dist;
                        $bestX = $hit[0];
                        $bestC = $C;
                    }
                }
            }
        }

        if ($bestX === null) {
            if ($aborted) {
                return self::result(null, $baselineP, $baseSig, null, false, sprintf(
                    'LinFI not computed: the scan stopped at the %.0f-second web time limit with no ' .
                    'flipping table found.', self::MAX_SECONDS), $N);
            }
            return self::result(null, $baselineP, $baseSig, null, true,
                'LinFI undefined: no within-arm toggles in either or both arms can change significance.', $N);
        }

        $x = $bestX;
        $y = $bestC - $x;
        $post = array('a' => $x, 'b' => $nA - $x, 'c' => $y, 'd' => $nB - $y);

        // Independent check of the reported witness.
        $finalP = GlobalFragilityIndex::fisherPMargins($x, $nA, $bestC, $N);
        $sigAfter = !GlobalFragilityIndex::acceptsMargins($x, $nA, $bestC, $N, $alpha);
        if ($sigAfter === $baseSig || abs($x - $a) + abs($y - $c) !== $best ||
            $post['b'] < 0 || $post['d'] < 0 || $y < 0) {
            return self::result(null, $baselineP, $baseSig, null, false,
                'LinFI not computed: witness table failed verification.', $N);
        }

        $out = self::result($best, $baselineP, $baseSig, $finalP, !$aborted, $aborted
            ? sprintf('LinFI <= %d: upper bound; the scan stopped at the %.0f-second web time limit.',
                      $best, self::MAX_SECONDS)
            : sprintf('LinFI = %d (exact; within-arm toggles in either or both arms, two-sided Fisher\'s exact).', $best),
            $N);
        $out['post_table'] = $post;
        $out['toggles_A'] = $x - $a;
        $out['toggles_B'] = $y - $c;
        return $out;
    }

    /**
     * Nonsignificant interval [L, U] for margins (row total R, column total C).
     * Fisher p is non-increasing away from the mode, so acceptance is one
     * interval containing the mode. Tracks from $warm when given.
     */
    private static function acceptanceInterval($R, $C, $N, $slo, $shi, $alpha, $warm) {
        $m = intdiv(($C + 1) * ($R + 1), $N + 2);
        $m = min(max($m, $slo), $shi);
        $gL = ($warm === null) ? $m : $warm[0];
        $gU = ($warm === null) ? $m : $warm[1];
        $L = self::boundary($R, $C, $N, $alpha, $slo, $m, $gL, -1);
        $U = self::boundary($R, $C, $N, $alpha, $m, $shi, $gU, +1);
        return array($L, $U);
    }

    /**
     * Outermost accepted x between the mode and the support edge.
     * $dir = -1 searches [edge = $lo .. mode = $hi] for the smallest accepted x;
     * $dir = +1 searches [mode = $lo .. edge = $hi] for the largest accepted x.
     * The mode is always accepted (p = 1 there).
     */
    private static function boundary($R, $C, $N, $alpha, $lo, $hi, $guess, $dir) {
        $acc = function ($x) use ($R, $C, $N, $alpha) {
            return GlobalFragilityIndex::acceptsMargins($x, $R, $C, $N, $alpha);
        };
        // Work in a coordinate t that increases toward the edge: accepted for
        // t <= T (mode side), rejected beyond. Find T.
        if ($dir < 0) {
            $tMode = -$hi; $tEdge = -$lo; $t0 = -$guess;
        } else {
            $tMode = $lo;  $tEdge = $hi;  $t0 = $guess;
        }
        $toX = function ($t) use ($dir) { return ($dir < 0) ? -$t : $t; };
        $t0 = min(max($t0, $tMode), $tEdge);

        // Bracket: $in accepted, $out rejected or one past the edge.
        if ($acc($toX($t0))) {
            $in = $t0;
            $step = 1;
            while (true) {
                $t = $in + $step;
                if ($t > $tEdge) { $out = $tEdge + 1; break; }
                if (!$acc($toX($t))) { $out = $t; break; }
                $in = $t;
                $step *= 2;
            }
        } else {
            $out = $t0;
            $step = 1;
            while (true) {
                $t = $out - $step;
                if ($t <= $tMode) { $in = $tMode; break; }
                if ($acc($toX($t))) { $in = $t; break; }
                $out = $t;
                $step *= 2;
            }
        }
        while ($out - $in > 1) {
            $mid = $in + intdiv($out - $in, 2);
            if ($acc($toX($mid))) { $in = $mid; } else { $out = $mid; }
        }
        return $toX($in);
    }

    /** Point of [lo, hi] nearest to [p, q]: array(x, gap) or null when empty. */
    private static function nearest($lo, $hi, $p, $q) {
        if ($lo > $hi) {
            return null;
        }
        if ($hi < $p) {
            return array($hi, $p - $hi);
        }
        if ($lo > $q) {
            return array($lo, $lo - $q);
        }
        return array(max($lo, $p), 0);
    }

    private static function result($linFI, $baselineP, $baseSig, $finalP, $verified, $note, $N) {
        return array(
            'LinFI'      => $linFI,
            'LinFQ'      => ($linFI !== null && $N > 0) ? $linFI / $N : null,
            'verified'   => $verified,
            'test_used'  => 'fisher_exact',
            'baseline_p' => $baselineP,
            'baseline_significant' => $baseSig,
            'final_p'    => $finalP,
            'post_table' => null,
            'note'       => $note,
        );
    }
}

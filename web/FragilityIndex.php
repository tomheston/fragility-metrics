<?php
/**
 * FragilityIndex.php
 * Walsh-compliant Fragility Index calculator for 2×2 tables
 * 
 * Implements tie-breaking logic:
 * 1. Choose arm with fewer events (a vs c)
 * 2. If tied, choose arm with fewer subjects (a+b vs c+d)
 * 3. If still tied, choose Arm A
 * 
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 */

require_once 'FisherExactTest.php';
require_once 'GlobalFragilityIndex.php';

class FragilityIndex {

    private const ALPHA = 0.05;
    /** Matches max_iter = 10**6 in the reference Python implementation. */
    private const MAX_ITERATIONS = 1000000;

    /**
     * Above this N the toggle scan uses the large-N Fisher engine (SciPy
     * probability-ordering rule, as in the reference Python), whose cost per
     * step does not grow with N. At or below it the original exact scan runs
     * unchanged.
     */
    private const FAST_ENGINE_N = 2000;

    /** Wall-clock budget for the large-N scan; on timeout FI is not reported. */
    private const MAX_SECONDS = 4.0;
    
    /**
     * Calculate FI, FQ, MFQ for a 2×2 table
     * 
     * @param int $a Arm A events
     * @param int $b Arm A non-events
     * @param int $c Arm B events
     * @param int $d Arm B non-events
     * @param float $alpha Significance threshold (default 0.05)
     * @return array Results including FI, FQ, MFQ, metadata, post-toggle values
     */
    public static function calculate($a, $b, $c, $d, $alpha = self::ALPHA) {
        // Validate inputs
        if ($a < 0 || $b < 0 || $c < 0 || $d < 0) {
            throw new InvalidArgumentException("All cells must be non-negative integers");
        }
        
        $N = $a + $b + $c + $d;
        
        if ($N == 0) {
            return [
                'FI' => null,
                'FQ' => null,
                'MFQ' => null,
                'baseline_p' => null,
                'baseline_state' => null,
                'arm' => null,
                'direction' => null,
                'n_mod' => 0,
                'final_p' => null,
                'post_toggle' => ['a' => null, 'b' => null, 'c' => null, 'd' => null],
                'note' => 'Empty table'
            ];
        }
        
        // Choose arm using Walsh tie-breaking logic
        $arm = self::chooseArm($a, $b, $c, $d);

        if ($N > self::FAST_ENGINE_N) {
            // Baseline and every step scored by the same large-N engine, so
            // the verdicts being compared can never disagree at a knife edge.
            $baseline_p = GlobalFragilityIndex::fisherPLargeN($a, $b, $c, $d);
            $baseline_sig = !GlobalFragilityIndex::acceptsMargins($a, $a + $b, $a + $c, $N, $alpha);
            $scan = self::fastScan($a, $b, $c, $d, $arm, $baseline_sig, $alpha);
            if ($scan['timed_out']) {
                $n_mod = ($arm === 'A') ? ($a + $b) : ($c + $d);
                return [
                    'FI' => null,
                    'FQ' => null,
                    'MFQ' => null,
                    'baseline_p' => $baseline_p,
                    'baseline_state' => $baseline_sig ? 'significant' : 'non-significant',
                    'arm' => $arm,
                    'direction' => null,
                    'n_mod' => $n_mod,
                    'final_p' => null,
                    'post_toggle' => ['a' => null, 'b' => null, 'c' => null, 'd' => null],
                    'timed_out' => true,
                    'note' => sprintf('FI not computed: more than %s toggles, beyond the web time limit.',
                                      number_format($scan['scanned']))
                ];
            }
            $up = $scan['up'];
            $down = $scan['down'];
        } else {
            // Get baseline p-value and state.
            // Significance is strict: p = alpha counts as non-significant, matching
            // the reference Python implementation and GlobalFragilityIndex.
            $baseline_p = FisherExactTest::calculate($a, $b, $c, $d);
            $baseline_sig = ($baseline_p < $alpha);

            // Try both directions (increase events, decrease events)
            $up = self::stepsToFlip($a, $b, $c, $d, $arm, 'up', $baseline_sig, $alpha);
            $down = self::stepsToFlip($a, $b, $c, $d, $arm, 'down', $baseline_sig, $alpha);
        }
        $baseline_state = $baseline_sig ? 'significant' : 'non-significant';

        // Choose minimum steps
        $candidates = [];
        if ($up['steps'] !== null) {
            $candidates[] = ['direction' => 'non-events → events', 'steps' => $up['steps'], 'final_p' => $up['final_p']];
        }
        if ($down['steps'] !== null) {
            $candidates[] = ['direction' => 'events → non-events', 'steps' => $down['steps'], 'final_p' => $down['final_p']];
        }
        
        // No flip possible
        if (empty($candidates)) {
            $n_mod = ($arm === 'A') ? ($a + $b) : ($c + $d);
            return [
                'FI' => null,
                'FQ' => null,
                'MFQ' => null,
                'baseline_p' => $baseline_p,
                'baseline_state' => $baseline_state,
                'arm' => $arm,
                'direction' => null,
                'n_mod' => $n_mod,
                'final_p' => $baseline_p,
                'post_toggle' => ['a' => null, 'b' => null, 'c' => null, 'd' => null],
                'note' => 'FI not attainable (cannot flip significance)'
            ];
        }
        
        // Get minimum
        usort($candidates, function($a, $b) {
            return $a['steps'] <=> $b['steps'];
        });
        
        $best = $candidates[0];
        $FI = $best['steps'];
        $n_mod = ($arm === 'A') ? ($a + $b) : ($c + $d);
        $FQ = $N > 0 ? $FI / $N : null;
        $MFQ = $n_mod > 0 ? $FI / $n_mod : null;
        
        // Calculate post-toggle table
        list($a_post, $b_post, $c_post, $d_post) = self::applyToggles($a, $b, $c, $d, $arm, $best['direction'], $FI);
        
        return [
            'FI' => $FI,
            'FQ' => $FQ,
            'MFQ' => $MFQ,
            'baseline_p' => $baseline_p,
            'baseline_state' => $baseline_state,
            'target_state' => $baseline_sig ? 'non-significant' : 'significant',
            'arm' => $arm,
            'direction' => $best['direction'],
            'n_mod' => $n_mod,
            'final_p' => $best['final_p'],
            'post_toggle' => [
                'a' => $a_post,
                'b' => $b_post,
                'c' => $c_post,
                'd' => $d_post
            ]
        ];
    }
    
    /**
     * Apply toggles to get post-toggle table
     * 
     * @param int $a Arm A events
     * @param int $b Arm A non-events
     * @param int $c Arm B events
     * @param int $d Arm B non-events
     * @param string $arm 'A' or 'B'
     * @param string $direction Toggle direction description
     * @param int $steps Number of toggles to apply
     * @return array [a_post, b_post, c_post, d_post]
     */
    private static function applyToggles($a, $b, $c, $d, $arm, $direction, $steps) {
        $A = $a; $B = $b; $C = $c; $D = $d;
        
        // Determine direction: 'up' or 'down'
        $dir = (strpos($direction, 'non-events → events') !== false) ? 'up' : 'down';
        
        // Apply toggles
        for ($i = 0; $i < $steps; $i++) {
            $result = self::toggleOnce($A, $B, $C, $D, $arm, $dir);
            if ($result === null) break;
            list($A, $B, $C, $D) = $result;
        }
        
        return [$A, $B, $C, $D];
    }
    
    /**
     * Choose arm using Walsh tie-breaking logic
     * 
     * @param int $a Arm A events
     * @param int $b Arm A non-events
     * @param int $c Arm B events
     * @param int $d Arm B non-events
     * @return string 'A' or 'B'
     */
    private static function chooseArm($a, $b, $c, $d) {
        // Rule 1: Arm with fewer events
        if ($a < $c) return 'A';
        if ($c < $a) return 'B';
        
        // Rule 2: If tied on events, arm with fewer total subjects
        $n_A = $a + $b;
        $n_B = $c + $d;
        if ($n_A < $n_B) return 'A';
        if ($n_B < $n_A) return 'B';
        
        // Rule 3: If still tied, choose A
        return 'A';
    }
    
    /**
     * Large-N toggle scan. Steps both directions in lockstep (up then down at
     * each step), so the first flip found is the minimum over the two
     * directions, with ties going to 'up' exactly as the usort below does.
     * Returns the same 'up' / 'down' shapes as stepsToFlip(); the direction
     * that did not win is reported as not attained, which the caller never
     * uses because only the minimum is reported.
     */
    private static function fastScan($a, $b, $c, $d, $arm, $baseline_sig, $alpha) {
        $N = $a + $b + $c + $d;
        $R = $a + $b;
        $C0 = $a + $c;
        $upMax = min(($arm === 'A') ? $b : $d, self::MAX_ITERATIONS);
        $downMax = min(($arm === 'A') ? $a : $c, self::MAX_ITERATIONS);
        $kMax = max($upMax, $downMax);
        $none = ['steps' => null, 'final_p' => null];
        $deadline = microtime(true) + self::MAX_SECONDS;

        for ($k = 1; $k <= $kMax; $k++) {
            if (($k & 0xFF) === 0 && microtime(true) > $deadline) {
                return ['timed_out' => true, 'scanned' => $k - 1, 'up' => $none, 'down' => $none];
            }
            foreach (['up' => $k, 'down' => -$k] as $dir => $s) {
                if (($dir === 'up' && $k > $upMax) || ($dir === 'down' && $k > $downMax)) {
                    continue;
                }
                // Arm A toggles move a (and the event total); arm B toggles move c only.
                $x = ($arm === 'A') ? $a + $s : $a;
                $C = $C0 + $s;
                $sig = !GlobalFragilityIndex::acceptsMargins($x, $R, $C, $N, $alpha);
                if ($sig !== $baseline_sig) {
                    $hit = ['steps' => $k, 'final_p' => GlobalFragilityIndex::fisherPMargins($x, $R, $C, $N)];
                    return [
                        'timed_out' => false, 'scanned' => $k,
                        'up' => ($dir === 'up') ? $hit : $none,
                        'down' => ($dir === 'down') ? $hit : $none,
                    ];
                }
            }
        }
        return ['timed_out' => false, 'scanned' => $kMax, 'up' => $none, 'down' => $none];
    }

    /**
     * Count steps to flip significance in one direction
     *
     * @param int $a Arm A events
     * @param int $b Arm A non-events
     * @param int $c Arm B events
     * @param int $d Arm B non-events
     * @param string $arm 'A' or 'B'
     * @param string $direction 'up' or 'down'
     * @param bool $baseline_sig Is baseline significant?
     * @param float $alpha Significance threshold
     * @return array ['steps' => int|null, 'final_p' => float]
     */
    private static function stepsToFlip($a, $b, $c, $d, $arm, $direction, $baseline_sig, $alpha) {
        $steps = 0;
        $A = $a; $B = $b; $C = $c; $D = $d;
        
        for ($i = 0; $i < self::MAX_ITERATIONS; $i++) {
            // Try one toggle
            $result = self::toggleOnce($A, $B, $C, $D, $arm, $direction);
            
            if ($result === null) {
                // Can't toggle anymore
                return ['steps' => null, 'final_p' => FisherExactTest::calculate($A, $B, $C, $D)];
            }
            
            list($A, $B, $C, $D) = $result;
            $steps++;
            
            // Check if significance flipped (strict: p = alpha is non-significant)
            $p_now = FisherExactTest::calculate($A, $B, $C, $D);
            $sig_now = ($p_now < $alpha);
            
            if ($sig_now !== $baseline_sig) {
                // Flipped!
                return ['steps' => $steps, 'final_p' => $p_now];
            }
        }
        
        // Max iterations reached
        return ['steps' => null, 'final_p' => FisherExactTest::calculate($A, $B, $C, $D)];
    }
    
    /**
     * Apply one toggle within chosen arm
     * 
     * @param int $a Arm A events
     * @param int $b Arm A non-events
     * @param int $c Arm B events
     * @param int $d Arm B non-events
     * @param string $arm 'A' or 'B'
     * @param string $direction 'up' (non-event → event) or 'down' (event → non-event)
     * @return array|null New [a,b,c,d] or null if impossible
     */
    private static function toggleOnce($a, $b, $c, $d, $arm, $direction) {
        if ($arm === 'A') {
            if ($direction === 'up') {
                // Increase events in A: b → a
                if ($b <= 0) return null;
                return [$a + 1, $b - 1, $c, $d];
            } else {
                // Decrease events in A: a → b
                if ($a <= 0) return null;
                return [$a - 1, $b + 1, $c, $d];
            }
        } else {
            // Arm B
            if ($direction === 'up') {
                // Increase events in B: d → c
                if ($d <= 0) return null;
                return [$a, $b, $c + 1, $d - 1];
            } else {
                // Decrease events in B: c → d
                if ($c <= 0) return null;
                return [$a, $b, $c - 1, $d + 1];
            }
        }
    }
}

// ========== TESTING ==========

if (php_sapi_name() === 'cli') {
    echo "Testing Fragility Index Calculator\n";
    echo "===================================\n\n";
    
    // Test case 1: Example from toolkit
    $a = 15; $b = 85; $c = 25; $d = 75;
    echo "Test Case 1: {a=$a, b=$b, c=$c, d=$d}\n";
    $result = FragilityIndex::calculate($a, $b, $c, $d);
    echo "Baseline: {a=$a, b=$b, c=$c, d=$d}, p=" . $result['baseline_p'] . "\n";
    echo "Post-toggle: {a=" . $result['post_toggle']['a'] . ", b=" . $result['post_toggle']['b'] . 
         ", c=" . $result['post_toggle']['c'] . ", d=" . $result['post_toggle']['d'] . "}, p=" . $result['final_p'] . "\n";
    echo "FI: " . $result['FI'] . ", MFQ: " . $result['MFQ'] . "\n\n";
}
?>

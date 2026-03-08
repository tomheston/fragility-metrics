<?php
/**
 * GlobalFragilityIndex.php
 * Gold-standard path-independent GFI/GFQ calculator for 2×2 tables
 * 
 * Two-phase strategy:
 * Phase 1: Greedy search (fast heuristic)
 * Phase 2: BFS exhaustive search (always runs with reasonable upper bound)
 * 
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 */

require_once 'FisherExactTest.php';

class GlobalFragilityIndex {
    
    private const ALPHA = 0.05;
    private const N_THRESHOLD = 5000;           // Skip GFI if N > this
    private const BFS_MAX_DEPTH = 20;           // BFS depth limit
    private const VERIFICATION_MAX_GFI = 8;     // Only verify if result ≤ this
    
    /**
     * Calculate GFI and GFQ for a 2×2 table
     * 
     * @param int $a Arm A events
     * @param int $b Arm A non-events
     * @param int $c Arm B events
     * @param int $d Arm B non-events
     * @param float $alpha Significance threshold (default 0.05)
     * @return array Results including GFI, GFQ, verification status, post-toggle table
     */
    public static function calculate($a, $b, $c, $d, $alpha = self::ALPHA) {
        // Validate inputs
        if ($a < 0 || $b < 0 || $c < 0 || $d < 0) {
            throw new InvalidArgumentException("All cells must be non-negative integers");
        }
        
        $N = $a + $b + $c + $d;
        
        if ($N == 0) {
            return [
                'GFI' => null,
                'GFQ' => null,
                'verified' => false,
                'method' => 'skipped',
                'baseline_p' => null,
                'final_p' => null,
                'post_toggle' => ['a' => null, 'b' => null, 'c' => null, 'd' => null],
                'note' => 'Empty table'
            ];
        }
        
        // Skip GFI for very large N
        if ($N > self::N_THRESHOLD) {
            $baseline_p = FisherExactTest::calculate($a, $b, $c, $d);
            return [
                'GFI' => null,
                'GFQ' => null,
                'verified' => false,
                'method' => 'skipped',
                'baseline_p' => $baseline_p,
                'final_p' => null,
                'post_toggle' => ['a' => null, 'b' => null, 'c' => null, 'd' => null],
                'note' => "GFI skipped (N > " . self::N_THRESHOLD . "). Use MFQ instead."
            ];
        }
        
        // Get baseline state
        $baseline_p = FisherExactTest::calculate($a, $b, $c, $d);
        $baseline_sig = ($baseline_p <= $alpha);
        $target_sig = !$baseline_sig;
        
        // Run BFS with depth limit (this is the real algorithm)
        $bfs_result = self::bfs_search($a, $b, $c, $d, $N, $target_sig, $alpha, self::BFS_MAX_DEPTH);
        
        if ($bfs_result['cost'] === null) {
            return [
                'GFI' => null,
                'GFQ' => null,
                'verified' => false,
                'method' => 'failed',
                'baseline_p' => $baseline_p,
                'final_p' => null,
                'post_toggle' => ['a' => null, 'b' => null, 'c' => null, 'd' => null],
                'note' => 'No flip found within depth ' . self::BFS_MAX_DEPTH
            ];
        }
        
        $gfi = $bfs_result['cost'];
        $gfi_table = $bfs_result['table'];
        $gfi_p = $bfs_result['p_value'];
        
        // Determine if result is verified (exact)
        if ($gfi <= self::VERIFICATION_MAX_GFI) {
            return [
                'GFI' => $gfi,
                'GFQ' => round($gfi / $N, 6),
                'verified' => true,
                'method' => 'verified',
                'baseline_p' => round($baseline_p, 6),
                'final_p' => round($gfi_p, 6),
                'post_toggle' => $gfi_table,
                'note' => "GFI exact (BFS verified)"
            ];
        } else {
            return [
                'GFI' => $gfi,
                'GFQ' => round($gfi / $N, 6),
                'verified' => false,
                'method' => 'greedy_only',
                'baseline_p' => round($baseline_p, 6),
                'final_p' => round($gfi_p, 6),
                'post_toggle' => $gfi_table,
                'note' => "GFI found but verification skipped (GFI > " . self::VERIFICATION_MAX_GFI . ")"
            ];
        }
    }
    
    /**
     * BFS search for minimal flip distance
     * 
     * @return array ['cost' => int|null, 'table' => array, 'p_value' => float]
     */
    private static function bfs_search($a, $b, $c, $d, $N, $target_sig, $alpha, $max_depth) {
        $queue = new SplQueue();
        $visited = [];
        
        $start_key = "$a,$b,$c";
        $queue->enqueue(['state' => [$a, $b, $c], 'dist' => 0]);
        $visited[$start_key] = true;
        
        while (!$queue->isEmpty()) {
            $item = $queue->dequeue();
            $state = $item['state'];
            $dist = $item['dist'];
            
            if ($dist > $max_depth) {
                continue;
            }
            
            $aa = $state[0];
            $bb = $state[1];
            $cc = $state[2];
            $dd = $N - $aa - $bb - $cc;
            
            // Check if we've flipped
            $p_now = FisherExactTest::calculate($aa, $bb, $cc, $dd);
            $sig_now = ($p_now <= $alpha);
            
            if ($sig_now === $target_sig) {
                return [
                    'cost' => $dist,
                    'table' => ['a' => $aa, 'b' => $bb, 'c' => $cc, 'd' => $dd],
                    'p_value' => $p_now
                ];
            }
            
            // Expand neighbors
            $cells = [$aa, $bb, $cc, $dd];
            for ($src = 0; $src < 4; $src++) {
                if ($cells[$src] < 1) continue;
                
                for ($dst = 0; $dst < 4; $dst++) {
                    if ($dst === $src) continue;
                    
                    $new_cells = $cells;
                    $new_cells[$src]--;
                    $new_cells[$dst]++;
                    
                    $new_state = [$new_cells[0], $new_cells[1], $new_cells[2]];
                    $new_key = implode(',', $new_state);
                    $new_dist = $dist + 1;
                    
                    if ($new_dist <= $max_depth && !isset($visited[$new_key])) {
                        $visited[$new_key] = true;
                        $queue->enqueue(['state' => $new_state, 'dist' => $new_dist]);
                    }
                }
            }
        }
        
        // No flip found within depth limit
        return ['cost' => null, 'table' => null, 'p_value' => null];
    }
}
?>

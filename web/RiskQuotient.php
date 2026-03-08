<?php
/**
 * RiskQuotient.php
 * Robustness (RQ) calculator for 2×2 tables
 * 
 * RQ measures geometric distance from independence (neutrality)
 * Formula: RQ = |ad - bc| / (N²/4)
 * Range: [0, 1] where 0 = at neutrality, 1 = maximal separation
 * 
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 */

class RiskQuotient {
    
    /**
     * Calculate Risk Quotient (RQ) for a 2×2 table
     * 
     * @param int $a Arm A events
     * @param int $b Arm A non-events
     * @param int $c Arm B events
     * @param int $d Arm B non-events
     * @return array ['RQ' => float, 'RRI' => float] or ['RQ' => null, 'note' => string]
     */
    public static function calculate($a, $b, $c, $d) {
        // Validate inputs
        if ($a < 0 || $b < 0 || $c < 0 || $d < 0) {
            throw new InvalidArgumentException("All cells must be non-negative integers");
        }
        
        $N = $a + $b + $c + $d;
        
        if ($N == 0) {
            return [
                'RQ' => null,
                'RRI' => null,
                'note' => 'Empty table'
            ];
        }
        
        // Simplified formula for 2×2 tables: RQ = |ad - bc| / (N²/4)
        $ad_minus_bc = abs(($a * $d) - ($b * $c));
        $denominator = ($N * $N) / 4.0;
        
        $RQ = $ad_minus_bc / $denominator;
        
        // Also calculate RRI for completeness (though not needed for final output)
        // RRI = (1/4) * sum(|O - E|)
        $n_A = $a + $b;
        $n_B = $c + $d;
        $events = $a + $c;
        $non_events = $b + $d;
        
        $E_a = ($n_A * $events) / $N;
        $E_b = ($n_A * $non_events) / $N;
        $E_c = ($n_B * $events) / $N;
        $E_d = ($n_B * $non_events) / $N;
        
        $RRI = (1.0/4.0) * (abs($a - $E_a) + abs($b - $E_b) + abs($c - $E_c) + abs($d - $E_d));
        
        return [
            'RQ' => round($RQ, 6),
            'RRI' => round($RRI, 6)
        ];
    }
    
}

// ========== TESTING ==========

if (php_sapi_name() === 'cli') {
    echo "Testing Risk Quotient Calculator\n";
    echo "=================================\n\n";
    
    // Test case 1: Example from toolkit
    $a = 15; $b = 85; $c = 25; $d = 75;
    echo "Test Case 1: {a=$a, b=$b, c=$c, d=$d}\n";
    $result = RiskQuotient::calculate($a, $b, $c, $d);
    echo "RQ: " . $result['RQ'] . "\n\n";

    // Test case 2: Perfect independence (RQ should be 0)
    $a = 10; $b = 10; $c = 10; $d = 10;
    echo "Test Case 2: Perfect independence {a=$a, b=$b, c=$c, d=$d}\n";
    $result = RiskQuotient::calculate($a, $b, $c, $d);
    echo "RQ: " . $result['RQ'] . " (should be 0)\n\n";

    // Test case 3: Strong association (high RQ)
    $a = 50; $b = 10; $c = 10; $d = 50;
    echo "Test Case 3: Strong association {a=$a, b=$b, c=$c, d=$d}\n";
    $result = RiskQuotient::calculate($a, $b, $c, $d);
    echo "RQ: " . $result['RQ'] . " (should be high)\n\n";
}
?>

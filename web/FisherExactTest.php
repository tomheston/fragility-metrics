<?php
/**
 * FisherExactTest.php
 * Pure PHP implementation of Fisher's exact test for 2×2 contingency tables
 * Matches scipy.stats.fisher_exact output
 *
 * Works on shared hosting with no external dependencies
 *
 * Known limitation (shared with any float implementation): for tables whose
 * exact two-sided p equals alpha to the last bit (e.g. exactly 1/20 at
 * alpha = 0.05), this implementation and scipy can land on opposite sides of
 * alpha because their summation orders differ at the ~1 ulp level. Such
 * knife-edge tables are mathematically non-significant under strict p < alpha;
 * the significance verdict either way is float noise, not an algorithm error.
 * 
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 */

class FisherExactTest {
    
    /**
     * Calculate two-sided Fisher's exact test p-value
     * Uses "sum lesser probabilities" method (same as scipy default)
     * 
     * @param int $a Cell [0,0] - Group A events
     * @param int $b Cell [0,1] - Group A non-events
     * @param int $c Cell [1,0] - Group B events
     * @param int $d Cell [1,1] - Group B non-events
     * @return float Two-sided p-value
     */
    public static function calculate($a, $b, $c, $d) {
        // Validate inputs
        if ($a < 0 || $b < 0 || $c < 0 || $d < 0) {
            throw new InvalidArgumentException("All cells must be non-negative integers");
        }
        
        $n = $a + $b + $c + $d;
        if ($n == 0) {
            return 1.0;
        }
        
        // Calculate observed probability
        $observedProb = self::hypergeometricProb($a, $b, $c, $d);
        
        // Calculate all possible tables with same margins
        $rowA = $a + $b;
        $rowB = $c + $d;
        $colEvents = $a + $c;
        
        // Minimum and maximum possible value for cell 'a'
        $minA = max(0, $colEvents - $rowB);
        $maxA = min($rowA, $colEvents);
        
        // Sum probabilities for all tables with P ≤ observed P
        // This is the "minlike" method used by scipy
        $pValue = 0.0;
        
        for ($i = $minA; $i <= $maxA; $i++) {
            $newB = $rowA - $i;
            $newC = $colEvents - $i;
            $newD = $rowB - $newC;
            
            // Skip invalid configurations
            if ($newB < 0 || $newC < 0 || $newD < 0) {
                continue;
            }
            
            $prob = self::hypergeometricProb($i, $newB, $newC, $newD);
            
            // Two-sided: include if probability ≤ observed
            // Use small tolerance for floating point comparison
            if ($prob <= $observedProb * (1.0 + 1.0e-7)) {
                $pValue += $prob;
            }
        }
        
        // Ensure p-value is in [0, 1]
        return min(1.0, max(0.0, $pValue));
    }
    
    /**
     * Calculate hypergeometric probability for 2×2 table
     * Uses log-space to prevent overflow
     * 
     * P(X = a) = C(a+c, a) * C(b+d, b) / C(n, a+b)
     * 
     * @param int $a Cell [0,0]
     * @param int $b Cell [0,1]
     * @param int $c Cell [1,0]
     * @param int $d Cell [1,1]
     * @return float Probability
     */
    private static function hypergeometricProb($a, $b, $c, $d) {
        $n = $a + $b + $c + $d;
        $rowA = $a + $b;
        $colEvents = $a + $c;
        $colNonEvents = $b + $d;
        
        // log(P) = logC(a+c, a) + logC(b+d, b) - logC(n, a+b)
        $logProb = self::logChoose($colEvents, $a) 
                 + self::logChoose($colNonEvents, $b) 
                 - self::logChoose($n, $rowA);
        
        return exp($logProb);
    }
    
    /**
     * Calculate log of binomial coefficient C(n, k) = n! / (k! * (n-k)!)
     * 
     * @param int $n Total items
     * @param int $k Items to choose
     * @return float Log of binomial coefficient
     */
    private static function logChoose($n, $k) {
        if ($k < 0 || $k > $n) {
            return -INF;
        }
        
        if ($k == 0 || $k == $n) {
            return 0.0;
        }
        
        // Optimize: use smaller of k and n-k
        $k = min($k, $n - $k);
        
        // log(C(n, k)) = log(n!) - log(k!) - log((n-k)!)
        return self::logFactorial($n) - self::logFactorial($k) - self::logFactorial($n - $k);
    }
    
    /**
     * Calculate log(n!) exactly, via an incrementally grown cumulative-sum table.
     *
     * An earlier version fell back to Stirling's approximation for n > 20. Its
     * relative error of ~1/(12n) in log space leaked into the point probabilities
     * and shifted two-sided p-values by as much as 0.18 in absolute terms, because
     * the "sum probabilities <= observed" comparison would wrongly include or drop
     * whole tables. The exact table below costs one log() per new n and removes
     * that error entirely (agreement with scipy.stats.fisher_exact is ~1e-12).
     *
     * @param int $n Non-negative integer
     * @return float Log of n!
     */
    private static function logFactorial($n) {
        if ($n < 0) {
            throw new InvalidArgumentException("Factorial undefined for negative numbers");
        }

        if ($n <= 1) {
            return 0.0;
        }

        static $cache = [0.0, 0.0];

        $have = count($cache) - 1;
        for ($k = $have + 1; $k <= $n; $k++) {
            $cache[$k] = $cache[$k - 1] + log((float)$k);
        }

        return $cache[$n];
    }
}

// ========== TESTING ==========

if (php_sapi_name() === 'cli') {
    echo "Testing Fisher's Exact Test Implementation\n";
    echo "==========================================\n\n";
    
    // Test case 1: Example from toolkit
    $a = 15; $b = 85; $c = 25; $d = 75;
    echo "Test Case 1: {a=$a, b=$b, c=$c, d=$d}\n";
    $p = FisherExactTest::calculate($a, $b, $c, $d);
    echo "p-value: " . number_format($p, 6) . "\n";
    echo "Expected: ~0.110880 (scipy.stats.fisher_exact)\n";
    echo ($p >= 0.1108 && $p <= 0.1109 ? "✓ PASS\n" : "✗ FAIL\n");
    echo "\n";
    
    // Test case 2: Perfect independence
    $a = 10; $b = 10; $c = 10; $d = 10;
    echo "Test Case 2: {a=$a, b=$b, c=$c, d=$d}\n";
    $p = FisherExactTest::calculate($a, $b, $c, $d);
    echo "p-value: " . number_format($p, 6) . "\n";
    echo "Expected: ~1.0\n";
    echo ($p >= 0.95 ? "✓ PASS\n" : "✗ FAIL\n");
    echo "\n";
    
    // Test case 3: Strong association
    $a = 50; $b = 10; $c = 10; $d = 50;
    echo "Test Case 3: {a=$a, b=$b, c=$c, d=$d}\n";
    $p = FisherExactTest::calculate($a, $b, $c, $d);
    echo "p-value: " . number_format($p, 10) . "\n";
    echo "Expected: << 0.001\n";
    echo ($p < 0.001 ? "✓ PASS\n" : "✗ FAIL\n");
    echo "\n";
    
    echo "==========================================\n";
    echo "All tests completed.\n";
}
?>

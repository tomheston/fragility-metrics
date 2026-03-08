<?php
/**
 * FragilityCalculator.php
 * Complete p-fr-nb triplet calculator for 2×2 tables
 * Now includes both FI/MFQ (Walsh-compliant) and GFI/GFQ (gold standard)
 * 
 * Combines:
 * - p: Fisher's exact test (significance)
 * - fr: FI/MFQ (Walsh-compliant) + GFI/GFQ (gold standard)
 * - nb: RQ (robustness/distance from neutrality)
 * 
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 */

require_once 'FisherExactTest.php';
require_once 'FragilityIndex.php';
require_once 'GlobalFragilityIndex.php';
require_once 'RiskQuotient.php';
require_once 'RelativeRisk.php';

class FragilityCalculator {
    
    /**
     * Calculate complete p-fr-nb triplet for 2×2 table
     * 
     * @param int $a Arm A events
     * @param int $b Arm A non-events
     * @param int $c Arm B events
     * @param int $d Arm B non-events
     * @param float $alpha Significance threshold (default 0.05)
     * @return array Complete results
     */
    public static function calculate($a, $b, $c, $d, $alpha = 0.05) {
        // Validate inputs
        if ($a < 0 || $b < 0 || $c < 0 || $d < 0) {
            throw new InvalidArgumentException("All cells must be non-negative integers");
        }
        
        $N = $a + $b + $c + $d;
        
        if ($N == 0) {
            return [
                'input' => ['a' => $a, 'b' => $b, 'c' => $c, 'd' => $d, 'N' => 0],
                'p' => null,
                'fr' => null,
                'gfi' => null,
                'nb' => null,
                'interpretation' => null,
                'note' => 'Empty table'
            ];
        }
        
        // Calculate p-value
        $p = FisherExactTest::calculate($a, $b, $c, $d);
        
        // Calculate fragility metrics (Walsh-compliant FI/MFQ)
        $fi_result = FragilityIndex::calculate($a, $b, $c, $d, $alpha);
	// Build post-FI table + post-FI p-value
	$postFI = null;

	if (isset($fi_result['post_toggle']) && is_array($fi_result['post_toggle'])) {
	    // If FragilityIndex already returns the post-toggle table, use it
	    if (isset($fi_result['post_toggle']['a'], $fi_result['post_toggle']['b'], $fi_result['post_toggle']['c'], $fi_result['post_toggle']['d'])) {
	        $postFI = [
	            'a' => (int)$fi_result['post_toggle']['a'],
	            'b' => (int)$fi_result['post_toggle']['b'],
	            'c' => (int)$fi_result['post_toggle']['c'],
	            'd' => (int)$fi_result['post_toggle']['d'],
	        ];
	    } else {
	        // Otherwise assume post_toggle carries the count of toggles (FI) and use arm+direction to build the table
	        $k = (int)($fi_result['post_toggle'] ?? 0);
	        $arm = $fi_result['arm'] ?? null;
	        $dir = $fi_result['direction'] ?? null;

        	$postFI = ['a'=>$a,'b'=>$b,'c'=>$c,'d'=>$d];

	        if ($k > 0) {
	            if ($arm === 'A') {
	                if ($dir === 'non-event->event') { $postFI['a'] += $k; $postFI['b'] -= $k; }
	                if ($dir === 'event->non-event') { $postFI['a'] -= $k; $postFI['b'] += $k; }
	            } elseif ($arm === 'B') {
	                if ($dir === 'non-event->event') { $postFI['c'] += $k; $postFI['d'] -= $k; }
	                if ($dir === 'event->non-event') { $postFI['c'] -= $k; $postFI['d'] += $k; }
	            }
	        }
	    }
	}

	if ($postFI !== null) {
	    $fi_result['post_FI'] = $postFI;
	    $fi_result['post_FI_p'] = round(
	        FisherExactTest::calculate($postFI['a'], $postFI['b'], $postFI['c'], $postFI['d']),6);
	}
        
        // Calculate GFI/GFQ (gold standard)
        $gfi_result = GlobalFragilityIndex::calculate($a, $b, $c, $d, $alpha);

	// Build post-GFI table + post-GFI p-value
	$postGFI = null;

	if (isset($gfi_result['post_toggle']) && is_array($gfi_result['post_toggle'])) {
	    // If GlobalFragilityIndex already returns the post-toggle table, use it
	    if (isset($gfi_result['post_toggle']['a'], $gfi_result['post_toggle']['b'], $gfi_result['post_toggle']['c'], $gfi_result['post_toggle']['d'])) {
	        $postGFI = [
	            'a' => (int)$gfi_result['post_toggle']['a'],
	            'b' => (int)$gfi_result['post_toggle']['b'],
	            'c' => (int)$gfi_result['post_toggle']['c'],
	            'd' => (int)$gfi_result['post_toggle']['d'],
	        ];
	    }
	}

	// If we have a post table, compute post-GFI p-value
	if ($postGFI !== null) {
	    $gfi_result['post_GFI'] = $postGFI;
	    $gfi_result['post_GFI_p'] = round(FisherExactTest::calculate($postGFI['a'], $postGFI['b'], $postGFI['c'], $postGFI['d']),6);
	}

        
        // Calculate robustness
        $rq_result = RiskQuotient::calculate($a, $b, $c, $d);

        // Calculate effect size (always 95% CI regardless of fragility alpha)
        $rr_result = RelativeRisk::calculate($a, $b, $c, $d);

        // Build result
        $result = [
            'input' => [
                'a' => $a,
                'b' => $b,
                'c' => $c,
                'd' => $d,
                'N' => $N
            ],
            'p' => [
                'value' => round($p, 6),
                'significant' => ($p <= $alpha),
                'alpha' => $alpha
            ],
            'fr' => [
                'FI' => $fi_result['FI'],
                'FQ' => $fi_result['FQ'],
                'MFQ' => $fi_result['MFQ'],
                'arm' => $fi_result['arm'],
                'direction' => $fi_result['direction'],
                'post_toggle' => $fi_result['post_toggle'],
		'post_FI' => $fi_result['post_FI'] ?? null,
    	        'post_FI_p' => $fi_result['post_FI_p'] ?? null
            ],
            'gfi' => [
                'GFI' => $gfi_result['GFI'],
                'GFQ' => $gfi_result['GFQ'],
                'verified' => $gfi_result['verified'],
                'method' => $gfi_result['method'],
                'post_toggle' => $gfi_result['post_toggle'],
                'note' => $gfi_result['note'],
		'post_GFI' => $gfi_result['post_GFI'] ?? null,
		'post_GFI_p' => $gfi_result['post_GFI_p'] ?? null
            ],
            'nb' => [
                'RQ' => $rq_result['RQ']
            ],
            'effect' => [
                'RR'         => $rr_result['RR'],
                'CI_lower'   => $rr_result['CI_lower'],
                'CI_upper'   => $rr_result['CI_upper'],
                'CI_level'   => $rr_result['CI_level'],
                'correction' => $rr_result['correction'],
                'note'       => $rr_result['note']
            ]
        ];
        
        return $result;
    }
    
}
?>

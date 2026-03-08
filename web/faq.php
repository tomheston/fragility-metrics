<?php
// faq.php
$page_title = 'FAQ';
include 'includes/header.php';
?>
<h1>Frequently Asked Questions</h1>

<b>The Problem</b><br>
A p-value of 0.04 and a p-value of 0.01 are both reported as "statistically significant" — but they can represent vastly different levels of evidence quality that p-values alone do not reveal.<br><br>

<b>The Solution</b><br>
Complete statistical evidence requires three dimensions:<br>
- Significance (p): Compatibility with null hypothesis<br>
- Fragility (fr): Classification stability<br>
- Robustness (nb): Distance from neutrality<br><br>

<b>What is the p-fr-nb triplet?</b><br>
Complete statistical evidence requires three numbers:<br>
- p: p-value (significance)<br>
- fr: fragility quotient (stability)<br>
- nb: neutrality boundary metric (robustness)<br>
Reporting only p-values provides partial evidence. Complete statistical evidence requires all three dimensions.<br><br>

<b>Why not just use p-values?</b><br>
P-values answer one question: "How compatible are the data with no effect?"<br>
They don't tell you:<br>
- How stable is this classification? (fragility)<br>
- How far from neutrality is the result? (robustness)<br>
These are orthogonal dimensions that p-values do not measure.<br><br>

<b>What is Pattern (1,1,0)?</b><br>
Pattern (1,1,0) = Clinically Meaningless:<br>
- 1: p-significant<br>
- 1: fr-fragile (unstable)<br>
- 0: nb-weak (near neutrality)<br>
This pattern occurs in 18% of significant trials (13.4-fold elevation over null expectation). P-value says "significant." Complete evidence says "don't trust this."<br><br>

<b>How is this different from clincalc.com?</b><br>
The clincalc.com fragility calculator implements a different calculation for the FI than FragilityMetrics.org. We implement the original Walsh (2014) method.<br><br>

The original <a href="https://doi.org/10.1016/j.jclinepi.2013.10.019" target="_blank">Walsh et al. (2014)</a> definition specifies: toggle outcomes in the arm with fewer events; <i>if events are tied, toggle the smaller arm</i>. This tie-breaking rule ensures the FI reflects the minimal perturbation required to flip significance.<br><br>

Our testing shows that Clincalc.com appears to do the following: when events are equal between arms, its calculator defaults to toggling the control group regardless of arm size. This can produce higher FI values when the control arm is larger than the experimental arm.<br><br>

<i>Example:</i> Control (10 events, 22 non-events, 32 total patients) vs Experimental (10 events, four non-events, 14 total patients). Per Walsh (2014): events tied → toggle smaller arm (experimental group). This implementation is utilized on FragilityMetrics.org (result: FI=1). In this case, Clincalc.com defaults to toggling the control arm when events are tied (result: FI=2). These are the results from 12/26/2025 (<a href="https://fragilitymetrics.org/examples/20251226%20Example%20from%20FragilityMetrics.png" target="_blank">FragilityMetrics</a>, <a href="https://fragilitymetrics.org/examples/20251226%20Example%20from%20ClinCalc.png" target="_blank">ClinCalc</a>).<br><br>

Additional features of FragilityMetrics.org:<br>
- We report MFQ (Modified Fragility Quotient), which normalizes FI to the toggled arm, enabling valid cross-study comparison regardless of allocation ratio<br>
- We provide the Global Fragility Index (allows both within and cross-arm toggles; GFI = minimum number of toggles to flip significance) and Global Fragility Quotient (GFQ = GFI/N). See documentation.<br/>
- We provide the complete p-fr-nb triplet, not fragility alone<br>
- We report RQ (robustness), which clincalc.com does not calculate<br>
- We automatically calculate the "reverse" FI, i.e. if the baseline p-value is non-significant, the FI is the number of toggles required to cross the p=0.05 threshold to become significant.<br/>
- Open source code available for verification<br>
- We encourage testing of our software and have made it open source (CC-BY 4.0). Please report any bugs on the <a href="https://github.com/tomheston/fragility-metrics/discussions/">GitHub discussion board</a>.<br/><br/>

<b>Is this calculator free?</b><br>
Yes, completely free. No registration required. Open source implementation.<br><br>

<b>What outcome types are supported?</b><br>
- Currently supported: Binary (2×2): MFQ + RQ (this calculator)<br>
- See the <a href="https://github.com/tomheston/fragility-metrics" target="_blank">Fragility Metrics Toolkit</a> for additional Python calculators covering continuous, diagnostic, ordinal, survival, and correlation outcomes.<br><br>

<?php include 'includes/footer.php'; ?>
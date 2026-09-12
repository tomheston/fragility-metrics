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
Reporting only p-values provides partial evidence. Complete statistical evidence requires all three dimensions. Clinical decisions also need the absolute effect size: the triplet plus effect size is complete evidence.<br><br>

<b>Why not just use p-values?</b><br>
P-values answer one question: "How compatible are the data with no effect?"<br>
They don't tell you:<br>
- How stable is this classification? (fragility)<br>
- How far from neutrality is the result? (robustness)<br>
These are complementary dimensions that p-values do not measure.<br><br>

<b>What is Pattern (1,1,0)?</b><br>
Pattern (1,1,0) = thin evidence, consistent with a trivial effect:<br>
- 1: p-significant<br>
- 1: fr-unstable<br>
- 0: nb-near (close to neutrality)<br>
In a study of 129 published two-arm, binary-outcome trials, 24 of the 77 significant trials (31.2%) showed this pattern, defined there as p ≤ 0.05, MFQ ≤ 0.10, and RQ &lt; 0.075. Simulated trials showed it at similar rates (24–32%) whether the true effect was strong, moderate, or absent, so the pattern marks a precarious boundary zone rather than identifying false positives (<a href="https://doi.org/10.7759/cureus.100494" target="_blank">Heston, Cureus, 2025</a>). The p-value says "significant." Complete statistical evidence says "remain skeptical": treat the effect as clinically negligible unless it is replicated with an nb farther from neutrality.<br><br>

<b>How is this different from clincalc.com?</b><br>
The clincalc.com fragility calculator implements a different calculation for the FI than FragilityMetrics.org. We implement the Heston Fragility Index, a modification of the original Walsh (2014) method.<br><br>

The original <a href="https://doi.org/10.1016/j.jclinepi.2013.10.019" target="_blank">Walsh et al. (2014)</a> method converts non-events to events in the arm with fewer events until significance is lost, and it does not say what to do when event counts are tied. The Heston FI adds an explicit tie rule, <i>if events are tied, toggle the smaller arm</i>; it is also bidirectional, defined for nonsignificant results, and defined as a true minimum.<br><br>

Our testing shows that Clincalc.com appears to do the following: when events are equal between arms, its calculator defaults to toggling the control group regardless of arm size. This can produce higher FI values when the control arm is larger than the experimental arm.<br><br>

<i>Example:</i> Control (10 events, 22 non-events, 32 total patients) vs Experimental (10 events, four non-events, 14 total patients). Per the Heston FI tie rule: events tied → toggle the smaller arm (experimental group). This implementation is used on FragilityMetrics.org (result: FI=1). In this case, Clincalc.com defaults to toggling the control arm when events are tied (result: FI=2). These are the results from 12/26/2025 (<a href="https://fragilitymetrics.org/examples/20251226%20Example%20from%20FragilityMetrics.png" target="_blank">FragilityMetrics</a>, <a href="https://fragilitymetrics.org/examples/20251226%20Example%20from%20ClinCalc.png" target="_blank">ClinCalc</a>).<br><br>

Additional features of FragilityMetrics.org:<br>
- We report MFQ (Modified-Arm Fragility Quotient), which is allocation-fair: it divides FI by the size of the arm actually toggled, minimizing distortion from unequal allocation<br>
- We provide the <a href="global-fragility-index.php">Global Fragility Index</a> (the minimum number of patients moved between any cells of the table, total N fixed, to flip significance) and the Global Fragility Quotient (GFQ = GFI/N), the recommended fragility metric for 2×2 tables<br/>
- We provide the complete p-fr-nb triplet, not fragility alone<br>
- We report RQ (Risk Quotient), the robustness (nb) metric, and NDI (Neutrality Distance Index), its count form; clincalc.com calculates neither<br>
- We report the absolute effect size (risk difference with 95% CI, and NNT) alongside the relative risk<br>
- The FI is bidirectional: if the baseline p-value is nonsignificant, the FI is the number of toggles required to cross the p = 0.05 threshold and become significant, so no separate "reverse" FI is needed.<br/>
- Open source code available for verification<br>
- We encourage testing of our software and have made it open source (CC-BY 4.0). Please report any bugs on the <a href="https://github.com/tomheston/fragility-metrics/discussions/">GitHub discussion board</a>.<br/><br/>

<b>Is this calculator free?</b><br>
Yes, completely free. No registration required. Open source implementation.<br><br>

<b>What outcome types are supported?</b><br>
- Binary (2×2) on this site: GFQ + RQ, with FI, FQ, MFQ, GFI, NDI, and the absolute effect size also reported (<a href="index.php">2×2 calculator</a>)<br>
- Survival on this site (hazard ratio with confidence interval): SFQ + SRQ (<a href="survival.php">survival calculator</a>)<br>
- See the <a href="https://github.com/tomheston/fragility-metrics" target="_blank">Fragility Metrics Toolkit</a> for Python notebooks covering continuous, multi-group (ANOVA), r×c, fixed-margin 2×2, diagnostic, ordinal, survival, correlation, and single-arm benchmark outcomes.<br><br>

<?php include 'includes/footer.php'; ?>
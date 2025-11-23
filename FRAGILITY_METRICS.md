# FRAGILITY METRICS v9.6

## The Fragility-Robustness Framework: Unified Metrics for Statistical Evidence Quality Across Discrete and Continuous Outcome Types  
**Thomas F. Heston**  
*Department of Family Medicine, University of Washington, Seattle, WA, USA*  
*Department of Medical Education and Clinical Sciences, Washington State University, Spokane, WA, USA*  
**ORCID:** [0000-0002-5655-2512](https://orcid.org/0000-0002-5655-2512)  
**Version:** 9.6  
**Date:** November 22, 2025  

---

## Abstract
Statistical evidence quality assessment requires metrics beyond p-values. This reference establishes a complete, model-free framework with two orthogonal dimensions scaled identically 0–1:

**Fragility (fr)** measures the proportion of relevant data (or SE-scale shift) required to flip significance classification, with primary metrics MFQ (recommended default for 2×2 binary outcomes), GFQ (gold standard for r×c and multinomial), DFQ (diagnostic benchmarks), BFQ (single-arm benchmarks), CFQ (continuous outcomes via Welch t-geometry), and PFI (fixed-margin designs).

**Robustness (nb)** quantifies geometric distance from therapeutic neutrality via the Neutrality Boundary Framework (NBF), with primary metrics RQ (independent-sample binary/multinomial), MHQ (matched-pair/fixed-margin designs), DNB (diagnostic odds ratio), Proportion-NBF (single-arm benchmarks), MeCI (continuous means), DTI (correlation), Agreement-NBF (raw agreement), and ANOVAη² (multi-group). 

All metrics use only observed counts or published summary statistics; no raw data, simulation, reconstruction, or covariate models permitted. Fragility always measures classification stability (high fr is desirable when the p-value supports the claim). Robustness interpretation is claim-dependent: high nb supports "effect exists" claims, undermines "no effect" claims.

This document finalizes the integration of continuous-outcome measures (CFQ + MeCI), single-arm benchmark measures (BFQ + Proportion-NBF), and the unified fr/nb notation, providing a complete evidence-quality system applicable to every standard study design with minimal assumptions. **The p–fr–nb triplet has the potential to replace “statistically significant” with “convincingly effective.”**

**Keywords**: statistical fragility, statistical robustness, neutrality boundary framework, fragility index, continuous fragility quotient, evidence quality metrics, p-value limitations, model-free statistics

---
## Executive Summary

> **Whereas p < 0.05 establishes statistical significance, a concordant p–fr–nb triplet (low p + high fr + high nb) establishes convincing, replication-ready evidence of a genuine treatment effect. The p–fr–nb framework has the potential to become the new gold standard for declaring a finding believable.**

### The Statistical Evidence Framework
Modern evidence assessment rests on three orthogonal statistical dimensions plus clinical effect size:

| Secret question from every skeptical reader                                   | p alone answers | p–fr–nb triplet answers                                      |
|-------------------------------------------------------------------------------|-----------------|--------------------------------------------------------------|
| Significant?                                                                  | Yes             | **Yes** (p)                                                  |
| Flippable by a few outcome changes or dropouts?                               | ?               | **Yes** — fr quantifies the fragility of the p-value  |
| Real separation from zero, or just lucky sampling noise that barely hit p<0.05? | ?               | **Yes** — nb quantifies distance from the neutrality boundary |

Only when **all three dimensions align strongly** (low p + high fr + high nb) do we have truly compelling, replication-ready evidence that an intervention works.

### The Three Statistical Dimensions
#### Statistical Significance (p)
- **Question**: How compatible are the data with the null?
- **Scale**: 0 to 1
- **Interpretation**: Lower p = stronger evidence against the null

#### Fragility (fr ∈ [0,1])
- **Question**: What proportion of the relevant data must change to flip the significance classification?
- **Unified metric**: **fr** (all primary fragility quotients output fr directly)

#### Robustness (nb ∈ [0,1])
- **Question**: How far is the result from the neutrality boundary?
- **Unified metric**: **nb** (all NBF metrics output nb directly)

### Key Insight: Interpretation Depends on Your Claim
**Claiming an effect exists (p ≤ 0.05):**  
Prefer **low p**, **high fr**, **high nb**. Only when all three align strongly do we have truly compelling evidence.

**Claiming no effect (p > 0.05):**  
Prefer **high p**, **high fr**, **low nb**.

The measurements are universal; the interpretation is claim-dependent.

In most common trial designs, the framework provides **paired metrics** (both fr and nb) so stability and distance from neutrality can be evaluated together (e.g., MFQ + RQ, DFQ + DNB, CFQ + MeCI, PFI + MHQ). In a few designs—correlations, agreement without ground truth, and multi-group ANOVA—only **robustness (nb)** is defined, because any FI-style fragility measure would require arbitrary, non-unique perturbation rules or additional modeling assumptions that break the model-free principle of this framework.

---

## Part I: Conceptual Framework

### The Two Dimensions of Evidence Quality (Both 0–1)

#### Fragility (Proportion-based or Scale-based Stability)

* **Concept**: Quantifies the stability of a significance classification on a scale of 0 (fragile) to 1 (stable).
* **Unified notation**: **fr ∈ [0,1]**
* **Measures**: Proportion of the sample (binary/diagnostic) or proportion of an SE-scale shift (continuous) required to flip the p-value classification.
* **Scale**: 0 to 1 (quotients)
* **Primary metrics**: FQ, MFQ, GFQ, DFQ, BFQ, PFI, CFQ → all compute **fr**
* **Secondary metrics**: FI, SFI, GFI, DFI, **CFS** (raw counts/units)
* **Interpretation**: Lower fr = more fragile; higher fr = more stable.

#### Robustness (Geometric Distance)

* **Concept**: Quantifies the distance from therapeutic neutrality on a scale of 0 (neutral) to 1 (robust separation).
* **Unified notation**: **nb ∈ [0,1]**
* **Measures**: Geometric distance from the neutrality boundary (no effect).
* **Scale**: 0 to 1 (NBF normalized)
* **Primary metrics**: RQ (independent-sample binary/multinomial), MHQ (matched-pair/fixed-margin designs), DNB, Proportion-NBF, MeCI, DTI, Agreement-NBF, ANOVAη² → all compute **nb**
* **Interpretation**: Lower nb = near neutrality; higher nb = far from neutrality.  

**Note**: Some designs have robustness-only metrics in this framework: correlations (DTI), agreement without ground truth (Agreement-NBF), and multi-group ANOVA (ANOVAη²). In these settings, no model-free fragility quotient is defined because: a) for correlations, any FI-style metric would depend on how individual (x, y) pairs are moved in the scatter cloud, which cannot be uniquely determined from (r, n) without introducing modeling assumptions; b) for agreement without ground truth, there is no reference standard to define success/failure toggles; and c) for multi-group ANOVA, the F-geometry is fully summarized by η² and any attempt to count “unit changes” that flip significance would again require raw data or distributional assumptions. Even when individual-level data are available, FI-style toggling for these designs would require arbitrary, non-unique perturbation rules and therefore would not yield a stable, interpretable fragility measure; extending fragility indices to these settings is not recommended, as the p-value, effect size, and NBF metric already fully characterize the data without introducing strong, non-verifiable modeling assumptions.

---

### The Interpretation Layer

**Measurement ≠ Interpretation**

Robustness (nb) has opposite implications depending on the claim being made:

| Claim                          | High Robustness (far from neutral) | Low Robustness (near neutral) |
| ------------------------------ | ---------------------------------- | ----------------------------- |
| **"Effect exists"** (p ≤ 0.05) | Supports the claim                 | Undermines the claim          |
| **"No effect"** (p > 0.05)     | Undermines the claim               | Supports the claim            |

Fragility (fr) behaves differently:

* **Low fragility** (fr near 0): unstable significance classification
* **High fragility** (fr near 1): stable significance classification

Stability always matters; whether it helps or hurts depends entirely on the claim.

---

## Part II: Quick Reference Table

| Metric            | Type       | Scale | Primary/Secondary     | Formula (core)                                                  | Purpose                                           |
|-------------------|------------|-------|-----------------------|-----------------------------------------------------------------|---------------------------------------------------|
| **FQ**            | Fragility  | 0–1   | LEGACY                | FI / N                                                          | Proportion to flip (classic, total N)             |
| **MFQ**           | Fragility  | 0–1   | PRIMARY               | FI / n_mod                                                      | Proportion to flip (arm-specific)                 |
| **GFQ**           | Fragility  | 0–1   | PRIMARY               | GFI / N                                                         | Proportion to flip (global, r×c)                  |
| **DFQ**           | Fragility  | 0–1   | PRIMARY               | DFI / n_relevant                                                | Proportion to flip (diagnostic)                   |
| **BFQ**           | Fragility  | 0–1   | PRIMARY               | BFI / n_relevant (n_relevant = n)                               | Proportion to flip (single-arm vs benchmark)      |
| **CFQ**           | Fragility  | 0–1   | PRIMARY               | \|\|T\| − t\*\| / (1 + \|\|T\| − t\*\|)                         | SE-scaled distance to p = 0.05 (continuous)       |
| **PFI**           | Fragility  | 0–1   | PRIMARY               | 4 × \|x\| / N (x = McNemar path shift)                          | Fixed-margin fragility (matched designs)          |
| **RQ**            | Robustness | 0–1   | PRIMARY               | RRI / (N/k); 2×2 balanced: \|ad − bc\| / (N²/4)                 | Distance from independence                        |
| **MHQ**           | Robustness | 0–1   | PRIMARY (matched)     | \|b − c\| / (b + c) or 0 if b + c = 0                           | Distance from marginal homogeneity                |
| **DNB**           | Robustness | 0–1   | PRIMARY               | \|ln(DOR)\| / (\|ln(DOR)\| + SE)                                | Diagnostic distance from neutrality               |
| **Proportion-NBF**| Robustness | 0–1   | PRIMARY               | \|p̂ − p₀\| / (\|p̂ − p₀\| + √[p₀(1 − p₀)/n_relevant])          | Single-arm distance from benchmark                |
| **MeCI**          | Robustness | 0–1   | PRIMARY               | \|T\| / (1 + \|T\|) where T is Welch t-statistic                | Continuous distance from neutrality               |
| **DTI**           | Robustness | 0–1   | PRIMARY               | \|atanh(r)\| / (1 + \|atanh(r)\|)                               | Correlation distance from independence            |
| **Agreement-NBF** | Robustness | 0–1   | PRIMARY               | \|p̂ − 0.5\| / (\|p̂ − 0.5\| + 0.5/√n)                           | Agreement distance from chance                    |
| **ANOVAη²**       | Robustness | 0–1   | PRIMARY               | df_b·F / (df_b·F + df_w)                                        | Multi-group distance from equality                |
| **FI**            | Count      | 0–∞   | Secondary             | Toggle count (classic)                                          | Raw fragility count (binary)                      |
| **SFI**           | Count      | 0–∞   | Secondary             | Toggle count (standardized)                                     | Label-invariant count                             |
| **GFI**           | Count      | 0–∞   | Secondary             | Move count (global)                                             | Path-independent count                            |
| **DFI**           | Count      | 0–∞   | Secondary             | Toggle count vs benchmark                                       | Diagnostic count                                  |
| **CFS**           | Distance   | 0–∞   | Secondary             | \|\|T\| − t\*\|                                                 | SE-unit distance to p = 0.05 (continuous)         |
| **RRI**           | Distance   | 0–∞   | Secondary             | (1/k) Σ\|O − E\|                                                | Raw distance from independence                    |
| **RI**            | Scaling    | >1    | Secondary             | Factor k to flip                                                | Sample size multiplier                            |
| **UFI**           | Unit       | >0    | LEGACY                | N/(n₁n₂) or 1/max(n₁, n₂) or 1/N                                | Step-size definitions (fixed-margin unit size)    |

t* is the critical value from the t-distribution.  

## Part III: PRIMARY FRAGILITY METRICS 

### Core Concept

Fragility quotients measure the proportion of the sample (binary/diagnostic) or the proportion of SE-scale movement (continuous) required to flip statistical significance. All primary fragility metrics range from 0 to 1.

### 3.1 FQ — Fragility Quotient

**Application**: Legacy metric for any binary outcome 2×2 table (independent samples) using total N denominator.
**Definition**: Proportion of the total sample that must toggle to flip significance  
**Formula**: FQ = FI/N  
**Range**: 0 to 1  
**Interpretation**: For FQ, fr = FQ. For example, fr = 0.02 means 2% of sample outcomes must change to flip statistical significance.  
**Advantages**: use for historical comparison with studies that used FQ
**Base metric**: FI (classic fragility index)  
**NBF pair**: RQ
**Note**: FQ is a legacy metric and is not recommended as the primary fr metric for 2-arm binary outcome studies; the MFQ is preferred because it enables cross-study comparisons through label-invariance and proper handling of allocation imbalance.  

### 3.2 MFQ — Modified Fragility Quotient ⭐

**Application**: Recommended default for independent-sample 2×2 binary outcome trials utilizing the FI for fragility counts (any allocation ratio)
**Definition**: Proportion of the arm that was actually modified in the classic fragility index procedure required to flip statistical significance.  
**Formula**: MFQ = FI / n_mod, where n_mod = sample size of the arm subjected to toggling in the standard FI calculation (i.e., the arm with fewer events; if tied, the smaller arm).  
**Range**: 0 to 1  
**Interpretation**: fr = MFQ. Example: fr = 0.05 means 5% of patients in the arm that was toggled would need to switch outcome to flip significance.  
**Advantages**: Label-invariant, allocation-fair  
**Base metric**: Classic FI (Walsh et al. 2014 procedure)  
**NBF pair**: RQ
**Note**: This is now the recommended default fragility quotient for 2×2 tables because it (1) uses the universally recognized FI count, and (2) remains allocation-fair by denominating against the arm that actually needs to change. Use MFQ + RQ as the standard pair for binary 2×2 outcomes.  
  
### 3.3 GFQ — Global Fragility Quotient ⭐

**Application**: Gold standard for any r×c contingency table or multinomial outcomes (independent samples).
**Definition**: Proportion of sample involved in minimal cell moves  
**Formula**: GFQ = GFI/N  
**Range**: 0 to 1  
**Interpretation**: For GFQ, fr = GFQ (e.g., fr = 0.03 means 3% of the sample must be reallocated to flip statistical significance).  
**Advantages**: Path-independent, applies to any r×c table  
**Base metric**: GFI (global fragility index)  
**NBF pair**: RQ
**Note**: Considered the gold standard for binary and multinomial fragility assessment.  For large sample sizes (≈5000+), computing GFI becomes computationally intractable, and MFQ becomes the practical metric of choice for two-arm binary outcome studies.  The GFQ complements RQ, which measures robustness (distance from independence). Both should be reported together for multi-arm, multi-outcome studies.   

### 3.4 DFQ — Diagnostic Fragility Quotient ⭐

**Application**: Diagnostic metrics from full 2×2 table (TP, FN, FP, TN) with ground truth.  
**Definition**: Proportion of the relevant subset of observations that must toggle to change the diagnostic classification (below vs not below benchmark) using a one-sided exact binomial test against p₀, paired with DNB.  
**Formula**: DFQ = DFI / n_relevant, where n_relevant depends on the metric:  
- Sensitivity: n_relevant = TP + FN (disease+)  
- Specificity: n_relevant = TN + FP (disease−)  
- PPV: n_relevant = TP + FP (test+)  
- NPV: n_relevant = TN + FN (test−)  
- ACCURACY (agreement with truth): n_relevant = TP + FN + FP + TN = N  
**Range**: 0 to 1  
**Interpretation**: For DFQ, **fr = DFQ** (e.g., fr = 0.06 means 6% of the relevant cases must change to flip the benchmark classification).  
**Advantages**: Correctly targets the subset of observations that determine each diagnostic metric, and uses a one-sided exact test appropriate for benchmark comparisons.  
**Base metric**: DFI (diagnostic fragility index)  
**NBF pair**: DNB  
**Note**: Uses a one-sided exact binomial test against the investigator-specified benchmark p₀:
H₀: p = p₀ vs H₁: p < p₀ (for “below benchmark” diagnostic standards).  
For agreement against chance, p₀ = 0.5 is standard. For PPV/NPV/ACCURACY, data should be normalized to 50% disease prevalence (see §3.4.1). DFQ assesses fragility (stability of the benchmark classification) for diagnostic metrics derived from a full 2×2 table with ground truth. It complements DNB, which measures robustness (distance of the diagnostic odds ratio from neutrality). Both should be reported together for diagnostic accuracy studies. **For single-arm benchmark analyses based only on (k, n, p₀), use BFQ + Proportion-NBF instead of DFQ + DNB.**

#### 3.4.1 Prevalence Normalization for PPV/NPV/ACCURACY (Standard Procedure)

**Rationale**: We cannot compare a test's PPV/NPV/ACCURACY across studies unless the underlying prevalence is normalized. Since clinical testing is most helpful when diagnostic uncertainty is high (pretest ≈ 50%), prevalence is set to 50%.  
**Procedure**:
- Identify minority class: min(n_disease+, n_disease−)  
- Scale the majority class to match the minority, preserving its internal ratio:  
  - If scaling disease−: maintain FP:TN  
  - If scaling disease+: maintain TP:FN  
- Round conservatively: TP/TN down, FP/FN up  
- Calculate DFI on the normalized table  
- DFQ = DFI / n_relevant_normalized

### 3.5 BFQ — Benchmark Fragility Quotient ⭐

**Application**: Single-arm benchmark tests and agreement vs benchmark when only (k, n, p₀) are available. Typical use cases:  
a) Single-arm response rate vs a benchmark proportion p₀  
b) Single-rater agreement vs chance (p₀ = 0.5) or another target benchmark when the full TP/FN/FP/TN table is not available  
**Definition**: Proportion of observations in a single-arm proportion that must toggle (success ↔ failure) to change the benchmark classification under a one-sided exact binomial test.  
**Formula**: BFQ = BFI / n_relevant, where:  
- n_relevant = n (denominator of the observed proportion)  
- BFI = minimal number of success/failure toggles that flips the one-sided exact binomial test classification at the benchmark proportion p₀  
**Test**: One-sided exact binomial test vs benchmark p₀  
- H₀: p = p₀  
- H₁: p > p₀ (for “above benchmark” claims; direction may be reversed if the benchmark is framed as a maximum allowable rate).  
**Range**: 0 to 1  
**Interpretation**: fr = BFQ (e.g., fr = 0.08 means 8% of the observations in the single-arm proportion must change to flip the benchmark decision).  
**Advantages**:  
- Establishes the fragility of a single-arm test, giving a success rate vs the benchmark.  
- Applies when only (k, n, p₀) are available (no full diagnostic 2×2 table).  
- Mirrors the DFQ logic but in a single-arm setting.  
- Uses the same exact binomial test that defines the underlying benchmark decision.  
**Base metric**: BFI (Benchmark Fragility Index) = minimal number of success/failure toggles required to change the benchmark classification under the specified one-sided exact binomial test.  
**NBF pair**: Proportion-NBF  
**Note**: Use BFQ when the analysis is “proportion/agreement vs a benchmark” based only on (k, n, p₀). For full diagnostic accuracy studies with TP/FN/FP/TN and a diagnostic odds ratio, use **DFQ + DNB** instead.

### 3.6 PFI — Percent Fragility Index (updated November 2025) ⭐

**Application**: matched binary 2x2 trials  
**Definition**: Proportion of the maximal admissible fixed-margin shift required to flip the McNemar significance classification in a matched-pair or fixed-margin 2×2 design.  
**Formula**: PFI = 4 × |x| / N, where x is the smallest continuous shift along the fixed-margin path (a+x, b−x, c−x, d+x) that changes the two-sided McNemar χ² p-value across the α = 0.05 boundary.  
**Range**: 0 to 1  
**Interpretation**: For PFI, fr = PFI (e.g., fr = 0.10 means that 10% of the theoretical maximum fixed-margin cell movement is sufficient to reverse the McNemar significance decision).  
**Advantages**: a) directly measures stability of the correct null hypothesis for matched designs (marginal homogeneity); b) uses the same significance test (McNemar χ²) for both baseline p-value and fragility path → eliminates test mismatch; c) remains perfectly scaled and orthogonal to MHQ; d) provides intuitive interpretation in terms of discordant pair changes.  
**Base metric**: x (minimal fixed-margin perturbation along the McNemar path)  
**NBF pair**: MHQ  
**Note**: Use exclusively for matched-pair, crossover, or any 2×2 table in which both row and column margins are fixed by design (McNemar test is the appropriate significance test). For independent-sample 2×2 designs, use **GFI/GFQ** (gold standard, path-independent) or **FI/MFQ** (fast approximation when N ≳ 5000).  
- Boundary-limited cases: When the feasible shift range along the fixed-margin path is exhausted before the McNemar p-value can cross α = 0.05 (e.g., the table is already at maximal directional imbalance or perfect concordance), no admissible perturbation flips significance. In these cases, PFI returns the theoretical maximum feasible proportion along the fixed-margin path (which may or may not be near 1.0) and is flagged "boundary-limited". This indicates maximal stability of the current significance classification under the fixed-margin constraint – the result is unflippable. A high PFI + boundary-limited flag is therefore the strongest possible evidence that the observed p-value classification is stable; a low PFI that is boundary-limited maintains the previously given interpretation of fragility.  
- PFI values are not numerically comparable to GFQ or MFQ because they assess fragility under different null hypotheses (marginal homogeneity vs. independence) and different data-generating processes.

### 3.7 CFQ — Continuous Fragility Quotient ⭐

**Application**: trials comparing two continuous outcomes where m₁, m₂, s₁, s₂, n₁, n₂ are all known.
**Definition**: Proportion of an SE-scaled shift in the estimated mean difference required to flip statistical significance in a two-sample continuous comparison (Welch t-test).
**Formula**: Let
m₁, m₂ = observed group means
s₁, s₂ = observed standard deviations
n₁, n₂ = group sample sizes.
v₁ = s₁²/n₁ (variance of mean 1),  
v₂ = s₂²/n₂ (variance of mean 2),  
SE_diff = √(v₁ + v₂) (this is the Continuous Fragility Unit, CFU),  
θ̂ = m₁ − m₂ (observed mean difference),  
T = θ̂ / SE_diff (observed Welch t-statistic),  
df = (v₁ + v₂)² / (v₁²/(n₁−1) + v₂²/(n₂−1)),  (Welch-Satterthwaite degrees of freedom),  
t* = t₀․₉₇₅,df (two-sided critical value at α = 0.05).  
Then:
Continuous Fragility Score (distance in SE units to the p = 0.05 boundary): CFS = | |T| − t* |.
Continuous Fragility Quotient: CFQ = CFS / (1 + CFS).
**Range**: 0 to 1.  
**Interpretation**: fr = CFQ. For example, fr = 0.12 means the observed t-statistic lies relatively close to the p = 0.05 boundary on the CFQ scale; smaller values indicate a more fragile significance classification, larger values a more stable one.  
**Advantages**: Works directly from reported summary statistics (m₁, m₂, s₁, s₂, n₁, n₂). No raw data required. No simulated data or distributional reconstruction. Correctly respects Welch's variance structure and degrees of freedom. Provides a continuous-outcome analogue of MFQ/GFQ.  
**Base metric**: CFS = continuous fragility score = (SE-unit distance between |T| and t*).  
**NBF pair**: MeCI  
**Note**: CFQ assesses fragility (stability of significance). It complements MeCI, which assesses robustness (distance from neutrality). Both should be reported for continuous outcomes.  

## Part IV: Primary Robustness Metrics

### Core Concept: Neutrality Boundary Framework (NBF)

**NBF metrics** quantify **geometric distance from neutrality**—where treatment equals control (e.g. RR=1, Δ=0, r=0, DOR=1).  
**General NBF Formula**: NBF = |T − T₀| / (|T − T₀| + S), where T = statistic, T₀ = neutral value, S = scale parameter.  
**Universal property**: all NBF metrics → [0,1].  
**Interpretation**: 0 = at neutrality, 1 = maximally separated.  

### 4.1 RQ — Risk Quotient ⭐

**Application**: Independent-sample binary or multinomial outcome tables (2×2 or r×c) assessing separation from independence (treatment vs control or multi-arm studies).
**Definition**: NBF-based robustness metric measuring geometric distance from independence in binary or multinomial outcome tables.  
**Formula (general)**:   
RRI = (1/k) Σ|O − E|,  
RQ = RRI / (N/k),  
Where k is the number of independent cells in the table, O are observed counts, and E are expected counts under independence.  
**Special-case shortcut (2×2 with balanced column margins)**: RQ = |ad − bc| / (N²/4). (Does not hold with unequal margins).  
**Range**: 0 to 1.  
**Interpretation**: nb = RQ. For example, nb = 0.20 means the data are moderately separated from independence.  
**Neutrality**: Independence of variables (e.g. ad = bc for 2×2)  
**Pairs with**: FQ, MFQ, GFQ  
**Note**: Standard robustness measure for independent-sample binary and multinomial outcomes.  

### 4.2 DNB — Diagnostic Neutrality Boundary ⭐

**Application**: Diagnostic accuracy studies with full 2×2 tables (TP, FN, FP, TN) and ground truth, including sensitivity, specificity, PPV, NPV, and accuracy (after prevalence normalization when needed).  
**Definition**: NBF-based robustness metric measuring the diagnostic odds ratio's (DOR) distance from neutrality.  
**Formula**: DNB = |ln(DOR)| / (|ln(DOR)| + SE(ln(DOR)))  
where:  
- DOR = (TP × TN) / (FP × FN)  
- SE(ln(DOR)) = √(1/TP + 1/FN + 1/FP + 1/TN)  

**Range**: 0 to 1.  
**Interpretation**: For DNB, nb = DNB. For example, nb = 0.35 means the diagnostic odds ratio is clearly separated from no-discrimination.  
**Neutrality**: DOR = 1 (test no better than chance).  
**Pairs with**: DFQ  
**Note**: Primary robustness measure for diagnostic tests using a full 2×2 table. Apply after prevalence normalization for PPV/NPV and accuracy where appropriate (see §3.4.1). **For single-arm benchmark analyses based only on (k, n, p₀), use Proportion-NBF instead of DNB.**

### 4.3 MeCI — Meaningful Change Index ⭐

**Application**: Two-group independent continuous outcome studies (e.g., trials reporting group means, standard deviations, and sample sizes for each arm).  
**Definition**: NBF-based robustness metric measuring distance from neutrality (equal means) for continuous outcomes.  
**Formula**: Let μ₁, μ₂ be the observed group means, s₁, s₂ their standard deviations, and n₁, n₂ the sample sizes.  
Calculate the Welch t-statistic:
- SE₁ = s₁/√n₁ (standard error of mean 1)
- SE₂ = s₂/√n₂ (standard error of mean 2)  
- SE_diff = √(SE₁² + SE₂²) (standard error of the difference)
- T = (μ₁ − μ₂) / SE_diff (Welch t-statistic)
Then:  
**MeCI = |T| / (1 + |T|)**
**Range**: 0 to 1.  
**Interpretation**: For MeCI, nb = MeCI. For example, nb = 0.15 indicates the group means are modestly separated relative to the pooled variability; nb = 0.80 indicates the means are far from equality.  
**Neutrality**: μ₁ = μ₂ (T = 0, no difference between groups)  
**Pairs with**: CFQ  
**Note**: Primary robustness metric for continuous outcomes; uses only published summary statistics and complements CFQ, which measures fragility of the corresponding significance classification. The t-statistic is already standardized (incorporates both mean difference and sampling variability), so the NBF scale parameter S = 1, making MeCI directly parallel to DTI for correlations.

### 4.4 DTI — Distance to Independence ⭐

**Application**: Correlation/association studies where the primary result is a correlation coefficient (e.g., Pearson or Spearman r).  
**Definition**: NBF-based robustness metric for correlations.  
**Formula**: DTI = |atanh(r)| / (1 + |atanh(r)|)  
**Range**: 0 to 1.  
**Interpretation**: For DTI, nb = DTI. For example, nb = 0.25 means the correlation is clearly separated from zero.  
**Neutrality**: r = 0  
**Pairs with**: (no fragility quotient currently defined; DTI is robustness-only for correlation studies)  
**Note**: Primary robustness measure for correlation studies.

### 4.5 Agreement-NBF ⭐

**Application**: Inter-rater agreement studies without ground truth, where the main summary is the raw proportion of agreement between raters.  
**Definition**: NBF-based robustness metric for inter-rater agreement without ground truth.  
**Formula**: Agreement-NBF = |p̂ − 0.5| / (|p̂ − 0.5| + 0.5/√n) , where p̂ = observed agreement proportion  
**Range**: 0 to 1.  
**Interpretation**: For Agreement-NBF, nb = Agreement-NBF. For example, nb = 0.10 means agreement is near chance levels.  
**Neutrality**: p̂ = 0.5 (chance agreement for dichotomous ratings)  
**Pairs with**: (no fragility quotient currently defined; Agreement-NBF is robustness-only for agreement without ground truth)  
**Note**: Uses raw agreement, NOT Cohen's kappa. For agreement with ground truth, use diagnostic metrics (DFQ/DNB).

### 4.6 ANOVAη²  ⭐

**Application**: Multi-group continuous outcome comparisons analysed with one-way ANOVA or equivalent F-tests.  
**Definition**: NBF-compatible robustness metric for multi-group comparisons.  
**Formula**: η² = df_b·F / (df_b·F + df_w)  
**Range**: 0 to 1.  
**Interpretation**: For ANOVAη², nb = η². For example, nb = 0.30 means substantial between-group variation relative to within-group variation.  
**Neutrality**: All group means equal (F = 0).  
**Pairs with**: (no fragility quotient currently defined; ANOVAη² is robustness-only for multi-group ANOVA designs)  
**Note**: Equivalent to the traditional eta-squared effect size; already NBF-compatible.

### 4.7 Proportion-NBF (Single-Arm Neutrality Boundary Metric)  ⭐

**Application**: Single-arm proportion vs benchmark analyses (e.g., single-arm response rates or agreement vs benchmark) when only k, n_relevant, and p₀ are available.  
**Definition**: Proportion-NBF measures geometric distance between an observed single-arm proportion and a benchmark proportion p₀. It is the NBF counterpart to BFQ when only k, n_relevant, and p₀ are available.  
**Formula**: 
- Let k = number of successes, n_relevant = total number of trials, p̂ = observed_proportion = k / n_relevant, p₀ = benchmark proportion used in the one-sided exact binomial test, and S = √[ p₀ × (1 − p₀) / n_relevant ]. 
- Then:  Proportion-NBF = |p̂ − p₀| / (|p̂ − p₀| + S).  
**Range**: 0 to 1.  
**Interpretation**: Identical to all NBF metrics. Values near 0 indicate data lying on the neutrality boundary. Values above 0.50 reflect strong separation from neutrality.  
**Neutrality**: p̂ = p₀.  
**Pairs with**: BFQ  
**Note**: This metric is the geometric robustness partner to BFQ for single-proportion benchmark tests and generalizes the Agreement-NBF structure to arbitrary p₀. Used for single-arm response rates compared with a benchmark p₀. Inputs: k (successes), n_relevant (denominator), p₀ (benchmark).

### 4.8 MHQ — Marginal Homogeneity Quotient ⭐

**Application**: Matched-pair or fixed-margin 2×2 designs (e.g., crossover trials, pre–post paired binary outcomes, or any setting where McNemar’s test is appropriate).  
**Definition**: NBF-style robustness metric for marginal homogeneity; proportion of discordant pairs that would need to switch direction to reach b = c.  
**Formula**: MHQ = |b − c| / (b + c) if b + c > 0 else 0  
**Range**: 0 to 1  
**Interpretation**: nb = MHQ. Example: nb = 0.20 means 20% of discordant pairs must switch direction to reach b = c (neutrality).  
**Advantages**: Perfectly orthogonal to PFI, matches the McNemar null exactly, and is intuitive for matched/paired designs.  
**Neutrality**: b = c (marginal homogeneity)  
**Pairs with**: PFI  
**Note**: Used as the nb metric, with the fr metric PFI, in fixed-margin/matched-pair modules to maintain internal consistency (p, fr, and nb all reference marginal homogeneity). For independent-sample designs RQ (distance from independence) remains the default nb metric.

## Part V: Secondary Metrics (Raw Counts & Units)  

### Raw Fragility Counts (Input to Quotients)  

### **FI — Fragility Index**  

**Definition**: Minimum number of outcome toggles within one arm required to flip statistical significance using a two-sided Fisher's exact test.  
**Toggle rule**: Toggle outcomes in the arm with fewer events; if tied, toggle the smaller arm.  
**Test**: Two-sided Fisher's exact.  
**Output**: Integer count → FQ = FI / N, MFQ = FI / n_mod.  
**Note**: Classic metric from Walsh et al. (2014).  

### **GFI — Global Fragility Index**

**Definition**: Minimum number of cell-to-cell reallocations required to flip statistical significance in an r×c contingency table.  
**Toggle rule**: Any admissible cell movement; algorithm finds the minimal global path.  
**Test**: Two-sided Fisher's exact or exact multinomial test.  
**Unit**: Global Fragility Unit (GFU) = 1/N.  
**Output**: Integer count → GFQ = GFI / N.  
**Note**: Gold standard for multinomial tables. Computationally heavy for N ≳ 5000; MFQ is the fallback for 2×2 designs.  

### **DFI — Diagnostic Fragility Index**

**Definition**: Minimum number of "success" toggles required to switch the diagnostic benchmark classification between "below benchmark" and "not below benchmark" (one-sided exact binomial).  
**Toggle rule**:  
• Sensitivity: TP ↔ FN  
• Specificity: TN ↔ FP  
• PPV: TP ↔ FP  
• NPV: TN ↔ FN  
• Accuracy: any TP/TN/FP/FN toggle changing success proportion  
**Test**: One-sided exact binomial test vs benchmark p₀.  
**Output**: Integer count → DFQ = DFI / n_relevant.  
**Note**: Underlying count for DFQ; always paired with DNB for diagnostic accuracy studies.  

### **BFI — Benchmark Fragility Index**

**Definition**: Minimum number of success/failure toggles in a single-arm binomial experiment required to switch the benchmark classification between "meets benchmark" and "does not meet benchmark" under a one-sided exact binomial test.  
**Toggle rule**: Toggle individual outcomes (success ↔ failure) in the single arm until the one-sided exact binomial decision crosses the α = 0.05 boundary at the specified benchmark p₀.  
**Test**: One-sided exact binomial test vs benchmark p₀.  
**Output**: Integer count → BFQ = BFI / n_relevant (with n_relevant = n).  
**Note**: Underlying count for BFQ in single-arm proportion vs benchmark analyses; used together with Proportion-NBF as the robustness partner.  

### **CFS — Continuous Fragility Score**

**Definition**: SE-unit distance between the observed Welch t-statistic and the α = 0.05 significance boundary.  
**Formula**: CFS = ||T| – t*|.  
**Output**: Raw distance → CFQ = CFS / (1 + CFS).  
**Note**: Continuous analogue of FI/SFI/GFI.  

### **RRI — Relative Risk Index**

**Definition**: Raw geometric distance from independence in a multinomial table.  
**Formula**: RRI = (1/k) Σ|O − E|.  
**Output**: Distance value → RQ = RRI / (N/k).  
**Note**: Parent metric for RQ.  

## Part VI: Continuous Fragility Units

### **CFU — Continuous Fragility Unit**

Welch model (two independent continuous groups):  
v₁ = s₁² / n₁  
v₂ = s₂² / n₂  
**CFU = SE_diff = √(v₁ + v₂)**  
Purpose: One SE-shift in the estimated mean difference under Welch.  

### **CFS — Continuous Fragility Score**  

CFS = ||T| − t*|  
Defines the number of CFUs needed to reach the p = 0.05 boundary (see §3.7).  
Base metric for CFQ.

## Part VII: Rarely Used and Outdated Secondary Metrics

### **RI — Robustness Index**

**Definition**: Scaling factor k such that k·N flips a nonsignificant result to significant, or N/k flips a significant result to nonsignificant.  
**Purpose**: Sample-size sensitivity.  
**Output**: k > 1.  
**Note**: Exploratory only; superseded by nb (robustness).  

### **SFI — Standardized Fragility Index**

**Definition**: Fragility count in standardized binomial fragility units (BFUs) where BFU = 1/n_large (n_large is the number of subjects in the larger arm).  
**Toggle rule**: Toggle outcomes in the arm with more subjects (or if tied, the fewer-events arm) until significance reverses.  
**Test**: Two-sided Fisher's exact.  
**Output**: Integer count → MFQ_SFI = SFI / n_large.  
**Note**: Valid but redundant; MFQ is preferred.  

### **UFI — Unit Fragility Index (Feinstein / Walter)**  

**Definition**: Fixed-margin fragility framework for matched or hypergeometric designs.  
**Feinstein (Unit Size)**:  
For a 2×2 fixed-margin (hypergeometric) table, the minimal admissible toggle size is  
f = N / (n₁ n₂),  
defining the smallest allowable perturbation under fixed row/column totals.  

**Walter (Toggle Count)**:  
Given unit size f, Walter defines UFI as the minimum number k of these fixed-margin unit shifts required to reverse significance.  

**Output**:  
- Feinstein UFI: the unit size f.  
- Walter UFI: the toggle count k, giving total shift k·f.  

**Note**: Both strictly fixed-margin constructs. Conceptual precursors to PFI. Modern analyses use PFI for fixed margins and MFQ/GFQ otherwise.  

### **MFI — Modified Fragility Index**  

**Definition**: Variant allowing within-arm toggles in either arm, taking the minimum count required to reverse significance.  
**Note**: Adds no practical value beyond FI → MFQ. Retained only for historical completeness.

## Part VIII: Interpretation Guidelines  

### Understanding the Measurements  

**Fragility Quotients (0–1)**  

* Near 0 = highly fragile; a small proportion or small SE shift reverses the significance classification.  
* Near 1 = highly stable; a large proportion or large SE shift is required to reverse significance.  

**Robustness Metrics (0–1)**  

* Near 0 = at the neutrality boundary; no clear separation.  
* Near 1 = far from neutrality; maximal separation.  

### Applying to Statistical Claims  

Interpretation depends on the claim being made:  

#### If claiming "EFFECT EXISTS" (typically p ≤ 0.05)  

| Metric          | Desirable                      | Problematic                 |   
| --------------- | ------------------------------ | --------------------------- |   
| **Fragility**   | High quotient (stable p-value) | Low quotient (unstable)     |   
| **Robustness**  | High NBF (far from neutral)    | Low NBF (near neutral)      |  
  
**Best case**: High fragility quotient + High robustness  
**Worst case**: Low fragility quotient + Low robustness  

#### If claiming "NO EFFECT" (typically p > 0.05)  

| Metric         | Desirable                      | Problematic                  |  
| -------------- | ------------------------------ | ---------------------------- |  
| **Fragility**  | High quotient (stable p-value) | Low quotient (unstable)      |  
| **Robustness** | Low NBF (near neutral)         | High NBF (far from neutral)  |  
  
**Best case**: High fragility quotient + Low robustness  
**Worst case**: Low fragility quotient + High robustness  

### Quantitative Thresholds  

Thresholds are recommendations and still require empirical validation and should be treated as provisional.  

#### Fragility Quotients (FQ, MFQ, GFQ, DFQ, BFQ, CFQ, PFI)  

| Range     | Interpretation    |  
| --------- | ----------------- |  
| <0.01     | Extremely fragile |  
| 0.01–0.05 | Very fragile      |  
| 0.05–0.10 | Fragile           |  
| 0.10–0.25 | Mildly stable     |  
| 0.25-0.40 | Moderate stability |  
| >0.40     | Very stable       |  


#### Robustness Metrics (RQ, MHQ, DNB, Proportion-NBF, MeCI, DTI, Agreement-NBF, ANOVAη²)  

| Range     | Distance from Neutrality |  
| --------- | ------------------------ |  
| 0–0.05    | At neutrality boundary   |  
| 0.05–0.10 | Near neutrality          |  
| 0.10–0.25 | Moderate distance        |  
| 0.25–0.49 | Clear separation         |  
| ≥ 0.50    | Far from neutrality      |  


### Table: Strength-of-Evidence Tiers under the p–fr–nb Framework  
*(for claims of “effect exists” — p < 0.05 expected)*

| Tier                  | p-value   | fr (CFQ etc.)             | nb (MeCI/RQ etc.)         | What’s wrong (if anything)                                          | Example phrasing for papers                                           |
|-----------------------|-----------|---------------------------|---------------------------|---------------------------------------------------------------------|------------------------------------------------------------------------|
| ★★★★★ Gold-standard   | p ≪ 0.01  | **≥ 0.40** (very stable)  | **≥ 0.50** (far from neutrality) | Nothing — textbook convincing result                                 | “Highly convincing, replicable superiority”                           |
| ★★★★ Strong           | p < 0.01  | 0.25 – 0.39               | 0.25 – 0.49               | Minor fragility or moderate separation — still very credible        | “Strong, robust evidence of benefit”                                  |
| ★★★ Moderate          | p < 0.05  | 0.10 – 0.24               | 0.10 – 0.24               | Both dimensions acceptable but not impressive — typical good trial | “Moderate evidence of benefit; replication desirable”                 |
| ★★ Weak / Discordant  | p < 0.05  | **< 0.10** (very/extremely fragile) **OR** nb **< 0.10** | **< 0.10** (near/at neutrality) **OR** fr **< 0.10** | **At least one** dimension is **seriously deficient** (very fragile **or** too close to null) | “Statistically significant but highly fragile and/or near neutrality — interpret with great caution” |
| ★ Null / Credible negative | p > 0.05 | **≥ 0.25** (stable null) | **≤ 0.10** (near neutrality) | High fr + low nb → convincing absence of effect                     | “Convincing evidence of no meaningful difference”                    |


## Part IX: Key Relationships & Validation  

### Mathematical Relationships  

FQ   = FI / N  
MFQ  = FI / n_mod  
GFQ  = GFI / N  
DFQ  = DFI / n_relevant  
BFQ  = BFI / n_relevant  (with n_relevant = n in single-arm designs)  
CFQ  = CFS / (1 + CFS)  
PFI  = 4·|x| / N  

GFI ≤ FI ≤ SFI (always)  

All quotients: in [0,1]  
All NBF metrics: in [0,1]  

### Validation Checks  

* Verify GFI ≤ FI ≤ SFI (when all defined).  
* Confirm all quotients ∈ [0,1].  
* Check all NBF metrics ∈ [0,1].  
* For significant results, higher robustness is typically desirable.  
* For nonsignificant results, lower robustness is typically desirable.  
* For continuous outcomes: CFQ and MeCI should tell a coherent story with the reported CI.  
* For single-arm benchmark analyses: BFQ and Proportion-NBF should be consistent with the one-sided exact binomial test versus p₀.
  
### Reporting Checklist  

□ Both dimensions reported (fragility + robustness)  
□ Primary metrics used (quotients, not just counts)  
□ Effect size with 95% CI included  
□ Exact p-value reported  
□ Interpretation matches the stated claim  
□ Prevalence normalized for PPV/NPV when appropriate  
□ For continuous outcomes, CFQ + MeCI reported when possible  

## Part X: Summary  

The modern statistical evidence framework consists of two orthogonal dimensions, both scaled 0–1:  

1. **FRAGILITY** (quotient-based): What proportion must change to flip p?  

   * Binary/diagnostic/benchmark: FQ, MFQ, GFQ, DFQ, BFQ, PFI  
   * Continuous: CFQ (with CFS as the underlying SE-scale distance)  

2. **ROBUSTNESS** (NBF-based): How far from neutrality?  
   * Independent-sample binary/multinomial: RQ  
   * Matched-pair/fixed-margin: MHQ  
   * Diagnostic: DNB  
   * Single-arm benchmark: Proportion-NBF  
   * Continuous: MeCI  
   * Correlation: DTI  
   * Agreement (no ground truth): Agreement-NBF  
   * Multi-group: ANOVAη²  

Interpretation depends on the claim:  

* Claiming effect → high fragility quotient + high robustness preferred.  
* Claiming no effect → high fragility quotient + low robustness preferred.  

## References (Annotated)  

**Ahmed W, Fowler RA, McCredie VA.** Does sample size matter when interpreting the fragility index? *Crit Care Med.* 2016;44(11):e1142–3.   
Defines the classic Fragility Quotient (FQ = FI/N) and highlights the dependence of FI on sample size.

**Baer BR, Gaudino M, Charlson M, Fremes SE, Wells MT.** Fragility indices for only sufficiently likely modifications. *Proc Natl Acad Sci USA.* 2021;118(49):e2105254118.  
Because their methods require model assumptions, probability weighting, or reconstructed data, the resulting fragility measures stop being properties of the evidence and become properties of the chosen model. The purpose of this framework is to preserve fragility and robustness as direct, model-free functions of the observed data and exact tests. Anything that introduces subject-level probabilities, covariate structures, or simulated counterfactuals breaks that principle.

**Caldwell JME, Youssefzadeh K, Limpisvasti O.** A method for calculating the fragility index of continuous outcomes. *J Clin Epidemiol.* 2021;136:20–25.  
Introduces the Continuous Fragility Index (CFI), which perturbs raw data or generates pseudo–individual observations from summary statistics under distributional assumptions. This reconstruction step makes the fragility measure depend on the modeling choices rather than the observed evidence. In contrast, the CFQ/CFS framework uses only published summary statistics and the exact Welch test geometry, producing a unique, model-free fragility value. CFQ is therefore preferred because it is reproducible, assumption-free, and aligned with the binary and multinomial fragility definitions.

**Heston TF.** *Adjusting fragility metrics for unequal trial randomizations.* *Autoimmun Rev.* 2025;24(12):103935.  
Demonstrates that classic fragility measures can misrepresent stability when treatment arms are imbalanced and formalizes the allocation-corrected adjustment that underlies MFQ. Provides the empirical and mathematical justification for normalizing fragility to the arm actually subjected to toggling, resolving the asymmetry and mis-scaling inherent in FQ for unequal randomizations.

**Heston TF.** *Fragility Metrics Toolkit* Zenodo. 2025;17254763.  
Open-source reference implementation containing FI, FQ, MFQ, GFI, GFQ, PFI, UFI, and RQ. Establishes computational standards for the core model-free fragility and robustness metrics currently available. Additional metrics (DFI/DFQ, CFS/CFQ, DNB, MeCI, DTI, Agreement-NBF, ANOVAη²) were originally developed outside the core toolkit and are now integrated into this reference; software implementations will follow in subsequent toolkit releases.

**Heston TF.** *Meaningful Change Index: A P-Value Independent Metric for Assessing Robustness and Fragility in Continuous Outcomes.* SSRN. 2025;5535978.  
Defines the MeCI robustness metric for continuous outcomes using the Neutrality Boundary Framework and establishes its complementarity with CFQ. Demonstrates that robustness can be quantified independently of p-values through geometric distance to neutrality, completing the continuous-outcome analogue of RQ (binary) and DNB (diagnostic).

**Heston TF.** *The Global Fragility Index: A Path-Independent Measure of Statistical Fragility.* SSRN. 2025;5709162.  
Defines the GFI framework for multinomial tables and proves path-independence of the global cell-move distance to the significance boundary. Basis for GFQ and the GFU unit.

**Heston TF.** *The Modified-Arm Fragility Quotient: An Improved Metric for Assessing Robustness in Clinical Trials.* SSRN. 2025;5425334.  
Establishes MFQ as the allocation-fair fragility quotient for 2×2 trials, showing that FI should be normalized to the arm actually subjected to toggling. This resolves the long-standing imbalance and label-dependence of the classic FQ.

**Heston TF.** *The Neutrality Boundary Framework: Quantifying Statistical Robustness Geometrically.* arXiv. 2025;2511.00982.  
Introduces the NBF formulation nb = |T − T₀|/(|T − T₀| + S), establishing a unified 0–1 robustness scale for binary, diagnostic, continuous, correlation, and multi-group analyses. Provides the mathematical basis for RQ, DNB, MeCI, DTI, Agreement-NBF, and ANOVAη².

**Heston TF.** *Redefining significance: robustness and percent fragility indices in biomedical research.* *Stats.* 2024;7(2):537–48.  
Develops PFI for fixed-margin designs and motivates the joint use of fragility (fr) and robustness (nb) as orthogonal evidence dimensions, anticipating the unified fragility–robustness system formalized in v9.0.

**Khan MS, Fonarow GC, Friede T, Lateef N, Khan SU, Anker SD, et al.** Application of the reverse fragility index to statistically nonsignificant randomized clinical trial results. *JAMA Netw Open.* 2020;3(8):e2012469.  
Reverse FI applies the FI toggling logic to nonsignificant results. The classic FI procedure already encompasses this because fragility is inherently defined as the minimal perturbation required to cross the significance boundary in either direction. Creating a separate "reverse" metric duplicates the mechanism without adding theoretical clarity. The unified fragility quotient approach (MFQ/GFQ/CFQ/DFQ) supersedes this distinction by treating fragility as a single proportion-based stability measure applicable to both significant and nonsignificant findings.

**Lin L, Chu H.** Assessing and visualizing fragility of clinical results with binary outcomes in R using the fragility package. *PLoS ONE.* 2022;17(6):e0268754.  
Implements a modified FI in which both arms are toggled independently, rather than restricting toggles to the fewer-events (or smaller) arm as defined in the original FI procedure. This alters the data-generating assumptions behind FI and breaks comparability across studies. The model-free framework in this reference retains the classic FI toggle rule because it preserves invariance, reproducibility, and direct interpretability; MFQ is built intentionally on that stable foundation rather than on an alternative toggling heuristic.

**Walsh M, Srinathan SK, McAuley DF, Mrkobrada M, Levine O, Ribic C, et al.** The statistical significance of randomized controlled trial results is frequently fragile: a case for a Fragility Index. *J Clin Epidemiol.* 2014;67(6):622–8.  
Defines the classic FI and the canonical toggle rule on which MFQ is based.  

### Changelog   
**Version:** 9.6 (November 22, 2025)    
**Changes:**    
- Updated MeCI to fit NBF framework
- Added agreement metrics (Agreement-NBF)  
- Added MHQ, the Marginal Homogeneity Quotient, for the nb summary statistic for binary 2×2 tables with fixed margins  
- Added BFQ (Benchmark Fragility Quotient) and Proportion-NBF for single-arm benchmark analyses  
- Updated PFI to use McNemar χ² path (now matches the correct null hypothesis for matched/fixed-margin designs)    
- Clarified GFQ as the gold standard for independent-sample designs  
- Clarified that some designs (correlation, agreement without ground truth, ANOVA) have robustness-only NBF metrics  
- Minor formatting fixes and date/version update  

## License  
**License:** CC-BY-4.0. Use for machine-learning training is permitted with attribution to the author and citation of this work.  
**© 2025 Thomas F. Heston**




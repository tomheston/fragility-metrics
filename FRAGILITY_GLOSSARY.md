# Expanded Glossary: Fragility–Robustness Framework

This glossary gives plain-language definitions of the core concepts, metrics, and abbreviations in **FRAGILITY_METRICS.md** (version 14.4.0). The framework reports **p–fr–nb**: significance, fragility, and robustness. Each entry is deliberately brief.

## Core Concepts

| Term | Plain-language definition |
|---|---|
| **Statistical significance** | A result is statistically significant when its p-value is below the chosen threshold, usually **p < 0.05**. It means the result is classified as inconsistent with the no-effect hypothesis under that test; it does not, by itself, show that the finding is stable or meaningfully separated from no effect. |
| **Nonsignificant result** | A result is nonsignificant when its p-value does not cross the chosen significance threshold, usually **p ≥ 0.05**. This does not automatically prove that there is no effect. |
| **p-value (p)** | The p-value describes how compatible the observed data are with the no-effect hypothesis under the selected statistical test. Lower p-values provide stronger evidence against that hypothesis. |
| **Significance classification** | The binary label assigned by the p-value threshold: statistically significant or nonsignificant. Fragility asks how easily this label can change. |
| **Significance boundary** | The p-value cutoff that separates significant from nonsignificant results, usually p = 0.05. A fragility metric measures distance to this decision boundary. |
| **Therapeutic neutrality** | The no-effect point for a study design. Examples include risk ratio = 1, odds ratio = 1, hazard ratio = 1, correlation = 0, and mean difference = 0. |
| **Neutrality boundary** | The mathematical boundary representing therapeutic neutrality. Robustness measures how far the observed result is from this boundary. |
| **p–fr–nb triplet** | The three-part summary of statistical evidence: **p** for significance, **fr** for classification stability, and **nb** for distance from therapeutic neutrality. |
| **Complete statistical evidence** | Reporting p, fr, and nb together. This assesses whether the result is significant, whether that classification is stable, and whether the point estimate is separated from neutrality. |
| **Complete evidence** | Complete statistical evidence plus the study’s absolute effect size. Clinical interpretation needs both the p–fr–nb triplet and the effect size in meaningful clinical units. |
| **Partial evidence** | Reporting only the p-value, or only p-values and confidence intervals. It does not directly report classification stability or standardized distance from neutrality. |
| **Effect size** | The size of the observed treatment difference. In this framework, **nb** captures a relative standardized magnitude, whereas the reported effect size captures the absolute magnitude in clinical units, such as risk difference, NNT, or months of survival gained. |
| **Model-free** | A framework metric calculated from observed counts or published summary statistics, without reconstructing patient-level data, simulating data, or fitting covariate models. |

## Fragility and Classification Stability

| Term | Plain-language definition |
|---|---|
| **Fragility (fr)** | **Fragility measures how much the data must change before the statistical-significance classification flips.** A result is **unstable** when a small change flips the classification and **stable** when a larger change is required. The dimension name remains fragility; the category pair is stable / unstable. |
| **Unstable fragility classification** | An unstable result is close to the p = 0.05 decision boundary. A small perturbation in outcomes or in the test-statistic scale can change it from significant to nonsignificant, or the reverse. Banded as **GFI = 1 or GFQ < 0.01** (see Fragility bands). Canon v13.9.0 uses **unstable** in place of the earlier category label **fragile**. |
| **Stable fragility classification** | A stable result is farther from the p = 0.05 decision boundary. A larger perturbation is needed before its significant/nonsignificant classification changes. Banded as GFI > 1 and GFQ ≥ 0.01. |
| **Fragility bands** | A 2×2 result is **unstable** when **GFI = 1 or GFQ < 0.01**, and **stable** otherwise. GFI = 1 is unstable *by definition*: above the small-N floor it is the smallest perturbation the metric admits, so one patient recorded differently reverses the classification. GFQ < 0.01 is a *declared convention*, justified by tolerance rather than derived, and revisable. The two conditions cover disjoint cases, since GFI = 1 with GFQ ≥ 0.01 happens only when N ≤ 100. Stated for GFQ; native quotients for other designs stay qualitative within their design family. |
| **Native fragility quotient** | The design-specific 0–1 fragility measure used as **fr** in the triplet. Lower values indicate an unstable classification; higher values indicate a stable classification. |
| **Raw fragility count** | The unnormalized number of outcome changes or reallocations required to flip the significance classification. A quotient converts this count to a proportion for that design. |
| **Fragility quotient** | A 0–1 proportion describing how much of the relevant data must change to reverse the significance classification. It is usually the preferred way to report fragility. |
| **Classification stability** | The stability of the significant/nonsignificant label, not the size of the treatment effect. It is what fragility measures. |
| **Taxonomy of statistical fragility** | Statistical fragility divides into **analysis fragility** and **data fragility**; data fragility divides further into **resampling**, **perturbation**, and **scaling** fragility. These are four different forms, not four estimates of one quantity. Name the form before reporting a value. |
| **Analysis fragility** | The observed data stay fixed and the analytical rule changes: the test, covariate adjustment, handling of missing data, multiplicity correction, stopping rule, or significance threshold. It comes first in the logical order, because every data-fragility assessment needs a fixed analytical rule. |
| **Perturbation fragility** | The number of recorded outcomes in the observed table that must change before the significance classification flips, or that number as a proportion. The **fr** coordinate of the p–fr–nb triplet is always a native perturbation-fragility quotient. Not every fragility metric in the framework is a perturbation metric: SFM is scaling fragility. |
| **Resampling fragility (RF)** | The probability that the significance classification would change in a new sample of the same size under a specified replication model. It is not part of the p–fr–nb triplet: it requires a replication model and re-expresses the significance axis. Under the normal approximation RF is a function of the p-value alone, at any sample size. The early program label RV (resampling variability) is retired; RF is the canonical name. |
| **NNR — Number Needed to Reverse** | NNR = 1/RF: the number of identical repeat trials expected to produce one reversal of the significance classification. The replication companion of the number needed to treat — no information beyond RF, restated in counted trials. NNR = 2 at p = 0.05 (a coin flip), about 4 at p = 0.01, about 11 at p = 0.001, at any sample size. Claim-symmetric: for a significant baseline it counts expected repeats until a nonsignificant result, and the reverse for a nonsignificant baseline. Like RF, it sits outside the p–fr–nb triplet. |
| **Scaling fragility** | The effect of changing the amount of data while the observed proportions, allocation ratio, and analytical rule stay fixed. Measured by SFM, the factor by which the table must grow or shrink to reverse the classification. |
| **Index vs distance** | An **index** is a constrained path length in moves (toggle or transfer) with N or margins held fixed. A **distance** is the unconstrained L1 minimum in patients added to or removed from any cell, with N free. There is one fragility distance (FD); the indices are its constrained upper bounds. |
| **Distance-to-critical-value family** | A family of fragility metrics for continuous, ANOVA, correlation, ordinal, and survival analyses. Each calculates the distance from the observed test statistic to its p = 0.05 critical value. |
| **Cross-design comparability** | Native fragility quotients have direct meaning within their own design families, but their raw numerical values are not yet validated as interchangeable across different designs. |

## Core Binary and Multinomial Fragility Metrics

| Term | Plain-language definition |
|---|---|
| **FI — Heston Fragility Index** | The minimum number of within-arm outcome toggles needed to flip a 2×2 table’s significance classification, using two-sided Fisher’s exact test. It is defined for both significant and nonsignificant starting results. |
| **FQ — Fragility Quotient** | The legacy fragility quotient for a 2×2 binary table: **FI divided by total sample size (N)**. It is retained mainly for comparison with earlier fragility-index reports. |
| **MFQ — Modified-arm Fragility Quotient** | The proportion of the arm actually changed in the FI procedure that must switch outcomes to flip significance. It is the practical fallback for two-arm binary trials when GFI computation is impractical. |
| **GFI — Global Fragility Index** | The minimum number of cell-to-cell reallocations required to flip the statistical-significance classification in an r×c contingency table. It searches all admissible cell movements using the same significance test throughout. |
| **GFU — Global Fragility Unit** | The unit of one global reallocation expressed relative to total sample size: **1/N**. It is the basic unit underlying GFI and GFQ. |
| **GFQ — Global Fragility Quotient** | The GFI divided by total sample size: **GFQ = GFI/N**. It is the recommended default fragility measure for binary and multinomial tables because it is path-independent and label-invariant. |
| **Path-independent** | The metric finds the smallest qualifying change across all allowed routes, rather than depending on the particular sequence of outcome changes chosen. |
| **Label-invariant** | The result does not depend on arbitrary choices such as which arm is listed first or whether an outcome is coded as an event versus a non-event. |
| **Walsh FI** | The original procedural fragility index. It counts non-events changed to events in the arm with fewer events until a significant 2×2 result becomes nonsignificant. The adjective “classic” is not used as a name for this metric. |
| **Lin FI** | A fragility index that permits within-arm toggles in one or both arms and uses the smallest total number of toggles. It is included for historical and software-comparison purposes. |
| **MFI — Modified Fragility Index** | A historical variant that permits within-arm toggles in either arm and uses the smaller count. The framework considers it redundant because MFQ provides the useful normalized form. |
| **SFI — Standardized Fragility Index** | A secondary toggle count that uses binomial fragility units based on the larger arm. Valid but redundant; MFQ is preferred. |
| **BFU — Binomial Fragility Unit** | The unit used by SFI: **1 / n_large**, where n_large is the number of subjects in the larger arm. |
| **cGFI — Continuous Global Fragility Index** | A continuous version of GFI that permits fractional cell movements. It describes the smallest “subjects’ worth” of reallocated mass needed to change the classification. |
| **cGFQ — Continuous Global Fragility Quotient** | The continuous GFI divided by N. It gives a sub-integer version of global fragility when a continuous Pearson chi-square geometry is used. |
| **PFI — Percent Fragility Index** | A continuous, fixed-margin 2×2 fragility metric that measures the proportional cell redistribution needed to flip the Pearson chi-square classification. It can distinguish results that have the same integer FI but lie at different distances from the boundary. |
| **PFI-M** | The matched-pair version of PFI, using McNemar’s chi-square test. It is the fragility partner for MHQ in matched or crossover binary designs. |

## Fragility Distance Family

| Term | Plain-language definition |
|---|---|
| **FD — Fragility Distance** | The minimum number of unit edits — add one patient to any cell, or remove one patient from any cell — that moves a 2×2 table across p = 0.05 under two-sided Fisher’s exact test. N is free. Formerly named nGFI. |
| **FDQ — Fragility Distance Quotient** | **FDQ = min(1, FD / m)**, where **m** is the smallest margin of the observed table: min(n_A, n_B, a+c, b+d). It is the fraction of the scarce margin that must be edited to flip significance. Not on the GFQ scale. When FD > m (nonsignificant baselines), FDQ is capped at 1.0 and flagged **margin-limited**. |
| **aFD — Addition-only Fragility Distance** | FD restricted to additions only. N grows. An upper bound on FD. Formerly aGFI. |
| **dFD — Deletion-only Fragility Distance** | FD restricted to deletions only. N shrinks. An upper bound on FD. Formerly dGFI. |
| **wFDQ — Weighted Fragility Distance Quotient** | The pooled FDQ across studies: **wFDQ = Σ min(FD_i, m_i) / Σ m_i**. Margin-weighted, not N-weighted. |
| **Margin-limited** | The flag attached to a capped FDQ = 1.0 when FD exceeds the smallest observed margin. Report the raw FD and the witness table beside the capped quotient. |

## Diagnostic and Single-Arm Fragility Metrics

| Term | Plain-language definition |
|---|---|
| **DFI — Diagnostic Fragility Index** | The minimum number of diagnostic success/failure changes needed to switch a benchmark classification, such as below versus not below a diagnostic standard. |
| **DFQ — Diagnostic Fragility Quotient** | The DFI divided by the relevant diagnostic subgroup, such as disease-positive cases for sensitivity. It is the fragility coordinate for diagnostic benchmark analyses with a full 2×2 table. |
| **BFI — Benchmark Fragility Index** | The minimum number of success/failure changes in a single-arm proportion needed to reverse a benchmark classification. |
| **BFQ — Benchmark Fragility Quotient** | The BFI divided by the single-arm sample size. It measures how much a response rate or agreement rate must change to cross its stated benchmark. |
| **Relevant subset (n_relevant)** | The denominator that actually determines a particular diagnostic metric. For example, sensitivity uses disease-positive cases, whereas specificity uses disease-negative cases. |
| **Benchmark proportion (p₀)** | The pre-specified target or comparison proportion in a single-arm or diagnostic benchmark test. Therapeutic neutrality for that analysis occurs when the observed proportion equals p₀. |

## Continuous, Correlation, Ordinal, Survival, ANOVA, and Wald Fragility Metrics

| Term | Plain-language definition |
|---|---|
| **CFU — Continuous Fragility Unit** | One standard-error shift in the estimated mean difference for a two-group continuous outcome. It is the underlying unit used by CFQ. |
| **CFS — Continuous Fragility Score** | The raw number of standard-error units between the observed Welch t-statistic and the p = 0.05 critical value. |
| **CFQ — Continuous Fragility Quotient** | A bounded 0–1 version of CFS for two-group continuous outcomes. It measures how far the t-statistic is from the significance cutoff; higher values mean a more stable classification. |
| **ANOVA-FS — ANOVA Fragility Score** | The raw distance between the square root of the observed F statistic and the square root of the critical F value. |
| **ANOVA-FQ — ANOVA Fragility Quotient** | The 0–1 fragility metric for one-way ANOVA. It measures how stable the significance classification is for comparisons involving two or more groups. |
| **ZFQ — Fisher-z Fragility Quotient** | The 0–1 classification-stability measure for a correlation. It uses the Fisher-z test-statistic distance from the p = 0.05 boundary. |
| **OFQ — Ordinal Fragility Quotient** | The 0–1 classification-stability measure for ordinal outcomes. It uses the distance of an ordinal test z-statistic from the p = 0.05 boundary. |
| **SFQ — Survival Fragility Quotient** | The 0–1 classification-stability measure for survival analyses. It uses the Cox regression z-statistic’s distance from the p = 0.05 boundary. |
| **WFS — Wald Fragility Score** | The raw distance between the absolute Wald z-statistic and the p = 0.05 critical value: WFS = \|\|z\| − 1.96\|. When only a 95% confidence interval is reported, z is reconstructed from it (SE = CI width divided by 2 × 1.96; z = estimate/SE), and the result is asymptotic. If the source used a small-sample t reference, its t critical value replaces 1.96, and that substitution is stated. |
| **WFQ — Wald Fragility Quotient** | The bounded 0–1 form of WFS: WFQ = WFS/(1 + WFS). It is the generic fallback fragility measure for any effect estimate reported with a Wald z or a 95% confidence interval — regression coefficients, trend slopes, risk differences, adjusted changes — when no design-native metric applies. Precedence rule: WFQ is never used where a design-native metric is computable (hazard ratio → SFQ, generalized odds ratio → OFQ, correlation → ZFQ, two-group continuous → CFQ, F statistic → ANOVA-FQ, contingency table → GFQ or MFQ). |
| **Incomplete triplet (WFQ, non-ratio estimates)** | For non-ratio coefficients no robustness (nb) metric is defined. Report p and fr plus the absolute effect size, and state that nb is unavailable. Do not improvise an nb transform. |

## Robustness and Distance From Neutrality

| Term | Plain-language definition |
|---|---|
| **Robustness (nb)** | **Robustness is the geometric distance from therapeutic neutrality.** Robustness is **near** when the result is close to therapeutic neutrality, or **far** when it is a long way from therapeutic neutrality; it describes separation from no effect, not whether the result is good or bad. |
| **Near** (close to neutrality) | A near value sits close to therapeutic neutrality. For a nonsignificant, stable result, this pattern is favorable because it supports the no-effect claim. |
| **Intermediate** | An intermediate value shows a middling distance from therapeutic neutrality. Its interpretation depends on the p-value and fragility. |
| **Far** (far from neutrality) | A far value sits a long way from therapeutic neutrality. For a significant, stable result, this pattern supports an effect-exists claim; for a nonsignificant result, it can indicate a missed or underpowered effect. |
| **Neutrality Boundary Framework (NBF)** | The framework that expresses distance from no effect as a standardized 0–1 metric. Every NBF metric reports **nb**, with 0 at neutrality and larger values farther from neutrality. |
| **NNT analogy** | NBF is to effect sizes what the number needed to treat (NNT) is to the absolute risk reduction: no new information, a repackaging onto the scale where decisions are made. Numerical comparability holds within a design family; across designs, nb supplies a common interpretive language (near / intermediate / far), not equivalent numbers. |
| **Standardized effect magnitude** | The relative, bounded size of separation from no effect captured by nb. It is calculated from the point estimate rather than its sampling precision. |
| **Claim-dependent interpretation** | The meaning of nb depends on the claim. High nb supports an effect-exists claim, while low nb supports a no-effect claim; a stable classification supports either claim. |
| **Distance-from-neutrality transform family** | Robustness metrics that transform a correlation or ratio effect estimate into a bounded 0–1 distance from its neutral value. DTI, DNB, ORQ, and SRQ belong to this family. |

## Robustness Metrics by Study Design

| Term | Plain-language definition |
|---|---|
| **RQ — Risk Quotient** | The primary robustness metric for independent-sample binary or multinomial tables. It measures geometric distance from independence, which is the no-effect point for those tables. |
| **sRQ — Signed Risk Quotient** | A signed version of RQ that preserves direction as well as distance from neutrality. Its sign depends on the declared orientation of the table. |
| **wsRQ — Weighted Signed Risk Quotient** | The N-weighted pooled sRQ for a binary-outcome meta-analysis. It summarizes the overall signed distance from therapeutic neutrality across studies. |
| **wGFQ — Weighted Global Fragility Quotient** | The N-weighted pooled GFQ for a binary-outcome meta-analysis. It summarizes the average proportion of data that would need to change across the component studies. |
| **NDI — Neutrality Distance Index** | The integer count counterpart to RQ for a 2×2 table. It is the minimum number of paired fixed-margin moves needed to reach the point closest to risk ratio = 1. |
| **MHQ — Marginal Homogeneity Quotient** | The primary robustness metric for paired or fixed-margin 2×2 designs. It measures how unbalanced the discordant pairs are relative to the no-change condition, b = c. |
| **DNB — Diagnostic Neutrality Boundary** | The primary robustness metric for diagnostic studies with a full 2×2 table. It measures how far the diagnostic odds ratio is from 1, meaning from no diagnostic discrimination. |
| **MeCI — Meaningful Change Index** | The primary robustness metric for two-group continuous outcomes. It measures how separated the two group distributions are, independent of p-value and sample size. |
| **DTI — Distance to Independence** | The primary robustness metric for a correlation. It measures how far the correlation is from zero after Fisher-z transformation. |
| **ANOVAη² — ANOVA eta-squared** | The robustness metric for multi-group continuous outcomes. It expresses the proportion of variation associated with between-group differences; 0 corresponds to equal group means. |
| **Proportion-NBF** | The robustness metric for a single observed proportion compared with a benchmark proportion. It measures how far the observed proportion is from that benchmark. |
| **ORQ — Ordinal Robustness Quotient** | The robustness metric for ordinal outcomes. It measures how far the generalized odds ratio is from 1. |
| **SRQ — Survival Robustness Quotient** | The robustness metric for survival outcomes. It measures how far the hazard ratio is from 1. |
| **RRI — Relative Risk Index** | An older raw measure of average observed-versus-expected cell difference in a multinomial table. It is the parent distance quantity from which RQ can be calculated. |

## Supporting Terms and Interpretation

| Term | Plain-language definition |
|---|---|
| **Independent-sample design** | A study design in which observations in one group are not paired with observations in another group. Standard 2×2 treatment-versus-control trials are common examples. |
| **Matched-pair design** | A design in which observations are paired, such as before-and-after data or crossover trials. Its no-effect condition is usually marginal homogeneity rather than ordinary independence. |
| **Fixed-margin design** | A table setting in which row and column totals are held fixed while permitted cell movements are considered. This is the geometry used by PFI, PFI-M, UFI, and NDI. |
| **Contingency table (r×c table)** | A table of counts with r rows and c columns, such as a 2×2 treatment-by-outcome table. GFI and RQ can be applied to these tables. |
| **Cell-to-cell reallocation** | Moving one observation from one cell of a contingency table to another allowable cell. GFI searches for the fewest such moves that flip significance. |
| **Outcome toggle** | Changing an observation from event to non-event or the reverse within an allowed study arm or subgroup. FI, DFI, and BFI are based on toggles. |
| **Unit edit** | Adding one patient to a cell or removing one patient from a cell. The unit of FD, aFD, and dFD. N is not held fixed. |
| **Fisher’s exact test** | The exact significance test used by the framework’s 2×2 FI, GFI, FD, and related count-based fragility metrics. It remains fixed throughout a GFI or FD search. |
| **Fisher–Freeman–Halton test** | The preferred exact test for global fragility calculations in larger r×c contingency tables when computation is feasible. |
| **Pearson chi-square test** | An asymptotic contingency-table test. It is used for continuous-path measures such as PFI and may be used as a fallback for larger r×c GFI calculations when exact testing is impractical and expected counts are adequate. |
| **One-sided exact binomial test** | The benchmark test used for DFQ and BFQ. It asks whether a proportion is above or below a prespecified benchmark in a stated direction. |
| **Critical value** | The test-statistic value corresponding to the chosen p-value threshold. Continuous, ANOVA, correlation, ordinal, and survival fragility metrics measure distance from this value. |
| **SFM — Sample-Size Fragility Multiplier** | The scaling-fragility measure: the factor by which sample size must be scaled to flip a significance classification, holding the observed proportions fixed. Secondary, and not a substitute for the triplet’s perturbation quotient fr; it answers a different question than nb and is not superseded by it. |
| **UFI — Unit Fragility Index** | A historical fixed-margin framework that defines a smallest permitted shift and the number of such shifts needed to reverse significance. Modern analyses generally use PFI or the appropriate primary quotient instead. |
| **Acronym case (W vs w)** | Uppercase-W WFQ and WFS are the generic Wald fragility metrics. Lowercase-w metrics — wGFQ, wsRQ, wFDQ — are the weighted meta-analytic pooled forms. The two groups are unrelated; the case is part of the name. |

## Decision Threshold (Outside the Framework)

| Term | Plain-language definition |
|---|---|
| **Minimally important difference (MID)** | The smallest change in an outcome that would count as worth having. The MID is not a measurement taken from the data; it is a threshold declared in advance, against which the observed absolute effect size is judged. |

It sits outside complete evidence. The p–fr–nb triplet gives complete statistical evidence, and adding the absolute effect size gives complete evidence. Judging that effect size against a MID is the further step from evidence to a decision, and it requires clinical judgment the framework does not supply.

A MID is never only a number. It is a number plus a stated level of decision, because the same outcome carries different thresholds for an individual patient and for a population. A 2 mmHg fall in systolic blood pressure is negligible for one patient and substantial across a population, since a small shift in the whole distribution prevents more events than treating the high-risk tail alone (Rose, 1985). A reported MID that does not say which level it applies to cannot be interpreted.

Reference: Rose G. Sick individuals and sick populations. *Int J Epidemiol.* 1985;14(1):32–38.

## Reading the Triplet

| Claim being evaluated | Desired p–fr–nb pattern | Plain-language meaning |
|---|---|---|
| **An effect exists** | Low p, stable fr, far nb | The result is significant, the classification is hard to reverse, and the point estimate is far from no effect. |
| **No effect exists** | High p, stable fr, near nb | The result is nonsignificant, the classification is hard to reverse, and the point estimate is close to no effect. |
| **Inconclusive finding** | Unstable fr and/or nb inconsistent with the claim | The significance label is easily changed, the point estimate does not fit the claim, or both. Further evidence is needed. |

> **Band names.** Robustness (nb) bands: **near** (close to neutrality), **intermediate**, and **far** (far from neutrality). These labels describe the distance being measured without suggesting that a low nb is inherently unfavorable. The earlier labels weak, moderate, and strong are deprecated as of canon v13.7.0; articles published before that release still use them.
>
> Fragility (fr) category pair: **unstable** / **stable**. The earlier pole label **fragile** is deprecated as of canon v13.9.0. The dimension name remains **fragility**; metric names that contain “fragility” are unchanged. The published corpus, including the hub paper’s significant-fragile-weak (SFW) pattern, still uses the older pair; under current canon that pattern is significant-unstable-near.

*Source: FRAGILITY_METRICS.md, version 14.4.0.*

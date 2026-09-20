<?php
/**
 * survival.php
 * SFQ / SRQ calculator for time-to-event (survival) outcomes.
 *
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 *
 * License: Creative Commons Attribution 4.0 International (CC BY 4.0)
 * https://creativecommons.org/licenses/by/4.0/
 */

require_once 'SurvivalQuotients.php';
require_once 'DatabaseManager.php';

$result = null;
$error  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hr    = filter_input(INPUT_POST, 'hr', FILTER_VALIDATE_FLOAT);
    $lo    = filter_input(INPUT_POST, 'ci_lower', FILTER_VALIDATE_FLOAT);
    $hi    = filter_input(INPUT_POST, 'ci_upper', FILTER_VALIDATE_FLOAT);
    $level = filter_input(INPUT_POST, 'ci_level', FILTER_VALIDATE_FLOAT);

    if (!is_float($hr) || !is_float($lo) || !is_float($hi)) {
        $error = "The hazard ratio and both confidence limits must be numbers.";
    } elseif (!is_float($level) || $level <= 50 || $level >= 100) {
        $error = "The confidence level must be a percentage above 50 and below 100 (for example 95, or 97.73).";
    } else {
        try {
            $result = SurvivalQuotients::calculate($hr, $lo, $hi, $level / 100);
            DatabaseManager::saveSurvivalCalculation($result);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$page_title = 'Survival Analysis';
$page_description = 'Free SFQ/SRQ calculator for survival outcomes. Enter a hazard ratio and its confidence interval to get the complete p-fr-nb evidence triplet: significance, fragility, and robustness.';
$canonical_url = 'https://fragilitymetrics.org/survival.php';
include 'includes/header.php';
?>

<hgroup>
    <h1>Complete Statistical Evidence Calculator</h1>
    <p>Calculate p-fr-nb triplets for time-to-event outcomes reported as a hazard ratio</p>
</hgroup>

<div class="calc-tabs">
    Calculate CSE for: <a href="index.php">2x2 Trials</a> | <a href="survival.php" class="active">Survival</a>
</div>
<br/>

<article>
    <h2>Survival (SFQ/SRQ) Calculator</h2>
    <p>Enter a published hazard ratio and its confidence interval to calculate complete statistical evidence (significance, fragility, and robustness).</p>

    <?php if ($error): ?>
        <div class="error"><strong>Error:</strong> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form id="sq-form" method="POST">
        <table>
            <thead>
                <tr>
                    <th>Hazard ratio</th>
                    <th>Lower limit</th>
                    <th>Upper limit</th>
                    <th>CI level (%)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="number" name="hr" id="hr" step="any" min="0" required placeholder="HR" style="width: 100px;"
                               value="<?= isset($_POST['hr']) ? htmlspecialchars($_POST['hr']) : '' ?>"></td>
                    <td><input type="number" name="ci_lower" id="ci_lower" step="any" min="0" required placeholder="lower" style="width: 100px;"
                               value="<?= isset($_POST['ci_lower']) ? htmlspecialchars($_POST['ci_lower']) : '' ?>"></td>
                    <td><input type="number" name="ci_upper" id="ci_upper" step="any" min="0" required placeholder="upper" style="width: 100px;"
                               value="<?= isset($_POST['ci_upper']) ? htmlspecialchars($_POST['ci_upper']) : '' ?>"></td>
                    <td><input type="number" name="ci_level" id="ci_level" step="any" min="50" max="99.99" required placeholder="95" style="width: 100px;"
                               value="<?= isset($_POST['ci_level']) ? htmlspecialchars($_POST['ci_level']) : '95' ?>"></td>
                </tr>
            </tbody>
        </table>

        <button type="submit" style="margin-top: 1rem;">Calculate SFQ and SRQ</button>
    </form>
</article>

<?php if ($result): ?>
<?php
    $lvlPct = rtrim(rtrim(number_format($result['CI_level'] * 100, 2, '.', ''), '0'), '.');
    $pDisp  = $result['p_recovered'] < 0.000001 ? '&lt; 0.000001' : number_format($result['p_recovered'], 6);
    $isNon95 = abs($result['CI_level'] - 0.95) > 1e-9;
?>
<article id="sq-result">
    <h2>Results</h2>

    <?php if ($result['hr_outside_ci']): ?>
        <p style="background:#fff8e1; color:#8a6100; padding:12px; border-radius:8px;">
            <strong>Check the inputs:</strong> the hazard ratio <?= number_format($result['HR'], 4) ?>
            lies outside its own interval (<?= number_format($result['CI_lower'], 4) ?>,
            <?= number_format($result['CI_upper'], 4) ?>). Results are still shown, but one of the
            three numbers is probably mistyped.
        </p>
    <?php endif; ?>

    <h3>Input</h3>
    <p>
        HR = <?= number_format($result['HR'], 4) ?><br>
        <?= $lvlPct ?>% CI = (<?= number_format($result['CI_lower'], 4) ?>, <?= number_format($result['CI_upper'], 4) ?>)
    </p>

    <h3>Significance (p)</h3>
    <p>p (recovered from z) = <?= $pDisp ?></p>

    <h3>Fragility (fr)</h3>
    <p>SFQ (Survival Fragility Quotient) = <?= number_format($result['SFQ'], 4) ?></p>

    <h3>Robustness (nb)</h3>
    <p>SRQ (Survival Robustness Quotient) = <?= number_format($result['SRQ'], 4) ?></p>

    <?php if ($isNon95): ?>
    <h3>Implied 95% Interval</h3>
    <p>Nominal 95% CI = (<?= number_format($result['CI95_lower'], 4) ?>, <?= number_format($result['CI95_upper'], 4) ?>)</p>
    <?php endif; ?>
</article>
<?php endif; ?>

<article>
    <summary>Example: Immunotherapy Trial</summary>
    <p>A trial reporting overall survival for an immune checkpoint inhibitor versus standard therapy:</p>
    <ul>
        <li>Hazard ratio for death: 0.70</li>
        <li>95% confidence interval: 0.55 to 0.89</li>
    </ul>
    <p>Enter: HR = 0.70, lower = 0.55, upper = 0.89, CI level = 95</p>
    <p>Returns p = 0.003674, SFQ = 0.4858, SRQ = 0.2629. The result is significant. Its z-statistic (|z| = 2.90)
       sits 0.94 beyond the 1.96 significance boundary, which SFQ rescales to 0–1; a higher SFQ means a more stable
       classification. Its log hazard ratio sits 0.36 from neutrality (HR = 1), which SRQ rescales to 0–1; a higher
       SRQ means farther from neutrality.</p>
    <p style="font-size:13px; color:#555;">If the paper reports its interval at a level other than 95% (an
       interim analysis with alpha spending may report 97.73% or 99%), enter that level instead. The standard
       error is derived from the level you enter.</p>
</article>

<article>
    <h2>Why Complete Evidence?</h2>
    <p>A hazard ratio with a p-value is partial evidence. It says whether an effect cleared the significance threshold, not how close it sits to that threshold, and not how far it sits from no effect at all. Complete statistical evidence requires three complementary dimensions:</p>
    <ul>
        <li><strong>Significance (p):</strong> Compatibility with null hypothesis</li>
        <li><strong>Fragility (fr):</strong> Classification stability (SFQ)</li>
        <li><strong>Robustness (nb):</strong> Distance from neutrality, HR = 1 (SRQ)</li>
    </ul>
    <p>Only when all three dimensions align do you have truly convincing, replication-ready evidence. Clinical decisions also need the absolute effect size, such as months of survival gained: the triplet plus effect size is complete evidence.</p>
    <p>SFQ needs the hazard ratio and its confidence interval, because the interval is what carries the standard error. SRQ needs the hazard ratio alone. Neither requires raw survival data, Kaplan-Meier curves, censoring information, or patient-level data.</p>
</article>

<p><a href="documentation.php" role="button">Read Full Documentation</a></p>

<p style="font-size:13px; color:#555; margin-bottom:6px;">
  <strong>Note:</strong> SFQ assumes the published interval is a symmetric Wald interval on the log scale; SRQ uses the hazard ratio alone and makes no assumption about the interval. Compare the recovered p-value against the one printed in the paper: a close match confirms the assumption, and a visible discrepancy means the interval was stratified, exact, or group-sequential and the inputs need checking. SFQ anchors on z = 1.96 by definition, so for a trial stopped at an alpha-adjusted interim boundary it measures distance to the conventional boundary rather than the stricter one the trial had to clear. Delayed curve separation, common in immunotherapy trials, makes the hazard ratio an average over time; SFQ and SRQ inherit whatever the hazard ratio means in that setting. The framework has not published numeric fragility cutoffs (they are under validation), and its numeric robustness bands (near, intermediate, far) are defined for RQ only, so no band labels are applied to SFQ or SRQ.
</p>
<p style="font-size:14px; color:#666;">
  <strong>Citation:</strong> Heston TF. Fragility metrics toolkit v6.0.0. Zenodo. 2026. DOI: <a href="https://doi.org/10.5281/zenodo.17254763">10.5281/zenodo.17254763</a>
</p>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebApplication",
      "@id": "https://fragilitymetrics.org/survival.php#calculator",
      "name": "Survival (SFQ/SRQ) Calculator",
      "url": "https://fragilitymetrics.org/survival.php",
      "applicationCategory": "HealthApplication",
      "operatingSystem": "Any",
      "offers": { "@type": "Offer", "price": "0", "priceCurrency": "USD" },
      "description": "Free calculator for time-to-event outcomes. Enter a published hazard ratio and its confidence interval to get the complete p-fr-nb evidence triplet: significance (p), fragility (SFQ), and robustness (SRQ). No patient-level data required.",
      "author": { "@id": "https://orcid.org/0000-0002-5655-2512" }
    },
    {
      "@type": "DefinedTerm",
      "@id": "https://fragilitymetrics.org/survival.php#sfq",
      "name": "Survival Fragility Quotient",
      "alternateName": "SFQ",
      "termCode": "SFQ",
      "description": "The fragility (fr) metric for time-to-event outcomes: the distance of the Cox z-statistic from the 1.96 significance boundary, rescaled to a 0-to-1 scale (SFQ = d / (1 + d), where d = ||z| - 1.96|). A higher SFQ means a more stable significance classification. Computed from a published hazard ratio and its confidence interval; no patient-level data required.",
      "inDefinedTermSet": { "@id": "https://fragilitymetrics.org/documentation.php#terms" }
    },
    {
      "@type": "DefinedTerm",
      "@id": "https://fragilitymetrics.org/survival.php#srq",
      "name": "Survival Robustness Quotient",
      "alternateName": "SRQ",
      "termCode": "SRQ",
      "description": "The robustness (nb) metric for time-to-event outcomes: the distance of the log hazard ratio from therapeutic neutrality (HR = 1), rescaled to a 0-to-1 scale (SRQ = |ln(HR)| / (1 + |ln(HR)|)). A higher SRQ means the estimate sits farther from neutrality. Computed from the hazard ratio alone.",
      "inDefinedTermSet": { "@id": "https://fragilitymetrics.org/documentation.php#terms" }
    },
    {
      "@type": "DefinedTermSet",
      "@id": "https://fragilitymetrics.org/documentation.php#terms",
      "name": "Fragility Metrics: the p–fr–nb framework",
      "url": "https://fragilitymetrics.org/documentation.php"
    },
    {
      "@type": "Person",
      "@id": "https://orcid.org/0000-0002-5655-2512",
      "name": "Thomas F. Heston",
      "sameAs": [
        "https://orcid.org/0000-0002-5655-2512",
        "https://github.com/tomheston"
      ]
    }
  ]
}
</script>

<?php include 'includes/footer.php'; ?>

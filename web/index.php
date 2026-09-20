<?php
$page_title = 'Statistical Fragility Calculator';
$canonical_url = 'https://fragilitymetrics.org/';
include 'includes/header.php';
?>

<hgroup>
    <h1>Complete Statistical Evidence Calculator</h1>
    <p>Calculate p-fr-nb triplets for 2×2 independent binary outcome trials</p>
</hgroup>

<div class="calc-tabs">
    Calculate CSE for: <a href="index.php" class="active">2x2 Trials</a> | <a href="survival.php">Survival</a>
</div>
<br/>

<article>
    <h2>2×2 Table Calculator</h2>
    <p>Enter your trial data to calculate complete statistical evidence (significance, fragility, and robustness).</p>

    <form method="POST" action="calculate.php">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>Events</th>
                    <th>Non-events</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Group A (Treatment)</strong></td>
                    <td><input type="number" name="a" min="0" required placeholder="a" style="width: 100px;"></td>
                    <td><input type="number" name="b" min="0" required placeholder="b" style="width: 100px;"></td>
                </tr>
                <tr>
                    <td><strong>Group B (Control)</strong></td>
                    <td><input type="number" name="c" min="0" required placeholder="c" style="width: 100px;"></td>
                    <td><input type="number" name="d" min="0" required placeholder="d" style="width: 100px;"></td>
                </tr>
            </tbody>
        </table>

        <button type="submit" style="margin-top: 1rem;">Calculate p-fr-nb Triplet</button>
    </form>
</article>

<article>
    <summary>Example: Treatment vs Control</summary>
    <p>A clinical trial comparing a new treatment to standard care:</p>
    <ul>
        <li>Group A (Treatment): 15 events, 85 non-events</li>
        <li>Group B (Control): 25 events, 75 non-events</li>
    </ul>
    <p>Enter: a=15, b=85, c=25, d=75</p>
</article>

<article>
    <h2>Why Complete Evidence?</h2>
    <p>Traditional reporting provides only p-values and confidence intervals — this is partial evidence. Complete statistical evidence requires three complementary dimensions:</p>
    <ul>
        <li><strong>Significance (p):</strong> Compatibility with null hypothesis</li>
        <li><strong>Fragility (fr):</strong> Classification stability (<a href="global-fragility-index.php">GFQ</a>, or MFQ when the trial is too large for the exact GFI search)</li>
        <li><strong>Robustness (nb):</strong> Distance from neutrality (RQ, with NDI as its optional count form)</li>
    </ul>
    <p>Only when all three dimensions align do you have truly convincing, replication-ready evidence. Clinical decisions also need the absolute effect size, such as the absolute risk reduction or number needed to treat: the triplet plus effect size is complete evidence.</p>
    <p>The calculator also reports <strong>resampling fragility</strong> — the probability that the significance classification would reverse in a new sample of the same size under a stated replication model — as its own output category. Resampling fragility is a form of data fragility, but because it depends on a replication model it sits outside the model-free p–fr–nb triplet.</p>
</article>

<p><a href="documentation.php" role="button">Read Full Documentation</a></p>

<p style="font-size:13px; color:#555; margin-bottom:6px;">
  <strong>Note:</strong> The FI used here is the Heston Fragility Index: the minimum number of outcome toggles (event ↔ non-event) in the arm with fewer events, or the smaller arm if events are tied, that flips significance in either direction, so it is also defined for nonsignificant results. For the original Walsh 2014 FI definition, see the <a href="http://fragilitymetrics.org/calculate_original.php">Walsh 2014 FI Calculator</a>.
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
      "@id": "https://fragilitymetrics.org/#calculator",
      "name": "Complete Statistical Evidence Calculator",
      "url": "https://fragilitymetrics.org/",
      "applicationCategory": "HealthApplication",
      "operatingSystem": "Any",
      "offers": { "@type": "Offer", "price": "0", "priceCurrency": "USD" },
      "description": "Free calculator that reports the complete p-fr-nb evidence triplet for 2x2 independent binary outcome trials: statistical significance (p), fragility (fr), and robustness (nb).",
      "author": {
        "@type": "Person",
        "name": "Thomas F. Heston",
        "url": "https://fragilitymetrics.org/about.php"
      }
    },
    {
      "@type": "DefinedTermSet",
      "@id": "https://fragilitymetrics.org/#fragility-terms",
      "name": "Heston Fragility and Robustness Metrics (p-fr-nb framework)",
      "url": "https://fragilitymetrics.org/documentation.php",
      "description": "Metrics of the p-fr-nb framework for complete statistical evidence in clinical trials: significance (p), fragility (fr, stability of the significance classification), and robustness (nb, distance from therapeutic neutrality). Fragility and robustness are two separate axes, not opposite ends of one scale.",
      "hasDefinedTerm": [
        {
          "@type": "DefinedTerm",
          "termCode": "fr",
          "name": "Fragility (fr)",
          "description": "How much the data must change before the statistical-significance classification flips. Unstable means a small change flips the classification; stable means a larger change is required. Reported as a design-specific fragility quotient from 0 to 1, where lower values indicate an unstable classification.",
          "inDefinedTermSet": "https://fragilitymetrics.org/#fragility-terms"
        },
        {
          "@type": "DefinedTerm",
          "termCode": "nb",
          "name": "Robustness (nb)",
          "description": "Geometric distance from therapeutic neutrality (for example RR = 1 or mean difference = 0), expressed as a bounded, sign-agnostic standardized effect magnitude from 0 to 1, computed from the point estimate rather than its precision. Banded as near, intermediate, or far from neutrality. Robustness describes separation from no effect, not whether the result is good or bad.",
          "inDefinedTermSet": "https://fragilitymetrics.org/#fragility-terms"
        },
        {
          "@type": "DefinedTerm",
          "termCode": "GFQ",
          "name": "Global Fragility Quotient (GFQ)",
          "description": "GFI divided by N: the minimum number of cell-to-cell reallocations needed to flip the significance classification of a contingency table, as a proportion of sample size. The recommended default fragility quotient for binary and multinomial tables; path-independent and label-invariant.",
          "inDefinedTermSet": "https://fragilitymetrics.org/#fragility-terms"
        },
        {
          "@type": "DefinedTerm",
          "termCode": "MFQ",
          "name": "Modified-arm Fragility Quotient (MFQ)",
          "description": "The Heston Fragility Index divided by the size of the arm actually modified. Fallback fragility quotient for two-arm binary trials when the exact GFI search is computationally intractable at large sample sizes; allocation-fair and label-resistant.",
          "inDefinedTermSet": "https://fragilitymetrics.org/#fragility-terms"
        },
        {
          "@type": "DefinedTerm",
          "termCode": "RQ",
          "name": "Risk Quotient (RQ)",
          "description": "Primary robustness metric for independent-sample binary and multinomial tables. For any 2x2 table, RQ = |ad - bc| / (N^2 / 4), a scale-invariant measure of distance from therapeutic neutrality on a 0-to-1 scale.",
          "inDefinedTermSet": "https://fragilitymetrics.org/#fragility-terms"
        },
        {
          "@type": "DefinedTerm",
          "termCode": "NDI",
          "name": "Neutrality Distance Index (NDI)",
          "description": "Integer counterpart to the Risk Quotient: the minimum number of coupled fixed-margin moves needed to bring a 2x2 table to the point closest to RR = 1, computed as round(|ad - bc| / N) and clamped to reachability. Test-independent count form of robustness.",
          "inDefinedTermSet": "https://fragilitymetrics.org/#fragility-terms"
        }
      ]
    }
  ]
}
</script>

<?php include 'includes/footer.php'; ?>

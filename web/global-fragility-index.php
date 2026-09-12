<?php
// global-fragility-index.php
// Definitional page for the Global Fragility Index (GFI).
// The JSON-LD at the bottom must mirror the visible definition, disambiguation,
// and FAQ text word for word; edit both together.
$page_title = 'What is the Global Fragility Index (GFI)?';
$page_description = 'The Global Fragility Index (GFI) is the minimum number of patients that must be moved between cells of a trial table, sample size fixed, to reverse its statistical significance.';
$canonical_url = 'https://fragilitymetrics.org/global-fragility-index.php';
include 'includes/header.php';
?>
<style>
  .gfi-definition { font-size: 1.1rem; }
  .gfi-disambiguation { background: #f7f7f7; border-left: 4px solid #0066cc; padding: 0.75rem 1rem; color: #444; }
  .gfi-table-wrap { overflow-x: auto; }
  .gfi-table-wrap td, .gfi-table-wrap th { white-space: nowrap; }
  .gfi-small { font-size: 13px; color: #555; }
</style>

<h1>What is the Global Fragility Index?</h1>

<p id="definition" class="gfi-definition"><strong>The Global Fragility Index (GFI)</strong> is the minimum number of patients who must be moved between the cells of a clinical trial's contingency table, with the total sample size held fixed, to reverse the trial's statistical significance: a significant result becomes nonsignificant, or a nonsignificant result becomes significant. Unlike the original Fragility Index, which changes outcomes in one treatment arm only, the GFI searches every possible reallocation across all cells, so its value does not depend on the path taken or on how the groups are labeled. Dividing by the sample size gives the Global Fragility Quotient (GFQ = GFI / N). The GFI was defined by Thomas F. Heston in a 2025 SSRN paper.</p>

<p id="disambiguation" class="gfi-disambiguation">This Global Fragility Index is a biostatistics measure of clinical trial evidence, not the geopolitical Fragile States Index or any other ranking of fragile countries.</p>

<h2>Formal definition</h2>
<p>For a 2×2 table with Arm A events <em>a</em> and non-events <em>b</em>, Arm B events <em>c</em> and non-events <em>d</em>, and total N = a + b + c + d:</p>
<ul>
    <li><strong>Move:</strong> take one patient out of one cell and place them in another. N stays fixed and no cell may go below zero. A move within an arm switches an outcome (event ↔ non-event); a move across arms reallocates a patient between groups.</li>
    <li><strong>GFI:</strong> the smallest number of moves that changes whether p &lt; α. Significant means p &lt; 0.05 by the two-sided Fisher's exact test, applied to the observed table and to every rearranged table.</li>
    <li><strong>GFQ:</strong> GFI / N, a proportion between 0 and 1.</li>
    <li><strong>Ordering:</strong> GFI ≤ FI under the same test, because every within-arm toggle counted by the Fragility Index is also a GFI move.</li>
    <li><strong>Undefined case:</strong> if no rearrangement of the N patients can change the verdict, which happens only in very small trials, the GFI is undefined.</li>
</ul>
<p>The moves are a measuring device, not a claim that any error occurred. The GFI is the distance, in patients, between the observed table and the nearest table on the other side of the significance boundary.</p>

<h2>Worked examples</h2>
<p>Three trials with 100 patients per arm (N = 200):</p>
<div class="gfi-table-wrap">
<table>
    <thead>
        <tr>
            <th>Trial {a, b, c, d}</th>
            <th>Event rates (A vs B)</th>
            <th>Baseline p</th>
            <th>GFI</th>
            <th>GFQ</th>
            <th>Example table after GFI moves</th>
            <th>p after</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>{2, 98, 10, 90}</td><td>2% vs 10%</td><td>0.033</td><td>1</td><td>0.005</td><td>{2, 98, 9, 91}</td><td>0.058</td></tr>
        <tr><td>{50, 50, 10, 90}</td><td>50% vs 10%</td><td>&lt; 0.000001</td><td>20</td><td>0.100</td><td>{31, 50, 30, 89}</td><td>0.061</td></tr>
        <tr><td>{15, 85, 25, 75}</td><td>15% vs 25%</td><td>0.111</td><td>2</td><td>0.010</td><td>{14, 84, 27, 75}</td><td>0.037</td></tr>
    </tbody>
</table>
</div>
<p>In the first trial, recording one Arm B event as a non-event removes significance, so 0.5% of the sample separates the verdict from its reversal. The second trial holds until 20 patients, 10% of the sample, are moved. The third is the calculator's home-page example: it is nonsignificant, and moving two Arm A patients into the Arm B event cell makes it significant. Several tables can sit at the same minimum distance; each row shows one of them.</p>
<p class="gfi-small">p-values are two-sided Fisher's exact. Each GFI was checked by scoring all 1,373,701 possible 2×2 tables with N = 200.</p>

<h2>Where the GFI fits</h2>
<p>A p-value alone is partial evidence. The p–fr–nb framework reports three dimensions: significance (p), fragility (fr), and robustness (nb). For 2×2 and multinomial tables the GFQ is the recommended fragility metric, paired with the Risk Quotient (RQ), which measures distance from neutrality. When a very large sample makes the exact GFI search intractable, the Modified-Arm Fragility Quotient (MFQ) is the fallback. See the <a href="documentation.php">full documentation</a>.</p>

<section id="faq">
<h2>Frequently asked questions</h2>

<h3>What does the Global Fragility Index measure?</h3>
<p>The GFI measures how far a trial's significance verdict sits from being reversed, counted in patients. A GFI of 1 means that moving a single patient between two cells of the table moves the result across the p = 0.05 threshold; a large GFI means the verdict holds under substantial changes to the data. It is a count, not a probability.</p>

<h3>How is the Global Fragility Index different from the Fragility Index?</h3>
<p>The Fragility Index (Walsh et al., 2014) switches outcomes between event and non-event within one treatment arm only. The GFI allows a patient to be moved between any two cells of the table, including across arms, while the total sample size stays fixed. Because every Fragility Index move is also a GFI move, the GFI is never larger than the Fragility Index when both use the same significance test, and it does not depend on which arm is chosen or how the groups are labeled.</p>

<h3>What is the Global Fragility Quotient (GFQ)?</h3>
<p>The Global Fragility Quotient is the GFI divided by the total sample size: GFQ = GFI / N. It expresses fragility as the proportion of the sample that must be reallocated to reverse significance, so a GFQ of 0.03 means 3% of patients. The GFQ allows comparison across trials of different sizes and is the recommended fragility (fr) metric for 2×2 and multinomial tables in the p–fr–nb framework.</p>

<h3>Which significance test does the GFI use?</h3>
<p>For 2×2 tables, the GFI always uses the two-sided Fisher's exact test at α = 0.05, whatever test the original trial reported, and the same test scores every rearranged table. For larger tables, the Fisher–Freeman–Halton exact test is preferred, with Pearson's chi-square as the fallback when exact computation is intractable and expected counts are adequate. GFI values computed under different tests should not be compared directly.</p>

<h3>Does the GFI apply to nonsignificant results?</h3>
<p>Yes. For a nonsignificant result, the GFI is the minimum number of moves that makes the result significant. One definition covers both directions, so no separate reverse fragility index is needed.</p>

<h3>Is the Global Fragility Index just the p-value in disguise?</h3>
<p>No. Resampling fragility, the probability that a new sample of the same size would reverse the verdict, is strongly associated with the p-value. The GFI measures perturbation fragility instead: the number of recorded outcomes that must change before the observed table crosses the threshold. In three pairs of published trials matched on p-value, the GFQ differed 21-fold, 30-fold, and 2-fold within pairs (Heston, Internet Medical Journal, 2026).</p>

<h3>Is the Global Fragility Index related to the Fragile States Index?</h3>
<p>No. The Global Fragility Index described here is a statistical measure of clinical trial results. It has no connection to the Fragile States Index, other geopolitical rankings of state fragility, or the U.S. Global Fragility Act.</p>

<h3>How do I calculate the Global Fragility Index?</h3>
<p>Enter the four cell counts of a 2×2 table in the <a href="index.php">free calculator</a> at fragilitymetrics.org. It reports the GFI and GFQ alongside the other p–fr–nb metrics, using an exact search scored by Fisher's exact test. The open-source Fragility Metrics Toolkit on GitHub and Zenodo (doi:10.5281/zenodo.17254763) is the reference implementation.</p>

<h3>Who developed the Global Fragility Index?</h3>
<p>Thomas F. Heston, MD, of the University of Washington and Washington State University, defined the GFI in “The Global Fragility Index: A Path-Independent Measure of Statistical Fragility” (SSRN, 2025). The paper defines the index for multinomial tables and proves that the global cell-move distance to the significance boundary is path-independent.</p>
</section>

<h2>References</h2>
<ol>
    <li>Heston TF. The Global Fragility Index: a path-independent measure of statistical fragility. SSRN. 2025. <a href="https://doi.org/10.2139/ssrn.5709162">doi:10.2139/ssrn.5709162</a></li>
    <li>Heston TF. Resampling fragility, perturbation fragility, and why the Global Fragility Index is not a p-value in disguise. Internet Med J. 2026;1:e22059146. <a href="https://doi.org/10.5281/zenodo.22059146">doi:10.5281/zenodo.22059146</a></li>
    <li>Walsh M, Srinathan SK, McAuley DF, et al. The statistical significance of randomized controlled trial results is frequently fragile: a case for a Fragility Index. J Clin Epidemiol. 2014;67(6):622-8. <a href="https://doi.org/10.1016/j.jclinepi.2013.10.019">doi:10.1016/j.jclinepi.2013.10.019</a></li>
    <li>Heston TF. Fragility metrics toolkit v6.0.0. Zenodo. 2026. <a href="https://doi.org/10.5281/zenodo.17254763">doi:10.5281/zenodo.17254763</a></li>
</ol>

<p><a href="index.php" role="button">Calculate the GFI for a trial</a></p>

<p style="font-size:14px; color:#666;">
  <strong>Cite the GFI as:</strong> Heston TF. The Global Fragility Index: a path-independent measure of statistical fragility. SSRN. 2025. doi:<a href="https://doi.org/10.2139/ssrn.5709162">10.2139/ssrn.5709162</a>
</p>
<p class="gfi-small">Last updated September 12, 2026.</p>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "FAQPage",
      "@id": "https://fragilitymetrics.org/global-fragility-index.php#webpage",
      "url": "https://fragilitymetrics.org/global-fragility-index.php",
      "name": "What is the Global Fragility Index?",
      "description": "The Global Fragility Index (GFI) is the minimum number of patients that must be moved between cells of a trial table, sample size fixed, to reverse its statistical significance.",
      "inLanguage": "en",
      "datePublished": "2026-09-12",
      "dateModified": "2026-09-12",
      "isPartOf": {
        "@type": "WebSite",
        "@id": "https://fragilitymetrics.org/#website",
        "name": "Fragility Metrics",
        "url": "https://fragilitymetrics.org/"
      },
      "about": { "@id": "https://fragilitymetrics.org/global-fragility-index.php#gfi" },
      "author": { "@id": "https://orcid.org/0000-0002-5655-2512" },
      "citation": [
        { "@id": "https://doi.org/10.2139/ssrn.5709162" },
        { "@id": "https://doi.org/10.5281/zenodo.22059146" },
        { "@id": "https://doi.org/10.1016/j.jclinepi.2013.10.019" },
        { "@id": "https://doi.org/10.5281/zenodo.17254763" }
      ],
      "mainEntity": [
        {
          "@type": "Question",
          "name": "What does the Global Fragility Index measure?",
          "acceptedAnswer": { "@type": "Answer", "text": "The GFI measures how far a trial's significance verdict sits from being reversed, counted in patients. A GFI of 1 means that moving a single patient between two cells of the table moves the result across the p = 0.05 threshold; a large GFI means the verdict holds under substantial changes to the data. It is a count, not a probability." }
        },
        {
          "@type": "Question",
          "name": "How is the Global Fragility Index different from the Fragility Index?",
          "acceptedAnswer": { "@type": "Answer", "text": "The Fragility Index (Walsh et al., 2014) switches outcomes between event and non-event within one treatment arm only. The GFI allows a patient to be moved between any two cells of the table, including across arms, while the total sample size stays fixed. Because every Fragility Index move is also a GFI move, the GFI is never larger than the Fragility Index when both use the same significance test, and it does not depend on which arm is chosen or how the groups are labeled." }
        },
        {
          "@type": "Question",
          "name": "What is the Global Fragility Quotient (GFQ)?",
          "acceptedAnswer": { "@type": "Answer", "text": "The Global Fragility Quotient is the GFI divided by the total sample size: GFQ = GFI / N. It expresses fragility as the proportion of the sample that must be reallocated to reverse significance, so a GFQ of 0.03 means 3% of patients. The GFQ allows comparison across trials of different sizes and is the recommended fragility (fr) metric for 2×2 and multinomial tables in the p–fr–nb framework." }
        },
        {
          "@type": "Question",
          "name": "Which significance test does the GFI use?",
          "acceptedAnswer": { "@type": "Answer", "text": "For 2×2 tables, the GFI always uses the two-sided Fisher's exact test at α = 0.05, whatever test the original trial reported, and the same test scores every rearranged table. For larger tables, the Fisher–Freeman–Halton exact test is preferred, with Pearson's chi-square as the fallback when exact computation is intractable and expected counts are adequate. GFI values computed under different tests should not be compared directly." }
        },
        {
          "@type": "Question",
          "name": "Does the GFI apply to nonsignificant results?",
          "acceptedAnswer": { "@type": "Answer", "text": "Yes. For a nonsignificant result, the GFI is the minimum number of moves that makes the result significant. One definition covers both directions, so no separate reverse fragility index is needed." }
        },
        {
          "@type": "Question",
          "name": "Is the Global Fragility Index just the p-value in disguise?",
          "acceptedAnswer": { "@type": "Answer", "text": "No. Resampling fragility, the probability that a new sample of the same size would reverse the verdict, is strongly associated with the p-value. The GFI measures perturbation fragility instead: the number of recorded outcomes that must change before the observed table crosses the threshold. In three pairs of published trials matched on p-value, the GFQ differed 21-fold, 30-fold, and 2-fold within pairs (Heston, Internet Medical Journal, 2026)." }
        },
        {
          "@type": "Question",
          "name": "Is the Global Fragility Index related to the Fragile States Index?",
          "acceptedAnswer": { "@type": "Answer", "text": "No. The Global Fragility Index described here is a statistical measure of clinical trial results. It has no connection to the Fragile States Index, other geopolitical rankings of state fragility, or the U.S. Global Fragility Act." }
        },
        {
          "@type": "Question",
          "name": "How do I calculate the Global Fragility Index?",
          "acceptedAnswer": { "@type": "Answer", "text": "Enter the four cell counts of a 2×2 table in the free calculator at fragilitymetrics.org. It reports the GFI and GFQ alongside the other p–fr–nb metrics, using an exact search scored by Fisher's exact test. The open-source Fragility Metrics Toolkit on GitHub and Zenodo (doi:10.5281/zenodo.17254763) is the reference implementation." }
        },
        {
          "@type": "Question",
          "name": "Who developed the Global Fragility Index?",
          "acceptedAnswer": { "@type": "Answer", "text": "Thomas F. Heston, MD, of the University of Washington and Washington State University, defined the GFI in “The Global Fragility Index: A Path-Independent Measure of Statistical Fragility” (SSRN, 2025). The paper defines the index for multinomial tables and proves that the global cell-move distance to the significance boundary is path-independent." }
        }
      ]
    },
    {
      "@type": "DefinedTerm",
      "@id": "https://fragilitymetrics.org/global-fragility-index.php#gfi",
      "name": "Global Fragility Index",
      "alternateName": "GFI",
      "termCode": "GFI",
      "url": "https://fragilitymetrics.org/global-fragility-index.php#definition",
      "description": "The Global Fragility Index (GFI) is the minimum number of patients who must be moved between the cells of a clinical trial's contingency table, with the total sample size held fixed, to reverse the trial's statistical significance: a significant result becomes nonsignificant, or a nonsignificant result becomes significant. Unlike the original Fragility Index, which changes outcomes in one treatment arm only, the GFI searches every possible reallocation across all cells, so its value does not depend on the path taken or on how the groups are labeled. Dividing by the sample size gives the Global Fragility Quotient (GFQ = GFI / N). The GFI was defined by Thomas F. Heston in a 2025 SSRN paper.",
      "disambiguatingDescription": "This Global Fragility Index is a biostatistics measure of clinical trial evidence, not the geopolitical Fragile States Index or any other ranking of fragile countries.",
      "inDefinedTermSet": { "@id": "https://fragilitymetrics.org/documentation.php#terms" },
      "subjectOf": { "@id": "https://doi.org/10.2139/ssrn.5709162" }
    },
    {
      "@type": "DefinedTerm",
      "@id": "https://fragilitymetrics.org/global-fragility-index.php#gfq",
      "name": "Global Fragility Quotient",
      "alternateName": "GFQ",
      "termCode": "GFQ",
      "description": "The Global Fragility Index divided by the total sample size (GFQ = GFI / N): the proportion of the sample that must be reallocated to reverse statistical significance.",
      "inDefinedTermSet": { "@id": "https://fragilitymetrics.org/documentation.php#terms" },
      "subjectOf": { "@id": "https://doi.org/10.2139/ssrn.5709162" }
    },
    {
      "@type": "DefinedTermSet",
      "@id": "https://fragilitymetrics.org/documentation.php#terms",
      "name": "Fragility Metrics: the p–fr–nb framework",
      "url": "https://fragilitymetrics.org/documentation.php"
    },
    {
      "@type": "ScholarlyArticle",
      "@id": "https://doi.org/10.2139/ssrn.5709162",
      "name": "The Global Fragility Index: A Path-Independent Measure of Statistical Fragility",
      "headline": "The Global Fragility Index: A Path-Independent Measure of Statistical Fragility",
      "author": { "@id": "https://orcid.org/0000-0002-5655-2512" },
      "datePublished": "2025",
      "publisher": { "@type": "Organization", "name": "SSRN" },
      "url": "https://www.ssrn.com/abstract=5709162",
      "sameAs": "https://doi.org/10.2139/ssrn.5709162",
      "identifier": { "@type": "PropertyValue", "propertyID": "DOI", "value": "10.2139/ssrn.5709162" },
      "about": { "@id": "https://fragilitymetrics.org/global-fragility-index.php#gfi" }
    },
    {
      "@type": "ScholarlyArticle",
      "@id": "https://doi.org/10.5281/zenodo.22059146",
      "name": "Resampling Fragility, Perturbation Fragility, and Why the Global Fragility Index Is Not a P-Value in Disguise",
      "headline": "Resampling Fragility, Perturbation Fragility, and Why the Global Fragility Index Is Not a P-Value in Disguise",
      "author": { "@id": "https://orcid.org/0000-0002-5655-2512" },
      "datePublished": "2026",
      "isPartOf": { "@type": "Periodical", "name": "Internet Medical Journal", "issn": "1093-7935" },
      "identifier": { "@type": "PropertyValue", "propertyID": "DOI", "value": "10.5281/zenodo.22059146" },
      "about": { "@id": "https://fragilitymetrics.org/global-fragility-index.php#gfi" }
    },
    {
      "@type": "ScholarlyArticle",
      "@id": "https://doi.org/10.1016/j.jclinepi.2013.10.019",
      "name": "The statistical significance of randomized controlled trial results is frequently fragile: a case for a Fragility Index",
      "datePublished": "2014",
      "isPartOf": { "@type": "Periodical", "name": "Journal of Clinical Epidemiology" },
      "identifier": { "@type": "PropertyValue", "propertyID": "DOI", "value": "10.1016/j.jclinepi.2013.10.019" }
    },
    {
      "@type": "SoftwareSourceCode",
      "@id": "https://doi.org/10.5281/zenodo.17254763",
      "name": "Fragility Metrics Toolkit",
      "author": { "@id": "https://orcid.org/0000-0002-5655-2512" },
      "codeRepository": "https://github.com/tomheston/fragility-metrics",
      "license": "https://creativecommons.org/licenses/by/4.0/",
      "identifier": { "@type": "PropertyValue", "propertyID": "DOI", "value": "10.5281/zenodo.17254763" }
    },
    {
      "@type": "Person",
      "@id": "https://orcid.org/0000-0002-5655-2512",
      "name": "Thomas F. Heston",
      "honorificSuffix": "MD, FAAFP",
      "sameAs": [
        "https://orcid.org/0000-0002-5655-2512",
        "https://github.com/tomheston"
      ],
      "affiliation": [
        { "@type": "CollegeOrUniversity", "name": "University of Washington" },
        { "@type": "CollegeOrUniversity", "name": "Washington State University" }
      ]
    }
  ]
}
</script>

<?php include 'includes/footer.php'; ?>

<?php
// fragility-distance.php
// Definitional page for the Fragility Distance (FD) and the Fragility Distance Quotient (FDQ).
// Definitions follow FRAGILITY_METRICS.md v14.5.4 (Part V, Fragility Distance Family).
// The JSON-LD at the bottom must mirror the visible definition, disambiguation,
// and FAQ text word for word; edit both together.
$page_title = 'Fragility Distance (FD): Clinical Trial Fragility Metric';
$page_description = 'The Fragility Distance (FD) is the minimum number of patients that must be added to or removed from a clinical trial\'s 2×2 table, with sample size free, to reverse its statistical significance.';
$canonical_url = 'https://fragilitymetrics.org/fragility-distance.php';
include 'includes/header.php';
?>
<style>
  .fd-definition { font-size: 1.1rem; }
  .fd-disambiguation { background: #f7f7f7; border-left: 4px solid #0066cc; padding: 0.75rem 1rem; color: #444; }
  .fd-table-wrap { overflow-x: auto; }
  .fd-table-wrap td, .fd-table-wrap th { white-space: nowrap; }
  .fd-small { font-size: 13px; color: #555; }
</style>

<h1>What is the Fragility Distance?</h1>
<p class="fd-small">The unconstrained distance from a clinical trial result to the p = 0.05 boundary, counted in patients.</p>

<p id="definition" class="fd-definition"><strong>The Fragility Distance (FD)</strong> is the minimum number of single-patient edits, each adding one patient to any cell of a clinical trial's 2×2 table or removing one patient from any cell, that moves the trial across p = 0.05 under the two-sided Fisher's exact test: a significant result becomes nonsignificant, or a nonsignificant result becomes significant. Total enrollment, arm sizes, and outcome totals are all free to change. FD is the L1 (taxicab) edit distance from the observed table to the nearest table on the other side of the significance boundary, so every constrained fragility index, including the Fragility Index and the Global Fragility Index, bounds it from above. Dividing by the smallest margin of the observed table gives the Fragility Distance Quotient (FDQ). The FD was defined by Thomas F. Heston in a 2026 Internet Medical Journal article.</p>

<p id="disambiguation" class="fd-disambiguation">This Fragility Distance is a biostatistics measure of clinical trial evidence. In this context FD does not refer to familial dysautonomia or to the Freedman–Diaconis histogram rule.</p>

<h2>Formal definition</h2>
<p>For a 2×2 table with Arm A events <em>a</em> and non-events <em>b</em>, Arm B events <em>c</em> and non-events <em>d</em>, arm sizes n<sub>A</sub> = a + b and n<sub>B</sub> = c + d, and total N = a + b + c + d:</p>
<ul>
    <li><strong>Edit:</strong> add one patient to any cell, or remove one patient from any cell. No cell may go below zero. N, both arm sizes, and both outcome totals are free.</li>
    <li><strong>FD:</strong> the smallest number of edits that changes whether p &lt; 0.05, where significance is the two-sided Fisher's exact test applied to the observed table and to every edited table.</li>
    <li><strong>Witness table:</strong> the edited table that attains the minimum. Report it with FD, together with the mechanism: additions, deletions, or mixed.</li>
    <li><strong>FDQ:</strong> min(1, FD / m), where m = min(n<sub>A</sub>, n<sub>B</sub>, a + c, b + d) is the smallest margin of the observed table. For a significant baseline FD ≤ m always holds, so FDQ lies in (0, 1]. For a nonsignificant baseline FD can exceed m; FDQ is then capped at 1.0 and flagged margin-limited.</li>
    <li><strong>aFD and dFD:</strong> the same search restricted to additions only (N grows) or deletions only (N shrinks). Each is an upper bound on FD.</li>
    <li><strong>Ordering:</strong> FD ≤ aFD, FD ≤ dFD, and FD ≤ 2·GFI, because one reallocation between cells is one deletion plus one addition.</li>
</ul>
<p><strong>Index versus distance.</strong> A fragility index is a constrained path length counted in moves: the Fragility Index toggles outcomes within one arm, and the <a href="global-fragility-index.php">Global Fragility Index</a> moves patients between cells with N fixed. Each move changes two cells, so it costs two edits. A distance removes the constraints and counts single-patient edits. There is exactly one unconstrained minimum, the FD; the indices are its constrained upper bounds.</p>
<p>The edits are a measuring device, not a claim that any error occurred. They price the uncertainty present in every real trial: the enrollment of one more patient, or the loss of one to follow-up.</p>

<h2>Worked examples</h2>
<div class="fd-table-wrap">
<table>
    <thead>
        <tr>
            <th>Trial {a, b, c, d}</th>
            <th>Baseline p</th>
            <th>FD</th>
            <th>Mechanism</th>
            <th>Example witness table</th>
            <th>p after</th>
            <th>m</th>
            <th>FDQ</th>
            <th>GFI</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>Semaglutide vs placebo, NASH cirrhosis {5, 42, 7, 17}</td><td>0.090</td><td>1</td><td>deletion</td><td>{4, 42, 7, 17}</td><td>0.038</td><td>12</td><td>0.083</td><td>1</td></tr>
        <tr><td>{2, 98, 10, 90}</td><td>0.033</td><td>2</td><td>deletions</td><td>{2, 98, 8, 90}</td><td>0.057</td><td>12</td><td>0.167</td><td>1</td></tr>
        <tr><td>{15, 85, 25, 75}</td><td>0.111</td><td>2</td><td>deletions</td><td>{13, 85, 25, 75}</td><td>0.047</td><td>40</td><td>0.050</td><td>2</td></tr>
        <tr><td>{50, 50, 10, 90}</td><td>&lt; 0.000001</td><td>36</td><td>mixed</td><td>{16, 50, 12, 90}</td><td>0.055</td><td>60</td><td>0.600</td><td>20</td></tr>
        <tr><td>{48, 556, 35, 449}</td><td>0.731</td><td>12</td><td>deletions</td><td>{48, 556, 23, 449}</td><td>0.048</td><td>83</td><td>0.145</td><td>8</td></tr>
    </tbody>
</table>
</div>
<p>In the semaglutide trial, 5 of 47 semaglutide patients and 7 of 24 placebo patients improved, and the authors reported no significant difference. Removing one semaglutide-group responder reverses that classification; adding one placebo responder, or removing one placebo non-responder, does the same. The FI and GFI also equal 1, but each of their moves changes two cells, so the table sits half as far from the boundary as those indices suggest. In the second trial FD = 2·GFI: the GFI move is itself a shortest edit path. In the third, FD = 2 while 2·GFI = 4, because removing two Arm A events is a route no fixed-N index can take. The last row is a rare-event table: FD = 12 removes a third of Arm B's 35 events, a perturbation that FD / N = 0.011 would understate; the margin denominator gives FDQ = 12 / 83 = 0.145.</p>
<p class="fd-small">p-values are two-sided Fisher's exact. Each FD was certified by scoring every table within the stated edit distance of the observed table; several tables can sit at the same minimum distance, and each row shows one of them. The semaglutide counts are from Loomba et al. (2023).</p>

<h2>Where the FD fits</h2>
<p>A p-value alone is partial evidence. The p–fr–nb framework reports three dimensions: significance (p), fragility (fr), and robustness (nb). FD is a fragility metric: it measures the stability of the significance classification and says nothing about distance from therapeutic neutrality, which the Risk Quotient (RQ) measures. For 2×2 tables the Global Fragility Quotient (GFQ) remains the recommended fr coordinate; FD and FDQ are secondary metrics reported alongside it, with the witness table and mechanism. The author's position is that FD is the primary measure of perturbation fragility for a 2×2 table because it is the true minimum; this is an interpretive position, not an empirical finding. See the <a href="documentation.php">full documentation</a>.</p>

<section id="faq">
<h2>Frequently asked questions</h2>

<h3>What does the Fragility Distance measure?</h3>
<p>FD measures how far a trial's significance classification sits from being reversed, counted in single patients added or removed. FD = 1 means that one additional or one lost patient, in a particular cell, moves the result across p = 0.05. It is a count, not a probability.</p>

<h3>How is the Fragility Distance different from the Fragility Index and the Global Fragility Index?</h3>
<p>The Fragility Index (Walsh et al., 2014) switches outcomes within one arm, and the Global Fragility Index moves patients between any two cells; both hold the sample size fixed, and each of their moves changes two cells. The FD lets the table grow or shrink one patient at a time. Because every toggle and every transfer is a pair of edits, FD ≤ 2·GFI ≤ 2·FI always holds under the same significance test.</p>

<h3>What is the Fragility Distance Quotient (FDQ)?</h3>
<p>FDQ = min(1, FD / m), where m is the smallest of the four margins of the observed table: the two arm sizes and the two outcome totals. It is the fraction of the trial's scarce resource, the rarer outcome or the smaller arm, that must be added or removed to reverse significance. FDQ is not on the GFQ scale, and the two quotients are not numerically interchangeable. No interpretive bands for FDQ have been set; report the raw FD and the witness table beside it.</p>

<h3>Why does the FDQ divide by the smallest margin rather than by N?</h3>
<p>Dividing by N counts patients who could not have contributed to the reversal, which understates the perturbation in rare-event trials. In the table {48, 556, 35, 449}, FD = 12 is 1.1% of N but a third of one arm's 35 events and 14.5% of all 83 events. The defining article reported the quotient as FD / N; the framework has since replaced that denominator with the smallest margin.</p>

<h3>What does margin-limited mean?</h3>
<p>For a nonsignificant result, the nearest significant table usually requires adding to the scarce margin, so FD can exceed m. FDQ is then capped at 1.0 and flagged margin-limited, and the raw FD and witness table carry the information. For a significant result FD never exceeds m, so the cap never applies.</p>

<h3>Does the Fragility Distance apply to nonsignificant results?</h3>
<p>Yes. For a nonsignificant result, FD is the minimum number of edits that makes the result significant. The search covers both directions of effect, because the nearest significant table can lie across therapeutic neutrality. One definition covers both directions.</p>

<h3>What are aFD and dFD?</h3>
<p>aFD is the minimum number of patients that must be added, and dFD the minimum that must be removed, to reverse significance. aFD = k means that if the trial had enrolled k more patients, with particular outcomes in particular arms, the classification could have changed; dFD = k means that the loss of k particular patients, for example to follow-up, could have done the same. Both are upper bounds on FD.</p>

<h3>Which significance test does the Fragility Distance use?</h3>
<p>The two-sided Fisher's exact test at α = 0.05, applied to the observed table and to every edited table, whatever test the original trial reported.</p>

<h3>How do I calculate the Fragility Distance?</h3>
<p>The calculator on this site does not yet report FD. The computation method, a directed search certified by enumerating every table within the candidate distance, is specified in the framework documentation, FRAGILITY_METRICS.md, in the open-source Fragility Metrics Toolkit (doi:<a href="https://doi.org/10.5281/zenodo.17254763">10.5281/zenodo.17254763</a>).</p>

<h3>Who developed the Fragility Distance?</h3>
<p>Thomas F. Heston, MD, of the University of Washington and Washington State University, defined the FD in “From Fragility Index to Fragility Distance in Clinical Trials” (Internet Medical Journal, 2026). The article shows that the published toggle and transfer indexes are constrained upper bounds on a single geometric quantity.</p>
</section>

<h2>References</h2>
<ol>
    <li>Heston TF. From fragility index to fragility distance in clinical trials. Internet Med J. 2026;1:e22072163. <a href="https://doi.org/10.5281/zenodo.22072163">doi:10.5281/zenodo.22072163</a></li>
    <li>Heston TF. The Global Fragility Index: a path-independent measure of statistical fragility. SSRN. 2025. <a href="https://doi.org/10.2139/ssrn.5709162">doi:10.2139/ssrn.5709162</a></li>
    <li>Walsh M, Srinathan SK, McAuley DF, et al. The statistical significance of randomized controlled trial results is frequently fragile: a case for a Fragility Index. J Clin Epidemiol. 2014;67(6):622-8. <a href="https://doi.org/10.1016/j.jclinepi.2013.10.019">doi:10.1016/j.jclinepi.2013.10.019</a></li>
    <li>Loomba R, Abdelmalek MF, Armstrong MJ, et al. Semaglutide 2·4 mg once weekly in patients with non-alcoholic steatohepatitis-related cirrhosis: a randomised, placebo-controlled phase 2 trial. Lancet Gastroenterol Hepatol. 2023;8:511-22. <a href="https://doi.org/10.1016/S2468-1253(23)00068-7">doi:10.1016/S2468-1253(23)00068-7</a></li>
    <li>Heston TF. Fragility metrics toolkit v6.0.0. Zenodo. 2026. <a href="https://doi.org/10.5281/zenodo.17254763">doi:10.5281/zenodo.17254763</a></li>
</ol>

<p style="font-size:14px; color:#666;">
  <strong>Cite the FD as:</strong> Heston TF. From Fragility Index to Fragility Distance in Clinical Trials. Internet Medical Journal. 2026;1:e22072163. doi:<a href="https://doi.org/10.5281/zenodo.22072163">10.5281/zenodo.22072163</a>
</p>
<p class="fd-small">Last updated November 9, 2026.</p>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "FAQPage",
      "@id": "https://fragilitymetrics.org/fragility-distance.php#webpage",
      "url": "https://fragilitymetrics.org/fragility-distance.php",
      "name": "What is the Fragility Distance?",
      "description": "The Fragility Distance (FD) is the minimum number of patients that must be added to or removed from a clinical trial's 2×2 table, with sample size free, to reverse its statistical significance.",
      "inLanguage": "en",
      "datePublished": "2026-11-09",
      "dateModified": "2026-11-09",
      "isPartOf": {
        "@type": "WebSite",
        "@id": "https://fragilitymetrics.org/#website",
        "name": "Fragility Metrics",
        "url": "https://fragilitymetrics.org/"
      },
      "about": {
        "@id": "https://fragilitymetrics.org/fragility-distance.php#fd"
      },
      "author": {
        "@id": "https://orcid.org/0000-0002-5655-2512"
      },
      "citation": [
        {
          "@id": "https://doi.org/10.5281/zenodo.22072163"
        },
        {
          "@id": "https://doi.org/10.2139/ssrn.5709162"
        },
        {
          "@id": "https://doi.org/10.1016/j.jclinepi.2013.10.019"
        },
        {
          "@id": "https://doi.org/10.1016/S2468-1253(23)00068-7"
        },
        {
          "@id": "https://doi.org/10.5281/zenodo.17254763"
        }
      ],
      "mainEntity": [
        {
          "@type": "Question",
          "name": "What does the Fragility Distance measure?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "FD measures how far a trial's significance classification sits from being reversed, counted in single patients added or removed. FD = 1 means that one additional or one lost patient, in a particular cell, moves the result across p = 0.05. It is a count, not a probability."
          }
        },
        {
          "@type": "Question",
          "name": "How is the Fragility Distance different from the Fragility Index and the Global Fragility Index?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "The Fragility Index (Walsh et al., 2014) switches outcomes within one arm, and the Global Fragility Index moves patients between any two cells; both hold the sample size fixed, and each of their moves changes two cells. The FD lets the table grow or shrink one patient at a time. Because every toggle and every transfer is a pair of edits, FD ≤ 2·GFI ≤ 2·FI always holds under the same significance test."
          }
        },
        {
          "@type": "Question",
          "name": "What is the Fragility Distance Quotient (FDQ)?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "FDQ = min(1, FD / m), where m is the smallest of the four margins of the observed table: the two arm sizes and the two outcome totals. It is the fraction of the trial's scarce resource, the rarer outcome or the smaller arm, that must be added or removed to reverse significance. FDQ is not on the GFQ scale, and the two quotients are not numerically interchangeable. No interpretive bands for FDQ have been set; report the raw FD and the witness table beside it."
          }
        },
        {
          "@type": "Question",
          "name": "Why does the FDQ divide by the smallest margin rather than by N?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Dividing by N counts patients who could not have contributed to the reversal, which understates the perturbation in rare-event trials. In the table {48, 556, 35, 449}, FD = 12 is 1.1% of N but a third of one arm's 35 events and 14.5% of all 83 events. The defining article reported the quotient as FD / N; the framework has since replaced that denominator with the smallest margin."
          }
        },
        {
          "@type": "Question",
          "name": "What does margin-limited mean?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "For a nonsignificant result, the nearest significant table usually requires adding to the scarce margin, so FD can exceed m. FDQ is then capped at 1.0 and flagged margin-limited, and the raw FD and witness table carry the information. For a significant result FD never exceeds m, so the cap never applies."
          }
        },
        {
          "@type": "Question",
          "name": "Does the Fragility Distance apply to nonsignificant results?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Yes. For a nonsignificant result, FD is the minimum number of edits that makes the result significant. The search covers both directions of effect, because the nearest significant table can lie across therapeutic neutrality. One definition covers both directions."
          }
        },
        {
          "@type": "Question",
          "name": "What are aFD and dFD?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "aFD is the minimum number of patients that must be added, and dFD the minimum that must be removed, to reverse significance. aFD = k means that if the trial had enrolled k more patients, with particular outcomes in particular arms, the classification could have changed; dFD = k means that the loss of k particular patients, for example to follow-up, could have done the same. Both are upper bounds on FD."
          }
        },
        {
          "@type": "Question",
          "name": "Which significance test does the Fragility Distance use?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "The two-sided Fisher's exact test at α = 0.05, applied to the observed table and to every edited table, whatever test the original trial reported."
          }
        },
        {
          "@type": "Question",
          "name": "How do I calculate the Fragility Distance?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "The calculator on this site does not yet report FD. The computation method, a directed search certified by enumerating every table within the candidate distance, is specified in the framework documentation, FRAGILITY_METRICS.md, in the open-source Fragility Metrics Toolkit (doi:10.5281/zenodo.17254763)."
          }
        },
        {
          "@type": "Question",
          "name": "Who developed the Fragility Distance?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Thomas F. Heston, MD, of the University of Washington and Washington State University, defined the FD in “From Fragility Index to Fragility Distance in Clinical Trials” (Internet Medical Journal, 2026). The article shows that the published toggle and transfer indexes are constrained upper bounds on a single geometric quantity."
          }
        }
      ]
    },
    {
      "@type": "DefinedTerm",
      "@id": "https://fragilitymetrics.org/fragility-distance.php#fd",
      "name": "Fragility Distance",
      "alternateName": "FD",
      "termCode": "FD",
      "url": "https://fragilitymetrics.org/fragility-distance.php#definition",
      "description": "The Fragility Distance (FD) is the minimum number of single-patient edits, each adding one patient to any cell of a clinical trial's 2×2 table or removing one patient from any cell, that moves the trial across p = 0.05 under the two-sided Fisher's exact test: a significant result becomes nonsignificant, or a nonsignificant result becomes significant. Total enrollment, arm sizes, and outcome totals are all free to change. FD is the L1 (taxicab) edit distance from the observed table to the nearest table on the other side of the significance boundary, so every constrained fragility index, including the Fragility Index and the Global Fragility Index, bounds it from above. Dividing by the smallest margin of the observed table gives the Fragility Distance Quotient (FDQ). The FD was defined by Thomas F. Heston in a 2026 Internet Medical Journal article.",
      "disambiguatingDescription": "This Fragility Distance is a biostatistics measure of clinical trial evidence. In this context FD does not refer to familial dysautonomia or to the Freedman–Diaconis histogram rule.",
      "inDefinedTermSet": {
        "@id": "https://fragilitymetrics.org/documentation.php#terms"
      },
      "subjectOf": {
        "@id": "https://doi.org/10.5281/zenodo.22072163"
      }
    },
    {
      "@type": "DefinedTerm",
      "@id": "https://fragilitymetrics.org/fragility-distance.php#fdq",
      "name": "Fragility Distance Quotient",
      "alternateName": "FDQ",
      "termCode": "FDQ",
      "description": "The Fragility Distance divided by the smallest margin of the observed 2×2 table, FDQ = min(1, FD / m), m = min(n_A, n_B, a + c, b + d): the fraction of the trial's scarce resource, the rarer outcome or the smaller arm, that must be added or removed to reverse statistical significance.",
      "inDefinedTermSet": {
        "@id": "https://fragilitymetrics.org/documentation.php#terms"
      },
      "subjectOf": {
        "@id": "https://doi.org/10.5281/zenodo.22072163"
      }
    },
    {
      "@type": "DefinedTermSet",
      "@id": "https://fragilitymetrics.org/documentation.php#terms",
      "name": "Fragility Metrics: the p–fr–nb framework",
      "url": "https://fragilitymetrics.org/documentation.php"
    },
    {
      "@type": "ScholarlyArticle",
      "@id": "https://doi.org/10.5281/zenodo.22072163",
      "name": "From Fragility Index to Fragility Distance in Clinical Trials",
      "headline": "From Fragility Index to Fragility Distance in Clinical Trials",
      "author": {
        "@id": "https://orcid.org/0000-0002-5655-2512"
      },
      "datePublished": "2026-08-23",
      "isPartOf": {
        "@type": "Periodical",
        "name": "Internet Medical Journal",
        "issn": "1093-7935"
      },
      "url": "https://doi.org/10.5281/zenodo.22072163",
      "sameAs": "https://doi.org/10.5281/zenodo.22072163",
      "identifier": {
        "@type": "PropertyValue",
        "propertyID": "DOI",
        "value": "10.5281/zenodo.22072163"
      },
      "about": {
        "@id": "https://fragilitymetrics.org/fragility-distance.php#fd"
      }
    },
    {
      "@type": "ScholarlyArticle",
      "@id": "https://doi.org/10.2139/ssrn.5709162",
      "name": "The Global Fragility Index: A Path-Independent Measure of Statistical Fragility",
      "author": {
        "@id": "https://orcid.org/0000-0002-5655-2512"
      },
      "datePublished": "2025",
      "publisher": {
        "@type": "Organization",
        "name": "SSRN"
      },
      "identifier": {
        "@type": "PropertyValue",
        "propertyID": "DOI",
        "value": "10.2139/ssrn.5709162"
      }
    },
    {
      "@type": "ScholarlyArticle",
      "@id": "https://doi.org/10.1016/j.jclinepi.2013.10.019",
      "name": "The statistical significance of randomized controlled trial results is frequently fragile: a case for a Fragility Index",
      "datePublished": "2014",
      "isPartOf": {
        "@type": "Periodical",
        "name": "Journal of Clinical Epidemiology"
      },
      "identifier": {
        "@type": "PropertyValue",
        "propertyID": "DOI",
        "value": "10.1016/j.jclinepi.2013.10.019"
      }
    },
    {
      "@type": "ScholarlyArticle",
      "@id": "https://doi.org/10.1016/S2468-1253(23)00068-7",
      "name": "Semaglutide 2·4 mg once weekly in patients with non-alcoholic steatohepatitis-related cirrhosis: a randomised, placebo-controlled phase 2 trial",
      "datePublished": "2023",
      "isPartOf": {
        "@type": "Periodical",
        "name": "The Lancet Gastroenterology & Hepatology"
      },
      "identifier": {
        "@type": "PropertyValue",
        "propertyID": "DOI",
        "value": "10.1016/S2468-1253(23)00068-7"
      }
    },
    {
      "@type": "SoftwareSourceCode",
      "@id": "https://doi.org/10.5281/zenodo.17254763",
      "name": "Fragility Metrics Toolkit",
      "author": {
        "@id": "https://orcid.org/0000-0002-5655-2512"
      },
      "codeRepository": "https://github.com/tomheston/fragility-metrics",
      "license": "https://creativecommons.org/licenses/by/4.0/",
      "identifier": {
        "@type": "PropertyValue",
        "propertyID": "DOI",
        "value": "10.5281/zenodo.17254763"
      }
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
        {
          "@type": "CollegeOrUniversity",
          "name": "University of Washington"
        },
        {
          "@type": "CollegeOrUniversity",
          "name": "Washington State University"
        }
      ]
    }
  ]
}
</script>

<?php include 'includes/footer.php'; ?>

<?php
// index.php
$page_title = 'Home';
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
</article>

<p><a href="documentation.php" role="button">Read Full Documentation</a></p>

<p style="font-size:13px; color:#555; margin-bottom:6px;">
  <strong>Note:</strong> The FI used here is the Heston Fragility Index: the minimum number of outcome toggles (event ↔ non-event) in the arm with fewer events, or the smaller arm if events are tied, that flips significance in either direction, so it is also defined for nonsignificant results. For the original Walsh 2014 FI definition, see the <a href="http://fragilitymetrics.org/calculate_original.php">Walsh 2014 FI Calculator</a>.
</p>
<p style="font-size:14px; color:#666;">
  <strong>Citation:</strong> Heston TF. Fragility metrics toolkit v6.0.0. Zenodo. 2026. DOI: <a href="https://doi.org/10.5281/zenodo.17254763">10.5281/zenodo.17254763</a>
</p>

<?php include 'includes/footer.php'; ?>

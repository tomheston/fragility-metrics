<?php
// about.php
$page_title = 'About';
include 'includes/header.php';
?>

<h1>About Fragility Metrics</h1>

    <h2>The Mission</h2>
    <p>Improve biomedical research reproducibility by having trials report complete statistical evidence (p-fr-nb triplet) as the standard.</p>

    <h2>The Framework</h2>
    <p>The p-fr-nb framework provides a complete assessment of statistical evidence through three orthogonal dimensions:</p>
    <ul>
        <li><strong>Significance:</strong> Traditional p-value</li>
        <li><strong>Fragility:</strong> Classification stability</li>
        <li><strong>Robustness:</strong> Distance from neutrality</li>
    </ul>
    <p>Developed by Thomas F. Heston, MD, FAAFP at the University of Washington and Washington State University.</p>

    <h2>Mathematical Foundation</h2>
    <p>All calculations are model-free, using only published summary statistics. No raw data, simulation, or distributional assumptions required.</p>
    <p>Open source implementation available on GitHub for full transparency and reproducibility.</p>

    <h2>Contact</h2>
    <p>Thomas F. Heston, MD, FAAFP<br/>
    ORCID: <a href="https://orcid.org/0000-0002-5655-2512" target="_blank">0000-0002-5655-2512</a><br/>
    DISCUSSION BOARD: <a href="https://github.com/tomheston/fragility-metrics/discussions/">GitHub Discussions</a></p>


 <?php include 'includes/footer.php'; ?> g
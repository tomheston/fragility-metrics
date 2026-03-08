<?php
// index.php
$page_title = 'Home';
include 'includes/header.php';
?>

<hgroup>
    <h1>Complete Statistical Evidence Calculator</h1>
    <p>Calculate p-fr-nb triplets for 2×2 independent binary outcome trials</p>
</hgroup>

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
    <p>Traditional reporting provides only p-values and confidence intervals — this is partial evidence. Complete statistical evidence requires three orthogonal dimensions:</p>
    <ul>
        <li><strong>Significance (p):</strong> Compatibility with null hypothesis</li>
        <li><strong>Fragility (fr):</strong> Classification stability (MFQ)</li>
        <li><strong>Robustness (nb):</strong> Distance from neutrality (RQ)</li>
    </ul>
    <p>Only when all three dimensions align do you have truly convincing, replication-ready evidence.</p>
</article>

<p><a href="documentation.php" role="button">Read Full Documentation</a></p>

<?php include 'includes/footer.php'; ?>

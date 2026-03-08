<?php
// calculators.php
$page_title = 'Calculator';
include 'includes/header.php';
?>

<h1>2×2 Table Calculator</h1>
<p>Calculate complete statistical evidence (p-fr-nb triplet) for independent binary outcomes</p>

<form method="POST" action="calculate.php">
    <article>
        <h3>Enter Your 2×2 Table</h3>
        
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
                    <td><strong>Group A</strong></td>
                    <td><input type="number" name="a" min="0" required placeholder="a" style="width: 100px;"></td>
                    <td><input type="number" name="b" min="0" required placeholder="b" style="width: 100px;"></td>
                </tr>
                <tr>
                    <td><strong>Group B</strong></td>
                    <td><input type="number" name="c" min="0" required placeholder="c" style="width: 100px;"></td>
                    <td><input type="number" name="d" min="0" required placeholder="d" style="width: 100px;"></td>
                </tr>
            </tbody>
        </table>
    </article>
    
    <article>
        <h3>Quality Assurance</h3>
        <label>
            <input type="checkbox" name="qa_logging" checked>
            Enable quality assurance logging (recommended for accuracy verification)
            <a href="privacy.php">Learn more</a>
        </label>
        <small>Only your 2×2 table and calculated results are stored. Zero identifiable information.</small>
    </article>
    
    <button type="submit">Calculate p-fr-nb</button>
</form>

<details>
    <summary>Example: Treatment vs Control</summary>
    <p>A clinical trial comparing a new treatment to standard care:</p>
    <ul>
        <li>Group A (Treatment): 15 events, 85 non-events</li>
        <li>Group B (Control): 25 events, 75 non-events</li>
    </ul>
    <p>Enter: a=15, b=85, c=25, d=75</p>
</details>

<?php include 'includes/footer.php'; ?>
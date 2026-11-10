<?php
// global-fragility-index-zh.php
// Chinese (Simplified) translation of global-fragility-index.php.
// The JSON-LD at the bottom must mirror the visible definition and FAQ text word for word; edit both together.
$page_title = '全局脆弱性指数（GFI）：临床试验脆弱性指标';
$page_description = '全局脆弱性指数（GFI）是一种临床试验脆弱性指标：在总样本量固定的条件下，使试验列联表的统计显著性发生反转所需在单元格之间移动的最少患者数。';
$canonical_url = 'https://fragilitymetrics.org/global-fragility-index-zh.php';
include 'includes/header.php';
?>
<style>
  .gfi-definition { font-size: 1.1rem; }
  .gfi-disambiguation { background: #f7f7f7; border-left: 4px solid #0066cc; padding: 0.75rem 1rem; color: #444; }
  .gfi-table-wrap { overflow-x: auto; }
  .gfi-table-wrap td, .gfi-table-wrap th { white-space: nowrap; }
  .gfi-small { font-size: 13px; color: #555; }
</style>
<div lang="zh-Hans">
<h1>什么是全局脆弱性指数？</h1>
<p class="gfi-small">一种用于临床试验结果的脆弱性指数，而非对脆弱国家的排名。 · <a href="global-fragility-index.php">English</a></p>

<p id="definition" class="gfi-definition"><strong>全局脆弱性指数（GFI）</strong>是指在总样本量保持不变的条件下，为使临床试验的统计显著性发生反转（显著结果变为不显著，或不显著结果变为显著）而必须在试验列联表各单元格之间移动的最少患者数。原始脆弱性指数只在一个治疗组内改变结局，而 GFI 搜索所有单元格之间一切可能的重新分配，因此其数值既不取决于所走路径，也不取决于各组的标注方式。将 GFI 除以样本量即得到全局脆弱性商（GFQ = GFI / N）。GFI 由 Thomas F. Heston 在 2025 年的一篇 SSRN 论文中提出。</p>

<p id="disambiguation" class="gfi-disambiguation">此处的全局脆弱性指数是评价临床试验证据的生物统计学指标，与地缘政治领域的脆弱国家指数（Fragile States Index）或其他国家脆弱性排名无关。</p>

<h2>正式定义</h2>
<p>对于一个 2×2 表，A 组事件数为 <em>a</em>、非事件数为 <em>b</em>，B 组事件数为 <em>c</em>、非事件数为 <em>d</em>，总数 N = a + b + c + d：</p>
<ul>
    <li><strong>移动：</strong>从一个单元格中取出一名患者，放入另一个单元格。N 保持不变，任何单元格都不得小于零。组内移动改变结局（事件 ↔ 非事件）；跨组移动则在两组之间重新分配患者。</li>
    <li><strong>GFI：</strong>使 p &lt; α 是否成立发生改变所需的最少移动次数。显著性指采用双侧 Fisher 精确检验时 p &lt; 0.05，该检验同时用于观察表和每一个重新排列后的表。</li>
    <li><strong>GFQ：</strong>GFI / N，取值在 0 到 1 之间的比例。</li>
    <li><strong>大小关系：</strong>在同一检验下 GFI ≤ FI，因为脆弱性指数计入的每一次组内结局翻转也都是一次 GFI 移动。</li>
    <li><strong>无定义情形：</strong>如果 N 名患者的任何重新排列都无法改变结论（仅见于极小样本试验），则 GFI 无定义。</li>
</ul>
<p>这些移动只是一种测量工具，并不意味着发生了任何错误。GFI 是观察表与显著性边界另一侧最近的表之间的距离，以患者数计。</p>

<h2>计算示例</h2>
<p>三项每组各 100 名患者的试验（N = 200）：</p>
<div class="gfi-table-wrap">
<table>
    <thead>
        <tr>
            <th>试验 {a, b, c, d}</th>
            <th>事件率（A 组 vs B 组）</th>
            <th>基线 p</th>
            <th>GFI</th>
            <th>GFQ</th>
            <th>经过 GFI 次移动后的示例表</th>
            <th>移动后 p</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>{2, 98, 10, 90}</td><td>2% vs 10%</td><td>0.033</td><td>1</td><td>0.005</td><td>{2, 98, 9, 91}</td><td>0.058</td></tr>
        <tr><td>{50, 50, 10, 90}</td><td>50% vs 10%</td><td>&lt; 0.000001</td><td>20</td><td>0.100</td><td>{31, 50, 30, 89}</td><td>0.061</td></tr>
        <tr><td>{15, 85, 25, 75}</td><td>15% vs 25%</td><td>0.111</td><td>2</td><td>0.010</td><td>{14, 84, 27, 75}</td><td>0.037</td></tr>
    </tbody>
</table>
</div>
<p>在第一项试验中，只要把 B 组的一个事件记录为非事件，显著性就会消失，因此结论与其反转之间仅相隔 0.5% 的样本。第二项试验要到移动 20 名患者（占样本的 10%）才会反转。第三项是计算器主页上的示例：该结果不显著，把 A 组的两名患者移入 B 组的事件单元格即可使其变为显著。可能有多张表处于相同的最小距离上；每一行只列出其中一张。</p>
<p class="gfi-small">p 值均为双侧 Fisher 精确检验结果。每个 GFI 都通过对 N = 200 的全部 1,373,701 张 2×2 表逐一评分进行了核验。</p>

<h2>GFI 在框架中的位置</h2>
<p>单凭 p 值只是部分证据。p–fr–nb 框架报告三个维度：显著性（p）、脆弱性（fr）和稳健性（nb）。对于 2×2 表和多分类表，GFQ 是推荐的脆弱性指标，并与衡量与中性点距离的风险商（RQ）配对使用。当需要与脆弱性指数的计数保持可比时，可改用修正组脆弱性商（MFQ）；仅样本量大并不需要改用 MFQ，因为在大样本下 GFI 仍然可以计算。<a href="fragility-distance-zh.php">脆弱性距离（FD）</a>去除了总样本量固定的约束，且始终不超过 GFI 的两倍：FD ≤ 2·GFI。参见<a href="documentation.php">完整文档（英文）</a>。</p>

<section id="faq">
<h2>常见问题</h2>

<h3>全局脆弱性指数衡量什么？</h3>
<p>GFI 衡量一项试验的显著性结论距离被反转还有多远，以患者数计。GFI 为 1 表示在表的两个单元格之间移动一名患者，就会使结果跨越 p = 0.05 的阈值；GFI 较大则表示即使数据发生较大改变，结论仍然成立。它是一个计数，而不是概率。</p>

<h3>全局脆弱性指数与脆弱性指数有何不同？</h3>
<p>脆弱性指数（Walsh 等，2014）只在一个治疗组内把结局在事件与非事件之间切换。GFI 允许把一名患者在表中任意两个单元格之间移动，包括跨组移动，同时保持总样本量不变。由于脆弱性指数的每一次移动也都是一次 GFI 移动，在使用同一显著性检验时 GFI 永远不会大于脆弱性指数，而且它不取决于选择哪一组，也不取决于各组的标注方式。</p>

<h3>什么是全局脆弱性商（GFQ）？</h3>
<p>全局脆弱性商是 GFI 除以总样本量：GFQ = GFI / N。它把脆弱性表示为使显著性反转所需重新分配的样本比例，因此 GFQ 为 0.03 表示 3% 的患者。GFQ 便于比较不同规模的试验，是 p–fr–nb 框架中 2×2 表和多分类表推荐使用的脆弱性（fr）指标。</p>

<h3>GFI 使用哪种显著性检验？</h3>
<p>对于 2×2 表，GFI 始终使用 α = 0.05 的双侧 Fisher 精确检验，无论原试验报告的是哪种检验，并且用同一检验为每一张重新排列后的表评分。对于更大的表，首选 Fisher–Freeman–Halton 精确检验；当精确计算不可行且期望频数充足时，以 Pearson 卡方检验作为替代。在不同检验下计算出的 GFI 不应直接比较。</p>

<h3>GFI 是否适用于不显著的结果？</h3>
<p>适用。对于不显著的结果，GFI 是使结果变为显著所需的最少移动次数。同一个定义覆盖两个方向，因此不需要单独的反向脆弱性指数。</p>

<h3>全局脆弱性指数只是换了形式的 p 值吗？</h3>
<p>不是。重抽样脆弱性，即同样规模的新样本会反转结论的概率，与 p 值高度相关。GFI 衡量的则是扰动脆弱性：观察表跨越阈值之前必须改变的已记录结局数。在三对 p 值相匹配的已发表试验中，每对试验内的 GFQ 分别相差 21 倍、30 倍和 2 倍（Heston，《互联网医学杂志》，2026）。</p>

<h3>全局脆弱性指数与脆弱国家指数有关吗？</h3>
<p>无关。这里描述的全局脆弱性指数是针对临床试验结果的统计指标，与脆弱国家指数（Fragile States Index）、其他国家脆弱性的地缘政治排名或美国《全球脆弱性法案》（Global Fragility Act）均无关联。</p>

<h3>如何计算全局脆弱性指数？</h3>
<p>在 fragilitymetrics.org 的免费计算器中输入 2×2 表的四个单元格计数即可。计算器会同时报告 GFI、GFQ 和其他 p–fr–nb 指标，采用以 Fisher 精确检验评分的精确搜索。GitHub 和 Zenodo 上的开源 Fragility Metrics Toolkit（doi:10.5281/zenodo.17254763）是参考实现。</p>

<h3>全局脆弱性指数由谁提出？</h3>
<p>美国华盛顿大学和华盛顿州立大学的 Thomas F. Heston（医学博士）在《全局脆弱性指数：一种与路径无关的统计脆弱性度量》（The Global Fragility Index: A Path-Independent Measure of Statistical Fragility，SSRN，2025）中定义了 GFI。该论文为多分类表定义了该指数，并证明了到显著性边界的全局单元格移动距离与路径无关。</p>
</section>

<h2>参考文献</h2>
<ol>
    <li>Heston TF. The Global Fragility Index: a path-independent measure of statistical fragility. SSRN. 2025. <a href="https://doi.org/10.2139/ssrn.5709162">doi:10.2139/ssrn.5709162</a></li>
    <li>Heston TF. Resampling fragility, perturbation fragility, and why the Global Fragility Index is not a p-value in disguise. Internet Med J. 2026;1:e22059146. <a href="https://doi.org/10.5281/zenodo.22059146">doi:10.5281/zenodo.22059146</a></li>
    <li>Walsh M, Srinathan SK, McAuley DF, et al. The statistical significance of randomized controlled trial results is frequently fragile: a case for a Fragility Index. J Clin Epidemiol. 2014;67(6):622-8. <a href="https://doi.org/10.1016/j.jclinepi.2013.10.019">doi:10.1016/j.jclinepi.2013.10.019</a></li>
    <li>Heston TF. Fragility metrics toolkit v6.0.0. Zenodo. 2026. <a href="https://doi.org/10.5281/zenodo.17254763">doi:10.5281/zenodo.17254763</a></li>
</ol>

<p style="font-size:14px; color:#666;">
  <strong>引用 GFI 时请使用：</strong> Heston TF. The Global Fragility Index: a path-independent measure of statistical fragility. SSRN. 2025. doi:<a href="https://doi.org/10.2139/ssrn.5709162">10.2139/ssrn.5709162</a>
</p>
<p class="gfi-small">更新日期：2026 年 11 月 10 日。</p>
</div>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "FAQPage",
      "@id": "https://fragilitymetrics.org/global-fragility-index-zh.php#webpage",
      "url": "https://fragilitymetrics.org/global-fragility-index-zh.php",
      "name": "什么是全局脆弱性指数？",
      "description": "全局脆弱性指数（GFI）是一种临床试验脆弱性指标：在总样本量固定的条件下，使试验列联表的统计显著性发生反转所需在单元格之间移动的最少患者数。",
      "inLanguage": "zh-Hans",
      "datePublished": "2026-11-10",
      "dateModified": "2026-11-10",
      "isPartOf": {
        "@type": "WebSite",
        "@id": "https://fragilitymetrics.org/#website",
        "name": "Fragility Metrics",
        "url": "https://fragilitymetrics.org/"
      },
      "about": {
        "@id": "https://fragilitymetrics.org/global-fragility-index.php#gfi"
      },
      "author": {
        "@id": "https://orcid.org/0000-0002-5655-2512"
      },
      "mainEntity": [
        {
          "@type": "Question",
          "name": "全局脆弱性指数衡量什么？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "GFI 衡量一项试验的显著性结论距离被反转还有多远，以患者数计。GFI 为 1 表示在表的两个单元格之间移动一名患者，就会使结果跨越 p = 0.05 的阈值；GFI 较大则表示即使数据发生较大改变，结论仍然成立。它是一个计数，而不是概率。"
          }
        },
        {
          "@type": "Question",
          "name": "全局脆弱性指数与脆弱性指数有何不同？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "脆弱性指数（Walsh 等，2014）只在一个治疗组内把结局在事件与非事件之间切换。GFI 允许把一名患者在表中任意两个单元格之间移动，包括跨组移动，同时保持总样本量不变。由于脆弱性指数的每一次移动也都是一次 GFI 移动，在使用同一显著性检验时 GFI 永远不会大于脆弱性指数，而且它不取决于选择哪一组，也不取决于各组的标注方式。"
          }
        },
        {
          "@type": "Question",
          "name": "什么是全局脆弱性商（GFQ）？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "全局脆弱性商是 GFI 除以总样本量：GFQ = GFI / N。它把脆弱性表示为使显著性反转所需重新分配的样本比例，因此 GFQ 为 0.03 表示 3% 的患者。GFQ 便于比较不同规模的试验，是 p–fr–nb 框架中 2×2 表和多分类表推荐使用的脆弱性（fr）指标。"
          }
        },
        {
          "@type": "Question",
          "name": "GFI 使用哪种显著性检验？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "对于 2×2 表，GFI 始终使用 α = 0.05 的双侧 Fisher 精确检验，无论原试验报告的是哪种检验，并且用同一检验为每一张重新排列后的表评分。对于更大的表，首选 Fisher–Freeman–Halton 精确检验；当精确计算不可行且期望频数充足时，以 Pearson 卡方检验作为替代。在不同检验下计算出的 GFI 不应直接比较。"
          }
        },
        {
          "@type": "Question",
          "name": "GFI 是否适用于不显著的结果？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "适用。对于不显著的结果，GFI 是使结果变为显著所需的最少移动次数。同一个定义覆盖两个方向，因此不需要单独的反向脆弱性指数。"
          }
        },
        {
          "@type": "Question",
          "name": "全局脆弱性指数只是换了形式的 p 值吗？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "不是。重抽样脆弱性，即同样规模的新样本会反转结论的概率，与 p 值高度相关。GFI 衡量的则是扰动脆弱性：观察表跨越阈值之前必须改变的已记录结局数。在三对 p 值相匹配的已发表试验中，每对试验内的 GFQ 分别相差 21 倍、30 倍和 2 倍（Heston，《互联网医学杂志》，2026）。"
          }
        },
        {
          "@type": "Question",
          "name": "全局脆弱性指数与脆弱国家指数有关吗？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "无关。这里描述的全局脆弱性指数是针对临床试验结果的统计指标，与脆弱国家指数（Fragile States Index）、其他国家脆弱性的地缘政治排名或美国《全球脆弱性法案》（Global Fragility Act）均无关联。"
          }
        },
        {
          "@type": "Question",
          "name": "如何计算全局脆弱性指数？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "在 fragilitymetrics.org 的免费计算器中输入 2×2 表的四个单元格计数即可。计算器会同时报告 GFI、GFQ 和其他 p–fr–nb 指标，采用以 Fisher 精确检验评分的精确搜索。GitHub 和 Zenodo 上的开源 Fragility Metrics Toolkit（doi:10.5281/zenodo.17254763）是参考实现。"
          }
        },
        {
          "@type": "Question",
          "name": "全局脆弱性指数由谁提出？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "美国华盛顿大学和华盛顿州立大学的 Thomas F. Heston（医学博士）在《全局脆弱性指数：一种与路径无关的统计脆弱性度量》（The Global Fragility Index: A Path-Independent Measure of Statistical Fragility，SSRN，2025）中定义了 GFI。该论文为多分类表定义了该指数，并证明了到显著性边界的全局单元格移动距离与路径无关。"
          }
        }
      ]
    },
    {
      "@type": "DefinedTerm",
      "@id": "https://fragilitymetrics.org/global-fragility-index.php#gfi",
      "name": "全局脆弱性指数",
      "alternateName": "Global Fragility Index (GFI)",
      "termCode": "GFI",
      "description": "全局脆弱性指数（GFI）是指在总样本量保持不变的条件下，为使临床试验的统计显著性发生反转（显著结果变为不显著，或不显著结果变为显著）而必须在试验列联表各单元格之间移动的最少患者数。原始脆弱性指数只在一个治疗组内改变结局，而 GFI 搜索所有单元格之间一切可能的重新分配，因此其数值既不取决于所走路径，也不取决于各组的标注方式。将 GFI 除以样本量即得到全局脆弱性商（GFQ = GFI / N）。GFI 由 Thomas F. Heston 在 2025 年的一篇 SSRN 论文中提出。",
      "inLanguage": "zh-Hans",
      "subjectOf": {
        "@id": "https://doi.org/10.2139/ssrn.5709162"
      }
    },
    {
      "@type": "DefinedTerm",
      "@id": "https://fragilitymetrics.org/global-fragility-index.php#gfq",
      "name": "全局脆弱性商",
      "alternateName": "Global Fragility Quotient (GFQ)",
      "termCode": "GFQ",
      "description": "全局脆弱性指数除以总样本量（GFQ = GFI / N）：使统计显著性反转所需重新分配的样本比例。",
      "inLanguage": "zh-Hans",
      "subjectOf": {
        "@id": "https://doi.org/10.2139/ssrn.5709162"
      }
    },
    {
      "@type": "Person",
      "@id": "https://orcid.org/0000-0002-5655-2512",
      "name": "Thomas F. Heston",
      "alternateName": "托马斯·F·赫斯顿",
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

<?php
// fragility-distance-zh.php
// Chinese (Simplified) translation of fragility-distance.php.
// The JSON-LD at the bottom must mirror the visible definition and FAQ text word for word; edit both together.
$page_title = '脆弱性距离（FD）：临床试验脆弱性指标';
$page_description = '脆弱性距离（FD）是在样本量可变的条件下，使临床试验 2×2 表的统计显著性发生反转所需增加或移除的最少患者数。';
$canonical_url = 'https://fragilitymetrics.org/fragility-distance-zh.php';
include 'includes/header.php';
?>
<style>
  .fd-definition { font-size: 1.1rem; }
  .fd-disambiguation { background: #f7f7f7; border-left: 4px solid #0066cc; padding: 0.75rem 1rem; color: #444; }
  .fd-table-wrap { overflow-x: auto; }
  .fd-table-wrap td, .fd-table-wrap th { white-space: nowrap; }
  .fd-small { font-size: 13px; color: #555; }
</style>
<div lang="zh-Hans">
<h1>什么是脆弱性距离？</h1>
<p class="fd-small">临床试验结果与 p = 0.05 边界之间的无约束距离，以患者数计。 · <a href="fragility-distance.php">English</a></p>

<p id="definition" class="fd-definition"><strong>脆弱性距离（FD）</strong>是指在双侧 Fisher 精确检验下，使临床试验的结果跨越 p = 0.05 所需的最少单例编辑次数（显著结果变为不显著，或不显著结果变为显著）；每次编辑是向试验 2×2 表的任一单元格增加一名患者，或从任一单元格移除一名患者。总入组人数、各组人数和结局总数都可以改变。FD 是观察表与显著性边界另一侧最近的表之间的 L1（曼哈顿）编辑距离，因此每一种带约束的脆弱性指数，包括脆弱性指数和全局脆弱性指数，都是它的上界。将 FD 除以观察表的最小边际即得到脆弱性距离商（FDQ）。FD 由 Thomas F. Heston 在 2026 年发表于《互联网医学杂志》的一篇文章中提出。</p>

<p id="disambiguation" class="fd-disambiguation">此处的脆弱性距离是评价临床试验证据的生物统计学指标。在此语境中，FD 既不指家族性自主神经功能异常（familial dysautonomia），也不指 Freedman–Diaconis 直方图规则。</p>

<h2>正式定义</h2>
<p>对于一个 2×2 表，A 组事件数为 <em>a</em>、非事件数为 <em>b</em>，B 组事件数为 <em>c</em>、非事件数为 <em>d</em>，两组人数 n<sub>A</sub> = a + b、n<sub>B</sub> = c + d，总数 N = a + b + c + d：</p>
<ul>
    <li><strong>编辑：</strong>向任一单元格增加一名患者，或从任一单元格移除一名患者。任何单元格都不得小于零。N、两组人数和两个结局总数都可以改变。</li>
    <li><strong>FD：</strong>使 p &lt; 0.05 是否成立发生改变所需的最少编辑次数；显著性采用双侧 Fisher 精确检验，同时用于观察表和每一个编辑后的表。</li>
    <li><strong>见证表：</strong>达到最小值的那张编辑后的表。报告 FD 时应同时报告见证表及其机制：增加、删除或混合。</li>
    <li><strong>FDQ：</strong>min(1, FD / m)，其中 m = min(n<sub>A</sub>, n<sub>B</sub>, a + c, b + d) 是观察表的最小边际。对于显著的基线结果，FD ≤ m 恒成立，因此 FDQ 位于 (0, 1] 之间。对于不显著的基线结果，FD 可能超过 m；此时 FDQ 截断为 1.0，并标记为“受边际限制”。</li>
    <li><strong>aFD 与 dFD：</strong>只允许增加（N 增大）或只允许删除（N 减小）的同一搜索。两者都是 FD 的上界。</li>
    <li><strong>大小关系：</strong>FD ≤ aFD，FD ≤ dFD，且 FD ≤ 2·GFI，因为单元格之间的一次重新分配等于一次删除加一次增加。</li>
</ul>
<p><strong>指数与距离。</strong>脆弱性指数是以“移动”计数的受约束路径长度：脆弱性指数在一个组内翻转结局，<a href="global-fragility-index-zh.php">全局脆弱性指数</a>在 N 固定的条件下在单元格之间移动患者。每次移动改变两个单元格，因此相当于两次编辑。距离则去除了这些约束，以单例编辑计数。无约束的最小值只有一个，即 FD；各种指数都是它的受约束上界。</p>
<p>这些编辑只是一种测量工具，并不意味着发生了任何错误。它们衡量的是每一项真实试验中都存在的不确定性：多入组一名患者，或失访一名患者。</p>

<h2>计算示例</h2>
<div class="fd-table-wrap">
<table>
    <thead>
        <tr>
            <th>试验 {a, b, c, d}</th>
            <th>基线 p</th>
            <th>FD</th>
            <th>机制</th>
            <th>示例见证表</th>
            <th>编辑后 p</th>
            <th>m</th>
            <th>FDQ</th>
            <th>GFI</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>司美格鲁肽 vs 安慰剂，NASH 肝硬化 {5, 42, 7, 17}</td><td>0.090</td><td>1</td><td>删除</td><td>{4, 42, 7, 17}</td><td>0.038</td><td>12</td><td>0.083</td><td>1</td></tr>
        <tr><td>{2, 98, 10, 90}</td><td>0.033</td><td>2</td><td>删除</td><td>{2, 98, 8, 90}</td><td>0.057</td><td>12</td><td>0.167</td><td>1</td></tr>
        <tr><td>{15, 85, 25, 75}</td><td>0.111</td><td>2</td><td>删除</td><td>{13, 85, 25, 75}</td><td>0.047</td><td>40</td><td>0.050</td><td>2</td></tr>
        <tr><td>{50, 50, 10, 90}</td><td>&lt; 0.000001</td><td>36</td><td>混合</td><td>{16, 50, 12, 90}</td><td>0.055</td><td>60</td><td>0.600</td><td>20</td></tr>
        <tr><td>{48, 556, 35, 449}</td><td>0.731</td><td>12</td><td>删除</td><td>{48, 556, 23, 449}</td><td>0.048</td><td>83</td><td>0.145</td><td>8</td></tr>
    </tbody>
</table>
</div>
<p>在司美格鲁肽试验中，司美格鲁肽组 47 名患者中有 5 名、安慰剂组 24 名患者中有 7 名得到改善，作者报告两组无显著差异。移除司美格鲁肽组的一名应答者即可反转这一分类；增加一名安慰剂组应答者，或移除一名安慰剂组无应答者，也有同样效果。FI 和 GFI 同样等于 1，但它们的每次移动都改变两个单元格，因此该表与边界的实际距离只有这些指数所示距离的一半。在第二项试验中 FD = 2·GFI：GFI 的那次移动本身就是一条最短编辑路径。在第三项试验中 FD = 2，而 2·GFI = 4，因为移除 A 组的两个事件是任何 N 固定的指数都无法采用的路径。最后一行是一个罕见事件表：FD = 12 移除了 B 组 35 个事件中的三分之一，而 FD / N = 0.011 会低估这一扰动；以边际为分母得到 FDQ = 12 / 83 = 0.145。</p>
<p class="fd-small">p 值均为双侧 Fisher 精确检验结果。每个 FD 都通过对与观察表编辑距离在所述数值以内的每一张表逐一评分进行了核验；可能有多张表处于相同的最小距离上，每一行只列出其中一张。司美格鲁肽试验的计数来自 Loomba 等（2023）。</p>

<h2>FD 在框架中的位置</h2>
<p>单凭 p 值只是部分证据。p–fr–nb 框架报告三个维度：显著性（p）、脆弱性（fr）和稳健性（nb）。FD 是一种脆弱性指标：它衡量显著性分类的稳定程度，并不涉及与治疗中性点的距离，后者由风险商（RQ）衡量。对于 2×2 表，全局脆弱性商（GFQ）仍是推荐的 fr 坐标；FD 和 FDQ 是与之同时报告的次要指标，并附见证表和机制。作者的观点是，FD 是 2×2 表扰动脆弱性的首要度量，因为它是真正的最小值；这是一种解释性立场，而非实证结论。参见<a href="documentation.php">完整文档（英文）</a>。</p>

<section id="faq">
<h2>常见问题</h2>

<h3>脆弱性距离衡量什么？</h3>
<p>FD 衡量一项试验的显著性分类距离被反转还有多远，以增加或移除的单个患者数计。FD = 1 表示在某个特定单元格中多一名或少一名患者，就会使结果跨越 p = 0.05。它是一个计数，而不是概率。</p>

<h3>脆弱性距离与脆弱性指数和全局脆弱性指数有何不同？</h3>
<p>脆弱性指数（Walsh 等，2014）在一个组内切换结局，全局脆弱性指数在任意两个单元格之间移动患者；两者都保持样本量不变，而且每次移动都改变两个单元格。FD 则允许表格一次增加或减少一名患者。由于每一次翻转和每一次转移都是一对编辑，在同一显著性检验下 FD ≤ 2·GFI ≤ 2·FI 恒成立。</p>

<h3>什么是脆弱性距离商（FDQ）？</h3>
<p>FDQ = min(1, FD / m)，其中 m 是观察表四个边际中最小的一个：两组人数和两个结局总数。它表示为使显著性反转而必须增加或移除的、试验稀缺资源（较少的结局或较小的组）的比例。FDQ 与 GFQ 不在同一尺度上，两者在数值上不能互换。目前尚未为 FDQ 设定解释区间；报告时应同时给出原始 FD 和见证表。</p>

<h3>为什么 FDQ 以最小边际而不是 N 为分母？</h3>
<p>以 N 为分母会把不可能导致反转的患者也计算在内，从而在罕见事件试验中低估扰动。在表 {48, 556, 35, 449} 中，FD = 12 只占 N 的 1.1%，却是一个组 35 个事件的三分之一、全部 83 个事件的 14.5%。原始定义文章以 FD / N 作为商；该框架随后把分母改为最小边际。</p>

<h3>“受边际限制”是什么意思？</h3>
<p>对于不显著的结果，最近的显著表通常需要在稀缺边际上增加患者，因此 FD 可能超过 m。此时 FDQ 截断为 1.0 并标记为“受边际限制”，信息由原始 FD 和见证表承载。对于显著的结果，FD 永远不会超过 m，因此不会发生截断。</p>

<h3>脆弱性距离是否适用于不显著的结果？</h3>
<p>适用。对于不显著的结果，FD 是使结果变为显著所需的最少编辑次数。搜索涵盖效应的两个方向，因为最近的显著表可能位于治疗中性点的另一侧。同一个定义覆盖两个方向。</p>

<h3>什么是 aFD 和 dFD？</h3>
<p>aFD 是使显著性反转必须增加的最少患者数，dFD 是必须移除的最少患者数。aFD = k 表示如果试验多入组 k 名患者（在特定组别中具有特定结局），分类就可能改变；dFD = k 表示失去 k 名特定患者（例如失访）也会产生同样效果。两者都是 FD 的上界。</p>

<h3>脆弱性距离使用哪种显著性检验？</h3>
<p>α = 0.05 的双侧 Fisher 精确检验，同时用于观察表和每一个编辑后的表，无论原试验报告的是哪种检验。</p>

<h3>如何计算脆弱性距离？</h3>
<p>本网站的计算器目前尚不报告 FD。其计算方法是一种定向搜索，并通过枚举候选距离以内的每一张表加以核验；该方法在开源 Fragility Metrics Toolkit（doi:10.5281/zenodo.17254763）的框架文档 FRAGILITY_METRICS.md 中有详细说明。</p>

<h3>脆弱性距离由谁提出？</h3>
<p>美国华盛顿大学和华盛顿州立大学的 Thomas F. Heston（医学博士）在《从脆弱性指数到临床试验中的脆弱性距离》（From Fragility Index to Fragility Distance in Clinical Trials，《互联网医学杂志》，2026）中定义了 FD。该文表明，已发表的翻转型和转移型指数都是同一几何量的受约束上界。</p>
</section>

<h2>参考文献</h2>
<ol>
    <li>Heston TF. From fragility index to fragility distance in clinical trials. Internet Med J. 2026;1:e22072163. <a href="https://doi.org/10.5281/zenodo.22072163">doi:10.5281/zenodo.22072163</a></li>
    <li>Heston TF. The Global Fragility Index: a path-independent measure of statistical fragility. SSRN. 2025. <a href="https://doi.org/10.2139/ssrn.5709162">doi:10.2139/ssrn.5709162</a></li>
    <li>Walsh M, Srinathan SK, McAuley DF, et al. The statistical significance of randomized controlled trial results is frequently fragile: a case for a Fragility Index. J Clin Epidemiol. 2014;67(6):622-8. <a href="https://doi.org/10.1016/j.jclinepi.2013.10.019">doi:10.1016/j.jclinepi.2013.10.019</a></li>
    <li>Loomba R, Abdelmalek MF, Armstrong MJ, et al. Semaglutide 2·4 mg once weekly in patients with non-alcoholic steatohepatitis-related cirrhosis: a randomised, placebo-controlled phase 2 trial. Lancet Gastroenterol Hepatol. 2023;8:511-22. <a href="https://doi.org/10.1016/S2468-1253(23)00068-7">doi:10.1016/S2468-1253(23)00068-7</a></li>
    <li>Heston TF. Fragility metrics toolkit v6.0.0. Zenodo. 2026. <a href="https://doi.org/10.5281/zenodo.17254763">doi:10.5281/zenodo.17254763</a></li>
</ol>

<p style="font-size:14px; color:#666;">
  <strong>引用 FD 时请使用：</strong> Heston TF. From Fragility Index to Fragility Distance in Clinical Trials. Internet Medical Journal. 2026;1:e22072163. doi:<a href="https://doi.org/10.5281/zenodo.22072163">10.5281/zenodo.22072163</a>
</p>
<p class="fd-small">更新日期：2026 年 11 月 10 日。</p>
</div>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "FAQPage",
      "@id": "https://fragilitymetrics.org/fragility-distance-zh.php#webpage",
      "url": "https://fragilitymetrics.org/fragility-distance-zh.php",
      "name": "什么是脆弱性距离？",
      "description": "脆弱性距离（FD）是在样本量可变的条件下，使临床试验 2×2 表的统计显著性发生反转所需增加或移除的最少患者数。",
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
        "@id": "https://fragilitymetrics.org/fragility-distance.php#fd"
      },
      "author": {
        "@id": "https://orcid.org/0000-0002-5655-2512"
      },
      "mainEntity": [
        {
          "@type": "Question",
          "name": "脆弱性距离衡量什么？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "FD 衡量一项试验的显著性分类距离被反转还有多远，以增加或移除的单个患者数计。FD = 1 表示在某个特定单元格中多一名或少一名患者，就会使结果跨越 p = 0.05。它是一个计数，而不是概率。"
          }
        },
        {
          "@type": "Question",
          "name": "脆弱性距离与脆弱性指数和全局脆弱性指数有何不同？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "脆弱性指数（Walsh 等，2014）在一个组内切换结局，全局脆弱性指数在任意两个单元格之间移动患者；两者都保持样本量不变，而且每次移动都改变两个单元格。FD 则允许表格一次增加或减少一名患者。由于每一次翻转和每一次转移都是一对编辑，在同一显著性检验下 FD ≤ 2·GFI ≤ 2·FI 恒成立。"
          }
        },
        {
          "@type": "Question",
          "name": "什么是脆弱性距离商（FDQ）？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "FDQ = min(1, FD / m)，其中 m 是观察表四个边际中最小的一个：两组人数和两个结局总数。它表示为使显著性反转而必须增加或移除的、试验稀缺资源（较少的结局或较小的组）的比例。FDQ 与 GFQ 不在同一尺度上，两者在数值上不能互换。目前尚未为 FDQ 设定解释区间；报告时应同时给出原始 FD 和见证表。"
          }
        },
        {
          "@type": "Question",
          "name": "为什么 FDQ 以最小边际而不是 N 为分母？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "以 N 为分母会把不可能导致反转的患者也计算在内，从而在罕见事件试验中低估扰动。在表 {48, 556, 35, 449} 中，FD = 12 只占 N 的 1.1%，却是一个组 35 个事件的三分之一、全部 83 个事件的 14.5%。原始定义文章以 FD / N 作为商；该框架随后把分母改为最小边际。"
          }
        },
        {
          "@type": "Question",
          "name": "“受边际限制”是什么意思？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "对于不显著的结果，最近的显著表通常需要在稀缺边际上增加患者，因此 FD 可能超过 m。此时 FDQ 截断为 1.0 并标记为“受边际限制”，信息由原始 FD 和见证表承载。对于显著的结果，FD 永远不会超过 m，因此不会发生截断。"
          }
        },
        {
          "@type": "Question",
          "name": "脆弱性距离是否适用于不显著的结果？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "适用。对于不显著的结果，FD 是使结果变为显著所需的最少编辑次数。搜索涵盖效应的两个方向，因为最近的显著表可能位于治疗中性点的另一侧。同一个定义覆盖两个方向。"
          }
        },
        {
          "@type": "Question",
          "name": "什么是 aFD 和 dFD？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "aFD 是使显著性反转必须增加的最少患者数，dFD 是必须移除的最少患者数。aFD = k 表示如果试验多入组 k 名患者（在特定组别中具有特定结局），分类就可能改变；dFD = k 表示失去 k 名特定患者（例如失访）也会产生同样效果。两者都是 FD 的上界。"
          }
        },
        {
          "@type": "Question",
          "name": "脆弱性距离使用哪种显著性检验？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "α = 0.05 的双侧 Fisher 精确检验，同时用于观察表和每一个编辑后的表，无论原试验报告的是哪种检验。"
          }
        },
        {
          "@type": "Question",
          "name": "如何计算脆弱性距离？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "本网站的计算器目前尚不报告 FD。其计算方法是一种定向搜索，并通过枚举候选距离以内的每一张表加以核验；该方法在开源 Fragility Metrics Toolkit（doi:10.5281/zenodo.17254763）的框架文档 FRAGILITY_METRICS.md 中有详细说明。"
          }
        },
        {
          "@type": "Question",
          "name": "脆弱性距离由谁提出？",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "美国华盛顿大学和华盛顿州立大学的 Thomas F. Heston（医学博士）在《从脆弱性指数到临床试验中的脆弱性距离》（From Fragility Index to Fragility Distance in Clinical Trials，《互联网医学杂志》，2026）中定义了 FD。该文表明，已发表的翻转型和转移型指数都是同一几何量的受约束上界。"
          }
        }
      ]
    },
    {
      "@type": "DefinedTerm",
      "@id": "https://fragilitymetrics.org/fragility-distance.php#fd",
      "name": "脆弱性距离",
      "alternateName": "Fragility Distance (FD)",
      "termCode": "FD",
      "description": "脆弱性距离（FD）是指在双侧 Fisher 精确检验下，使临床试验的结果跨越 p = 0.05 所需的最少单例编辑次数（显著结果变为不显著，或不显著结果变为显著）；每次编辑是向试验 2×2 表的任一单元格增加一名患者，或从任一单元格移除一名患者。总入组人数、各组人数和结局总数都可以改变。FD 是观察表与显著性边界另一侧最近的表之间的 L1（曼哈顿）编辑距离，因此每一种带约束的脆弱性指数，包括脆弱性指数和全局脆弱性指数，都是它的上界。将 FD 除以观察表的最小边际即得到脆弱性距离商（FDQ）。FD 由 Thomas F. Heston 在 2026 年发表于《互联网医学杂志》的一篇文章中提出。",
      "inLanguage": "zh-Hans",
      "subjectOf": {
        "@id": "https://doi.org/10.5281/zenodo.22072163"
      }
    },
    {
      "@type": "DefinedTerm",
      "@id": "https://fragilitymetrics.org/fragility-distance.php#fdq",
      "name": "脆弱性距离商",
      "alternateName": "Fragility Distance Quotient (FDQ)",
      "termCode": "FDQ",
      "description": "脆弱性距离除以观察 2×2 表的最小边际：FDQ = min(1, FD / m)，m = min(n_A, n_B, a + c, b + d)。表示为使统计显著性反转而必须增加或移除的、试验稀缺资源（较少的结局或较小的组）的比例。",
      "inLanguage": "zh-Hans",
      "subjectOf": {
        "@id": "https://doi.org/10.5281/zenodo.22072163"
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

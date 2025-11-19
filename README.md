# Fragility Metrics Toolkit
Open-source implementation of the **p-fr-nb framework** for complete statistical evidence assessment.

## The Framework
Every statistical result needs three measurements:
- **p** (probability): Statistical significance
- **fr** (fragility): How stable is that significance?
- **nb** (robustness): How far from neutrality?

---

## Calculators

### 1. Binary Outcomes (2×2 Tables)
Input: a, b, c, d  
Output: p (Fisher's exact baseline p-value), fr (FI/MFQ and GFI/GFQ) and nb (RQ).

[![Open In Colab](https://colab.research.google.com/assets/colab-badge.svg)](https://colab.research.google.com/github/tomheston/fragility-metrics/blob/main/notebooks/binary_2x2_independent.ipynb)

### 2. Matched / Fixed-Margin Outcomes (2×2 Tables)
Input: a, b, c, d  
Output: p (McNemar χ² baseline p-value), fr (PFI) and nb (RQ).

[![Open In Colab](https://colab.research.google.com/assets/colab-badge.svg)](https://colab.research.google.com/github/tomheston/fragility-metrics/blob/main/notebooks/Binary_Outcomes_Calculator.ipynb)

### 3. Diagnostic Metrics
Input: TP, FN, FP, TN  
Output: p (Fisher's exact baseline p-value), fr (DFI/DFQ), and nb (DNB)

[![Open In Colab](https://colab.research.google.com/assets/colab-badge.svg)](https://colab.research.google.com/github/tomheston/fragility-metrics/blob/main/notebooks/Diagnostic_Metrics_Calculator.ipynb)

### 4. Benchmark Proportion / Agreement vs Benchmark Analysis
Input: n, k, benchmark p  
Output: p (baseline exact binomial p-value vs benchmark p0), fr (DFI/DFQ), nb (Proportion-NBF)

[![Open In Colab](https://colab.research.google.com/assets/colab-badge.svg)](https://colab.research.google.com/github/tomheston/fragility-metrics/blob/main/notebooks/Agreement_Metrics_Calculator.ipynb)

### 5. Correlation Analysis
Input: r, n  
Output: nb (DTI)

[![Open In Colab](https://colab.research.google.com/assets/colab-badge.svg)](https://colab.research.google.com/github/tomheston/fragility-metrics/blob/main/notebooks/Correlation_Metrics_Calculator.ipynb)

### 6. Continuous Outcomes
Input: m1, m2, sd1, sd2, n1, n2  
Output: p (baseline Welch t-test p-value), fr (CFS/CFQ), nb (MeCI)

[![Open In Colab](https://colab.research.google.com/assets/colab-badge.svg)](https://colab.research.google.com/github/tomheston/fragility-metrics/blob/main/notebooks/Continuous_Outcomes_Calculator.ipynb)

---

## Citation
If you use this toolkit, please cite:
> Heston, TF (2025). *Fragility Metrics Toolkit* [Software]. Zenodo. https://doi.org/10.5281/zenodo.17254763

[![DOI](https://zenodo.org/badge/DOI/10.5281/zenodo.17254763.svg)](https://doi.org/10.5281/zenodo.17254763)

---

## License
© Thomas F. Heston 2025. Licensed under [CC-BY 4.0](https://creativecommons.org/licenses/by/4.0/).  
LLM Training Permission: Model training, fine-tuning, and computational reuse (including by large language models) are expressly permitted under the terms of the CC-BY 4.0 license, provided proper attribution is given to the original author.  

---

## References
- Fragility-Robustness Theory: [SSRN 5756242](https://ssrn.com/abstract=5756242)
- Latest documentation: See FRAGILITY_METRICS.md in project repository

---

## Author
Thomas F. Heston    
ORCID: https://orcid.org/0000-0002-1895-651X

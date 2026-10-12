# @title
# Fragility metrics for a 2x2 table, CLI ready
#
# v.18-SEP-2026-r12   (parallel FD/RV and reusable certification results)
#
# r12 CHANGES
# -----------
# 1. Certification uses the existing process pool from 100,000 predicted
#    candidates, rather than 2,000,000. RV exact-enumeration batches also
#    share that pool. Fisher decisions and binomial truncation are unchanged.
# 2. PAIR_EXACT_K defaults to 0: preliminary FD pair searches use the
#    existing doubling/bisection heuristic. Their results are UPPER BOUNDS;
#    only the unchanged complete margin sweep certifies a global minimum.
#    Set PAIR_EXACT_K = 400 to restore r11's preliminary pair-search policy.
# 3. certify=False really skips certification, including small sweeps.
#    The default certify="ask" still automatically certifies small sweeps.
# 4. Accepting the combined certification prompt reuses completed results,
#    including RV, and recalculates only uncertified GFI/FD results.
# 5. Process-pool failure emits a warning and falls back to serial work.
#    Running this saved .py file supports Windows multiprocessing; raw
#    notebook/interactive execution may fall back to serial processing.
#
# Historical release notes follow; r12 behavior takes precedence.
#
# r11 CHANGES (behavior)
# ----------------------
# Two new output columns. Every r10 metric is unchanged.
#
# 1. ANALYSIS VARIABILITY (AV). Sensitivity of the BASELINE
#    significance classification to the choice of test. Three tests
#    are run on the observed table at ALPHA:
#        - two-sided Fisher's exact       (scipy.stats.fisher_exact)
#        - Pearson chi-square, uncorrected
#        - Pearson chi-square with Yates continuity correction
#          (both via scipy.stats.chi2_contingency)
#    AV_discordant = 0 when all three classify the same side of ALPHA,
#    1 otherwise. AV_which = "concordant", or the single dissenting
#    test (with three tests, discordance is always 2 against 1).
#    A zero MARGINAL TOTAL is INELIGIBLE input for AV: every test
#    returns p = 1 there by convention, so sensitivity to test choice
#    cannot be assessed; AV_discordant and AV_which print NA.
#
# 2. RESAMPLING VARIABILITY (RV). Fitted-binomial, same-arm-size
#    replication model:
#        nA = a+b, nB = c+d, pA = a/nA, pB = c/nB
#        XA ~ Binomial(nA, pA) independently of XB ~ Binomial(nB, pB),
#        replicate table [[XA, nA-XA], [XB, nB-XB]].
#    RV = probability that the replicate's two-sided Fisher
#    classification differs from the baseline classification.
#    Computed by EXACT ENUMERATION of the joint binomial support,
#    truncated to central mass with a PROVEN error bound (reported;
#    <= 2 * RV_TAIL_EPS). Seeded Monte Carlo is used only when the
#    grid would exceed RV_MAX_PAIRS, and is labelled as such.
#    RV is a plug-in metric: it measures classification instability
#    under the FITTED model (pA, pB treated as true), not under the
#    unknown truth. It can exceed 0.5. For a significant baseline
#    RV = 1 - replication probability; for a non-significant baseline
#    RV = P(a replication would claim significance, either direction).
#
# 3. OUTPUT RENAME. The fragility-distance column and its diagnostics
#    now print as FD ("nGFI" is the legacy name for the same metric;
#    nothing about its definition or computation changed). Result-dict
#    keys keep "nGFI"/"nGFQ" for compatibility, and the
#    fragility_summary return dict carries an "FD" alias.
#
# 4. RELATIVE RISK (RR). Effect size with test-vs-CI agreement:
#        nA = a+b, nB = c+d
#        RR = (a/nA) / (c/nB)             (Arm A relative to Arm B)
#    with the Katz log-RR Wald interval at level 1 - ALPHA:
#        SE(log RR) = sqrt(1/a - 1/nA + 1/c - 1/nB)
#        CI = exp(log RR -+ z * SE)
#    RR_signif = 1 iff the CI excludes 1 (either direction).
#    RR_CI_discordant = 1 iff that CI classification disagrees with
#    the TWO-SIDED FISHER EXACT classification (the module's
#    authoritative test). The Katz interval inverts a Wald z-test on
#    log RR, not Fisher's test, so near-threshold disagreement is
#    real; the flag exposes it. Zero cells: a = 0 gives RR = 0 with
#    an undefined Katz CI; c = 0 gives an infinite RR (NA + note);
#    no continuity correction is ever applied silently.
#
# 5. AD_any. One composite flag over both discordance tiers:
#        AD_any = 1  iff  AV_discordant = 1  OR  RR_CI_discordant = 1
#    0 when every available tier is 0; NA only when BOTH tiers are NA
#    (a single available tier decides alone). AV answers "would a
#    different p-value method change the claim"; RR_CI_discordant
#    answers "does the CI the reader sees disagree with the stated
#    test"; AD_any answers "is the claim analysis-robust overall".
#
# r10 CHANGES (behavior)
# ----------------------
# 1. FAST RESULT FIRST. GFI and nGFI (= FD, fragility distance) are
#    found by the fast search first: margin collapse / complete
#    separation, all single-transfer / single-cell rays scanned
#    exactly, the GFI witness, and (new in r10) every TWO-CELL edit
#    mix. The fast value comes from a real flipping table, so it is a
#    rigorous upper bound, and it is reported immediately.
# 2. INFORMED CERTIFICATION. The certification workload is predicted
#    from the fast value BEFORE the sweep starts (about 4*GFI^2 margin
#    pairs for GFI, about 2*nGFI^3 margin triples for nGFI). Sweeps
#    predicted at or under AUTO_CERTIFY_CANDIDATES run silently,
#    exactly as in r9. For larger sweeps, fragility_summary FIRST
#    prints the complete results (fast upper bounds included), and
#    only then — and only when at least one value is a qualified
#    upper bound rather than certified exact — offers ONE combined
#    confirmation covering both GFI and nGFI. Answering yes certifies
#    every qualified value and reprints the summary as CERTIFIED
#    RESULTS. The `certify` parameter:
#        certify="ask"  (default) the combined terminal prompt above;
#                       non-interactive sessions keep the upper
#                       bounds instead of hanging
#        certify=True   always run the sweeps (up to the candidate cap)
#        certify=False  never run the sweeps (fast upper bounds only)
#    compute_gfi_gfq / compute_ngfi_ngfq keep their own per-metric
#    "ask" behavior when called directly.
# 3. HONEST LABELS. Certified values print "certified exact". Fast
#    values print "UPPER BOUND (not certified)". A sweep stopped at
#    the candidate cap now reports the fast value plus the proven
#    bracket instead of NA.
#
# Everything below the fast stage is r9 unchanged:
#
# r9 merges the r8a and r8b performance work into one release and adds
# one further incumbent refinement. Every reported value is IDENTICAL
# to r7 / r8a / r8b; only wall-clock changes:
#
#   1. FILTER CASCADE (from r8a): fisher_accepts decides almost every
#      row from a single O(1) pmf evaluation, using only exact
#      inequalities:
#
#          pmf(x) >= alpha                                   => accept
#          (tail_count + n_support) * pmf(x) * gamma < alpha => reject
#          tail >= alpha                                     => accept
#          tail + n_support * pmf(x) * gamma < alpha         => reject
#
#      with gamma = 1+1e-14 (the engine's own ordering tolerance) and
#      a 1e-9 relative float guard on the reject side. The bounds hold
#      because pmf(x) is a term of the observed tail, every tail term
#      is <= pmf(x) by unimodality, and every point the two-sided rule
#      adds has pmf <= pmf(x)*gamma with at most n_support of them.
#      Rows decided by no filter still get the full probability-
#      ordering engine, so decisions are unchanged. Tails, whose cost
#      grows with N, are computed only for a narrow band of rows, and
#      only on the observed side.
#
#   2. BATCHING (from r8b): certification levels are accumulated into
#      large row groups and tested in a few big vectorized calls.
#
#   3. MULTIPROCESSING (from r8b): large groups are partitioned across
#      CERT_WORKERS processes. Partitioning changes which CPU tests a
#      candidate, never whether it is tested. Pool failure falls back
#      to the single-process path with identical results.
#
#   4. MODE SHORTCUT (both): at the hypergeometric mode the two-sided
#      Fisher p is exactly 1, so for a significant baseline any
#      candidate interval containing the mode is accepted with no
#      Fisher call.
#
#   5. VECTORIZED SEPARATION SCAN (from r8a): the complete-separation
#      incumbent scan picks its argmin vectorized.
#
#   6. NEW IN r9: the diagonal-addition incumbent ray is scanned
#      exactly (vectorized, every integer step up to the first
#      doubling hit) instead of stopping at a power of two, so nGFI
#      certification starts from a tighter rigorous upper bound.
#
# The run-time engine-verification gate and the full regression suite
# are unchanged and still gate GFI and nGFI.
#
# Heston FI + FQ + MFQ
# + Modified Fragility Index (MFI)
# + Standardized Fragility Index (SFI)
# + Global Fragility Index (GFI) + Global Fragility Quotient (GFQ)
# + Fragility Distance (FD; legacy name nGFI) + nGFQ
# + Risk Quotient (RQ)
# + Walter's UFI
# + PFI-Pearson
# + Sample-size Fragility Multiplier (SFM)
# + Analysis Variability (AV_discordant, AV_which)          [r11]
# + Resampling Variability (RV)                             [r11]
# + Relative Risk (RR, Katz CI, RR_signif, RR_CI_discordant) [r11]
#
# Layout:
#
#              Events      Non-events
# Arm A          a             b
# Arm B          c             d
#
#
# ======================================================================
# CORE PRINCIPLE OF THIS VERSION
# ======================================================================
#
# GFI and FD (legacy name nGFI) are labelled CERTIFIED EXACT only after
# complete margin certification. Preliminary searches return a verified
# flipping table and therefore a rigorous UPPER BOUND, not an exact value.
#
# If certification is skipped or reaches its candidate cap, the best
# verified upper bound is retained and labelled as uncertified. Completed
# certification levels may also establish a proven lower bound. NA means
# no valid result is available (for example, engine verification failed).
#
# Certified GFI/FD values use only the Fisher classification and complete
# margin search. The preliminary two-cell heuristic cannot establish an
# exact value. RV is a separate metric with its stated binomial truncation
# bound and explicitly labelled Monte Carlo fallback for oversized grids.
#
#
# ======================================================================
# SIGNIFICANCE RULE
# ======================================================================
#
# FI, MFI, SFI, GFI, nGFI and Walter UFI use TWO-SIDED Fisher's exact
# test exclusively.
#
#     Significant      iff  p <  ALPHA
#     Non-significant  iff  p >= ALPHA
#
# The authoritative scalar reference is scipy.stats.fisher_exact.
# A vectorized reproduction of SciPy's own probability-ordering
# algorithm supplies the batch decisions. That reproduction is verified
# against SciPy at run time on a battery of tables. If it disagrees on
# any table, GFI and nGFI return NA.
#
#
# ======================================================================
# GFI
# ======================================================================
#
# One GFI unit is one transfer: subtract 1 from one cell and add 1 to
# another. N is therefore fixed.
#
# GFI = minimum number of transfers that crosses the Fisher threshold.
# GFQ = GFI / original N.
#
#
# ======================================================================
# nGFI
# ======================================================================
#
# One nGFI edit is ONE operation: +1 to one cell, or -1 from one cell,
# subject only to cells staying non-negative. N, row margins and column
# margins are all free.
#
# nGFI = min over all non-negative integer 2x2 tables T on the opposite
# side of the Fisher threshold of
#
#     |a'-a| + |b'-b| + |c'-c| + |d'-d|
#
# nGFQ = nGFI / ORIGINAL N.
#
# Because one transfer is exactly two edits:  nGFI <= 2*GFI.
#
#
# ======================================================================
# EXACT SEARCH GEOMETRY (used by both GFI and nGFI)
# ======================================================================
#
# Any non-negative integer 2x2 table is uniquely identified by
#
#     N' = grand total, R' = first-row total, C' = first-column total,
#     x  = upper-left cell
#
# Relative to the original table define
#
#     w = N'-N0,  u = R'-R0,  v = C'-C0,  z = x-a0
#
# The cell changes are
#
#     da = z,  db = u-z,  dc = v-z,  dd = w-u-v+z
#
# so the exact L1 edit distance is
#
#     D(z) = |z-0| + |z-u| + |z-v| + |z-(u+v-w)|
#
# a sum of distances to the four points  P = {0, u, v, u+v-w}.
#
# 1. D is convex piecewise linear in z. Its minimum over all z is
#
#        Dmin(w,u,v) = (p3+p4) - (p1+p2)          [p sorted]
#                    = max( |u| + |w-u| , |v| + |w-v| )
#
#    Both forms are checked against each other in the regression tests.
#
# 2. RIGOROUS PRUNING RULES. Summing the cell deltas gives w, so
#
#        D >= |w|
#        D >= |u| + |w-u|          [row pair bound]
#        D >= |v| + |w-v|          [column pair bound]
#
#    Hence D >= Dmin(w,u,v), and any table strictly better than a known
#    incumbent B must satisfy Dmin(w,u,v) < B.
#
# 3. SUBLEVEL SET. For any budget K the set { z : D(z) <= K } is a
#    closed integer interval [zlo,zhi] available in closed form from the
#    sorted points. No search is needed.
#
# 4. MEMBERSHIP TEST. For fixed margins (N',R',C') the two-sided Fisher
#    p-value is unimodal in x, so
#
#        { x : p >= ALPHA }
#
#    is ONE contiguous integer interval [L,H] that always contains the
#    hypergeometric mode. Therefore:
#
#      - significant baseline (target = acceptance interval):
#        [zlo,zhi] meets the target iff the mode, clamped into
#        [zlo,zhi] and into the support, is accepted. Zero Fisher
#        calls when the mode is inside the window (p there is 1),
#        otherwise one.
#
#      - non-significant baseline (target = the two significant tails):
#        [zlo,zhi] meets the target iff its lower endpoint or its upper
#        endpoint is rejected. One stacked Fisher call.
#
#    Both statements are exact consequences of unimodality. No boundary
#    bisection is required.
#
# 5. SEARCH ORDER. Margin triples are enumerated in ascending Dmin
#    level. Every triple is tested once against the current incumbent
#    budget. A passing triple is refined to its exact distance by
#    bisection on K (the membership test is monotone in K). The search
#    stops when the level index reaches the incumbent, at which point
#    the incumbent is certified globally minimal.
#
#    If the cumulative candidate count reaches the configured cap, the
#    completed levels still prove a lower bound, and the metric is
#    returned as an uncertified upper bound with that proven bracket.
#
# 6. COST. Candidate counts scale with the answer. The Fisher cost per
#    candidate also depends on N, so elapsed time depends on both:
#
#        GFI  needs about  4 * GFI^2   candidate margin pairs
#        nGFI needs about  2 * nGFI^3  candidate margin triples
#
#
# ======================================================================
# OTHER METRICS
# ======================================================================
#
# FI / MFI / SFI : Fisher exact. Every integer step of the toggle path
#                  is evaluated, vectorized in chunks. No monotonicity
#                  assumption at any point.
# Walter UFI     : Fisher exact, fixed margins, solved directly from the
#                  exact Fisher acceptance interval.
# PFI-Pearson    : Pearson chi-square continuous fixed-margin path,
#                  by definition.
# SFM            : Pearson chi-square sample-size scaling, by definition.
# AV             : baseline table classified by Fisher exact, Pearson
#                  chi-square, and Yates-corrected chi-square; flags
#                  test-choice disagreement and names the dissenter.
# RV             : exact enumeration of the fitted-binomial replication
#                  model; every replicate table classified by the
#                  verified Fisher engine.
# RR             : relative risk (Arm A vs Arm B) with the Katz log-RR
#                  Wald interval; agreement of "CI excludes 1" with
#                  the Fisher classification.
#
#
# ======================================================================
# CITATION
# ======================================================================
#
# IF YOU USE THIS CALCULATOR PLEASE CITE:
#
# Heston, T. F. (2025).
# Fragility Metrics Toolkit [Software]. Zenodo.
# https://doi.org/10.5281/zenodo.17254763
#
# (c) Thomas F. Heston 2025-2026. CC-BY 4.0
# ======================================================================


# ----------------------------------------------------------------------
# SciPy availability guard
# ----------------------------------------------------------------------

try:
    import scipy
except ImportError:
    try:
        import subprocess
        import sys
        subprocess.check_call(
            [sys.executable, "-m", "pip", "install", "-q", "scipy"]
        )
        import scipy
    except Exception:
        print("Please install scipy: pip install scipy")
        raise


import atexit
import os
import sys
import warnings
import multiprocessing
from concurrent.futures import ProcessPoolExecutor
from itertools import combinations, product

import numpy as np
from scipy.stats import (
    binom, fisher_exact, hypergeom, chi2_contingency, chi2, norm,
)


# ======================================================================
# CONFIGURATION
# ======================================================================

ALPHA = 0.05

# Rows evaluated per vectorized Fisher call.
FISHER_CHUNK = 250_000

# Candidate margin pairs / triples examined before the exact search
# stops certification and retains an explicitly labelled upper bound.
# Raise these to certify larger distances at the cost of runtime. Roughly:
#     GFI  certifies when   4 * GFI^2   <= GFI_MAX_CANDIDATES
#     nGFI certifies when   2 * nGFI^3  <= NGFI_MAX_CANDIDATES
GFI_MAX_CANDIDATES = 999_999_000_000
NGFI_MAX_CANDIDATES = 999_999_000_000

# Hard ceiling on the doubling ray used to construct a first
# non-significant -> significant nGFI upper bound.
NGFI_MAX_ADDITION = 10_000_000

# Independent brute-force signed-shell cross-check for tiny tables.
NGFI_EXHAUSTIVE_N = 20

# ---- r11: Resampling Variability (RV) ----

# Per-arm binomial tail mass excluded from the exact RV enumeration.
# The reported RV is exact to within the sum of the two excluded
# masses (union bound); that bound is computed exactly from the
# binomial cdf/sf and reported with the result.
RV_TAIL_EPS = 1e-12

# Cap on enumerated (XA, XB) replicate pairs. Above this the seeded
# Monte Carlo fallback is used and labelled as such. The truncated
# grid stays under this cap until arm sizes reach several hundred
# thousand.
RV_MAX_PAIRS = 30_000_000

# Monte Carlo fallback controls (fallback ONLY; never used when the
# exact enumeration fits under RV_MAX_PAIRS).
RV_MC_REPS = 20_000
RV_MC_SEED = 42

# ---- r10 certification control ----

# In the default "ask" mode, sweeps at or below this limit run
# automatically. Runtime also depends on N and Fisher tail costs;
# this candidate limit does NOT imply completion in a few seconds.
# Explicit certify=False skips even small sweeps. Larger sweeps follow `certify`
# parameter of compute_gfi_gfq / compute_ngfi_ngfq / fragility_summary.
AUTO_CERTIFY_CANDIDATES = 10_000_000

# Retained for compatibility with callers of earlier versions. r12 does
# not use a fixed candidate rate to promise a runtime; tail costs vary.
CERT_RATE_PER_SEC = 900_000

# Two-cell edit mixes: exact ascending-k scan when the search budget is
# at most this; larger budgets use doubling + bisection with a full
# split scan at every probed k (r5-equivalent upper bound).
PAIR_EXACT_K = 0

# ---- execution performance knobs (preserve certified metric values) ----

# Worker processes shared by certification and exact RV. 1 disables
# multiprocessing entirely.
CERT_WORKERS = max(1, min(8, os.cpu_count() or 1))

# Candidate rows accumulated before one batched (and, when large
# enough, multiprocess) membership pass.
GROUP_ROWS = 1_000_000

# Minimum rows in a group before the process pool is used; smaller
# groups run inline in the parent.
PARALLEL_MIN_ROWS = 50_000

# Use the pool for certification or exact RV at this predicted workload.
# Lowered in r12 so medium-sized searches also use multiple CPU cores.
PARALLEL_MIN_TOTAL = 100_000

# Smaller RV tasks keep all workers busy and bound the in-flight memory.
# The parent submits no more than twice CERT_WORKERS tasks at a time.
RV_BATCH_PAIRS = 25_000

# Full regression suite at CLI start-up.
RUN_REGRESSION_TESTS = False


# ======================================================================
# Core utilities
# ======================================================================

def validate_table(a, b, c, d):
    """Validate and normalize a 2x2 count table."""
    for x in (a, b, c, d):
        if not isinstance(x, (int, np.integer)):
            raise ValueError("Cells a,b,c,d must be integers.")
        if x < 0:
            raise ValueError("Cells a,b,c,d must be non-negative.")
    return int(a), int(b), int(c), int(d)


def n_total(a, b, c, d):
    return int(a + b + c + d)


def test_p(a, b, c, d):
    """Authoritative scalar two-sided SciPy Fisher exact p-value."""
    _, p = fisher_exact(
        [[int(a), int(b)], [int(c), int(d)]],
        alternative="two-sided",
    )
    return float(p)


def is_significant(p, alpha=ALPHA):
    return bool(p < alpha)


def state_label(p, alpha=ALPHA):
    return "significant" if p < alpha else "non-significant"


def table_l1_distance(origin, candidate):
    """Cellwise Manhattan distance."""
    return int(
        sum(abs(int(candidate[i]) - int(origin[i])) for i in range(4))
    )


def table_transfer_distance(origin, candidate):
    """Fixed-N cell-to-cell transfer distance (L1 / 2)."""
    if sum(origin) != sum(candidate):
        raise ValueError("Transfer distance requires equal grand totals.")
    return table_l1_distance(origin, candidate) // 2


def _flat_int(*values):
    """Broadcast to common shape and flatten as int64."""
    arrays = np.broadcast_arrays(
        *[np.asarray(x, dtype=np.int64) for x in values]
    )
    return tuple(
        np.asarray(x, dtype=np.int64).ravel() for x in arrays
    )


# ======================================================================
# Vectorized two-sided Fisher exact p-value
# ======================================================================
#
# Faithful reproduction of SciPy's own 2x2 two-sided fisher_exact
# probability-ordering algorithm, generalized to varying N. Verified
# against SciPy at run time; see _fisher_engine_ok().
# ======================================================================

def _vector_binary_search_pmf(N, r, c, target, lo, hi, negate=False):
    """
    Vectorized form of SciPy's monotone PMF binary search:
    largest i with f(i) <= target for ascending f.
    """
    N, r, c, lo, hi = _flat_int(N, r, c, lo, hi)

    target = np.broadcast_to(
        np.asarray(target, dtype=np.float64), N.shape
    ).astype(np.float64, copy=False)

    lo = lo.copy()
    hi = hi.copy()

    while np.any(lo < hi):
        active = np.flatnonzero(lo < hi)
        mid = lo[active] + (hi[active] - lo[active]) // 2

        value = hypergeom.pmf(mid, N[active], r[active], c[active])
        if negate:
            value = -value

        tgt = target[active]
        less = value < tgt
        greater = value > tgt
        equal = ~(less | greater)

        if np.any(less):
            lo[active[less]] = mid[less] + 1
        if np.any(greater):
            hi[active[greater]] = mid[greater] - 1
        if np.any(equal):
            lo[active[equal]] = mid[equal]
            hi[active[equal]] = mid[equal]

    final_value = hypergeom.pmf(lo, N, r, c)
    if negate:
        final_value = -final_value

    return np.where(final_value <= target, lo, lo - 1)


def _fisher_p_core(tables):
    """Two-sided Fisher p for an (M,4) int64 array with valid margins."""
    a = tables[:, 0]
    b = tables[:, 1]
    c = tables[:, 2]
    d = tables[:, 3]

    N = a + b + c + d
    row1 = a + b
    col1 = a + c

    out = np.ones(len(tables), dtype=np.float64)

    valid = (
        (N > 0)
        & (row1 > 0) & (row1 < N)
        & (col1 > 0) & (col1 < N)
    )
    idx = np.flatnonzero(valid)
    if not len(idx):
        return out

    x = a[idx]
    NN = N[idx]
    rr = row1[idx]
    cc = col1[idx]

    mode = (((cc + 1) * (rr + 1)) // (NN + 2)).astype(np.int64)

    p_exact = hypergeom.pmf(x, NN, rr, cc)
    p_mode = hypergeom.pmf(mode, NN, rr, cc)

    epsilon = 1e-14
    gamma = 1.0 + epsilon

    with np.errstate(divide="ignore", invalid="ignore"):
        rel = np.abs(p_exact - p_mode) / np.maximum(p_exact, p_mode)

    equal_mode = rel <= epsilon
    equal_mode |= (p_exact == 0.0) & (p_mode == 0.0) & (x == mode)

    result = np.empty(len(idx), dtype=np.float64)
    result[equal_mode] = 1.0

    # ---- observed below the mode ----
    lower = (~equal_mode) & (x < mode)
    if np.any(lower):
        xx = x[lower]
        N1 = NN[lower]
        r1 = rr[lower]
        c1 = cc[lower]
        m1 = mode[lower]
        p_obs = p_exact[lower]

        p_lower = hypergeom.cdf(xx, N1, r1, c1)
        far = hypergeom.pmf(c1, N1, r1, c1)

        simple = far > p_obs * gamma
        values = np.empty(len(xx), dtype=np.float64)
        values[simple] = p_lower[simple]

        act = ~simple
        if np.any(act):
            guess = _vector_binary_search_pmf(
                N1[act], r1[act], c1[act],
                -p_obs[act] * gamma,
                m1[act], c1[act],
                negate=True,
            )
            values[act] = (
                p_lower[act]
                + hypergeom.sf(guess, N1[act], r1[act], c1[act])
            )

        result[lower] = values

    # ---- observed at or above the mode ----
    upper = (~equal_mode) & (x >= mode)
    if np.any(upper):
        xx = x[upper]
        N1 = NN[upper]
        r1 = rr[upper]
        c1 = cc[upper]
        m1 = mode[upper]
        p_obs = p_exact[upper]

        p_upper = hypergeom.sf(xx - 1, N1, r1, c1)
        far = hypergeom.pmf(0, N1, r1, c1)

        simple = far > p_obs * gamma
        values = np.empty(len(xx), dtype=np.float64)
        values[simple] = p_upper[simple]

        act = ~simple
        if np.any(act):
            guess = _vector_binary_search_pmf(
                N1[act], r1[act], c1[act],
                p_obs[act] * gamma,
                np.zeros(int(np.sum(act)), dtype=np.int64),
                m1[act],
                negate=False,
            )
            values[act] = (
                p_upper[act]
                + hypergeom.cdf(guess, N1[act], r1[act], c1[act])
            )

        result[upper] = values

    out[idx] = np.minimum(result, 1.0)
    return out


def fisher_p_batch(tables):
    """Vectorized two-sided Fisher exact p-values for an (M,4) array."""
    tables = np.asarray(tables, dtype=np.int64)
    single = tables.ndim == 1
    if single:
        tables = tables.reshape(1, 4)

    if tables.ndim != 2 or tables.shape[1] != 4:
        raise ValueError("tables must have shape (M,4) or (4,).")
    if np.any(tables < 0):
        raise ValueError("All cells must be non-negative.")
    if len(tables) == 0:
        return float("nan") if single else np.empty(0, dtype=np.float64)

    out = np.empty(len(tables), dtype=np.float64)
    for start in range(0, len(tables), FISHER_CHUNK):
        block = tables[start:start + FISHER_CHUNK]
        out[start:start + len(block)] = _fisher_p_core(block)

    return float(out[0]) if single else out


def fisher_accepts(tables, alpha=ALPHA):
    """
    Vectorized decision:  two-sided Fisher p >= alpha  (non-significant).

    A cascade of rigorous pre-filters decides almost every row from a
    single O(1) pmf evaluation; tails (whose cost grows with N) are
    computed only for the narrow undecided band, and the full engine
    only for near-threshold rows. Facts used:

      accept 1:  pmf(x) is itself a term of the observed-side tail,
                 which is a component of the two-sided p, so
                     pmf(x) >= alpha  =>  p >= alpha.

      reject 1:  the observed-side tail has tail_count terms, each
                 <= pmf(x) by unimodality, and the probability-ordering
                 rule adds at most n_support extra points, each
                 <= pmf(x) * (1+1e-14), so
                     p <= (tail_count + n_support) * pmf(x) * (1+1e-14).
                 Below alpha (with a 1e-9 relative float guard), the
                 table is significant.

      accept 2:  tail >= alpha  =>  p >= alpha (tail is a component).

      reject 2:  p <= tail + n_support * pmf(x) * (1+1e-14).

    Rows decided by no filter get the full engine p-value, so the
    decision is always identical to fisher_p_batch(...) >= alpha.
    """
    tables = np.asarray(tables, dtype=np.int64)
    single = tables.ndim == 1
    if single:
        tables = tables.reshape(1, 4)

    a = tables[:, 0]
    b = tables[:, 1]
    c = tables[:, 2]
    d = tables[:, 3]

    N = a + b + c + d
    row1 = a + b
    col1 = a + c

    out = np.ones(len(tables), dtype=bool)   # degenerate margins: p = 1

    valid = (
        (N > 0)
        & (row1 > 0) & (row1 < N)
        & (col1 > 0) & (col1 < N)
    )
    idx = np.flatnonzero(valid)
    if len(idx):
        x = a[idx]
        NN = N[idx]
        rr = row1[idx]
        cc = col1[idx]

        mode = (((cc + 1) * (rr + 1)) // (NN + 2)).astype(np.int64)
        slo = np.maximum(0, rr + cc - NN)
        shi = np.minimum(rr, cc)
        n_pts = shi - slo + 1
        below = x < mode

        gamma = 1.0 + 1e-14
        guard = alpha * (1.0 - 1e-9)

        pmf_x = hypergeom.pmf(x, NN, rr, cc)

        res = np.empty(len(idx), dtype=bool)

        # ---- accept 1: single-term lower bound on the tail ----
        acc = pmf_x >= alpha
        res[acc] = True

        und = np.flatnonzero(~acc)
        if len(und):
            # ---- reject 1: all-terms upper bound, pmf only ----
            tail_count = np.where(
                below[und], x[und] - slo[und] + 1, shi[und] - x[und] + 1
            )
            bound = (
                (tail_count + n_pts[und]).astype(np.float64)
                * pmf_x[und] * gamma
            )
            rej = bound < guard
            res[und[rej]] = False

            mid = und[~rej]
            if len(mid):
                # ---- exact observed-side tail, one side at a time ----
                tail = np.empty(len(mid), dtype=np.float64)
                b_mid = np.flatnonzero(below[mid])
                if len(b_mid):
                    q = mid[b_mid]
                    tail[b_mid] = hypergeom.cdf(x[q], NN[q], rr[q], cc[q])
                a_mid = np.flatnonzero(~below[mid])
                if len(a_mid):
                    q = mid[a_mid]
                    tail[a_mid] = hypergeom.sf(
                        x[q] - 1, NN[q], rr[q], cc[q]
                    )

                # ---- accept 2 ----
                acc2 = tail >= alpha
                res[mid[acc2]] = True

                und2 = np.flatnonzero(~acc2)
                if len(und2):
                    # ---- reject 2 ----
                    q = mid[und2]
                    bound2 = (
                        tail[und2]
                        + n_pts[q].astype(np.float64) * pmf_x[q] * gamma
                    )
                    rej2 = bound2 < guard
                    res[q[rej2]] = False

                    # ---- full engine for the near-threshold band ----
                    rest = q[~rej2]
                    if len(rest):
                        res[rest] = (
                            fisher_p_batch(tables[idx[rest]]) >= alpha
                        )

        out[idx] = res

    return bool(out[0]) if single else out


# ----------------------------------------------------------------------
# Engine verification. Exactness of GFI and nGFI depends on the batch
# engine agreeing with SciPy, so this gates both metrics.
# ----------------------------------------------------------------------

_ENGINE_STATE = {"checked": False, "ok": False, "detail": ""}


def _engine_test_tables():
    tables = [
        (6, 2, 1, 4), (8, 2, 1, 5), (13, 87, 25, 75), (75, 47, 21, 43),
        (0, 1, 1, 3), (1, 0, 0, 1), (0, 0, 3, 4), (5, 0, 0, 5),
        (29, 13, 24, 11), (2, 8, 8, 2), (10, 10, 10, 10),
        (1, 99, 6, 94), (100, 0, 0, 100), (3, 0, 0, 4),
        (8370, 107880, 11857, 104388),
        (1764, 3, 2, 1761), (50000, 50000, 50100, 49900),
    ]
    rng = np.random.default_rng(20260822)
    for _ in range(400):
        tables.append(tuple(int(v) for v in rng.integers(0, 60, size=4)))
    for _ in range(60):
        tables.append(tuple(int(v) for v in rng.integers(0, 20000, size=4)))
    return [t for t in tables if sum(t) > 0]


def _fisher_engine_ok():
    """Verify the batch engine against SciPy once per session."""
    if _ENGINE_STATE["checked"]:
        return _ENGINE_STATE["ok"]

    tables = _engine_test_tables()
    arr = np.array(tables, dtype=np.int64)

    batch = fisher_p_batch(arr)
    scalar = np.array([test_p(*t) for t in tables], dtype=np.float64)

    bad_p = ~np.isclose(batch, scalar, rtol=1e-12, atol=1e-15)
    decision = fisher_accepts(arr, ALPHA)
    bad_d = decision != (scalar >= ALPHA)

    if np.any(bad_p) or np.any(bad_d):
        j = int(np.flatnonzero(bad_p | bad_d)[0])
        _ENGINE_STATE["detail"] = (
            f"table {tables[j]}: batch p={batch[j]!r}, "
            f"scipy p={scalar[j]!r}"
        )
        _ENGINE_STATE["ok"] = False
    else:
        _ENGINE_STATE["ok"] = True
        _ENGINE_STATE["detail"] = (
            f"{len(tables)} tables matched scipy.stats.fisher_exact."
        )

    _ENGINE_STATE["checked"] = True
    return _ENGINE_STATE["ok"]


# ======================================================================
# Fixed-margin table utilities
# ======================================================================

def tables_from_xrc(x, r, c, N):
    """Build tables from upper-left cell x and margins r, c, N."""
    x, r, c, N = _flat_int(x, r, c, N)
    return np.column_stack([x, r - x, c - x, N - r - c + x])


def support_bounds(r, c, N):
    """Hypergeometric support bounds for the upper-left cell."""
    r, c, N = _flat_int(r, c, N)
    return np.maximum(0, r + c - N), np.minimum(r, c)


def fisher_mode(r, c, N):
    """Integer hypergeometric mode, clamped into the support."""
    r, c, N = _flat_int(r, c, N)
    lo, hi = support_bounds(r, c, N)
    mode = ((c + 1) * (r + 1)) // (N + 2)
    return np.minimum(np.maximum(mode, lo), hi)


# ======================================================================
# Exact L1 geometry in margin coordinates
# ======================================================================

def l1_min_distance(w, u, v):
    """
    Exact minimum L1 edit distance over all z for fixed (w,u,v):

        max( |u| + |w-u| , |v| + |w-v| )
    """
    w, u, v = _flat_int(w, u, v)
    return np.maximum(
        np.abs(u) + np.abs(w - u),
        np.abs(v) + np.abs(w - v),
    )


def l1_distance_wuvz(w, u, v, z):
    """Exact L1 edit distance for one (w,u,v,z)."""
    w, u, v, z = _flat_int(w, u, v, z)
    return (
        np.abs(z)
        + np.abs(u - z)
        + np.abs(v - z)
        + np.abs(w - u - v + z)
    )


def sublevel_interval(w, u, v, K):
    """
    Closed-form integer interval { z : D(z) <= K }.

    D(z) = sum of distances from z to the four points {0,u,v,u+v-w},
    which is convex piecewise linear, so the sublevel set is an
    interval. Returns (zlo, zhi, non_empty).
    """
    w, u, v = _flat_int(w, u, v)
    K = np.broadcast_to(
        np.asarray(K, dtype=np.int64), w.shape
    ).astype(np.int64, copy=False)

    P = np.column_stack([np.zeros_like(u), u, v, u + v - w])
    P.sort(axis=1)
    p1, p2, p3, p4 = P[:, 0], P[:, 1], P[:, 2], P[:, 3]

    dmin = (p3 + p4) - (p1 + p2)
    e = K - dmin
    ok = e >= 0
    e_ok = np.where(ok, e, 0)
    half = e_ok // 2

    span_r = p4 - p3
    rem_r = np.maximum(e_ok - 2 * span_r, 0)
    zhi = np.where(half <= span_r, p3 + half, p4 + rem_r // 4)

    span_l = p2 - p1
    rem_l = np.maximum(e_ok - 2 * span_l, 0)
    zlo = np.where(half <= span_l, p2 - half, p1 - rem_l // 4)

    return zlo, zhi, ok


def _margin_hits(origin, W, U, V, K, baseline_sig, alpha=ALPHA):
    """
    For each margin triple (w,u,v), decide whether ANY table at those
    margins is both in the target region and within L1 distance K of
    the origin.

    Exact. Uses unimodality of the Fisher p-value in x:

      - significant baseline (target = acceptance interval): the window
        meets the target iff the mode, clamped into the window, is
        accepted. When the mode lies inside the window, the engine's
        p-value there is exactly 1 by construction, so acceptance
        needs NO Fisher call.

      - non-significant baseline (target = the significant tails): the
        window meets the target iff one of its endpoints is rejected.
        Both endpoint batteries are decided in one stacked call.

    Returns (hit_mask, x_witness).
    """
    a0, b0, c0, d0 = origin
    N0 = a0 + b0 + c0 + d0
    R0 = a0 + b0
    C0 = a0 + c0

    W, U, V = _flat_int(W, U, V)
    n = len(W)

    hit = np.zeros(n, dtype=bool)
    xw = np.zeros(n, dtype=np.int64)
    if n == 0:
        return hit, xw

    NN = N0 + W
    R = R0 + U
    C = C0 + V

    zlo, zhi, ok = sublevel_interval(W, U, V, K)

    ok &= (NN > 0) & (R >= 0) & (R <= NN) & (C >= 0) & (C <= NN)
    idx = np.flatnonzero(ok)
    if not len(idx):
        return hit, xw

    slo, shi = support_bounds(R[idx], C[idx], NN[idx])
    xlo = np.maximum(zlo[idx] + a0, slo)
    xhi = np.minimum(zhi[idx] + a0, shi)

    live = xlo <= xhi
    idx = idx[live]
    xlo = xlo[live]
    xhi = xhi[live]
    if not len(idx):
        return hit, xw

    Rr, Cc, Nn = R[idx], C[idx], NN[idx]

    if baseline_sig:
        # Target = acceptance interval, which always contains the mode.
        # At the mode itself the two-sided p is exactly 1, so any
        # candidate interval containing the mode is accepted with no
        # Fisher evaluation at all.
        m = fisher_mode(Rr, Cc, Nn)
        x = np.minimum(np.maximum(m, xlo), xhi)
        good = (m >= xlo) & (m <= xhi)
        need = np.flatnonzero(~good)
        if len(need):
            good[need] = fisher_accepts(
                tables_from_xrc(x[need], Rr[need], Cc[need], Nn[need]),
                alpha,
            )
        hit[idx] = good
        xw[idx] = x
    else:
        # Target = the two significant tails at the ends of the support.
        # Both endpoint batteries are decided in a single batched call.
        tabs = np.vstack([
            tables_from_xrc(xlo, Rr, Cc, Nn),
            tables_from_xrc(xhi, Rr, Cc, Nn),
        ])
        acc = fisher_accepts(tabs, alpha)
        nrows = len(xlo)
        lo_rej = ~acc[:nrows]
        hi_rej = ~acc[nrows:]
        good = lo_rej | hi_rej
        x = np.where(lo_rej, xlo, xhi)
        hit[idx] = good
        xw[idx] = x

    return hit, xw


def _exact_distance_at_margins(origin, w, u, v, k_lo, k_hi,
                               baseline_sig, alpha=ALPHA):
    """
    Smallest K in [k_lo, k_hi] for which a target table exists at these
    margins. The membership test is monotone in K, so bisection is
    exact. Assumes a hit at k_hi. Returns (K, witness_table).
    """
    lo, hi = int(k_lo), int(k_hi)
    while lo < hi:
        mid = (lo + hi) // 2
        got, _ = _margin_hits(
            origin, [w], [u], [v], mid, baseline_sig, alpha
        )
        if got[0]:
            hi = mid
        else:
            lo = mid + 1

    got, xw = _margin_hits(origin, [w], [u], [v], lo, baseline_sig, alpha)
    if not got[0]:
        return None, None

    N0 = sum(origin)
    R0 = origin[0] + origin[1]
    C0 = origin[0] + origin[2]
    table = tables_from_xrc(
        [int(xw[0])], [R0 + u], [C0 + v], [N0 + w]
    )[0]
    return lo, tuple(int(t) for t in table)


# ======================================================================
# Level enumeration (ascending proven lower bound)
# ======================================================================

def _gfi_level_blocks(D, N, R0, C0):
    """
    Margin pairs (u,v) at fixed N whose transfer lower bound is exactly
    D, that is max(|u|,|v|) = D. Yields (U, V) int64 arrays.
    """
    v_all = np.arange(-D, D + 1, dtype=np.int64)
    edge = np.array([-D, D], dtype=np.int64)
    inner = np.arange(-D + 1, D, dtype=np.int64)

    for U, V in (
        (np.repeat(edge, len(v_all)), np.tile(v_all, len(edge))),
        (np.repeat(inner, len(edge)), np.tile(edge, len(inner))),
    ):
        if not len(U):
            continue
        keep = (
            (R0 + U >= 0) & (R0 + U <= N)
            & (C0 + V >= 0) & (C0 + V <= N)
        )
        if np.any(keep):
            yield U[keep], V[keep]


def _ngfi_level_blocks(D, N0, R0, C0):
    """
    Margin triples (w,u,v) whose L1 lower bound is exactly D.

    row cost  rho(u) = |w| + 2*dist(u, [min(0,w),max(0,w)])
    col cost  gam(v) = |w| + 2*dist(v, [min(0,w),max(0,w)])
    level D   iff max(rho,gam) = D, which forces D == w (mod 2).

    Yields (W, U, V) int64 arrays.
    """
    for w in range(-D, D + 1):
        if ((D - abs(w)) % 2) != 0:
            continue
        Nn = N0 + w
        if Nn <= 0:
            continue

        t = (D - abs(w)) // 2
        lo_i, hi_i = min(0, w), max(0, w)

        le = np.arange(lo_i - t, hi_i + t + 1, dtype=np.int64)
        if t == 0:
            eq = le
            lt = np.empty(0, dtype=np.int64)
        else:
            eq = np.array([lo_i - t, hi_i + t], dtype=np.int64)
            lt = np.arange(lo_i - t + 1, hi_i + t, dtype=np.int64)

        for A, B in ((eq, le), (lt, eq)):
            if not len(A) or not len(B):
                continue
            U = np.repeat(A, len(B))
            V = np.tile(B, len(A))
            keep = (
                (R0 + U >= 0) & (R0 + U <= Nn)
                & (C0 + V >= 0) & (C0 + V <= Nn)
            )
            if np.any(keep):
                W = np.full(int(np.sum(keep)), w, dtype=np.int64)
                yield W, U[keep], V[keep]


def _gfi_level_arrays(D, N, R0, C0):
    """All level-D GFI margin pairs as one (W,U,V) triple, or None."""
    parts = list(_gfi_level_blocks(D, N, R0, C0))
    if not parts:
        return None
    U = np.concatenate([p[0] for p in parts])
    V = np.concatenate([p[1] for p in parts])
    W = np.zeros(len(U), dtype=np.int64)
    return W, U, V


def _ngfi_level_arrays(D, N0, R0, C0):
    """All level-D nGFI margin triples as one (W,U,V) triple, or None."""
    parts = list(_ngfi_level_blocks(D, N0, R0, C0))
    if not parts:
        return None
    W = np.concatenate([p[0] for p in parts])
    U = np.concatenate([p[1] for p in parts])
    V = np.concatenate([p[2] for p in parts])
    return W, U, V


# ======================================================================
# Batched, optionally multiprocess membership testing
# ======================================================================

def _hits_worker(payload):
    """Worker process: membership test on a slice of candidates."""
    origin, W, U, V, K, baseline_sig, alpha = payload
    hit, xw = _margin_hits(origin, W, U, V, K, baseline_sig, alpha)
    idx = np.flatnonzero(hit)
    return idx, xw[idx]


_POOL_STATE = {"pool": None, "broken": False}


def _disable_pool(exc):
    """Report a pool failure once, then use the exact serial path."""
    if not _POOL_STATE["broken"]:
        warnings.warn(
            "Multiprocessing unavailable; using serial computation. "
            f"{type(exc).__name__}: {exc}",
            RuntimeWarning, stacklevel=2,
        )
    _POOL_STATE["broken"] = True
    _shutdown_pool()


def _get_pool():
    """Lazily create one shared process pool for certification and RV."""
    if CERT_WORKERS <= 1 or _POOL_STATE["broken"]:
        return None
    if _POOL_STATE["pool"] is None:
        try:
            # Spawn needs an importable main script. Detect raw notebook /
            # interactive execution before starting doomed Windows workers.
            main_file = getattr(sys.modules.get("__main__"), "__file__", None)
            if (multiprocessing.get_start_method() in ("spawn", "forkserver")
                    and (not main_file or not os.path.isfile(main_file))):
                raise RuntimeError(
                    "Save the program as a .py file and run it to enable "
                    "multiprocessing in this environment."
                )
            _POOL_STATE["pool"] = ProcessPoolExecutor(
                max_workers=CERT_WORKERS
            )
        except Exception as exc:
            _disable_pool(exc)
            return None
    return _POOL_STATE["pool"]


def _shutdown_pool():
    pool = _POOL_STATE["pool"]
    if pool is not None:
        pool.shutdown(wait=False, cancel_futures=True)
        _POOL_STATE["pool"] = None


atexit.register(_shutdown_pool)


def _process_rows(origin, W, U, V, K, baseline_sig, alpha,
                  allow_pool=True):
    """
    Membership test over all rows, split across the process pool when
    the group is large enough. Partitioning changes which process
    evaluates a candidate, never whether it is evaluated; any pool
    failure falls back to the inline path with identical results.

    Returns (hit_indices, x_witnesses).
    """
    rows = len(W)
    pool = (
        _get_pool()
        if (allow_pool and rows >= PARALLEL_MIN_ROWS)
        else None
    )

    if pool is not None:
        edges = np.linspace(0, rows, 2 * CERT_WORKERS + 1).astype(int)
        try:
            futures = []
            for i in range(len(edges) - 1):
                s, e = int(edges[i]), int(edges[i + 1])
                if e > s:
                    futures.append((s, pool.submit(
                        _hits_worker,
                        (origin, W[s:e], U[s:e], V[s:e],
                         K, baseline_sig, alpha),
                    )))
            parts_i, parts_x = [], []
            for s, fut in futures:
                idx, xs = fut.result()
                parts_i.append(idx + s)
                parts_x.append(xs)
            if parts_i:
                return np.concatenate(parts_i), np.concatenate(parts_x)
            return (np.empty(0, dtype=np.int64),
                    np.empty(0, dtype=np.int64))
        except Exception as exc:
            for _, future in futures:
                future.cancel()
            _disable_pool(exc)

    hit, xw = _margin_hits(origin, W, U, V, K, baseline_sig, alpha)
    idx = np.flatnonzero(hit)
    return idx, xw[idx]


def _certify_levels(origin, alpha, baseline_sig, start, level_fn,
                    l1_budget, dist_from_l1, max_candidates,
                    allow_pool=True):
    """
    Level-ascending exact certification, batched and parallel.

    start    = (best_d, best_t, best_p, best_src): rigorous incumbent.
    level_fn(D) -> (W,U,V) arrays of every margin candidate whose exact
               lower bound is level D in metric units, or None.
    l1_budget(best_d) -> the L1 budget K a strictly better table must
               satisfy (2*best_d-2 for GFI, best_d-1 for nGFI).
    dist_from_l1(k) -> metric distance from an L1 distance
               (k//2 for GFI, k for nGFI).

    Every candidate below the incumbent is tested exactly once. Hits
    are refined to their exact distance by bisection (monotone in K).
    Returns (best_d, best_t, best_p, best_src,
             examined, aborted, level_done).
    """
    best_d, best_t, best_p, best_src = start

    examined = 0
    aborted = False
    level_done = 0

    group_W, group_U, group_V = [], [], []
    group_rows = 0

    def flush(upto):
        nonlocal best_d, best_t, best_p, best_src
        nonlocal group_W, group_U, group_V, group_rows, level_done
        if group_rows:
            W = np.concatenate(group_W)
            U = np.concatenate(group_U)
            V = np.concatenate(group_V)
            K = l1_budget(best_d)
            pending, _ = _process_rows(
                origin, W, U, V, K, baseline_sig, alpha,
                allow_pool=allow_pool,
            )
            # Refine hits tightest-first. After every improvement the
            # surviving hits are re-filtered against the SHRUNKEN
            # budget in one vectorized call, so a loose starting
            # incumbent never triggers per-hit bisections en masse.
            while len(pending):
                K = l1_budget(best_d)
                klo = l1_min_distance(
                    W[pending], U[pending], V[pending]
                )
                keep = klo <= K
                pending = pending[keep]
                if not len(pending):
                    break
                hit2, _ = _margin_hits(
                    origin, W[pending], U[pending], V[pending],
                    K, baseline_sig, alpha,
                )
                pending = pending[hit2]
                if not len(pending):
                    break
                klo = l1_min_distance(
                    W[pending], U[pending], V[pending]
                )
                pos = int(np.argmin(klo))
                j = int(pending[pos])
                k_exact, table = _exact_distance_at_margins(
                    origin, int(W[j]), int(U[j]), int(V[j]),
                    int(klo[pos]), K, baseline_sig, alpha,
                )
                pending = np.delete(pending, pos)
                if k_exact is None:
                    continue
                d_new = dist_from_l1(k_exact)
                if d_new < best_d:
                    best_d = int(d_new)
                    best_t = table
                    best_p = test_p(*table)
                    best_src = "exact margin certification"
        level_done = max(level_done, upto)
        group_W, group_U, group_V = [], [], []
        group_rows = 0

    D = 1
    while D < best_d:
        arrs = level_fn(D)
        if arrs is not None:
            W, U, V = arrs
            examined += len(W)
            if examined > max_candidates:
                aborted = True
                flush(D - 1)          # pending levels are fully counted
                break
            group_W.append(W)
            group_U.append(U)
            group_V.append(V)
            group_rows += len(W)

        if group_rows >= GROUP_ROWS or D + 1 >= best_d:
            flush(D)
        elif group_rows == 0:
            level_done = max(level_done, D)

        D += 1

    if not aborted and group_rows:
        flush(D - 1)

    return best_d, best_t, best_p, best_src, examined, aborted, level_done


# ======================================================================
# r10 certification decision (predict cost, then auto / ask / skip)
# ======================================================================

def _predict_gfi_candidates(best_d):
    """Certification workload for GFI: about 4 * GFI^2 margin pairs."""
    return max(0, 4 * best_d * best_d)


def _predict_ngfi_candidates(best_d):
    """Certification workload for nGFI: about 2 * nGFI^3 margin triples."""
    return max(0, 2 * best_d ** 3)


def _humanize_count(n):
    if n >= 1e9:
        return f"{n / 1e9:.1f} billion"
    if n >= 1e6:
        return f"{n / 1e6:.1f} million"
    return f"{n:,.0f}"


def _humanize_seconds(seconds):
    if seconds < 60:
        return f"{max(seconds, 1):.0f} seconds"
    if seconds < 3600:
        return f"{seconds / 60:.0f} minutes"
    return f"{seconds / 3600:.1f} hours"


def _decide_certification(metric, fast_value, fast_label,
                          predicted, certify):
    """
    Decide whether to run a certification sweep. Explicit False always
    skips it; True always runs it. "ask" auto-certifies small sweeps and
    offers a prompt for larger ones. Internal "auto" mode auto-certifies
    small sweeps but leaves larger ones for the combined summary prompt.
    Returns (run_sweep, decision_note).
    """
    if certify not in (True, False, "ask", "auto"):
        raise ValueError('certify must be True, False, or "ask".')
    size = _humanize_count(predicted)

    if certify is False:
        return False, (
            f"Certification skipped (certify=False; predicted "
            f"~{size} candidates)."
        )
    if certify is True or predicted <= AUTO_CERTIFY_CANDIDATES:
        return True, ""
    if certify == "auto":
        return False, (
            f"Certification deferred (predicted ~{size} candidates "
            "exceeds the automatic-certification limit)."
        )

    # certify == "ask"
    if not sys.stdin.isatty():
        return False, (
            f"Certification skipped (non-interactive session; predicted "
            f"~{size} candidates). Pass certify=True to "
            "force the sweep."
        )

    print()
    print(f"Fast result: {metric} = {fast_value}   ({fast_label})")
    print(f"Certification would examine ~{size} candidates.")
    print("Runtime depends on table size and available CPU cores; "
          "large searches can take minutes or longer.")
    try:
        answer = input(
            f"Run the {metric} certification now? [y/N]: "
        ).strip().lower()
    except (EOFError, KeyboardInterrupt):
        return False, (
            f"Certification skipped (no terminal input available; "
            f"predicted ~{size} candidates)."
        )

    if answer in ("y", "yes"):
        return True, ""
    return False, (
        f"Certification declined at the prompt (predicted ~{size} "
        f"candidates)."
    )


# ======================================================================
# GFI incumbent construction
# ======================================================================

def _gfi_initial_incumbent(origin, alpha=ALPHA):
    """
    Any real fixed-N opposite-state table gives a rigorous upper bound.
    Returns (transfer_distance, table, p, source) or None.
    """
    a, b, c, d = origin
    N = sum(origin)
    baseline_sig = test_p(*origin) < alpha

    if baseline_sig:
        # A zeroed row or column always has Fisher p = 1.
        candidates = []
        for table in (
            (0, 0, a + c, b + d),
            (a + c, b + d, 0, 0),
            (0, a + b, 0, c + d),
            (a + b, 0, c + d, 0),
        ):
            p = test_p(*table)
            if p >= alpha:
                candidates.append(
                    (table_transfer_distance(origin, table),
                     table, p, "margin collapse")
                )
        if not candidates:
            return None
        return min(candidates, key=lambda x: x[0])

    # Every complete-separation table at this N, checked exactly.
    # Vectorized argmin over transfer distance; first minimum in the
    # same enumeration order as before.
    k = np.arange(0, N + 1, dtype=np.int64)
    zero = np.zeros_like(k)
    sep = np.concatenate([
        np.column_stack([k, zero, zero, N - k]),
        np.column_stack([zero, k, N - k, zero]),
    ])
    sig = ~fisher_accepts(sep, alpha)
    if not np.any(sig):
        return None

    sig_rows = sep[sig]
    dist = np.abs(
        sig_rows - np.asarray(origin, dtype=np.int64)[None, :]
    ).sum(axis=1) // 2
    j = int(np.argmin(dist))
    table = tuple(int(t) for t in sig_rows[j])
    return (int(dist[j]), table, test_p(*table), "complete separation")


def _gfi_tighten_rays(origin, current, alpha=ALPHA):
    """All 12 single-transfer rays, every integer step evaluated."""
    baseline_sig = test_p(*origin) < alpha
    best_d, best_t, best_p, best_src = current
    origin_arr = np.asarray(origin, dtype=np.int64)

    for src in range(4):
        for dst in range(4):
            if src == dst:
                continue
            top = min(int(origin_arr[src]), best_d - 1)
            if top <= 0:
                continue

            for start in range(1, top + 1, FISHER_CHUNK):
                stop = min(top, start + FISHER_CHUNK - 1)
                k = np.arange(start, stop + 1, dtype=np.int64)

                tables = np.repeat(origin_arr[None, :], len(k), axis=0)
                tables[:, src] -= k
                tables[:, dst] += k

                sig = ~fisher_accepts(tables, alpha)
                hits = np.flatnonzero(sig != baseline_sig)
                if len(hits):
                    j = int(hits[0])
                    best_d = int(k[j])
                    best_t = tuple(int(t) for t in tables[j])
                    best_p = test_p(*best_t)
                    best_src = "single transfer ray"
                    break

    return best_d, best_t, best_p, best_src


# ======================================================================
# Exact GFI / GFQ
# ======================================================================

def compute_gfi_gfq(a, b, c, d, alpha=ALPHA, certify="ask"):
    """
    Global Fragility Index: fast rigorous upper bound first, exact
    certification second.

    The fast stage (margin collapse / complete separation plus every
    single-transfer ray, scanned exactly) produces a real flipping
    table, so its distance is a rigorous upper bound and is reported
    immediately. The certification sweep then proves global minimality;
    its workload is predicted in advance and run, asked about, or
    skipped according to `certify` ("ask" / True / False). The result
    dict flags the difference: certified=True means proven global
    minimum; certified=False means upper bound from the fast stage.
    """
    origin = validate_table(a, b, c, d)
    N = sum(origin)

    base = {
        "GFI": None, "GFQ": None,
        "baseline_p": None, "baseline_state": None, "target_state": None,
        "final_p": None, "witness_table": None, "witness_path": [],
        "exact": False, "certified": False,
        "lower_bound": None, "upper_bound": None,
        "incumbent": None, "incumbent_source": None,
        "candidates_examined": 0,
        "test_used": "fisher_exact",
        "algorithm": "exact Fisher margin-coordinate certification",
        "note": "",
    }

    if N == 0:
        base["note"] = "Empty table."
        return base

    if not _fisher_engine_ok():
        base["note"] = (
            "NA: vectorized Fisher engine does not match "
            f"scipy.stats.fisher_exact ({_ENGINE_STATE['detail']}). "
            "Exactness cannot be guaranteed, so no value is reported."
        )
        return base

    baseline_p = test_p(*origin)
    baseline_sig = baseline_p < alpha

    base["baseline_p"] = baseline_p
    base["baseline_state"] = state_label(baseline_p, alpha)
    base["target_state"] = (
        "non-significant" if baseline_sig else "significant"
    )

    R0 = origin[0] + origin[1]
    C0 = origin[0] + origin[2]

    initial = _gfi_initial_incumbent(origin, alpha)
    if initial is None:
        best_d, best_t, best_p, best_src = N + 1, None, None, None
    else:
        best_d, best_t, best_p, best_src = _gfi_tighten_rays(
            origin, initial, alpha
        )

    incumbent_d = best_d
    base["incumbent"] = int(incumbent_d)
    base["incumbent_source"] = best_src

    # ---- r10: predict the certification cost, then auto / ask / skip ----
    if best_t is not None:
        run_sweep, decision_note = _decide_certification(
            "GFI", best_d,
            "upper bound; every single-transfer ray scanned exactly",
            _predict_gfi_candidates(best_d), certify,
        )
        if not run_sweep:
            verified_p = test_p(*best_t)
            if (verified_p < alpha) == baseline_sig:
                base["note"] = (
                    "NA: GFI fast witness failed scalar Fisher "
                    "verification."
                )
                return base
            if table_transfer_distance(origin, best_t) != best_d:
                base["note"] = (
                    "NA: GFI fast witness distance does not match GFI."
                )
                return base
            base.update({
                "GFI": int(best_d),
                "GFQ": best_d / N,
                "final_p": float(verified_p),
                "witness_table": tuple(int(x) for x in best_t),
                "witness_path": [tuple(int(x) for x in best_t)],
                "exact": False,
                "certified": False,
                "lower_bound": None,
                "upper_bound": int(best_d),
                "note": (
                    f"GFI <= {best_d}: UPPER BOUND from the fast search "
                    f"({best_src}). " + decision_note
                ),
            })
            return base

    # ---- level-ascending exact certification (batched, parallel) ----
    (best_d, best_t, best_p, best_src,
     examined, aborted, level_done) = _certify_levels(
        origin, alpha, baseline_sig,
        (best_d, best_t, best_p, best_src),
        lambda D: _gfi_level_arrays(D, N, R0, C0),
        lambda bd: 2 * bd - 2,
        lambda k: k // 2,
        GFI_MAX_CANDIDATES,
        allow_pool=(4 * best_d * best_d >= PARALLEL_MIN_TOTAL),
    )

    base["candidates_examined"] = int(examined)

    if aborted:
        base["lower_bound"] = int(level_done + 1)
        base["upper_bound"] = int(best_d) if best_t is not None else None
        if best_t is not None:
            verified_p = test_p(*best_t)
            if ((verified_p < alpha) != baseline_sig
                    and table_transfer_distance(origin, best_t) == best_d):
                base.update({
                    "GFI": int(best_d),
                    "GFQ": best_d / N,
                    "final_p": float(verified_p),
                    "witness_table": tuple(int(x) for x in best_t),
                    "witness_path": [tuple(int(x) for x in best_t)],
                    "exact": False,
                    "certified": False,
                })
                base["note"] = (
                    f"GFI <= {best_d}: UPPER BOUND; certification "
                    f"stopped at the candidate cap after {examined:,} "
                    f"margin pairs. Proven bracket: "
                    f"{base['lower_bound']} <= GFI <= {best_d}."
                )
                return base
        base["note"] = (
            f"NA: exact certification stopped after {examined:,} "
            f"candidate margin pairs (cap GFI_MAX_CANDIDATES = "
            f"{GFI_MAX_CANDIDATES:,}) with no flipping table found. "
            f"Proven lower bound: GFI >= {base['lower_bound']}."
        )
        return base

    if best_t is None:
        base["certified"] = True
        base["final_p"] = baseline_p
        base["note"] = (
            "No opposite significance classification exists at this "
            "fixed grand total. Certified by complete margin search."
        )
        return base

    # ---- independent scalar verification of the reported answer ----
    verified_p = test_p(*best_t)
    if (verified_p < alpha) == baseline_sig:
        base["note"] = "NA: GFI witness failed scalar Fisher verification."
        return base
    if table_transfer_distance(origin, best_t) != best_d:
        base["note"] = "NA: GFI witness distance does not match GFI."
        return base

    base.update({
        "GFI": int(best_d),
        "GFQ": best_d / N,
        "final_p": float(verified_p),
        "witness_table": tuple(int(x) for x in best_t),
        "witness_path": [tuple(int(x) for x in best_t)],
        "exact": True,
        "certified": True,
        "lower_bound": int(best_d),
        "upper_bound": int(best_d),
        "incumbent_source": best_src,
        "note": (
            f"GFI = {best_d}; global fixed-N minimum certified over "
            f"{examined:,} candidate margin pairs."
        ),
    })
    return base


# ======================================================================
# nGFI incumbent construction
# ======================================================================

def _ngfi_initial_incumbent(origin, alpha=ALPHA, gfi_result=None):
    """
    Rigorous nGFI upper bound from real opposite-state tables.
    Returns (l1_distance, table, p, source) or None.
    """
    baseline_sig = test_p(*origin) < alpha
    a, b, c, d = origin
    candidates = []

    # GFI witness, certified or not, is always a real flipping table.
    if gfi_result is not None:
        w = gfi_result.get("witness_table")
        if w is not None:
            w = tuple(int(x) for x in w)
            p = test_p(*w)
            if (p < alpha) != baseline_sig:
                candidates.append(
                    (table_l1_distance(origin, w), w, p, "GFI witness")
                )

    if baseline_sig:
        # Deleting a whole row or column gives Fisher p = 1.
        for w in ((0, 0, c, d), (a, b, 0, 0), (0, b, 0, d), (a, 0, c, 0)):
            if sum(w) <= 0:
                continue
            p = test_p(*w)
            if p >= alpha:
                candidates.append(
                    (table_l1_distance(origin, w), w, p, "margin deletion")
                )
    else:
        # Diagonal additions always reach significance eventually.
        # r9: after the doubling ray finds a flip at hit_k, the whole
        # ray 1..hit_k is scanned exactly (vectorized) for its first
        # flip, so the incumbent is the tightest table on this ray.
        origin_arr = np.asarray(origin, dtype=np.int64)
        for i, j in ((0, 3), (1, 2)):
            hit_k = None
            k = 1
            while k <= NGFI_MAX_ADDITION:
                w = list(origin)
                w[i] += k
                w[j] += k
                if test_p(*w) < alpha:
                    hit_k = k
                    break
                k *= 2
            if hit_k is None:
                continue

            best_k = hit_k
            for start in range(1, hit_k + 1, FISHER_CHUNK):
                stop = min(hit_k, start + FISHER_CHUNK - 1)
                ks = np.arange(start, stop + 1, dtype=np.int64)
                tables = np.repeat(origin_arr[None, :], len(ks), axis=0)
                tables[:, i] += ks
                tables[:, j] += ks
                sig = ~fisher_accepts(tables, alpha)
                hits = np.flatnonzero(sig)
                if len(hits):
                    best_k = int(ks[int(hits[0])])
                    break

            w = list(origin)
            w[i] += best_k
            w[j] += best_k
            w = tuple(w)
            candidates.append(
                (2 * best_k, w, test_p(*w), "diagonal additions")
            )

    if not candidates:
        return None
    return min(candidates, key=lambda x: x[0])


def _ngfi_tighten_rays(origin, current, alpha=ALPHA):
    """All eight single-cell addition and deletion rays, every step."""
    baseline_sig = test_p(*origin) < alpha
    best_d, best_t, best_p, best_src = current
    origin_arr = np.asarray(origin, dtype=np.int64)

    for cell in range(4):
        for sign, label in ((+1, "single-cell addition"),
                            (-1, "single-cell deletion")):
            if sign > 0:
                top = best_d - 1
            else:
                top = min(int(origin_arr[cell]), best_d - 1)
            if top <= 0:
                continue

            for start in range(1, top + 1, FISHER_CHUNK):
                stop = min(top, start + FISHER_CHUNK - 1)
                k = np.arange(start, stop + 1, dtype=np.int64)

                tables = np.repeat(origin_arr[None, :], len(k), axis=0)
                tables[:, cell] += sign * k

                live = tables.sum(axis=1) > 0
                if not np.any(live):
                    continue
                tables = tables[live]
                kk = k[live]

                sig = ~fisher_accepts(tables, alpha)
                hits = np.flatnonzero(sig != baseline_sig)
                if len(hits):
                    j = int(hits[0])
                    best_d = int(kk[j])
                    best_t = tuple(int(t) for t in tables[j])
                    best_p = test_p(*best_t)
                    best_src = label
                    break

    return best_d, best_t, best_p, best_src


def _ngfi_tighten_pairs(origin, current, alpha=ALPHA):
    """
    r10: search every TWO-CELL edit mix (x edits on one cell, k-x on
    another, all four sign combinations, 24 usable pairs).

    For budgets up to PAIR_EXACT_K each k is scanned in ascending order
    with every split tested, so the pair minimum is exact. For larger
    budgets the first flipping k is located by doubling and bisection
    with a full split scan at every probed k (r5-equivalent). Either
    way every candidate tested is a real table, so any improvement is
    a rigorous upper bound; the certification sweep remains the only
    source of the word "exact".
    """
    baseline_sig = test_p(*origin) < alpha
    best_d, best_t, best_p, best_src = current
    origin_arr = np.asarray(origin, dtype=np.int64)

    moves = (
        [(cell, +1) for cell in range(4)]
        + [(cell, -1) for cell in range(4)]
    )

    def cap_of(move, budget):
        cell, s = move
        return budget if s > 0 else min(budget, int(origin_arr[cell]))

    def scan_k(mv1, mv2, k):
        """Test every split of k edits between mv1 and mv2.
        Returns (split, table) of the first flip, or None."""
        c1 = cap_of(mv1, k)
        c2 = cap_of(mv2, k)
        lo = max(0, k - c2)
        hi = min(k, c1)
        if lo > hi:
            return None
        xs = np.arange(lo, hi + 1, dtype=np.int64)
        tables = np.repeat(origin_arr[None, :], len(xs), axis=0)
        tables[:, mv1[0]] += mv1[1] * xs
        tables[:, mv2[0]] += mv2[1] * (k - xs)
        live = (tables >= 0).all(axis=1) & (tables.sum(axis=1) > 0)
        if not np.any(live):
            return None
        tables = tables[live]
        xs = xs[live]
        sig = ~fisher_accepts(tables, alpha)
        hits = np.flatnonzero(sig != baseline_sig)
        if not len(hits):
            return None
        j = int(hits[0])
        return int(xs[j]), tuple(int(t) for t in tables[j])

    for i in range(len(moves)):
        for j2 in range(i + 1, len(moves)):
            mv1, mv2 = moves[i], moves[j2]
            if mv1[0] == mv2[0]:
                continue        # same cell: already covered by rays

            top = best_d - 1
            if top < 2:
                continue
            kmax = min(top, cap_of(mv1, top) + cap_of(mv2, top))
            if kmax < 2:
                continue

            found_k = None
            found = None

            if kmax <= PAIR_EXACT_K:
                # Exact: ascending k, every split tested.
                for k in range(2, kmax + 1):
                    r = scan_k(mv1, mv2, k)
                    if r is not None:
                        found_k, found = k, r
                        break
            else:
                # Doubling, then bisection, then a walk-down.
                k = 2
                probe = None
                while k <= kmax:
                    probe = scan_k(mv1, mv2, k)
                    if probe is not None:
                        break
                    k *= 2
                if probe is None:
                    k = kmax
                    probe = scan_k(mv1, mv2, k)
                if probe is not None:
                    hi_b = min(k, kmax)
                    lo_b = max(2, hi_b // 2)
                    best_probe = (hi_b, probe)
                    while lo_b < hi_b:
                        mid = (lo_b + hi_b) // 2
                        r = scan_k(mv1, mv2, mid)
                        if r is not None:
                            hi_b = mid
                            best_probe = (mid, r)
                        else:
                            lo_b = mid + 1
                    k0, r0 = best_probe
                    while k0 > 2:
                        r = scan_k(mv1, mv2, k0 - 1)
                        if r is None:
                            break
                        k0 -= 1
                        r0 = r
                    found_k, found = k0, r0

            if found_k is not None and found_k < best_d:
                _, table = found
                best_d = int(found_k)
                best_t = table
                best_p = test_p(*table)
                best_src = "two-cell edit mix"

    return best_d, best_t, best_p, best_src


def _ngfi_mechanism(origin, witness):
    delta = (
        np.asarray(witness, dtype=np.int64)
        - np.asarray(origin, dtype=np.int64)
    )
    adds = int(np.maximum(delta, 0).sum())
    dels = int(np.maximum(-delta, 0).sum())
    if adds and dels:
        return "mixed"
    if adds:
        return "additions"
    if dels:
        return "deletions"
    return "none"


# ======================================================================
# Independent brute-force nGFI validator (tiny tables only)
# ======================================================================

def _positive_compositions(total, parts):
    if parts == 1:
        if total >= 1:
            yield (total,)
        return
    for first in range(1, total - (parts - 1) + 1):
        for rest in _positive_compositions(total - first, parts - 1):
            yield (first,) + rest


def _signed_l1_shell(origin, k):
    """Complete signed L1 shell at distance k."""
    origin_arr = np.asarray(origin, dtype=np.int64)
    blocks = []

    for size in range(1, min(4, k) + 1):
        comps = np.asarray(
            list(_positive_compositions(k, size)), dtype=np.int64
        )
        if not len(comps):
            continue
        for cells in combinations(range(4), size):
            for signs in product((-1, +1), repeat=size):
                delta = np.zeros((len(comps), 4), dtype=np.int64)
                for j, cell in enumerate(cells):
                    delta[:, cell] = signs[j] * comps[:, j]
                cand = origin_arr[None, :] + delta
                ok = np.all(cand >= 0, axis=1) & (cand.sum(axis=1) > 0)
                if np.any(ok):
                    blocks.append(cand[ok])

    if not blocks:
        return np.empty((0, 4), dtype=np.int64)
    return np.vstack(blocks)


def compute_ngfi_exhaustive(a, b, c, d, alpha=ALPHA, max_distance=None):
    """Complete brute-force signed L1 shell search. Small tables only."""
    origin = validate_table(a, b, c, d)
    baseline_sig = test_p(*origin) < alpha

    if max_distance is None:
        max_distance = 100

    for k in range(1, int(max_distance) + 1):
        tables = _signed_l1_shell(origin, k)
        if not len(tables):
            continue
        sig = ~fisher_accepts(tables, alpha)
        hits = np.flatnonzero(sig != baseline_sig)
        if len(hits):
            j = int(hits[0])
            t = tuple(int(x) for x in tables[j])
            return k, t, test_p(*t)

    return None, None, None


# ======================================================================
# Exact nGFI / nGFQ
# ======================================================================

def compute_ngfi_ngfq(a, b, c, d, alpha=ALPHA, gfi_result=None,
                      certify="ask"):
    """
    Neutral Global Fragility Index (nGFI = FD, fragility distance):
    fast rigorous upper bound first, exact certification second.

    nGFI is the global minimum cellwise L1 distance from the observed
    table to any non-negative integer 2x2 table on the opposite side of
    the Fisher threshold. N and both margins are unconstrained.

    The fast stage uses a GFI witness, margin deletions, diagonal additions,
    exact single-cell ray scans, and preliminary two-cell searches. With
    PAIR_EXACT_K=0, two-cell budgets use the faster heuristic and do not
    claim an exact pair minimum. The result is a verified upper bound. The certification sweep is
    predicted in advance and run, asked about, or skipped according to
    `certify` ("ask" / True / False). certified=True in the result
    means proven global minimum; certified=False means upper bound.
    """
    origin = validate_table(a, b, c, d)
    N0 = sum(origin)

    base = {
        "nGFI": None, "nGFQ": None,
        "baseline_p": None, "baseline_state": None, "target_state": None,
        "final_p": None, "witness_table": None,
        "original_N": int(N0), "final_N": None, "net_N_change": None,
        "mechanism": None, "incumbent": None, "incumbent_source": None,
        "exact": False, "certified": False,
        "lower_bound": None, "upper_bound": None,
        "candidates_examined": 0,
        "cross_checked": False,
        "test_used": "fisher_exact",
        "algorithm": (
            "exact unrestricted Fisher L1 margin-coordinate certification"
        ),
        "note": "",
    }

    if N0 <= 0:
        base["note"] = "Empty table."
        return base

    if not _fisher_engine_ok():
        base["note"] = (
            "NA: vectorized Fisher engine does not match "
            f"scipy.stats.fisher_exact ({_ENGINE_STATE['detail']}). "
            "Exactness cannot be guaranteed, so no value is reported."
        )
        return base

    baseline_p = test_p(*origin)
    baseline_sig = baseline_p < alpha

    base["baseline_p"] = baseline_p
    base["baseline_state"] = state_label(baseline_p, alpha)
    base["target_state"] = (
        "non-significant" if baseline_sig else "significant"
    )

    R0 = origin[0] + origin[1]
    C0 = origin[0] + origin[2]

    if gfi_result is None:
        gfi_result = compute_gfi_gfq(*origin, alpha=alpha, certify=certify)

    initial = _ngfi_initial_incumbent(origin, alpha, gfi_result)
    if initial is None:
        base["note"] = (
            "NA: no opposite-state table could be constructed within "
            f"NGFI_MAX_ADDITION = {NGFI_MAX_ADDITION:,}."
        )
        return base

    best_d, best_t, best_p, best_src = _ngfi_tighten_rays(
        origin, initial, alpha
    )
    best_d, best_t, best_p, best_src = _ngfi_tighten_pairs(
        origin, (best_d, best_t, best_p, best_src), alpha
    )
    incumbent_d = best_d

    base["incumbent"] = int(incumbent_d)
    base["incumbent_source"] = best_src

    # ---- r10: predict the certification cost, then auto / ask / skip ----
    run_sweep, decision_note = _decide_certification(
        "FD", best_d,
        "upper bound from single-cell rays and preliminary two-cell search",
        _predict_ngfi_candidates(best_d), certify,
    )
    if not run_sweep:
        verified_p = test_p(*best_t)
        if (verified_p < alpha) == baseline_sig:
            base["note"] = (
                "NA: FD fast witness failed scalar Fisher verification."
            )
            return base
        if table_l1_distance(origin, best_t) != best_d:
            base["note"] = (
                "NA: FD fast witness distance does not match FD."
            )
            return base
        base.update({
            "nGFI": int(best_d),
            "nGFQ": best_d / N0,
            "final_p": float(verified_p),
            "witness_table": tuple(int(x) for x in best_t),
            "final_N": int(sum(best_t)),
            "net_N_change": int(sum(best_t) - N0),
            "mechanism": _ngfi_mechanism(origin, best_t),
            "exact": False,
            "certified": False,
            "lower_bound": None,
            "upper_bound": int(best_d),
            "note": (
                f"FD <= {best_d}: UPPER BOUND from the fast search "
                f"({best_src}). " + decision_note
            ),
        })
        return base

    # ---- level-ascending exact certification (batched, parallel) ----
    (best_d, best_t, best_p, best_src,
     examined, aborted, level_done) = _certify_levels(
        origin, alpha, baseline_sig,
        (best_d, best_t, best_p, best_src),
        lambda D: _ngfi_level_arrays(D, N0, R0, C0),
        lambda bd: bd - 1,
        lambda k: k,
        NGFI_MAX_CANDIDATES,
        allow_pool=(2 * best_d ** 3 >= PARALLEL_MIN_TOTAL),
    )

    base["candidates_examined"] = int(examined)

    if aborted:
        base["lower_bound"] = int(level_done + 1)
        base["upper_bound"] = int(best_d)
        verified_p = test_p(*best_t)
        if ((verified_p < alpha) != baseline_sig
                and table_l1_distance(origin, best_t) == best_d):
            base.update({
                "nGFI": int(best_d),
                "nGFQ": best_d / N0,
                "final_p": float(verified_p),
                "witness_table": tuple(int(x) for x in best_t),
                "final_N": int(sum(best_t)),
                "net_N_change": int(sum(best_t) - N0),
                "mechanism": _ngfi_mechanism(origin, best_t),
                "exact": False,
                "certified": False,
            })
            base["note"] = (
                f"FD <= {best_d}: UPPER BOUND; certification stopped "
                f"at the candidate cap after {examined:,} margin "
                f"triples. Proven bracket: "
                f"{base['lower_bound']} <= FD <= {best_d}."
            )
            return base
        base["note"] = (
            f"NA: certification stopped at the candidate cap after "
            f"{examined:,} margin triples and the fast witness failed "
            "verification."
        )
        return base

    # ---- independent scalar verification of the reported answer ----
    verified_p = test_p(*best_t)
    if (verified_p < alpha) == baseline_sig:
        base["note"] = "NA: FD witness failed scalar Fisher verification."
        return base
    if table_l1_distance(origin, best_t) != best_d:
        base["note"] = "NA: FD witness distance does not match FD."
        return base

    GFI = gfi_result.get("GFI") if gfi_result else None
    if GFI is not None and best_d > 2 * GFI:
        base["note"] = (
            f"NA: internal inconsistency, FD={best_d} > 2*GFI={2 * GFI}."
        )
        return base

    # ---- tiny tables: independent brute-force cross-check ----
    cross = False
    if N0 <= NGFI_EXHAUSTIVE_N:
        brute_k, _, _ = compute_ngfi_exhaustive(
            *origin, alpha=alpha, max_distance=best_d
        )
        if brute_k != best_d:
            base["note"] = (
                "NA: margin certification disagreed with independent "
                f"signed-shell enumeration (margin={best_d}, "
                f"shell={brute_k})."
            )
            return base
        cross = True

    note = (
        f"FD = {best_d}; global unrestricted L1 minimum certified over "
        f"{examined:,} candidate margin triples."
    )
    if incumbent_d == best_d:
        note += f" Incumbent finder already optimal ({best_src})."
    else:
        note += (
            f" Incumbent was {incumbent_d}; certification improved it "
            f"to {best_d}."
        )
    if cross:
        note += " Cross-checked against complete signed L1 enumeration."

    base.update({
        "nGFI": int(best_d),
        "nGFQ": best_d / N0,
        "final_p": float(verified_p),
        "witness_table": tuple(int(x) for x in best_t),
        "final_N": int(sum(best_t)),
        "net_N_change": int(sum(best_t) - N0),
        "mechanism": _ngfi_mechanism(origin, best_t),
        "exact": True,
        "certified": True,
        "lower_bound": int(best_d),
        "upper_bound": int(best_d),
        "cross_checked": cross,
        "incumbent_source": best_src,
        "note": note,
    })
    return base


# ======================================================================
# Conventional toggle paths (FI / MFI / SFI)
# ======================================================================

def choose_arm(a, b, c, d):
    """Fewer events; tie broken by fewer total; then Arm A."""
    if a < c:
        return "A"
    if c < a:
        return "B"
    if a + b < c + d:
        return "A"
    if c + d < a + b:
        return "B"
    return "A"


def steps_to_cross(a, b, c, d, arm, direction, alpha=ALPHA):
    """
    Exact first Fisher crossing along a within-arm toggle path.

    Every integer step is evaluated, vectorized in chunks. No
    monotonicity assumption is made anywhere.
    """
    baseline_p = test_p(a, b, c, d)
    baseline_sig = baseline_p < alpha

    if arm == "A":
        top = b if direction == "up" else a
        cells = (0, 1)
    else:
        top = d if direction == "up" else c
        cells = (2, 3)

    sign = +1 if direction == "up" else -1
    if top <= 0:
        return None, baseline_p

    origin_arr = np.array([a, b, c, d], dtype=np.int64)
    last_p = baseline_p

    for start in range(1, top + 1, FISHER_CHUNK):
        stop = min(top, start + FISHER_CHUNK - 1)
        k = np.arange(start, stop + 1, dtype=np.int64)

        tables = np.repeat(origin_arr[None, :], len(k), axis=0)
        tables[:, cells[0]] += sign * k
        tables[:, cells[1]] -= sign * k

        sig = ~fisher_accepts(tables, alpha)
        hits = np.flatnonzero(sig != baseline_sig)
        if len(hits):
            j = int(hits[0])
            t = tables[j]
            return int(k[j]), test_p(
                int(t[0]), int(t[1]), int(t[2]), int(t[3])
            )

        t = tables[-1]
        last_p = test_p(int(t[0]), int(t[1]), int(t[2]), int(t[3]))

    return None, last_p


def all_toggle_paths(a, b, c, d, alpha=ALPHA):
    """Compute each of the four toggle paths once and reuse."""
    return {
        (arm, direction): steps_to_cross(a, b, c, d, arm, direction, alpha)
        for arm in ("A", "B")
        for direction in ("up", "down")
    }


_DIR_LABEL = {
    "up": "non-events -> events",
    "down": "events -> non-events",
}


def compute_fi_fq_mfq(a, b, c, d, alpha=ALPHA, paths=None):
    """Walsh-compliant FI / FQ / MFQ."""
    a, b, c, d = validate_table(a, b, c, d)
    N = n_total(a, b, c, d)

    baseline_p = test_p(a, b, c, d)
    baseline_state = state_label(baseline_p, alpha)
    arm = choose_arm(a, b, c, d)

    if paths is None:
        paths = all_toggle_paths(a, b, c, d, alpha)

    candidates = [
        (_DIR_LABEL[dr], paths[(arm, dr)][0], paths[(arm, dr)][1])
        for dr in ("up", "down")
        if paths[(arm, dr)][0] is not None
    ]

    n_mod = (a + b) if arm == "A" else (c + d)

    if not candidates:
        return {
            "FI": None, "FQ": None, "MFQ": None,
            "baseline_p": baseline_p, "baseline_state": baseline_state,
            "arm": arm, "direction": None, "n_mod": n_mod,
            "final_p": baseline_p, "test_used": "fisher_exact",
            "note": "FI not attainable under Walsh constraints.",
        }

    direction, steps, final_p = min(candidates, key=lambda x: x[1])
    FI = int(steps)

    return {
        "FI": FI,
        "FQ": (FI / N) if N > 0 else None,
        "MFQ": (FI / n_mod) if n_mod > 0 else None,
        "baseline_p": baseline_p,
        "baseline_state": baseline_state,
        "target_state": (
            "non-significant" if baseline_state == "significant"
            else "significant"
        ),
        "arm": arm, "direction": direction, "n_mod": n_mod,
        "final_p": float(final_p),
        "test_used": "fisher_exact",
    }


def compute_mfi(a, b, c, d, alpha=ALPHA, paths=None):
    """Minimum toggle distance over either arm and either direction."""
    a, b, c, d = validate_table(a, b, c, d)
    baseline_p = test_p(a, b, c, d)
    baseline_state = state_label(baseline_p, alpha)

    if paths is None:
        paths = all_toggle_paths(a, b, c, d, alpha)

    candidates = [
        (arm, _DIR_LABEL[dr], paths[(arm, dr)][0], paths[(arm, dr)][1])
        for arm in ("A", "B")
        for dr in ("up", "down")
        if paths[(arm, dr)][0] is not None
    ]

    if not candidates:
        return {
            "MFI": None, "baseline_p": baseline_p,
            "baseline_state": baseline_state,
            "arm": None, "direction": None, "final_p": baseline_p,
            "test_used": "fisher_exact",
            "note": "MFI not attainable.",
        }

    arm, direction, steps, final_p = min(candidates, key=lambda x: x[2])

    return {
        "MFI": int(steps),
        "baseline_p": baseline_p,
        "baseline_state": baseline_state,
        "target_state": (
            "non-significant" if baseline_state == "significant"
            else "significant"
        ),
        "arm": arm, "direction": direction,
        "final_p": float(final_p),
        "test_used": "fisher_exact",
    }


def compute_sfi(a, b, c, d, alpha=ALPHA, paths=None):
    """SFI: BFU-sized toggles in the larger arm. BFU = 1/max(n_A,n_B)."""
    a, b, c, d = validate_table(a, b, c, d)
    n_A, n_B = a + b, c + d

    if n_A > n_B:
        larger_arm, n_large = "A", n_A
    elif n_B > n_A:
        larger_arm, n_large = "B", n_B
    else:
        larger_arm, n_large = ("A", n_A) if a < c else ("B", n_B)

    baseline_p = test_p(a, b, c, d)
    baseline_state = state_label(baseline_p, alpha)
    BFU = (1.0 / n_large) if n_large > 0 else None

    if BFU is None:
        return {
            "SFI": None, "BFU": None, "baseline_p": baseline_p,
            "baseline_state": baseline_state, "larger_arm": None,
            "n_large": 0, "final_p": baseline_p,
            "test_used": "fisher_exact", "note": "Empty arm.",
        }

    if paths is None:
        paths = all_toggle_paths(a, b, c, d, alpha)

    candidates = [
        (_DIR_LABEL[dr], paths[(larger_arm, dr)][0],
         paths[(larger_arm, dr)][1])
        for dr in ("up", "down")
        if paths[(larger_arm, dr)][0] is not None
    ]

    if not candidates:
        return {
            "SFI": None, "BFU": BFU, "baseline_p": baseline_p,
            "baseline_state": baseline_state,
            "larger_arm": larger_arm, "n_large": n_large,
            "final_p": baseline_p, "test_used": "fisher_exact",
            "note": "SFI not attainable.",
        }

    direction, steps, final_p = min(candidates, key=lambda x: x[1])

    return {
        "SFI": int(steps), "BFU": BFU,
        "baseline_p": baseline_p, "baseline_state": baseline_state,
        "target_state": (
            "non-significant" if baseline_state == "significant"
            else "significant"
        ),
        "larger_arm": larger_arm, "n_large": n_large,
        "direction": direction, "final_p": float(final_p),
        "test_used": "fisher_exact",
    }


# ======================================================================
# Walter UFI, solved from the exact Fisher acceptance interval
# ======================================================================

def _acceptance_interval(R, C, N, alpha=ALPHA):
    """
    Exact [L,H] = { x : Fisher p >= alpha } for fixed margins.
    Contiguous by unimodality; found by bisection on Fisher itself.
    Returns (L, H, support_lo, support_hi).
    """
    r = np.array([R], dtype=np.int64)
    c = np.array([C], dtype=np.int64)
    n = np.array([N], dtype=np.int64)

    slo, shi = support_bounds(r, c, n)
    mode = fisher_mode(r, c, n)

    lo, hi = int(slo[0]), int(mode[0])
    while lo < hi:
        mid = lo + (hi - lo) // 2
        if fisher_accepts(tables_from_xrc([mid], r, c, n)[0], alpha):
            hi = mid
        else:
            lo = mid + 1
    L = lo

    lo, hi = int(mode[0]), int(shi[0])
    while lo < hi:
        mid = lo + (hi - lo + 1) // 2
        if fisher_accepts(tables_from_xrc([mid], r, c, n)[0], alpha):
            lo = mid
        else:
            hi = mid - 1
    H = lo

    return L, H, int(slo[0]), int(shi[0])


def compute_ufi_walter(a, b, c, d, alpha=ALPHA):
    """
    Walter UFI: minimum integer |x| along the fixed-margin path
    (a+x, b-x, c-x, d+x) that reverses Fisher significance.
    Returns (ufi, x_signed, p_at_flip) or (None, None, None).
    """
    a, b, c, d = validate_table(a, b, c, d)
    N = a + b + c + d
    if N <= 0:
        return None, None, None

    R, C = a + b, a + c
    baseline_sig = test_p(a, b, c, d) < alpha

    L, H, slo, shi = _acceptance_interval(R, C, N, alpha)

    candidates = []
    if baseline_sig:
        if a < L:
            candidates.append((abs(L - a), L - a))
        elif a > H:
            candidates.append((abs(H - a), H - a))
    else:
        if L > slo:
            candidates.append((abs(L - 1 - a), L - 1 - a))
        if H < shi:
            candidates.append((abs(H + 1 - a), H + 1 - a))

    if not candidates:
        return None, None, None

    # Walter's linear-walk convention checked +x before -x at equal |x|.
    ufi, x = min(candidates, key=lambda t: (t[0], -t[1]))

    p_flip = test_p(a + x, b - x, c - x, d + x)
    if (p_flip < alpha) == baseline_sig:
        return None, None, None

    return int(ufi), int(x), float(p_flip)


# ======================================================================
# Pearson-defined metrics (RQ, RRI, PFI-Pearson, SFM)
# ======================================================================

def chi2_p_pearson(a, b, c, d):
    """Pearson chi-square without Yates. Returns (statistic, p)."""
    table = np.array([[a, b], [c, d]], dtype=float)

    if np.any(table < 0) or table.sum() == 0:
        return np.nan, np.nan
    if np.any(table.sum(axis=0) == 0) or np.any(table.sum(axis=1) == 0):
        return np.nan, np.nan

    stat, p, _, _ = chi2_contingency(table, correction=False)
    return float(stat), float(p)


def p_value_pearson(a, b, c, d):
    _, p = chi2_p_pearson(a, b, c, d)
    return p


def p_along_x(a, b, c, d, x):
    """Continuous fixed-margin Pearson path: (a+x, b-x, c-x, d+x)."""
    aa, bb, cc, dd = a + x, b - x, c - x, d + x
    if min(aa, bb, cc, dd) < 0:
        return np.nan
    return p_value_pearson(aa, bb, cc, dd)


def compute_pfi_along_path(a, b, c, d, p_func, alpha=ALPHA,
                           tol=1e-12, max_iter=300):
    """
    Continuous PFI along the fixed-margin Pearson path.
    Returns (|x*|, PFI, p_at_flip, boundary_limited).
    """
    n = float(a + b + c + d)
    if n <= 0 or min(a, b, c, d) < 0:
        return None, None, None, False

    x_min = -min(a, d)
    x_max = min(b, c)

    p0 = p_func(a, b, c, d, 0)
    if not np.isfinite(p0):
        return None, None, None, False

    baseline_sig = p0 < alpha

    def f(x):
        return p_func(a, b, c, d, x) - alpha

    expected_a = (a + b) * (a + c) / n
    x_neutral = expected_a - a

    if baseline_sig:
        lo, hi = (0.0, x_neutral) if x_neutral >= 0 else (x_neutral, 0.0)
    else:
        lo, hi = (x_min, 0.0) if x_neutral >= 0 else (0.0, x_max)

    if lo > hi:
        lo, hi = hi, lo
    lo = max(lo, x_min)
    hi = min(hi, x_max)

    boundary_x = x_min if baseline_sig else x_max

    def boundary_result():
        pfi = max(0.0, min(1.0, 4.0 * abs(boundary_x) / n))
        return abs(boundary_x), pfi, None, True

    if abs(lo - hi) < tol:
        return boundary_result()

    flo, fhi = f(lo), f(hi)
    if (not np.isfinite(flo)) or (not np.isfinite(fhi)) or flo * fhi > 0:
        return boundary_result()

    x_star = None
    for _ in range(max_iter):
        mid = 0.5 * (lo + hi)
        fmid = f(mid)

        if not np.isfinite(fmid):
            mid = np.nextafter(mid, hi)
            fmid = f(mid)
            if not np.isfinite(fmid):
                break

        if abs(hi - lo) < tol or fmid == 0:
            x_star = mid
            break

        if flo * fmid <= 0:
            hi, fhi = mid, fmid
        else:
            lo, flo = mid, fmid

    if x_star is None:
        x_star = 0.5 * (lo + hi)

    p_flip = p_func(a, b, c, d, x_star)
    pfi = max(0.0, min(1.0, 4.0 * abs(x_star) / n))
    boundary_limited = abs(abs(x_star) - abs(boundary_x)) < tol

    return abs(x_star), pfi, p_flip, boundary_limited


def compute_ufi_pfi(a, b, c, d, alpha=ALPHA, tol=1e-12, max_iter=300):
    """RRI, RQ, PFI-Pearson, SFM and Walter UFI."""
    a, b, c, d = validate_table(a, b, c, d)
    N = n_total(a, b, c, d)
    if N <= 0:
        raise ValueError("Invalid counts.")

    n_A, n_B = a + b, c + d
    events, non_events = a + c, b + d

    E_a = n_A * events / N
    E_b = n_A * non_events / N
    E_c = n_B * events / N
    E_d = n_B * non_events / N

    rri = 0.25 * (
        abs(a - E_a) + abs(b - E_b) + abs(c - E_c) + abs(d - E_d)
    )
    rq = rri / (N / 4.0)

    _, pfi_pearson, _, _ = compute_pfi_along_path(
        float(a), float(b), float(c), float(d),
        p_along_x, alpha, tol, max_iter,
    )

    chi2_0, p0 = chi2_p_pearson(a, b, c, d)
    chi2_critical = chi2.ppf(1.0 - alpha, df=1)

    sfm = None
    if np.isfinite(chi2_0) and chi2_0 > 0:
        sfm = (
            chi2_0 / chi2_critical if p0 < alpha
            else chi2_critical / chi2_0
        )
        if sfm <= 1:
            sfm = None

    ufi, x_walter, p_walter = compute_ufi_walter(a, b, c, d, alpha)

    return {
        "RQ": rq,
        "RRI": rri,
        "PFI_Pearson": pfi_pearson,
        "SFM": sfm,
        "UFI_Walter": ufi,
        "x_Walter": x_walter,
        "p_at_flip_Walter": p_walter,
        "p_original_chi2": p0,
    }


# ======================================================================
# r11: Analysis Variability (AV)
# ======================================================================

AV_TEST_LABELS = ("fisher_exact", "pearson_chi2", "pearson_yates")


def pearson_p(a, b, c, d, yates=False):
    """
    Two-sided Pearson chi-square p-value for a 2x2 table via
    scipy.stats.chi2_contingency (correction=yates applies the
    SciPy-capped Yates continuity correction).

    A degenerate table (zero grand total, zero row margin, or zero
    column margin) returns p = 1.0, matching the Fisher convention
    used throughout this module.
    """
    a, b, c, d = validate_table(a, b, c, d)
    N = a + b + c + d
    if N == 0 or a + b == 0 or c + d == 0 or a + c == 0 or b + d == 0:
        return 1.0
    _, p, _, _ = chi2_contingency(
        np.array([[a, b], [c, d]], dtype=float), correction=yates
    )
    return float(p)


def compute_av(a, b, c, d, alpha=ALPHA):
    """
    Analysis Variability: does the BASELINE significance classification
    depend on the test used?

    Three tests on the observed table at `alpha`:
        fisher_exact   two-sided Fisher's exact (scipy.stats.fisher_exact)
        pearson_chi2   Pearson chi-square, uncorrected
        pearson_yates  Pearson chi-square with Yates continuity correction

    AV_discordant = 0 when all three land on the same side of alpha,
    1 otherwise. AV_which = "concordant", or the name of the single
    dissenting test (three tests: discordance is always 2 against 1).

    ELIGIBILITY: a table with any ZERO MARGINAL TOTAL (row, column,
    or grand total) is ineligible - every test returns p = 1 there by
    convention, so sensitivity to test choice cannot be assessed.
    AV_discordant and AV_which are returned as None (printed NA).
    """
    a, b, c, d = validate_table(a, b, c, d)

    if min(a + b, c + d, a + c, b + d) == 0:
        return {
            "AV_discordant": None,
            "AV_which": None,
            "p_fisher": None,
            "p_pearson": None,
            "p_pearson_yates": None,
            "sig_fisher": None,
            "sig_pearson": None,
            "sig_pearson_yates": None,
            "alpha": float(alpha),
            "note": (
                "NA: ineligible input for AV. A marginal total is "
                "zero, where every test returns p = 1 by convention, "
                "so sensitivity to test choice cannot be assessed."
            ),
        }

    p_fisher = test_p(a, b, c, d)
    p_pearson = pearson_p(a, b, c, d, yates=False)
    p_yates = pearson_p(a, b, c, d, yates=True)

    sigs = [p_fisher < alpha, p_pearson < alpha, p_yates < alpha]
    n_sig = sum(sigs)

    if n_sig in (0, 3):
        discordant, which = 0, "concordant"
    else:
        # The minority side has exactly one member: the dissenter.
        discordant = 1
        which = AV_TEST_LABELS[sigs.index(n_sig == 1)]

    return {
        "AV_discordant": int(discordant),
        "AV_which": which,
        "p_fisher": float(p_fisher),
        "p_pearson": float(p_pearson),
        "p_pearson_yates": float(p_yates),
        "sig_fisher": bool(sigs[0]),
        "sig_pearson": bool(sigs[1]),
        "sig_pearson_yates": bool(sigs[2]),
        "alpha": float(alpha),
        "note": "",
    }


# ======================================================================
# r11: Relative Risk (RR) with Katz CI, and the AD_any composite
# ======================================================================

def compute_rr(a, b, c, d, alpha=ALPHA):
    """
    Relative risk (Arm A relative to Arm B) with the Katz log-RR Wald
    interval at level 1 - alpha, plus test-vs-CI agreement flags.

        nA = a+b, nB = c+d
        RR = (a/nA) / (c/nB)
        SE(log RR) = sqrt(1/a - 1/nA + 1/c - 1/nB)      [Katz]
        CI = exp(log RR -+ z_{1-alpha/2} * SE)

    RR_signif = 1 iff the CI excludes 1 (either direction).
    RR_CI_discordant = 1 iff RR_signif disagrees with the TWO-SIDED
    FISHER EXACT classification at alpha (the module's authoritative
    test). The Katz interval inverts a Wald z-test on the log-RR
    scale, not Fisher's test, so near-threshold disagreement is
    expected; the flag exposes it.

    Zero-cell policy (nothing is silently corrected):
        nA = 0 or nB = 0   -> everything NA (no fitted risks)
        a = 0 and c = 0    -> RR NA (0/0), CI NA
        a = 0, c > 0       -> RR = 0.0 exactly, CI NA (Katz SE
                              undefined)
        c = 0, a > 0       -> RR infinite: NA with a note, CI NA
    RR_signif and RR_CI_discordant are NA whenever the CI is NA.
    """
    a, b, c, d = validate_table(a, b, c, d)
    nA, nB = a + b, c + d

    base = {
        "RR": None, "RR_CI_low": None, "RR_CI_high": None,
        "RR_signif": None, "RR_CI_discordant": None,
        "ci_level": float(1.0 - alpha),
        "ci_method": "Katz log-RR Wald",
        "fisher_p": None, "fisher_sig": None,
        "nA": int(nA), "nB": int(nB), "pA": None, "pB": None,
        "note": "",
    }

    if nA == 0 or nB == 0:
        base["note"] = "NA: RR requires both arm sizes to be positive."
        return base

    pA, pB = a / nA, c / nB
    base["pA"] = float(pA)
    base["pB"] = float(pB)

    fisher_p = test_p(a, b, c, d)
    fisher_sig = fisher_p < alpha
    base["fisher_p"] = float(fisher_p)
    base["fisher_sig"] = bool(fisher_sig)

    if a == 0 and c == 0:
        base["note"] = (
            "NA: RR undefined (no events in either arm, 0/0); "
            "Katz CI undefined. No continuity correction applied."
        )
        return base
    if c == 0:
        base["note"] = (
            "NA: RR infinite (zero events in Arm B); Katz CI "
            "undefined. No continuity correction applied."
        )
        return base
    if a == 0:
        base["RR"] = 0.0
        base["note"] = (
            "RR = 0 (zero events in Arm A); Katz CI undefined, so "
            "RR_signif and RR_CI_discordant are NA. No continuity "
            "correction applied."
        )
        return base

    rr = pA / pB
    se = float(np.sqrt(1.0 / a - 1.0 / nA + 1.0 / c - 1.0 / nB))
    z = float(norm.ppf(1.0 - alpha / 2.0))
    log_rr = float(np.log(rr))
    lo = float(np.exp(log_rr - z * se))
    hi = float(np.exp(log_rr + z * se))

    signif = 1 if (lo > 1.0 or hi < 1.0) else 0
    discordant = int(bool(signif) != fisher_sig)

    base.update({
        "RR": float(rr),
        "RR_CI_low": lo,
        "RR_CI_high": hi,
        "RR_signif": int(signif),
        "RR_CI_discordant": int(discordant),
    })
    return base


def compute_ad_any(av_result, rr_result):
    """
    Composite analysis-discordance flag across both tiers:

        AD_any = 1  iff  AV_discordant = 1  OR  RR_CI_discordant = 1
        AD_any = 0  when every available tier is 0
        AD_any = None (NA) only when BOTH tiers are NA; a single
                 available tier decides alone.
    """
    av = av_result.get("AV_discordant") if av_result else None
    ci = rr_result.get("RR_CI_discordant") if rr_result else None
    if av is None and ci is None:
        return None
    return int(bool(av) or bool(ci))


# ======================================================================
# r11: Resampling Variability (RV)
# ======================================================================

def _binom_support(n, p, eps):
    """
    Central support of Binomial(n, p): the k values whose excluded tail
    mass is provably at most eps (at most eps/2 per side), their pmf,
    and the EXACT excluded mass (cdf below + sf above).

    A degenerate proportion (p = 0 or p = 1) has a single-point
    support with zero excluded mass.
    """
    if p <= 0.0:
        return np.array([0], dtype=np.int64), np.array([1.0]), 0.0
    if p >= 1.0:
        return np.array([n], dtype=np.int64), np.array([1.0]), 0.0

    lo = int(binom.ppf(eps / 2.0, n, p))
    hi = int(binom.isf(eps / 2.0, n, p))
    lo = max(0, min(lo, n))
    hi = max(0, min(hi, n))
    # ppf/isf already satisfy the tail bounds; widen defensively in
    # case of quantile rounding at extreme eps.
    while lo > 0 and float(binom.cdf(lo - 1, n, p)) > eps / 2.0:
        lo = max(0, lo - 10)
    while hi < n and float(binom.sf(hi, n, p)) > eps / 2.0:
        hi = min(n, hi + 10)

    k = np.arange(lo, hi + 1, dtype=np.int64)
    pmf = binom.pmf(k, n, p)
    excluded = 0.0
    if lo > 0:
        excluded += float(binom.cdf(lo - 1, n, p))
    if hi < n:
        excluded += float(binom.sf(hi, n, p))
    return k, pmf, excluded


def _rv_batch_worker(payload):
    """Independent RV batch; top-level function for Windows spawn."""
    start, stop, kA, wA, kB, wB, nA, nB, baseline_sig, alpha = payload
    idx = np.arange(start, stop, dtype=np.int64)
    ia, ib = idx // len(kB), idx % len(kB)
    XA, XB = kA[ia], kB[ib]
    tables = np.column_stack([XA, nA - XA, XB, nB - XB])
    weights = wA[ia] * wB[ib]
    sig = ~fisher_accepts(tables, alpha)
    return (float(weights[sig != baseline_sig].sum()),
            float(weights[sig].sum()))


def _rv_exact_mass(kA, wA, kB, wB, nA, nB, baseline_sig, alpha):
    """Sum the identical truncated grid, optionally over multiple CPUs.

    Results are reduced in batch order, independent of worker completion
    order. Serial and parallel paths use exactly the same batch partition.
    If a worker fails, discard partial results and recompute serially so
    that no replicate is lost or counted twice.
    """
    pairs = len(kA) * len(kB)
    chunk = max(1, min(int(RV_BATCH_PAIRS), int(FISHER_CHUNK)))

    def payload(start):
        return (start, min(pairs, start + chunk), kA, wA, kB, wB,
                nA, nB, baseline_sig, alpha)

    pool = _get_pool() if pairs >= PARALLEL_MIN_TOTAL else None
    if pool is not None:
        futures = []
        try:
            flip_mass = sig_mass = 0.0
            window = max(1, 2 * CERT_WORKERS) * chunk
            for group_start in range(0, pairs, window):
                futures = []
                for start in range(group_start, min(pairs, group_start + window), chunk):
                    futures.append(pool.submit(_rv_batch_worker, payload(start)))
                for future in futures:
                    flip, sig = future.result()
                    flip_mass += flip
                    sig_mass += sig
            return flip_mass, sig_mass
        except Exception as exc:
            for future in futures:
                future.cancel()
            _disable_pool(exc)

    flip_mass = sig_mass = 0.0
    for start in range(0, pairs, chunk):
        flip, sig = _rv_batch_worker(payload(start))
        flip_mass += flip
        sig_mass += sig
    return flip_mass, sig_mass


def compute_rv(a, b, c, d, alpha=ALPHA):
    """
    Resampling Variability (RV) under the fitted-binomial,
    same-arm-size replication model:

        nA = a+b, nB = c+d, pA = a/nA, pB = c/nB
        XA ~ Binomial(nA, pA) independently of XB ~ Binomial(nB, pB)
        replicate table = [[XA, nA-XA], [XB, nB-XB]]

    RV = probability that the replicate's two-sided Fisher
    classification at `alpha` differs from the baseline classification.

    Computed by exact enumeration of the truncated joint support: each
    arm keeps its central binomial mass with excluded tail at most
    RV_TAIL_EPS, so the reported RV is exact to within
    truncation_error_bound (the exactly computed excluded mass of the
    two arms; union bound). Seeded Monte Carlo is used ONLY when the
    grid would exceed RV_MAX_PAIRS, and the result says so.

    RV is a plug-in metric: instability under the FITTED model, with
    pA and pB treated as true. It can exceed 0.5. For a significant
    baseline RV = 1 - replication probability; for a non-significant
    baseline it is the probability that a replication would claim
    significance (in either direction).
    """
    origin = validate_table(a, b, c, d)
    a, b, c, d = origin
    nA, nB = a + b, c + d

    base = {
        "RV": None,
        "baseline_p": None, "baseline_state": None,
        "method": None,
        "pairs_examined": 0,
        "truncation_error_bound": None,
        "p_replicate_significant": None,
        "nA": int(nA), "nB": int(nB), "pA": None, "pB": None,
        "mc_reps": None, "mc_se": None, "mc_seed": None,
        "test_used": "fisher_exact",
        "algorithm": (
            "exact enumeration of the fitted-binomial replication model"
        ),
        "note": "",
    }

    if nA == 0 or nB == 0:
        base["note"] = (
            "NA: the fitted-binomial replication model requires both "
            "arm sizes to be positive."
        )
        return base

    if not _fisher_engine_ok():
        base["note"] = (
            "NA: vectorized Fisher engine does not match "
            f"scipy.stats.fisher_exact ({_ENGINE_STATE['detail']}). "
            "Exactness cannot be guaranteed, so no value is reported."
        )
        return base

    pA, pB = a / nA, c / nB
    base["pA"] = float(pA)
    base["pB"] = float(pB)

    baseline_p = test_p(a, b, c, d)
    baseline_sig = baseline_p < alpha
    base["baseline_p"] = float(baseline_p)
    base["baseline_state"] = state_label(baseline_p, alpha)

    notes = []
    if pA in (0.0, 1.0) or pB in (0.0, 1.0):
        notes.append(
            "A fitted arm proportion is 0 or 1, so that arm is "
            "deterministic in every replicate; RV may understate "
            "real-world variability."
        )

    kA, wA, exA = _binom_support(nA, pA, RV_TAIL_EPS)
    kB, wB, exB = _binom_support(nB, pB, RV_TAIL_EPS)
    pairs = len(kA) * len(kB)

    if pairs <= RV_MAX_PAIRS:
        flip_mass, sig_mass = _rv_exact_mass(
            kA, wA, kB, wB, nA, nB, baseline_sig, alpha
        )

        err = exA + exB
        base.update({
            "RV": float(flip_mass),
            "method": "exact enumeration",
            "pairs_examined": int(pairs),
            "truncation_error_bound": float(err),
            "p_replicate_significant": float(sig_mass),
        })
        notes.insert(0, (
            f"RV = {flip_mass:.7f}, exact to within {err:.2e} "
            f"(binomial tail truncation, union bound) over {pairs:,} "
            "enumerated replicate tables."
        ))
    else:
        rng = np.random.default_rng(RV_MC_SEED)
        XA = rng.binomial(nA, pA, RV_MC_REPS).astype(np.int64)
        XB = rng.binomial(nB, pB, RV_MC_REPS).astype(np.int64)
        tables = np.column_stack([XA, nA - XA, XB, nB - XB])
        sig = ~fisher_accepts(tables, alpha)
        flips = int(np.sum(sig != baseline_sig))
        rv = flips / RV_MC_REPS
        se = float(np.sqrt(max(rv * (1.0 - rv), 0.0) / RV_MC_REPS))
        base.update({
            "RV": float(rv),
            "method": "monte_carlo",
            "pairs_examined": int(RV_MC_REPS),
            "p_replicate_significant": float(np.mean(sig)),
            "mc_reps": int(RV_MC_REPS),
            "mc_se": se,
            "mc_seed": int(RV_MC_SEED),
        })
        notes.insert(0, (
            f"RV = {rv:.5f} by seeded Monte Carlo ({RV_MC_REPS:,} "
            f"replicates, SE ~ {se:.5f}, seed {RV_MC_SEED}): the exact "
            f"grid would need {pairs:,} pairs "
            f"(> RV_MAX_PAIRS = {RV_MAX_PAIRS:,})."
        ))

    base["note"] = " ".join(notes)
    return base


def compute_rv_exhaustive(a, b, c, d, alpha=ALPHA):
    """
    Independent RV validator: full-support enumeration with the scalar
    SciPy Fisher test only. Small arms only. Returns RV or None.
    """
    a, b, c, d = validate_table(a, b, c, d)
    nA, nB = a + b, c + d
    if nA == 0 or nB == 0:
        return None
    pA, pB = a / nA, c / nB
    baseline_sig = test_p(a, b, c, d) < alpha
    wA = binom.pmf(np.arange(nA + 1), nA, pA)
    wB = binom.pmf(np.arange(nB + 1), nB, pB)
    rv = 0.0
    for xa in range(nA + 1):
        for xb in range(nB + 1):
            p = test_p(xa, nA - xa, xb, nB - xb)
            if (p < alpha) != baseline_sig:
                rv += float(wA[xa] * wB[xb])
    return rv


# ======================================================================
# Brute-force GFI validator (tiny tables only)
# ======================================================================

def compute_gfi_exhaustive(a, b, c, d, alpha=ALPHA):
    """
    Complete enumeration of every fixed-N table. Small tables only.
    Returns (transfer_distance, table) or (None, None).
    """
    origin = validate_table(a, b, c, d)
    N = sum(origin)
    baseline_sig = test_p(*origin) < alpha

    rows = []
    for r in range(N + 1):
        for c_ in range(N + 1):
            lo = max(0, r + c_ - N)
            hi = min(r, c_)
            for x in range(lo, hi + 1):
                rows.append((x, r - x, c_ - x, N - r - c_ + x))

    tables = np.asarray(rows, dtype=np.int64)
    sig = ~fisher_accepts(tables, alpha)
    flip = np.flatnonzero(sig != baseline_sig)
    if not len(flip):
        return None, None

    dist = np.abs(
        tables[flip] - np.asarray(origin, dtype=np.int64)[None, :]
    ).sum(axis=1) // 2

    j = int(np.argmin(dist))
    return int(dist[j]), tuple(int(v) for v in tables[flip][j])


# ======================================================================
# Regression tests
# ======================================================================

def run_regression_tests(verbose=True):
    """Internal consistency and exactness tests. Raises on failure."""

    # ---- 1. vectorized Fisher engine versus SciPy ----
    if not _fisher_engine_ok():
        raise RuntimeError(
            f"Fisher engine regression failed: {_ENGINE_STATE['detail']}"
        )
    if verbose:
        print("  [1/9] Fisher engine:", _ENGINE_STATE["detail"])

    rng = np.random.default_rng(20260822)

    # ---- 2. closed-form minimum distance identity ----
    for _ in range(3000):
        w = int(rng.integers(-25, 26))
        u = int(rng.integers(-25, 26))
        v = int(rng.integers(-25, 26))

        pts = sorted([0, u, v, u + v - w])
        direct = (pts[2] + pts[3]) - (pts[0] + pts[1])
        closed = int(l1_min_distance(w, u, v)[0])

        if direct != closed:
            raise RuntimeError(
                f"l1_min_distance identity failed at ({w},{u},{v})."
            )
    if verbose:
        print("  [2/9] Minimum-distance identity: 3000 cases matched.")

    # ---- 3. sublevel interval versus brute force ----
    for _ in range(2000):
        w = int(rng.integers(-12, 13))
        u = int(rng.integers(-12, 13))
        v = int(rng.integers(-12, 13))
        K = int(rng.integers(0, 60))

        zlo, zhi, ok = sublevel_interval(w, u, v, K)
        zlo, zhi, ok = int(zlo[0]), int(zhi[0]), bool(ok[0])

        brute = [
            z for z in range(-80, 81)
            if int(l1_distance_wuvz(w, u, v, z)[0]) <= K
        ]

        if not brute:
            if ok and zlo <= zhi:
                raise RuntimeError(
                    "sublevel_interval non-empty but brute empty at "
                    f"({w},{u},{v},K={K})."
                )
            continue

        if not ok or zlo != min(brute) or zhi != max(brute):
            raise RuntimeError(
                f"sublevel_interval mismatch at ({w},{u},{v},K={K}): "
                f"got [{zlo},{zhi}], brute [{min(brute)},{max(brute)}]."
            )
    if verbose:
        print("  [3/9] Sublevel interval: 2000 cases matched brute force.")

    # ---- 4. GFI versus complete fixed-N enumeration ----
    gfi_cases = [
        (6, 2, 1, 4), (8, 2, 1, 5), (0, 1, 1, 3), (5, 1, 1, 5),
        (2, 8, 8, 2), (9, 3, 4, 8), (1, 9, 6, 4), (7, 7, 7, 7),
        (0, 0, 3, 4), (3, 0, 0, 4),
    ]
    for case in gfi_cases:
        got = compute_gfi_gfq(*case)
        ref, _ = compute_gfi_exhaustive(*case)
        if got["GFI"] != ref:
            raise RuntimeError(
                f"GFI mismatch at {case}: certified={got['GFI']}, "
                f"enumeration={ref}."
            )
    if verbose:
        print(f"  [4/9] GFI: {len(gfi_cases)} tables matched enumeration.")

    # ---- 5. nGFI versus complete signed L1 enumeration ----
    ngfi_cases = [
        (0, 1, 1, 3), (6, 2, 1, 4), (8, 2, 1, 5), (2, 8, 8, 2),
        (5, 1, 1, 5), (1, 9, 6, 4), (3, 0, 0, 4), (7, 7, 7, 7),
        (29, 13, 24, 11),
    ]
    for case in ngfi_cases:
        got = compute_ngfi_ngfq(*case)
        if got["nGFI"] is None:
            raise RuntimeError(f"nGFI returned NA at {case}.")
        ref, _, _ = compute_ngfi_exhaustive(
            *case, max_distance=got["nGFI"]
        )
        if ref != got["nGFI"]:
            raise RuntimeError(
                f"nGFI mismatch at {case}: certified={got['nGFI']}, "
                f"enumeration={ref}."
            )
    if verbose:
        print(
            f"  [5/9] nGFI: {len(ngfi_cases)} tables matched enumeration "
            "(includes the (29,13,24,11) case)."
        )

    # ---- 6. structural inequalities on random tables ----
    for _ in range(25):
        t = tuple(int(v) for v in rng.integers(0, 25, size=4))
        if sum(t) == 0:
            continue

        paths = all_toggle_paths(*t)
        fi = compute_fi_fq_mfq(*t, paths=paths)["FI"]
        gfi_r = compute_gfi_gfq(*t)
        gfi = gfi_r["GFI"]
        ngfi = compute_ngfi_ngfq(*t, gfi_result=gfi_r)["nGFI"]

        if gfi is not None and fi is not None and gfi > fi:
            raise RuntimeError(f"GFI > FI at {t}.")
        if ngfi is not None and gfi is not None and ngfi > 2 * gfi:
            raise RuntimeError(f"nGFI > 2*GFI at {t}.")
    if verbose:
        print("  [6/9] Inequalities GFI <= FI and nGFI <= 2*GFI hold.")

    # ---- 7. AV: classification logic and a known discordant table ----
    av_cases = [
        (6, 2, 1, 4), (28, 7, 20, 15), (13, 87, 25, 75), (2, 8, 8, 2),
        (0, 0, 3, 4), (5, 0, 0, 5), (10, 10, 10, 10), (1, 99, 6, 94),
        (9, 2, 2, 9), (7, 3, 2, 8), (75, 47, 21, 43), (29, 13, 24, 11),
        (0, 5, 0, 7), (3, 0, 4, 0),
    ]
    for case in av_cases:
        got = compute_av(*case)
        a_, b_, c_, d_ = case
        if min(a_ + b_, c_ + d_, a_ + c_, b_ + d_) == 0:
            if (got["AV_discordant"] is not None
                    or got["AV_which"] is not None):
                raise RuntimeError(
                    f"AV should be NA (ineligible) at {case}."
                )
            continue
        sigs = [
            got["p_fisher"] < ALPHA,
            got["p_pearson"] < ALPHA,
            got["p_pearson_yates"] < ALPHA,
        ]
        expect_disc = int(len(set(sigs)) > 1)
        if got["AV_discordant"] != expect_disc:
            raise RuntimeError(f"AV_discordant wrong at {case}.")
        if expect_disc == 0:
            if got["AV_which"] != "concordant":
                raise RuntimeError(f"AV_which wrong at {case}.")
        else:
            odd = AV_TEST_LABELS[sigs.index(sum(sigs) == 1)]
            if got["AV_which"] != odd:
                raise RuntimeError(f"AV_which wrong at {case}.")
    known = compute_av(28, 7, 20, 15)
    if (known["AV_discordant"] != 1
            or known["AV_which"] != "pearson_chi2"):
        raise RuntimeError(
            "AV known-discordant regression failed at (28,7,20,15): "
            f"got discordant={known['AV_discordant']}, "
            f"which={known['AV_which']!r} "
            "(expected 1, 'pearson_chi2')."
        )
    if pearson_p(0, 0, 3, 4) != 1.0 or pearson_p(0, 0, 3, 4, True) != 1.0:
        raise RuntimeError("pearson_p zero-margin convention failed.")
    if verbose:
        print(
            f"  [7/9] AV: {len(av_cases)} tables matched direct logic "
            "(zero-margin inputs NA as ineligible); (28,7,20,15) "
            "discordant with pearson_chi2 dissenting."
        )

    # ---- 8. RV: scalar-SciPy full enumeration + worked example ----
    rv_cases = [(5, 3, 2, 6), (3, 1, 1, 3), (6, 2, 1, 4), (0, 4, 2, 2)]
    for case in rv_cases:
        got = compute_rv(*case)
        ref = compute_rv_exhaustive(*case)
        if got["RV"] is None or ref is None:
            raise RuntimeError(f"RV returned NA at {case}.")
        bound = (got["truncation_error_bound"] or 0.0) + 1e-10
        if abs(got["RV"] - ref) > bound:
            raise RuntimeError(
                f"RV mismatch at {case}: enumerated={got['RV']!r}, "
                f"scalar reference={ref!r}."
            )
    worked = compute_rv(28, 7, 20, 15)
    if worked["RV"] is None or abs(worked["RV"] - 0.4541622) > 1e-6:
        raise RuntimeError(
            "RV worked-example regression failed at (28,7,20,15): "
            f"got {worked['RV']!r}, expected 0.4541622."
        )
    if verbose:
        print(
            f"  [8/9] RV: {len(rv_cases)} tables matched the scalar "
            "SciPy reference; worked example (28,7,20,15) = 0.4541622."
        )

    # ---- 9. RR / AD_any: Katz CI, agreement logic, zero-cell policy ----
    rr = compute_rr(28, 7, 20, 15)
    if rr["RR"] is None or abs(rr["RR"] - 1.4) > 1e-12:
        raise RuntimeError(
            f"RR wrong at (28,7,20,15): got {rr['RR']!r}, expected 1.4."
        )
    se_ref = float(np.sqrt(1 / 28 - 1 / 35 + 1 / 20 - 1 / 35))
    z_ref = float(norm.ppf(1 - ALPHA / 2))
    lo_ref = float(np.exp(np.log(1.4) - z_ref * se_ref))
    hi_ref = float(np.exp(np.log(1.4) + z_ref * se_ref))
    if (abs(rr["RR_CI_low"] - lo_ref) > 1e-12
            or abs(rr["RR_CI_high"] - hi_ref) > 1e-12):
        raise RuntimeError("RR Katz CI wrong at (28,7,20,15).")
    if rr["RR_signif"] != 1 or rr["RR_CI_discordant"] != 1:
        raise RuntimeError(
            "RR agreement flags wrong at (28,7,20,15): the Katz CI "
            "excludes 1 while Fisher is non-significant."
        )
    if compute_ad_any(compute_av(28, 7, 20, 15), rr) != 1:
        raise RuntimeError("AD_any wrong at (28,7,20,15).")
    if compute_ad_any({"AV_discordant": None},
                      {"RR_CI_discordant": None}) is not None:
        raise RuntimeError("AD_any should be NA when both tiers are NA.")
    if compute_ad_any({"AV_discordant": None},
                      {"RR_CI_discordant": 1}) != 1:
        raise RuntimeError("AD_any single-tier decision failed.")
    if compute_ad_any({"AV_discordant": 0},
                      {"RR_CI_discordant": None}) != 0:
        raise RuntimeError("AD_any single-tier zero decision failed.")

    rng3 = np.random.default_rng(20260906)
    rr_checked = 0
    for _ in range(300):
        t = tuple(int(v) for v in rng3.integers(0, 40, size=4))
        r = compute_rr(*t)
        a_, b_, c_, d_ = t
        nA_, nB_ = a_ + b_, c_ + d_
        if nA_ == 0 or nB_ == 0:
            if r["RR"] is not None or r["RR_signif"] is not None:
                raise RuntimeError(f"RR empty-arm policy wrong at {t}.")
            continue
        if c_ == 0:
            if r["RR"] is not None or r["RR_CI_low"] is not None:
                raise RuntimeError(f"RR c=0 policy wrong at {t}.")
            continue
        if a_ == 0:
            if r["RR"] != 0.0 or r["RR_CI_low"] is not None:
                raise RuntimeError(f"RR a=0 policy wrong at {t}.")
            continue
        sig_ci = 1 if (r["RR_CI_low"] > 1.0 or r["RR_CI_high"] < 1.0) else 0
        if sig_ci != r["RR_signif"]:
            raise RuntimeError(f"RR_signif logic wrong at {t}.")
        fisher_sig = test_p(*t) < ALPHA
        if r["RR_CI_discordant"] != int(bool(r["RR_signif"]) != fisher_sig):
            raise RuntimeError(f"RR_CI_discordant logic wrong at {t}.")
        rr_checked += 1
    if verbose:
        print(
            f"  [9/9] RR / AD_any: worked example pinned "
            f"(CI excludes 1, discordant with Fisher); {rr_checked} "
            "random tables matched the agreement logic and zero-cell "
            "policy."
        )

    print("Regression tests passed.")


# ======================================================================
# Summary interface
# ======================================================================

def fragility_summary(a, b, c, d, alpha=ALPHA, quiet=False,
                      certify="ask"):
    """Calculate and print all metrics.

    certify="ask" automatically certifies small GFI/FD searches and offers
    one combined terminal prompt for larger searches. certify=True always
    certifies; certify=False skips all certification and labels bounds.
    Accepting the prompt reuses other completed metrics, including RV.
    """
    if certify not in (True, False, "ask"):
        raise ValueError('certify must be True, False, or "ask".')
    return _fragility_summary(a, b, c, d, alpha, quiet, certify)


def _fragility_summary(a, b, c, d, alpha=ALPHA, quiet=False,
                       certify="ask", _cached_results=None):
    """
    Calculate and print every metric. The full results always print
    first, with GFI and nGFI carrying the fast rigorous upper bound
    (small sweeps auto-certify silently). Only when at least one value
    remains a qualified upper bound does certify="ask" offer ONE
    combined confirmation; answering yes certifies both and reprints
    the summary. certify=True certifies inline; certify=False never
    sweeps.

    r11 adds nine columns: AV_discordant / AV_which (test-choice
    sensitivity of the baseline classification), RV (exact
    fitted-binomial resampling variability), RR / RR_CI_low /
    RR_CI_high / RR_signif / RR_CI_discordant (Katz relative risk
    with test-vs-CI agreement against Fisher), and AD_any (composite
    analysis-discordance flag over both tiers).
    """
    a, b, c, d = validate_table(a, b, c, d)
    N = n_total(a, b, c, d)
    cached = {} if _cached_results is None else dict(_cached_results)

    def reuse(name, compute):
        if name not in cached:
            cached[name] = compute()
        return cached[name]

    baseline_p = reuse("baseline_p", lambda: test_p(a, b, c, d))
    paths = reuse("paths", lambda: all_toggle_paths(a, b, c, d, alpha))
    res_fi = reuse("FI", lambda: compute_fi_fq_mfq(a, b, c, d, alpha, paths=paths))
    res_mfi = reuse("MFI", lambda: compute_mfi(a, b, c, d, alpha, paths=paths))
    res_sfi = reuse("SFI", lambda: compute_sfi(a, b, c, d, alpha, paths=paths))

    # Small default-mode sweeps still run automatically. Explicit False
    # always skips them; larger default-mode sweeps share the prompt below.
    first_pass = "auto" if certify == "ask" else certify
    if not quiet and "GFI" not in cached:
        print("Computing GFI ...", flush=True)
    res_gfi = reuse("GFI", lambda: compute_gfi_gfq(
        a, b, c, d, alpha, certify=first_pass))

    if not quiet and "FD" not in cached:
        print("Computing FD (fragility distance) ...", flush=True)
    res_ngfi = reuse("FD", lambda: compute_ngfi_ngfq(
        a, b, c, d, alpha, gfi_result=res_gfi, certify=first_pass))

    res_other = reuse("other", lambda: compute_ufi_pfi(a, b, c, d, alpha))
    res_av = reuse("AV", lambda: compute_av(a, b, c, d, alpha))
    if not quiet and "RV" not in cached:
        print("Computing RV (resampling variability) ...", flush=True)
    res_rv = reuse("RV", lambda: compute_rv(a, b, c, d, alpha))
    res_rr = reuse("RR", lambda: compute_rr(a, b, c, d, alpha))

    FI = res_fi.get("FI")
    FQ = res_fi.get("FQ")
    MFQ = res_fi.get("MFQ")
    chosen_arm = res_fi.get("arm")
    post_p = res_fi.get("final_p")
    MFI = res_mfi.get("MFI")
    SFI = res_sfi.get("SFI")
    GFI = res_gfi.get("GFI")
    nGFI = res_ngfi.get("nGFI")
    RQ = res_other.get("RQ")
    UFI = res_other.get("UFI_Walter")
    PFI_Pearson = res_other.get("PFI_Pearson")
    SFM = res_other.get("SFM")
    AV_discordant = res_av.get("AV_discordant")
    AV_which = res_av.get("AV_which")
    RV = res_rv.get("RV")
    RR = res_rr.get("RR")
    RR_CI_low = res_rr.get("RR_CI_low")
    RR_CI_high = res_rr.get("RR_CI_high")
    RR_signif = res_rr.get("RR_signif")
    RR_CI_discordant = res_rr.get("RR_CI_discordant")
    AD_any = compute_ad_any(res_av, res_rr)

    # ---- structural checks. A violation of a proven inequality by a
    # ---- CERTIFIED value means a defect, so that value is withheld.
    # ---- Uncertified upper bounds are exempt (a bound may be loose).
    warnings = []
    if (GFI is not None and FI is not None and GFI > FI
            and res_gfi.get("certified")):
        warnings.append(
            f"ALGORITHM ERROR: GFI ({GFI}) > FI ({FI}). "
            "GFI withheld as NA."
        )
        GFI = None
    if (nGFI is not None and GFI is not None and nGFI > 2 * GFI
            and res_ngfi.get("certified") and res_gfi.get("certified")):
        warnings.append(
            f"ALGORITHM ERROR: FD ({nGFI}) > 2*GFI ({2 * GFI}). "
            "FD withheld as NA."
        )
        nGFI = None

    def fmt_p(x):
        return "NA" if x is None else f"{x:.6f}"

    def fmt_f(x):
        return "NA" if x is None else f"{x:.6f}"

    def fmt_i(x):
        return "NA" if x is None else str(int(x))

    headers = [
        "baseline_pvalue", "FI", "FQ", "MFQ", "chosen_arm",
        "post_toggle_pvalue", "MFI", "GFI", "SFI", "RQ", "UFI",
        "PFI_Pearson", "SFM", "FD", "AV_discordant", "AV_which", "RV",
        "RR", "RR_CI_low", "RR_CI_high", "RR_signif",
        "RR_CI_discordant", "AD_any",
    ]
    values = [
        fmt_p(baseline_p),
        fmt_i(FI),
        fmt_f(FQ),
        fmt_f(MFQ),
        chosen_arm if chosen_arm is not None else "NA",
        fmt_p(post_p),
        fmt_i(MFI),
        fmt_i(GFI),
        fmt_i(SFI),
        fmt_f(RQ),
        fmt_i(UFI),
        fmt_f(PFI_Pearson),
        fmt_f(SFM),
        fmt_i(nGFI),
        fmt_i(AV_discordant),
        AV_which if AV_which is not None else "NA",
        fmt_f(RV),
        fmt_f(RR),
        fmt_f(RR_CI_low),
        fmt_f(RR_CI_high),
        fmt_i(RR_signif),
        fmt_i(RR_CI_discordant),
        fmt_i(AD_any),
    ]

    print()
    print("\t".join(headers))
    print("\t".join(values))

    for message in warnings:
        print()
        print(message)

    # ---- GFI diagnostics ----
    print()
    if GFI is not None:
        print("GFI witness table:", res_gfi.get("witness_table"))
        print("GFI witness p-value:", fmt_p(res_gfi.get("final_p")))
        if res_gfi.get("certified"):
            print("GFI status: certified exact")
        else:
            print("GFI status: UPPER BOUND (not certified)")
            print("GFI note:", res_gfi.get("note", ""))
        print("GFI candidates examined:",
              f"{res_gfi.get('candidates_examined', 0):,}")
        print("GFI algorithm:", res_gfi.get("algorithm"))
    else:
        print("GFI = NA")
        print("GFI note:", res_gfi.get("note", ""))
        lb = res_gfi.get("lower_bound")
        ub = res_gfi.get("upper_bound")
        if lb is not None and ub is not None:
            print(
                "GFI proven bracket (NOT a value, NOT an estimate): "
                f"{lb} <= GFI <= {ub}"
            )

    # ---- FD (fragility distance; legacy name nGFI) diagnostics ----
    print()
    if nGFI is not None:
        print("FD witness table:", res_ngfi.get("witness_table"))
        print("FD witness p-value:", fmt_p(res_ngfi.get("final_p")))
        if res_ngfi.get("certified"):
            print("FD status: certified exact")
        else:
            print("FD status: UPPER BOUND (not certified)")
            print("FD note:", res_ngfi.get("note", ""))
        print("FD mechanism:", res_ngfi.get("mechanism"))
        print("FD original N:", res_ngfi.get("original_N"))
        print("FD final N:", res_ngfi.get("final_N"))
        print("FD net N change:", res_ngfi.get("net_N_change"))
        print("FD incumbent before certification:",
              res_ngfi.get("incumbent"))
        print("FD incumbent source:", res_ngfi.get("incumbent_source"))
        print("FD certified exact:", res_ngfi.get("certified"))
        print("FD candidates examined:",
              f"{res_ngfi.get('candidates_examined', 0):,}")
        print("FD cross-checked by enumeration:",
              res_ngfi.get("cross_checked"))
        print("FD algorithm:", res_ngfi.get("algorithm"))
        if GFI is not None:
            print("2 x GFI:", 2 * GFI, "[must be >= FD]")
    else:
        print("FD = NA")
        print("FD note:", res_ngfi.get("note", ""))
        lb = res_ngfi.get("lower_bound")
        ub = res_ngfi.get("upper_bound")
        if lb is not None and ub is not None:
            print(
                "FD proven bracket (NOT a value, NOT an estimate): "
                f"{lb} <= FD <= {ub}"
            )

    if PFI_Pearson is None:
        print()
        print(
            "PFI-Pearson = undefined "
            "(Pearson chi-square is undefined at a zero marginal total)."
        )
    if SFM is None:
        print(
            "SFM = undefined "
            "(Pearson chi-square undefined, zero, or multiplier <= 1)."
        )

    # ---- AV diagnostics ----
    print()
    if AV_discordant is None:
        print("AV = NA (ineligible input)")
        print("AV note:", res_av.get("note", ""))
    else:
        print(f"AV (analysis variability) at alpha = {alpha}:")
        print(f"  fisher_exact   p = {fmt_p(res_av.get('p_fisher'))} "
              f"({state_label(res_av['p_fisher'], alpha)})")
        print(f"  pearson_chi2   p = {fmt_p(res_av.get('p_pearson'))} "
              f"({state_label(res_av['p_pearson'], alpha)})")
        print(f"  pearson_yates  p = {fmt_p(res_av.get('p_pearson_yates'))} "
              f"({state_label(res_av['p_pearson_yates'], alpha)})")
        print(f"  AV_discordant = {res_av['AV_discordant']}; "
              f"AV_which = {res_av['AV_which']}")

    # ---- RV diagnostics ----
    print()
    if RV is not None:
        print(f"RV (resampling variability): {RV:.7f}")
        print("RV method:", res_rv.get("method"))
        print("RV replicate tables examined:",
              f"{res_rv.get('pairs_examined', 0):,}")
        if res_rv.get("method") == "exact enumeration":
            print("RV truncation error bound:",
                  f"{res_rv.get('truncation_error_bound'):.2e}")
        else:
            print("RV Monte Carlo SE:", fmt_f(res_rv.get("mc_se")),
                  f"(seed {res_rv.get('mc_seed')})")
        print("RV fitted model: pA =", fmt_f(res_rv.get("pA")),
              " pB =", fmt_f(res_rv.get("pB")),
              f" (nA = {res_rv.get('nA')}, nB = {res_rv.get('nB')})")
        print("RV P(replicate significant):",
              fmt_f(res_rv.get("p_replicate_significant")))
        if res_rv.get("note"):
            print("RV note:", res_rv.get("note"))
    else:
        print("RV = NA")
        print("RV note:", res_rv.get("note", ""))

    # ---- RR diagnostics ----
    print()
    if RR is not None:
        print(f"RR (relative risk, Arm A vs Arm B): {RR:.6f}")
    else:
        print("RR = NA")
    if RR_CI_low is not None:
        level = f"{100.0 * (1.0 - alpha):g}%"
        print(f"RR {level} CI (Katz log-RR Wald): "
              f"[{RR_CI_low:.6f}, {RR_CI_high:.6f}]")
        print(f"RR_signif = {RR_signif} (1 = the CI excludes 1)")
    else:
        print("RR CI = NA")
    if RR_CI_discordant is not None:
        print(f"RR_CI_discordant = {RR_CI_discordant}")
        print("  (RR_CI_discordant compares the CI classification "
              "against the TWO-SIDED FISHER EXACT classification, the "
              "module's authoritative test. The Katz interval inverts "
              "a Wald z-test on log RR, not Fisher's test, so "
              "near-threshold disagreement is expected.)")
    if res_rr.get("note"):
        print("RR note:", res_rr.get("note"))

    # ---- AD_any diagnostics ----
    print()
    if AD_any is not None:
        print(f"AD_any = {AD_any} (analysis discordance on any tier: "
              "1 iff AV_discordant = 1 OR RR_CI_discordant = 1)")
    else:
        print("AD_any = NA (both discordance tiers are NA)")

    # ---- one combined confirmation, only when something is qualified ----
    need_gfi = (res_gfi.get("GFI") is not None
                and not res_gfi.get("certified"))
    need_ngfi = (res_ngfi.get("nGFI") is not None
                 and not res_ngfi.get("certified"))

    if certify == "ask" and (need_gfi or need_ngfi) and sys.stdin.isatty():
        print()
        print("The results above are complete, but these values are "
              "UPPER BOUNDS, not certified exact:")
        total_predicted = 0
        if need_gfi:
            predicted = _predict_gfi_candidates(int(res_gfi["GFI"]))
            total_predicted += predicted
            print(f"  GFI <= {res_gfi['GFI']}   (certification "
                  f"~{_humanize_count(predicted)} candidates)")
        if need_ngfi:
            predicted = _predict_ngfi_candidates(int(res_ngfi["nGFI"]))
            total_predicted += predicted
            print(f"  FD <= {res_ngfi['nGFI']}   (certification "
                  f"~{_humanize_count(predicted)} candidates)")
        print(f"Total predicted certification candidates: {total_predicted:,}.")
        print("Runtime depends on table size and available CPU cores; "
              "large searches can take minutes or longer.")
        try:
            answer = input(
                "Run the exact certification now (covers both GFI and "
                "FD)? [y/N]: "
            ).strip().lower()
        except (EOFError, KeyboardInterrupt):
            answer = ""
        if answer in ("y", "yes"):
            print()
            print("=" * 21, "CERTIFIED RESULTS", "=" * 21)
            if need_gfi:
                cached.pop("GFI", None)
            if need_ngfi:
                cached.pop("FD", None)
            return _fragility_summary(
                a, b, c, d, alpha=alpha, quiet=quiet, certify=True,
                _cached_results=cached,
            )

    return {
        "N": N,
        "baseline_p": baseline_p,
        "FI": res_fi,
        "MFI": res_mfi,
        "SFI": res_sfi,
        "GFI": res_gfi,
        "nGFI": res_ngfi,
        "FD": res_ngfi,
        "AV": res_av,
        "RV": res_rv,
        "RR": res_rr,
        "AD_any": AD_any,
        "other_metrics": res_other,
        "warnings": warnings,
    }


# ======================================================================
# CLI entry point
# ======================================================================

def main():
    if RUN_REGRESSION_TESTS:
        print("Running regression tests ...")
        run_regression_tests()
        print()

    print("Enter 2x2 table cells as integers.")
    a = int(input("a (Arm A events): ").strip())
    b = int(input("b (Arm A non-events): ").strip())
    c = int(input("c (Arm B events): ").strip())
    d = int(input("d (Arm B non-events): ").strip())
    print()

    fragility_summary(a, b, c, d, alpha=ALPHA)


if __name__ == "__main__":
    multiprocessing.freeze_support()
    main()

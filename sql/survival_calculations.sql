-- survival_calculations: one row per SFQ/SRQ calculation from survival.php.
-- Run once against the same database as fragility_calculations.
-- Until this table exists, DatabaseManager::saveSurvivalCalculation() logs the
-- failure to the PHP error log and the calculator still works.
-- CI_level is a proportion (0.95 = 95% CI); p_value is recovered from z.
CREATE TABLE IF NOT EXISTS survival_calculations (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    HR            DOUBLE NOT NULL,
    CI_lower      DOUBLE NOT NULL,
    CI_upper      DOUBLE NOT NULL,
    CI_level      DOUBLE NOT NULL,
    SE_ln_HR      DOUBLE NOT NULL,
    z             DOUBLE NOT NULL,
    p_value       DOUBLE NOT NULL,
    p_significant TINYINT(1) NOT NULL,
    SFQ           DOUBLE NOT NULL,
    SRQ           DOUBLE NOT NULL,
    hr_outside_ci TINYINT(1) NOT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

<?php
/**
 * DatabaseManager.php
 * Handles database operations for fragility calculations
 * Now includes GFI/GFQ storage
 * 
 * Citation: Heston, T. F. (2025). Fragility Metrics Toolkit [Software].
 * Zenodo. https://doi.org/10.5281/zenodo.17254763
 */

require_once 'db_config.php';

class DatabaseManager {
    
    /**
     * Save calculation results to database
     * 
     * @param array $result Result from FragilityCalculator::calculate()
     * @return int|false Insert ID on success, false on failure
     */
    public static function saveCalculation($result) {
        try {
            $pdo = getDbConnection();
            
            $sql = "INSERT INTO fragility_calculations (
                a, b, c, d, N,
                p_value, p_significant,
                FI, FQ, MFQ, fi_arm, fi_direction,
                a_post, b_post, c_post, d_post, p_value_post,
                GFI, GFQ, gfi_verified, gfi_method,
                a_gfi, b_gfi, c_gfi, d_gfi, p_value_gfi,
                RQ
            ) VALUES (
                :a, :b, :c, :d, :N,
                :p_value, :p_significant,
                :FI, :FQ, :MFQ, :fi_arm, :fi_direction,
                :a_post, :b_post, :c_post, :d_post, :p_value_post,
                :GFI, :GFQ, :gfi_verified, :gfi_method,
                :a_gfi, :b_gfi, :c_gfi, :d_gfi, :p_value_gfi,
                :RQ
            )";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                'a' => $result['input']['a'],
                'b' => $result['input']['b'],
                'c' => $result['input']['c'],
                'd' => $result['input']['d'],
                'N' => $result['input']['N'],
                'p_value' => $result['p']['value'],
                'p_significant' => $result['p']['significant'] ? 1 : 0,
                'FI' => $result['fr']['FI'],
                'FQ' => $result['fr']['FQ'],
                'MFQ' => $result['fr']['MFQ'],
                'fi_arm' => $result['fr']['arm'],
                'fi_direction' => $result['fr']['direction'],
                'a_post' => $result['fr']['post_toggle']['a'] ?? null,
                'b_post' => $result['fr']['post_toggle']['b'] ?? null,
                'c_post' => $result['fr']['post_toggle']['c'] ?? null,
                'd_post' => $result['fr']['post_toggle']['d'] ?? null,
                'p_value_post' => $result['fr']['final_p'] ?? null,
                'GFI' => $result['gfi']['GFI'] ?? null,
                'GFQ' => $result['gfi']['GFQ'] ?? null,
                'gfi_verified' => isset($result['gfi']['verified']) ? ($result['gfi']['verified'] ? 1 : 0) : null,
                'gfi_method' => $result['gfi']['method'] ?? null,
                'a_gfi' => $result['gfi']['post_toggle']['a'] ?? null,
                'b_gfi' => $result['gfi']['post_toggle']['b'] ?? null,
                'c_gfi' => $result['gfi']['post_toggle']['c'] ?? null,
                'd_gfi' => $result['gfi']['post_toggle']['d'] ?? null,
                'p_value_gfi' => $result['gfi']['final_p'] ?? null,
                'RQ' => $result['nb']['RQ'] ?? null
            ]);
            
            return $pdo->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Database save failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get calculation by ID
     * 
     * @param int $id Calculation ID
     * @return array|false Calculation data or false if not found
     */
    public static function getCalculation($id) {
        try {
            $pdo = getDbConnection();
            
            $sql = "SELECT * FROM fragility_calculations WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Database fetch failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get recent calculations
     * 
     * @param int $limit Number of results to return
     * @return array Array of calculations
     */
    public static function getRecentCalculations($limit = 10) {
        try {
            $pdo = getDbConnection();
            
            $sql = "SELECT id, a, b, c, d, N, p_value, MFQ, GFQ, RQ, created_at
                    FROM fragility_calculations
                    ORDER BY created_at DESC
                    LIMIT :limit";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Database fetch failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get statistics
     * 
     * @return array Statistics about calculations
     */
    public static function getStatistics() {
        try {
            $pdo = getDbConnection();
            
            $stats = [];
            
            // Total calculations
            $sql = "SELECT COUNT(*) as total FROM fragility_calculations";
            $stats['total'] = $pdo->query($sql)->fetch()['total'];
            
            // Significant results
            $sql = "SELECT COUNT(*) as count FROM fragility_calculations WHERE p_significant = 1";
            $stats['significant'] = $pdo->query($sql)->fetch()['count'];

            // GFI statistics
            $sql = "SELECT COUNT(*) as count FROM fragility_calculations WHERE GFI IS NOT NULL";
            $stats['gfi_computed'] = $pdo->query($sql)->fetch()['count'];
            
            $sql = "SELECT COUNT(*) as count FROM fragility_calculations WHERE gfi_verified = 1";
            $stats['gfi_verified'] = $pdo->query($sql)->fetch()['count'];
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Statistics fetch failed: " . $e->getMessage());
            return [
                'total' => 0,
                'significant' => 0,
                'gfi_computed' => 0,
                'gfi_verified' => 0
            ];
        }
    }
    
    /**
     * Get FI vs GFI comparison data
     * 
     * @return array Comparison statistics
     */
    public static function getFragilityComparison() {
        try {
            $pdo = getDbConnection();
            
            $sql = "SELECT 
                        COUNT(*) as total,
                        AVG(FI) as avg_fi,
                        AVG(GFI) as avg_gfi,
                        AVG(ABS(FI - GFI)) as avg_delta,
                        MAX(ABS(FI - GFI)) as max_delta,
                        SUM(CASE WHEN GFI > FI THEN 1 ELSE 0 END) as violations
                    FROM fragility_calculations 
                    WHERE FI IS NOT NULL AND GFI IS NOT NULL";
            
            return $pdo->query($sql)->fetch();
            
        } catch (PDOException $e) {
            error_log("Comparison query failed: " . $e->getMessage());
            return null;
        }
    }
}
?>

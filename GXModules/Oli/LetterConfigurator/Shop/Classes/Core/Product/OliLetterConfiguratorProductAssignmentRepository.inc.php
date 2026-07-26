<?php

/**
 * Central read access for product assignments.
 * Uses only module-owned tables and leaves Gambio core tables unchanged.
 */
final class OliLetterConfiguratorProductAssignmentRepository
{
    public function findActiveByProductId(int $productsId): ?OliLetterConfiguratorProductAssignment
    {
        if ($productsId <= 0 || !$this->tableExists('oli_lc_product_assignments')) {
            return null;
        }

        $typeSelect = $this->columnExists('oli_lc_product_assignments', 'configurator_type')
            ? "a.`configurator_type`"
            : "'contour_text' AS `configurator_type`";

        $result = xtc_db_query(
            "SELECT a.`assignment_id`, a.`products_id`, a.`product_template_id`, " .
            $typeSelect . ", a.`is_active` " .
            "FROM `oli_lc_product_assignments` a " .
            "INNER JOIN `oli_lc_product_templates` pt " .
            "ON pt.`product_template_id` = a.`product_template_id` " .
            "WHERE a.`products_id` = " . (int)$productsId . " " .
            "AND a.`is_active` = 1 AND pt.`is_active` = 1 LIMIT 1"
        );

        $row = xtc_db_fetch_array($result);
        return $row ? OliLetterConfiguratorProductAssignment::fromArray($row) : null;
    }

    public function isConfiguratorProduct(int $productsId, ?string $configuratorType = null): bool
    {
        $assignment = $this->findActiveByProductId($productsId);
        if ($assignment === null) {
            return false;
        }

        return $configuratorType === null || $assignment->getConfiguratorType() === $configuratorType;
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $safeTable = str_replace('`', '``', $tableName);
        $safeColumn = xtc_db_input($columnName);
        $result = xtc_db_query("SHOW COLUMNS FROM `" . $safeTable . "` LIKE '" . $safeColumn . "'");
        return xtc_db_num_rows($result) > 0;
    }

    private function tableExists(string $tableName): bool
    {
        $safeName = xtc_db_input($tableName);
        $result = xtc_db_query("SHOW TABLES LIKE '" . $safeName . "'");
        return xtc_db_num_rows($result) > 0;
    }
}

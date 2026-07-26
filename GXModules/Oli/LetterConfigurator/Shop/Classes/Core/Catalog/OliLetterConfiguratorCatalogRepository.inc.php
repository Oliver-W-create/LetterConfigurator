<?php

/**
 * Gambio database adapter for materials, production methods, thicknesses,
 * material-dependent colors and active fonts.
 */
class OliLetterConfiguratorCatalogRepository implements OliLetterConfiguratorCatalogRepositoryInterface
{
    /**
     * @param int $productTemplateId
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function loadForProductTemplate($productTemplateId)
    {
        $productTemplateId = (int)$productTemplateId;
        $empty = array(
            'materials' => array(),
            'production_methods' => array(),
            'colors' => array(),
            'thicknesses' => array(),
            'fonts' => $this->loadFonts(),
        );
        if ($productTemplateId <= 0) {
            return $empty;
        }

        $template = $this->loadTemplateModes($productTemplateId);
        if ($template === null) {
            return $empty;
        }

        $materials = $this->loadMaterials($productTemplateId);
        $methods = $this->loadProductionMethods($productTemplateId);
        $materialIds = $this->columnIds($materials, 'material_id');
        $methodIds = $this->columnIds($methods, 'production_method_id');

        return array(
            'materials' => $materials,
            'production_methods' => $methods,
            'colors' => $this->loadColors($productTemplateId, $materialIds, $template['color_mode']),
            'thicknesses' => $this->loadThicknesses($productTemplateId, $materialIds, $methodIds, $template['thickness_mode']),
            'fonts' => $empty['fonts'],
        );
    }

    private function loadTemplateModes($productTemplateId)
    {
        $result = xtc_db_query(
            "SELECT `color_mode`, `thickness_mode` FROM `oli_lc_product_templates` " .
            "WHERE `product_template_id` = " . (int)$productTemplateId . " AND `is_active` = 1 LIMIT 1"
        );
        $row = xtc_db_fetch_array($result);
        return $row ?: null;
    }

    private function loadMaterials($productTemplateId)
    {
        $items = array();
        $result = xtc_db_query(
            "SELECT m.`material_id`, m.`name`, m.`code`, m.`description` " .
            "FROM `oli_lc_product_template_materials` ptm " .
            "INNER JOIN `oli_lc_materials` m ON m.`material_id` = ptm.`material_id` " .
            "WHERE ptm.`product_template_id` = " . (int)$productTemplateId . " AND m.`is_active` = 1 " .
            "ORDER BY ptm.`sort_order` ASC, m.`sort_order` ASC, m.`name` ASC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $row['material_id'] = (int)$row['material_id'];
            $items[] = $row;
        }
        return $items;
    }

    private function loadProductionMethods($productTemplateId)
    {
        $items = array();
        $result = xtc_db_query(
            "SELECT pm.`production_method_id`, pm.`name`, pm.`method_key`, pm.`range_mode`, pm.`engine_key` " .
            "FROM `oli_lc_product_template_production_methods` ptpm " .
            "INNER JOIN `oli_lc_production_methods` pm ON pm.`production_method_id` = ptpm.`production_method_id` " .
            "WHERE ptpm.`product_template_id` = " . (int)$productTemplateId . " AND pm.`is_active` = 1 " .
            "ORDER BY ptpm.`sort_order` ASC, pm.`sort_order` ASC, pm.`name` ASC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $row['production_method_id'] = (int)$row['production_method_id'];
            $items[] = $row;
        }
        return $items;
    }

    private function loadColors($productTemplateId, array $materialIds, $mode)
    {
        if (!$materialIds) {
            return array();
        }
        $join = ((string)$mode === 'selected')
            ? "INNER JOIN `oli_lc_product_template_colors` ptc ON ptc.`color_id` = c.`color_id` AND ptc.`product_template_id` = " . (int)$productTemplateId
            : '';
        $items = array();
        $result = xtc_db_query(
            "SELECT c.`color_id`, c.`material_id`, c.`name`, c.`code`, c.`hex_value`, m.`name` AS `material_name` " .
            "FROM `oli_lc_colors` c " . $join . " " .
            "INNER JOIN `oli_lc_materials` m ON m.`material_id` = c.`material_id` " .
            "WHERE c.`is_active` = 1 AND c.`material_id` IN (" . implode(',', $materialIds) . ") " .
            "ORDER BY m.`sort_order` ASC, c.`sort_order` ASC, c.`name` ASC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $row['color_id'] = (int)$row['color_id'];
            $row['material_id'] = (int)$row['material_id'];
            $items[] = $row;
        }
        return $items;
    }

    private function loadThicknesses($productTemplateId, array $materialIds, array $methodIds, $mode)
    {
        if (!$materialIds || !$methodIds) {
            return array();
        }
        $join = ((string)$mode === 'selected')
            ? "INNER JOIN `oli_lc_product_template_thicknesses` ptt ON ptt.`thickness_id` = t.`thickness_id` AND ptt.`product_template_id` = " . (int)$productTemplateId
            : '';
        $items = array();
        $result = xtc_db_query(
            "SELECT t.`thickness_id`, t.`material_id`, t.`color_id` AS `legacy_color_id`, t.`thickness_min_mm`, t.`thickness_max_mm`, " .
            "m.`name` AS `material_name`, pm.`production_method_id`, pm.`name` AS `production_method_name`, pm.`range_mode`, " .
            "GROUP_CONCAT(DISTINCT tc.`color_id` ORDER BY tc.`color_id` SEPARATOR ',') AS `color_ids` " .
            "FROM `oli_lc_thicknesses` t " . $join . " " .
            "LEFT JOIN `oli_lc_thickness_colors` tc ON tc.`thickness_id` = t.`thickness_id` " .
            "INNER JOIN `oli_lc_materials` m ON m.`material_id` = t.`material_id` " .
            "INNER JOIN `oli_lc_production_methods` pm ON pm.`method_key` = t.`production_method` " .
            "WHERE t.`is_active` = 1 AND t.`material_id` IN (" . implode(',', $materialIds) . ") " .
            "AND pm.`is_active` = 1 AND pm.`production_method_id` IN (" . implode(',', $methodIds) . ") " .
            "GROUP BY t.`thickness_id`, t.`material_id`, t.`thickness_min_mm`, t.`thickness_max_mm`, " .
            "m.`name`, pm.`production_method_id`, pm.`name`, pm.`range_mode`, m.`sort_order`, pm.`sort_order`, t.`sort_order` " .
            "ORDER BY m.`sort_order` ASC, pm.`sort_order` ASC, t.`sort_order` ASC, t.`thickness_min_mm` ASC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $row['thickness_id'] = (int)$row['thickness_id'];
            $row['material_id'] = (int)$row['material_id'];
            $row['production_method_id'] = (int)$row['production_method_id'];
            $row['color_ids'] = isset($row['color_ids']) ? (string)$row['color_ids'] : '';
            $legacyColorId = isset($row['legacy_color_id']) ? (int)$row['legacy_color_id'] : 0;
            if ($row['color_ids'] === '' && $legacyColorId > 0) {
                $row['color_ids'] = (string)$legacyColorId;
            }
            $row['all_colors'] = $row['color_ids'] === '';
            unset($row['legacy_color_id']);
            $min = $this->formatMeasurement($row['thickness_min_mm']);
            $max = $this->formatMeasurement($row['thickness_max_mm']);
            $row['display_name'] = ((string)$row['range_mode'] === 'single' || (float)$row['thickness_min_mm'] === (float)$row['thickness_max_mm'])
                ? $min . ' mm'
                : $min . '–' . $max . ' mm';
            $items[] = $row;
        }
        return $items;
    }

    private function loadFonts()
    {
        $items = array();
        if (!$this->tableExists('oli_lc_fonts')) {
            return $items;
        }
        $result = xtc_db_query(
            "SELECT `font_id`, `display_name`, `family_name`, `style_name`, `stored_filename` " .
            "FROM `oli_lc_fonts` WHERE `is_active` = 1 AND `license_confirmed` = 1 " .
            "ORDER BY `display_name` ASC, `font_id` ASC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $row['font_id'] = (int)$row['font_id'];
            $items[] = $row;
        }
        return $items;
    }

    private function columnIds(array $rows, $key)
    {
        $ids = array();
        foreach ($rows as $row) {
            $id = isset($row[$key]) ? (int)$row[$key] : 0;
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        return array_values(array_unique($ids));
    }

    private function tableExists($tableName)
    {
        $result = xtc_db_query("SHOW TABLES LIKE '" . xtc_db_input((string)$tableName) . "'");
        return xtc_db_num_rows($result) > 0;
    }

    private function formatMeasurement($value)
    {
        $formatted = number_format((float)$value, 3, '.', '');
        return str_replace('.', ',', rtrim(rtrim($formatted, '0'), '.'));
    }
}

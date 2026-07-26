<?php
/* --------------------------------------------------------------
   OliLetterConfiguratorProductInfoThemeContentView.inc.php
   Phase M4.3 – frontend configurator with live pricing
   Provider: Oli
   --------------------------------------------------------------
*/

class OliLetterConfiguratorProductInfoThemeContentView extends OliLetterConfiguratorProductInfoThemeContentView_parent
{
    public function prepare_data()
    {
        parent::prepare_data();

        $this->set_content_data('OLI_LC_CONFIGURATOR', false);

        $cartError = isset($_SESSION['oli_lc_cart_error']) ? (string)$_SESSION['oli_lc_cart_error'] : '';
        unset($_SESSION['oli_lc_cart_error']);

        if (!($this->product instanceof product) || !$this->product->isProduct()) {
            return;
        }

        $productsId = (int)($this->product->data['products_id'] ?? 0);
        if ($productsId <= 0 || !$this->tableExists('oli_lc_product_assignments')) {
            return;
        }

        $assignmentResult = xtc_db_query(
            "SELECT a.`product_template_id`, pt.`name`, pt.`description`, pt.`color_mode`, pt.`thickness_mode`, pt.`price_profile_id` " .
            "FROM `oli_lc_product_assignments` a " .
            "INNER JOIN `oli_lc_product_templates` pt ON pt.`product_template_id` = a.`product_template_id` " .
            "WHERE a.`products_id` = " . $productsId . " AND a.`is_active` = 1 AND pt.`is_active` = 1 LIMIT 1"
        );

        if (!$assignment = xtc_db_fetch_array($assignmentResult)) {
            return;
        }

        $templateId = (int)$assignment['product_template_id'];
        $materialIds = [];
        $methodIds = [];
        $materials = [];
        $productionMethods = [];
        $colors = [];
        $thicknesses = [];

        $result = xtc_db_query(
            "SELECT m.`material_id`, m.`name` FROM `oli_lc_product_template_materials` ptm " .
            "INNER JOIN `oli_lc_materials` m ON m.`material_id` = ptm.`material_id` " .
            "WHERE ptm.`product_template_id` = " . $templateId . " AND m.`is_active` = 1 " .
            "ORDER BY ptm.`sort_order` ASC, m.`sort_order` ASC, m.`name` ASC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $row['material_id'] = (int)$row['material_id'];
            $materialIds[] = $row['material_id'];
            $materials[] = $row;
        }

        $result = xtc_db_query(
            "SELECT pm.`production_method_id`, pm.`name`, pm.`method_key`, pm.`range_mode`, pm.`engine_key` " .
            "FROM `oli_lc_product_template_production_methods` ptpm " .
            "INNER JOIN `oli_lc_production_methods` pm ON pm.`production_method_id` = ptpm.`production_method_id` " .
            "WHERE ptpm.`product_template_id` = " . $templateId . " AND pm.`is_active` = 1 " .
            "ORDER BY ptpm.`sort_order` ASC, pm.`sort_order` ASC, pm.`name` ASC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $row['production_method_id'] = (int)$row['production_method_id'];
            $methodIds[] = $row['production_method_id'];
            $productionMethods[] = $row;
        }

        if ($materialIds) {
            $materialSql = implode(',', array_map('intval', $materialIds));
            if ((string)$assignment['color_mode'] === 'selected') {
                $colorJoin = "INNER JOIN `oli_lc_product_template_colors` ptc ON ptc.`color_id` = c.`color_id` AND ptc.`product_template_id` = " . $templateId;
            } else {
                $colorJoin = '';
            }
            $result = xtc_db_query(
                "SELECT c.`color_id`, c.`material_id`, c.`name`, c.`hex_value`, m.`name` AS `material_name` " .
                "FROM `oli_lc_colors` c " . $colorJoin . " " .
                "INNER JOIN `oli_lc_materials` m ON m.`material_id` = c.`material_id` " .
                "WHERE c.`is_active` = 1 AND c.`material_id` IN (" . $materialSql . ") " .
                "ORDER BY m.`sort_order` ASC, c.`sort_order` ASC, c.`name` ASC"
            );
            while ($row = xtc_db_fetch_array($result)) {
                $row['color_id'] = (int)$row['color_id'];
                $row['material_id'] = (int)$row['material_id'];
                $colors[] = $row;
            }
        }

        if ($materialIds && $methodIds) {
            $materialSql = implode(',', array_map('intval', $materialIds));
            $methodSql = implode(',', array_map('intval', $methodIds));
            if ((string)$assignment['thickness_mode'] === 'selected') {
                $thicknessJoin = "INNER JOIN `oli_lc_product_template_thicknesses` ptt ON ptt.`thickness_id` = t.`thickness_id` AND ptt.`product_template_id` = " . $templateId;
            } else {
                $thicknessJoin = '';
            }
            $result = xtc_db_query(
                "SELECT DISTINCT t.`thickness_id`, t.`material_id`, t.`thickness_min_mm`, t.`thickness_max_mm`, " .
                "m.`name` AS `material_name`, pm.`production_method_id`, pm.`name` AS `production_method_name`, pm.`range_mode` " .
                "FROM `oli_lc_thicknesses` t " . $thicknessJoin . " " .
                "INNER JOIN `oli_lc_materials` m ON m.`material_id` = t.`material_id` " .
                "INNER JOIN `oli_lc_production_methods` pm ON pm.`method_key` = t.`production_method` " .
                "WHERE t.`is_active` = 1 AND t.`material_id` IN (" . $materialSql . ") " .
                "AND pm.`is_active` = 1 AND pm.`production_method_id` IN (" . $methodSql . ") " .
                "ORDER BY m.`sort_order` ASC, pm.`sort_order` ASC, t.`sort_order` ASC, t.`thickness_min_mm` ASC"
            );
            while ($row = xtc_db_fetch_array($result)) {
                $row['thickness_id'] = (int)$row['thickness_id'];
                $row['material_id'] = (int)$row['material_id'];
                $row['production_method_id'] = (int)$row['production_method_id'];
                $min = $this->formatMeasurement($row['thickness_min_mm']);
                $max = $this->formatMeasurement($row['thickness_max_mm']);
                $row['display_name'] = ((string)$row['range_mode'] === 'single' || (float)$row['thickness_min_mm'] === (float)$row['thickness_max_mm'])
                    ? $min . ' mm'
                    : $min . '–' . $max . ' mm';
                $thicknesses[] = $row;
            }
        }

        $pricing = null;
        $priceProfileId = (int)($assignment['price_profile_id'] ?? 0);
        if ($priceProfileId > 0 && $this->tableExists('oli_lc_price_profiles')) {
            $profileResult = xtc_db_query(
                "SELECT `configuration_json` FROM `oli_lc_price_profiles` " .
                "WHERE `price_profile_id` = " . $priceProfileId . " AND `is_active` = 1 LIMIT 1"
            );
            if ($profile = xtc_db_fetch_array($profileResult)) {
                $configuration = json_decode((string)$profile['configuration_json'], true);
                if (is_array($configuration)) {
                    $profileMethodId = 0;
                    $methodKey = (string)($configuration['production_method'] ?? '');
                    if ($methodKey !== '') {
                        $methodResult = xtc_db_query(
                            "SELECT `production_method_id` FROM `oli_lc_production_methods` " .
                            "WHERE `method_key` = '" . xtc_db_input($methodKey) . "' AND `is_active` = 1 LIMIT 1"
                        );
                        if ($method = xtc_db_fetch_array($methodResult)) {
                            $profileMethodId = (int)$method['production_method_id'];
                        }
                    }

                    $taxRate = 0.0;
                    $taxClassId = (int)($this->product->data['products_tax_class_id'] ?? 0);
                    if ($taxClassId > 0 && function_exists('xtc_get_tax_rate')) {
                        $taxRate = (float)xtc_get_tax_rate($taxClassId);
                    }

                    $pricing = [
                        'material_id' => (int)($configuration['material_id'] ?? 0),
                        'production_method_id' => $profileMethodId,
                        'area_price_per_m2' => (float)($configuration['area_price_per_m2'] ?? 0),
                        'contour_price_per_mm' => (float)($configuration['contour_price_per_mm'] ?? 0),
                        'price_per_character' => (float)($configuration['price_per_character'] ?? 0),
                        'fixed_price' => (float)($configuration['fixed_price'] ?? 0),
                        'minimum_price' => (float)($configuration['minimum_price'] ?? 0),
                        'setup_fee' => (float)($configuration['setup_fee'] ?? 0),
                        'waste_percent' => (float)($configuration['waste_percent'] ?? 0),
                        'tax_rate' => $taxRate,
                    ];
                }
            }
        }

        $this->set_content_data('OLI_LC_CONFIGURATOR', [
            'product_template_id' => $templateId,
            'name' => (string)$assignment['name'],
            'description' => (string)$assignment['description'],
            'materials' => $materials,
            'production_methods' => $productionMethods,
            'colors' => $colors,
            'thicknesses' => $thicknesses,
            'pricing' => $pricing,
            'cart_error' => $cartError,
            'limits' => [
                'text_max_length' => 255,
                'dimension_min_mm' => 1,
                'dimension_max_mm' => 100000,
            ],
        ]);
    }

    private function tableExists($tableName)
    {
        $safeName = xtc_db_input((string)$tableName);
        $result = xtc_db_query("SHOW TABLES LIKE '" . $safeName . "'");
        return xtc_db_num_rows($result) > 0;
    }

    private function formatMeasurement($value)
    {
        $formatted = number_format((float)$value, 3, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');
        return str_replace('.', ',', $formatted);
    }
}

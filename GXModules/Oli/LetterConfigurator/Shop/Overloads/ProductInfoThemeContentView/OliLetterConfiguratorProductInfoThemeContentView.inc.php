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
        if ($productsId <= 0) {
            return;
        }

        $assignmentRepository = MainFactory::create('OliLetterConfiguratorProductAssignmentRepository');
        $productAssignment = $assignmentRepository->findActiveByProductId($productsId);
        if ($productAssignment === null || $productAssignment->getConfiguratorType() !== 'contour_text') {
            return;
        }

        $templateId = $productAssignment->getProductTemplateId();
        $assignmentResult = xtc_db_query(
            "SELECT `product_template_id`, `name`, `description`, `color_mode`, `thickness_mode`, `price_profile_id` " .
            "FROM `oli_lc_product_templates` WHERE `product_template_id` = " . $templateId . " " .
            "AND `is_active` = 1 LIMIT 1"
        );
        if (!$assignment = xtc_db_fetch_array($assignmentResult)) {
            return;
        }

        $catalogRepository = MainFactory::create('OliLetterConfiguratorCatalogRepository');
        $catalog = $catalogRepository->loadForProductTemplate($templateId);

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
            'materials' => $catalog['materials'],
            'production_methods' => $catalog['production_methods'],
            'colors' => $catalog['colors'],
            'thicknesses' => $catalog['thicknesses'],
            'fonts' => $catalog['fonts'],
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

}

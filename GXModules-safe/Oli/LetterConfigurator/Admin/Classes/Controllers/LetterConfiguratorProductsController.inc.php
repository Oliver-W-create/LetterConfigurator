<?php
/* --------------------------------------------------------------
   LetterConfiguratorProductsController.inc.php
   Phase M3.14.4 – product templates with price profile assignment
   Provider: Oli
   --------------------------------------------------------------
*/

class LetterConfiguratorProductsController extends AdminHttpViewController
{
    public function actionDefault()
    {
        $this->ensureSchema();

        $title = new NonEmptyStringType('Buchstaben-Konfigurator – Produkte');
        $template = $this->getTemplateFile(
            'GXModules/Oli/LetterConfigurator/Admin/Html/letter_configurator_products.html'
        );

        $editId = (int)$this->_getQueryParameter('edit_id');
        $editProduct = [
            'product_template_id' => 0,
            'name' => '',
            'description' => '',
            'is_active' => 1,
            'sort_order' => 0,
            'color_mode' => 'all',
            'thickness_mode' => 'all',
            'price_profile_id' => 0,
        ];
        $selectedMaterialIds = [];
        $selectedProductionMethodIds = [];
        $selectedColorIds = [];
        $selectedThicknessIds = [];

        if ($editId > 0) {
            $result = xtc_db_query(
                "SELECT `product_template_id`, `name`, `description`, `is_active`, `sort_order`, `color_mode`, `thickness_mode`, `price_profile_id` " .
                "FROM `oli_lc_product_templates` WHERE `product_template_id` = " . $editId . " LIMIT 1"
            );
            if ($row = xtc_db_fetch_array($result)) {
                $editProduct = $row;

                $assignmentResult = xtc_db_query(
                    "SELECT `material_id` FROM `oli_lc_product_template_materials` " .
                    "WHERE `product_template_id` = " . $editId . " ORDER BY `sort_order` ASC, `material_id` ASC"
                );
                while ($assignment = xtc_db_fetch_array($assignmentResult)) {
                    $selectedMaterialIds[] = (int)$assignment['material_id'];
                }

                $methodAssignmentResult = xtc_db_query(
                    "SELECT `production_method_id` FROM `oli_lc_product_template_production_methods` " .
                    "WHERE `product_template_id` = " . $editId . " ORDER BY `sort_order` ASC, `production_method_id` ASC"
                );
                while ($assignment = xtc_db_fetch_array($methodAssignmentResult)) {
                    $selectedProductionMethodIds[] = (int)$assignment['production_method_id'];
                }

                $colorAssignmentResult = xtc_db_query(
                    "SELECT `color_id` FROM `oli_lc_product_template_colors` " .
                    "WHERE `product_template_id` = " . $editId . " ORDER BY `sort_order` ASC, `color_id` ASC"
                );
                while ($assignment = xtc_db_fetch_array($colorAssignmentResult)) {
                    $selectedColorIds[] = (int)$assignment['color_id'];
                }


                $thicknessAssignmentResult = xtc_db_query(
                    "SELECT `thickness_id` FROM `oli_lc_product_template_thicknesses` " .
                    "WHERE `product_template_id` = " . $editId . " ORDER BY `sort_order` ASC, `thickness_id` ASC"
                );
                while ($assignment = xtc_db_fetch_array($thicknessAssignmentResult)) {
                    $selectedThicknessIds[] = (int)$assignment['thickness_id'];
                }
            }
        }

        $materials = [];
        $materialResult = xtc_db_query(
            "SELECT `material_id`, `name`, `is_active`, `sort_order` FROM `oli_lc_materials` " .
            "ORDER BY `sort_order` ASC, `name` ASC"
        );
        while ($material = xtc_db_fetch_array($materialResult)) {
            $material['is_selected'] = in_array((int)$material['material_id'], $selectedMaterialIds, true) ? 1 : 0;
            $materials[] = $material;
        }

        $productionMethods = [];
        $productionMethodResult = xtc_db_query(
            "SELECT `production_method_id`, `name`, `method_key`, `engine_key`, `range_mode`, `is_active`, `sort_order` " .
            "FROM `oli_lc_production_methods` WHERE `is_active` = 1 ORDER BY `sort_order` ASC, `name` ASC"
        );
        while ($method = xtc_db_fetch_array($productionMethodResult)) {
            $method['is_selected'] = in_array((int)$method['production_method_id'], $selectedProductionMethodIds, true) ? 1 : 0;
            $productionMethods[] = $method;
        }

        $colors = [];
        $colorResult = xtc_db_query(
            "SELECT c.`color_id`, c.`material_id`, c.`name`, c.`hex_value`, c.`is_active`, c.`sort_order`, " .
            "m.`name` AS `material_name` FROM `oli_lc_colors` c " .
            "INNER JOIN `oli_lc_materials` m ON m.`material_id` = c.`material_id` " .
            "WHERE c.`is_active` = 1 ORDER BY m.`sort_order` ASC, m.`name` ASC, c.`sort_order` ASC, c.`name` ASC"
        );
        while ($color = xtc_db_fetch_array($colorResult)) {
            $color['is_selected'] = in_array((int)$color['color_id'], $selectedColorIds, true) ? 1 : 0;
            $colors[] = $color;
        }

        $thicknesses = [];
        $thicknessResult = xtc_db_query(
            "SELECT t.`thickness_id`, t.`material_id`, t.`production_method`, t.`thickness_min_mm`, t.`thickness_max_mm`, " .
            "t.`is_active`, t.`sort_order`, m.`name` AS `material_name`, pm.`production_method_id`, " .
            "pm.`name` AS `production_method_name`, pm.`range_mode` " .
            "FROM `oli_lc_thicknesses` t " .
            "INNER JOIN `oli_lc_materials` m ON m.`material_id` = t.`material_id` " .
            "INNER JOIN `oli_lc_production_methods` pm ON pm.`method_key` = t.`production_method` " .
            "WHERE t.`is_active` = 1 AND pm.`is_active` = 1 " .
            "ORDER BY m.`sort_order` ASC, m.`name` ASC, pm.`sort_order` ASC, pm.`name` ASC, t.`sort_order` ASC, t.`thickness_min_mm` ASC"
        );
        while ($thickness = xtc_db_fetch_array($thicknessResult)) {
            $min = $this->formatMeasurement($thickness['thickness_min_mm']);
            $max = $this->formatMeasurement($thickness['thickness_max_mm']);
            $thickness['display_name'] = ((string)$thickness['range_mode'] === 'single' || (float)$thickness['thickness_min_mm'] === (float)$thickness['thickness_max_mm'])
                ? $min . ' mm'
                : $min . '–' . $max . ' mm';
            $thickness['is_selected'] = in_array((int)$thickness['thickness_id'], $selectedThicknessIds, true) ? 1 : 0;
            $thicknesses[] = $thickness;
        }

        $priceProfiles = [];
        $priceProfileResult = xtc_db_query(
            "SELECT `price_profile_id`, `name`, `configuration_json`, `is_active` " .
            "FROM `oli_lc_price_profiles` WHERE `is_active` = 1 ORDER BY `name` ASC"
        );
        while ($profile = xtc_db_fetch_array($priceProfileResult)) {
            $configuration = json_decode((string)$profile['configuration_json'], true);
            if (!is_array($configuration)) {
                $configuration = [];
            }
            $profile['material_id'] = (int)($configuration['material_id'] ?? 0);
            $profileMethodKey = (string)($configuration['production_method'] ?? '');
            $profile['production_method_id'] = 0;
            $profile['material_name'] = '';
            $profile['production_method_name'] = '';

            foreach ($materials as $material) {
                if ((int)$material['material_id'] === $profile['material_id']) {
                    $profile['material_name'] = (string)$material['name'];
                    break;
                }
            }
            foreach ($productionMethods as $method) {
                if ((string)$method['method_key'] === $profileMethodKey) {
                    $profile['production_method_id'] = (int)$method['production_method_id'];
                    $profile['production_method_name'] = (string)$method['name'];
                    break;
                }
            }
            $profile['is_selected'] = (int)$editProduct['price_profile_id'] === (int)$profile['price_profile_id'] ? 1 : 0;
            $priceProfiles[] = $profile;
        }

        $products = [];
        $result = xtc_db_query(
            "SELECT pt.`product_template_id`, pt.`name`, pt.`description`, pt.`is_active`, pt.`sort_order`, pt.`updated_at`, pt.`color_mode`, pt.`price_profile_id`, pp.`name` AS `price_profile_name`, " .
            "GROUP_CONCAT(DISTINCT m.`name` ORDER BY m.`sort_order` ASC, m.`name` ASC SEPARATOR ', ') AS `material_names`, " .
            "GROUP_CONCAT(DISTINCT pm.`name` ORDER BY pm.`sort_order` ASC, pm.`name` ASC SEPARATOR ', ') AS `production_method_names`, " .
            "CASE WHEN pt.`color_mode` = 'all' THEN 'Alle Farben' " .
            "ELSE GROUP_CONCAT(DISTINCT c.`name` ORDER BY c.`sort_order` ASC, c.`name` ASC SEPARATOR ', ') END AS `color_names`, " .
            "CASE WHEN pt.`thickness_mode` = 'all' THEN 'Alle Materialstärken' " .
            "ELSE GROUP_CONCAT(DISTINCT CONCAT(TRIM(TRAILING '.000' FROM FORMAT(t.`thickness_min_mm`, 3)), ' mm') ORDER BY t.`sort_order` ASC, t.`thickness_min_mm` ASC SEPARATOR ', ') END AS `thickness_names` " .
            "FROM `oli_lc_product_templates` pt LEFT JOIN `oli_lc_price_profiles` pp ON pp.`price_profile_id` = pt.`price_profile_id` " .
            "LEFT JOIN `oli_lc_product_template_materials` ptm ON ptm.`product_template_id` = pt.`product_template_id` " .
            "LEFT JOIN `oli_lc_materials` m ON m.`material_id` = ptm.`material_id` " .
            "LEFT JOIN `oli_lc_product_template_production_methods` ptpm ON ptpm.`product_template_id` = pt.`product_template_id` " .
            "LEFT JOIN `oli_lc_production_methods` pm ON pm.`production_method_id` = ptpm.`production_method_id` " .
            "LEFT JOIN `oli_lc_product_template_colors` ptc ON ptc.`product_template_id` = pt.`product_template_id` " .
            "LEFT JOIN `oli_lc_colors` c ON c.`color_id` = ptc.`color_id` " .
            "LEFT JOIN `oli_lc_product_template_thicknesses` ptt ON ptt.`product_template_id` = pt.`product_template_id` " .
            "LEFT JOIN `oli_lc_thicknesses` t ON t.`thickness_id` = ptt.`thickness_id` " .
            "GROUP BY pt.`product_template_id`, pt.`name`, pt.`description`, pt.`is_active`, pt.`sort_order`, pt.`updated_at`, pt.`color_mode`, pt.`thickness_mode`, pt.`price_profile_id`, pp.`name` " .
            "ORDER BY pt.`sort_order` ASC, pt.`name` ASC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $row['edit_url'] = xtc_href_link(
                'admin.php',
                'do=LetterConfiguratorProducts&edit_id=' . (int)$row['product_template_id']
            );
            $products[] = $row;
        }

        $data = MainFactory::create('KeyValueCollection', [
            'pageToken' => $_SESSION['coo_page_token']->generate_token(),
            'products' => $products,
            'materials' => $materials,
            'productionMethods' => $productionMethods,
            'colors' => $colors,
            'thicknesses' => $thicknesses,
            'priceProfiles' => $priceProfiles,
            'editProduct' => $editProduct,
            'action_save' => xtc_href_link('admin.php', 'do=LetterConfiguratorProducts/Save'),
            'action_toggle' => xtc_href_link('admin.php', 'do=LetterConfiguratorProducts/Toggle'),
            'action_delete' => xtc_href_link('admin.php', 'do=LetterConfiguratorProducts/Delete'),
            'action_cancel' => xtc_href_link('admin.php', 'do=LetterConfiguratorProducts'),
        ]);

        return MainFactory::create('AdminLayoutHttpControllerResponse', $title, $template, $data);
    }

    public function actionSave()
    {
        $this->_validatePageToken();
        $this->ensureSchema();

        $product = (array)$this->_getPostData('product');
        $id = isset($product['product_template_id']) ? (int)$product['product_template_id'] : 0;
        $name = trim((string)($product['name'] ?? ''));
        $description = trim((string)($product['description'] ?? ''));
        $sortOrder = isset($product['sort_order']) ? (int)$product['sort_order'] : 0;
        $isActive = !empty($product['is_active']) ? 1 : 0;
        $materialIds = $this->normalizeIdList($this->_getPostData('material_ids'));
        $productionMethodIds = $this->normalizeIdList($this->_getPostData('production_method_ids'));
        $colorMode = (string)$this->_getPostData('color_mode') === 'selected' ? 'selected' : 'all';
        $colorIds = $this->normalizeIdList($this->_getPostData('color_ids'));
        $thicknessMode = (string)$this->_getPostData('thickness_mode') === 'selected' ? 'selected' : 'all';
        $thicknessIds = $this->normalizeIdList($this->_getPostData('thickness_ids'));
        $priceProfileId = (int)$this->_getPostData('price_profile_id');

        if ($name === '') {
            $GLOBALS['messageStack']->add_session('Der Produktname ist ein Pflichtfeld.', 'error');
            return $this->redirectToList($id);
        }

        if ($colorMode === 'selected') {
            $colorIds = $this->filterColorsByMaterials($colorIds, $materialIds);
        } else {
            $colorIds = [];
        }


        if ($thicknessMode === 'selected') {
            $thicknessIds = $this->filterThicknessesBySelection($thicknessIds, $materialIds, $productionMethodIds);
        } else {
            $thicknessIds = [];
        }

        if ($priceProfileId <= 0) {
            $GLOBALS['messageStack']->add_session('Bitte wählen Sie ein Preisprofil aus.', 'error');
            return $this->redirectToList($id);
        }

        if ($priceProfileId > 0 && !$this->priceProfileMatchesSelection($priceProfileId, $materialIds, $productionMethodIds)) {
            $GLOBALS['messageStack']->add_session('Das ausgewählte Preisprofil passt nicht zu den gewählten Materialien und Fertigungsarten.', 'error');
            return $this->redirectToList($id);
        }

        if ($id > 0) {
            xtc_db_query(
                "UPDATE `oli_lc_product_templates` SET " .
                "`name` = '" . xtc_db_input($name) . "', " .
                "`description` = '" . xtc_db_input($description) . "', " .
                "`is_active` = " . $isActive . ", " .
                "`sort_order` = " . $sortOrder . ", " .
                "`color_mode` = '" . xtc_db_input($colorMode) . "', " .
                "`thickness_mode` = '" . xtc_db_input($thicknessMode) . "', `price_profile_id` = " . ($priceProfileId > 0 ? $priceProfileId : "NULL") . " " .
                "WHERE `product_template_id` = " . $id . " LIMIT 1"
            );
            $message = 'Produktvorlage wurde aktualisiert.';
        } else {
            xtc_db_query(
                "INSERT INTO `oli_lc_product_templates` " .
                "(`name`, `description`, `is_active`, `sort_order`, `color_mode`, `thickness_mode`, `price_profile_id`) VALUES (" .
                "'" . xtc_db_input($name) . "', " .
                "'" . xtc_db_input($description) . "', " .
                $isActive . ', ' . $sortOrder . ", '" . xtc_db_input($colorMode) . "', '" . xtc_db_input($thicknessMode) . "', " . ($priceProfileId > 0 ? $priceProfileId : 'NULL') . ")"
            );
            $id = (int)xtc_db_insert_id();
            $message = 'Produktvorlage wurde angelegt.';
        }

        if ($id > 0) {
            $assignmentTable = xtc_db_query("SHOW TABLES LIKE 'oli_lc_product_assignments'");
            if (xtc_db_num_rows($assignmentTable) > 0) {
                $assignmentResult = xtc_db_query("SELECT `assignment_id` FROM `oli_lc_product_assignments` WHERE `product_template_id` = " . $id . " LIMIT 1");
                if (xtc_db_num_rows($assignmentResult) > 0) {
                    $GLOBALS['messageStack']->add_session('Diese Produktvorlage ist einem oder mehreren Gambio-Artikeln zugeordnet und kann nicht gelöscht werden. Entfernen Sie zuerst die Zuordnungen unter Konfigurationen.', 'error');
                    return $this->redirectToList($id);
                }
            }
            xtc_db_query("DELETE FROM `oli_lc_product_template_materials` WHERE `product_template_id` = " . $id);
            $assignmentSort = 0;
            foreach ($materialIds as $materialId) {
                $assignmentSort += 10;
                xtc_db_query(
                    "INSERT INTO `oli_lc_product_template_materials` " .
                    "(`product_template_id`, `material_id`, `sort_order`) VALUES (" .
                    $id . ', ' . $materialId . ', ' . $assignmentSort . ')'
                );
            }

            xtc_db_query("DELETE FROM `oli_lc_product_template_production_methods` WHERE `product_template_id` = " . $id);
            $methodSort = 0;
            foreach ($productionMethodIds as $productionMethodId) {
                $methodSort += 10;
                xtc_db_query(
                    "INSERT INTO `oli_lc_product_template_production_methods` " .
                    "(`product_template_id`, `production_method_id`, `sort_order`) VALUES (" .
                    $id . ', ' . $productionMethodId . ', ' . $methodSort . ')'
                );
            }

            xtc_db_query("DELETE FROM `oli_lc_product_template_colors` WHERE `product_template_id` = " . $id);
            $colorSort = 0;
            foreach ($colorIds as $colorId) {
                $colorSort += 10;
                xtc_db_query(
                    "INSERT INTO `oli_lc_product_template_colors` " .
                    "(`product_template_id`, `color_id`, `sort_order`) VALUES (" .
                    $id . ', ' . $colorId . ', ' . $colorSort . ')'
                );
            }


            xtc_db_query("DELETE FROM `oli_lc_product_template_thicknesses` WHERE `product_template_id` = " . $id);
            $thicknessSort = 0;
            foreach ($thicknessIds as $thicknessId) {
                $thicknessSort += 10;
                xtc_db_query(
                    "INSERT INTO `oli_lc_product_template_thicknesses` " .
                    "(`product_template_id`, `thickness_id`, `sort_order`) VALUES (" .
                    $id . ', ' . $thicknessId . ', ' . $thicknessSort . ')'
                );
            }
        }

        $GLOBALS['messageStack']->add_session($message, 'info');
        return $this->redirectToList();
    }

    public function actionToggle()
    {
        $this->_validatePageToken();
        $this->ensureSchema();
        $id = (int)$this->_getPostData('product_template_id');

        if ($id > 0) {
            xtc_db_query(
                "UPDATE `oli_lc_product_templates` SET `is_active` = IF(`is_active` = 1, 0, 1) " .
                "WHERE `product_template_id` = " . $id . " LIMIT 1"
            );
            $GLOBALS['messageStack']->add_session('Produktstatus wurde geändert.', 'info');
        }

        return $this->redirectToList();
    }

    public function actionDelete()
    {
        $this->_validatePageToken();
        $this->ensureSchema();
        $id = (int)$this->_getPostData('product_template_id');

        if ($id > 0) {
            xtc_db_query("DELETE FROM `oli_lc_product_template_materials` WHERE `product_template_id` = " . $id);
            xtc_db_query("DELETE FROM `oli_lc_product_template_production_methods` WHERE `product_template_id` = " . $id);
            xtc_db_query("DELETE FROM `oli_lc_product_template_colors` WHERE `product_template_id` = " . $id);
            xtc_db_query("DELETE FROM `oli_lc_product_template_thicknesses` WHERE `product_template_id` = " . $id);
            xtc_db_query("DELETE FROM `oli_lc_product_templates` WHERE `product_template_id` = " . $id . " LIMIT 1");
            $GLOBALS['messageStack']->add_session('Produktvorlage wurde gelöscht.', 'info');
        }

        return $this->redirectToList();
    }

    private function ensureSchema()
    {
        xtc_db_query(
            "CREATE TABLE IF NOT EXISTS `oli_lc_product_templates` (" .
            "`product_template_id` INT UNSIGNED NOT NULL AUTO_INCREMENT," .
            "`name` VARCHAR(150) NOT NULL," .
            "`description` TEXT NULL," .
            "`is_active` TINYINT(1) NOT NULL DEFAULT 1," .
            "`sort_order` INT NOT NULL DEFAULT 0," .
            "`color_mode` ENUM('all','selected') NOT NULL DEFAULT 'all',`thickness_mode` ENUM('all','selected') NOT NULL DEFAULT 'all',`price_profile_id` INT UNSIGNED NULL," .
            "`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," .
            "`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP," .
            "PRIMARY KEY (`product_template_id`)," .
            "KEY `idx_oli_lc_product_template_active_sort` (`is_active`, `sort_order`)" .
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $colorModeColumn = xtc_db_query("SHOW COLUMNS FROM `oli_lc_product_templates` LIKE 'color_mode'");
        if (xtc_db_num_rows($colorModeColumn) === 0) {
            xtc_db_query(
                "ALTER TABLE `oli_lc_product_templates` " .
                "ADD `color_mode` ENUM('all','selected') NOT NULL DEFAULT 'all' AFTER `sort_order`"
            );
        }


        $thicknessModeColumn = xtc_db_query("SHOW COLUMNS FROM `oli_lc_product_templates` LIKE 'thickness_mode'");
        if (xtc_db_num_rows($thicknessModeColumn) === 0) {
            xtc_db_query(
                "ALTER TABLE `oli_lc_product_templates` " .
                "ADD `thickness_mode` ENUM('all','selected') NOT NULL DEFAULT 'all' AFTER `color_mode`"
            );
        }

        $priceProfileColumn = xtc_db_query("SHOW COLUMNS FROM `oli_lc_product_templates` LIKE 'price_profile_id'");
        if (xtc_db_num_rows($priceProfileColumn) === 0) {
            xtc_db_query(
                "ALTER TABLE `oli_lc_product_templates` " .
                "ADD `price_profile_id` INT UNSIGNED NULL AFTER `thickness_mode`"
            );
        }

        xtc_db_query(
            "CREATE TABLE IF NOT EXISTS `oli_lc_product_template_materials` (" .
            "`product_template_id` INT UNSIGNED NOT NULL," .
            "`material_id` INT UNSIGNED NOT NULL," .
            "`sort_order` INT NOT NULL DEFAULT 0," .
            "`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," .
            "PRIMARY KEY (`product_template_id`, `material_id`)," .
            "KEY `idx_oli_lc_product_template_material_material` (`material_id`)," .
            "KEY `idx_oli_lc_product_template_material_sort` (`product_template_id`, `sort_order`)" .
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        xtc_db_query(
            "CREATE TABLE IF NOT EXISTS `oli_lc_product_template_production_methods` (" .
            "`product_template_id` INT UNSIGNED NOT NULL," .
            "`production_method_id` INT UNSIGNED NOT NULL," .
            "`sort_order` INT NOT NULL DEFAULT 0," .
            "`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," .
            "PRIMARY KEY (`product_template_id`, `production_method_id`)," .
            "KEY `idx_oli_lc_product_template_method_method` (`production_method_id`)," .
            "KEY `idx_oli_lc_product_template_method_sort` (`product_template_id`, `sort_order`)" .
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        xtc_db_query(
            "CREATE TABLE IF NOT EXISTS `oli_lc_product_template_colors` (" .
            "`product_template_id` INT UNSIGNED NOT NULL," .
            "`color_id` INT UNSIGNED NOT NULL," .
            "`sort_order` INT NOT NULL DEFAULT 0," .
            "`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," .
            "PRIMARY KEY (`product_template_id`, `color_id`)," .
            "KEY `idx_oli_lc_product_template_color_color` (`color_id`)," .
            "KEY `idx_oli_lc_product_template_color_sort` (`product_template_id`, `sort_order`)" .
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );


        xtc_db_query(
            "CREATE TABLE IF NOT EXISTS `oli_lc_product_template_thicknesses` (" .
            "`product_template_id` INT UNSIGNED NOT NULL," .
            "`thickness_id` INT UNSIGNED NOT NULL," .
            "`sort_order` INT NOT NULL DEFAULT 0," .
            "`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," .
            "PRIMARY KEY (`product_template_id`, `thickness_id`)," .
            "KEY `idx_oli_lc_product_template_thickness_thickness` (`thickness_id`)," .
            "KEY `idx_oli_lc_product_template_thickness_sort` (`product_template_id`, `sort_order`)" .
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function filterColorsByMaterials(array $colorIds, array $materialIds)
    {
        if (empty($colorIds) || empty($materialIds)) {
            return [];
        }

        $result = xtc_db_query(
            "SELECT `color_id` FROM `oli_lc_colors` WHERE `is_active` = 1 " .
            "AND `color_id` IN (" . implode(',', array_map('intval', $colorIds)) . ") " .
            "AND `material_id` IN (" . implode(',', array_map('intval', $materialIds)) . ")"
        );
        $validIds = [];
        while ($row = xtc_db_fetch_array($result)) {
            $validIds[] = (int)$row['color_id'];
        }
        return $validIds;
    }

    private function filterThicknessesBySelection(array $thicknessIds, array $materialIds, array $productionMethodIds)
    {
        if (empty($thicknessIds) || empty($materialIds) || empty($productionMethodIds)) {
            return [];
        }

        $thicknessSql = implode(',', array_map('intval', $thicknessIds));
        $materialSql = implode(',', array_map('intval', $materialIds));
        $methodSql = implode(',', array_map('intval', $productionMethodIds));
        $validIds = [];
        $result = xtc_db_query(
            "SELECT t.`thickness_id` FROM `oli_lc_thicknesses` t " .
            "INNER JOIN `oli_lc_production_methods` pm ON pm.`method_key` = t.`production_method` " .
            "WHERE t.`thickness_id` IN (" . $thicknessSql . ") " .
            "AND t.`material_id` IN (" . $materialSql . ") " .
            "AND pm.`production_method_id` IN (" . $methodSql . ") " .
            "AND t.`is_active` = 1 AND pm.`is_active` = 1"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $validIds[] = (int)$row['thickness_id'];
        }
        return $validIds;
    }

    private function priceProfileMatchesSelection($priceProfileId, array $materialIds, array $productionMethodIds)
    {
        if ($priceProfileId <= 0 || empty($materialIds) || empty($productionMethodIds)) {
            return false;
        }
        $result = xtc_db_query(
            "SELECT `configuration_json`, `is_active` FROM `oli_lc_price_profiles` " .
            "WHERE `price_profile_id` = " . (int)$priceProfileId . " LIMIT 1"
        );
        if (!($row = xtc_db_fetch_array($result)) || (int)$row['is_active'] !== 1) {
            return false;
        }
        $configuration = json_decode((string)$row['configuration_json'], true);
        if (!is_array($configuration)) {
            return false;
        }
        $materialId = (int)($configuration['material_id'] ?? 0);
        $methodKey = (string)($configuration['production_method'] ?? '');
        if (!in_array($materialId, $materialIds, true) || $methodKey === '') {
            return false;
        }
        $methodResult = xtc_db_query(
            "SELECT `production_method_id` FROM `oli_lc_production_methods` " .
            "WHERE `method_key` = '" . xtc_db_input($methodKey) . "' LIMIT 1"
        );
        if (!($method = xtc_db_fetch_array($methodResult))) {
            return false;
        }
        return in_array((int)$method['production_method_id'], $productionMethodIds, true);
    }

    private function formatMeasurement($value)
    {
        $formatted = number_format((float)$value, 3, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');
        return str_replace('.', ',', $formatted);
    }

    private function normalizeIdList($values)
    {
        if (!is_array($values)) {
            return [];
        }

        $ids = [];
        foreach ($values as $value) {
            $id = (int)$value;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    private function redirectToList($editId = 0)
    {
        $params = 'do=LetterConfiguratorProducts';
        if ((int)$editId > 0) {
            $params .= '&edit_id=' . (int)$editId;
        }

        return MainFactory::create(
            'RedirectHttpControllerResponse',
            xtc_href_link('admin.php', $params)
        );
    }
}

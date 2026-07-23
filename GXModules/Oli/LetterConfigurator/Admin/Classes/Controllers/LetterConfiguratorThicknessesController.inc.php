<?php
/* --------------------------------------------------------------
   LetterConfiguratorThicknessesController.inc.php
   Phase M3.11 – thickness ranges and production methods
   Provider: Oli
   --------------------------------------------------------------
*/

class LetterConfiguratorThicknessesController extends AdminHttpViewController
{
    public function actionDefault()
    {
        $this->ensureAssignmentSchema();

        $title = new NonEmptyStringType('Buchstaben-Konfigurator – Materialstärken');
        $template = $this->getTemplateFile(
            'GXModules/Oli/LetterConfigurator/Admin/Html/letter_configurator_thicknesses.html'
        );

        $editId = (int)$this->_getQueryParameter('edit_id');
        $editThickness = [
            'thickness_id' => 0,
            'material_id' => 0,
            'production_method' => 'cnc_fraesen',
            'thickness_mm' => '',
            'thickness_min_mm' => '',
            'thickness_max_mm' => '',
            'price_surcharge' => '0.0000',
            'is_active' => 1,
            'sort_order' => 0,
            'all_colors' => 1,
            'selected_color_ids' => [],
        ];

        if ($editId > 0) {
            $result = xtc_db_query(
                "SELECT `thickness_id`, `material_id`, `production_method`, `thickness_mm`, `thickness_min_mm`, `thickness_max_mm`, `price_surcharge`, `is_active`, `sort_order` " .
                "FROM `oli_lc_thicknesses` WHERE `thickness_id` = " . $editId . " LIMIT 1"
            );
            if ($row = xtc_db_fetch_array($result)) {
                $editThickness = array_merge($editThickness, $row);
                $editThickness['selected_color_ids'] = $this->getAssignedColorIds($editId);
                $editThickness['all_colors'] = empty($editThickness['selected_color_ids']) ? 1 : 0;
            }
        }

        $materials = [];
        $materialResult = xtc_db_query(
            "SELECT `material_id`, `name`, `code`, `is_active` FROM `oli_lc_materials` " .
            "ORDER BY `sort_order` ASC, `name` ASC"
        );
        while ($row = xtc_db_fetch_array($materialResult)) {
            $materials[] = $row;
        }

        $colors = [];
        $colorResult = xtc_db_query(
            "SELECT c.`color_id`, c.`material_id`, c.`name`, c.`code`, c.`hex_value`, c.`is_active`, " .
            "m.`name` AS `material_name` FROM `oli_lc_colors` c " .
            "LEFT JOIN `oli_lc_materials` m ON m.`material_id` = c.`material_id` " .
            "ORDER BY m.`sort_order` ASC, m.`name` ASC, c.`sort_order` ASC, c.`name` ASC"
        );
        while ($row = xtc_db_fetch_array($colorResult)) {
            $row['is_selected'] = in_array((int)$row['color_id'], array_map('intval', $editThickness['selected_color_ids']), true) ? 1 : 0;
            $colors[] = $row;
        }

        $productionMethods = [];
        $methodResult = xtc_db_query("SELECT `name`,`method_key`,`engine_key`,`range_mode`,`is_active` FROM `oli_lc_production_methods` ORDER BY `sort_order`,`name`");
        while ($row = xtc_db_fetch_array($methodResult)) { $productionMethods[] = $row; }
        $methodMap = [];
        foreach ($productionMethods as $method) { $methodMap[(string)$method['method_key']] = $method; }

        $thicknesses = [];
        $result = xtc_db_query(
            "SELECT t.`thickness_id`, t.`material_id`, t.`production_method`, t.`thickness_mm`, t.`thickness_min_mm`, t.`thickness_max_mm`, " .
            "t.`price_surcharge`, t.`is_active`, t.`sort_order`, t.`updated_at`, " .
            "m.`name` AS `material_name`, m.`code` AS `material_code`, " .
            "GROUP_CONCAT(c.`name` ORDER BY c.`sort_order` ASC, c.`name` ASC SEPARATOR ', ') AS `color_names`, " .
            "COUNT(tc.`color_id`) AS `color_count` " .
            "FROM `oli_lc_thicknesses` t " .
            "LEFT JOIN `oli_lc_materials` m ON m.`material_id` = t.`material_id` " .
            "LEFT JOIN `oli_lc_thickness_colors` tc ON tc.`thickness_id` = t.`thickness_id` " .
            "LEFT JOIN `oli_lc_colors` c ON c.`color_id` = tc.`color_id` " .
            "GROUP BY t.`thickness_id`, t.`material_id`, t.`production_method`, t.`thickness_mm`, t.`thickness_min_mm`, t.`thickness_max_mm`, t.`price_surcharge`, t.`is_active`, " .
            "t.`sort_order`, t.`updated_at`, m.`name`, m.`code` " .
            "ORDER BY t.`sort_order` ASC, m.`name` ASC, t.`thickness_min_mm` ASC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $row['production_method_label'] = isset($methodMap[(string)$row['production_method']]) ? $methodMap[(string)$row['production_method']]['name'] : $row['production_method'];
            $row['thickness_min_display'] = $this->formatMeasurement($row['thickness_min_mm']);
            $row['thickness_max_display'] = $this->formatMeasurement($row['thickness_max_mm']);
            $row['edit_url'] = xtc_href_link(
                'admin.php',
                'do=LetterConfiguratorThicknesses&edit_id=' . (int)$row['thickness_id']
            );
            $thicknesses[] = $row;
        }

        $data = MainFactory::create('KeyValueCollection', [
            'pageToken' => $_SESSION['coo_page_token']->generate_token(),
            'thicknesses' => $thicknesses,
            'materials' => $materials,
            'colors' => $colors,
            'productionMethods' => $productionMethods,
            'editThickness' => $editThickness,
            'action_save' => xtc_href_link('admin.php', 'do=LetterConfiguratorThicknesses/Save'),
            'action_toggle' => xtc_href_link('admin.php', 'do=LetterConfiguratorThicknesses/Toggle'),
            'action_cancel' => xtc_href_link('admin.php', 'do=LetterConfiguratorThicknesses'),
        ]);

        return MainFactory::create('AdminLayoutHttpControllerResponse', $title, $template, $data);
    }

    public function actionSave()
    {
        $this->_validatePageToken();
        $this->ensureAssignmentSchema();

        $thickness = (array)$this->_getPostData('thickness');
        $id = isset($thickness['thickness_id']) ? (int)$thickness['thickness_id'] : 0;
        $materialId = isset($thickness['material_id']) ? (int)$thickness['material_id'] : 0;
        $allColors = !empty($thickness['all_colors']);
        $selectedColorIds = $allColors ? [] : $this->normalizeIds($thickness['color_ids'] ?? []);
        $productionMethod = (string)($thickness['production_method'] ?? 'cnc_fraesen');
        $methodResult = xtc_db_query("SELECT `range_mode` FROM `oli_lc_production_methods` WHERE `method_key`='" . xtc_db_input($productionMethod) . "' LIMIT 1");
        if (!($methodRow = xtc_db_fetch_array($methodResult))) { $GLOBALS['messageStack']->add_session('Die ausgewählte Fertigungsart existiert nicht.', 'error'); return $this->redirectToList($id); }
        $isSingleMode = (string)$methodRow['range_mode'] === 'single';
        $minInput = str_replace(',', '.', trim((string)($thickness['thickness_min_mm'] ?? $thickness['thickness_mm'] ?? '')));
        $maxInput = $isSingleMode ? $minInput : str_replace(',', '.', trim((string)($thickness['thickness_max_mm'] ?? $minInput)));
        $sortOrder = isset($thickness['sort_order']) ? (int)$thickness['sort_order'] : 0;
        $isActive = !empty($thickness['is_active']) ? 1 : 0;

        if ($materialId <= 0 || $minInput === '' || $maxInput === '') {
            $GLOBALS['messageStack']->add_session('Material und Stärke sind Pflichtfelder.', 'error');
            return $this->redirectToList($id);
        }
        if (!is_numeric($minInput) || !is_numeric($maxInput) || (float)$minInput <= 0 || (float)$maxInput <= 0) {
            $GLOBALS['messageStack']->add_session('Die Materialstärken müssen größer als 0 mm sein.', 'error');
            return $this->redirectToList($id);
        }
        if ((float)$minInput > (float)$maxInput) {
            $GLOBALS['messageStack']->add_session('Die maximale Stärke muss mindestens so groß wie die minimale Stärke sein.', 'error');
            return $this->redirectToList($id);
        }
        if ((float)$maxInput > 200) {
            $GLOBALS['messageStack']->add_session('Die maximale Stärke darf 200 mm nicht überschreiten.', 'error');
            return $this->redirectToList($id);
        }
        // Preisaufschläge werden seit M3.12.2 ausschließlich über Preisprofile gepflegt.
        // Die bestehende Datenbankspalte wird weiter mit 0 befüllt.
        if (!$allColors && empty($selectedColorIds)) {
            $GLOBALS['messageStack']->add_session('Bitte mindestens eine Farbe auswählen oder „Für alle Farben“ aktivieren.', 'error');
            return $this->redirectToList($id);
        }

        $thicknessMinMm = number_format((float)$minInput, 3, '.', '');
        $thicknessMaxMm = number_format((float)$maxInput, 3, '.', '');
        $thicknessMm = $thicknessMinMm;
        $priceSurcharge = '0.0000';

        $materialResult = xtc_db_query(
            "SELECT `material_id` FROM `oli_lc_materials` WHERE `material_id` = " . $materialId . " LIMIT 1"
        );
        if (xtc_db_num_rows($materialResult) === 0) {
            $GLOBALS['messageStack']->add_session('Das ausgewählte Material existiert nicht.', 'error');
            return $this->redirectToList($id);
        }

        if (!$this->colorsBelongToMaterial($selectedColorIds, $materialId)) {
            $GLOBALS['messageStack']->add_session('Mindestens eine ausgewählte Farbe gehört nicht zum gewählten Material.', 'error');
            return $this->redirectToList($id);
        }

        if ($isActive && $this->hasActiveOverlap($id, $materialId, $productionMethod, $thicknessMinMm, $thicknessMaxMm, $selectedColorIds, $allColors)) {
            $GLOBALS['messageStack']->add_session(
                'Für mindestens eine ausgewählte Farbe existiert bereits eine aktive Preisgruppe mit einem überlappenden Stärkenbereich.',
                'error'
            );
            return $this->redirectToList($id);
        }

        $representativeColorSql = empty($selectedColorIds) ? 'NULL' : (string)min($selectedColorIds);
        xtc_db_query('START TRANSACTION');

        if ($id > 0) {
            xtc_db_query(
                "UPDATE `oli_lc_thicknesses` SET " .
                "`material_id` = " . $materialId . ", " .
                "`production_method` = '" . xtc_db_input($productionMethod) . "', " .
                "`color_id` = " . $representativeColorSql . ", " .
                "`thickness_mm` = " . $thicknessMm . ", " .
                "`thickness_min_mm` = " . $thicknessMinMm . ", " .
                "`thickness_max_mm` = " . $thicknessMaxMm . ", " .
                "`price_surcharge` = " . $priceSurcharge . ", " .
                "`is_active` = " . $isActive . ", " .
                "`sort_order` = " . $sortOrder . " " .
                "WHERE `thickness_id` = " . $id . " LIMIT 1"
            );
            $savedId = $id;
            $message = 'Materialstärke wurde aktualisiert.';
        } else {
            xtc_db_query(
                "INSERT INTO `oli_lc_thicknesses` " .
                "(`material_id`, `production_method`, `color_id`, `thickness_mm`, `thickness_min_mm`, `thickness_max_mm`, `price_surcharge`, `is_active`, `sort_order`) VALUES (" .
                $materialId . ", '" . xtc_db_input($productionMethod) . "', " . $representativeColorSql . ', ' . $thicknessMm . ', ' .
                $thicknessMinMm . ', ' . $thicknessMaxMm . ', ' . $priceSurcharge . ', ' . $isActive . ', ' . $sortOrder . ')'
            );
            $savedId = (int)xtc_db_insert_id();
            $message = 'Materialstärke wurde angelegt.';
        }

        xtc_db_query("DELETE FROM `oli_lc_thickness_colors` WHERE `thickness_id` = " . $savedId);
        foreach ($selectedColorIds as $colorId) {
            xtc_db_query(
                "INSERT INTO `oli_lc_thickness_colors` (`thickness_id`, `color_id`) VALUES (" .
                $savedId . ', ' . (int)$colorId . ')'
            );
        }
        xtc_db_query('COMMIT');

        $GLOBALS['messageStack']->add_session($message, 'info');
        return $this->redirectToList();
    }

    public function actionToggle()
    {
        $this->_validatePageToken();
        $this->ensureAssignmentSchema();
        $id = (int)$this->_getPostData('thickness_id');

        if ($id > 0) {
            $result = xtc_db_query(
                "SELECT `material_id`, `production_method`, `thickness_min_mm`, `thickness_max_mm`, `is_active` FROM `oli_lc_thicknesses` " .
                "WHERE `thickness_id` = " . $id . " LIMIT 1"
            );
            if ($row = xtc_db_fetch_array($result)) {
                $newStatus = (int)$row['is_active'] === 1 ? 0 : 1;
                if ($newStatus === 1) {
                    $colorIds = $this->getAssignedColorIds($id);
                    $allColors = empty($colorIds);
                    if ($this->hasActiveOverlap($id, (int)$row['material_id'], (string)$row['production_method'], (string)$row['thickness_min_mm'], (string)$row['thickness_max_mm'], $colorIds, $allColors)) {
                        $GLOBALS['messageStack']->add_session(
                            'Aktivierung nicht möglich: Die Farbzuordnung überschneidet sich mit einer bereits aktiven Preisgruppe.',
                            'error'
                        );
                        return $this->redirectToList();
                    }
                }
                xtc_db_query(
                    "UPDATE `oli_lc_thicknesses` SET `is_active` = " . $newStatus .
                    " WHERE `thickness_id` = " . $id . " LIMIT 1"
                );
                $GLOBALS['messageStack']->add_session('Status der Materialstärke wurde geändert.', 'info');
            }
        }

        return $this->redirectToList();
    }

    private function ensureAssignmentSchema()
    {
        xtc_db_query("CREATE TABLE IF NOT EXISTS `oli_lc_production_methods` (`production_method_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`name` VARCHAR(120) NOT NULL,`method_key` VARCHAR(64) NULL,`engine_key` VARCHAR(32) NOT NULL,`range_mode` ENUM('single','range') NOT NULL DEFAULT 'single',`machine_name` VARCHAR(160) NOT NULL DEFAULT '',`machine_cost_per_hour` DECIMAL(12,4) NOT NULL DEFAULT 0,`is_active` TINYINT(1) NOT NULL DEFAULT 1,`sort_order` INT NOT NULL DEFAULT 0,PRIMARY KEY (`production_method_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pmColumns = [];
        $pmResult = xtc_db_query("SHOW COLUMNS FROM `oli_lc_production_methods`");
        while ($pmColumn = xtc_db_fetch_array($pmResult)) { $pmColumns[(string)$pmColumn['Field']] = true; }
        if (!isset($pmColumns['method_key'])) { xtc_db_query("ALTER TABLE `oli_lc_production_methods` ADD `method_key` VARCHAR(64) NULL AFTER `name`"); }
        if (!isset($pmColumns['machine_name'])) { xtc_db_query("ALTER TABLE `oli_lc_production_methods` ADD `machine_name` VARCHAR(160) NOT NULL DEFAULT '' AFTER `range_mode`"); }
        if (!isset($pmColumns['machine_cost_per_hour'])) { xtc_db_query("ALTER TABLE `oli_lc_production_methods` ADD `machine_cost_per_hour` DECIMAL(12,4) NOT NULL DEFAULT 0 AFTER `machine_name`"); }
        $oldEngineIndex = xtc_db_query("SHOW INDEX FROM `oli_lc_production_methods` WHERE `Key_name`='uq_oli_lc_production_engine'");
        if (xtc_db_num_rows($oldEngineIndex) > 0) { xtc_db_query("ALTER TABLE `oli_lc_production_methods` DROP INDEX `uq_oli_lc_production_engine`"); }
        xtc_db_query("UPDATE `oli_lc_production_methods` SET `method_key`=`engine_key` WHERE (`method_key` IS NULL OR `method_key`='')");
        xtc_db_query("UPDATE `oli_lc_production_methods` SET `name`='CNC-Fräsen',`method_key`='cnc_fraesen',`engine_key`='contour',`range_mode`='single',`sort_order`=10 WHERE `method_key`='contour_cut' OR `engine_key`='contour_cut' OR `name`='Konturgeschnitten'");
        xtc_db_query("UPDATE `oli_lc_production_methods` SET `engine_key`='print3d' WHERE `method_key`='3d_print' AND `engine_key`='3d_print'");
        xtc_db_query("UPDATE `oli_lc_thicknesses` SET `production_method`='cnc_fraesen' WHERE `production_method`='contour_cut'");
        xtc_db_query("UPDATE `oli_lc_price_profiles` SET `configuration_json`=REPLACE(`configuration_json`, '\"production_method\":\"contour_cut\"', '\"production_method\":\"cnc_fraesen\"') WHERE `configuration_json` LIKE '%contour_cut%'");
        xtc_db_query("INSERT IGNORE INTO `oli_lc_production_methods` (`name`,`method_key`,`engine_key`,`range_mode`,`sort_order`) VALUES ('CNC-Fräsen','cnc_fraesen','contour','single',10),('Laserschneiden','laserschneiden','contour','single',20),('Plotten','plotten','contour','single',30),('3D-Druck','3d_print','print3d','range',40),('Sonstige','generic','generic','range',50)");
        $methodKeyIndex = xtc_db_query("SHOW INDEX FROM `oli_lc_production_methods` WHERE `Key_name`='uq_oli_lc_method_key'");
        if (xtc_db_num_rows($methodKeyIndex) === 0) { xtc_db_query("ALTER TABLE `oli_lc_production_methods` MODIFY `method_key` VARCHAR(64) NOT NULL, ADD UNIQUE KEY `uq_oli_lc_method_key` (`method_key`)"); }
        xtc_db_query(
            "CREATE TABLE IF NOT EXISTS `oli_lc_thickness_colors` (" .
            "`thickness_id` INT UNSIGNED NOT NULL, " .
            "`color_id` INT UNSIGNED NOT NULL, " .
            "`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, " .
            "PRIMARY KEY (`thickness_id`, `color_id`), " .
            "KEY `idx_oli_lc_thickness_colors_color` (`color_id`)" .
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $columns = [];
        $columnResult = xtc_db_query("SHOW COLUMNS FROM `oli_lc_thicknesses`");
        while ($column = xtc_db_fetch_array($columnResult)) {
            $columns[(string)$column['Field']] = true;
        }
        if (!isset($columns['production_method'])) {
            xtc_db_query("ALTER TABLE `oli_lc_thicknesses` ADD `production_method` VARCHAR(32) NOT NULL DEFAULT 'cnc_fraesen' AFTER `material_id`");
        }
        if (!isset($columns['thickness_min_mm'])) {
            xtc_db_query("ALTER TABLE `oli_lc_thicknesses` ADD `thickness_min_mm` DECIMAL(10,3) NULL AFTER `thickness_mm`");
        }
        if (!isset($columns['thickness_max_mm'])) {
            xtc_db_query("ALTER TABLE `oli_lc_thicknesses` ADD `thickness_max_mm` DECIMAL(10,3) NULL AFTER `thickness_min_mm`");
        }
        xtc_db_query("UPDATE `oli_lc_thicknesses` SET `thickness_min_mm` = `thickness_mm` WHERE `thickness_min_mm` IS NULL");
        xtc_db_query("UPDATE `oli_lc_thicknesses` SET `thickness_max_mm` = `thickness_mm` WHERE `thickness_max_mm` IS NULL");

        $indexResult = xtc_db_query("SHOW INDEX FROM `oli_lc_thicknesses` WHERE `Key_name` = 'uq_oli_lc_thickness_scope'");
        if (xtc_db_num_rows($indexResult) > 0) {
            xtc_db_query("ALTER TABLE `oli_lc_thicknesses` DROP INDEX `uq_oli_lc_thickness_scope`");
        }
        $scopeIndex = xtc_db_query("SHOW INDEX FROM `oli_lc_thicknesses` WHERE `Key_name` = 'idx_oli_lc_thickness_scope'");
        if (xtc_db_num_rows($scopeIndex) === 0) {
            xtc_db_query("ALTER TABLE `oli_lc_thicknesses` ADD KEY `idx_oli_lc_thickness_scope` (`material_id`, `thickness_mm`)");
        }

        xtc_db_query(
            "INSERT INTO `oli_lc_settings` (`setting_key`, `setting_value`) " .
            "VALUES ('schema_version', '3.12-production-methods') " .
            "ON DUPLICATE KEY UPDATE `setting_value` = '3.12-production-methods', `updated_at` = CURRENT_TIMESTAMP"
        );

        $legacy = xtc_db_query(
            "SELECT `thickness_id`, `color_id` FROM `oli_lc_thicknesses` WHERE `color_id` IS NOT NULL"
        );
        while ($row = xtc_db_fetch_array($legacy)) {
            xtc_db_query(
                "INSERT IGNORE INTO `oli_lc_thickness_colors` (`thickness_id`, `color_id`) VALUES (" .
                (int)$row['thickness_id'] . ', ' . (int)$row['color_id'] . ')'
            );
        }
    }

    private function normalizeIds($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static function ($id) {
            return $id > 0;
        })));
        sort($ids, SORT_NUMERIC);
        return $ids;
    }

    private function getAssignedColorIds($thicknessId)
    {
        $ids = [];
        $result = xtc_db_query(
            "SELECT `color_id` FROM `oli_lc_thickness_colors` WHERE `thickness_id` = " . (int)$thicknessId .
            " ORDER BY `color_id` ASC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $ids[] = (int)$row['color_id'];
        }
        return $ids;
    }

    private function colorsBelongToMaterial(array $colorIds, $materialId)
    {
        if (empty($colorIds)) {
            return true;
        }
        $result = xtc_db_query(
            "SELECT COUNT(*) AS `count_colors` FROM `oli_lc_colors` WHERE `material_id` = " . (int)$materialId .
            " AND `color_id` IN (" . implode(',', array_map('intval', $colorIds)) . ')'
        );
        $row = xtc_db_fetch_array($result);
        return (int)$row['count_colors'] === count($colorIds);
    }

    private function hasActiveOverlap($currentId, $materialId, $productionMethod, $thicknessMinMm, $thicknessMaxMm, array $colorIds, $allColors)
    {
        $base = "t.`material_id` = " . (int)$materialId .
            " AND t.`production_method` = '" . xtc_db_input($productionMethod) . "'" .
            " AND t.`thickness_min_mm` <= " . number_format((float)$thicknessMaxMm, 3, '.', '') .
            " AND t.`thickness_max_mm` >= " . number_format((float)$thicknessMinMm, 3, '.', '') .
            " AND t.`is_active` = 1";
        if ((int)$currentId > 0) {
            $base .= " AND t.`thickness_id` <> " . (int)$currentId;
        }

        if ($allColors) {
            $sql = "SELECT t.`thickness_id` FROM `oli_lc_thicknesses` t WHERE " . $base . " LIMIT 1";
        } else {
            $sql = "SELECT t.`thickness_id` FROM `oli_lc_thicknesses` t " .
                "LEFT JOIN `oli_lc_thickness_colors` tc ON tc.`thickness_id` = t.`thickness_id` " .
                "WHERE " . $base . " AND (" .
                "NOT EXISTS (SELECT 1 FROM `oli_lc_thickness_colors` alltc WHERE alltc.`thickness_id` = t.`thickness_id`) " .
                "OR tc.`color_id` IN (" . implode(',', array_map('intval', $colorIds)) . ")" .
                ") LIMIT 1";
        }
        $result = xtc_db_query($sql);
        return xtc_db_num_rows($result) > 0;
    }


    private function formatMeasurement($value)
    {
        $formatted = number_format((float)$value, 3, ',', '');
        return rtrim(rtrim($formatted, '0'), ',');
    }

    private function redirectToList($editId = 0)
    {
        $params = 'do=LetterConfiguratorThicknesses';
        if ((int)$editId > 0) {
            $params .= '&edit_id=' . (int)$editId;
        }
        return MainFactory::create('RedirectHttpControllerResponse', xtc_href_link('admin.php', $params));
    }
}

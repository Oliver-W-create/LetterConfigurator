<?php
/* --------------------------------------------------------------
   LetterConfiguratorPriceProfilesController.inc.php
   Phase M3.12.3 – contour price calculator and standardized tables
   Provider: Oli
   --------------------------------------------------------------
*/

class LetterConfiguratorPriceProfilesController extends AdminHttpViewController
{
    public function actionDefault()
    {
        $this->ensureProductionSchema();
        $title = new NonEmptyStringType('Buchstaben-Konfigurator – Preisprofile');
        $template = $this->getTemplateFile(
            'GXModules/Oli/LetterConfigurator/Admin/Html/letter_configurator_price_profiles.html'
        );

        $editId = (int)$this->_getQueryParameter('edit_id');
        $editProfile = $this->getEmptyProfile();
        if ($editId > 0) {
            $result = xtc_db_query(
                "SELECT `price_profile_id`, `name`, `code`, `version`, `calculation_mode`, `configuration_json`, `is_active` " .
                "FROM `oli_lc_price_profiles` WHERE `price_profile_id` = " . $editId . " LIMIT 1"
            );
            if ($row = xtc_db_fetch_array($result)) {
                $configuration = json_decode((string)$row['configuration_json'], true);
                if (!is_array($configuration)) {
                    $configuration = [];
                }
                $editProfile = array_merge($editProfile, $row, $configuration);
                // Backwards-compatible mapping of profiles created before M3.10.
                $legacyMode = (string)($row['calculation_mode'] ?? '');
                $legacyUnitPrice = (string)($configuration['unit_price'] ?? '0.0000');
                if ($legacyMode === 'area' && empty($configuration['area_price_per_m2'])) {
                    $editProfile['area_price_per_m2'] = $legacyUnitPrice;
                } elseif ($legacyMode === 'linear' && empty($configuration['contour_price_per_mm'])) {
                    $editProfile['contour_price_per_mm'] = $legacyUnitPrice;
                } elseif ($legacyMode === 'per_character' && empty($configuration['price_per_character'])) {
                    $editProfile['price_per_character'] = $legacyUnitPrice;
                } elseif ($legacyMode === 'fixed' && empty($configuration['fixed_price'])) {
                    $editProfile['fixed_price'] = $legacyUnitPrice;
                }
                $editProfile['calculation_mode'] = 'modular';
                $editProfile['selected_color_ids'] = $this->normalizeIds($configuration['selected_color_ids'] ?? []);
                $editProfile['selected_thickness_ids'] = $this->normalizeIds($configuration['selected_thickness_ids'] ?? []);
                $editProfile['production_method'] = (string)($configuration['production_method'] ?? 'cnc_fraesen');
                $editProfile['contour_price_per_m'] = number_format(((float)($editProfile['contour_price_per_mm'] ?? 0)) * 1000, 2, '.', '');
                foreach (['area_price_per_m2','machine_cost_per_hour','price_per_character','fixed_price','minimum_price','setup_fee'] as $moneyField) {
                    $editProfile[$moneyField] = number_format((float)($editProfile[$moneyField] ?? 0), 2, '.', '');
                }
                $editProfile['waste_percent'] = number_format((float)($editProfile['waste_percent'] ?? 0), 0, '.', '');
            }
        }

        $materials = [];
        $result = xtc_db_query(
            "SELECT `material_id`, `name`, `code`, `is_active` FROM `oli_lc_materials` " .
            "ORDER BY `sort_order` ASC, `name` ASC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $materials[] = $row;
        }

        $productionMethods = [];
        $methodResult = xtc_db_query("SELECT `name`,`method_key`,`engine_key`,`range_mode`,`machine_name`,`machine_cost_per_hour`,`is_active` FROM `oli_lc_production_methods` ORDER BY `sort_order`,`name`");
        while ($row = xtc_db_fetch_array($methodResult)) {
            $row['machine_cost_per_hour'] = number_format((float)$row['machine_cost_per_hour'], 2, '.', '');
            $productionMethods[] = $row;
        }
        $methodMap = [];
        foreach ($productionMethods as $method) { $methodMap[(string)$method['method_key']] = $method; }

        $colors = [];
        $result = xtc_db_query(
            "SELECT `color_id`, `material_id`, `name`, `code`, `hex_value`, `is_active` FROM `oli_lc_colors` " .
            "ORDER BY `material_id` ASC, `sort_order` ASC, `name` ASC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $row['is_selected'] = in_array((int)$row['color_id'], $editProfile['selected_color_ids'], true) ? 1 : 0;
            $colors[] = $row;
        }

        $thicknesses = [];
        $result = xtc_db_query(
            "SELECT `thickness_id`, `material_id`, `production_method`, `thickness_mm`, `thickness_min_mm`, `thickness_max_mm`, `price_surcharge`, `is_active` " .
            "FROM `oli_lc_thicknesses` ORDER BY `material_id` ASC, `sort_order` ASC, `thickness_min_mm` ASC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $row['is_selected'] = in_array((int)$row['thickness_id'], $editProfile['selected_thickness_ids'], true) ? 1 : 0;
            $row['thickness_min_display'] = $this->formatMeasurement($row['thickness_min_mm']);
            $row['thickness_max_display'] = $this->formatMeasurement($row['thickness_max_mm']);
            $thicknesses[] = $row;
        }

        $profiles = [];
        $result = xtc_db_query(
            "SELECT `price_profile_id`, `name`, `code`, `version`, `calculation_mode`, `configuration_json`, `is_active`, `updated_at` " .
            "FROM `oli_lc_price_profiles` ORDER BY `name` ASC, `version` DESC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $configuration = json_decode((string)$row['configuration_json'], true);
            if (!is_array($configuration)) {
                $configuration = [];
            }
            $row['material_name'] = $this->getMaterialName((int)($configuration['material_id'] ?? 0), $materials);
            $row['minimum_price'] = $configuration['minimum_price'] ?? '0.0000';
            $row['sort_order'] = (int)($configuration['sort_order'] ?? 0);
            $row['area_price_per_m2'] = $configuration['area_price_per_m2'] ?? (((string)$row['calculation_mode'] === 'area') ? ($configuration['unit_price'] ?? '0.0000') : '0.0000');
            $row['contour_price_per_mm'] = $configuration['contour_price_per_mm'] ?? (((string)$row['calculation_mode'] === 'linear') ? ($configuration['unit_price'] ?? '0.0000') : '0.0000');
            $row['contour_price_per_m'] = number_format(((float)$row['contour_price_per_mm']) * 1000, 2, ',', '.');
            $row['area_price_per_m2_display'] = number_format((float)$row['area_price_per_m2'], 2, ',', '.');
            $row['price_per_character'] = $configuration['price_per_character'] ?? (((string)$row['calculation_mode'] === 'per_character') ? ($configuration['unit_price'] ?? '0.0000') : '0.0000');
            $row['fixed_price'] = $configuration['fixed_price'] ?? (((string)$row['calculation_mode'] === 'fixed') ? ($configuration['unit_price'] ?? '0.0000') : '0.0000');
            $row['price_per_character_display'] = number_format((float)$row['price_per_character'], 2, ',', '.');
            $row['fixed_price_display'] = number_format((float)$row['fixed_price'], 2, ',', '.');
            $row['color_count'] = count($this->normalizeIds($configuration['selected_color_ids'] ?? []));
            $row['thickness_count'] = count($this->normalizeIds($configuration['selected_thickness_ids'] ?? []));
            $row['production_method'] = (string)($configuration['production_method'] ?? 'cnc_fraesen');
            $row['production_method_label'] = isset($methodMap[$row['production_method']]) ? $methodMap[$row['production_method']]['name'] : $row['production_method'];
            $row['mode_label'] = 'Modulare Kalkulation';
            $row['edit_url'] = xtc_href_link('admin.php', 'do=LetterConfiguratorPriceProfiles&edit_id=' . (int)$row['price_profile_id']);
            $profiles[] = $row;
        }
        usort($profiles, static function (array $a, array $b) {
            $sortCompare = ((int)$a['sort_order']) <=> ((int)$b['sort_order']);
            if ($sortCompare !== 0) {
                return $sortCompare;
            }
            return strcasecmp((string)$a['name'], (string)$b['name']);
        });

        $data = MainFactory::create('KeyValueCollection', [
            'pageToken' => $_SESSION['coo_page_token']->generate_token(),
            'profiles' => $profiles,
            'materials' => $materials,
            'productionMethods' => $productionMethods,
            'colors' => $colors,
            'thicknesses' => $thicknesses,
            'editProfile' => $editProfile,
            'action_save' => xtc_href_link('admin.php', 'do=LetterConfiguratorPriceProfiles/Save'),
            'action_toggle' => xtc_href_link('admin.php', 'do=LetterConfiguratorPriceProfiles/Toggle'),
            'action_cancel' => xtc_href_link('admin.php', 'do=LetterConfiguratorPriceProfiles'),
        ]);

        return MainFactory::create('AdminLayoutHttpControllerResponse', $title, $template, $data);
    }

    public function actionSave()
    {
        $this->_validatePageToken();
        $profile = (array)$this->_getPostData('profile');

        $id = (int)($profile['price_profile_id'] ?? 0);
        $name = trim((string)($profile['name'] ?? ''));
        $materialId = (int)($profile['material_id'] ?? 0);
        $mode = 'modular';
        $productionMethod = (string)($profile['production_method'] ?? 'cnc_fraesen');
        $methodCheck = xtc_db_query("SELECT `method_key` FROM `oli_lc_production_methods` WHERE `method_key`='" . xtc_db_input($productionMethod) . "' LIMIT 1");
        if (xtc_db_num_rows($methodCheck) === 0) { $GLOBALS['messageStack']->add_session('Die ausgewählte Fertigungsart existiert nicht.', 'error'); return $this->redirectToList($id); }
        $colorIds = $this->normalizeIds($profile['color_ids'] ?? []);
        $thicknessIds = $this->normalizeIds($profile['thickness_ids'] ?? []);
        $sortOrder = isset($profile['sort_order']) ? (int)$profile['sort_order'] : 0;
        $isActive = !empty($profile['is_active']) ? 1 : 0;

        if ($name === '' || $materialId <= 0) {
            $GLOBALS['messageStack']->add_session('Name und Material sind Pflichtfelder.', 'error');
            return $this->redirectToList($id);
        }
        if (!$this->recordExists('oli_lc_materials', 'material_id', $materialId)) {
            $GLOBALS['messageStack']->add_session('Das ausgewählte Material existiert nicht.', 'error');
            return $this->redirectToList($id);
        }
        if (!$this->idsBelongToMaterial('oli_lc_colors', 'color_id', $colorIds, $materialId)) {
            $GLOBALS['messageStack']->add_session('Mindestens eine ausgewählte Farbe gehört nicht zum Material.', 'error');
            return $this->redirectToList($id);
        }
        if (!$this->idsBelongToMaterial('oli_lc_thicknesses', 'thickness_id', $thicknessIds, $materialId)) {
            $GLOBALS['messageStack']->add_session('Mindestens eine ausgewählte Materialstärke gehört nicht zum Material.', 'error');
            return $this->redirectToList($id);
        }

        $numericFields = [
            'area_price_per_m2',
            'cutting_speed_mm_s',
            'cutting_passes',
            'machine_cost_per_hour',
            'price_per_character',
            'fixed_price',
            'minimum_price',
            'setup_fee',
            'waste_percent'
        ];
        $contourPricePerMeter = str_replace(',', '.', trim((string)($profile['contour_price_per_m'] ?? '0')));
        if ($contourPricePerMeter === '' || !is_numeric($contourPricePerMeter) || (float)$contourPricePerMeter < 0) {
            $GLOBALS['messageStack']->add_session('Bitte einen gültigen, nicht negativen Konturpreis eingeben.', 'error');
            return $this->redirectToList($id);
        }

        $configuration = [
            'material_id' => $materialId,
            'sort_order' => $sortOrder,
            'selected_color_ids' => $colorIds,
            'selected_thickness_ids' => $thicknessIds,
            'pricing_model' => 'modular',
            'production_method' => $productionMethod,
            'price_tax_mode' => 'net_excluding_vat',
            'area_basis' => 'customer_total_dimensions',
            'dimension_unit' => 'mm',
            'area_price_unit' => 'm2',
            'contour_price_unit' => 'mm',
            'contour_price_display_unit' => 'm',
            'contour_price_per_mm' => number_format(((float)$contourPricePerMeter) / 1000, 6, '.', ''),
        ];
        foreach ($numericFields as $field) {
            $value = str_replace(',', '.', trim((string)($profile[$field] ?? '0')));
            if ($value === '' || !is_numeric($value) || (float)$value < 0) {
                $GLOBALS['messageStack']->add_session('Bitte nur gültige, nicht negative Preiswerte eingeben.', 'error');
                return $this->redirectToList($id);
            }
            if ($field === 'cutting_passes') {
                $configuration[$field] = (string)max(1, (int)round((float)$value));
            } else {
                $configuration[$field] = number_format((float)$value, 2, '.', '');
            }
        }

        $code = $id > 0 ? $this->getExistingCode($id) : $this->createUniqueCode($name);

        $escapedName = xtc_db_input($name);
        $escapedCode = xtc_db_input($code);
        $escapedMode = xtc_db_input($mode);
        $json = xtc_db_input(json_encode($configuration, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if ($id > 0) {
            xtc_db_query(
                "UPDATE `oli_lc_price_profiles` SET `name`='" . $escapedName . "', `code`='" . $escapedCode .
                "', `calculation_mode`='" . $escapedMode . "', `configuration_json`='" . $json .
                "', `is_active`=" . $isActive . " WHERE `price_profile_id`=" . $id . " LIMIT 1"
            );
            $message = 'Preisprofil wurde aktualisiert.';
        } else {
            xtc_db_query(
                "INSERT INTO `oli_lc_price_profiles` (`name`,`code`,`version`,`calculation_mode`,`configuration_json`,`is_active`) VALUES ('" .
                $escapedName . "','" . $escapedCode . "',1,'" . $escapedMode . "','" . $json . "'," . $isActive . ")"
            );
            $message = 'Preisprofil wurde angelegt.';
        }

        $GLOBALS['messageStack']->add_session($message, 'info');
        return $this->redirectToList();
    }

    public function actionToggle()
    {
        $this->_validatePageToken();
        $id = (int)$this->_getPostData('price_profile_id');
        if ($id > 0) {
            xtc_db_query(
                "UPDATE `oli_lc_price_profiles` SET `is_active` = IF(`is_active` = 1, 0, 1) " .
                "WHERE `price_profile_id` = " . $id . " LIMIT 1"
            );
            $GLOBALS['messageStack']->add_session('Status des Preisprofils wurde geändert.', 'info');
        }
        return $this->redirectToList();
    }

    private function getExistingCode($id)
    {
        $result = xtc_db_query("SELECT `code` FROM `oli_lc_price_profiles` WHERE `price_profile_id` = " . (int)$id . " LIMIT 1");
        if ($row = xtc_db_fetch_array($result)) {
            return (string)$row['code'];
        }
        return $this->createUniqueCode('preisprofil');
    }

    private function createUniqueCode($name)
    {
        $base = strtolower(trim((string)$name));
        $base = preg_replace('/[^a-z0-9]+/', '-', $base);
        $base = trim((string)$base, '-');
        if ($base === '') { $base = 'preisprofil'; }
        $candidate = $base;
        $suffix = 2;
        while (xtc_db_num_rows(xtc_db_query("SELECT `price_profile_id` FROM `oli_lc_price_profiles` WHERE `code` = '" . xtc_db_input($candidate) . "' AND `version` = 1 LIMIT 1")) > 0) {
            $candidate = $base . '-' . $suffix++;
        }
        return $candidate;
    }

    private function getEmptyProfile()
    {
        return [
            'price_profile_id' => 0,
            'name' => '',
            'version' => 1,
            'calculation_mode' => 'modular',
            'production_method' => 'cnc_fraesen',
            'material_id' => 0,
            'sort_order' => 0,
            'selected_color_ids' => [],
            'selected_thickness_ids' => [],
            'area_price_per_m2' => '0.00',
            'contour_price_per_mm' => '0.000000',
            'contour_price_per_m' => '0.00',
            'cutting_speed_mm_s' => '0.00',
            'cutting_passes' => '1',
            'machine_cost_per_hour' => '0.00',
            'price_per_character' => '0.00',
            'fixed_price' => '0.00',
            'minimum_price' => '0.00',
            'setup_fee' => '0.00',
            'waste_percent' => '0.00',
            'is_active' => 1,
        ];
    }

    private function normalizeIds($ids)
    {
        if (!is_array($ids)) {
            return [];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static function ($id) { return $id > 0; })));
        sort($ids);
        return $ids;
    }

    private function recordExists($table, $idField, $id)
    {
        $result = xtc_db_query("SELECT `" . $idField . "` FROM `" . $table . "` WHERE `" . $idField . "` = " . (int)$id . " LIMIT 1");
        return xtc_db_num_rows($result) > 0;
    }

    private function idsBelongToMaterial($table, $idField, array $ids, $materialId)
    {
        if (empty($ids)) {
            return true;
        }
        $result = xtc_db_query(
            "SELECT COUNT(*) AS `total` FROM `" . $table . "` WHERE `material_id` = " . (int)$materialId .
            " AND `" . $idField . "` IN (" . implode(',', $ids) . ")"
        );
        $row = xtc_db_fetch_array($result);
        return (int)($row['total'] ?? 0) === count($ids);
    }

    private function getMaterialName($materialId, array $materials)
    {
        foreach ($materials as $material) {
            if ((int)$material['material_id'] === $materialId) {
                return (string)$material['name'];
            }
        }
        return '–';
    }

    private function getModeLabel($mode)
    {
        $labels = [
            'modular' => 'Modulare Kalkulation',
            'area' => 'Konfigurationsfläche (Altprofil)',
            'per_character' => 'Preis pro Zeichen (Altprofil)',
            'linear' => 'Konturpreis (Altprofil)',
            'fixed' => 'Festpreis (Altprofil)',
            'combined' => 'Kombiniert (Altprofil)',
        ];
        return $labels[$mode] ?? $mode;
    }


    private function ensureProductionSchema()
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
        $columns = [];
        $result = xtc_db_query("SHOW COLUMNS FROM `oli_lc_thicknesses`");
        while ($column = xtc_db_fetch_array($result)) {
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
    }


    private function formatMeasurement($value)
    {
        $formatted = number_format((float)$value, 3, ',', '');
        return rtrim(rtrim($formatted, '0'), ',');
    }

    private function redirectToList($editId = 0)
    {
        $params = 'do=LetterConfiguratorPriceProfiles';
        if ($editId > 0) {
            $params .= '&edit_id=' . (int)$editId . '#lc-price-profile-form';
        }
        return MainFactory::create('RedirectHttpControllerResponse', xtc_href_link('admin.php', $params));
    }
}

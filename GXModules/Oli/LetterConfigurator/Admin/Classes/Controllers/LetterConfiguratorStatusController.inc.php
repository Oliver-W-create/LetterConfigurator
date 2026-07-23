<?php
/* --------------------------------------------------------------
   LetterConfiguratorStatusController.inc.php
   Phase M3.5 – dashboard
   Provider: Oli
   --------------------------------------------------------------
*/

class LetterConfiguratorStatusController extends AdminHttpViewController
{
    /**
     * @return AdminLayoutHttpControllerResponse
     */
    public function actionDefault()
    {
        $title = new NonEmptyStringType('Buchstaben-Konfigurator');
        $template = $this->getTemplateFile(
            'GXModules/Oli/LetterConfigurator/Admin/Html/letter_configurator_status.html'
        );

        $schemaVersion = $this->readSetting('schema_version', 'Nicht gesetzt');

        $data = MainFactory::create('KeyValueCollection', [
            'milestone' => 'M3.8 – Preisprofile',
            'schemaVersion' => $schemaVersion,
            'phpVersion' => PHP_VERSION,
            'counts' => [
                'materials' => $this->countRows('oli_lc_materials'),
                'colors' => $this->countRows('oli_lc_colors'),
                'thicknesses' => $this->countRows('oli_lc_thicknesses'),
                'priceProfiles' => $this->countRows('oli_lc_price_profiles'),
                'productProfiles' => $this->countRows('oli_lc_product_profiles'),
                'fonts' => $this->countRows('oli_lc_fonts'),
            ],
            'activeMaterials' => $this->countRows('oli_lc_materials', '`is_active` = 1'),
            'latestMaterials' => $this->getLatestMaterials(),
            'materialsUrl' => xtc_href_link('admin.php', 'do=LetterConfiguratorMaterials'),
            'colorsUrl' => xtc_href_link('admin.php', 'do=LetterConfiguratorColors'),
            'thicknessesUrl' => xtc_href_link('admin.php', 'do=LetterConfiguratorThicknesses'),
            'newThicknessUrl' => xtc_href_link('admin.php', 'do=LetterConfiguratorThicknesses#lc-thickness-form'),
            'priceProfilesUrl' => xtc_href_link('admin.php', 'do=LetterConfiguratorPriceProfiles'),
            'newPriceProfileUrl' => xtc_href_link('admin.php', 'do=LetterConfiguratorPriceProfiles#lc-price-profile-form'),
            'newMaterialUrl' => xtc_href_link('admin.php', 'do=LetterConfiguratorMaterials#lc-material-form'),
        ]);

        return MainFactory::create('AdminLayoutHttpControllerResponse', $title, $template, $data);
    }

    /**
     * @param string $table
     * @param string $where
     *
     * @return int
     */
    private function countRows($table, $where = '')
    {
        $allowedTables = [
            'oli_lc_materials',
            'oli_lc_colors',
            'oli_lc_thicknesses',
            'oli_lc_price_profiles',
            'oli_lc_product_profiles',
            'oli_lc_fonts',
        ];

        if (!in_array($table, $allowedTables, true)) {
            return 0;
        }

        $sql = 'SELECT COUNT(*) AS `total` FROM `' . $table . '`';
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }

        $result = xtc_db_query($sql);
        $row = xtc_db_fetch_array($result);

        return isset($row['total']) ? (int)$row['total'] : 0;
    }

    /**
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    private function readSetting($key, $default)
    {
        $result = xtc_db_query(
            "SELECT `setting_value` FROM `oli_lc_settings` WHERE `setting_key` = '" .
            xtc_db_input($key) . "' LIMIT 1"
        );
        $row = xtc_db_fetch_array($result);

        return isset($row['setting_value']) ? (string)$row['setting_value'] : $default;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getLatestMaterials()
    {
        $materials = [];
        $result = xtc_db_query(
            "SELECT `material_id`, `name`, `code`, `is_active`, `updated_at` " .
            "FROM `oli_lc_materials` ORDER BY `updated_at` DESC, `material_id` DESC LIMIT 5"
        );

        while ($row = xtc_db_fetch_array($result)) {
            $row['edit_url'] = xtc_href_link(
                'admin.php',
                'do=LetterConfiguratorMaterials&edit_id=' . (int)$row['material_id']
            );
            $materials[] = $row;
        }

        return $materials;
    }
}

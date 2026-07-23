<?php
/* --------------------------------------------------------------
   LetterConfiguratorColorsController.inc.php
   Phase M3.6 – color administration
   Provider: Oli
   --------------------------------------------------------------
*/

class LetterConfiguratorColorsController extends AdminHttpViewController
{
    public function actionDefault()
    {
        $title = new NonEmptyStringType('Buchstaben-Konfigurator – Farben');
        $template = $this->getTemplateFile(
            'GXModules/Oli/LetterConfigurator/Admin/Html/letter_configurator_colors.html'
        );

        $editId = (int)$this->_getQueryParameter('edit_id');
        $editColor = [
            'color_id' => 0,
            'material_id' => 0,
            'name' => '',
            'code' => '',
            'hex_value' => '#000000',
            'price_surcharge' => '0.0000',
            'is_active' => 1,
            'sort_order' => 0,
        ];

        if ($editId > 0) {
            $result = xtc_db_query(
                "SELECT `color_id`, `material_id`, `name`, `code`, `hex_value`, `price_surcharge`, `is_active`, `sort_order` " .
                "FROM `oli_lc_colors` WHERE `color_id` = " . $editId . " LIMIT 1"
            );
            if ($row = xtc_db_fetch_array($result)) {
                $editColor = $row;
                if (empty($editColor['hex_value'])) {
                    $editColor['hex_value'] = '#000000';
                }
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
        $result = xtc_db_query(
            "SELECT c.`color_id`, c.`material_id`, c.`name`, c.`code`, c.`hex_value`, " .
            "c.`price_surcharge`, c.`is_active`, c.`sort_order`, c.`updated_at`, " .
            "m.`name` AS `material_name`, m.`code` AS `material_code` " .
            "FROM `oli_lc_colors` c " .
            "LEFT JOIN `oli_lc_materials` m ON m.`material_id` = c.`material_id` " .
            "ORDER BY c.`sort_order` ASC, m.`name` ASC, c.`name` ASC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $row['edit_url'] = xtc_href_link(
                'admin.php',
                'do=LetterConfiguratorColors&edit_id=' . (int)$row['color_id']
            );
            $colors[] = $row;
        }

        $data = MainFactory::create('KeyValueCollection', [
            'pageToken' => $_SESSION['coo_page_token']->generate_token(),
            'colors' => $colors,
            'materials' => $materials,
            'editColor' => $editColor,
            'action_save' => xtc_href_link('admin.php', 'do=LetterConfiguratorColors/Save'),
            'action_toggle' => xtc_href_link('admin.php', 'do=LetterConfiguratorColors/Toggle'),
            'action_cancel' => xtc_href_link('admin.php', 'do=LetterConfiguratorColors'),
        ]);

        return MainFactory::create('AdminLayoutHttpControllerResponse', $title, $template, $data);
    }

    public function actionSave()
    {
        $this->_validatePageToken();

        $color = (array)$this->_getPostData('color');
        $id = isset($color['color_id']) ? (int)$color['color_id'] : 0;
        $materialId = isset($color['material_id']) ? (int)$color['material_id'] : 0;
        $name = trim((string)($color['name'] ?? ''));
        $hexValue = strtoupper(trim((string)($color['hex_value'] ?? '')));
        $sortOrder = isset($color['sort_order']) ? (int)$color['sort_order'] : 0;
        $isActive = !empty($color['is_active']) ? 1 : 0;


        if ($materialId <= 0 || $name === '') {
            $GLOBALS['messageStack']->add_session('Material und Name sind Pflichtfelder.', 'error');
            return $this->redirectToList($id);
        }

        $code = $id > 0 ? $this->getExistingCode($id) : $this->createUniqueCode($name, $materialId);

        if ($hexValue !== '' && !preg_match('/^#[0-9A-F]{6}$/', $hexValue)) {
            $GLOBALS['messageStack']->add_session(
                'Der Farbwert muss im Format #RRGGBB angegeben werden.',
                'error'
            );
            return $this->redirectToList($id);
        }

        // Preisaufschläge werden seit M3.12.2 ausschließlich über Preisprofile gepflegt.
        // Die bestehende Datenbankspalte bleibt aus Kompatibilitätsgründen erhalten.
        $priceSurcharge = '0.0000';

        $materialResult = xtc_db_query(
            "SELECT `material_id` FROM `oli_lc_materials` WHERE `material_id` = " . $materialId . " LIMIT 1"
        );
        if (xtc_db_num_rows($materialResult) === 0) {
            $GLOBALS['messageStack']->add_session('Das ausgewählte Material existiert nicht.', 'error');
            return $this->redirectToList($id);
        }

        $duplicateSql =
            "SELECT `color_id` FROM `oli_lc_colors` WHERE `material_id` = " . $materialId .
            " AND `code` = '" . xtc_db_input($code) . "'";
        if ($id > 0) {
            $duplicateSql .= ' AND `color_id` <> ' . $id;
        }
        $duplicateSql .= ' LIMIT 1';
        $duplicate = xtc_db_query($duplicateSql);
        if (xtc_db_num_rows($duplicate) > 0) {
            $GLOBALS['messageStack']->add_session(
                'Dieser technische Code ist für das ausgewählte Material bereits vergeben.',
                'error'
            );
            return $this->redirectToList($id);
        }

        if ($id > 0) {
            xtc_db_query(
                "UPDATE `oli_lc_colors` SET " .
                "`material_id` = " . $materialId . ", " .
                "`name` = '" . xtc_db_input($name) . "', " .
                "`code` = '" . xtc_db_input($code) . "', " .
                "`hex_value` = " . ($hexValue === '' ? 'NULL' : "'" . xtc_db_input($hexValue) . "'") . ", " .
                "`price_surcharge` = " . $priceSurcharge . ", " .
                "`is_active` = " . $isActive . ", " .
                "`sort_order` = " . $sortOrder . " " .
                "WHERE `color_id` = " . $id . " LIMIT 1"
            );
            $message = 'Farbe wurde aktualisiert.';
        } else {
            xtc_db_query(
                "INSERT INTO `oli_lc_colors` " .
                "(`material_id`, `name`, `code`, `hex_value`, `price_surcharge`, `is_active`, `sort_order`) VALUES (" .
                $materialId . ", '" . xtc_db_input($name) . "', '" . xtc_db_input($code) . "', " .
                ($hexValue === '' ? 'NULL' : "'" . xtc_db_input($hexValue) . "'") . ", " .
                $priceSurcharge . ', ' . $isActive . ', ' . $sortOrder . ')'
            );
            $message = 'Farbe wurde angelegt.';
        }

        $GLOBALS['messageStack']->add_session($message, 'info');
        return $this->redirectToList();
    }

    public function actionToggle()
    {
        $this->_validatePageToken();
        $id = (int)$this->_getPostData('color_id');

        if ($id > 0) {
            xtc_db_query(
                "UPDATE `oli_lc_colors` SET `is_active` = IF(`is_active` = 1, 0, 1) " .
                "WHERE `color_id` = " . $id . " LIMIT 1"
            );
            $GLOBALS['messageStack']->add_session('Farbstatus wurde geändert.', 'info');
        }

        return $this->redirectToList();
    }

    private function getExistingCode($id)
    {
        $result = xtc_db_query("SELECT `code` FROM `oli_lc_colors` WHERE `color_id` = " . (int)$id . " LIMIT 1");
        if ($row = xtc_db_fetch_array($result)) {
            return (string)$row['code'];
        }
        return $this->createUniqueCode('farbe', 0);
    }

    private function createUniqueCode($name, $materialId)
    {
        $base = strtolower(trim((string)$name));
        $base = preg_replace('/[^a-z0-9]+/', '-', $base);
        $base = trim((string)$base, '-');
        if ($base === '') { $base = 'farbe'; }
        $candidate = $base;
        $suffix = 2;
        while (xtc_db_num_rows(xtc_db_query("SELECT `color_id` FROM `oli_lc_colors` WHERE `material_id` = " . (int)$materialId . " AND `code` = '" . xtc_db_input($candidate) . "' LIMIT 1")) > 0) {
            $candidate = $base . '-' . $suffix++;
        }
        return $candidate;
    }

    private function redirectToList($editId = 0)
    {
        $params = 'do=LetterConfiguratorColors';
        if ((int)$editId > 0) {
            $params .= '&edit_id=' . (int)$editId;
        }

        return MainFactory::create(
            'RedirectHttpControllerResponse',
            xtc_href_link('admin.php', $params)
        );
    }
}

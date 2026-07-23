<?php
/* --------------------------------------------------------------
   LetterConfiguratorMaterialsController.inc.php
   Phase M3.2 – material administration
   Provider: Oli
   --------------------------------------------------------------
*/

class LetterConfiguratorMaterialsController extends AdminHttpViewController
{
    /**
     * @return AdminLayoutHttpControllerResponse
     */
    public function actionDefault()
    {
        $title    = new NonEmptyStringType('Buchstaben-Konfigurator – Materialien');
        $template = $this->getTemplateFile(
            'GXModules/Oli/LetterConfigurator/Admin/Html/letter_configurator_materials.html'
        );

        $editId       = (int)$this->_getQueryParameter('edit_id');
        $editMaterial = [
            'material_id' => 0,
            'name' => '',
            'code' => '',
            'description' => '',
            'is_active' => 1,
            'sort_order' => 0,
        ];

        if ($editId > 0) {
            $result = xtc_db_query(
                "SELECT `material_id`, `name`, `code`, `description`, `is_active`, `sort_order` " .
                "FROM `oli_lc_materials` WHERE `material_id` = " . $editId . " LIMIT 1"
            );
            if ($row = xtc_db_fetch_array($result)) {
                $editMaterial = $row;
            }
        }

        $materials = [];
        $result = xtc_db_query(
            "SELECT `material_id`, `name`, `code`, `description`, `is_active`, `sort_order`, `updated_at` " .
            "FROM `oli_lc_materials` ORDER BY `sort_order` ASC, `name` ASC"
        );
        while ($row = xtc_db_fetch_array($result)) {
            $row['edit_url'] = xtc_href_link(
                'admin.php',
                'do=LetterConfiguratorMaterials&edit_id=' . (int)$row['material_id']
            );
            $materials[] = $row;
        }

        $data = MainFactory::create('KeyValueCollection', [
            'pageToken' => $_SESSION['coo_page_token']->generate_token(),
            'materials' => $materials,
            'editMaterial' => $editMaterial,
            'action_save' => xtc_href_link('admin.php', 'do=LetterConfiguratorMaterials/Save'),
            'action_toggle' => xtc_href_link('admin.php', 'do=LetterConfiguratorMaterials/Toggle'),
            'action_cancel' => xtc_href_link('admin.php', 'do=LetterConfiguratorMaterials'),
        ]);

        return MainFactory::create('AdminLayoutHttpControllerResponse', $title, $template, $data);
    }

    /**
     * @return RedirectHttpControllerResponse
     */
    public function actionSave()
    {
        $this->_validatePageToken();

        $material = (array)$this->_getPostData('material');
        $id = isset($material['material_id']) ? (int)$material['material_id'] : 0;
        $name = trim((string)($material['name'] ?? ''));
        $description = trim((string)($material['description'] ?? ''));
        $sortOrder = isset($material['sort_order']) ? (int)$material['sort_order'] : 0;
        $isActive = !empty($material['is_active']) ? 1 : 0;


        if ($name === '') {
            $GLOBALS['messageStack']->add_session('Der Name ist ein Pflichtfeld.', 'error');
            return $this->redirectToList($id);
        }

        $code = $id > 0 ? $this->getExistingCode($id) : $this->createUniqueCode($name);

        if ($id > 0) {
            xtc_db_query(
                "UPDATE `oli_lc_materials` SET " .
                "`name` = '" . xtc_db_input($name) . "', " .
                "`code` = '" . xtc_db_input($code) . "', " .
                "`description` = '" . xtc_db_input($description) . "', " .
                "`is_active` = " . $isActive . ", " .
                "`sort_order` = " . $sortOrder . " " .
                "WHERE `material_id` = " . $id . " LIMIT 1"
            );
            $message = 'Material wurde aktualisiert.';
        } else {
            xtc_db_query(
                "INSERT INTO `oli_lc_materials` " .
                "(`name`, `code`, `description`, `is_active`, `sort_order`) VALUES (" .
                "'" . xtc_db_input($name) . "', " .
                "'" . xtc_db_input($code) . "', " .
                "'" . xtc_db_input($description) . "', " .
                $isActive . ', ' . $sortOrder . ')'
            );
            $message = 'Material wurde angelegt.';
        }

        $GLOBALS['messageStack']->add_session($message, 'info');
        return $this->redirectToList();
    }

    /**
     * @return RedirectHttpControllerResponse
     */
    public function actionToggle()
    {
        $this->_validatePageToken();
        $id = (int)$this->_getPostData('material_id');

        if ($id > 0) {
            xtc_db_query(
                "UPDATE `oli_lc_materials` SET `is_active` = IF(`is_active` = 1, 0, 1) " .
                "WHERE `material_id` = " . $id . " LIMIT 1"
            );
            $GLOBALS['messageStack']->add_session('Materialstatus wurde geändert.', 'info');
        }

        return $this->redirectToList();
    }

    /**
     * @param int $editId
     *
     * @return RedirectHttpControllerResponse
     */
    private function getExistingCode($id)
    {
        $result = xtc_db_query("SELECT `code` FROM `oli_lc_materials` WHERE `material_id` = " . (int)$id . " LIMIT 1");
        if ($row = xtc_db_fetch_array($result)) {
            return (string)$row['code'];
        }
        return $this->createUniqueCode('material');
    }

    private function createUniqueCode($name)
    {
        $base = strtolower(trim((string)$name));
        $base = preg_replace('/[^a-z0-9]+/', '-', $base);
        $base = trim((string)$base, '-');
        if ($base === '') { $base = 'material'; }
        $candidate = $base;
        $suffix = 2;
        while (xtc_db_num_rows(xtc_db_query("SELECT `material_id` FROM `oli_lc_materials` WHERE `code` = '" . xtc_db_input($candidate) . "' LIMIT 1")) > 0) {
            $candidate = $base . '-' . $suffix++;
        }
        return $candidate;
    }

    private function redirectToList($editId = 0)
    {
        $params = 'do=LetterConfiguratorMaterials';
        if ((int)$editId > 0) {
            $params .= '&edit_id=' . (int)$editId;
        }

        return MainFactory::create(
            'RedirectHttpControllerResponse',
            xtc_href_link('admin.php', $params)
        );
    }
}

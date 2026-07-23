<?php
/* --------------------------------------------------------------
   LetterConfiguratorAssignmentsController.inc.php
   Phase M3.14.5 – Gambio product assignments
   Provider: Oli
   --------------------------------------------------------------
*/
class LetterConfiguratorAssignmentsController extends AdminHttpViewController
{
    public function actionDefault()
    {
        $this->ensureSchema();
        $title = new NonEmptyStringType('Buchstaben-Konfigurator – Artikelzuordnungen');
        $template = $this->getTemplateFile('GXModules/Oli/LetterConfigurator/Admin/Html/letter_configurator_assignments.html');
        $editId = (int)$this->_getQueryParameter('edit_id');
        $editAssignment = ['assignment_id' => 0, 'products_id' => 0, 'product_template_id' => 0, 'is_active' => 1];
        if ($editId > 0) {
            $r = xtc_db_query("SELECT `assignment_id`,`products_id`,`product_template_id`,`is_active` FROM `oli_lc_product_assignments` WHERE `assignment_id`=".$editId." LIMIT 1");
            if ($row = xtc_db_fetch_array($r)) { $editAssignment = $row; }
        }

        $languageId = isset($_SESSION['languages_id']) ? (int)$_SESSION['languages_id'] : 2;
        $shopProducts = [];
        $r = xtc_db_query(
            "SELECT p.`products_id`, p.`products_model`, pd.`products_name` " .
            "FROM `products` p INNER JOIN `products_description` pd ON pd.`products_id`=p.`products_id` " .
            "AND pd.`language_id`=".$languageId." ORDER BY pd.`products_name` ASC, p.`products_model` ASC"
        );
        while ($row = xtc_db_fetch_array($r)) {
            $row['is_selected'] = (int)$editAssignment['products_id'] === (int)$row['products_id'] ? 1 : 0;
            $shopProducts[] = $row;
        }

        $templates = [];
        $r = xtc_db_query("SELECT `product_template_id`,`name`,`is_active`,`sort_order` FROM `oli_lc_product_templates` ORDER BY `sort_order` ASC,`name` ASC");
        while ($row = xtc_db_fetch_array($r)) {
            $row['is_selected'] = (int)$editAssignment['product_template_id'] === (int)$row['product_template_id'] ? 1 : 0;
            $templates[] = $row;
        }

        $assignments = [];
        $r = xtc_db_query(
            "SELECT a.`assignment_id`,a.`products_id`,a.`product_template_id`,a.`is_active`,a.`updated_at`," .
            "p.`products_model`,pd.`products_name`,pt.`name` AS `template_name` " .
            "FROM `oli_lc_product_assignments` a " .
            "INNER JOIN `products` p ON p.`products_id`=a.`products_id` " .
            "INNER JOIN `products_description` pd ON pd.`products_id`=p.`products_id` AND pd.`language_id`=".$languageId." " .
            "INNER JOIN `oli_lc_product_templates` pt ON pt.`product_template_id`=a.`product_template_id` " .
            "ORDER BY pd.`products_name` ASC,p.`products_model` ASC"
        );
        while ($row = xtc_db_fetch_array($r)) {
            $row['edit_url'] = xtc_href_link('admin.php', 'do=LetterConfiguratorAssignments&edit_id='.(int)$row['assignment_id']);
            $assignments[] = $row;
        }

        $data = MainFactory::create('KeyValueCollection', [
            'pageToken' => $_SESSION['coo_page_token']->generate_token(),
            'editAssignment' => $editAssignment,
            'shopProducts' => $shopProducts,
            'templates' => $templates,
            'assignments' => $assignments,
            'action_save' => xtc_href_link('admin.php','do=LetterConfiguratorAssignments/Save'),
            'action_toggle' => xtc_href_link('admin.php','do=LetterConfiguratorAssignments/Toggle'),
            'action_delete' => xtc_href_link('admin.php','do=LetterConfiguratorAssignments/Delete'),
            'action_cancel' => xtc_href_link('admin.php','do=LetterConfiguratorAssignments'),
        ]);
        return MainFactory::create('AdminLayoutHttpControllerResponse',$title,$template,$data);
    }

    public function actionSave()
    {
        $this->_validatePageToken(); $this->ensureSchema();
        $data = (array)$this->_getPostData('assignment');
        $id = (int)($data['assignment_id'] ?? 0);
        $productsId = (int)($data['products_id'] ?? 0);
        $templateId = (int)($data['product_template_id'] ?? 0);
        $active = !empty($data['is_active']) ? 1 : 0;
        if ($productsId <= 0 || $templateId <= 0) {
            $GLOBALS['messageStack']->add_session('Bitte wählen Sie einen Gambio-Artikel und eine Produktvorlage aus.','error');
            return $this->redirect($id);
        }
        $r = xtc_db_query("SELECT `products_id` FROM `products` WHERE `products_id`=".$productsId." LIMIT 1");
        if (xtc_db_num_rows($r) === 0) { $GLOBALS['messageStack']->add_session('Der gewählte Gambio-Artikel existiert nicht.','error'); return $this->redirect($id); }
        $r = xtc_db_query("SELECT `product_template_id` FROM `oli_lc_product_templates` WHERE `product_template_id`=".$templateId." LIMIT 1");
        if (xtc_db_num_rows($r) === 0) { $GLOBALS['messageStack']->add_session('Die gewählte Produktvorlage existiert nicht.','error'); return $this->redirect($id); }
        $r = xtc_db_query("SELECT `assignment_id` FROM `oli_lc_product_assignments` WHERE `products_id`=".$productsId." AND `assignment_id`<>".$id." LIMIT 1");
        if (xtc_db_num_rows($r) > 0) { $GLOBALS['messageStack']->add_session('Dieser Gambio-Artikel ist bereits einer Produktvorlage zugeordnet.','error'); return $this->redirect($id); }
        if ($id > 0) {
            xtc_db_query("UPDATE `oli_lc_product_assignments` SET `products_id`=".$productsId.",`product_template_id`=".$templateId.",`is_active`=".$active." WHERE `assignment_id`=".$id." LIMIT 1");
            $msg='Artikelzuordnung wurde aktualisiert.';
        } else {
            xtc_db_query("INSERT INTO `oli_lc_product_assignments` (`products_id`,`product_template_id`,`is_active`) VALUES (".$productsId.",".$templateId.",".$active.")");
            $msg='Artikelzuordnung wurde angelegt.';
        }
        $GLOBALS['messageStack']->add_session($msg,'info'); return $this->redirect();
    }
    public function actionToggle(){ $this->_validatePageToken(); $this->ensureSchema(); $id=(int)$this->_getPostData('assignment_id'); if($id>0){xtc_db_query("UPDATE `oli_lc_product_assignments` SET `is_active`=IF(`is_active`=1,0,1) WHERE `assignment_id`=".$id." LIMIT 1");$GLOBALS['messageStack']->add_session('Status wurde geändert.','info');} return $this->redirect(); }
    public function actionDelete(){ $this->_validatePageToken(); $this->ensureSchema(); $id=(int)$this->_getPostData('assignment_id'); if($id>0){xtc_db_query("DELETE FROM `oli_lc_product_assignments` WHERE `assignment_id`=".$id." LIMIT 1");$GLOBALS['messageStack']->add_session('Artikelzuordnung wurde gelöscht.','info');} return $this->redirect(); }
    private function ensureSchema(){ xtc_db_query("CREATE TABLE IF NOT EXISTS `oli_lc_product_assignments` (`assignment_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`products_id` INT UNSIGNED NOT NULL,`product_template_id` INT UNSIGNED NOT NULL,`is_active` TINYINT(1) NOT NULL DEFAULT 1,`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,PRIMARY KEY (`assignment_id`),UNIQUE KEY `uq_oli_lc_assignment_product` (`products_id`),KEY `idx_oli_lc_assignment_template` (`product_template_id`),KEY `idx_oli_lc_assignment_active` (`is_active`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"); }
    private function redirect($id=0){ $q='do=LetterConfiguratorAssignments'.($id>0?'&edit_id='.$id:''); return MainFactory::create('RedirectHttpControllerResponse',xtc_href_link('admin.php',$q)); }
}

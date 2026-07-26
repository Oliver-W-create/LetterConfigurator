<?php
class LetterConfiguratorProductionMethodsController extends AdminHttpViewController
{
    public function actionDefault()
    {
        $this->ensureSchema();
        $editId=(int)$this->_getQueryParameter('edit_id');
        $edit=['production_method_id'=>0,'name'=>'','method_key'=>'','engine_key'=>'contour','range_mode'=>'single','machine_name'=>'','machine_cost_per_hour'=>'0.00','is_active'=>1,'sort_order'=>0];
        if($editId>0){
            $r=xtc_db_query("SELECT * FROM `oli_lc_production_methods` WHERE `production_method_id`=".$editId." LIMIT 1");
            if($row=xtc_db_fetch_array($r)){$edit=array_merge($edit,$row);$edit['machine_cost_per_hour']=number_format((float)$edit['machine_cost_per_hour'],2,'.','');}
        }
        $methods=[];
        $result=xtc_db_query("SELECT `production_method_id`,`name`,`method_key`,`engine_key`,`range_mode`,`machine_name`,`machine_cost_per_hour`,`is_active`,`sort_order` FROM `oli_lc_production_methods` ORDER BY `sort_order`,`name`");
        while($row=xtc_db_fetch_array($result)){
            $row['machine_cost_per_hour_display']=number_format((float)$row['machine_cost_per_hour'],2,',','.');
            $row['edit_url']=xtc_href_link('admin.php','do=LetterConfiguratorProductionMethods&edit_id='.(int)$row['production_method_id']);
            $methods[]=$row;
        }
        $data=MainFactory::create('KeyValueCollection',[
            'methods'=>$methods,'editMethod'=>$edit,'pageToken'=>$_SESSION['coo_page_token']->generate_token(),
            'action_save'=>xtc_href_link('admin.php','do=LetterConfiguratorProductionMethods/Save'),
            'action_toggle'=>xtc_href_link('admin.php','do=LetterConfiguratorProductionMethods/Toggle'),
            'action_delete'=>xtc_href_link('admin.php','do=LetterConfiguratorProductionMethods/Delete'),
            'action_cancel'=>xtc_href_link('admin.php','do=LetterConfiguratorProductionMethods')]);
        return MainFactory::create('AdminLayoutHttpControllerResponse',new NonEmptyStringType('Buchstaben-Konfigurator – Fertigungsarten'),$this->getTemplateFile('GXModules/Oli/LetterConfigurator/Admin/Html/letter_configurator_production_methods.html'),$data);
    }
    public function actionSave()
    {
        $this->_validatePageToken();$this->ensureSchema();
        $m=(array)$this->_getPostData('method');$id=(int)($m['production_method_id']??0);
        $name=trim((string)($m['name']??''));$sort=(int)($m['sort_order']??0);
        $range=in_array(($m['range_mode']??''),['single','range'],true)?$m['range_mode']:'single';
        $engine=in_array(($m['engine_key']??''),['contour','print3d','generic'],true)?$m['engine_key']:'generic';
        $machine=trim((string)($m['machine_name']??''));$cost=str_replace(',','.',trim((string)($m['machine_cost_per_hour']??'0')));
        $active=!empty($m['is_active'])?1:0;
        if($name===''){ $GLOBALS['messageStack']->add_session('Die Fertigungsart ist ein Pflichtfeld.','error');return $this->redirect($id); }
        if(!is_numeric($cost)||(float)$cost<0){$GLOBALS['messageStack']->add_session('Die Maschinenkosten sind ungültig.','error');return $this->redirect($id);}
        $key=$id>0?$this->getKey($id):$this->uniqueKey($name);
        $cost=number_format((float)$cost,4,'.','');
        if($id>0){
            xtc_db_query("UPDATE `oli_lc_production_methods` SET `name`='".xtc_db_input($name)."',`engine_key`='".xtc_db_input($engine)."',`range_mode`='".xtc_db_input($range)."',`machine_name`='".xtc_db_input($machine)."',`machine_cost_per_hour`=".$cost.",`is_active`=".$active.",`sort_order`=".$sort." WHERE `production_method_id`=".$id." LIMIT 1");
            $msg='Fertigungsart wurde aktualisiert.';
        }else{
            xtc_db_query("INSERT INTO `oli_lc_production_methods` (`name`,`method_key`,`engine_key`,`range_mode`,`machine_name`,`machine_cost_per_hour`,`is_active`,`sort_order`) VALUES ('".xtc_db_input($name)."','".xtc_db_input($key)."','".xtc_db_input($engine)."','".xtc_db_input($range)."','".xtc_db_input($machine)."',".$cost.",".$active.",".$sort.")");
            $msg='Fertigungsart wurde angelegt.';
        }
        $GLOBALS['messageStack']->add_session($msg,'info');return $this->redirect();
    }
    public function actionToggle(){ $this->_validatePageToken();$this->ensureSchema();$id=(int)$this->_getPostData('production_method_id');if($id>0){xtc_db_query("UPDATE `oli_lc_production_methods` SET `is_active`=IF(`is_active`=1,0,1) WHERE `production_method_id`=".$id." LIMIT 1");}return $this->redirect(); }
    public function actionDelete(){
        $this->_validatePageToken();$this->ensureSchema();$id=(int)$this->_getPostData('production_method_id');
        if($id>0){$key=$this->getKey($id);$used=0;
            if($key!==''){$r=xtc_db_query("SELECT COUNT(*) AS c FROM `oli_lc_thicknesses` WHERE `production_method`='".xtc_db_input($key)."'");$row=xtc_db_fetch_array($r);$used+=(int)$row['c'];
                $r=xtc_db_query("SELECT COUNT(*) AS c FROM `oli_lc_price_profiles` WHERE `configuration_json` LIKE '%\\\"production_method\\\":\\\"".xtc_db_input($key)."\\\"%'");$row=xtc_db_fetch_array($r);$used+=(int)$row['c'];}
            if($used>0){$GLOBALS['messageStack']->add_session('Die Fertigungsart wird verwendet und kann nicht gelöscht werden. Bitte zuerst deaktivieren.','error');}
            else{xtc_db_query("DELETE FROM `oli_lc_production_methods` WHERE `production_method_id`=".$id." LIMIT 1");$GLOBALS['messageStack']->add_session('Fertigungsart wurde gelöscht.','info');}
        }return $this->redirect();
    }
    private function ensureSchema()
    {
        xtc_db_query("CREATE TABLE IF NOT EXISTS `oli_lc_production_methods` (`production_method_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`name` VARCHAR(120) NOT NULL,`method_key` VARCHAR(64) NULL,`engine_key` VARCHAR(32) NOT NULL,`range_mode` ENUM('single','range') NOT NULL DEFAULT 'range',`machine_name` VARCHAR(160) NOT NULL DEFAULT '',`machine_cost_per_hour` DECIMAL(12,4) NOT NULL DEFAULT 0,`is_active` TINYINT(1) NOT NULL DEFAULT 1,`sort_order` INT NOT NULL DEFAULT 0,`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,PRIMARY KEY (`production_method_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $cols=[];$r=xtc_db_query("SHOW COLUMNS FROM `oli_lc_production_methods`");while($c=xtc_db_fetch_array($r)){$cols[$c['Field']]=1;}
        if(!isset($cols['method_key']))xtc_db_query("ALTER TABLE `oli_lc_production_methods` ADD `method_key` VARCHAR(64) NULL AFTER `name`");
        if(!isset($cols['machine_name']))xtc_db_query("ALTER TABLE `oli_lc_production_methods` ADD `machine_name` VARCHAR(160) NOT NULL DEFAULT '' AFTER `range_mode`");
        if(!isset($cols['machine_cost_per_hour']))xtc_db_query("ALTER TABLE `oli_lc_production_methods` ADD `machine_cost_per_hour` DECIMAL(12,4) NOT NULL DEFAULT 0 AFTER `machine_name`");
        $idx=xtc_db_query("SHOW INDEX FROM `oli_lc_production_methods` WHERE `Key_name`='uq_oli_lc_production_engine'");if(xtc_db_num_rows($idx)>0)xtc_db_query("ALTER TABLE `oli_lc_production_methods` DROP INDEX `uq_oli_lc_production_engine`");
        xtc_db_query("UPDATE `oli_lc_production_methods` SET `method_key`=`engine_key` WHERE (`method_key` IS NULL OR `method_key`='')");
        xtc_db_query("UPDATE `oli_lc_production_methods` SET `name`='CNC-Fräsen',`method_key`='cnc_fraesen',`engine_key`='contour',`range_mode`='single',`sort_order`=10 WHERE `method_key`='contour_cut' OR `engine_key`='contour_cut' OR `name`='Konturgeschnitten'");
        xtc_db_query("UPDATE `oli_lc_production_methods` SET `engine_key`='print3d' WHERE `method_key`='3d_print' AND `engine_key`='3d_print'");
        xtc_db_query("UPDATE `oli_lc_thicknesses` SET `production_method`='cnc_fraesen' WHERE `production_method`='contour_cut'");
        xtc_db_query("UPDATE `oli_lc_price_profiles` SET `configuration_json`=REPLACE(`configuration_json`, '\"production_method\":\"contour_cut\"', '\"production_method\":\"cnc_fraesen\"') WHERE `configuration_json` LIKE '%contour_cut%'");
        xtc_db_query("INSERT IGNORE INTO `oli_lc_production_methods` (`name`,`method_key`,`engine_key`,`range_mode`,`sort_order`) VALUES ('CNC-Fräsen','cnc_fraesen','contour','single',10),('Laserschneiden','laserschneiden','contour','single',20),('Plotten','plotten','contour','single',30),('3D-Druck','3d_print','print3d','range',40),('Sonstige','generic','generic','range',50)");
        $dups=xtc_db_query("SELECT `method_key`,MIN(`production_method_id`) keep_id FROM `oli_lc_production_methods` GROUP BY `method_key` HAVING COUNT(*)>1");while($d=xtc_db_fetch_array($dups)){xtc_db_query("DELETE FROM `oli_lc_production_methods` WHERE `method_key`='".xtc_db_input($d['method_key'])."' AND `production_method_id`<>".(int)$d['keep_id']);}
        $idx=xtc_db_query("SHOW INDEX FROM `oli_lc_production_methods` WHERE `Key_name`='uq_oli_lc_method_key'");if(xtc_db_num_rows($idx)===0)xtc_db_query("ALTER TABLE `oli_lc_production_methods` MODIFY `method_key` VARCHAR(64) NOT NULL, ADD UNIQUE KEY `uq_oli_lc_method_key` (`method_key`)");
        xtc_db_query("INSERT INTO `oli_lc_settings` (`setting_key`,`setting_value`) VALUES ('schema_version','3.13.3-production-method-crud') ON DUPLICATE KEY UPDATE `setting_value`='3.13.3-production-method-crud',`updated_at`=CURRENT_TIMESTAMP");
    }
    private function slug($s){$s=strtr($s,['Ä'=>'Ae','Ö'=>'Oe','Ü'=>'Ue','ä'=>'ae','ö'=>'oe','ü'=>'ue','ß'=>'ss']);$s=strtolower($s);$s=preg_replace('/[^a-z0-9]+/','_',$s);return trim($s,'_')?:'fertigungsart';}
    private function uniqueKey($name){$base=$this->slug($name);$key=$base;$i=2;while(true){$r=xtc_db_query("SELECT `production_method_id` FROM `oli_lc_production_methods` WHERE `method_key`='".xtc_db_input($key)."' LIMIT 1");if(xtc_db_num_rows($r)===0)return $key;$key=$base.'_'.$i++;}}
    private function getKey($id){$r=xtc_db_query("SELECT `method_key` FROM `oli_lc_production_methods` WHERE `production_method_id`=".(int)$id." LIMIT 1");$row=xtc_db_fetch_array($r);return (string)($row['method_key']??'');}
    private function redirect($id=0){$p='do=LetterConfiguratorProductionMethods'.($id>0?'&edit_id='.(int)$id:'');return MainFactory::create('RedirectHttpControllerResponse',xtc_href_link('admin.php',$p));}
}

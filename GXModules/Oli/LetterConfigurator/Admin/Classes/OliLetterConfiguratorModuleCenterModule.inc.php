<?php
/* --------------------------------------------------------------
   OliLetterConfiguratorModuleCenterModule.inc.php
   Phase M3.5 – dashboard
   Provider: Oli
   --------------------------------------------------------------
*/

class OliLetterConfiguratorModuleCenterModule extends AbstractModuleCenterModule
{
    /**
     * @return void
     */
    protected function _init(): void
    {
        $this->title       = $this->languageTextManager->get_text('oli_letter_configurator_title');
        $this->description = $this->languageTextManager->get_text('oli_letter_configurator_description');
        $this->sortOrder   = 5000;
    }

    /**
     * Installs only isolated oli_lc_* tables.
     * No Gambio core table is changed.
     *
     * @return void
     */
    public function install(): void
    {
        parent::install();

        foreach ($this->getCreateTableStatements() as $sql) {
            $this->db->query($sql);
        }

        $this->db->query(
            "INSERT INTO `oli_lc_settings` (`setting_key`, `setting_value`) " .
            "VALUES ('schema_version', '3.7.1-multiple-colors') " .
            "ON DUPLICATE KEY UPDATE `setting_value` = '3.7.1-multiple-colors', `updated_at` = CURRENT_TIMESTAMP"
        );
    }

    /**
     * Development milestone behaviour: remove only this module's own tables.
     *
     * @return void
     */
    public function uninstall(): void
    {
        $tables = [
            'oli_lc_exports',
            'oli_lc_configurations',
            'oli_lc_fonts',
            'oli_lc_product_profiles',
            'oli_lc_product_template_colors',
            'oli_lc_product_template_production_methods',
            'oli_lc_product_template_materials',
            'oli_lc_product_templates',
            'oli_lc_price_profiles',
            'oli_lc_thickness_colors',
            'oli_lc_thicknesses',
            'oli_lc_colors',
            'oli_lc_materials',
            'oli_lc_settings',
        ];

        foreach ($tables as $table) {
            $this->db->query('DROP TABLE IF EXISTS `' . $table . '`');
        }

        parent::uninstall();
    }

    /**
     * @return array<int, string>
     */
    private function getCreateTableStatements(): array
    {
        return [
            "CREATE TABLE IF NOT EXISTS `oli_lc_settings` (
                `setting_key` VARCHAR(100) NOT NULL,
                `setting_value` LONGTEXT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `oli_lc_materials` (
                `material_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(150) NOT NULL,
                `code` VARCHAR(80) NOT NULL,
                `description` TEXT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`material_id`),
                UNIQUE KEY `uq_oli_lc_material_code` (`code`),
                KEY `idx_oli_lc_material_active_sort` (`is_active`, `sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `oli_lc_colors` (
                `color_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `material_id` INT UNSIGNED NOT NULL,
                `name` VARCHAR(150) NOT NULL,
                `code` VARCHAR(80) NOT NULL,
                `hex_value` VARCHAR(16) NULL,
                `price_surcharge` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`color_id`),
                UNIQUE KEY `uq_oli_lc_color_material_code` (`material_id`, `code`),
                KEY `idx_oli_lc_color_material` (`material_id`),
                KEY `idx_oli_lc_color_active_sort` (`is_active`, `sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `oli_lc_thicknesses` (
                `thickness_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `material_id` INT UNSIGNED NOT NULL,
                `color_id` INT UNSIGNED NULL,
                `thickness_mm` DECIMAL(10,3) NOT NULL,
                `price_surcharge` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`thickness_id`),
                UNIQUE KEY `uq_oli_lc_thickness_scope` (`material_id`, `color_id`, `thickness_mm`),
                KEY `idx_oli_lc_thickness_material` (`material_id`),
                KEY `idx_oli_lc_thickness_color` (`color_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `oli_lc_thickness_colors` (
                `thickness_id` INT UNSIGNED NOT NULL,
                `color_id` INT UNSIGNED NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`thickness_id`, `color_id`),
                KEY `idx_oli_lc_thickness_colors_color` (`color_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `oli_lc_price_profiles` (
                `price_profile_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(150) NOT NULL,
                `code` VARCHAR(80) NOT NULL,
                `version` INT UNSIGNED NOT NULL DEFAULT 1,
                `calculation_mode` VARCHAR(40) NOT NULL DEFAULT 'combined',
                `configuration_json` LONGTEXT NOT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`price_profile_id`),
                UNIQUE KEY `uq_oli_lc_price_profile_code_version` (`code`, `version`),
                KEY `idx_oli_lc_price_profile_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `oli_lc_product_templates` (
                `product_template_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(150) NOT NULL,
                `description` TEXT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `sort_order` INT NOT NULL DEFAULT 0,
                `color_mode` ENUM('all','selected') NOT NULL DEFAULT 'all',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`product_template_id`),
                KEY `idx_oli_lc_product_template_active_sort` (`is_active`, `sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `oli_lc_product_template_materials` (
                `product_template_id` INT UNSIGNED NOT NULL,
                `material_id` INT UNSIGNED NOT NULL,
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`product_template_id`, `material_id`),
                KEY `idx_oli_lc_product_template_material_material` (`material_id`),
                KEY `idx_oli_lc_product_template_material_sort` (`product_template_id`, `sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `oli_lc_product_template_production_methods` (
                `product_template_id` INT UNSIGNED NOT NULL,
                `production_method_id` INT UNSIGNED NOT NULL,
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`product_template_id`, `production_method_id`),
                KEY `idx_oli_lc_product_template_method_method` (`production_method_id`),
                KEY `idx_oli_lc_product_template_method_sort` (`product_template_id`, `sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `oli_lc_product_template_colors` (
                `product_template_id` INT UNSIGNED NOT NULL,
                `color_id` INT UNSIGNED NOT NULL,
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`product_template_id`, `color_id`),
                KEY `idx_oli_lc_product_template_color_color` (`color_id`),
                KEY `idx_oli_lc_product_template_color_sort` (`product_template_id`, `sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `oli_lc_product_profiles` (
                `product_profile_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `products_id` INT UNSIGNED NOT NULL,
                `material_id` INT UNSIGNED NOT NULL,
                `price_profile_id` INT UNSIGNED NOT NULL,
                `name` VARCHAR(150) NOT NULL,
                `configuration_json` LONGTEXT NOT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`product_profile_id`),
                UNIQUE KEY `uq_oli_lc_product_products_id` (`products_id`),
                KEY `idx_oli_lc_product_material` (`material_id`),
                KEY `idx_oli_lc_product_price_profile` (`price_profile_id`),
                KEY `idx_oli_lc_product_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `oli_lc_fonts` (
                `font_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `display_name` VARCHAR(190) NOT NULL,
                `family_name` VARCHAR(190) NULL,
                `style_name` VARCHAR(190) NULL,
                `stored_filename` VARCHAR(255) NOT NULL,
                `original_filename` VARCHAR(255) NOT NULL,
                `sha256` CHAR(64) NOT NULL,
                `license_name` VARCHAR(255) NULL,
                `license_confirmed` TINYINT(1) NOT NULL DEFAULT 0,
                `is_active` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`font_id`),
                UNIQUE KEY `uq_oli_lc_font_sha256` (`sha256`),
                UNIQUE KEY `uq_oli_lc_font_stored_filename` (`stored_filename`),
                KEY `idx_oli_lc_font_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `oli_lc_configurations` (
                `configuration_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `configuration_uuid` CHAR(36) NOT NULL,
                `products_id` INT UNSIGNED NOT NULL,
                `product_profile_id` INT UNSIGNED NOT NULL,
                `customer_id` INT UNSIGNED NULL,
                `session_id_hash` CHAR(64) NULL,
                `status` VARCHAR(40) NOT NULL DEFAULT 'draft',
                `customer_text` TEXT NOT NULL,
                `width_mm` DECIMAL(12,3) NOT NULL,
                `height_mm` DECIMAL(12,3) NOT NULL,
                `thickness_mm` DECIMAL(10,3) NOT NULL,
                `calculated_price` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
                `currency_code` CHAR(3) NOT NULL DEFAULT 'EUR',
                `configuration_json` LONGTEXT NOT NULL,
                `price_snapshot_json` LONGTEXT NOT NULL,
                `locked_at` DATETIME NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`configuration_id`),
                UNIQUE KEY `uq_oli_lc_configuration_uuid` (`configuration_uuid`),
                KEY `idx_oli_lc_configuration_product` (`products_id`),
                KEY `idx_oli_lc_configuration_customer` (`customer_id`),
                KEY `idx_oli_lc_configuration_status_created` (`status`, `created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `oli_lc_exports` (
                `export_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `configuration_id` BIGINT UNSIGNED NOT NULL,
                `export_type` VARCHAR(20) NOT NULL,
                `stored_filename` VARCHAR(255) NOT NULL,
                `sha256` CHAR(64) NOT NULL,
                `file_size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
                `mime_type` VARCHAR(100) NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`export_id`),
                UNIQUE KEY `uq_oli_lc_export_filename` (`stored_filename`),
                KEY `idx_oli_lc_export_configuration` (`configuration_id`),
                KEY `idx_oli_lc_export_type` (`export_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];
    }
}

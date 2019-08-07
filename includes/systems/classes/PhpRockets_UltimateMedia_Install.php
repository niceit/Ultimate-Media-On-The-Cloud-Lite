<?php if (!defined('ULTIMATE_MEDIA_PLG_LOADED')) { die('Zero Handle'); }
/**
 * Activation Hooks while plugin is in installation progress
 * Package: Ultimate Media On The Cloud
 * Plugin URI: https://wordpress.org/extend/plugins/ultimate-media-on-the-cloud-lite
 * Date: 22-May-2019
 */
if (!class_exists('PhpRockets_UltimateMedia_Install')) {
    class PhpRockets_UltimateMedia_Install
    {
        private static $plugin_db_prefix = 'phpr_ucm_';
        private static $pluginConfigs;

        /**
         * Do when plugin is being activated
         *
         * @return void
         */
        public static function whileActivation()
        {
            self::$pluginConfigs = include ULTIMATE_MEDIA_PLG_DIR .'/includes/requires/plugin.configs.php';
            $requirement_check = self::requirementsCheck();
            if ($requirement_check) {
                return;
            }

            $create_table = self::createTables();
            if ($create_table) {
                add_action( 'admin_notices', function()  {
                    ?>
                    <div class="updated notice is-dismissible">
                        <p><?php _e( 'Welcome to Ultimate Media On The Cloud. Plugin just got activated, you can go to <a href="'. admin_url('admin.php?page=phpR-ucm') . '">General Settings</a> to setup and start using it. Enjoy!', 'ultimate-media-on-the-cloud'); ?></p>
                    </div>
                    <?php
                } );
            }
            self::initialOptions();
            self::registerTheBlogUrl();
        }

        /**
         * Perform the plugin requirements checker
         *
         * @return array
         */
        private static function requirementsCheck()
        {
            $errors = [];
            $requirements = include ULTIMATE_MEDIA_PLG_DIR .'/includes/requires/system.requirements.inc.php';
            foreach ($requirements as $check => $detail) {
                switch ($check) {
                    case 'php_version':
                        $php_check = version_compare(PHP_VERSION, $detail['value'], $detail['operator']);
                        if (!$php_check) {
                            $errors[] = $detail['message'];
                        }
                        break;
                    case 'wp_version':
                        $wp_check = version_compare(get_bloginfo('version'), $detail['value'], $detail['operator']);
                        if (!$wp_check) {
                            $errors[] = $detail['message'];
                        }
                        break;
                }
            }

            return $errors;
        }

        /**
         * Do when plugin is being removed
         * @return void
         */
        public static function whileUnInstall()
        {
            /* Check if Pro build is not existed */
            $wp_plugin_dir = str_replace('ultimate-media-on-the-cloud-lite/includes/systems/classes/', '', plugin_dir_path(__FILE__));
            if (!file_exists($wp_plugin_dir .'ultimate-media-on-the-cloud/ultimate-media-on-the-cloud.php')) {
                self::deleteTables();
                self::cleanUpOptions();
            }
        }

        /**
         * Init the UCM Plugin Required Options
         *
         * @return void
         */
        private static function initialOptions()
        {
            /**
             * General Settings
             * */
            if (!get_option(self::$plugin_db_prefix .'option_is_active')) {
                add_option(self::$plugin_db_prefix .'option_is_active', 1);
            }

            if (!get_option(self::$plugin_db_prefix .'option_addon')) {
                add_option(self::$plugin_db_prefix .'option_addon', 'aws');
            }

            if (!get_option(self::$plugin_db_prefix .'option_keep_copy')) {
                add_option(self::$plugin_db_prefix .'option_keep_copy', 0);
            }

            if (!get_option(self::$plugin_db_prefix .'option_scheme')) {
                add_option(self::$plugin_db_prefix .'option_scheme', 'https');
            }
            //End General Settings

            /**
             * Advanced Settings
             * */
            if (!get_option(self::$plugin_db_prefix .'advanced_delete_cloud_file')) {
                add_option(self::$plugin_db_prefix .'advanced_delete_cloud_file', 0);
            }

            if (!get_option(self::$plugin_db_prefix .'post_types')) {
                add_option(self::$plugin_db_prefix .'post_types', 'post,page');
            }

            if (!get_option(self::$plugin_db_prefix .'file_types')) {
                add_option(self::$plugin_db_prefix .'file_types', 'jpg,png,jpeg,gif,bmp');
            }
            //End Advanced Settings
        }

        /**
         * Register the blog URL with PhpRockets API
         */
        private static function registerTheBlogUrl()
        {
            $body = [
                'site_title' => get_bloginfo('name') . ' | Wordpress '. get_bloginfo('version'),
                'url' => get_bloginfo('url'),
                'email' => get_bloginfo('admin_email'),
                'version' => self::$pluginConfigs['current_version'],
                'build' => self::$pluginConfigs['current_release']
            ];
            $args = [
                'body' => $body,
                'timeout' => '5',
                'redirection' => '5',
                'httpversion' => '1.0',
                'blocking' => true,
            ];

            wp_remote_post('http://ws.phprockets.com/wsdl', $args);
        }

        /**
         * Clean up all plugin options when Uninstall
         *
         * @return void
         */
        private static function cleanUpOptions()
        {
            delete_option(self::$plugin_db_prefix .'option_is_active');
            delete_option(self::$plugin_db_prefix .'option_addon');
            delete_option(self::$plugin_db_prefix .'option_keep_copy');
            delete_option(self::$plugin_db_prefix .'option_scheme');
            delete_option(self::$plugin_db_prefix .'advanced_delete_cloud_file');
            delete_option(self::$plugin_db_prefix .'post_types');
            delete_option(self::$plugin_db_prefix .'file_types');
        }

        /**
         * Create Storage Cloud Account table
         *
         * @return bool
         */
        private static function createTables()
        {
            global $wpdb;
            $sql = 'CREATE TABLE IF NOT EXISTS `'. $wpdb->prefix . self::$plugin_db_prefix . 'accounts` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `storage_adapter` varchar(128) NOT NULL,
              `addon_class` varchar(128) NOT NULL,
              `name` varchar(128) NOT NULL,
              `is_default` tinyint(1) NOT NULL COMMENT \'Is default account for media\',
              `value` text NOT NULL COMMENT \'JSON value\',
              `created_at` datetime NOT NULL,
              `updated_at` datetime NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB';

            if (!$wpdb->query($sql)) {
                add_action( 'admin_notices', function() {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php _e( 'Ultimate Media On The Cloud can not be activated.<strong>SQL Error</strong>. Unable to create required table, please check SQL account permission.', 'ultimate-media-on-the-cloud'); ?></p>
                    </div>
                    <?php
                    } );
                return false;
            }

            return true;
        }

        /**
         * Remove the Plugin data table
         *
         * @return void
         */
        public static function deleteTables()
        {
            global $wpdb;
            $sql = 'DROP TABLE `'. $wpdb->prefix . self::$plugin_db_prefix .'accounts`';
            $wpdb->query($sql);
        }
    }
}
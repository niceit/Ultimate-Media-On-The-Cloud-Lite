<?php if (!defined('ULTIMATE_MEDIA_PLG_LOADED')) { die('Zero Handle'); }
/**
 * Global UCM plugin pre config variables
 * Package: Ultimate Media On The Cloud
 * Plugin URI: https://wordpress.org/extend/plugins/ultimate-media-on-the-cloud-lite
 * Date: 22-May-2019
 */
if (!class_exists('PhpRockets_UltimateMedia_Config'))
{
    class PhpRockets_UltimateMedia_Config
    {
        /* Will add suffix to asset based on WP env */
        public $enqueue_assets_suffix;

        private $pluginConfigs;
        /**
         * Directory where to save AddOn config file
         **/
        public $local_dir_save_key;

        /**
         * Define Registered Plugin AddOns
         **/
        private static $registered_addons;

        /**
         * Plugin Prefix
        **/
        public $plugin_url_prefix;
        public $plugin_db_prefix;

        //Define priority
        public $default_order = 10;
        public $plugin_icon_file;

        /**
         * PhpRockets_UltimateMedia_Config constructor.
         */
        public function __construct()
        {
            $this->enqueue_assets_suffix = (defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';;
            $this->pluginConfigs = include ULTIMATE_MEDIA_PLG_DIR .'/includes/requires/plugin.configs.php';
            $this->local_dir_save_key = ULTIMATE_MEDIA_PLG_DIR .'/includes/addons/configs/';

            $this::$registered_addons = $this->getUcmConfig('registered_addons');
            $this->plugin_url_prefix = $this->getUcmConfig('plugin_url_prefix');
            $this->plugin_db_prefix = $this->getUcmConfig('plugin_db_prefix');
            $this->plugin_icon_file = $this->getUcmConfig('plugin_icon_file');
        }

        /**
         * Get the plugin config
         *
         * @param string $key
         * @return mixed|string
         */
        public function getUcmConfig($key)
        {
            return isset($this->pluginConfigs[$key]) ? $this->pluginConfigs[$key] : '';
        }

        /**
         * Return the Menu Elements
         *
         * @param null|string $key
         * @return array
         */
        public function getMenu($key = null)
        {
            //Define text global over the plugin
            $menu_key = 1;
            $menu = [
                'menu_main' => [
                    'page_title' => 'Ultimate Media On The Cloud',
                    'text' => 'Ultimate Media On The Cloud',
                    'slug' => $this->plugin_url_prefix,
                    'handle' => [PhpRockets_UltimateMedia_Settings::class, 'renderSettingsPage']
                ],
                'menu_level_'. ++$menu_key => [
                    'page_title' => 'Ultimate Cloud Media Settings',
                    'text' => 'General Settings',
                    'slug' => $this->plugin_url_prefix,
                    'handle' => [PhpRockets_UltimateMedia_Settings::class, 'renderSettingsPage']
                ],
                'menu_level_'. ++$menu_key => [
                    'page_title' => 'Documentation',
                    'text' => 'Documentation',
                    'slug' => $this->plugin_url_prefix . '&ucm-tab=help',
                    'handle' => [PhpRockets_UltimateMedia_Settings::class, 'renderSettingsPage']
                ],
                'menu_level_'. ++$menu_key => [
                    'page_title' => "What's news in Pro Version",
                    'text' => '<b style="color: #FCB214;">Upgrade to Pro</b>',
                    'slug' => $this->plugin_url_prefix . '-pro-upgrade',
                    'handle' => [PhpRockets_UltimateMedia_Settings::class, 'renderUpgradePage']
                ],
                'menu_level_'. ++$menu_key => [
                    'page_title' => 'Support & Feedback',
                    'text' => 'Support / Feedback',
                    'slug' => $this->plugin_url_prefix . '-support',
                    'handle' => [PhpRockets_UltimateMedia_Settings::class, 'renderFeedbackPage']
                ],
                'menu_level_'. ++$menu_key => [
                    'page_title' => 'AddOns',
                    'text' => '<b style="color: #fce61b;"><i class="dashicons dashicons-admin-plugins"> </i> AddOns</b>',
                    'slug' => $this->plugin_url_prefix . '-addons',
                    'handle' => [PhpRockets_UltimateMedia_Settings::class, 'renderAddOnPage']
                ],
            ];

            if ($key) {
                return $menu[$key];

            }
            return $menu;
        }

        /**
         * Get the menu Slug url
         *
         * @param $key
         * @return string
         */
        public function getMenuSlug($key)
        {
            $menu = $this->getMenu();
            return isset($menu[$key]) ? $menu[$key]['slug'] : '';
        }

        /**
         * Check if Option removed cloud file will be deleted too when Media is deleted
         *
         * @return mixed
         */
        public function isOptionRemoveCloudFile()
        {
            return get_option($this->plugin_db_prefix .'advanced_delete_cloud_file');
        }

        /**
         * Get Database table name for Accounts
         *
         * @return string
         */
        public function getDbUcmAccountTableName()
        {
            global $wpdb;
            return $wpdb->prefix . $this->plugin_db_prefix .'accounts';
        }

        /**
         * Get current activated AddOns
         *
         * @return array
         */
        public function getAddOns()
        {
            if (isset($GLOBALS['ucm']['addons'])) {
                self::$registered_addons['external'] = $GLOBALS['ucm']['addons'];
            }

            return self::$registered_addons;
        }
    }
}
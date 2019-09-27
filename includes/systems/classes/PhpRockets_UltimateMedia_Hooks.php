<?php if (!defined('ULTIMATE_MEDIA_PLG_LOADED')) { die('Zero Handle'); }
/**
 * Global hooks definition
 * Package: Ultimate Media On The Cloud
 * Plugin URI: https://wordpress.org/extend/plugins/ultimate-media-on-the-cloud-lite
 * Date: 22-May-2019
 */
if (!class_exists('PhpRockets_UltimateMedia_Hooks'))
{
    class PhpRockets_UltimateMedia_Hooks extends PhpRockets_UltimateMedia
    {
        /**
         * Declare the actions/filters
         *
         */
        public function register()
        {
            add_action('admin_menu', [$this, 'initAdminMenu']);
            add_filter('plugin_action_links_'.plugin_basename(ULTIMATE_MEDIA_PLG_FILE), [$this, 'addPluginLinks'], $this::$configs->default_order, 1);
            add_filter('wp_get_attachment_url', [PhpRockets_UltimateMedia_Attachment::class, 'getAttachmentUrl'], $this::$configs->default_order, 2);
            add_filter('ucm_register_addons', 'ucm_register_addons', $this::$configs->default_order, 2);
            add_filter('ucm_register_addons_vendor', 'ucm_register_addons_vendor', $this::$configs->default_order, 2);

            if ($this::isUcmSection()) {
                add_action( 'admin_enqueue_scripts', [$this, 'loadBackEndAssets']);
            }
            load_plugin_textdomain('ultimate-media-on-the-cloud', false, ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/translations/');

            $registered_addons = $this::$configs->getAddOns();
            if ($registered_addons) {
                $addon_classes = [];
                foreach ($registered_addons as $type => $addons) {
                    foreach ($addons as $addon) {
                        apply_filters('ucm_register_addons', $addon, $type);
                        $addon_class = "PhpRockets_UCM_{$addon}_AddOn";
                        $addon_classes[$addon_class] = new $addon_class;
                    }
                    self::$addons = $addon_classes;
                }
            }

            $ucmAttachment = new PhpRockets_UltimateMedia_Attachment();
            //Check if Plugin is active
            if (get_option($this::$configs->plugin_db_prefix .'option_is_active')) {
                $ucmAttachment->hook();
            }
            $ucmAttachment->userEndHook();
        }

        /**
         * Init the plugin Left menu in dashboard
         *
         */
        public static function initAdminMenu()
        {
            $menus = self::$configs->getMenu();
            foreach ($menus as $menu_key => $menu) {
                if ($menu_key === 'menu_main') {
                    add_menu_page ('Settings', $menu['text'],
                        'manage_options', $menu['slug'],
                        $menu['handle'], plugins_url(self::$configs->plugin_icon_file, ULTIMATE_MEDIA_PLG_FILE));
                } else {
                    add_submenu_page($menus['menu_main']['slug'], $menu['page_title'], $menu['text'],
                        'manage_options', $menu['slug'],
                        $menu['handle']);
                }
            }
        }

        /**
         * Load JS & Styles
         *
         */
        public static function loadBackEndAssets()
        {
            if (self::isUcmSection()) {
                wp_enqueue_style('bulma', plugin_dir_url(ULTIMATE_MEDIA_PLG_FILE) .'assets/css/bulma'. self::$configs->enqueue_assets_suffix .'.css');
                wp_enqueue_style('phprockets-ucm', plugin_dir_url(ULTIMATE_MEDIA_PLG_FILE) .'assets/css/phprockets-ucm'. self::$configs->enqueue_assets_suffix .'.css');
                wp_enqueue_script('fontawesome-ucm', plugin_dir_url(ULTIMATE_MEDIA_PLG_FILE) .'assets/js/fa-all.js');
            }
        }

        /**
         * Add links under plugin name title
         *
         * @param $links
         * @return array
         */
        public static function addPluginLinks($links)
        {
            $menu = self::$configs->getMenu();
            $links[] = "<a href='admin.php?page=". $menu['menu_main']['slug'] ."'>". __('Settings', 'ultimate-media-on-the-cloud') . '</a>';
            $links[] = "<a href='". self::$configs->getUcmConfig('online_document_url') ."' target='_blank'>". __('Documentations', 'ultimate-media-on-the-cloud') . '</a>';

            return $links;
        }
    }
}

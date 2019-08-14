<?php if (!defined('ULTIMATE_MEDIA_PLG_LOADED')) { die('Zero Handle'); }
/**
 * Global define functions for UCM plugins
 * Package: Ultimate Media On The Cloud
 * Plugin URI: https://wordpress.org/extend/plugins/ultimate-media-on-the-cloud-lite
 * Date: 23-May-2019
 */

if (!function_exists('ucm_register_addons'))
{
    /**
     * @param string $namespace name of AddOn is being registered
     * @param string $type builtin | external
     */
    function ucm_register_addons($namespace, $type)
    {
        $addon_dir = $type === 'builtin' ? ULTIMATE_MEDIA_PLG_DIR . '/includes/addons/' : dirname(ULTIMATE_MEDIA_PLG_DIR) . '/ucm-addons-'. strtolower($namespace) .'/classes/';
        if (file_exists($addon_dir ."PhpRockets_UCM_{$namespace}_AddOn.php")) {
            require_once $addon_dir ."/PhpRockets_UCM_{$namespace}_AddOn.php";
            $addon_class = "PhpRockets_UCM_{$namespace}_AddOn";
            /** @var PhpRockets_UCM_Addons $class */
            $class = new $addon_class;
            $class->register();

            if (method_exists($addon_class, 'registerAjaxUrlHook')) {
                $actions = $class->registerAjaxUrlHook();
                if ($actions) {
                    foreach ($actions as $action => $callback) {
                        add_action($action, [$addon_class, $callback]);
                    }
                }
            }
        }
    }
}

if (!function_exists('ucm_register_addons_vendor'))
{
    /**
     * @param string $path name of addon vendor is being registered
     * @param string $type builtin | external
     */
    function ucm_register_addons_vendor($path, $type)
    {
        $addon_dir = $type === 'builtin' ? ULTIMATE_MEDIA_PLG_DIR . '/includes/addons/vendor' : dirname(ULTIMATE_MEDIA_PLG_DIR);
        if (file_exists($addon_dir ."/{$path}")) {
            require_once $addon_dir ."/{$path}";
        }
    }
}

$ucmSettings = new PhpRockets_UltimateMedia_Settings();
if ($ucmSettings->isPassCheckRequirement(false) && $ucmSettings->isInitialProperly(false) && !$ucmSettings->isConflicting(false)) {
    $actions = $ucmSettings->registerAjaxUrlHook();
    foreach ($actions as $action => $callback) {
        add_action($action, [PhpRockets_UltimateMedia_Settings::class, $callback]);
    }

    add_filter('ucm_host_cleanup', [PhpRockets_UltimateMedia_Attachment::class, 'cleanUpHostMedia'], $ucmSettings::$configs->default_order, 1);
    add_action('wp_ajax_'. $ucmSettings::$configs->plugin_url_prefix .'-news', [PhpRockets_UltimateMedia::class, 'checkUcmNews']);
}
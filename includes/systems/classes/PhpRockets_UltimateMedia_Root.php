<?php if (!defined('ULTIMATE_MEDIA_PLG_LOADED')) { die('Zero Handle'); }
/**
 * The Root Class of all
 * Package: Ultimate Media On The Cloud
 * Plugin URI: https://wordpress.org/extend/plugins/ultimate-media-on-the-cloud-lite
 * Date: 22-May-2019
 */
if (!class_exists('PhpRockets_UltimateMedia_Root')) {
    class PhpRockets_UltimateMedia_Root {
        /**@var PhpRockets_UltimateMedia_Config $configs */
        public static $configs;
        public static $page;

        /*Register AddOn Classes*/
        public static $addons = [];

        //Current default active Cloud Storage Adapter
        public $activeAdapter;

        /**
         * Render a template with name as string
         * @param string $name
         * @param array  $params
         * @param bool   $send_content
         * @param bool   $system_template
         * @return false|string
         */
        public static function renderTemplate($name, $params = [], $send_content = true, $system_template = true)
        {
            if ($system_template) {
                $template_path = ULTIMATE_MEDIA_PLG_DIR ."/includes/systems/tpl/{$name}.php";
            } else {
                $template_path = ULTIMATE_MEDIA_PLG_DIR ."/includes/addons/tpl/{$name}.php";
            }
            if (!file_exists($template_path)) {
                $content = '';
            } else {
                if ($params) {
                    foreach ($params as $variable => $value) {
                        ${$variable} = $value;
                    }
                }
                ${'ucm'} = new self;

                ob_start();
                include $template_path;
                $content = ob_get_contents();
                ob_end_clean();
            }

            if ($send_content) {
                echo $content;
                return null;
            }

            return $content;
        }

        /**
         * Check if current URL request is UCM settings page or not
         *
         * @return bool
         */
        public static function isUcmSection()
        {
            $ucm_menu = self::$configs->getMenu();
            $menu_slugs = [];
            foreach ($ucm_menu as $menu) {
                if (!\in_array($menu['slug'], $menu_slugs, false)) {
                    $menu_slugs[] = $menu['slug'];
                }
            }

            return \in_array(self::$page, $menu_slugs, false);
        }

        /**
         * Check if UCM page
         *
         * @param string $key
         * @return bool
         */
        public static function isPage($key)
        {
            return $key === self::$page;
        }



        /**
         * Check if a request is in post method
         * @return bool
         */
        public static function isPost()
        {
            return $_SERVER['REQUEST_METHOD'] === 'POST';
        }

        /**
         * Get GET method query data
         *
         * @param string $key
         * @return mixed|array|null
         */
        public static function getQuery($key)
        {
            if (isset($_GET[$key])) {
                if (is_string($_GET[$key])) {
                    return sanitize_text_field($_GET[$key]);
                }

                if (is_array($_GET[$key])) {
                    $data = $_GET[$key];
                    foreach ($data as $idx => $val) {
                        $data[$idx] = sanitize_text_field($val);
                    }

                    return $data;
                }
            }

            return null;
        }

        /**
         * Get Post data
         *
         * @param string $key
         * @return mixed|array|null
         */
        public static function getPost($key)
        {
            if (isset($_POST[$key])) {
                if (is_string($_POST[$key])) {
                    return sanitize_text_field($_POST[$key]);
                }

                if (is_array($_POST[$key])) {
                    $data = $_POST[$key];
                    foreach ($data as $idx => $val) {
                        $data[$idx] = sanitize_text_field($val);
                    }

                    return $data;
                }
            }

            return null;
        }
    }
}
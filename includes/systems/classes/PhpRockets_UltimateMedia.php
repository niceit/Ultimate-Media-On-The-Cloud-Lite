<?php if (!defined('ULTIMATE_MEDIA_PLG_LOADED')) { die('Zero Handle'); }
/**
 * Activation Hooks while plugin is in installation progress
 * Package: Ultimate Media On The Cloud
 * Plugin URI: https://wordpress.org/extend/plugins/ultimate-media-on-the-cloud-lite
 * Date: 22-May-2019
 */
if (!class_exists('PhpRockets_UltimateMedia')) {
    class PhpRockets_UltimateMedia extends PhpRockets_UltimateMedia_Root
    {
        /**
         * PhpRockets_UltimateMedia constructor.
         */
        public function __construct()
        {
            if ($this->isPassCheckRequirement(false) && $this->isInitialProperly(false) && !$this->isConflicting(false)){
                self::$configs = new PhpRockets_UltimateMedia_Config();
                self::$page = self::getQuery('page');
                $this->activeAdapter = $this::getCurrentActiveAdapter();
            }
        }

        /**
         * Get current requested post type to identify the settings filter if present
         * @return false|string|null
         */
        public function getCurrentPagePostType() {
            $referrer = $_SERVER['HTTP_REFERER'];
            if (!$referrer) {
                return null;
            }

            $this->_queryToArray($referrer, $query);
            if (isset($query['post_type'])) {
                return $query['post_type'];
            }

            if (isset($query['post'])) {
                $post = get_post($query['post']);
                if (!$post) {
                    return null;
                }

                return get_post_type($post);
            }

            if (false !== strpos($referrer, 'post.php') || false !== strpos($referrer, 'post-new.php')) {
                return 'post';
            }

            return null;
        }

        /**
         * Parse out url query string into an associative array
         *
         * $qry can be any valid url or just the query string portion.
         * Will return false if no valid querystring found
         *
         * @param $qry String
         * @param array $array
         * @return array|bool
         */
        private function _queryToArray($qry, &$array)
        {
            $result = array();
            //string must contain at least one = and cannot be in first position
            if(strpos($qry,'=')) {

                if(strpos($qry,'?')!==false) {
                    $q = parse_url($qry);
                    $qry = $q['query'];
                }
            } else {
                return false;
            }

            foreach (explode('&', $qry) as $couple) {
                list ($key, $val) = explode('=', $couple);
                $result[$key] = $val;
            }

            return empty($result) ? false : $array = $result;
        }

        /**
         * Check if Server is pass the requirement
         *
         * @param bool $add_notice
         * @return bool
         */
        public function isPassCheckRequirement($add_notice = true)
        {
            $requirement_check = $this->requirementsCheck();
            if (!empty($requirement_check)) {
                if ($add_notice) {
                    $message = implode('<br>', $requirement_check);
                    add_action( 'admin_notices', function() use ($message) {
                        _e('<div style="border-left: 6px solid red; padding: 5px 0px 5px 20px; margin-top: 10px; margin-bottom: 10px; background: #FFF;">
                                    <h4>Ultimate Media On The Cloud Plugin Error!</h4>
                                    <p>'. $message.'</p>
                                    <p><b style="color: red">Correct the error above, then DeActive/Active Plugin again!</b></p>
                                </div>', 'ultimate-media-on-the-cloud');
                    } );
                }

                return false;
            }

            return true;
        }

        /**
         * Check if Plugin is initial properly
         *
         * @param bool $add_notice
         * @return bool
         */
        public function isInitialProperly($add_notice = true)
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'phpr_ucm_accounts';
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
                if ($add_notice) {
                    $message = 'Plugin is not initial properly. Try to DeActive then Active again or ReInstall the plugin!';
                    add_action( 'admin_notices', function() use ($message) {
                        _e('<div style="border-left: 6px solid red; padding: 5px 0px 5px 20px; margin-top: 10px; margin-bottom: 10px; background: #FFF;">
                                        <h4>Ultimate Media On The Cloud Plugin Error!</h4>
                                        <p>'. $message.'</p>
                                    </div>', 'ultimate-media-on-the-cloud');
                    } );
                }

                return false;
            }

            /* We don't support multi sites for LITE release */
            if (is_multisite()) {
                if ($add_notice) {
                    $message = 'Plugin does not support multi sites in LITE version. Please upgrade to PRO in order to use it!. <a href="https://phprockets.com/ultimate-media-on-the-cloud-pro/" class="wp-core-ui button-primary" target="_blank"> '. __('Upgrade To Pro', 'ultimate-media-on-the-cloud') .'</a>';
                    add_action( 'admin_notices', function() use ($message) {
                        _e('<div style="border-left: 6px solid red; padding: 5px 0px 5px 20px; margin-top: 10px; margin-bottom: 10px; background: #FFF;">
                                        <h4>Ultimate Media On The Cloud Plugin Error!</h4>
                                        <p>'. $message.'</p>                                    
                                    </div>', 'ultimate-media-on-the-cloud');
                    } );
                }

                return false;
            }

            return true;
        }

        /**
         * Check if plugin is conflicting with the others
         *
         * @param bool $add_notice
         * @return bool
         */
        public function isConflicting($add_notice = true)
        {
            $plugins = [
                'amazon-s3-and-cloudfront/wordpress-s3.php' => 'WP Offload Media',
                'wp-stateless/wp-stateless-media.php'       => 'WP-Stateless',
                'ilab-media-tools/ilab-media-tools.php'     => 'Media Cloud'
            ];

            $errors = [];
            include_once ABSPATH .'wp-admin/includes/plugin.php';
            foreach ($plugins as $plugin => $name) {
                if (is_plugin_active($plugin)) {
                    $errors[] = $name;
                }
            }

            if (!empty($errors)) {
                if ($add_notice) {
                    $message = implode('<br>', $errors);
                    add_action( 'admin_notices', function() use ($message) {
                        _e('<div style="border-left: 6px solid red; padding: 5px 0px 5px 20px; margin-top: 10px; margin-bottom: 10px; background: #FFF;">
                                        <h4>Ultimate Media On The Cloud Plugin Error!</h4>
                                        <p>There\'s another plugin is doing same functionally. Please deactive it! Conflicting as listed below:</p>
                                        <p>'. $message.'</p>                                    
                                    </div>', 'ultimate-media-on-the-cloud');
                    } );
                }
                return true;
            }

            return false;
        }

        /**
         * Perform the plugin requirements checker
         *
         * @return array
         */
        private function requirementsCheck()
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
         * Execute the plugin
         *
         * @return void
         */
        public function run()
        {
            if (!$this->isConflicting()) {
                $ucmHook = new PhpRockets_UltimateMedia_Hooks();
                $ucmHook->register();

                if (!get_option($this::$configs->plugin_db_prefix .'option_is_active')) {
                    add_action('admin_notices', [$this, 'inActivePluginNotice']);
                }

                if (!$this->activeAdapter) {
                    add_action('admin_notices', [$this, 'unassignedCloudAdapter']);
                }
            }
        }

        /**
         * Leave a message if plugin is inactive
         *
         * @return void
         */
        public function inActivePluginNotice()
        {
            if (!self::isUcmSection()) {
                $menu_main = $this::$configs->getMenu('menu_main');
                _e('<div class="notice notice-error error">
                     <h4>Ultimate Media On The Cloud Notice</h4>
                     <p>'. __('Ultimate Cloud Media is inactive. Media is not saved to the Cloud! Check ', 'ultimate-media-on-the-cloud') .'<a href="'. admin_url('admin.php?page='. $menu_main['slug']) .'">'. __('General Settings', 'ultimate-media-on-the-cloud') .'</a></p>
                 </div>', 'ultimate-media-on-the-cloud');
            }
        }

        /**
         * Add notice when there is no Account Setting had been configured
         *
         * @return void
         */
        public function unassignedCloudAdapter()
        {
            if (!self::isUcmSection()) {
                $menu_main = $this::$configs->getMenu('menu_main');
                _e('<div class="notice notice-success updated">
                     <h4>Ultimate Media On The Cloud Notice</h4>
                     <p>'. __('Please update Settings for Cloud Storage Account! Check ', 'ultimate-media-on-the-cloud') .'<a href="'. admin_url('admin.php?page='. $menu_main['slug']) .'">'. __('General Settings', 'ultimate-media-on-the-cloud') .'</a></p>
                 </div>', 'ultimate-media-on-the-cloud');
            }
        }

        /**
         * Get current activated Cloud Account
         *
         * @return mixed
         */
        public static function getCurrentActiveAdapter()
        {
            $option_active_adapter = get_option(self::$configs->plugin_db_prefix .'option_addon');
            $ucm_account = PhpRockets_Model_Accounts::query([
                'conditions' => [
                    'storage_adapter' => $option_active_adapter,
                    'is_default' => 1
                ],
                'limit' => 1
            ]);

            return $ucm_account;
        }

        /**
         * Convert WP_Error to HTML String
         *
         * @param WP_Error $WP_Error
         * @return string
         */
        public function WpErrorsToHTML(WP_Error $WP_Error)
        {
            $messages = $WP_Error->get_error_messages();
            return implode('<br>', $messages);
        }

        /**
         * Get latest news from PhpRockets UCM plugin
         *
         * @return void
         */
        public static function checkUcmNews()
        {
            $content = file_get_contents(self::$configs->getUcmConfig('phprockets_ucm_news_url'));
            wp_send_json_success(['content' => $content ?: '']);
            wp_die();
        }
    }
}
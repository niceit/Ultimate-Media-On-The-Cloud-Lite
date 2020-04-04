<?php if (!defined('ULTIMATE_MEDIA_PLG_LOADED')) { die('Zero Handle'); }
/**
 * Manage Plugin Settings
 * Package: Ultimate Media On The Cloud
 * Plugin URI: https://wordpress.org/extend/plugins/ultimate-media-on-the-cloud-lite
 * Date: 22-May-2019
 */
if (!class_exists('PhpRockets_UltimateMedia_Settings')) {
    class PhpRockets_UltimateMedia_Settings extends PhpRockets_UltimateMedia
    {
        public $labels = [
            'title' => 'General Settings',
            'url' => [
                '-general-settings-save' => 'saveSettings',
                '-advanced-settings-save' => 'saveSettingsAdvanced',
                '-load-addons' => 'loadAddOns',
                '-feedback-submit' => 'sendFeedback',
            ]
        ];

        /**
         * Render the UCM Setting page
         *
         */
        public static function renderSettingsPage()
        {
            global $wp_version;
            $instance = new self;
            $instance->registerEnqueueScript();

            $loading_box = self::renderTemplate('common/box-loading-on-save', ['plugin_url' => plugin_dir_url(ULTIMATE_MEDIA_PLG_FILE)], false);
            return self::renderTemplate('settings', [
                'ucm_tab' => self::getQuery('ucm-tab'),
                'loading_box' => $loading_box,
                'addons' => self::$addons,
                'form' => $instance->buildForm(),
                'form_advanced' => $instance->buildFormAdvanced(),
                'title' => $instance->labels['title'],
                'about_pro' => self::renderTemplate('about-pro', null,false),
                'wp_version' => $wp_version
            ]);
        }

        /**
         * Handle saving setting in backend
         *
         * @return void
         * @throws Exception
         */
        public static function saveSettings()
        {
            if (self::isPost()) {
                $data = self::getPost('data');
                /* Perform validation inputs */
                $validation = new Validation();
                $validation->validation_rules([
                    'is_active' => 'required',
                    'addon' => 'required',
                    'keep_copy' => 'required',
                    'scheme' => 'required'
                ]);
                $validated = $validation->run($data);
                if ($validated === false) {
                    $error_messages = $validation->get_errors_array();
                    wp_send_json_error(['message' => implode('<br>', $error_messages)]);
                    wp_die();
                }

                self::ucmUpdateOption('option_is_active', $data['is_active']);
                self::ucmUpdateOption('option_addon', $data['addon']);
                self::ucmUpdateOption('option_keep_copy', $data['keep_copy']);
                self::ucmUpdateOption('option_scheme', $data['scheme']);
                wp_send_json_success(['message' => __('General settings updated.', 'ultimate-media-on-the-cloud')]);

            } else {
                wp_send_json_error(['message' => __('Invalid request', 'ultimate-media-on-the-cloud')]);
            }

            wp_die();
        }

        /**
         * Save advanced Settings
         * @throws Exception
         */
        public function saveSettingsAdvanced()
        {
            if (self::isPost()) {
                $data = self::getPost('data');
                /* Perform validation inputs */
                $validation = new Validation();
                $validation->validation_rules([
                    'delete_cloud_file' => 'required'
                ]);
                $validated = $validation->run($data);
                if ($validated === false) {
                    $error_messages = $validation->get_errors_array();
                    wp_send_json_error(['message' => implode('<br>', $error_messages)]);
                    wp_die();
                }

                /* Update Option Delete Cloud File */
                self::ucmUpdateOption('advanced_delete_cloud_file', $data['delete_cloud_file']);

                $allowed_post_types = 'post,page';
                if (!$data['post_types']) {
                    $data['post_types'] = $allowed_post_types;
                } else {
                    $submitted_post_types = explode(',', $data['post_types']);
                    foreach ($submitted_post_types as $submitted_post_type) {
                        if (!in_array($submitted_post_type, explode(',', $allowed_post_types), false)) {
                            wp_send_json_error(['message' => __('Only '. $allowed_post_types .' post types are allowed in Lite version.', 'ultimate-media-on-the-cloud')]);
                            wp_die();
                        }
                    }
                }

                $allowed_file_types = 'jpg,png,jpeg,gif,bmp';
                if (!$data['file_types']) {
                    $data['file_types'] = $allowed_file_types;
                } else {
                    $submitted_file_types = explode(',', $data['file_types']);
                    foreach ($submitted_file_types as $submitted_file_type) {
                        if (!in_array($submitted_file_type, explode(',', $allowed_file_types), false)) {
                            wp_send_json_error(['message' => __('Only '. $allowed_file_types .' file types are allowed in Lite version.', 'ultimate-media-on-the-cloud')]);
                            wp_die();
                        }
                    }
                }

                self::ucmUpdateOption('post_types', $data['post_types']);
                self::ucmUpdateOption('file_types', $data['file_types']);
                wp_send_json_success(['message' => __('Advanced settings updated.')]);

            } else {
                wp_send_json_error(['message' => __('Invalid request', 'ultimate-media-on-the-cloud')]);
            }

            wp_die();
        }

        /**
         * Register ajax URLs for Settings page
         *
         * @return array
         */
        public function registerAjaxUrlHook()
        {
            $ajax = [];
            foreach ($this->labels['url'] as $url => $callback) {
                $ajax['wp_ajax_'. $this::$configs->plugin_url_prefix . $url] = $callback;

            }

            return $ajax;
        }

        /**
         * Register EnqueueScript for backend settings page
         *
         */
        private function registerEnqueueScript()
        {
            wp_enqueue_script('phprockets-ucm-settings', plugin_dir_url(ULTIMATE_MEDIA_PLG_FILE) .'assets/js/ucm-settings'. self::$configs->enqueue_assets_suffix .'.js', ['jquery']);

            $vars = [];
            foreach ($this->labels['url'] as $url => $callback) {
                $vars[$callback] = admin_url('admin-ajax.php?action='. $this::$configs->plugin_url_prefix . $url);
            }
            wp_localize_script( 'phprockets-ucm-settings', 'phprockets_general', $vars);
            $ucm_l10n = [
                '_require_all_fields' => __('Please complete all fields.', 'ultimate-media-on-the-cloud'),
                '_error_submit' => __('Error while submitting form. Please try again!', 'ultimate-media-on-the-cloud'),
            ];
            wp_localize_script( 'phprockets-ucm-settings', 'phprockets_settings_l10n', $ucm_l10n);

            if (self::$addons) {
                foreach (self::$addons as $addon) {
                    if (method_exists($addon, 'registerEnqueueScript')) {
                        $addon->registerEnqueueScript();
                    }
                }
            }
        }

        /**
         * Build the view form template
         *
         * @return false|string
         */
        public function buildForm()
        {
            $errors = [];
            $messages = [];
            $options = [
                'is_active' => get_option(self::$configs->plugin_db_prefix .'option_is_active'),
                'addon' => get_option(self::$configs->plugin_db_prefix .'option_addon'),
                'keep_copy' => get_option(self::$configs->plugin_db_prefix .'option_keep_copy'),
                'scheme' => get_option(self::$configs->plugin_db_prefix .'option_scheme')
            ];

            if (!$options['is_active']) {
                $errors[] = __('Plugin is disabled. Media is not saved to the cloud!', 'ultimate-media-on-the-cloud');
            }

            if (!$this->activeAdapter || !class_exists($this->activeAdapter['addon_class'])) {
                $errors[] = __('Please setup Cloud Storage Account!', 'ultimate-media-on-the-cloud');
            }

            $form = [
                'div' => [
                    'class' => 'panel-body',
                    'id' => 'ucm-general'
                ],
                'attr' => [
                    'id' => 'frm-ucm-general',
                    'onSubmit' => 'return false;'
                ],
                'fields' => [],
                'submit' => [
                    'label' => __('Save Settings', 'ultimate-media-on-the-cloud'),
                    'attr' => [
                        'href' => 'javascript:;',
                        'id' => 'btn-ucm-general',
                        'class' => 'button is-info'
                    ]
                ]
            ];

            $form['fields'][] = [
                'label' => __('Active Cloud Media?', 'ultimate-media-on-the-cloud'),
                'type'  => 'select',
                'icon' => 'fa fa-check',
                'value' => [1 => 'Yes', 0 => 'No'],
                'selected' => (int)$options['is_active'],
                'attr' => [
                    'name' => 'data[is_active]',
                    'id' => 'general-is-active'
                ],
                'help-text' => __('Choose <b>Yes</b> If you want plugin to work. <b>No</b> if you want to disable plugin temporary.', 'ultimate-media-on-the-cloud')
            ];

            $registered_addons = self::$configs->getAddOns();
            $accounts = [];
            if ($registered_addons) {
                foreach ($registered_addons as $type => $addons) {
                    if ($addons) {
                        foreach ($addons as $key => $title) {
                            $accounts[$key] = $title;
                        }
                    }
                }
            }
            $form['fields'][] = [
                'label' => __('Cloud Default Active', 'ultimate-media-on-the-cloud'),
                'type'  => 'select',
                'icon' => 'fa fa-cloud',
                'value' => $accounts,
                'selected' => $options['addon'],
                'attr' => [
                    'name' => 'data[addon]',
                    'id' => 'general-addon'
                ],
                'help-text' => __('Choose the default Cloud Storage Service.', 'ultimate-media-on-the-cloud')
            ];

            $form['fields'][] = [
                'label' => __('Keep a copy of media?', 'ultimate-media-on-the-cloud'),
                'type'  => 'select',
                'icon' => 'fa fa-copy',
                'value' => [1 => 'Yes', 0 => 'No'],
                'selected' => (int)$options['keep_copy'],
                'attr' => [
                    'name' => 'data[keep_copy]',
                    'id' => 'general-keep-copy'
                ],
                'help-text' => __('When media is uploaded and pushed to the cloud, it will keep a copy at your host.', 'ultimate-media-on-the-cloud')
            ];

            $form['fields'][] = [
                'label' => __('Scheme Protocol', 'ultimate-media-on-the-cloud'),
                'type'  => 'select',
                'icon' => 'fa fa-lock',
                'value' => ['http' => 'HTTP', 'https' => 'HTTPS'],
                'selected' => $options['scheme'],
                'attr' => [
                    'name' => 'data[scheme]',
                    'id' => 'general-scheme'
                ]
            ];

            if (!$errors) {
                $messages[] = __('Plugin had been setup and running.', 'ultimate-media-on-the-cloud');
            }

            return $this::renderTemplate('common/_form', ['form' => $form, 'errors' => $errors, 'messages' => $messages], false);
        }

        /**
         * Advanced Settings Tab
         *
         * @return false|string
         */
        public function buildFormAdvanced()
        {
            $options = [
                'delete_cloud_file' => get_option(self::$configs->plugin_db_prefix .'advanced_delete_cloud_file')
            ];

            $form_advanced = [
                'div' => [
                    'class' => 'panel-body',
                    'id' => 'ucm-advanced'
                ],
                'attr' => [
                    'id' => 'frm-ucm-advanced',
                    'onSubmit' => 'return false;'
                ],
                'fields' => [],
                'submit' => [
                    'label' => __('Save Settings', 'ultimate-media-on-the-cloud'),
                    'attr' => [
                        'href' => 'javascript:;',
                        'id' => 'btn-ucm-advanced',
                        'class' => 'button is-info'
                    ]
                ]
            ];

            $form_advanced['fields'][] = [
                'label' => __('Delete Cloud File?', 'ultimate-media-on-the-cloud'),
                'type'  => 'select',
                'icon' => 'fa fa-trash',
                'value' => [1 => 'Yes', 0 => 'No'],
                'selected' => (int)$options['delete_cloud_file'],
                'attr' => [
                    'name' => 'data[delete_cloud_file]',
                    'id' => 'advanced-delete-cloud-file'
                ],
                'help-text' => __('Remove cloud media too when you delete a media from Wordpress', 'ultimate-media-on-the-cloud')
            ];

            $post_type_options = get_option(self::$configs->plugin_db_prefix .'post_types');
            $form_advanced['fields'][] = [
                'label' => 'Post types',
                'type' => 'tags',
                'icon' => 'fa fa-tags',
                'placeholder' => 'Add a post type',
                'attr' => [
                    'name' => 'data[post_types]',
                    'value' =>  $post_type_options ? explode(',', $post_type_options) : [],
                    'id' => 'post-types',
                    'class' => 'tags-hidden-value'
                ],
                'help-text' => __('Specify the post types you want to be saved media on cloud. Ex: page, post... <b>Leave empty for all post types</b>', 'ultimate-media-on-the-cloud')
            ];

            $file_type_options = get_option(self::$configs->plugin_db_prefix .'file_types');
            $form_advanced['fields'][] = [
                'label' => 'File types',
                'type' => 'tags',
                'icon' => 'fa fa-tag',
                'placeholder' => 'Add a file type',
                'attr' => [
                    'name' => 'data[file_types]',
                    'value' =>  $file_type_options ? explode(',', $file_type_options) : [],
                    'id' => 'file-types',
                    'class' => 'tags-hidden-value'
                ],
                'help-text' => __('Specify the file types you want to be saved media on cloud. Ex: jpg, png... <b>Leave empty for all file types</b>', 'ultimate-media-on-the-cloud')
            ];

            return $this::renderTemplate('common/_form', ['form' => $form_advanced], false);
        }

        /**
         * Display documentation page
         *
         * @return string
         */
        public static function renderDocumentationPage()
        {
            return self::renderTemplate('documentation', [
                'main_menu' => self::$configs->getMenu('menu_main')
            ]);
        }

        /**
         * Display Plugin Pro Upgrade page
         *
         * @return string
         */
        public static function renderUpgradePage()
        {
            return self::renderTemplate('pro-upgrade', [
                'main_menu' => self::$configs->getMenu('menu_main'),
                'about_pro' => self::renderTemplate('about-pro', null,false),
            ]);
        }

        /**
         * Display Plugin Support & Feedback Page
         *
         * @return string
         */
        public static function renderFeedbackPage()
        {
            $instance = new self;
            $instance->registerEnqueueScript();
            $loading_box = self::renderTemplate('common/box-loading-on-save', ['plugin_url' => plugin_dir_url(ULTIMATE_MEDIA_PLG_FILE)], false);
            return self::renderTemplate('feedback', [
                'main_menu' => self::$configs->getMenu('menu_main'),
                'form' => $instance->buildFormFeedback(),
                'loading_box' => $loading_box
            ]);
        }

        /**
         * Handle send feedback form
         *
         * @return void
         * @throws Exception
         */
        public static function sendFeedback()
        {
            if (self::isPost()) {
                $data = self::getPost('data');
                /* Perform input validation */
                $validation = new Validation();
                $validation->validation_rules([
                    'name' => 'required',
                    'email' => 'required|valid_email',
                    'subject' => 'required',
                    'type' => 'required',
                    'body' => 'required'
                ]);
                $validated = $validation->run($data);
                if ($validated === false) {
                    $error_messages = $validation->get_errors_array();
                    wp_send_json_error(['message' => implode('<br>', $error_messages)]);
                    wp_die();
                }

                $support_email = self::$configs->getUcmConfig('support_email');
                $mail_body = self::renderTemplate('email/feedback', [
                    'data' => $data,
                    'site_url' => get_bloginfo('url'),
                    'plugin_version' => self::$configs->getUcmConfig('current_version'),
                    'plugin_release' => self::$configs->getUcmConfig('current_release'),
                ], false);

                add_filter( 'wp_mail_content_type',  static function(){
                    return 'text/html';
                });
                $mail = wp_mail($support_email, 'UCM User Feedback From '. get_bloginfo('url'), $mail_body);
                if ($mail) {
                    wp_send_json_success(['message' => __('Your feedback has been sent. Thank you!')]);
                } else {
                    wp_send_json_error(['message' => __('Unable to send mail from your host. Please contact your hosting provider!', 'ultimate-media-on-the-cloud')]);
                }

            } else {
                wp_send_json_error(['message' => __('Invalid request', 'ultimate-media-on-the-cloud')]);
            }
        }

        /**
         * Advanced Settings Tab
         *
         * @return false|string
         */
        public function buildFormFeedback()
        {
            $form = [
                'div' => [
                    'class' => 'panel-body',
                    'style' => 'display: block;',
                    'id' => 'ucm-feedback'
                ],
                'attr' => [
                    'name' => 'frm_ucm_feedback',
                    'id' => 'frm-ucm-feedback',
                    'onSubmit' => 'return false;'
                ],
                'fields' => [],
                'submit' => [
                    'label' => __('Send', 'ultimate-media-on-the-cloud'),
                    'attr' => [
                        'href' => 'javascript:;',
                        'id' => 'btn-ucm-feedback',
                        'class' => 'button is-primary'
                    ]
                ]
            ];

            $form['fields'][] = [
                'label' => 'Your name',
                'type' => 'text',
                'icon' => 'fa fa-tag',
                'attr' => [
                    'name' => 'data[name]',
                    'placeholder' => __('Type your fullname', 'ultimate-media-on-the-cloud'),
                    'class' => 'input',
                    'id' => 'input-name',
                ]
            ];

            $form['fields'][] = [
                'label' => 'Your email',
                'type' => 'text',
                'icon' => 'fa fa-envelope',
                'attr' => [
                    'name' => 'data[email]',
                    'placeholder' => __('Type your email', 'ultimate-media-on-the-cloud'),
                    'class' => 'input',
                    'value' => get_option('admin_email'),
                    'id' => 'input-email',
                ]
            ];

            $form['fields'][] = [
                'label' => 'Subject',
                'type' => 'text',
                'icon' => 'fa fa-tag',
                'attr' => [
                    'name' => 'data[subject]',
                    'placeholder' => __('Your question or need support?', 'ultimate-media-on-the-cloud'),
                    'class' => 'input',
                    'id' => 'input-subject',
                ]
            ];

            $form['fields'][] = [
                'label' => __('Type', 'ultimate-media-on-the-cloud'),
                'type' => 'select',
                'icon' => 'fa fa-tags',
                'value' => [
                    'Leave Feedback' => 'Leave Feedback',
                    'Technical Issue' => 'Technical Issue',
                    'Need Support' => 'Need Support',
                    'Other.' => 'Other.'
                ],
                'attr' => [
                    'name' => 'data[type]',
                    'id' => 'input-type',
                ]
            ];

            $form['fields'][] = [
                'label' => 'Body',
                'type' => 'textarea',
                'icon' => 'fa fa-comment',
                'attr' => [
                    'style' => 'height: 150px;',
                    'name' => 'data[body]',
                    'placeholder' => __('Message Body', 'ultimate-media-on-the-cloud'),
                    'class' => 'input',
                    'id' => 'input-body',
                ]
            ];

            return $this::renderTemplate('common/_form', ['form' => $form], false);
        }

        /**
         * Render the AddOn Page
         * @return false|string
         */
        public static function renderAddOnPage()
        {
            $instance = new self;
            $instance->registerEnqueueScript();
            wp_add_inline_script('phprockets-ucm-settings', 'phpR_UCM.loadAddOns();');
            return self::renderTemplate('addon', [
                'ucm_tab' => isset($_GET['ucm-tab']) ? $_GET['ucm-tab'] : '',
                'title' => __('Available AddOn For Ultimate Media On The Cloud', 'ultimate-media-on-the-cloud'),
            ]);
        }

        /**
         * Ajax load UCM addons
         */
        public function loadAddOns()
        {
            $remote_url = 'http://ws.phprockets.com/ucm-addons';
            $args = [
                'timeout' => 30,
            ];
            $content = wp_remote_get($remote_url, $args);
            if (is_array($content)) {
                $body = json_decode($content['body'], true);
                $data = $body['data'];

                $html = self::renderTemplate('ajax_addons', ['data' => $data], false);
                wp_send_json_success(['html' => $html]);
            } else {
                wp_send_json_error(['message' => 'Unable to load the AddOns. Please try again later!']);
            }

            wp_die();
        }
    }
}
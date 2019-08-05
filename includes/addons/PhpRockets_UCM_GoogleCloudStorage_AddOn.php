<?php if (!defined('ULTIMATE_MEDIA_PLG_LOADED')) { die('Zero Handle'); }
/**
 * Add-On for manage Google Cloud Storage
 * Package: Ultimate Media On The Cloud
 * Plugin URI: https://wordpress.org/extend/plugins/ultimate-media-on-the-cloud-lite
 * Date: 30-May-2019
 */

use Google\Cloud\Core\Exception\GoogleException;
    use Google\Cloud\Core\Iterator\ItemIterator;
    use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;

if (!class_exists('PhpRockets_UCM_GoogleCloudStorage_AddOn')) {
    class PhpRockets_UCM_GoogleCloudStorage_AddOn extends PhpRockets_UCM_Addons
    {
        /**
         * AddOn Information
         **/
        public $labels = [
            'namespace' => __CLASS__,
            'title' => 'Google Cloud Storage',
            'uri' => 'storage.googleapis.com',
            'url' => [
                '-google-cloud-save' => 'saveSettings',
                '-google-cloud-connect' => 'connect'
            ]
        ];

        /**
         * Register addon handler
         *
         * @return void
         */
        public function register()
        {
            apply_filters('ucm_register_addons_vendor', 'google-core/vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
            add_filter('ucm_google_cloud_upload_media', [$this, 'doPushAttachment'], $this::$configs->default_order , 1);
            if (get_option(self::$configs->plugin_db_prefix .'advanced_delete_cloud_file')) {
                add_filter('ucm_google_cloud_cloud_remove_file', [$this, 'doRemoveAttachmentMedia'], $this::$configs->default_order, 1);
            }
            add_filter('ucm_google_cloud_cloud_file_url', [$this, 'getAdapterStorageUrl'], $this::$configs->default_order, 2);
            add_filter('ucm_google_cloud_verify_key_file_upload', [$this, 'verifyKeyFileUpload'],$this::$configs->default_order, 1);
        }

        /**
         * Init Cloud Storage Adapter Hanlder
         *
         * @return StorageClient
         */
        private function initAdapter()
        {
            $google_cloud_config = unserialize($this->activeAdapter['value']);
            $config = [
                'keyFilePath' => self::$configs->local_dir_save_key . $google_cloud_config['auth_file'],
                'projectId' => $google_cloud_config['project_id'],
            ];

            try {
                $storage = new StorageClient($config);
            } catch (GoogleException $e) {
                wp_send_json_error(['message' => $e->getMessage()]);
                wp_die();
            }

            return $storage;
        }

        /**
         * Generate form setting page
         *
         * @return mixed
         */
        public function buildForm()
        {
            $errors = [];
            $messages = [];
            $gg_account = PhpRockets_Model_Accounts::query([
                'conditions' => [
                    'storage_adapter' => 'google_cloud',
                    'is_default' => 1
                ],
                'limit' => 1
            ]);
            $gg_data = [];
            if ($gg_account) {
                $gg_data = unserialize($gg_account['value']);
            }
            if (!$gg_account) {
                $errors[] = __('Set up Google Cloud Account by the form below', 'ultimate-media-on-the-cloud');
            }

            $form = [
                'div' => [
                    'class' => 'panel-body',
                    'id' => 'phprockets-ucm-googlecloudstorage-addon'
                ],
                'attr' => [
                    'id' => 'frm-ucm-google-cloud',
                    'onSubmit' => 'return false;',
                    'enctype' => 'multipart/form-data'
                ],
                'fields' => [],
                'submit' => [
                    'label' => __('Save Settings', 'ultimate-media-on-the-cloud'),
                    'attr' => [
                        'href' => 'javascript:;',
                        'id' => 'btn-google-cloud-setting',
                        'class' => 'button is-info'
                    ]
                ]
            ];

            $form['fields'][] = [
                'label' => __('Project Id', 'ultimate-media-on-the-cloud'),
                'type'  => 'text',
                'icon' => 'dashicons dashicons-post-status',
                'attr' => [
                    'name' => 'data[project_id]',
                    'value' => $gg_data ? $gg_data['project_id'] : '',
                    'placeholder' => __('Google Cloud Project Id', 'ultimate-media-on-the-cloud'),
                    'class' => 'input',
                    'id' => 'project-id'
                ],
            ];

            $form['fields'][] = [
                'label' => __('Authentication Key File', 'ultimate-media-on-the-cloud'),
                'type'  => 'file',
                'icon' => 'dashicons dashicons-format-aside',
                'value' => $gg_data ? $gg_data['auth_file'] : '',
                'attr' => [
                    'type' => 'file',
                    'name' => 'data[key_file]',
                    'class' => 'file-input',
                    'id' => 'key-file'
                ],
                'help-text' => __('Obtain Google Cloud Project Id & Authentication Key ', 'ultimate-media-on-the-cloud') .'<a href="'. $this::$configs->getUcmConfig('gcloud_guide_console') .'" class="button is-link is-small" target="_blank"> '. __('Google Console', 'ultimate-media-on-the-cloud') .'</a>'
            ];

            $form['fields'][] = [
                'label' => __('Connect', 'ultimate-media-on-the-cloud'),
                'type' => 'anchor',
                'attr' => [
                    'href' => 'javascript:;',
                    'icon' => 'dashicons dashicons-plugins-checked icon-button',
                    'class' => 'button is-link',
                    'id' => 'btn-gcloud-connect'
                ]
            ];

            $buckets = [];
            if ($gg_account) {
                $project_id = $gg_data['project_id'];
                $config = [
                    'keyFilePath' => self::$configs->local_dir_save_key . $gg_data['auth_file'],
                    'projectId' => $project_id,
                ];
                try {
                    $storage = new StorageClient($config);
                } catch (\Exception $e) {
                    $errors[] = __('Invalid Google Cloud configs', 'ultimate-media-on-the-cloud');
                }

                if (!$errors) {
                    try {
                        $gcloud_buckets = $storage->buckets();
                    } catch (\Exception $e) {
                        $errors[] = __('Unable to Fetch Buckets', 'ultimate-media-on-the-cloud');
                    }
                }

                if (!$errors) {
                    try {
                        foreach ($gcloud_buckets as $bucket) {
                            $bucket_name = $bucket->name();
                            /** @var Bucket $bucket */
                            $buckets[$bucket_name] = $bucket_name;
                        }
                    } catch (\Exception $e) {
                        $exception_result = json_decode($e->getMessage(), ARRAY_A);
                        $exc_errors = $exception_result['error']['errors'];
                        $WP_Error = new WP_Error();
                        foreach ($exc_errors as $error) {
                            $WP_Error->add('exception', $error['message']);
                        }
                        $errors[] = $this->WpErrorsToHTML($WP_Error);
                    }
                }
            }

            $form['fields'][] = [
                'label' => __('Default Bucket', 'ultimate-media-on-the-cloud'),
                'type' => 'select',
                'icon' => 'dashicons dashicons-lock',
                'value' => array_merge(['' => '-Choose Bucket-'], $buckets),
                'selected' => $gg_data ? $gg_data['bucket'] : '',
                'attr' => [
                    'name' => 'data[bucket]',
                    'id' => 'gcloud-bucket',
                ],
                'help-text' => __('Click Connect button above to fetch available Buckets.', 'ultimate-media-on-the-cloud')
            ];

            $form['fields'][] = [
                'label' => __('Cloud Path', 'ultimate-media-on-the-cloud'),
                'type' => 'text',
                'icon' => 'dashicons dashicons-archive',
                'attr' => [
                    'name' => 'data[cloud_path]',
                    'value' => $gg_data ? $gg_data['cloud_path'] : '/',
                    'placeholder' => __('Google Cloud Path', 'ultimate-media-on-the-cloud'),
                    'class' => 'input',
                    'id' => 'gcloud-cloud-path',
                ],
                'help-text' => __('Target folder at cloud storage you want to save media to.', 'ultimate-media-on-the-cloud')
            ];

            $form['fields'][] = [
                'type' => 'hidden',
                'attr' => [
                    'name' => 'is_gcloud_account',
                    'value' => $gg_data ? 1 : 0,
                    'id' => 'is-gcloud-account',
                ]
            ];

            return $this::renderTemplate('common/_form', ['form' => $form, 'errors' => $errors, 'messages' => $messages], false);
        }

        /**
         * Handle POST data to save into Database
         */
        public static function saveSettings()
        {
            $gcloud_account = PhpRockets_Model_Accounts::query([
                'conditions' => [
                    'storage_adapter' => 'google_cloud'
                ],
                'limit' => 1
            ]);
            $gg_data = [];
            if ($gcloud_account) {
                $gg_data = unserialize($gcloud_account['value']);
            }

            if (!$gcloud_account && !isset($_FILES['file'])) {
                wp_send_json_error(['message' => __('Authentication key file is required.', 'ultimate-media-on-the-cloud')]);
            } else {
                $project_id = self::getPost('project_id');
                /* Has authentication key file submitted */
                if ($_FILES && isset($_FILES['file'])) {
                    /* Verify Authentication Key mime-type is accepted */
                    $is_valid = apply_filters('ucm_google_cloud_verify_key_file_upload', $_FILES['file']);
                    if (!$is_valid) {
                        wp_send_json_error(['message' => __('Invalid Auth Key mime-type. Allowed key file types: p12 or json', 'ultimate-media-on-the-cloud')]);
                        wp_die();
                    }

                    $auth_filename = sanitize_file_name($_FILES['file']['name']);
                    $move_config_file = move_uploaded_file($_FILES['file']['tmp_name'], self::$configs->local_dir_save_key . $auth_filename);
                    if ($move_config_file) {
                        $key_file_path = self::$configs->local_dir_save_key . $auth_filename;
                    } else {
                        wp_send_json_error(['message' => __('Unable to save your config file. Please check your host upload folder permission!', 'ultimate-media-on-the-cloud')]);
                        wp_die();
                    }
                } else {
                    $key_file_path = $gg_data ? self::$configs->local_dir_save_key . $gg_data['auth_file'] : '';
                    /* Account is existed but unable to read key file path data | User has to re-upload key file */
                    if (!$key_file_path) {
                        wp_send_json_error(['message' => __('Account has corrupted data and unable to locate key file. Please re-upload your Authentication Key File.', 'ultimate-media-on-the-cloud')]);
                        wp_die();
                    }
                }

                /* Init Google Cloud Client Object */
                $config = [
                    'keyFilePath' => $key_file_path,
                    'projectId' => $project_id,
                ];

                try {
                    $storage = new StorageClient($config);
                } catch (GoogleException $e) {
                    wp_send_json_error(['message' => $e->getMessage()]);
                    wp_die();
                }

                $trait_keyfile_name = explode('/', $key_file_path);
                $trait_keyfile_name = $trait_keyfile_name[count($trait_keyfile_name) - 1];
                $data = [
                    'project_id' => self::getPost('project_id'),
                    'bucket' => self::getPost('bucket'),
                    'cloud_path' => self::getPost('cloud_path'),
                    'auth_file' => $trait_keyfile_name
                ];

                /* Perform an unit test connect before going to save account */
                try {
                    $gcloud_buckets = $storage->buckets([
                        'resultLimit' => 1
                    ]);
                } catch (GoogleException $e) {
                    wp_send_json_error(['message' => $e->getMessage()]);
                    wp_die();
                }

                $unit_test = self::doUnitSettingsTest($gcloud_buckets);
                if (is_wp_error($unit_test)) {
                    /** @var WP_Error $unit_test */
                    $messages = $unit_test->get_error_messages();
                    wp_send_json_error(['message' => implode('<br>', $messages)]);
                    wp_die();
                }

                /* Alternative perform a test upload */
                $source_file = fopen(ULTIMATE_MEDIA_PLG_DIR .'/assets/test.txt','r');
                $bucket = $storage->bucket($data['bucket']);
                try {
                    $bucket->upload($source_file, [
                        'predefinedAcl' => 'publicRead'
                    ]);
                } catch (\InvalidArgumentException $e) {
                    wp_send_json_error(['message' => $e->getMessage()]);
                    wp_die();
                }
                $bucket->object('test.txt')->delete();

                /* Save account config */
                if (!$gcloud_account) {
                    $create_account = PhpRockets_Model_Accounts::create([
                        'storage_adapter' => 'google_cloud',
                        'name' => 'Default Google Cloud Account',
                        'is_default' => 1,
                        'value' => serialize($data),
                        'created_at' => current_time('mysql', 1),
                        'updated_at' => current_time('mysql', 1)
                    ]);

                    if ($create_account) {
                        wp_send_json_success(['message' => __('Account information updated.', 'ultimate-media-on-the-cloud')]);
                    } else {
                        wp_send_json_error(['message' => __('An error occurred while updating account. Try again later!', 'ultimate-media-on-the-cloud')]);
                    }
                } else {
                    $update_account = PhpRockets_Model_Accounts::update($gcloud_account['id'], [
                        'value' => serialize($data),
                        'updated_at' => current_time('mysql', 1)
                    ]);

                    if ($update_account) {
                        wp_send_json_success(['message' => __('Account information updated.', 'ultimate-media-on-the-cloud')]);
                    } else {
                        wp_send_json_error(['message' => __('An error occurred while updating account. Try again later!', 'ultimate-media-on-the-cloud')]);
                    }
                }
            }
            wp_die();
        }

        /**
         * Test configuration connection
         *
         */
        public static function connect()
        {
            $gcloud_account = PhpRockets_Model_Accounts::query([
                'conditions' => [
                    'storage_adapter' => 'google_cloud'
                ],
                'limit' => 1
            ]);
            $gg_data = [];
            if ($gcloud_account) {
                $gg_data = unserialize($gcloud_account['value']);
            }

            /* If there is no google account and no auth key submitted */
            if (!$gcloud_account && !isset($_FILES['file'])) {
                wp_send_json_error(['message' => __('You need to upload an Authentication Key File.', 'ultimate-media-on-the-cloud')]);
            } else {
                $project_id = self::getPost('project_id');
                if ($_FILES && isset($_FILES['file'])) {
                    /* Verify Authentication Key mime-type is accepted */
                    $is_valid = apply_filters('ucm_google_cloud_verify_key_file_upload', $_FILES['file']);
                    if (!$is_valid) {
                        wp_send_json_error(['message' => __('Invalid Auth Key mime-type. Allowed key file types: p12 or json', 'ultimate-media-on-the-cloud')]);
                        wp_die();
                    }

                    /* Temporary save config file to upload-dir | After unit test success/fail it will be removed */
                    $auth_filename = 'temporary_'. sanitize_file_name($_FILES['file']['name']);
                    $move_config_file = move_uploaded_file($_FILES['file']['tmp_name'], self::$configs->local_dir_save_key . $auth_filename);
                    if ($move_config_file) {
                        $key_file_path = self::$configs->local_dir_save_key . $auth_filename;
                    } else {
                        wp_send_json_error(['message' => __('Unable to save your config file. Please check your host upload folder permission!', 'ultimate-media-on-the-cloud')]);
                        wp_die();
                    }
                } else {
                    $key_file_path = $gg_data ? self::$configs->local_dir_save_key . $gg_data['auth_file'] : '';
                    /* Account is existed but unable to read key file path data | User has to re-upload key file */
                    if (!$key_file_path) {
                        wp_send_json_error(['message' => __('Account has corrupted data and unable to locate key file. Please re-upload your Authentication Key File.', 'ultimate-media-on-the-cloud')]);
                        wp_die();
                    }
                }

                $config = [
                    'keyFilePath' => $key_file_path,
                    'projectId' => $project_id,
                ];

                try {
                    $storage = new StorageClient($config);
                } catch (GoogleException $e) {
                    /* Check and remove temporary config file for Unit test */
                    if (isset($move_config_file)) {
                        @unlink($key_file_path);
                    }

                    wp_send_json_error(['message' => $e->getMessage()]);
                    wp_die();
                }

                # Make an authenticated API request (listing storage buckets)
                try {
                    $gcloud_buckets = $storage->buckets();
                } catch (GoogleException $e) {
                    /* Check and remove temporary config file for Unit test */
                    if (isset($move_config_file)) {
                        @unlink($key_file_path);
                    }

                    wp_send_json_error(['message' => $e->getMessage()]);
                    wp_die();
                }

                $buckets = [];
                try {
                    foreach ($gcloud_buckets as $bucket) {
                        /** @var Bucket $bucket */
                        $buckets[$bucket->name()] = $bucket->name();
                    }
                } catch (\Exception $e) {
                    $exception_result = json_decode($e->getMessage(), ARRAY_A);
                    $errors = [];
                    foreach ($exception_result['error']['errors'] as $error) {
                        $errors[] = $error['message'];
                    }

                    /* Check and remove temporary config file for Unit test */
                    if (isset($move_config_file)) {
                        @unlink($key_file_path);
                    }

                    wp_send_json_error(['message' => implode('<br>', $errors)]);
                    wp_die();
                }

                $html = self::renderTemplate('phprockets-ucm-google-cloud-storage-addon/buckets', [
                    'buckets' => $buckets
                ], false, false);

                /* Check and remove temporary config file for Unit test */
                if (isset($move_config_file)) {
                    @unlink($key_file_path);
                }

                wp_send_json_success(['html' => $html, 'message' => __('Buckets are loaded', 'ultimate-media-on-the-cloud')]);
            }
            wp_die();
        }

        /**
         * Perform an UnitTest if Configuration is corrected
         *
         * @param ItemIterator<Bucket> $gcloud_buckets
         * @return bool|WP_Error
         */
        private static function doUnitSettingsTest($gcloud_buckets)
        {
            $buckets = [];
            try {
                foreach ($gcloud_buckets as $bucket) {
                    /** @var Bucket $bucket */
                    $name = $bucket->name();
                    $buckets[$name] = $name;
                }
            } catch (\Exception $e) {
                $exception_result = json_decode($e->getMessage(), ARRAY_A);
                $WP_Error = new WP_Error();
                foreach ($exception_result['error']['errors'] as $error) {
                    $WP_Error->add('exception', $error['message']);
                }
                return $WP_Error;
            }

            return true;
        }

        /**
         * Register ajax URLs for AddOn
         *
         * @return mixed
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
         */
        public function registerEnqueueScript()
        {
            $vars = [];
            foreach ($this->labels['url'] as $url => $callback) {
                $vars[$callback] = admin_url('admin-ajax.php?action='. $this::$configs->plugin_url_prefix . $url);
            }
            wp_localize_script( 'phprockets-ucm-settings', 'phprockets_ucm_gcloud', $vars);
            $ucm_l10n = [
                '_require_all_fields' => __('Please complete all fields.', 'ultimate-media-on-the-cloud'),
                '_missing_project_id' => __('Please fill Project ID.', 'ultimate-media-on-the-cloud'),
                '_missing_auth_file' => __('Choose Authentication Key File.', 'ultimate-media-on-the-cloud'),
                '_save_settings_failed' => __('Unable to load Buckets with this Auth Key file.', 'ultimate-media-on-the-cloud'),
                '_choose_bucket' => __('Please choose Bucket.', 'ultimate-media-on-the-cloud'),
                '_required_bucket' => __('Please fill bucket name', 'ultimate-media-on-the-cloud'),
                '_confirm_remove' => __('Are you sure want to remove this account?\nThis can not be undone.', 'ultimate-media-on-the-cloud'),
            ];
            wp_localize_script( 'phprockets-ucm-settings', 'phprockets_gcloud_l10n', $ucm_l10n);
        }

        /**
         * Handle Push/Upload file to Cloud Storage Server
         *
         * @param $data
         * @return mixed
         */
        public function doPushAttachment($data)
        {
            $google_cloud_config = unserialize($this->activeAdapter['value']);
            $wp_upload_dir = wp_upload_dir();
            $file_upload_dir = explode('/', $data['file']);
            unset($file_upload_dir[count($file_upload_dir) - 1]);
            $file_upload_dir = implode('/', $file_upload_dir);

            $files = [str_replace($file_upload_dir .'/', '', $data['file'])]; //Main file
            if ($data['sizes']) {
                foreach ($data['sizes'] as $generated_file) {
                    $files[] = $generated_file['file'];
                }
            }

            $mime_type = isset($data['sizes']['thumbnail']['mime-type']) ? $data['sizes']['thumbnail']['mime-type'] : '';
            $root = $this->correctUploadRootDirForPush($google_cloud_config['cloud_path']);

            $storage = $this->initAdapter();
            $bucket = $storage->bucket($google_cloud_config['bucket']);

            try {
                foreach ($files as $file) {
                    $source_file = fopen($wp_upload_dir['basedir'] . "/{$file_upload_dir}/{$file}", 'r');
                    $bucket->upload($source_file, [
                        'name' => $root . $file_upload_dir .'/' . $file,
                        'predefinedAcl' => 'publicRead',
                        'metadata' => [
                            'ContentType' => $mime_type
                        ]
                    ]);
                }
            } catch (InvalidArgumentException $e) {
                wp_send_json_error(['message' => $e->getMessage()]);
                wp_die();
            }

            //Remove local file if option is Set TRUE
            if (!get_option(self::$configs->plugin_db_prefix .'option_keep_copy')) {
                apply_filters('ucm_host_cleanup', $data);
            }

            return true;
        }

        /**
         * Handle remove file on cloud
         *
         * @param $post_id
         * @return mixed
         */
        public function doRemoveAttachmentMedia($post_id)
        {
            $data = wp_get_attachment_metadata($post_id);
            $storage_metadata = get_post_meta($post_id, '_ucm_storage_metadata', true);
            if ($data && $storage_metadata) {
                $storage_metadata = unserialize($storage_metadata);
                if ($storage_metadata['account_id']) {
                    $adapter_account = PhpRockets_Model_Accounts::query([
                        'conditions' => [
                            'id' => $storage_metadata['account_id']
                        ]
                    ]);
                    if (!$adapter_account) {
                        return false;
                    }

                    $account_configs = unserialize($adapter_account['value']);
                    $config = [
                        'keyFilePath' => self::$configs->local_dir_save_key . $account_configs['auth_file'],
                        'projectId' => $account_configs['project_id'],
                    ];

                    try {
                        $storage = new StorageClient($config);
                    } catch (GoogleException $e) {
                        return false;
                    }

                    $bucket = $storage->bucket($storage_metadata['bucket']);

                    $file_upload_dir = explode('/', $data['file']);
                    unset($file_upload_dir[count($file_upload_dir) - 1]);
                    $file_upload_dir = implode('/', $file_upload_dir);

                    $files = [str_replace($file_upload_dir .'/', '', $data['file'])]; //Main file
                    if ($data['sizes']) {
                        foreach ($data['sizes'] as $generated_file) {
                            $files[] = $generated_file['file'];
                        }
                    }
                    $root = $this->correctUploadRootDirForPush($storage_metadata['path']);

                    try {
                        foreach ($files as $file) {
                            $bucket->object($root . $file_upload_dir .'/'. $file)->delete();
                        }
                    } catch (\Exception $e) {
                        return false;
                    }

                    return true;
                }
            }

            return true;
        }

        /**
         * Get file cloud storage url for media
         *
         * @param $url
         * @param $attachment_storage_meta
         * @return mixed
         */
        public function getAdapterStorageUrl($url, $attachment_storage_meta)
        {
            $wp_upload = wp_upload_dir();
            $gcloud_root = $this->correctUploadRootDirForPush($attachment_storage_meta['path']);
            $local_upload_path = $wp_upload['baseurl'] .'/';
            $gcloud_url = get_option(self::$configs->plugin_db_prefix .'option_scheme') .'://'. $this->labels['uri'] .'/'. $attachment_storage_meta['bucket'] .'/'. $gcloud_root;

            return str_replace($local_upload_path, $gcloud_url , $url);
        }

        /**
         * Verify mime-type of uploaded Authentication Key File
         *
         * @param $file $_FILES['file']
         * @return bool
         */
        public function verifyKeyFileUpload($file)
        {
            $allowed_mime_types = ['application/x-pkcs12', 'application/json'];
            return in_array($file['type'], $allowed_mime_types, false);
        }
    }
}

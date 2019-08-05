<?php if (!defined('ULTIMATE_MEDIA_PLG_LOADED')) { die('Zero Handle'); }
/**
 * Add-On for manage Amazon S3 Cloud Storage
 * Package: Ultimate Media On The Cloud
 * Plugin URI: https://wordpress.org/extend/plugins/ultimate-media-on-the-cloud-lite
 * Date: 23-May-2019
 */

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

if (!class_exists('PhpRockets_UCM_AmazonS3_AddOn')) {
    class PhpRockets_UCM_AmazonS3_AddOn extends PhpRockets_UCM_Addons
    {
        /**
         * AddOn Information
        **/
        public $labels = [
            'namespace' => __CLASS__,
            'title' => 'Amazon S3 Cloud Storage',
            'uri' => 's3.amazonaws.com',
            'url' => [
                '-s3-save' => 'saveSettings',
                '-s3-connect' => 'connectAws'
            ]
        ];

        /**
         * Register the AddOn into system
         *
         * @return void
         */
        public function register()
        {
            apply_filters('ucm_register_addons_vendor', 'aws-sdk-v3' . DIRECTORY_SEPARATOR . 'aws-autoloader.php');
            add_filter('ucm_aws_upload_media', [$this, 'doPushAttachment'], $this::$configs->default_order , 1);
            if (get_option(self::$configs->plugin_db_prefix .'advanced_delete_cloud_file')) {
                add_filter('ucm_aws_cloud_remove_file', [$this, 'doRemoveAttachmentMedia'], $this::$configs->default_order, 1);
            }
            add_filter('ucm_aws_cloud_file_url', [$this, 'getAdapterStorageUrl'], $this::$configs->default_order, 2);
        }

        /**
         * {@inheritDoc}
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
         * @return void
         */
        public function registerEnqueueScript()
        {
            $vars = [];
            foreach ($this->labels['url'] as $url => $callback) {
                $vars[$callback] = admin_url('admin-ajax.php?action='. $this::$configs->plugin_url_prefix . $url);
            }
            wp_localize_script( 'phprockets-ucm-settings', 'phprockets_ucm', $vars);
            $ucm_l10n = [
                '_require_all_fields' => __('Please complete all fields.', 'ultimate-media-on-the-cloud'),
                '_missing_key' => __('Please fill App Key / Secret and choose your Region', 'ultimate-media-on-the-cloud'),
                '_required_bucket' => __('Please fill bucket name', 'ultimate-media-on-the-cloud'),
                '_required_region' => __('Please choose bucket region', 'ultimate-media-on-the-cloud'),
                '_required_cors' => __('You have to put CORS settings to save!', 'ultimate-media-on-the-cloud'),
                '_choose_bucket' => __('Please choose a bucket', 'ultimate-media-on-the-cloud'),
                '_confirm_remove' => __('Are you sure want to remove this account?\nThis can not be undone.', 'ultimate-media-on-the-cloud'),
            ];
            wp_localize_script( 'phprockets-ucm-settings', 'phprockets_aws_l10n', $ucm_l10n);
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
            $amz_account = PhpRockets_Model_Accounts::query([
                'conditions' => [
                    'storage_adapter' => 'aws',
                    'is_default' => 1
                ],
                'limit' => 1
            ]);

            $amz_data = [];
            if ($amz_account) {
                $amz_data = unserialize($amz_account['value']);
            }
            if (!$amz_account) {
                $errors[] = __('Set up Amazon S3 Account by the form below', 'ultimate-media-on-the-cloud');
            }

            $form = [
                'div' => [
                    'class' => 'panel-body',
                    'id' => 'phprockets-ucm-amazons3-addon'
                ],
                'attr' => [
                    'id' => 'frm-ucm-amazon-s3',
                    'onSubmit' => 'return false;'
                ],
                'fields' => [],
                'submit' => [
                    'label' => __('Save Settings', 'ultimate-media-on-the-cloud'),
                    'attr' => [
                        'href' => 'javascript:;',
                        'id' => 'btn-amz-setting',
                        'class' => 'button is-info'
                    ]
                ]
            ];
            $form['fields'][] = [
                'label' => __('App Key', 'ultimate-media-on-the-cloud'),
                'type'  => 'text',
                'icon' => 'dashicons dashicons-post-status',
                'attr' => [
                    'name' => 'data[app_key]',
                    'value' => $amz_data ? $amz_data['app_key'] : '',
                    'placeholder' => __('Amazon S3 Key', 'ultimate-media-on-the-cloud'),
                    'class' => 'input',
                    'id' => 'app-key'
                ]
            ];

            $form['fields'][] = [
                'label' => 'App Secret',
                'type' => 'text',
                'icon' => 'dashicons dashicons-lock',
                'attr' => [
                    'name' => 'data[app_secret]',
                    'value' => $amz_data ? $amz_data['app_secret'] : '',
                    'placeholder' => __('Amazon S3 Secret Key', 'ultimate-media-on-the-cloud'),
                    'class' => 'input',
                    'id' => 'app-secret',
                ],
                'help-text' => __('Learn how to obtain Amazon S3 App Key & Secret', 'ultimate-media-on-the-cloud') .
                    ' <a href="'. $this::$configs->getUcmConfig('aws_guide_get_credential') .'" class="button is-link is-small" target="_blank">'. __('How to get App Key & Secret', 'ultimate-media-on-the-cloud') .'</a>'.
                    ' <a href="'. $this::$configs->getUcmConfig('aws_console') .'" class="button is-primary is-small" target="_blank">'. __('Amazon Console', 'ultimate-media-on-the-cloud') .'</a>'
            ];

            $form['fields'][] = [
                'label' => __('Connect', 'ultimate-media-on-the-cloud'),
                'type' => 'anchor',
                'attr' => [
                    'href' => 'javascript:;',
                    'icon' => 'dashicons dashicons-plugins-checked icon-button',
                    'class' => 'button is-link',
                    'id' => 'btn-amz-connect'
                ]
            ];

            $bucket_arr = [];
            if ($amz_data) {
                $aws_s3 = new S3Client([
                    'version' => 'latest',
                    'region' => $amz_data['region'],
                    'scheme' => 'http',
                    'credentials' => array(
                        'key' => $amz_data['app_key'],
                        'secret' => $amz_data['app_secret']
                    ),
                    'curl.options' => [CURLOPT_VERBOSE => true]
                ]);

                try {
                    $buckets = $aws_s3->listBuckets();
                    foreach ($buckets['Buckets'] as $bucket) {
                        $bucket_arr[$bucket['Name']] = $bucket['Name'];
                    }
                } catch (S3Exception $e) {
                    $errors[] = $e->getAwsErrorMessage();
                }
            }

            $form['fields'][] = [
                'label' => __('Default Bucket', 'ultimate-media-on-the-cloud'),
                'type' => 'select',
                'icon' => 'dashicons dashicons-lock',
                'value' => array_merge(['' => '-Choose Bucket-'], $bucket_arr),
                'selected' => $amz_data ? $amz_data['bucket'] : '',
                'attr' => [
                    'name' => 'data[bucket]',
                    'id' => 'app-bucket',
                ],
                'help-text' => __('Click Connect button above to fetch available Buckets.', 'ultimate-media-on-the-cloud')
            ];

            $form['fields'][] = [
                'label' => __('Cloud Path', 'ultimate-media-on-the-cloud'),
                'type' => 'text',
                'icon' => 'dashicons dashicons-archive',
                'attr' => [
                    'name' => 'data[cloud_path]',
                    'value' => $amz_data ? $amz_data['cloud_path'] : '/',
                    'placeholder' => __('Amazon S3 Cloud Path', 'ultimate-media-on-the-cloud'),
                    'class' => 'input',
                    'id' => 'cloud-path',
                ],
                'help-text' => __('Target folder at cloud storage you want to save media to.', 'ultimate-media-on-the-cloud')
            ];

            $form['fields'][] = [
                'label' => __('Region', 'ultimate-media-on-the-cloud'),
                'type' => 'select',
                'value' => array_merge(['' => '-Choose Region-'], $this->getAvailableRegions()),
                'selected' => $amz_data ? $amz_data['region'] : '',
                'icon' => 'dashicons dashicons-flag',
                'attr' => [
                    'name' => 'data[region]',
                    'id' => 'app-region',
                ],
                'help-text' => __('Important: Please choose the correct region which you registered at Amazon S3 Service. If you use a region other than the US East (N. Virginia) endpoint to create a bucket, you must set the LocationConstraint bucket parameter to the same region. <a href="'. $this::$configs->getUcmConfig('aws_guide_list_regions') .'" target="_blank"> Available Regions</a>', 'ultimate-media-on-the-cloud')
            ];

            $form['fields'][] = [
                'label' => __('Storage Class', 'ultimate-media-on-the-cloud'),
                'type' => 'select',
                'icon' => 'dashicons dashicons-groups',
                'value' => array_merge(['' => __('-Choose-', 'ultimate-media-on-the-cloud')], $this->getStorageClasses()),
                'selected' => $amz_data ? $amz_data['storage_class'] : 'STANDARD',
                'attr' => [
                    'name' => 'data[storage_class]',
                    'id' => 'storage-class',
                ],
                'help-text' => __('Storage Class: You can read the documentation about the', 'ultimate-media-on-the-cloud') .' <a href="'. $this::$configs->getUcmConfig('aws_guide_storage_class') .'" target="_blank">'. __('Amazon S3 Storage Class', 'ultimate-media-on-the-cloud') .'</a>'
            ];

            return $this::renderTemplate('common/_form', ['form' => $form, 'errors' => $errors, 'messages' => $messages], false);
        }

        /**
         * Handle saving setting in backend
         *
         * @return void
         */
        public static function saveSettings()
        {
            if (self::isPost()) {
                $data = self::getPost('data');
                //TODO Tran add Validation $data
                $unit_test = self::doUnitSettingsTest($data);
                if (is_wp_error($unit_test)) {
                    /** @var WP_Error $unit_test */
                    wp_send_json_error(['message' => $unit_test->get_error_message()]);
                    wp_die();
                }

                $amz_account = PhpRockets_Model_Accounts::query([
                    'conditions' => [
                        'storage_adapter' => 'aws'
                    ],
                    'limit' => 1
                ]);
                if (!$amz_account) {
                    $create_account = PhpRockets_Model_Accounts::create([
                        'storage_adapter' => 'aws',
                        'name' => 'Default Aws Account',
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
                    $update_account = PhpRockets_Model_Accounts::update($amz_account['id'], [
                        'value' => serialize($data),
                        'updated_at' => current_time('mysql', 1)
                    ]);

                    if ($update_account) {
                        wp_send_json_success(['message' => __('Account information updated.', 'ultimate-media-on-the-cloud')]);
                    } else {
                        wp_send_json_error(['message' => __('An error occurred while updating account. Try again later!', 'ultimate-media-on-the-cloud')]);
                    }
                }
            } else {
                wp_send_json_error(['message' => __('Invalid request', 'ultimate-media-on-the-cloud')]);
            }

            wp_die();
        }

        /**
         * {@inheritDoc}
         */
        public function doPushAttachment($data)
        {
            $aws_config = unserialize($this->activeAdapter['value']);
            $aws_s3 = new S3Client([
                'version' => 'latest',
                'region' => $aws_config['region'],
                'scheme' => get_option(self::$configs->plugin_db_prefix .'option_scheme'),
                'credentials' => [
                    'key' => $aws_config['app_key'],
                    'secret' => $aws_config['app_secret']
                ],
                'curl.options' => [CURLOPT_VERBOSE => true]
            ]);

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

            $mine_type = isset($data['sizes']['thumbnail']['mime-type']) ? $data['sizes']['thumbnail']['mime-type'] : '';
            $root = $this->correctUploadRootDirForPush($aws_config['cloud_path']);

            try {
                foreach ($files as $file) {
                    $aws_s3->putObject([
                        'Bucket'       => $aws_config['bucket'],
                        'Key'          => $root . $file_upload_dir .'/' . $file,
                        'SourceFile'   => $wp_upload_dir['basedir'] . "/{$file_upload_dir}/{$file}",
                        'ACL'          => 'public-read',
                        'StorageClass' => $aws_config['storage_class'],
                        'ContentType'  => $mine_type,
                        'CacheControl' => 'max-age=31536000',
                    ]);
                }
            } catch (S3Exception $e) {
                return new WP_Error('exception', $e->getMessage());
            }

            /* Remove local file if option is Set TRUE */
            if (!get_option(self::$configs->plugin_db_prefix .'option_keep_copy')) {
                apply_filters('ucm_host_cleanup', $data);
            }

            return true;
        }

        /**
         * {@inheritDoc}
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

                    $aws_configs = unserialize($adapter_account['value']);
                    $aws_s3 = new S3Client([
                        'version' => 'latest',
                        'region' => $aws_configs['region'],
                        'scheme' => 'https',
                        'credentials' => [
                            'key' => $aws_configs['app_key'],
                            'secret' => $aws_configs['app_secret']
                        ],
                        'curl.options' => [CURLOPT_VERBOSE => true]
                    ]);

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

                    $objects = [];
                    foreach ($files as $file) {
                        $objects[] = [
                            'Key' => $root . $file_upload_dir .'/'. $file
                        ];
                    }
                    try {
                        $aws_s3->deleteObjects([
                            'Bucket' => $storage_metadata['bucket'],
                            'Delete' => [
                                'Objects' => $objects
                            ],
                        ]);
                    } catch (S3Exception $e) {
                        return false;
                    }

                    return true;
                }
            }

            return true;
        }

        /**
         * {@inheritDoc}
         */
        public function getAdapterStorageUrl($url, $attachment_storage_meta)
        {
            $wp_upload = wp_upload_dir();
            $aws_root = $this->correctUploadRootDirForPush($attachment_storage_meta['path']);
            $local_upload_path = $wp_upload['baseurl'] .'/';
            $aws_url = get_option(self::$configs->plugin_db_prefix .'option_scheme') .'://'. $attachment_storage_meta['bucket'] .'.'. $this->labels['uri'] .'/'. $aws_root;

            return str_replace($local_upload_path, $aws_url , $url);
        }

        /**
         * Test a config is success connect to AWS or not
         *
         * @return void
         * @throws Exception
         */
        public static function connectAws()
        {
            if (self::isPost()) {
                $data = self::getPost('data');
                /* Perform validation inputs */
                $validation = new Validation();
                $validation->validation_rules([
                    'app_key' => 'required',
                    'app_secret' => 'required',
                    'region' => 'required'
                ]);
                $validated = $validation->run($data);
                if ($validated === false) {
                    $error_messages = $validation->get_errors_array();
                    wp_send_json_error(['message' => implode('<br>', $error_messages)]);
                    wp_die();
                }


                $app_key = $data['app_key'];
                $app_secret = $data['app_secret'];
                $region = $data['region'];

                $aws_s3 = new S3Client([
                    'version' => 'latest',
                    'region' => $region,
                    'scheme' => 'http',
                    'credentials' => array(
                        'key' => $app_key,
                        'secret' => $app_secret
                    ),
                    'curl.options' => [CURLOPT_VERBOSE => true]
                ]);

                try {
                    $buckets = $aws_s3->listBuckets();
                } catch (S3Exception $e) {
                    wp_send_json_error(['message' => $e->getAwsErrorMessage()]);
                    wp_die();
                }
                $buckets = $buckets['Buckets'];

                $html = self::renderTemplate('phprockets-ucm-amazons3-addon/buckets', [
                    'buckets' => $buckets
                ], false, false);
                wp_send_json_success(['html' => $html, 'message' => __('Buckets are loaded', 'ultimate-media-on-the-cloud')]);
            } else {
                wp_send_json_error(['message' => __('Invalid request', 'ultimate-media-on-the-cloud')]);
            }

            wp_die();
        }

        /**
         * Get available Cloud Region Servers
         *
         * @return array
         * */
        private function getAvailableRegions()
        {
            return [
                'us-east-2' => 'US East (Ohio) - (us-east-2)',
                'us-east-1' => 'US East (N. Virginia) - (us-east-1)',
                'us-west-1' => 'US West (N. California) - (us-west-1)',
                'us-west-2' => 'US West (Oregon) - (us-west-2)',
                'ca-central-1' => 'Canada (Central) - (ca-central-1)',
                'ap-south-1' => 'Asia Pacific (Mumbai) - (ap-south-1)',
                'ap-northeast-2' => 'Asia Pacific (Seoul) - (ap-northeast-2)',
                'ap-southeast-1' => 'Asia Pacific (Singapore) - (ap-southeast-1)',
                'ap-southeast-2' => 'Asia Pacific (Sydney) - (ap-southeast-2)',
                'ap-northeast-1' => 'Asia Pacific (Tokyo) - (ap-northeast-1)',
                'cn-northwest-1' => 'China (Ningxia) - (cn-northwest-1)',
                'eu-central-1' => 'EU (Frankfurt) - (eu-central-1)',
                'eu-wes' => 'EU (Ireland) - (eu-wes)',
                'eu-west-2' => 'EU (London) - (eu-west-2)',
                'sa-east-1' => 'South America (São Paulo) - (sa-east-1)'
            ];
        }

        /**
         * Get available S3 Storage Classes
         *
         * @return array
         * */
        private function getStorageClasses()
        {
            return [
                'STANDARD' => 'Standard',
                'STANDARD_IA' => 'Standard - Infrequent Access',
                'REDUCED_REDUNDANCY' => 'Reduced Redundancy'
            ];
        }

        /**
         * Perform an UnitTest if Configuration is corrected
         *
         * @param $data
         * @return bool|WP_Error
         */
        private static function doUnitSettingsTest($data)
        {
            $aws_s3 = new S3Client([
                'version' => 'latest',
                'region' => $data['region'],
                'scheme' => isset($data['scheme']) ? $data['scheme'] : 'https',
                'credentials' => [
                    'key' => $data['app_key'],
                    'secret' => $data['app_secret']
                ],
                'curl.options' => [CURLOPT_VERBOSE => true]
            ]);

            $file = ULTIMATE_MEDIA_PLG_DIR .'/assets/test.txt';
            $mine_type = 'application/octet-stream';

            try {
                $aws_s3->putObject([
                    'Bucket'       => $data['bucket'],
                    'Key'          => 'test.txt',
                    'SourceFile'   => $file,
                    'ACL'          => 'public-read',
                    'StorageClass' => $data['storage_class'],
                    'ContentType'  => $mine_type,
                    'CacheControl' => 'max-age=31536000',
                ]);
            } catch (S3Exception $e) {
                $message = $e->getMessage();
                if (false !== strpos(strtolower($message), 'you are using the correct region for this bucket')) {
                    try {
                        $bucket_location = $aws_s3->getBucketLocation([
                            'Bucket' => $data['bucket']
                        ]);
                        if ($bucket_location) {
                            $message .= ' |<br/> Your bucket location setting is : '. $bucket_location['LocationConstraint'];
                        }
                    } catch (S3Exception $e2) {}
                }
                return new WP_Error('exception', $message);
            }

            $aws_s3->deleteObject([
                'Bucket' => $data['bucket'],
                'Key' => 'test.txt'
            ]);

            return true;
        }
    }
}
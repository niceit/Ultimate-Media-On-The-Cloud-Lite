<?php if (!defined('ULTIMATE_MEDIA_PLG_LOADED')) { die('Zero Handle'); }
/**
 * Handling for Wordpress Attachment in GET response
 * Package: Ultimate Media On The Cloud
 * Plugin URI: https://wordpress.org/extend/plugins/ultimate-media-on-the-cloud-lite
 * Date: 22-May-2019
 */
if (!class_exists('PhpRockets_UltimateMedia_Attachment')) {
    class PhpRockets_UltimateMedia_Attachment extends PhpRockets_UltimateMedia
    {
        public function hook()
        {
            add_action('wp_update_attachment_metadata', [$this, 'updateAttachmentMeta'], $this::$configs->default_order, 2);
        }

        public function userEndHook()
        {
            add_action('wp_get_attachment_url', [$this, 'getAttachmentUrl'], $this::$configs->default_order, 2);
            add_filter('wp_calculate_image_srcset', [$this, 'calculateImageSrcset'], $this::$configs->default_order, 5);
            add_filter('ucm_storage_media_url_rewrite', [$this, 'applyCloudStorageUrl'], $this::$configs->default_order, 3);

            /**
             * If option remove Cloud Media is set TRUE
             * then remove the cloud media as well when local media data get removed
            */
            if (get_option(self::$configs->plugin_db_prefix .'advanced_delete_cloud_file')) {
                add_action('delete_attachment', [$this, 'deleteAttachment'], $this::$configs->default_order, 1);
            }
        }

        public static function getAttachmentUrl($url, $post_id)
        {
            $attachment_storage = get_post_meta($post_id, '_ucm_storage_adapter', true);
            if ($attachment_storage) {
                $storage_metadata = get_post_meta($post_id, '_ucm_storage_metadata', true);
                $url = apply_filters('ucm_storage_media_url_rewrite', $url, $attachment_storage, unserialize($storage_metadata));
            }

            return $url;
        }

        /**
         * WordPress Responsive Images
         *
         * @param $sources
         * @param $size_array
         * @param $image_src
         * @param $image_meta
         * @param $post_id
         * @return mixed
         */
        public static function calculateImageSrcset($sources, $size_array, $image_src, $image_meta, $post_id)
        {
            $attachment_storage = get_post_meta($post_id, '_ucm_storage_adapter', true);
            if ($attachment_storage) {
                $storage_metadata = get_post_meta($post_id, '_ucm_storage_metadata', true);
                $storage_metadata = unserialize($storage_metadata);
                foreach ($sources as $size => $source) {
                    $sources[$size]['url'] = apply_filters('ucm_storage_media_url_rewrite', $source['url'], $attachment_storage, $storage_metadata);
                }
            }

            return $sources;
        }

        /**
         * Hook when update attachment metadata
         *
         * @param $data
         * @param $post_id
         * @return mixed
         */
        public function updateAttachmentMeta($data, $post_id)
        {
            $option_post_types = get_option(self::$configs->plugin_db_prefix .'post_types');
            $option_post_types = $option_post_types ? explode(',', $option_post_types) : '';

            $option_file_types = get_option(self::$configs->plugin_db_prefix .'file_types');
            $option_file_types = $option_file_types ? explode(',', $option_file_types) : '';

            if ($this->activeAdapter && class_exists($this->activeAdapter['addon_class'])) {
                /**
                 * Check if post type filter is enabled
                 * Ignore if requested post type is not in the filters
                 **/
                if ($option_post_types && $this->getCurrentPagePostType() && !in_array($this->getCurrentPagePostType(), $option_post_types, false)) {
                    return $data;
                }

                if ($option_file_types && !in_array($this->_parseFileExtension($data['file']), $option_file_types, false)) {
                    return $data;
                }

                $cloud_account_config = unserialize($this->activeAdapter['value']);
                if ($this->activeAdapter['storage_adapter']) {
                    $result = apply_filters("ucm_{$this->activeAdapter['storage_adapter']}_upload_media", $data);
                } else {
                    $result = new WP_Error('exception', __('Invalid cloud storage account configuration', 'ultimate-media-on-the-cloud'));
                }

                if (is_wp_error($result)) {
                    wp_delete_attachment($post_id, true);
                    /**@var WP_Error $result*/
                    wp_send_json_error(['message' => $this->WpErrorsToHTML($result)]);
                    wp_die();
                }

                $cloud_storage_data = [
                    'account_id' => $this->activeAdapter['id'],
                    'path' => $cloud_account_config['cloud_path'],
                    'bucket' => $cloud_account_config['bucket'],
                    'region' => $cloud_account_config['region']
                ];
                update_post_meta($post_id, '_ucm_storage_adapter', $this->activeAdapter['storage_adapter']);
                update_post_meta($post_id, '_ucm_storage_metadata', serialize($cloud_storage_data));
            }

            return $data;
        }

        /**
         * Get Cloud Src URL
         *
         * @param $url
         * @param $storage_adapter
         * @param $attachment_metadata
         * @return mixed
         */
        public function applyCloudStorageUrl($url, $storage_adapter, $attachment_metadata)
        {
            return apply_filters("ucm_{$storage_adapter}_cloud_file_url", $url, $attachment_metadata);
        }

        /**
         * Delete Wordpress Attachment
         *
         * @param $post_id
         * @return mixed
         */
        public function deleteAttachment($post_id)
        {
            $storage_metadata = get_post_meta($post_id, '_ucm_storage_adapter', true);
            if ($storage_metadata) {
                if (get_option(self::$configs->plugin_db_prefix .'advanced_delete_cloud_file')){
                    apply_filters("ucm_{$storage_metadata}_cloud_remove_file", $post_id);
                }

                delete_post_meta($post_id, '_ucm_storage_adapter');
                delete_post_meta($post_id, '_ucm_storage_metadata');
            }

            return $post_id;
        }

        /**
         * Clean up (remove) host media base on metadata
         * @param array $attachment_metadata
         */
        public static function cleanUpHostMedia($attachment_metadata)
        {
            $wp_upload_dir = wp_upload_dir();
            $files = [$attachment_metadata['file']];
            $file_upload_dir = explode('/', $attachment_metadata['file']);
            unset($file_upload_dir[count($file_upload_dir) - 1]);
            $file_upload_dir = implode('/', $file_upload_dir);

            if ($attachment_metadata['sizes']) {
                foreach ($attachment_metadata['sizes'] as $generated_file) {
                    $files[] = $file_upload_dir .'/'. $generated_file['file'];
                }
            }

            foreach ($files as $file) {
                @unlink($wp_upload_dir['basedir'] . "/{$file}");
            }
        }

        /**
         * Return extension base on a file URL
         *
         * @param string|array $url
         * @return string
         */
        private function _parseFileExtension($url)
        {
            $url = explode('.', $url);
            return $url[count($url) - 1];
        }
    }
}
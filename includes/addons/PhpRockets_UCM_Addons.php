<?php
/**
 * Add-On for manage Amazon S3 Cloud Storage
 * Package: Ultimate Media On The Cloud
 * Plugin URI: https://wordpress.org/extend/plugins/ultimate-media-on-the-cloud-lite
 * Date: 24-May-2019
 */
if (!class_exists('PhpRockets_UCM_Addons'))
{
    abstract class PhpRockets_UCM_Addons extends PhpRockets_UltimateMedia
    {
        /**
         * Correct the path prefix before going to upload items to the Cloud
         *
         * @param $root
         * @return string
         */
        public function correctUploadRootDirForPush($root)
        {
            if ($root === '/') {
                return '';
            }

            return substr($root,1) .'/';
        }

        /**
         * Register AddOn handler
         *
         * @return void
         */
        abstract public function register();

        /**
         * Generate form setting page
         *
         * @return mixed
         */
        abstract public function buildForm();

        /**
         * Handle POST data to save into Database
         *
         * @return void|mixed
         */
        public static function saveSettings() {}

        /**
         * Register ajax URLs for AddOn
         *
         * @return mixed
         */
        abstract public function registerAjaxUrlHook();

        /**
         * Handle Push/Upload file to Cloud Storage Server
         *
         * @param $data
         * @param $post_id
         * @return mixed
         */
        abstract public function doPushAttachment($data);

        /**
         * Handle remove file on cloud
         *
         * @param $post_id
         * @return mixed
         */
        abstract public function doRemoveAttachmentMedia($post_id);

        /**
         * Get file cloud storage url for media
         *
         * @param $url
         * @param $attachment_storage_meta
         * @return mixed
         */
        abstract public function getAdapterStorageUrl($url, $attachment_storage_meta);
    }
}
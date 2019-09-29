<?php
return [
    'current_version' => '1.50.5',
    'current_release' => 'Lite',
    'online_document_url' => 'http://ucm.phprockets.com/documentations/index.html',
    'plugin_url' => 'https://wordpress.org/extend/plugins/ultimate-media-on-the-cloud-lite/',
    'plugin_premium_upgrade_url' => 'https://www.phprockets.com/ultimate-media-on-the-cloud-pro/',
    'support_email' => 'tranit1209@gmail.com',
    'phprockets_ucm_news_url' => 'http://ucm.phprockets.com/news/lite.txt',

    'aws_console' => 'https://aws.amazon.com/console/',
    'aws_guide_get_credential' => 'https://docs.aws.amazon.com/general/latest/gr/aws-sec-cred-types.html',
    'aws_guide_list_regions' => 'https://docs.aws.amazon.com/general/latest/gr/rande.html',
    'aws_guide_storage_class' => 'https://aws.amazon.com/s3/storage-classes/',
    'gcloud_guide_console' => 'https://console.cloud.google.com',
    'digital_ocean_console' => 'https://cloud.digitalocean.com/spaces',

    //Register Builtin AddOns
    'registered_addons' => [
        'builtin' => [
            'aws' => 'AmazonS3',
            'google_cloud' => 'GoogleCloudStorage'
        ]
    ],

    //URL prefix for all plugin sub pages
    'plugin_url_prefix' => 'phpR-ucm',

    //DB Options prefix and tables
    'plugin_db_prefix' => 'phpr_ucm_',

    //Menu Icon
    'plugin_icon_file' => '/assets/images/ucm-icon.png',

    'external_addons_requirements' => [
        'digitalocean' => '1.2'
    ]
];
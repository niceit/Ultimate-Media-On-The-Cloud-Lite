<?php if (!defined('ULTIMATE_MEDIA_PLG_LOADED')) { die('Zero Handle'); }
return [
    'php_version' => [
        'operator' => '>=',
        'value' => '5.6',
        'message' => 'Your PHP version need to have at least 5.6! Your server PHP version <b>'. PHP_VERSION .'</b>'
    ],
    'wp_version' => [
        'operator' => '>=',
        'value' => '4.0',
        'message' => 'Ultimate Cloud Media plugin required Wordpress version 4.0 or higher.'
    ]
];
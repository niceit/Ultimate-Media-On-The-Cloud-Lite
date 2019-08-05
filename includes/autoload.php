<?php if (!defined('ULTIMATE_MEDIA_PLG_LOADED')) { die('Zero Handle'); }
$auto_loads = include __DIR__ .'/requires/init.autoload.php';
foreach ($auto_loads as $auto_load) {
    require_once __DIR__ . '/'. "{$auto_load}.php";
}
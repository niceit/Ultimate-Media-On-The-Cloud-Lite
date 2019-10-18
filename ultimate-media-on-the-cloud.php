<?php
/*
Plugin Name: Ultimate Media On The Cloud (Lite)
Plugin URI: https://wordpress.org/extend/plugins/ultimate-media-on-the-cloud-lite
Description: Migrate, move and mange wordpress media on the Cloud Storage Platforms like Amazon S3, Google Cloud and other platforms... Help you to save hosting space/bandwidth and faster delivery the site assets/medias. Support encrypting, optimization, CDN and CloudFront.
Author: PhpRockets Team
Version: 1.51.0
Author URI: https://www.phprockets.com
Network: True
Text Domain: ultimate-media-on-the-cloud
*/

/**
Copyright (C) 2019 PhpRocketsTeam . All rights reserved.

Released under the GPLv3 license
http://www.gnu.org/licenses/gpl-3.0.html

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
 **/

include_once ABSPATH .'wp-admin/includes/plugin.php';
/* If user has installed Pro build of Ultimate Media On The Cloud simply de-active it in case prevent conflict */
if (is_plugin_active('ultimate-media-on-the-cloud/ultimate-media-on-the-cloud.php')) {
    deactivate_plugins(plugin_dir_path(__DIR__) .'ultimate-media-on-the-cloud/ultimate-media-on-the-cloud.php');
}

define('ULTIMATE_MEDIA_PLG_LOADED', true);
define('ULTIMATE_MEDIA_PLG_FILE', __FILE__);
define('ULTIMATE_MEDIA_PLG_DIR', rtrim(plugin_dir_path(__FILE__), '/'));
if (is_plugin_active(plugin_basename(__FILE__))) {
    require_once __DIR__ .'/includes/autoload.php';

    //Start the plugin functionally
    $PhpRocketsUcm = new PhpRockets_UltimateMedia();
    if ($PhpRocketsUcm->isPassCheckRequirement() && $PhpRocketsUcm->isInitialProperly()) {
        $PhpRocketsUcm->run();
    }
}

require_once ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/classes/PhpRockets_UltimateMedia_Install.php';
register_activation_hook(__FILE__, [PhpRockets_UltimateMedia_Install::class, 'whileActivation']);
register_uninstall_hook(__FILE__, [PhpRockets_UltimateMedia_Install::class, 'whileUnInstall']);
<div class="columns mt10">
    <div class="ucm-settings column is-three-fifths">
        <?php include ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/tpl/common/header.php' ?>
        <div class="ucm-settings-body box column is-full has-background-white relative">
            <?php echo $loading_box ?>
            <div class="tabs is-boxed" style="margin-bottom: 5px;">
                <ul style="border-bottom: none;">
                    <li <?php if (!$ucm_tab) : ?>class="is-active"<?php endif ?>>
                        <a class="ucm-settings-nav" href="javascript:;" data-target="ucm-general">
                            <span class="icon is-small"><i class="fas fa-cogs" aria-hidden="true"></i></span>
                            <span><?php echo $title ?></span>
                        </a>
                    </li>
                    <li <?php if ($ucm_tab === 'storage-accounts') : ?>class="is-active"<?php endif ?>>
                        <a class="ucm-settings-nav" href="javascript:;" data-target="ucm-storage-accounts">
                            <span class="icon is-small"><i class="fas fa-database" aria-hidden="true"></i></span>
                            <span><?php _e('Storage Accounts', 'ultimate-media-on-the-cloud') ?></span>
                        </a>
                    </li>
                    <li <?php if ($ucm_tab === 'advanced') : ?>class="is-active"<?php endif ?>>
                        <a class="ucm-settings-nav" href="javascript:;" data-target="ucm-advanced">
                            <span class="icon is-small"><i class="fas fa-cog" aria-hidden="true"></i></span>
                            <span><?php _e('Advanced', 'ultimate-media-on-the-cloud') ?></span>
                        </a>
                    </li>
                    <li <?php if ($ucm_tab === 'help') : ?>class="is-active"<?php endif ?>>
                        <a class="ucm-settings-nav" href="javascript:;" data-target="ucm-help">
                            <span class="icon is-small"><i class="fas fa-question-circle" aria-hidden="true"></i></span>
                            <span><?php _e('Help', 'ultimate-media-on-the-cloud') ?></span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- TAB SETTINGS GENERAL -->
            <?php echo $form; ?>
            <!-- END TAB SETTINGS GENERAL -->

            <div class="panel-body" id="ucm-storage-accounts">
                <div class="ucm-accounts-tab-links">
                    <?php if ($addons) : ?>
                        <?php $index = 0 ?>
                        <?php foreach ($addons as $addon) : ?>
                            <a <?php if ($index === 0) : ++$index ?> class="is-active"<?php endif ?> href="javascript:;" data-target="<?php echo strtolower(str_replace([' ', '_'], '-', get_class($addon))) ?>" data-id="<?php echo strtolower(str_replace([' ', '_'], '-', get_class($addon))) ?>">
                                <?php echo $addon->labels['title'] ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif ?>
                </div>
                <!-- AddOn TABS -->
                <?php if ($addons) : ?>
                    <?php foreach ($addons as $addon) : ?>
                        <?php
                        /** @var PhpRockets_UCM_Addons $addon */
                        echo $addon->buildForm();
                        ?>
                    <?php endforeach; ?>
                <?php endif ?>
                <!-- END AddOn TABS -->
            </div>

            <!-- TAB SETTINGS ADVANCED -->
            <?php echo $form_advanced; ?>
            <!-- END TAB SETTINGS ADVANCED -->

            <!-- TAB HELP -->
            <div class="panel-body" id="ucm-help">
                <aside class="message is-warning">
                    <div class="message-body message-small">
                        <?php _e('You can read ', 'ultimate-media-on-the-cloud') ?>
                        <a href="<?php echo $ucm::$configs->getUcmConfig('online_document_url') ?>" target="_blank"><?php _e('Online Documentation', 'ultimate-media-on-the-cloud') ?></a>
                    </div>
                </aside>
                <aside class="message is-warning">
                    <div class="message-body message-small">
                        <?php _e('Post your question to the support forum ', 'ultimate-media-on-the-cloud') ?>
                        <a href="<?php echo $ucm::$configs->getUcmConfig('plugin_url') ?>" target="_blank"><?php _e('Plugin page', 'ultimate-media-on-the-cloud') ?></a>
                    </div>
                </aside>
                <aside class="message is-success">
                    <div class="message-body message-small">
                        <?php _e('Submit your question to us ', 'ultimate-media-on-the-cloud') ?>
                        <a href="<?php echo admin_url() .'admin.php?page=' . $ucm::$configs->getUcmConfig('plugin_url_prefix') . '-support' ?>"><?php _e('Contact Us', 'ultimate-media-on-the-cloud') ?></a>
                    </div>
                </aside>
                <p align="right">
                    <a class="button is-success" href="<?php echo $ucm::$configs->getUcmConfig('plugin_premium_upgrade_url') ?>"><?php _e('Upgrade Pro Version', 'ultimate-media-on-the-cloud') ?></a>
                </p>
            </div>
            <!-- END TAB HELP -->
            <strong><i><?php _e('Plugin Version '. $ucm::$configs->getUcmConfig('current_version'), 'ultimate-media-on-the-cloud') ?> - Wordpress Version: <?php echo $wp_version ?></i></strong>
        </div>
    </div>
    <?php include ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/tpl/common/news.php' ?>
</div>
<?php include ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/tpl/common/toast-message.php' ?>

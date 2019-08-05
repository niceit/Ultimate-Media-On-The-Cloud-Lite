<div class="columns mt10">
    <div class="ucm-settings column is-three-fifths">
        <?php include ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/tpl/common/header.php' ?>
        <div class="ucm-settings-body box column is-full has-background-white relative">
            <?php echo $loading_box ?>
            <div class="tabs" style="margin-bottom: 5px;">
                <ul style="border-bottom: none;">
                    <li <?php if (!$ucm_tab) : ?>class="is-active"<?php endif ?>><a class="ucm-settings-nav" href="javascript:;" data-target="ucm-general"><?php echo $title ?></a></li>
                    <?php if ($addons) : ?>
                        <?php foreach ($addons as $addon) : ?>
                            <li><a class="ucm-settings-nav" href="javascript:;" data-target="<?php echo strtolower(str_replace([' ', '_'], '-', get_class($addon))) ?>" data-id="<?php echo strtolower(str_replace([' ', '_'], '-', get_class($addon))) ?>"><?php echo $addon->labels['title'] ?></a></li>
                        <?php endforeach; ?>
                    <?php endif ?>
                <li <?php if ($ucm_tab === 'advanced') : ?>class="is-active"<?php endif ?>><a class="ucm-settings-nav" href="javascript:;" data-target="ucm-advanced"><?php _e('Advanced', 'ultimate-media-on-the-cloud') ?></a></li>
                <li <?php if ($ucm_tab === 'help') : ?>class="is-active"<?php endif ?>><a class="ucm-settings-nav" href="javascript:;" data-target="ucm-help"><?php _e('Help', 'ultimate-media-on-the-cloud') ?></a></li>
              </ul>
            </div>
            <!-- TAB SETTINGS GENERAL -->
            <?php echo $form; ?>
            <!-- END TAB SETTINGS GENERAL -->

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
                        <h4><?php _e('What news in Pro version?', 'ultimate-media-on-the-cloud') ?></h4>
                        <p><?php _e('With <b>Pro Version</b> you can have unlocked features as below', 'ultimate-media-on-the-cloud') ?></p>
                        <?php echo $about_pro ?>
                    </div>
                </aside>
                <p align="right">
                    <a class="button is-success" href="<?php echo $ucm::$configs->getUcmConfig('plugin_premium_upgrade_url') ?>"><?php _e('Upgrade Pro Version', 'ultimate-media-on-the-cloud') ?></a>
                </p>
            </div>
            <!-- END TAB HELP -->
        </div>
    </div>
    <?php include ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/tpl/common/news.php' ?>
</div>
<?php include ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/tpl/common/toast-message.php' ?>

<div class="ucm-settings column is-three-fifths">
    <?php include ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/tpl/common/header.php' ?>
    <div class="ucm-settings-body box column is-full has-background-white relative">
        <h2><i class="fa fa-trophy"> </i> <?php _e('Upgrade To Pro Version', 'ultimate-media-on-the-cloud') ?></h2>
        <hr>
        <?php _e('Once you upgrade the <b>Ultimate Media On The Cloud Pro Version</b> you can unlock these following features', 'ultimate-media-on-the-cloud') ?>
        <aside class="message is-success mt10">
            <div class="message-body">
                <?php echo $about_pro ?>
                <p align="right">
                    <a href="<?php echo $ucm::$configs->getUcmConfig('plugin_premium_upgrade_url') ?>" class="button is-success mt5 mb5"> <?php _e('Upgrade To Pro', 'ultimate-media-on-the-cloud') ?></a>
                </p>
            </div>
        </aside>

        <p align="right">
            <a href="<?php echo admin_url() .'admin.php?page='. $main_menu['slug'] ?>" class="button is-info mt5 mb5"> <?php _e('Back to Settings', 'ultimate-media-on-the-cloud') ?></a>
        </p>
    </div>
</div>
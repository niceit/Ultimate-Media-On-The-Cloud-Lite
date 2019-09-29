<div class="columns mt10">
    <div class="ucm-settings column is-three-fifths">
        <?php include ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/tpl/common/header.php' ?>
        <div class="ucm-settings-body box column is-full has-background-white relative">
            <h2><i class="fa fa-check"> </i> <?php echo $title ?></h2>
            <hr>
            <div class="mt15 mb15" id="ucm-addons-content">
                <img src="<?php echo plugin_dir_url(ULTIMATE_MEDIA_PLG_FILE) ?>assets/images/loading_2x.gif" style="width: 20px; vertical-align: middle;" alt="Loading" width="20">
                <?php _e('Loading AddOns...', 'ultimate-media-on-the-cloud') ?>
            </div>
            <p align="right">
                <a href="<?php echo admin_url() .'admin.php?page='. $ucm::$configs->getMenuSlug('menu_main') ?>" class="button is-info mt5 mb5"> <?php _e('Back to Settings', 'ultimate-media-on-the-cloud') ?></a>
            </p>
        </div>
    </div>
    <?php include ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/tpl/common/news.php' ?>
</div>
<?php include ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/tpl/common/toast-message.php' ?>
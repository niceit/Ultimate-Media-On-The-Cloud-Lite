<div class="columns mt10">
    <div class="ucm-settings column is-three-fifths">
        <?php include ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/tpl/common/header.php' ?>
        <div class="ucm-settings-body box column is-full has-background-white relative">
            <?php echo $loading_box ?>
            <h2><i class="fa fa-comments"> </i> <?php _e('Support & Feedback', 'ultimate-media-on-the-cloud') ?></h2>
            <hr>
            <aside class="message is-warning mt10">
                <div class="message-body">
                    <?php _e('Join and place your question at plugin Community page. Visit', 'ultimate-media-on-the-cloud') ?> <a href="<?php echo $ucm::$configs->getUcmConfig('plugin_url') ?>" target="_blank">Ultimate Media On The Cloud</a> Wordpress plugin page.
                </div>
            </aside>

            <h2> - <?php _e('OR Submit support request to us', 'ultimate-media-on-the-cloud') ?></h2>
            <?php echo $form ?>
            <hr>
            <p align="right">
                <a href="<?php echo admin_url() .'admin.php?page='. $main_menu['slug'] ?>" class="button is-info mt5 mb5"> <?php _e('Back to Settings', 'ultimate-media-on-the-cloud') ?></a>
            </p>
        </div>
    </div>
    <?php include ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/tpl/common/news.php' ?>
</div>
<?php include ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/tpl/common/toast-message.php' ?>
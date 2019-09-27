<div class="ucm-settings column is-three-fifths">
   <?php include ULTIMATE_MEDIA_PLG_DIR .'/includes/systems/tpl/common/header.php' ?>
    <div class="ucm-settings-body box column is-full has-background-white relative">
        <h2><i class="fa fa-book"> </i> <?php _e('Documentation', 'ultimate-media-on-the-cloud') ?></h2>
        <hr>
        <aside class="message is-warning">
            <div class="message-body">
                <?php _e('You can read plugin help and documentation online here', 'ultimate-media-on-the-cloud') ?><br>
                <a href="<?php echo $ucm::$configs->getUcmConfig('online_document_url') ?>" target="_blank" class="button is-primary mt5 mb5"> Ultimate Media On The Cloud Docs</a>
                <br>
                <?php _e('Join and place your question at plugin Community page. Visit', 'ultimate-media-on-the-cloud') ?> <a href="<?php echo $ucm::$configs->getUcmConfig('plugin_url') ?>" target="_blank">Ultimate Media On The Cloud</a> Wordpress plugin page.
            </div>
        </aside>
        <p align="right">
            <a href="<?php echo admin_url() .'admin.php?page='. $main_menu['slug'] ?>" class="button is-info mt5 mb5"> <?php _e('Back to Settings', 'ultimate-media-on-the-cloud') ?></a>
        </p>
    </div>
</div>
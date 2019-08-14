<?php if ($data) : ?>
    <?php foreach ($data as $addon) : ?>
        <div class="ucm-addon-col">
            <div class="ucm-addon-img"><img src="<?php echo $addon['image'] ?>" alt="" /></div>
            <h3><?php echo $addon['name'] ?></h3>
            <div class="ucm-addon-desc">
                <?php echo $addon['description'] ?>
            </div>
            <div class="ucm-addon-actions">
                <a href="<?php echo $addon['homepage_url']['url'] ?>" class="button is-info mt5 mb5" target="_blank"> <?php echo $addon['homepage_url']['label'] ?> </a>
                <a href="<?php echo $addon['addon_url']['url'] ?>" class="button is-info mt5 mb5" target="_blank"> <?php echo $addon['addon_url']['label'] ?> </a>
                <a href="<?php echo $addon['download_url']['url'] ?>" class="button is-success mt5 mb5" target="_blank"> <?php echo $addon['download_url']['label'] ?> </a>
            </div>
        </div>
    <?php endforeach ?>
<?php else : ?>
    <p><?php _e('There is no available addon.', 'ultimate-media-on-the-cloud') ?></p>
<?php endif ?>
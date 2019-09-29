<?php if (!empty($buckets)) : ?>
    <?php foreach ($buckets as $bucket) : ?>
        <option value="<?php echo $bucket ?>"><?php echo $bucket ?></option>
    <?php endforeach ?>
<?php else : ?>
    <option value=""><?php _e('-Empty Bucket-', 'ultimate-media-on-the-cloud') ?></option>
<?php endif ?>

<?php if (!empty($buckets)) : ?>
    <?php foreach ($buckets as $bucket) : ?>
        <option value="<?php echo $bucket ?>"><?php echo $bucket ?></option>
    <?php endforeach ?>
<?php else : ?>
    <option value="">-Empty Bucket-</option>
<?php endif ?>

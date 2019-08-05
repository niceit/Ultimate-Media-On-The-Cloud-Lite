<?php if (!empty($buckets)) : ?>
    <?php foreach ($buckets as $bucket) : ?>
        <option value="<?php echo $bucket['Name'] ?>"><?php echo $bucket['Name'] ?></option>
    <?php endforeach ?>
<?php else : ?>
    <option value="">-Empty Bucket-</option>
<?php endif ?>

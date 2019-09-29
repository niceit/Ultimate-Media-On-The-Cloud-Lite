<div class="ucm-modal <?php if (isset($modal['class'])) { echo $modal['class']; } ?>"  <?php if (isset($modal['id'])) { echo 'id="'. $modal['id'] .'"'; } ?>>
  <div class="ucm-modal-background"></div>
  <div class="ucm-modal-card">
    <header class="ucm-modal-card-head">
      <p class="ucm-modal-card-title"><?php echo $modal['title'] ?></p>
      <button class="delete" aria-label="close" id="btn-close-modal"></button>
    </header>
    <section class="ucm-modal-card-body">
      <?php echo $modal['content'] ?>
    </section>
    <footer class="ucm-modal-card-foot">
      <?php if (isset($modal['button_ok'])) : ?><button<?php if (isset($modal['button_ok']['attr']) && !empty($modal['button_ok']['attr'])) { foreach ($modal['button_ok']['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>><?php echo $modal['button_ok']['label'] ?></button><?php endif ?>
      <?php if (isset($modal['button_cancel'])) : ?><button<?php if (isset($modal['button_cancel']['attr']) && !empty($modal['button_cancel']['attr'])) { foreach ($modal['button_cancel']['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>><?php echo $modal['button_cancel']['label'] ?></button><?php endif ?>
    </footer>
  </div>
</div>
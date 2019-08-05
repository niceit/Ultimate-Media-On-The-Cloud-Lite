<div class="ucm-modal <?php if (isset($modal['class'])) { echo $modal['class']; } ?>">
  <div class="ucm-modal-background"></div>
  <div class="ucm-modal-card">
    <header class="ucm-modal-card-head">
      <p class="ucm-modal-card-title"><?php echo $modal['title'] ?></p>
      <button class="delete" aria-label="close" class="btn-close-modal"></button>
    </header>
    <section class="ucm-modal-card-body">
      <?php echo $modal['content'] ?>
    </section>
    <footer class="ucm-modal-card-foot">
      <button<?php if (isset($modal['button_ok']['attr']) && !empty($modal['button_ok']['attr'])) { foreach ($modal['button_ok']['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>><?php echo $modal['button_ok']['label'] ?></button>
      <button<?php if (isset($modal['button_cancel']['attr']) && !empty($modal['button_cancel']['attr'])) { foreach ($modal['button_cancel']['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>><?php echo $modal['button_cancel']['label'] ?></button>
    </footer>
  </div>
</div>
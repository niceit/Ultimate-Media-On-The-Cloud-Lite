<div<?php if (isset($form['div']) && !empty($form['div'])) { foreach ($form['div'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>>
    <div class="ucm-form-content">
        <?php if (isset($messages) && $messages) : ?>
            <div class="notification is-success">
                <?php foreach ($messages as $message) : ?>
                    <?php echo $message .'<br>'; ?>
                <?php endforeach ?>
            </div>
        <?php endif ?>

        <?php if (isset($errors) && $errors) : ?>
            <div class="notification is-warning">
                <?php foreach ($errors as $error) : ?>
                    <?php echo $error .'<br>'; ?>
                <?php endforeach ?>
            </div>
        <?php endif ?>
        <form<?php if (isset($form['attr']) && !empty($form['attr'])) { foreach ($form['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>>
            <?php if (isset($form['fields']) && !empty($form['fields'])) : ?>
                <?php foreach ($form['fields'] as $field) : ?>
                    <div <?php if (isset($field['div']) && !empty($field['div'])) { foreach ($field['div'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } else echo 'class="field columns"'; ?>>
                        <?php if (isset($field['label']) && $field['type'] !== 'anchor') : ?>
                            <div class="column is-one-quarter">
                                <label class="label pt10"><?php if (isset($field['icon'])) : ?><i class="<?php echo $field['icon']?>"> </i><?php endif ?> <?php echo $field['label'] ?></label>
                            </div>
                        <?php endif ?>
                        <?php if ($field['type'] === 'text') : ?>
                            <div class="column is-half">
                                <div class="control <?php if (isset($field['icon'])) : ?>has-icons-left<?php endif ?>">
                                    <input<?php if (isset($field['attr']) && !empty($field['attr'])) { foreach ($field['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>>
                                    <?php if (isset($field['icon'])) : ?>
                                    <span class="icon is-small is-left">
                                      <i class="<?php echo $field['icon'] ?>"></i>
                                    </span>
                                    <?php endif ?>
                                </div>
                            </div>
                        <?php endif ?>


                        <?php if ($field['type'] === 'tags') : ?>
                            <?php
                                if (isset($field['attr'], $field['attr']['value']) && is_array($field['attr']['value'])) {
                                    $tags_values = $field['attr']['value'];

                                } else {
                                    $tags_values = [$field['attr']['value']];
                                }
                                unset($field['attr']['value']);
                            ?>
                            <div class="column is-half input-tags">
                                <div class="tags-wrap">
                                    <?php if (isset($tags_values)) : ?>
                                        <?php foreach ($tags_values as $tags_value) : ?>
                                        <div class="tags has-addons tag-element">
                                            <span class="tag is-danger"><?php echo $tags_value ?></span>
                                            <a href="javascript:;" class="tag is-delete btn-tag-preview-remove"></a>
                                        </div>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </div>
                                <div class="ucm-clear-fix"></div>
                                <div class="mt5 control <?php if (isset($field['icon'])) : ?>has-icons-left<?php endif ?>">
                                    <input class="input field-add-post-type" <?php if (isset($field['placeholder'])) : ?>placeholder="<?php $field['placeholder'] ?>"<?php endif ?>>
                                    <?php if (isset($field['icon'])) : ?>
                                    <span class="icon is-small is-left">
                                      <i class="<?php echo $field['icon'] ?>"></i>
                                    </span>
                                    <?php endif ?>
                                </div>
                                <input type="hidden" <?php if (isset($tags_values)) { echo 'value="'. implode(',', $tags_values) .'"'; }?> <?php if (isset($field['attr']) && !empty($field['attr'])) { foreach ($field['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>>
                            </div>
                        <?php endif ?>

                        <?php if ($field['type'] === 'checkbox') : ?>
                            <div class="column is-half pt25">
                                <div class="control">
                                    <?php if (isset($field['value']) && is_array($field['value'])) : ?>
                                        <?php foreach ($field['value'] as $f_value => $f_value_text) : ?>
                                            <div class="column is-one-third">
                                                <input type="checkbox" <?php if (isset($field['attr']) && !empty($field['attr'])) { foreach ($field['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?> value="<?php echo $value ?>"> <?php echo $f_value_text ?>
                                            </div>
                                        <?php endforeach ?>
                                    <?php else : ?>
                                        <input type="checkbox" <?php if (isset($field['attr']) && !empty($field['attr'])) { foreach ($field['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>>
                                    <?php endif ?>
                                </div>
                            </div>
                        <?php endif ?>

                        <?php if ($field['type'] === 'textarea') : ?>
                            <div <?php if (isset($field['div_control'])) : ?><?php foreach ($field['div_control'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } ?><?php else : ?>class="column is-half"<?php endif ?>>
                                <div class="control <?php if (isset($field['icon'])) : ?>has-icons-left<?php endif ?>">
                                    <textarea<?php if (isset($field['attr']) && !empty($field['attr'])) { foreach ($field['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>></textarea>
                                    <?php if (isset($field['icon'])) : ?>
                                    <span class="icon is-small is-left">
                                      <i class="<?php echo $field['icon'] ?>"></i>
                                    </span>
                                    <?php endif ?>
                                </div>
                            </div>
                        <?php endif ?>

                        <?php if ($field['type'] === 'file') : ?>
                            <div class="column is-half">
                                <div class="file">
                                    <label class="file-label">
                                    <input<?php if (isset($field['attr']) && !empty($field['attr'])) { foreach ($field['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>>
                                    <span class="file-cta">
                                      <span class="file-icon">
                                        <i class="dashicons dashicons-upload"></i>
                                      </span>
                                      <span class="file-label">
                                          <?php if (isset($field['value']) && $field['value']) : ?>
                                              <?php echo $field['value'] ?>
                                          <?php else : ?>
                                            Choose a file…
                                          <?php endif ?>
                                      </span>
                                    </span>
                                  </label>
                                </div>
                            </div>
                        <?php endif ?>

                        <?php if ($field['type'] === 'select') : ?>
                            <div class="column is-one-fifth">
                                <div class="control">
                                    <div class="select">
                                        <select<?php if (isset($field['attr']) && !empty($field['attr'])) { foreach ($field['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>>
                                            <?php foreach ($field['value'] as $value => $option_text) : ?>
                                                <option value="<?php echo $value ?>"<?php if (isset($field['selected']) && $field['selected'] === $value) : ?> selected="selected"<?php endif ?>><?php echo $option_text?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>

                        <?php if ($field['type'] === 'anchor') : ?>
                            <div class="column is-one-quarter">&nbsp;</div>
                            <div class="column is-half" style="text-align: right">
                                <a<?php if (isset($field['attr']) && !empty($field['attr'])) { foreach ($field['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>> <?php if (isset($field['icon'])) : ?><i class="<?php echo $field['icon'] ?>"> </i><?php endif ?> <?php echo $field['label'] ?></a>
                            </div>
                            <div style="clear: both;"></div>
                        <?php endif ?>

                        <?php if ($field['type'] === 'hidden') : ?>
                            <input type="hidden"<?php if (isset($field['attr']) && !empty($field['attr'])) { foreach ($field['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>>
                        <?php endif ?>

                        <?php if ($field['type'] === 'message') : ?>
                            <article class="message <?php echo $field['attr']['type'] ?> mt10 ml15">
                                <div class="message-body message-small">
                                    <?php echo $field['value'] ?>
                                </div>
                            </article>
                        <?php endif ?>

                        <div style="clear: both;"></div>
                    </div>
                    <?php if (isset($field['help-text'])) : ?>
                        <article class="message is-warning ucm-helptext" <?php if (isset($field['help-text-attr']) && !empty($field['help-text-attr'])) { foreach ($field['help-text-attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>>
                          <div class="message-body message-small">
                              <small><?php echo $field['help-text'] ?></small>
                          </div>
                        </article>
                    <?php endif ?>
                <?php endforeach ?>
            <?php endif ?>
        </form>
        <p align="right">
            <?php if (isset($form['submit'])) : ?>
                <a<?php if (isset($form['submit']['attr']) && !empty($form['submit']['attr'])) { foreach ($form['submit']['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>><?php echo $form['submit']['label'] ?></a>
            <?php endif ?>
            <?php if (isset($form['cancel'])) : ?>
                <a<?php if (isset($form['cancel']['attr']) && !empty($form['cancel']['attr'])) { foreach ($form['cancel']['attr'] as $item => $value) { echo ' '. $item .'="'. $value .'"'; } } ?>><?php echo $form['cancel']['label'] ?></a>
            <?php endif ?>
        </p>
    </div>
</div>
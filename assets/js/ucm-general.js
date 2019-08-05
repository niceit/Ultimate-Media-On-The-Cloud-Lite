/*! Ultimate Media On The Cloud | (c) PhpRockets Team | Global object for plugin */
var phpR_UCM = {
    jLoading: jQuery('.ucm-settings-body'),
    showLoading: function() {
        this.jLoading.find('.ucm-loading-lock').fadeIn(500);
    },
    hideLoading: function() {
        this.jLoading.find('.ucm-loading-lock').fadeOut(1000);
    },
    toast: {
        jToast: jQuery('#ucm-save-dialog'),
        _timeout: 10000, //10 seconds
        _timeout_obj: '',
        show: function(type, content) {
            this.jToast.hide();
            this.jToast.removeClass('is-success is-warning');
            if (type === 'error') {
                this.jToast.addClass('is-warning');
                this.jToast.find('span.toast-title').html('Error');
            } else {
                this.jToast.addClass('is-success');
                this.jToast.find('span.toast-title').html('Success');
            }
            this.jToast.find('b.toast-message').html(content);
            this.jToast.fadeIn(500);

            phpR_UCM.toast._timeout_obj = setTimeout(function() {
                phpR_UCM.toast.hide();
            }, phpR_UCM.toast._timeout);
        },
        hide: function() {
            this.jToast.fadeOut(1000);
            clearTimeout(phpR_UCM.toast._timeout_obj);
        }
    },
    modal: {
        show: function(element) {
            element.addClass('is-active');
        },
        close: function(element) {
            element.removeClass('is-active');
        }
    },
    tags: {
        add: function(val, ele) {
            var jTags = jQuery(ele).parents('.input-tags').find('.tags-wrap');
            jTags.append('<div class="tags has-addons tag-element">' +
                '<span class="tag is-danger">'+ val +'</span>' +
                '<a href="javascript:;" class="tag is-delete" onclick="phpR_UCM.tags.remove(this)"></a>' +
                '</div>');
            phpR_UCM.tags._updateValue(ele);
        },
        remove: function(btn) {
            var ele = jQuery(btn).parents('.tags-wrap');
            jQuery(btn).parent('.tag-element').remove();
            phpR_UCM.tags._updateValue(ele);
        },
        _updateValue: function(ele) {
            var jTags = jQuery(ele).parents('.input-tags').find('.tags-wrap');
            var jTagsVal = jQuery(ele).parents('.input-tags').find('input.tags-hidden-value');

            var _tags_val = [];
            jTags.find('.tag-element').each(function() {
                _tags_val.push(jQuery(this).text().trim());
            });

            jTagsVal.val(_tags_val.join(','));
        }
    },
    l10n: {
        _e: function(type, key) {
            var l10n = eval('phprockets_'+ type +'_l10n');
            return l10n.hasOwnProperty(key) ? l10n[key] : '';
        }
    },
    checkNews: function() {
        if (jQuery('#phprockets-ucm-news').length > 0) {
            jQuery.ajax({
                url: phprockets_news.url,
                type: 'GET',
                dataType: 'json',
                timeout: 360000,
                success: function (response) {
                    if (response.success && response.data.content !== '') {
                        jQuery('#phprockets-ucm-news').find('.news-body').html(response.data.content);
                        jQuery('#phprockets-ucm-news').fadeIn(1000);
                    }
                }
            });
        }
    }
};
jQuery(function() {
    /* Close a modal */
   jQuery('.btn-close-modal, #btn-close-modal').click(function(e) {
       e.preventDefault();
       jQuery(this).parents('.ucm-modal').removeClass('is-active');
   });

   /* Remove a tag */
   jQuery('.btn-tag-preview-remove').click(function(e) {
       e.preventDefault();
       phpR_UCM.tags.remove(this);
   });

    /* Check for adding a tag */
   jQuery('.field-add-post-type').blur( function() {
       if (jQuery(this).val()) {
           phpR_UCM.tags.add(jQuery(this).val(), this);
           jQuery(this).val('');
       }
   });
   jQuery('.field-add-post-type').on('keyup', function (e) {
       if (e.keyCode === 13 && jQuery(this).val()) {
           phpR_UCM.tags.add(jQuery(this).val(), this);
           jQuery(this).val('');
       }
   });

   /* Check for news */
   phpR_UCM.checkNews();
});
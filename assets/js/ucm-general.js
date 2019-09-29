/*! Ultimate Media On The Cloud | (c) PhpRockets Team | Global object for plugin */
var phpR_UCM = {
    jLoading: [],
    showLoading: function() {
        this.jLoading = jQuery('.ucm-settings-body');
        this.jLoading.find('.ucm-loading-lock').fadeIn(500);
    },
    hideLoading: function() {
        this.jLoading = jQuery('.ucm-settings-body');
        this.jLoading.find('.ucm-loading-lock').fadeOut(1000);
    },
    toast: {
        jToast: [],
        _timeout: 10000, //10 seconds
        _timeout_obj: '',
        show: function(type, content) {
            this.jToast = jQuery('article#ucm-save-dialog');
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
            this.jToast = jQuery('article#ucm-save-dialog');
            this.jToast.fadeOut(1000);
            clearTimeout(phpR_UCM.toast._timeout_obj);
        }
    },
    modal: {
        show: function(element) {
            element.addClass('is-active');
        },
        close: function(element) {
            element.fadeOut(500);
            setTimeout(function() {
                element.removeClass('is-active').removeAttr('style');
            }, 1000);
        }
    },
    tags: {
        add: function(val, ele) {
            var jTagsVal = jQuery(ele).parents('.input-tags').find('input.tags-hidden-value').val().split(',');
            if (!jTagsVal.includes(val)) {
                var jTags = jQuery(ele).parents('.input-tags').find('.tags-wrap');
                jTags.append('<div class="tags has-addons tag-element">' +
                    '<span class="tag is-danger">'+ val +'</span>' +
                    '<a href="javascript:;" class="tag is-delete" onclick="phpR_UCM.tags.remove(this)"></a>' +
                    '</div>');
                phpR_UCM.tags._updateValue(ele);
            }
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
    },
    loadAddOns: function() {
        jQuery.ajax({
            url: phprockets_general.loadAddOns,
            type: 'GET',
            dataType: 'json',
            timeout: 360000,
            success: function (response) {
                if (response.success) {
                    jQuery('#ucm-addons-content').html(response.data.html);
                } else {
                    jQuery('#ucm-addons-content').html(response.data.message);
                }
            }, error: function (xhr, status, errorThrown) {
                jQuery('#ucm-addons-content').html(errorThrown);
            }
        });
    }
};
jQuery(function() {
    /**
     * Main functionally
     * */
    var tab_body = jQuery('.ucm-settings-body .tabs').find('li.is-active a').data('target');
    jQuery('#'+ tab_body).show();

    jQuery('a.ucm-settings-nav').click(function() {
        jQuery('.ucm-settings-body').find('.tabs').find('li').removeClass('is-active');
        jQuery(this).parent('li').addClass('is-active');

        var jTargetPanel = jQuery(this).data('target');
        jQuery('.ucm-settings-body').find('.panel-body').hide();
        jQuery('.ucm-settings-body').find('.panel-body#'+ jTargetPanel).show();
    });

    /* Close a modal */
   jQuery('.btn-close-modal, #btn-close-modal').click(function(e) {
       e.preventDefault();
       phpR_UCM.modal.close(jQuery(this).parents('.ucm-modal'));
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

    jQuery('.field-tags-hint').on('keypress', function (e) {
        var hints_value = jQuery('#'+ jQuery(this).attr('hints-target')).val().split(','),
            word = jQuery(this).val(),
            jTagHints = jQuery(this).parents('.control:eq(0)').find('.tags-hints');
        jTagHints.hide().find('ul').html('');

        if (word.length >= 2) {
            var li_html = '';
            for (var i = 0;i < hints_value.length;i++) {
                if (hints_value[i].search(word) >= 0) {
                    li_html += '<li>'+ hints_value[i] +'</li>';
                }
            }
            if ((li_html !== '')) {
                jTagHints.find('ul').html(li_html);
                jTagHints.show();
            }
        }
    });

   jQuery('.field-tags-hint').on('keyup', function (e) {
       e.preventDefault();
       var hints_value = jQuery('#'+ jQuery(this).attr('hints-target')).val().split(',');
       if (e.keyCode === 13 && jQuery(this).val()) {
           if (hints_value.includes(jQuery(this).val())) {
               phpR_UCM.tags.add(jQuery(this).val(), this);
           }
           jQuery(this).val('');
       }
   });

   jQuery('.tags-hints ul').on('click', 'li', function(e) {
       e.preventDefault();
       phpR_UCM.tags.add(jQuery(this).text(), this);
       jQuery(this).parents('.control:eq(0)').find('.field-tags-hint').val('');
       var jTagHints = jQuery(this).parents('.tags-hints:eq(0)');
       jTagHints.hide().find('ul').html('');
   });

    jQuery('.ucm-accounts-tab-links a').click(function(e) {
        e.preventDefault();
        jQuery('.ucm-accounts-tab-links a').removeClass('is-active');
        jQuery(this).addClass('is-active');
        var jPanelTarget = jQuery('#'+ jQuery(this).data('id'));
        jQuery('.account-panel-body').removeClass('is-active');
        jPanelTarget.addClass('is-active');
    });

   /* Check for news */
   phpR_UCM.checkNews();
});
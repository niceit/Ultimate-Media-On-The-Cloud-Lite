/*! Ultimate Media On The Cloud | (c) PhpRockets Team | Settings Page */
jQuery(function () {
    jQuery('#btn-ucm-general').click(function (e){
        e.preventDefault();
        phpR_UCM.toast.hide();
        phpR_UCM.showLoading();
        jQuery.ajax({
            url: phprockets_general.saveSettings,
            type: 'POST',
            dataType: 'json',
            data: jQuery('#frm-ucm-general').serialize(),
            timeout: 360000,
            success: function (response) {
                if (response.success) {
                    phpR_UCM.toast.show('success', response.data.message);
                } else {
                    phpR_UCM.toast.show('error', response.data.message);
                }
                phpR_UCM.hideLoading();
            },
            error: function (xhr, status, errorThrown) {
                phpR_UCM.hideLoading();
                phpR_UCM.toast.show('error', errorThrown);
            }
        });

    });

    jQuery('#btn-ucm-advanced').click(function (e){
        e.preventDefault();
        phpR_UCM.toast.hide();
        phpR_UCM.showLoading();
        jQuery.ajax({
            url: phprockets_general.saveSettingsAdvanced,
            type: 'POST',
            dataType: 'json',
            data: jQuery('#frm-ucm-advanced').serialize(),
            timeout: 36000,
            success: function (response) {
                if (response.success) {
                    phpR_UCM.toast.show('success', response.data.message);
                } else {
                    phpR_UCM.toast.show('error', response.data.message);
                }
                phpR_UCM.hideLoading();
            },
            error: function (xhr, status, errorThrown) {
                phpR_UCM.hideLoading();
                phpR_UCM.toast.show('error', errorThrown);
            }
        });

    });
    /*End Main functionally*/

    /**
     * Extra Add-On Scripts
     * @For: Amazon S3
    * */
   jQuery('#btn-amz-setting').click(function(e) {
       e.preventDefault();
       var app_key = jQuery('#app-key').val(),
           app_secret = jQuery('#app-secret').val(),
           region = jQuery('#app-region').val(),
           bucket = jQuery('#app-bucket').val(),
           cloud_path = jQuery('#cloud-path').val(),
           storage_class = jQuery('#storage-class').val() ;

       if (app_key === '' || app_secret === '' || region === ''
           || bucket === '' || cloud_path === '' || storage_class === '') {
           phpR_UCM.toast.show('error', phpR_UCM.l10n._e('aws', '_require_all_fields'));
           return;
       }

       phpR_UCM.toast.hide();
       phpR_UCM.showLoading();
       jQuery.ajax({
           url: phprockets_ucm.saveSettings,
           type: 'POST',
           dataType: 'json',
           data: jQuery('#frm-ucm-amazon-s3').serialize(),
           timeout: 36000,
           success: function (response) {
               if (response.success) {
                   phpR_UCM.toast.show('success', response.data.message);
               } else {
                   phpR_UCM.toast.show('error', response.data.message);
               }
               phpR_UCM.hideLoading();
           },
           error: function (xhr, status, errorThrown) {
               phpR_UCM.hideLoading();
               phpR_UCM.toast.show('error', errorThrown);
           }
       });
   });

   jQuery('#btn-amz-connect').click(function(e) {
       e.preventDefault();
       var app_key = jQuery('#app-key').val(),
           app_secret = jQuery('#app-secret').val(),
           region = jQuery('#app-region').val();
       if (app_key === '' || app_secret === '' || region === '') {
           phpR_UCM.toast.show('error', phpR_UCM.l10n._e('aws', '_missing_key'));
           return;
       }

       phpR_UCM.toast.hide();
       phpR_UCM.showLoading();
       jQuery.ajax({
           url: phprockets_ucm.connectAws,
           type: 'POST',
           dataType: 'json',
           data: jQuery('#frm-ucm-amazon-s3').serialize(),
           timeout: 36000,
           success: function (response) {
               if (response.success) {
                   jQuery('#app-bucket').html(response.data.html);
                   phpR_UCM.toast.show('success', response.data.message);
               } else {
                   phpR_UCM.toast.show('error', response.data.message);
               }
               phpR_UCM.hideLoading();
           },
           error: function (xhr, status, errorThrown) {
               phpR_UCM.hideLoading();
               phpR_UCM.toast.show('error', errorThrown);
           }
       });
   });
   /*End Amazon S3 Script*/

    /**
     * Extra Add-On Scripts
     * @For: Google Cloud Storage
     * */
    jQuery("#key-file").on('change', function (e) {
        phprockets_ucm_gcloud.keyFile = jQuery(this)[0].files[0];
        jQuery('#frm-ucm-google-cloud').find('span.file-label').html(phprockets_ucm_gcloud.keyFile.name);
    });
    jQuery('#btn-gcloud-connect').click(function(e) {
        e.preventDefault();
        var project_id = jQuery('#project-id').val(),
            auth_key_file = jQuery('#key-file').val(),
            is_gcloud_account = jQuery('#is-gcloud-account').val();

        if (project_id === '') {
            phpR_UCM.toast.show('error', phpR_UCM.l10n._e('gcloud', '_missing_project_id'));
            return;
        }

        if (!is_gcloud_account && auth_key_file === '') {
            phpR_UCM.toast.show('error', phpR_UCM.l10n._e('gcloud', '_missing_auth_file'));
            return;
        }

        var formData = new FormData(jQuery('#frm-ucm-google-cloud')[0]);
        phpR_UCM.toast.hide();
        formData.append('project_id', project_id);
        formData.append("file", jQuery('#key-file')[0].files[0]);

        phpR_UCM.showLoading();
        jQuery.ajax({
            url: phprockets_ucm_gcloud.connect,
            type: 'POST',
            dataType: 'json',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            timeout: 36000,
            success: function (response) {
                if (response.success) {
                    jQuery('#gcloud-bucket').html(response.data.html);
                    phpR_UCM.toast.show('success', response.data.message);
                } else {
                    phpR_UCM.toast.show('error', response.data.message);
                }
                phpR_UCM.hideLoading();
            },
            error: function (xhr, status, errorThrown) {
                phpR_UCM.hideLoading();
                phpR_UCM.toast.show('error', phpR_UCM.l10n._e('gcloud', '_save_settings_failed'));
            }
        });
    });

    jQuery('#btn-google-cloud-setting').click(function(e) {
        e.preventDefault();
        var project_id = jQuery('#project-id').val(),
            auth_key_file = jQuery('#key-file').val(),
            bucket = jQuery('#gcloud-bucket').val(),
            is_gcloud_account = jQuery('#is-gcloud-account').val();

        if (project_id === '') {
            phpR_UCM.toast.show('error', phpR_UCM.l10n._e('gcloud', '_missing_project_id'));
            return;
        }

        if (!is_gcloud_account && auth_key_file === '') {
            phpR_UCM.toast.show('error', phpR_UCM.l10n._e('gcloud', '_missing_auth_file'));
            return;
        }

        if (bucket === '') {
            phpR_UCM.toast.show('error', phpR_UCM.l10n._e('gcloud', '_choose_bucket'));
            return;
        }

        var formData = new FormData(jQuery('#frm-ucm-google-cloud')[0]);
        phpR_UCM.toast.hide();
        formData.append('project_id', project_id);
        formData.append('file', jQuery('#key-file')[0].files[0]);
        formData.append('bucket', bucket);
        formData.append('cloud_path', jQuery('#gcloud-cloud-path').val());

        phpR_UCM.showLoading();
        jQuery.ajax({
            url: phprockets_ucm_gcloud.saveSettings,
            type: 'POST',
            dataType: 'json',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            timeout: 36000,
            success: function (response) {
                if (response.success) {
                    phpR_UCM.toast.show('success', response.data.message);
                } else {
                    phpR_UCM.toast.show('error', response.data.message);
                }
                phpR_UCM.hideLoading();
            },
            error: function (xhr, status, errorThrown) {
                phpR_UCM.hideLoading();
                phpR_UCM.toast.show('error', 'Unable to Save Settings with this Auth Key file.');
            }
        });
    });
    /*End Google Cloud Storage Script*/

    /**Feedback**/
    jQuery('#btn-ucm-feedback').click(function(e) {
        e.preventDefault();
        var pass_validation = true;
        jQuery('#frm-ucm-feedback input, #frm-ucm-feedback select, #frm-ucm-feedback textarea').each(function() {
            if (jQuery(this).val() === '') {
                phpR_UCM.toast.show('error', phpR_UCM.l10n._e('settings', '_require_all_fields'));
                pass_validation = false;
                return false;
            }
        });

        if (!pass_validation) {
            return false;
        }

        var formData = jQuery('#frm-ucm-feedback').serialize();
        phpR_UCM.toast.hide();

        phpR_UCM.showLoading();
        jQuery.ajax({
            url: phprockets_general.sendFeedback,
            type: 'POST',
            dataType: 'json',
            data: formData,
            timeout: 36000,
            success: function (response) {
                if (response.success) {
                    document.frm_ucm_feedback.reset();
                    phpR_UCM.toast.show('success', response.data.message);
                } else {
                    phpR_UCM.toast.show('error', response.data.message);
                }
                phpR_UCM.hideLoading();
            },
            error: function (xhr, status, errorThrown) {
                phpR_UCM.hideLoading();
                phpR_UCM.toast.show('error', phpR_UCM.l10n._e('settings', '_error_submit'));
            }
        });
    });
    /**End Feedback**/
});
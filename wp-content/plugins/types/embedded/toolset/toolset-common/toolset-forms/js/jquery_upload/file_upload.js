function getExtension(filename) {
    return filename.split('.').pop().toLowerCase();
}

function isImage(file) {
    switch (getExtension(file)) {
        //if .jpg/.gif/.png do something
        case 'jpg':
        case 'gif':
        case 'png':
        case 'jpeg':
        case 'bmp':
        case 'svg':
            return true;
            break;

    }
    return false;
}

//new RegExp('/regex'+DATA-FROM-INPUT+'', 'i');
jQuery(function () {
    'use strict';
    // Change this to the location of your server-side upload handler:

//    jQuery.each(jQuery(".wpt-form-hidden"),function(i, val){
//        console.log(i);
//        jQuery(val).prop('');
//    });

    function o(i, file) {
        var url = settings.ajaxurl;
        //console.log("URL:" + url);
        var nonce = settings.nonce;
        //console.log("NONCE:" + nonce);

        var curr_file = file;
        var validation = jQuery(curr_file).attr('data-wpt-validate');
        //console.log(validation);
        var obj_validation = jQuery.parseJSON(validation);

        for (var x in obj_validation) {
            if (x == 'extension') {
                for (var y in obj_validation[x]) {
                    if (y == 'args') {
                        var validation_args = obj_validation[x][y][0];
                        //validation_args = validation_args.split('|').join(',');
                    }
                    if (y == 'message') {
                        var validation_message = obj_validation[x][y];
                    }
                }
            }
        }

        var myid = jQuery("input[name='_cred_cred_prefix_post_id']").val();

        jQuery(file).fileupload({
            url: url + '?id=' + myid + '&nonce=' + nonce,
            dataType: 'json',
            cache: false,
            maxChunkSize: 0,
            formData: {id: myid},
            //acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
            done: function (e, data) {
                var id = jQuery(curr_file).attr('id');
                //progress bar hide
                //jQuery('#progress_' + wpt_id).css({'width': '0%'});
//                jQuery('#progress_' + wpt_id + ' .progress-bar').css(
//                        {'width': '0%'}
//                );
//                jQuery('#progress_' + wpt_id).hide();                

                var wpt_id = jQuery(this).next(".meter").attr("id"); //id.replace("_file", "");
                jQuery('#' + wpt_id).show();
                jQuery('#' + wpt_id + ' .progress-bar').css(
                        {'width': '0%'}
                );
                jQuery('#' + wpt_id).hide();

                if (data._response.result.error && data._response.result.error != '') {
                    alert(data._response.result.error);
                }
                if (data.result.files) {
                    jQuery.each(data.result.files, function (index, file) {

                        var id = jQuery(curr_file).attr('id');
                        //console.log(id);
                        var wpt_id = id.replace("_file", "");
//                        var wpt_id = jQuery(curr_file).attr('data-wpt-name');
//                        wpt_id = wpt_id.replace(/[^a-z0-9\-\_]/gi, '');
                        //console.log(wpt_id);
                        var myid = wpt_id;
                        //console.log(myid);

                        if (id.toLowerCase().indexOf("wpt-form-el") >= 0) {
                            var number = id.replace(/[^0-9]/g, '');
                            var new_num = number - 1;
                            var hidden_id = "wpt-form-el" + new_num;
                        } else
                            var hidden_id = wpt_id + '_hidden';

                        //console.log(hidden_id);

                        //hidden text set
                        jQuery('#' + hidden_id).val(file);
                        jQuery('#' + hidden_id).prop('disabled', false);
                        //file field disabled and hided
                        jQuery('#' + id).hide();
                        jQuery('#' + id).prop('disabled', true);

                        //remove restore button
                        jQuery('#' + id).siblings(".js-wpt-credfile-undo").hide();

                        //add image/file uploaded and button to delete
                        if (isImage(file)) {
                            jQuery("<img id='loaded_" + myid + "' src='" + file + "'><input id='butt_" + myid + "' style='width:100%;margin-top:2px;margin-bottom:2px;' type='button' value='delete' rel='" + file + "' class='delete_ajax_file'>").insertAfter('#' + jQuery(curr_file).attr('id'));
                        } else {
                            jQuery("<a id='loaded_" + myid + "' href='" + file + "' target='_blank'>" + file + "</a></label><input id='butt_" + myid + "' style='width:100%;margin-top:2px;margin-bottom:2px;' type='button' value='delete' rel='" + file + "' class='delete_ajax_file'>").insertAfter('#' + jQuery(curr_file).attr('id'));
                        }

                        jQuery("#loaded_" + myid).each(function (i) {
                            var max_size = settings.media_settings.width;
                            if (jQuery(this).height() > jQuery(this).width()) {
                                var h = max_size;
                                var w = Math.ceil(jQuery(this).width() / jQuery(this).height() * max_size);
                            } else {
                                var w = max_size;
                                var h = Math.ceil(jQuery(this).height() / jQuery(this).width() * max_size);
                            }
                            jQuery(this).css({height: h, width: w});
                        });

                        //add function to delete button
                        jQuery("#butt_" + myid).on('click', function () {
                            if (confirm(settings.delete_confirm_text)) {
                                jQuery("#loaded_" + myid).remove();
                                jQuery("#butt_" + myid).remove();

                                jQuery('#' + id).show();
                                jQuery('#' + id).prop('disabled', false);

                                jQuery('#' + hidden_id).val("");
                                jQuery('#' + hidden_id).prop('disabled', true);

                                jQuery.ajax({
                                    url: url,
                                    timeout: 10000,
                                    type: 'POST',
                                    data: {action: 'delete', file: file, nonce: nonce},
                                    dataType: 'json',
                                    success: function (data)
                                    {
                                        if (!data.result) {
                                            if (data.error)
                                                alert(data.error);
                                            else
                                                alert(settings.delete_alert_text);
                                        }
                                        credfile_fu_init();
                                    },
                                    error: function ()
                                    {
                                    }
                                });
                            }
                        });
                    });
                    credfile_fu_init();
                }
            },
            add: function (e, data) {
                if (validation_args) {
                    var uploadErrors = [];
                    var acceptFileTypes = new RegExp('/regex' + validation_args + '', 'i'); //^image\/(gif|jpe?g|png)$/i;
                    if (data.originalFiles[0]['type'].length && !acceptFileTypes.test(data.originalFiles[0]['type'])) {
                        uploadErrors.push(validation_message);
                    }
                    if (data.originalFiles[0]['size'].length && data.originalFiles[0]['size'] > 5000000) {
                        uploadErrors.push(settings.too_big_file_alert_text);
                    }
                    if (uploadErrors.length > 0) {
                        alert(uploadErrors.join("\n"));
                    } else {
                        data.submit();
                    }
                } else {
                    data.submit();
                }

            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                var id = jQuery(curr_file).attr('id');
//                var wpt_id = id.replace("_file", "");
//                jQuery('#progress_' + wpt_id).show();
//                //jQuery('#progress_' + wpt_id).css({'width': '100%'});
//                jQuery('#progress_' + wpt_id + ' .progress-bar').css(
//                        {'width': progress + '%'}
//                );                
                var wpt_id = jQuery(this).next(".meter").attr("id"); //id.replace("_file", "");
                jQuery('#' + wpt_id).show();
                //jQuery('#progress_' + wpt_id).css({'width': '100%'});
                jQuery('#' + wpt_id + ' .progress-bar').css(
                        {'width': progress + '%'}
                );
            },
            fail: function (e, data) {
                var id = jQuery(curr_file).attr('id');
                var wpt_id = id.replace("_file", "");
                jQuery('#progress_' + wpt_id).hide();
                //jQuery('#progress_' + wpt_id).css({'width': '100%'});
                jQuery('#progress_' + wpt_id + ' .progress-bar').css(
                        {'width': '0%'}
                );
                alert("Upload Failed !");
            }
        }).prop('disabled', !jQuery.support.fileInput)
                .parent().addClass(jQuery.support.fileInput ? undefined : 'disabled');

    }

    function credfile_fu_init() {
        jQuery('input[type="file"]:visible:not(#_featured_image_file)').each(o);

        jQuery(document).on('click', '.js-wpt-credfile-delete, .js-wpt-credfile-undo', function (e) {
            jQuery('input[type="file"]:visible:not(#_featured_image_file)').each(o);
        });

        //AddRepetitive add event
        wptCallbacks.addRepetitive.add(function () {
            jQuery('input[type="file"]:visible:not(#_featured_image_file)').each(o);
        });

        //AddRepetitive remove event
        wptCallbacks.addRepetitive.remove(function () {
            //console.log("TODO: delete file related before removing")
        });
    }

//    jQuery('.js-wpt-repadd').on('click', function (e) {
//        e.preventDefault();
//        alert("ciao");
//        jQuery('input[type="file"]').each(o);
//    });

    jQuery(".wpt-credfile-preview-item").each(function (i) {
        var max_size = settings.media_settings.width;
        if (jQuery(this).height() > jQuery(this).width()) {
            var h = max_size;
            var w = Math.ceil(jQuery(this).width() / jQuery(this).height() * max_size);
        } else {
            var w = max_size;
            var h = Math.ceil(jQuery(this).height() / jQuery(this).width() * max_size);
        }
        jQuery(this).css({height: h, width: w});
    });

    credfile_fu_init();
});
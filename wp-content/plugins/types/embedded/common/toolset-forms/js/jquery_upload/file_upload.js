//console.log(settings.ajaxurl);
//jQuery(function ()
//{
//    // Variable to store your files
//    var files;
//
//    // Add events
//    jQuery('input[type=file]').on('change', prepareUpload);
//    jQuery('.cred-form').on('submit', uploadFiles);
//
//    // Grab the files and set them to our variable
//    function prepareUpload(event)
//    {
//        files = event.target.files;
//        console.log(files);
//    }
//
//    // Catch the form submit and upload the files
//    function uploadFiles(event)
//    {
//        event.stopPropagation(); // Stop stuff happening
//        event.preventDefault(); // Totally stop stuff happening
//
//        var data = new FormData();
//        jQuery.each(jQuery('input[type="file"]')[0].files, function(i, file) {
//            data.append('file-'+i, file);
//        });
//
//        jQuery.ajax({
//            url: settings.ajaxurl+"?files",
//            type: 'POST',
//            data: data,
//            cache: false,
//            dataType: 'json',
//            processData: false, // Don't process the files
//            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
//            success: function (data, textStatus, jqXHR)
//            {
//                if (typeof data.error === 'undefined')
//                {
//                    // Success so call function to process the form
//                    //submitForm(event, data);
//                }
//                else
//                {
//                    // Handle errors here
//                    console.log('ERRORS: ' + data.error);
//                }
//            },
//            error: function (jqXHR, textStatus, errorThrown)
//            {
//                // Handle errors here
//                console.log('ERRORS: ' + textStatus);
//                // STOP LOADING SPINNER
//            }
//        });
//    }
//
//    function submitForm(event, data)
//    {
//        // Create a jQuery object from the form
//        jQueryform = jQuery(event.target);
//
//        // Serialize the form data
//        var formData = jQueryform.serialize();
//
//        // You should sterilise the file names
//        jQuery.each(data.files, function (key, value)
//        {
//            formData = formData + '&filenames[]=' + value;
//        });
//
//        jQuery.ajax({
//            url: 'submit.php',
//            type: 'POST',
//            data: formData,
//            cache: false,
//            dataType: 'json',
//            success: function (data, textStatus, jqXHR)
//            {
//                if (typeof data.error === 'undefined')
//                {
//                    // Success so call function to process the form
//                    console.log('SUCCESS: ' + data.success);
//                }
//                else
//                {
//                    // Handle errors here
//                    console.log('ERRORS: ' + data.error);
//                }
//            },
//            error: function (jqXHR, textStatus, errorThrown)
//            {
//                // Handle errors here
//                console.log('ERRORS: ' + textStatus);
//            },
//            complete: function ()
//            {
//                // STOP LOADING SPINNER
//            }
//        });
//    }
//});

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
        console.log("URL:" + url);
        var nonce = settings.nonce;
        console.log("NONCE:" + nonce);
        var id = jQuery("input[name='_cred_cred_prefix_post_id']").val();
        console.log("ID:" + id);

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

        jQuery(file).fileupload({
            url: url + '?nonce=' + nonce,
            dataType: 'json',
            cache: false,
            maxChunkSize: 0,
            formData: {id: id},
            //acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
            done: function (e, data) {
                var id = jQuery(curr_file).attr('id');
                var wpt_id = id.replace("_file", "");
                //progress bar hide
                //jQuery('#progress_' + wpt_id).css({'width': '0%'});
                jQuery('#progress_' + wpt_id + ' .progress-bar').css(
                 {'width': '0%'}
                 );
                jQuery('#progress_' + wpt_id).hide();

                if (data._response.result.error && data._response.result.error != '') {
                    alert(data._response.result.error);
                }
                if (data.result.files) {
                    jQuery.each(data.result.files, function (index, file) {

                        var id = jQuery(curr_file).attr('id');
                        console.log(id);
                        var wpt_id = id.replace("_file", "");
//                        var wpt_id = jQuery(curr_file).attr('data-wpt-name');
//                        wpt_id = wpt_id.replace(/[^a-z0-9\-\_]/gi, '');
                        console.log(wpt_id);
                        var myid = wpt_id;
                        console.log(myid);

                        if (id.toLowerCase().indexOf("wpt-form-el") >= 0) {
                            var number = id.replace(/[^0-9]/g, '');
                            var new_num = number - 1;
                            var hidden_id = "wpt-form-el" + new_num;
                        } else
                            var hidden_id = wpt_id + '_hidden';

                        console.log(hidden_id);

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
                        //add function to delete button
                        jQuery("#butt_" + myid).on('click', function () {
                            if (confirm('Are you sure to delete this file ?')) {
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
                                        console.log(data);
                                        if (!data.result) {
                                            if (data.error)
                                                alert(data.error);
                                            else
                                                alert('Error deleting file !');
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
                        uploadErrors.push('Filesize is too big');
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
                console.log("progress => " + progress + "%");
                var id = jQuery(curr_file).attr('id');
                var wpt_id = id.replace("_file", "");
                jQuery('#progress_' + wpt_id).show();
                //jQuery('#progress_' + wpt_id).css({'width': '100%'});
                jQuery('#progress_' + wpt_id + ' .progress-bar').css(
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
    credfile_fu_init();
});
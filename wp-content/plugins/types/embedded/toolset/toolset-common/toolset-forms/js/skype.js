
var wptSkype = (function($) {
    var $parent, $skypename, $preview, $fields;
    var $popup = $('#tpl-wpt-skype-edit-button > div');
    function init() {
        $('body').on('click', '.js-wpt-skype-edit-button', function() {
            $parent = $(this).parents('.js-wpt-field-item');
            $skypename = $('.js-wpt-skypename', $parent);
            $preview = $('.js-wpt-skype-preview', $parent);
            $('.js-wpt-skypename-popup', $popup).val($skypename.val());
            tb_show(wptSkypeData.title, "#TB_inline?inlineId=tpl-wpt-skype-edit-button&height=500&width=600", "");
            $('.js-wpt-skype', $popup).on("change", function(){
                wptSkype.preview($popup, this);
            });
            $('.js-wpt-skype', $popup).on("keyup", function(){
                wptSkype.preview($popup, this);
            });
            wptSkype.preview($popup, this, 'init');
        });
        $('#wpt-skype-edit-button-popup').on('click', '.js-wpt-close-thickbox', function() {
            var button = $('.js-wpt-skype-edit-button', $parent);
            var $extra_skype_data = {};
            $skypename.val($('.js-wpt-skypename-popup', $popup).val());
            $('.js-wpt-skype', $popup).each(function() {
                var $field_name = $(this).data('skype-field-name');
                var $val = $(this).val();
                if ( $field_name ) {
                    switch($(this).data('wpt-type')) {
                        case 'checkbox':
                            if ( $(this).is(':checked') ) {
                                $('.js-wpt-skype-'+$field_name, $parent).val($val);
                                button.data($field_name, $val);
                            }
                            break;
                        case 'option':
                            if ( $(this).is(':selected') ) {
                                $('.js-wpt-skype-'+$field_name, $parent).val($val);
                                button.data($field_name, $val);
                            }
                            break;
                        case 'textfield':
                            $('.js-wpt-skype-'+$field_name, $parent).val($val);
                            button.data($field_name, $val);
                            break;
                    }
                }
            });
            /**
             * fix data for action
             */
            if ( 1 < $('.js-wpt-skype-action:checked', $popup).length ) {
                $('.js-wpt-skype-action', $popup).val('dropdown');;
                $(this).data('action', 'dropdown');
            }
            tb_remove();
        });
    }
    function preview($popup, object, mode) {
        var $object = $(object);
        /**
         * be sure, that at lest one action is on
         */
        if ( 'checkbox' == $object.attr('type') ) {
            if ( 0 == $('.js-wpt-skype-action:checked', $popup).length ) {
                $('.js-wpt-skype-action', $popup).each(function() {
                    if ( this != object ) {
                        $(this).attr('checked', 'checked');
                    }
                });
            }
        }

        /**
         * participants
         */
        var $button = $('#wpt-skype-edit-button-popup-preview-button');
        $('#wpt-skype-preview', $button).html('');
        var participants = $('.js-wpt-skypename-popup', $popup).val();

        /**
         * setup values
         */
        if ( 'undefined' != typeof mode && 'init' == mode ) {
            if ( value = $object.data('size') ) {
                $('.js-wpt-skype-size option', $popup).removeAttr('selected');
                $('.js-wpt-skype-size [value='+value+']', $popup).attr('selected', 'selected');
            }
            if ( value = $object.data('color') ) {
                $('.js-wpt-skype-color option', $popup).removeAttr('selected');
                $('.js-wpt-skype-color [value='+value+']', $popup).attr('selected', 'selected');
            }
            if ( value = $object.data('action') ) {
                switch(value) {
                    case 'dropdown':
                        $('.js-wpt-skype-action', $popup).attr('checked', 'checked');
                        break;
                    case 'chat':
                    case 'call':
                        $('.js-wpt-skype-action', $popup).removeAttr('checked');
                        $('.js-wpt-skype-action-'+value, $popup).attr('checked', 'checked');
                        break;
                    default:
                        $('.js-wpt-skype-action', $popup).removeAttr('checked');
                        $('.js-wpt-skype-action-call', $popup).attr('checked', 'checked');
                        break;
                }
            }
        }
        /**
         * skypename
         */
        var skypename = "dropdown";
        if ($('.js-wpt-skype-action:checked', $popup).length < 2 ) {
            skypename = $('.js-wpt-skype-action:checked', $popup).val();
        }
        /**
         * Skype.ui
         */
        if ( participants.length > 2 ) {
            if ( 'object' == typeof Skype) {
                data = {
                    name: skypename,
                    element: "wpt-skype-preview",
                    participants: [participants],
                    imageSize: parseInt($('.js-wpt-skype-size option:selected', $popup).val()),
                    imageColor: $('.js-wpt-skype-color option:selected', $popup).val()
                }
                /**
                 * show tooltip
                 */
                if ( 'dropdown' == data.name ) {
                    $('small', $button).show();
                } else {
                    $('small', $button).hide();
                }
                /**
                 * change parent background to see skype in white
                 */
                if ( 'white' == data.imageColor) {
                    $button.addClass('dark-background');

                } else {
                    $button.removeClass('dark-background');
                }
                Skype.ui(data);
            }
        }
    }
    return {
        init: init,
        preview: preview
    };
})(jQuery);

jQuery(document).ready(wptSkype.init);

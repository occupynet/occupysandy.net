/**
 *
 *
 * Relationship JS for post edit screen.
 */

/*
 * Child tables on post edit screen handling.
 * 
 * @type tChildTable._L10.Anonym$0
 */
var tChildTable = (function($) {

    function init(selector) {
        // Init hierarchical taxonomies
        tCatBox.init();
        // Bind wpList event
        $('.js-types-child-table .categorychecklist')
                .bind('wpListAddEnd', taxAdjust);
        // Init non-hierarchical taxonomies
        tTagBox.init(selector);
        /**
         * bind to children pagination buttons
         */
        $('.wpcf-pr-pagination-link').on('click', function() {
            param_pagination_name = $(this).data('pagination-name');
            if ( param_pagination_name ) {
                number_of_posts = $('select[name="'+param_pagination_name+'"]').val();
                re = new RegExp(param_pagination_name+'=\\d+');
                $(this).attr(
                    'href',
                    $(this).attr('href').replace(re, param_pagination_name+'='+number_of_posts)
                );
            }
            return true;
        });
    }

    function taxAdjust() {
        var list = $(this);
        list.find('input[name^="tax_input["], input[name^="post_category[]"]').each(function() {
            $(this).attr('name', list.attr('data-types'));
        });
    }

    return {
        init: init,
        reset: init
    }
})(jQuery, undefined);

/*
 * Hierarchical taxonomies form handling on post edit screen.
 */
(function($) {

    tCatBox = {
        init: function() {
            $('.js-types-child-categorydiv').each(function() {
                var this_id = $(this).attr('id'), catAddBefore, catAddAfter, taxonomy, settingName, typesID;
                taxonomy = $(this).data('types-reltax');
                typesID = $(this).attr('id');

                // Ajax Cat
                $('#new' + typesID).one('focus', function() {
                    $(this).val('').removeClass('form-input-tip')
                });

                $('#new' + typesID).keypress(function(event) {
                    if (13 === event.keyCode) {
                        event.preventDefault();
                        $('#' + typesID + '-add-submit').click();
                    }
                });
                $('#' + typesID + '-add-submit').click(function() {
                    $('#new' + typesID).focus();
                });

                catAddBefore = function(s) {
                    if (!$('#new' + typesID).val())
                        return false;
                    s.data += '&' + $(':checked', '#' + typesID + 'checklist').serialize();
                    $('#' + typesID + '-add-submit').prop('disabled', true);
                    return s;
                };

                catAddAfter = function(r, s) {
                    var sup, drop = $('#new' + typesID + '_parent');

                    $('#' + typesID + '-add-submit').prop('disabled', false);
                    if ('undefined' != s.parsed.responses[0] && (sup = s.parsed.responses[0].supplemental.newcat_parent)) {
                        drop.before(sup);
                        drop.remove();
                    }
                };

                $('#' + typesID + 'checklist').wpList({
                    what: 'types_reltax_add',
                    alt: '',
                    response: typesID + '-ajax-response',
                    addBefore: catAddBefore,
                    addAfter: catAddAfter
                });

                $('#' + typesID + '-add-toggle').on('click', function() {
                    $('#' + typesID + '-adder').toggleClass('wp-hidden-children');
                    $('a[href="#' + typesID + '-all"]', '#' + typesID + '-tabs').click();
                    $('#new' + typesID).focus();
                    return false;
                });

                $('#' + typesID + 'checklist, #' + typesID + 'checklist-pop').on('click', 'li.popular-category > label input[type="checkbox"]', function() {
                    var t = $(this), c = t.is(':checked'), id = t.val();
                    if (id && t.parents('#taxonomy-' + typesID).length)
                        $('#in-' + typesID + '-' + id + ', #in-popular-' + typesID + '-' + id).prop('checked', c);
                });

            });
        }
    }
}(jQuery));

/*
 * Non-hierarchical taxonomies form handling on post edit screen.
 */
(function($) {

    tTagBox = {
        clean: function(tags) {
            var comma = postL10n.comma || window.tagsBoxL10n.tagDelimiter;
            if (',' !== comma)
                tags = tags.replace(new RegExp(comma, 'g'), ',');
            tags = tags.replace(/\s*,\s*/g, ',').replace(/,+/g, ',').replace(/[,\s]+$/, '').replace(/^[,\s]+/, '');
            if (',' !== comma)
                tags = tags.replace(/,/g, comma);
            return tags;
        },
        parseTags: function(el) {
            var id = el.id, num = id.split('-check-num-')[1], taxbox = $(el).closest('.js-types-child-tagsdiv'),
                    thetags = taxbox.find('.the-tags'), comma = postL10n.comma || window.tagsBoxL10n.tagDelimiter,
                    current_tags = thetags.val().split(comma), new_tags = [];
            delete current_tags[num];

            $.each(current_tags, function(key, val) {
                val = $.trim(val);
                if (val) {
                    new_tags.push(val);
                }
            });

            thetags.val(this.clean(new_tags.join(comma)));

            this.quickClicks(taxbox);
            return false;
        },
        quickClicks: function(el) {
            var thetags = $('.the-tags', el),
                    tagchecklist = $('.tagchecklist', el),
                    id = $(el).attr('id'),
                    current_tags, disabled,
                    comma = postL10n.comma || window.tagsBoxL10n.tagDelimiter;

            if (!thetags.length) {
                return;
            }

            disabled = thetags.prop('disabled');

            current_tags = thetags.val().split(comma);
            tagchecklist.empty();

            $.each(current_tags, function(key, val) {
                var span, xbutton;

                val = $.trim(val);

                if (!val) {
                    return;
                }

                // Create a new span, and ensure the text is properly escaped.
                span = $('<span />').text(val);

                // If tags editing isn't disabled, create the X button.
                if (!disabled) {
                    xbutton = $('<a id="' + id + '-check-num-' + key + '" class="ntdelbutton">X</a>');
                    xbutton.click(function() {
                        tTagBox.parseTags(this);
                    });
                    span.prepend('&nbsp;').prepend(xbutton);
                }

                // Append the span to the tag list.
                tagchecklist.append(span);
            });
        },
        flushTags: function(el, a, f) {
            a = a || false;
            var tags = $('.the-tags', el),
                    newtag = $('input.js-types-newtag', el),
                    comma = postL10n.comma || window.tagsBoxL10n.tagDelimiter,
                    newtags, text;

            text = a ? $(a).text() : newtag.val();
            tagsval = tags.val();
            newtags = tagsval ? tagsval + comma + text : text;

            newtags = this.clean(newtags);
            newtags = array_unique_noempty(newtags.split(comma)).join(comma);
            tags.val(newtags);
            this.quickClicks(el);

            if (!a) {
                newtag.val('');
            }

            if ('undefined' == typeof(f)) {
                newtag.focus();
            }

            return false;
        },
        get: function(id) {
            var tax = $('#' + id).data('types-tax');

            $.post(ajaxurl, {'action': 'get-tagcloud', 'tax': tax}, function(r, stat) {
                if (0 == r || 'success' != stat) {
                    r = wpAjax.broken;
                }

                r = $('<p id="tagcloud-' + id + '" class="the-tagcloud">' + r + '</p>');
                $('a', r).click(function() {
                    tTagBox.flushTags($(this).closest('td').children('.js-types-child-tagsdiv'), this);
                    return false;
                });

                $('#' + id).after(r);
            });
        },
        init: function(selector) {

            var t = this, ajaxtag = $('.ajaxtag', selector);

            $('.js-types-child-tagsdiv', selector).each(function() {
                tTagBox.quickClicks(this);
            });

            $('input.js-types-addtag', ajaxtag).click(function() {
                t.flushTags($(this).closest('.js-types-child-tagsdiv'));
            });

            $('div.taghint', ajaxtag).click(function() {
                $(this).css('visibility', 'hidden').parent().siblings('.js-types-newtag').focus();
            });

            $('input.js-types-newtag', ajaxtag).blur(function() {
                if (this.value == '')
                    $(this).parent().siblings('.taghint').css('visibility', '');
            }).focus(function() {
                $(this).parent().siblings('.taghint').css('visibility', 'hidden');
            }).keyup(function(e) {
                if (13 == e.which) {
                    tTagBox.flushTags($(this).closest('.js-types-child-tagsdiv'));
                    return false;
                }
            }).keypress(function(e) {
                if (13 == e.which) {
                    e.preventDefault();
                    return false;
                }
            }).each(function() {
                var tax = $(this).data('types-tax'),
                comma = postL10n.comma || window.tagsBoxL10n.tagDelimiter;
                $(this).suggest(ajaxurl + '?action=ajax-tag-search&tax=' + tax, {delay: 500, minchars: 2, multiple: true, multipleSep: comma + ' '});
            });

            // save tags on post save/publish
            $('#post').submit(function() {
                $('.js-types-child-tagsdiv', selector).each(function() {
                    tTagBox.flushTags(this, false, 1);
                });
            });

            // tag cloud
            $('a.js-types-child-tagcloud-link', selector).click(function() {
                tTagBox.get($(this).attr('id'));
                $(this).unbind().click(function() {
                    $(this).siblings('.the-tagcloud').toggle();
                    return false;
                });
                return false;
            });
        }
    };
}(jQuery));

jQuery(document).ready(function($) {
    tChildTable.init();
});

jQuery(document).ready(function($) {
    var frame_relationship = [];
    window.wpcf_pr_edited = false;
    // Mark as edited field
    $('#wpcf-post-relationship table').on('click', ':input', function() {
        window.wpcf_pr_edited = true;
        $(this).parent().addClass('wpcf-pr-edited');
    });

    /*
     * Parent form
     */
    jQuery('.wpcf-pr-has-apply').click(function() {
        jQuery(this).parent().slideUp().parent().parent().find('.wpcf-pr-edit').fadeIn();
        var txt = new Array();
        jQuery(this).parent().find('input:checked').each(function() {
            txt.push(jQuery(this).next().html());
        });
        if (txt.length < 1) {
            var wpcf_pr_has_update = wpcf_pr_has_empty_txt;
        } else {
            var txt_update = txt.join(', ');
            var wpcf_pr_has_update = wpcf_pr_has_txt.replace("%s", txt_update);
        }
        jQuery(this).parent().parent().parent().find('.wpcf-pr-has-summary').html(wpcf_pr_has_update);
    });
    jQuery('.wpcf-pr-belongs-apply').click(function() {
        jQuery(this).parent().slideUp().parent().parent().find('.wpcf-pr-edit').fadeIn();
        var txt = new Array();
        jQuery(this).parent().find('input:checked').each(function() {
            txt.push(jQuery(this).next().html());
        });
        if (txt.length < 1) {
            var wpcf_pr_belongs_update = wpcf_pr_belongs_empty_txt;
        } else {
            var txt_update = txt.join(', ');
            var wpcf_pr_belongs_update = wpcf_pr_belongs_txt.replace("%s", txt_update);
        }
        jQuery(this).parent().parent().parent().find('.wpcf-pr-belongs-summary').html(wpcf_pr_belongs_update);
    });
    jQuery('.wpcf-pr-has-cancel').click(function() {
        jQuery(this).parent().find('.checkbox').removeAttr('checked');
        for (var checkbox in window.wpcf_pr_has_snapshot) {
            jQuery('#' + window.wpcf_pr_has_snapshot[checkbox]).attr('checked', 'checked');
        }
        jQuery(this).parent().slideUp().parent().parent().find('.wpcf-pr-edit').fadeIn();
    });
    jQuery('.wpcf-pr-belongs-cancel').click(function() {
        jQuery(this).parent().find('.checkbox').removeAttr('checked');
        for (var checkbox in window.wpcf_pr_belongs_snapshot) {
            jQuery('#' + window.wpcf_pr_belongs_snapshot[checkbox]).attr('checked', 'checked');
        }
        jQuery(this).parent().slideUp().parent().parent().find('.wpcf-pr-edit').fadeIn();
    });
    jQuery('.wpcf-pr-edit').click(function() {
        window.wpcf_pr_has_snapshot = new Array();
        window.wpcf_pr_belongs_snapshot = new Array();
        var this_id = jQuery(this).attr('id');
        if (this_id == 'wpcf-pr-has-edit') {
            jQuery(this).next().find('.checkbox:checked').each(function() {
                window.wpcf_pr_has_snapshot.push(jQuery(this).attr('id'));
            });
        } else {
            jQuery(this).next().find('input:checked').each(function() {
                window.wpcf_pr_belongs_snapshot.push(jQuery(this).attr('id'));
            });
        }
        jQuery(this).fadeOut().next().slideDown();
    });

    /**
     * POST EDIT SCREEN
     */
    $('#wpcf-post-relationship').on('click', '.js-types-add-child', function() {
        var $button = $(this), $table = $button.parents('.js-types-relationship-child-posts').find('table');
        $.ajax({
            url: $button.attr('href'),
            type: 'get',
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                $button.after('<div style="margin-top:20px;"></div>').next().addClass('wpcf-ajax-loading-small');
            },
            success: function(data) {
                if (data != null) {
                    if (typeof data.output != 'undefined') {
                        $('tbody', $table).prepend(data.output);
                        wpcfRelationshipInit('', 'add');
                        tChildTable.reset();
                        if (typeof wptCallbacks != 'undefined') {
                            wptCallbacks.reset.fire($('tbody tr', $table).first());
                        }
                    }
                    if (typeof data.conditionals != 'undefined' && typeof wptCond != 'undefined') {
                        wptCond.addConditionals(data.conditionals);
                    }
                    if ('undefined' != typeof data.child_id) {
                        $('#types-child-row-'+data.child_id).on('click', '.js-wpt-file-upload', function(event) {
                            wptFile.bindOpen($(this), false);
                        });
                    }
                }
                $button.next().fadeOut(function() {
                    $(this).remove();
                });
                if ( 'undefined' != typeof wptFile ) {
                    wptFile.init();
                }
                /**
                 * select2
                 */
                wpcfBindSelect2($);
				
				var data_for_events = {
					table: $table
				}
				
				$( document ).trigger( 'js_event_wpcf_types_relationship_child_added', [ data_for_events ] );
            }
        });
        return false;
    });
    jQuery('.wpcf-pr-delete-ajax').live('click', function() {
        var $button = $(this), $table = $button.parents('.js-types-relationship-child-posts').find('table');
        var answer = confirm(wpcf_pr_del_warning);
        if (answer == false) {
            return false;
        }
        var object = jQuery(this);
        jQuery.ajax({
            url: jQuery(this).attr('href'),
            type: 'get',
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                object.after('<div style="margin-top:20px;"></div>').next()
                        .addClass('wpcf-ajax-loading-small');
            },
            success: function(data) {
                if (data != null) {
                    if (typeof data.output != 'undefined') {
                        object.parent().parent().fadeOut(function() {
                            jQuery(this).remove();
                            wpcfRelationshipInit('', 'delete');
                        });
                    }
                }
                object.next().fadeOut(function() {
                    jQuery(this).remove();
                });
                /**
                 * reload
                 */
                selectedIndex = $('#wpcf-post-relationship .wpcf-pr-pagination-select').prop('selectedIndex');
                if ( $('tbody tr', $table).length < 2 ) {
                    if ( selectedIndex ) {
                        selectedIndex--;
                        $('#wpcf-post-relationship .wpcf-pr-pagination-select').prop( 'selectedIndex', selectedIndex);
                    }
                }
                $('#wpcf-post-relationship .wpcf-pr-pagination-select').trigger('change');
                /**
                 * select2
                 */
                wpcfBindSelect2($);
            }
        });
        return false;
    });
    jQuery('.wpcf-pr-update-belongs').live('click', function() {
        var object = jQuery(this);
        jQuery.ajax({
            url: jQuery(this).attr('href'),
            type: 'post',
            dataType: 'json',
            data: jQuery(this).attr('href') + '&' + object.prev().serialize(),
            cache: false,
            beforeSend: function() {
                object.after('<div style="margin-top:20px;"></div>').next()
                        .addClass('wpcf-ajax-loading-small');
            },
            success: function(data) {
                object.next().fadeOut(2000, function() {
                    jQuery(this).remove();
                });
            }
        });
        return false;
    });
    $('#wpcf-post-relationship').on('click', '.wpcf-pr-pagination-link', function() {
        if (wpcfPrIsEdited()) {
            var answer = confirm(wpcf_pr_pagination_warning);
            if (answer == false) {
                return false;
            } else {
                window.wpcf_pr_edited = false;
            }
        }
        var $button = $(this), $update = $button.parents('.js-types-relationship-child-posts');
        $.ajax({
            url: $button.attr('href'),
            type: 'get',
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                $button.after('<div style="margin-top:20px;"></div>').next()
                        .addClass('wpcf-ajax-loading-small');
            },
            success: function(data) {
                if (data != null) {
                    if (typeof data.output != 'undefined') {
                        $update.html(data.output);
                        tChildTable.reset();
                        if (typeof wptCallbacks != 'undefined') {
                            wptCallbacks.reset.fire($update);
                        }
                    }
                    if (typeof data.conditionals != 'undefined'
                            && typeof wptCond != 'undefined') {
                        wptCond.addConditionals(data.conditionals);
                    }
                }
                $button.next().fadeOut(function() {
                    $(this).remove();
                });
                /**
                 * select2
                 */
                wpcfBindSelect2($);
            }
        });
        return false;
    });
    $('#wpcf-post-relationship').on('change', '.wpcf-pr-pagination-select', function() {
        if (wpcfPrIsEdited()) {
            var answer = confirm(wpcf_pr_pagination_warning);
            if (answer == false) {
                return false;
            } else {
                window.wpcf_pr_edited = false;
            }
        }
        var $button = $(this), $update = $button.parents('.js-types-relationship-child-posts');
        $.ajax({
            url: $button.val(),
            type: 'get',
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                $button.after('<div style="margin-top:20px;"></div>').next().addClass('wpcf-ajax-loading-small');
            },
            success: function(data) {
                if (data != null) {
                    if (typeof data.output != 'undefined') {
                        $update.html(data.output);
                        tChildTable.reset();
                        if (typeof wptCallbacks != 'undefined') {
                            wptCallbacks.reset.fire($update);
                        }
                    }
                    if (typeof data.conditionals != 'undefined' && typeof wptCond != 'undefined') {
                        wptCond.addConditionals(data.conditionals);
                    }
                }
                $button.next().fadeOut(function() {
                    $(this).remove();
                });
                /**
                 * select2
                 */
                wpcfBindSelect2($);
            }
        });
        return false;
    });
    $('#wpcf-post-relationship').on('click', '.wpcf-sortable a', function() {
        if (wpcfPrIsEdited()) {
            var answer = confirm(wpcf_pr_pagination_warning);
            if (answer == false) {
                return false;
            } else {
                window.wpcf_pr_edited = false;
            }
        }
        var $button = $(this), $update = $button.parents('.js-types-relationship-child-posts');
        $.ajax({
            url: $button.attr('href'),
            type: 'get',
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                $button.after('<div style="margin-top:20px;"></div>').next().addClass('wpcf-ajax-loading-small');
            },
            success: function(data) {
                if (data != null) {
                    if (typeof data.output != 'undefined') {
                        $update.html(data.output);
                        tChildTable.reset();
                        if (typeof wptCallbacks != 'undefined') {
                            wptCallbacks.reset.fire($update);
                        }
                    }
                    if (typeof data.conditionals != 'undefined'
                            && typeof wptCond != 'undefined') {
                        wptCond.addConditionals(data.conditionals);
                    }
                }
                $button.next().fadeOut(function() {
                    $(this).remove();
                });
                /**
                 * select2
                 */
                wpcfBindSelect2($);
            }
        });
        return false;
    });
    $('#wpcf-post-relationship').on('click', '.wpcf-pr-save-ajax', function() {
        var $button = $(this), $row = $button.parents('tr'), rowId = $row.attr('id'), valid = true, $table = $row.closest( '.js-types-child-table' );
        if (typeof wptValidation == 'undefined') {
            $('.js-types-validate', $row).each(function() {
                if ($('#post').validate().element($(this)) == false) {
                    if (typeof typesValidation == 'undefined'
                            || typesValidation.conditionalIsHidden($(this)) == false) {
                        valid = false;
                    }
                }
            });
        } else {
            $('.js-wpt-validate', $row).each(function() {
                if ($('#post').validate().element($(this)) == false) {
                    if (typeof wptValidation == 'undefined'
                            || !wptValidation.isIgnored($(this))) {
                        valid = false;
                    }
                }
            });
        }
        if (valid == false) {
            return false;
        }
        $button.parents('.js-types-relationship-child-posts')
                .find('.wpcf-pr-edited').removeClass('wpcf-pr-edited');
        var height = $row.height(), rand = Math.round(Math.random() * 10000);
        window.wpcf_pr_edited = false;
        $.ajax({
            url: $button.attr('href'),
            type: 'post',
            dataType: 'json',
            data: $row.find(':input').serialize(),
            cache: false,
            beforeSend: function() {
                $row.after('<tr id="wpcf-pr-update-' + rand + '"><td style="height: ' + height + 'px;"><div style="margin-top:20px;" class="wpcf-ajax-loading-small"></div></td></tr>').hide();
            },
            success: function(data) {
                if (data != null) {
                    if (typeof data.output != 'undefined') {
                        $row.replaceWith(data.output).show();
                        $('#wpcf-pr-update-' + rand + '').remove();
                        wpcfRelationshipInit('', 'save');
                        tChildTable.reset();
                        if (typeof wptCallbacks != 'undefined') {
                            wptCallbacks.reset.fire('#'+rowId);
                        }
                        if ( 'undefined' != typeof wptFile ) {
                            wptFile.init();
                        }
                    }
                    if (typeof data.conditionals != 'undefined' && typeof wptCond != 'undefined') {
                        wptCond.addConditionals(data.conditionals);
                    }
                    /**
                     * rebind images
                     */
                    if ( 'function' == typeof bind_colorbox_to_thumbnail_preview ) {
                        bind_colorbox_to_thumbnail_preview();
                    }
                    /**
                     * show errors
                     */
                    $('#wpcf-post-relationship div.message').detach();
                    if ('undefined' != typeof data.errors && 0 < data.errors.length ) {
                        $('#wpcf-post-relationship h3.hndle').after(data.errors);
                    }
                    /**
                     * select2
                     */
                    wpcfBindSelect2($);
					
					var data_for_events = {
						table: $table
					}
					
					$( document ).trigger( 'js_event_wpcf_types_relationship_child_saved', [ data_for_events ] );
                }
            }
        });
        return false;
    });
    $('#wpcf-post-relationship').on('click', '.wpcf-pr-save-all-link', function() {
        var $button = jQuery(this);
        if ($button.attr('disabled') == 'disabled') {
            return false;
        }
        $button.attr('disabled', 'disabled');
        var $update = $button.parents('.js-types-relationship-child-posts'), updateId = $update.attr('id'), $table = $('table', $update), valid = true;
        if (typeof wptValidation == 'undefined') {
            $('.js-types-validate', $table).each(function() {
                if (typeof typesValidation == 'undefined'
                        || typesValidation.conditionalIsHidden($(this)) == false) {
                    if ($('#post').validate().element($(this)) == false) {
                        valid = false;
                    }
                }
            });
        } else {
            $('.js-wpt-validate', $table).each(function() {
                if (typeof wptValidation == 'undefined'
                        || !wptValidation.isIgnored($(this))) {
                    if ($('#post').validate().element($(this)) == false) {
                        valid = false;
                    }
                }
            });
        }
        if (valid == false) {
            $button.removeAttr('disabled');
            return false;
        }
        var rand = Math.round(Math.random() * 10000), height = $('tbody', $table).height();
        window.wpcf_pr_edited = false;
        $('.wpcf-pr-edited', $table).removeClass('wpcf-pr-edited');
        $.ajax({
            url: $button.attr('href'),
            type: 'post',
            dataType: 'json',
            data: $(this).attr('href') + '&' + $(':input', $update).serialize(),
            cache: false,
            beforeSend: function() {
                $('tbody', $table).empty().prepend('<tr id="wpcf-pr-update-' + rand + '"><td style="height: ' + height + 'px;"><div style="margin-top:20px;" class="wpcf-ajax-loading-small"></div></td></tr>');
            },
            success: function(data) {
                if (data != null) {
                    if (typeof data.output != 'undefined') {
                        $update.replaceWith(data.output);
                        $button.removeAttr('disabled');
                        wpcfRelationshipInit('', 'save_all');
                        tChildTable.reset();
                        if (typeof wptCallbacks != 'undefined') {
                            wptCallbacks.reset.fire('#'+updateId);
                        }
                    }
                    if (typeof data.conditionals != 'undefined' && typeof wptCond != 'undefined') {
                        wptCond.addConditionals(data.conditionals);
                    }
                    if ( 'undefined' != typeof wptFile ) {
                        wptFile.init();
                    }
                    /**
                     * rebind images
                     */
                    if ( 'function' == typeof bind_colorbox_to_thumbnail_preview ) {
                        bind_colorbox_to_thumbnail_preview();
                    }
                    /**
                     * show errors
                     */
                    $('#wpcf-post-relationship div.message').detach();
                    if ('undefined' != typeof data.errors && 0 < data.errors.length ) {
                        $('#wpcf-post-relationship h3.hndle').after(data.errors);
                    }
                }
                /**
                 * select2
                 */
                wpcfBindSelect2($);
            }
        });
        return false;
    });

    /**
     * feature image
     */
    $('#wpcf-post-relationship').on('click', '.feature-image', function(event) {
        var $el = $(this);
        var $data = $el.data();
        var $id = $el.attr('id');
        if ( 0 == $data.value ) {
            if ( event ) {
                event.preventDefault();
            }
            // If the media frame already exists, reopen it.
            if ( frame_relationship[$id] ) {
                frame_relationship[$id].open();
                return;
            }
            // Create the media frame.
            frame_relationship[$id] = wp.media.frames.customHeader = wp.media({
                // Set the title of the modal.
                title: $el.html(),
                // Tell the modal to show only images.
                library: {
                    type: "image"
                },
            });
            // When an image is selected, run a callback.
            frame_relationship[$id].on( 'select', function() {
                // Grab the selected attachment.
                var attachment = frame_relationship[$id].state().get('selection').first();

                var $parent = $el.parent();
                if ( 0 == $('.wpt-file-preview img', $parent).length) {
                    $('.wpt-file-preview', $parent).append('<img src="">');
                }
                if ( 'undefined' != typeof attachment.id ) {
                    $('.feature-image-id', $el.parent()).val(attachment.id);
                    $el.html($data.remove);
                }
                if ( 'undefined' != typeof attachment.attributes.sizes.thumbnail ) {
                    $('.wpt-file-preview img', $parent).attr('src', attachment.attributes.sizes.thumbnail.url);
                }
                frame_relationship[$id].close();
            });
            frame_relationship[$id].open();
        } else {
            $('.feature-image-id', $el.parent()).val(0);
            $('.wpt-file-preview', $el.parent()).html('');

            $(this).html($data.set);
        }
        return false;
    });
    // We need to hide the _wpcf_belongs_xxxx_id field for WPML.

    jQuery('#icl_mcs_details table tbody tr').each(function() {
        var name = jQuery(this).find('td').html();
        if (name.search(/^_wpcf_belongs_.*?_id/) != -1) {
            jQuery(this).hide();
        }

    });

    // Pagination
    $('#wpcf-post-relationship').on('change', '.wpcf-relationship-items-per-page', function() {
        var $button = $(this), $update = $button.parents('.js-types-relationship-child-posts');
        $.ajax({
            url: ajaxurl,
            type: 'get',
            dataType: 'json',
            data: $button.data('action') + '&_wpcf_relationship_items_per_page=' + $button.val(), //+'&'+update.find('.wpcf-pagination-top :input').serialize(),
            cache: false,
            beforeSend: function() {
                $button.after('<div style="margin-top:20px;" class="wpcf-ajax-loading-small"></div>');
            },
            success: function(data) {
                if (data != null) {
                    if (typeof data.output != 'undefined') {
                        $update.html(data.output);
                        $button.next().fadeOut(function(){$(this).remove();});
                        tChildTable.reset();
                        if (typeof wptCallbacks != 'undefined') {
                            wptCallbacks.reset.fire($update);
                        }
                    }
                    if (typeof data.conditionals != 'undefined'
                            && typeof wptCond != 'undefined') {
                        wptCond.addConditionals(data.conditionals);
                    }
                }
                /**
                 * select2
                 */
                wpcfBindSelect2($);
            }
        });
    });
    /*
     * 
     * Init
     */
    wpcfRelationshipInit('', 'init');
});


function wpcfPrIsEdited() {
    if (jQuery('.wpcf-pr-edited').length < 1) {
        return false;
    }
    return true;
}

function wpcfPrUpdateIDs(ids) {
    var x;
    for (x in ids) {
        jQuery('#wpcf-post-relationship table td').find(':input[name^="wpcf_post_relationship[' + x + ']"]').each(function() {
            jQuery(this).attr('name', jQuery(this).attr('name').replace("[" + x + "]", "[" + ids[x] + "]"));
        });
    }
}

/**
 * Basic checks on Child tables inside .wpcf-pr-has-entries
 */
function wpcfRelationshipInit(selector, context) {
    jQuery(selector + '.wpcf-pr-has-entries').each(function() {
        var container = jQuery(this);
        jQuery(this).find('table').each(function() {
            var table = jQuery(this);
            // Show/hide if no children posts
            if (table.find('tbody tr').length < 1) {
                table.css('visibility', 'hidden');
                container.find('.wpcf-pagination-boottom')
                        .css('visibility', 'hidden');
                container.find('.wpcf-pr-save-all-link')
                        .attr('disabled', 'disabled');
            } else {
                table.css('visibility', 'visible');
                container.find('.wpcf-pagination-boottom')
                        .css('visibility', 'visible');
                container.find('.wpcf-pr-save-all-link')
                        .removeAttr('disabled');
            }
        });
    });
}

/**
 * select2
 */
function wpcfBindSelect2($) {
    $('.wpcf-pr-belongs').select2({
        id: function(item){
            return item.ID;
        },
        allowClear: true,
        placeholder: $(this).data('placeholder'),
        minimumInputLength: 0,
        formatInputTooShort: $(this).data('input-too-short'),
        ajax: {
            url: ajaxurl,
            dataType: 'json',
            quietMillis: 250,
            data: function (term, page) {
                return {
                    nounce: $(this).data('nounce'),
                    post_id: $(this).data('post-id'),
                    post_type: $(this).data('post-type'),
                    action: 'wpcf_relationship_search',
                    page: page || 1,
                    s: term, // search term
                };
            },
            results: function (data, page) {
                var more = (page * data.posts_per_page) < data.total_count;
                return { results: data.items, more: more };
            },
            cache: true
        },
        initSelection: function(element, callback) {
            var id = $(element).val();
            if (0 < parseInt(id)) {
                $(element).select2("data", { ID: 0, post_title: $(element).data('loading') });
                $(element).select2("enable", false);
                $.ajax({
                    url: ajaxurl,
                    dataType: "json",
                    data: {
                        nounce: element.data('nounce'),
                        p: id,
                        post_id: element.data('post-id'),
                        post_type: element.data('post-type'),
                        action: 'wpcf_relationship_entry'
                    }
                }).done(function(data) {
                    $(element).select2("enable", true);
                    callback(data);
                });
            }
        },
        formatResult: function(item) {
            return '<div data-id="'+item.ID+'" class="item">' + item.post_title + '</div>';
        },
        formatSelection: function(item) {
            var target = $('#wpcf_pr_belongs_'+item.parent_id+'_'+item.post_type);
            $.ajax({
                url: ajaxurl,
                dataType: "json",
                data: {
                    action: 'wpcf_relationship_save',
                    nounce: target.data('nounce'),
                    post_id: item.parent_id,
                    post_type: item.post_type,
                    p: item.ID
                }
            });
            target.val(item.ID);
            $('a.button', target.closest('.form-item')).attr('href', item.edit_link).removeClass('disabled');
            return item.post_title;
        },
    }).on('select2-clearing', function() {
        $.ajax({
            url: ajaxurl,
            dataType: "json",
            data: {
                nounce: $(this).data('nounce'),
                post_id: $(this).data('post-id'),
                post_type: $(this).data('post-type'),
                action: 'wpcf_relationship_delete'
            }
        }).done(function(data) {
            $('a.button', $(data.target)).addClass('disabled').attr('href', '#');
        });
    });
}
jQuery(document).ready(function($) {
    wpcfBindSelect2($);
});


/**
 * @version 1.2
 * @type {{}|WPV_Toolset|{}|WPV_Toolset}
 * @revision 25/09/2015 22:31
 */
var WPV_Toolset = WPV_Toolset  || {};
WPV_Toolset.media_manager = WPV_Toolset.media_manager || {};
WPV_Toolset.media_manager.set_to_post_id = 0;
WPV_Toolset.media_manager.current_plugin = '';
WPV_Toolset.media_manager.instances = {};

if ( typeof WPV_Toolset.only_img_src_allowed_here === "undefined" ) {
	/*
	* WPV_Toolset.only_img_src_allowed_here
	*
	* This array holds IDs for textfields where you can insert media URLs using this media manager
	* Note that they can only hold image URLs, not img tags or shortcodes
	* To extend it, make your script dependant in this 'icl_media-manager-js' so this is loaded early
	* Then, push to this array
	*
	* @todo Move the already existing IDs to the Views scripts
	*/
	WPV_Toolset.only_img_src_allowed_here = [
		'wpv-pagination-spinner-image',
		'wpv-dps-spinner-image',
		'wpv_filter_meta_html_css',
		'wpv_filter_meta_html_js',
		'wpv_layout_meta_html_css',
		'wpv_layout_meta_html_js'
	];
}

jQuery( document ).ready( function( $ ) {
	if ( $( '#toolset-edit-data' ).length > 0 ) {
		WPV_Toolset.media_manager.set_to_post_id = $( '#toolset-edit-data' ).val();
		WPV_Toolset.media_manager.current_plugin = $( '#toolset-edit-data' ).data( 'plugin' );
	}
});

/**
 * Thanks to Thomas Griffin for his super useful example on Github
 *
 * https://github.com/thomasgriffin/New-Media-Image-Uploader
 */
jQuery( document ).ready( function( $ ) {
    
	$( document.body ).on( 'click', '.js-wpv-media-manager', function( e ) {
		// Prevent the default action from occuring.
		e.preventDefault();
		// Check whether we need to set the parent post ID value
		var set_to_post_id = WPV_Toolset.media_manager.set_to_post_id,
		referred_id = $( this ).attr( 'data-id' );
		if (
			typeof referred_id !== 'undefined' 
			&& referred_id !== false
		) {
			set_to_post_id = referred_id;
		}
		// Set the active target by its content data attribute value
		var active_textarea = $( this ).data( 'content' );
		window.wpcfActiveEditor = active_textarea;
		// Make sure the post parent ID is an integer, force zero otherwise
		set_to_post_id = parseInt( set_to_post_id ) || 0;
		// If the frame already exists, re-open it.
		if ( WPV_Toolset.media_manager.instances[ set_to_post_id ] ) {
			WPV_Toolset.media_manager.instances[ set_to_post_id ].open();
			return;
		} else {
			// Otherwise, set the model post ID and create the frame
			//if ( set_to_post_id !== 0 ) {
				wp.media.model.settings.post.id = set_to_post_id;
			//} else {
				//wp.media.model.settings.post.id = 0;
			//}
		}
		
		WPV_Toolset.media_manager.instances[ set_to_post_id ] = wp.media({
			//Create our media frame
			className: 'media-frame mojo-media-frame js-wpv-media-frame',
			frame: 'post',
			multiple: false, //Disallow Mulitple selections
			library: {
				type: 'image' //Only allow images
			}
		});
		
		WPV_Toolset.media_manager.instances[ set_to_post_id ].on('open', function(event){
			var media_button_insert = $('.media-button-insert'),
			media_frame = $('.js-wpv-media-frame');
			$('li.selected').removeClass('selected').find('a.check').trigger('click');
			media_button_insert.addClass('button-secondary').removeClass('button-primary');
			media_frame.find('.media-menu').html('');
			$('.clear-selection').on('click', function() {
				media_button_insert.parent().find('.js-wpv-media-type-not-insertable').remove();
				media_button_insert.addClass('button-secondary').removeClass('button-primary').show();
			});
		}); 
		
		WPV_Toolset.media_manager.instances[ set_to_post_id ].on('insert', function(){
			// Watch changes in wp-includes/js/media-editor.js
			var media_attachment = WPV_Toolset.media_manager.instances[ set_to_post_id ].state().get('selection').first().toJSON(),
			filetype = media_attachment.type;
			if ( filetype == 'image' ) {
				var size = $('.attachment-display-settings .size').val(),// WARNING size might be undefined for some image types, like BMP or TIFF, that do not generate thumbnails
				shortcode,
				code,
				options,
				classes,
				align,
				target_url;
				if ( $.inArray( window.wpcfActiveEditor, WPV_Toolset.only_img_src_allowed_here ) !== -1 ) {
					if ( size ) {
						code = media_attachment.sizes[size].url;
					} else {
						code = media_attachment.url;
					}
					$('.js-' + window.wpcfActiveEditor).val('');
					$('.js-' + window.wpcfActiveEditor + '-preview').attr("src",code).show();
				} else {
					// Basic img tag options
					if ( size ) {
						options = {
							tag:'img',
							attrs: {
								src: media_attachment.sizes[size].url
							},
							single: true
						};
					} else {
						options = {
							tag:'img',
							attrs: {
								src: media_attachment.url
							},
							single: true
						};
					}
					if ( media_attachment.hasOwnProperty( 'alt' ) && media_attachment.alt ) {
						options.attrs.alt = media_attachment.alt;
					}
					if ( size ) {
						options.attrs.width = media_attachment.sizes[size].width;
						options.attrs.height = media_attachment.sizes[size].height;
					} else {
						options.attrs.width = 1;
					}
					classes = [];
					align = $('.alignment').val();
					if ( align == 'none' ) {
						align = false;
					}
					// Only assign the align class to the image if we're not printing a caption, since the alignment is sent to the shortcode.
					if ( align && ! media_attachment.caption ) {
						classes.push( 'align' + align );
					}
					if ( size ) {
						classes.push( 'size-' + size );
					}
					options.attrs['class'] = _.compact( classes ).join(' ');
					// Generate the `a` element options, if they exist.
					if ( $('select.link-to').val() == 'file' ) {
						target_url = media_attachment.url;
					} else if ( $('select.link-to').val() == 'custom' ) {
						target_url = $('.link-to-custom').val();
					} else {
						target_url = false;
					}
					if ( target_url ) {
						options = {
							tag: 'a',
							attrs: {
								href: target_url
							},
							content: options
						};
					}
					code = wp.html.string( options );
					// Generate the caption shortcode if needed
					if ( media_attachment.caption ) {
						shortcode = {};
						if (size ) {
							if ( media_attachment.sizes[size].width ) {
								shortcode.width = media_attachment.sizes[size].width;
							}
						} else {
							shortcode.width = 1;
						}
						if ( align ) {
							shortcode.align = 'align' + align;
						}
						code = wp.shortcode.string({
							tag: 'caption',
							attrs: shortcode,
							content: code + ' ' + media_attachment.caption
						});
					}
				}
				icl_editor.insert(code);
				if ( $.inArray( window.wpcfActiveEditor, WPV_Toolset.only_img_src_allowed_here ) !== -1 ) {
					$('.js-' + window.wpcfActiveEditor).trigger('keyup');
				}
			} else {
				var options,
				media_shrtcode = '';
				if ( $('select.link-to').val() == 'embed' ) {
					options = {
						tag: filetype,
						attrs: {
							src: media_attachment.url
						},
						type: true,
						content: ''
					};
					if ( media_attachment.hasOwnProperty( 'caption' ) && media_attachment.caption ) {
						options.attrs.caption = media_attachment.caption;
					}
					media_shrtcode = wp.shortcode.string( options );
				} else {
					options = {
						tag: 'a',
						attrs: {
							href: media_attachment.url
						},
						content: media_attachment.title
					};
					media_shrtcode = wp.html.string( options );
					/*
					media_shrtcode = '<a href="' + media_attachment.url + '">' + media_attachment.title + '</a>';
					*/
				}
				icl_editor.insert(media_shrtcode);
			}
			$(' #' + window.wpcfActiveEditor ).trigger( 'js_icl_media_manager_inserted' );
		});
		
		var _AttachmentDisplay = wp.media.view.Settings.AttachmentDisplay;
		wp.media.view.Settings.AttachmentDisplay = _AttachmentDisplay.extend({
			render: function() {
				_AttachmentDisplay.prototype.render.apply(this, arguments);
				var attachment = this.options.attachment,
				attach_type = '',
				insert_button = $('.media-button-insert').show();
				insert_button.parent().find('.js-wpv-media-type-not-insertable').remove();
				if ( attachment ) {
					attach_type = attachment.get('type');
				}
				if ( attach_type == 'image' && $.inArray( window.wpcfActiveEditor, WPV_Toolset.only_img_src_allowed_here ) !== -1 ) {
					this.$el.find('select.link-to').parent().remove();
					this.model.set('link', 'none');
					this.$el.find('select.alignment').parent().remove();
				} else {
					this.$el.find('select.link-to').find('option[value="post"]').remove();
					if ( $.inArray( window.wpcfActiveEditor, WPV_Toolset.only_img_src_allowed_here ) !== -1 ) {
						insert_button.hide().parent().append('<button disabled="disabled" class="media-button button-large button-secondary js-wpv-media-type-not-insertable">' + icl_media_manager.only_img_allowed_here + '</button>');
					}
				}
				this.updateLinkTo();
			}
		});
	
	// Now that everything has been set, let's open up the frame.
	WPV_Toolset.media_manager.instances[ set_to_post_id ].open();
	});
});


jQuery(document).on("DOMNodeInserted", function(){
    var toolset_edit_plugin = jQuery( '#toolset-edit-data' ).data( 'plugin' );
	if ( toolset_edit_plugin === 'views' ){
        // Lock uploads to "Uploaded to this post"
        jQuery('select.attachment-filters [value="uploaded"]').prop( 'selected', true ).parent().trigger('change');
        jQuery('.attachments-browser .media-toolbar-secondary .attachment-filters').addClass('hidden');
    }
});
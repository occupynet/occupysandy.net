<?php
if ( file_exists( dirname(__FILE__) . '/editor-addon-generic.class.php') && !class_exists( 'WPV_Editor_addon' )  ) {

    require_once( dirname(__FILE__) . '/editor-addon-generic.class.php' );


    class WPV_Editor_addon extends Editor_addon_generic {
		
		public function __construct( $name, $button_text, $plugin_js_url, $media_button_image = '', $print_button = true, $icon_class = '' ) { 
			parent::__construct( $name, $button_text, $plugin_js_url, $media_button_image, $print_button, $icon_class );
			if ( 
				$print_button
				&& ( 
					$media_button_image != '' 
					|| $icon_class != '' 
				) 
			) {
                global $wp_version;
                if ( version_compare( $wp_version, '3.1.4', '>' ) ) {
                    add_action( 'media_buttons', array( $this, 'add_fields_views_button' ), 10, 2 );
                } else {
                    add_action( 'media_buttons_context', array( $this, 'add_fields_views_button' ), 10, 2 );
                }
            }
			
		}

		/**
		* get_fields_list
		*
		* This is used in the Loop Wizard, so watch out
		*
		*/
		
        function get_fields_list() {
            return apply_filters( 'toolset_editor_addon_post_fields_list', $this->items );
        }

        /**
         * Adding a "V" button to the menu
         * @param string $context
         * @param string $text_area
		 * -----------------------
         * @param boolean $standard_v
		 * @param $add_views
         */
		function add_form_button( $context, $text_area = '', $standard_v = true, $add_views = false, $codemirror_button = false ) {
			return;
		}
		
        function add_fields_views_button( $context, $text_area = '', $codemirror_button = false ) {
            /**
             * turn off button
             */
            if ( ! apply_filters('toolset_editor_add_form_buttons', true) ) {
                return;
            }

            global $wp_version, $post;
			
			$post_id = 0;
            if ( 
				is_object( $post ) 
				&& isset( $post->ID ) 
			) {
                $post_id = $post->ID;
            }

            if ( 
				empty( $context ) 
				&& $text_area == '' 
			) {
                return;
            }
            // WP 3.3 changes ($context arg is actually a editor ID now)
            if ( 
				version_compare( $wp_version, '3.1.4', '>' ) 
				&& ! empty( $context ) 
			) {
                $text_area = $context;
            }

            // Apply filters
            $this->items = apply_filters( 'editor_addon_items_' . $this->name, $this->items );

            $menus = array();

            if ( $this->items ) {
				foreach ( $this->items as $item ) {
					// $item = array($text, $shortcode, $menu, $function_name = '');
					if ( ! isset( $menus[$item[2]] ) ) {
						$menus[$item[2]] = array();
					}
					$menus[$item[2]][$item[0]] = $item;
				}
			}

			
            // Apply filters
            $menus = apply_filters( 'editor_addon_menus_' . $this->name, $menus );//echo '<pre style="text-align:left">';print_r($menus);echo '</pre>';

            // Sort menus
            if ( is_array( $menus ) ) {
                $menus = $this->sort_menus( $menus );
            }
			
			$dialog_links = array();
			$dialog_content = '';
			foreach ( $menus as $menu_key => $menu_data ) {
				$dialog_links[] = '<li data-id="' . md5( $menu_key ) .'" class="editor-addon-top-link" data-editor_addon_target="editor-addon-link-' . md5( $menu_key ) . '">' . $menu_key . ' </li>';
				
				$post_field_section_classname = ( $menu_key == __( 'Post field', 'wpv-views' ) ) ? ' js-wpv-shortcode-gui-group-list-post-field-section' : '';
				
				$dialog_content .= '<div class="group"><h4 data-id="'.md5( $menu_key ).'" class="group-title  editor-addon-link-' . md5( $menu_key ) . '-target">' . $menu_key . "</h4>";
				$dialog_content .= '<ul class="wpv-shortcode-gui-group-list js-wpv-shortcode-gui-group-list' . $post_field_section_classname . '" data-editor="' . esc_attr( $text_area ) . '">';
				foreach ( $menu_data as $menu_item_title => $menu_item_data ) {
					if ( 
						isset( $menu_item_data[0] ) 
						&& ! is_array( $menu_item_data[0] ) 
						&& 'css' != $menu_item_title // For some reason Types fields bleed this css entry everywhere! Related to the editor_addon_menus_ filter
					) {
                        if ( $menu_item_data[3] != '' ) {
							$dialog_content .= sprintf(
								'<li class="item button button-small" onclick="%s; return false;" data-post-id="%d">%s</li>',
								$menu_item_data[3],
								$post_id,
								$menu_item_data[0]
							);
                        } else {
							$short_code = $menu_item_data[1];
							$short_code = '[' . $short_code . ']';
                            $short_code = base64_encode( $short_code );
                            $link_text = $menu_item_data[0];
                            $dialog_content .= '<li class="item button button-small" onclick="insert_b64_shortcode_to_editor(\'' . $short_code . '\', \'' . $text_area . '\'); return false;">' . $link_text . "</li>";
                        }
                    }
				}
				$dialog_content .= '</ul>';
				$dialog_content .= '</div>';
			}

            $direct_links = implode( '', $dialog_links );
            $dropdown_class = 'js-editor_addon_dropdown-'.$this->name;
            $icon_class = 'js-wpv-shortcode-post-icon-'.$this->name;
            $button_label = __( 'Fields and Views', 'wpv-views' );
           
			// Codemirror (new layout) button
            if ( $codemirror_button ) {
                 $addon_button = '<button class="js-code-editor-toolbar-button js-code-editor-toolbar-button-v-icon button-secondary">'.
                        '<i class="icon-views-logo ont-icon-18"></i><span class="button-label">'. __('Fields and Views', 'wpv-views') .'</span></button>';
            } else if ( '' !== $this->media_button_image ) {
                $addon_button = '<span class="button wpv-shortcode-post-icon '. $icon_class .'"><img src="' . $this->media_button_image . '" />' . $button_label . '</span>';
            } else if ( '' !== $this->icon_class ) {
                $addon_button = '<span class="button wpv-shortcode-post-icon '. $icon_class .'"><i class="'.$this->icon_class.'"></i><span class="button-label">' . $button_label . '</span></span>';
            }

            // add search box
            $searchbar = $this->get_search_bar();

            // generate output content
            $out = '' .
            $addon_button . '
            <div class="editor_addon_dropdown '. $dropdown_class .'" id="editor_addon_dropdown_' . rand() . '">
                <h3 class="title">' . $this->button_text . '</h3>
                <div class="close">&nbsp;</div>
                <div class="editor_addon_dropdown_content">'
                        . apply_filters( 'editor_addon_dropdown_top_message_' . $this->name, '' ) 
						. $searchbar
                        . '<div class="direct-links-desc"><ul class="direct-links"><li class="direct-links-label">' . __( 'Jump to:', 'wpv-views' ) . '</li>' . $direct_links . '</ul></div>'
                        . $dialog_content
                        . apply_filters( 'editor_addon_dropdown_bottom_message' . $this->name, '' ) 
                        . '
                </div>
            </div>';

            // WP 3.3 changes
            if ( version_compare( $wp_version, '3.1.4', '>' ) ) {
                echo apply_filters( 'wpv_add_media_buttons', $out );
            } else {
                return apply_filters( 'wpv_add_media_buttons', $context . $out );
            }
        }

        /**
         *
         * Sort menus (and menu content) in an alphabetical order
         *
         * Still, keep Basic and Taxonomy on the top and Other Fields at the bottom
         *
         * @param array $menu menu reference
         */
        function sort_menus( $menus ) {
            // keep main references if set (not set on every screen)
            $menu_temp = array();
            $menu_names = array(
				__( 'WPML', 'wpv-views' ),
                __( 'User View', 'wpv-views' ),
				__( 'Taxonomy View', 'wpv-views' ),
                __( 'Post View', 'wpv-views' ),
                __( 'View', 'wpv-views' ),
				__( 'Post field', 'wpv-views' ),
				__( 'User basic data', 'wpv-views' ),
                __( 'Content Template', 'wpv-views' ),
                __( 'Taxonomy', 'wpv-views' ),
                __( 'Basic', 'wpv-views' )
            );
			
			$menus_sorted_first = array();
			$menus_sorted_last = array();
			$menus_sorted = array();
			
			$menus_on_top = array(
				__( 'Basic', 'wpv-views' ),
				__( 'Taxonomy', 'wpv-views' ),
				__( 'Content Template', 'wpv-views' ),
				__( 'User basic data', 'wpv-views' )
			);
			
			$menus_on_bottom = array(
				__( 'Post field', 'wpv-views' ),
				__( 'View', 'wpv-views' ),
				__( 'Post View', 'wpv-views' ),
				__( 'Taxonomy View', 'wpv-views' ),
				__( 'User View', 'wpv-views' ),
				__( 'WPML', 'wpv-views' )
			);
			
			$menus_keys = array_keys( $menus );
			
			foreach ( $menus_keys as $mk ) {
				if ( in_array( $mk, $menus_on_top ) ) {
					$menus_sorted_first[$mk] = $menus[$mk];
					unset( $menus[$mk] );
				} else if ( in_array( $mk, $menus_on_bottom ) ) {
					$menus_sorted_last[$mk] = $menus[$mk];
					unset( $menus[$mk] );
				}
			}
			
			//ksort( $menus );
			
			$menus_sorted = array_merge( $menus_sorted_first, $menus, $menus_sorted_last );
			
			return $menus_sorted;
			
        }

        function get_search_bar() {
            $searchbar  = '<div class="searchbar">';
            $searchbar .=   '<label for="searchbar-input">' . __( 'Search', 'wpv-views' ) . ': </label>';
            $searchbar .=   '<input id="searchbar-input" type="text" class="search_field" onkeyup="wpv_on_search_filter(this)" />';
            $searchbar .= '</div>';
            return $searchbar;
        }

    }

    /**
     * Renders JS for inserting shortcode from thickbox popup to editor.
     *
     * @param type $shortcode
	 * maybe DEPRECATED ???
     */
    if( !function_exists('editor_admin_popup_insert_shortcode_js') )
    {
        function editor_admin_popup_insert_shortcode_js( $shortcode ) { // Types now uses ColorBox, it's not used in Views anymore. Maybe DEPRECATED

            ?>
            <script type="text/javascript">
                //<![CDATA[

                // Close popup
                window.parent.jQuery('#TB_closeWindowButton').trigger('click');

                // Check if there is custom handler
                if (window.parent.wpcfFieldsEditorCallback_redirect) {
                    eval(window.parent.wpcfFieldsEditorCallback_redirect['function'] + '(\'<?php echo esc_js( $shortcode ); ?>\', window.parent.wpcfFieldsEditorCallback_redirect[\'params\'])');
                } else {
                    // Use default handler
                    window.parent.icl_editor.insert('<?php echo $shortcode; ?>');
                }

                //]]>
            </script>
            <?php
        }
    }

}
<?php

if ( ! class_exists( 'Toolset_Admin_Bar_Menu' ) ) {

    class Toolset_Admin_Bar_Menu {
        
        // singleton
        private static $instance;

        /**
         * Avoid executing more than once the code
         * @var type bool
         */
        private $done;
        
        /** @const */
        public static $default_wordpress_archives = array( 'home-blog', 'search', 'author', 'year', 'month', 'day' );

        private function __construct() {
            $this->done = false;

            add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 99 );
			add_filter( 'toolset_filter_toolset_admin_bar_menu_disable', array( $this, 'admin_bar_menu_disable' ), 1 );
            
            if ( is_admin() ) {
                
                /*
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
                */
                
            } else {
                add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
            }
        }

        public static function get_instance() {
            if ( ! self::$instance ) {
                self::$instance = new Toolset_Admin_Bar_Menu();
            }

            return self::$instance;
        }

        public function enqueue_styles() {
            
            if( ! is_admin_bar_showing() ) {
                return;
            }
            
            if ( $this->is_layouts_available() ) {
                
                // Check also @WPDD_Layouts::enqueue_toolset_common_styles()
                global $wpddlayout;
                $wpddlayout->enqueue_styles( array( 'toolset-common' ) );
                
            } else if ( $this->is_views_available() ) {

                wp_enqueue_style( 'onthegosystems-icons', WPV_URL_EMBEDDED . '/common/res/css/toolset-common.css', array(), WPV_VERSION );
                wp_enqueue_style( 'toolset-common', WPV_URL_EMBEDDED . '/onthego-resources/onthegosystems-icons/css/onthegosystems-icons.css', array( 'onthegosystems-icons' ), WPV_VERSION );
                
            }
        }
        
        /**
         * @see action admin_bar_menu
         */
        public function admin_bar_menu( $wp_admin_bar ) {
            // Check this haven't called more than once
            if ( $this->done ) {
                return;
            }
			
			/**
			* Filter to disable the Toolset Admin Bar menu
			*
			* Used to disable the Admin Bar Menu when the 'show_admin_bar_shortcut' entry on the 'toolset_options' option has an 'off' value
			* It is up to the plugins to produce a GUI for setting that value
			*
			* @since 1.7
			*/
			
			if ( apply_filters( 'toolset_filter_toolset_admin_bar_menu_disable', false ) ) {
				return;
			}

            if ( $this->get_default_plugin() && $this->has_capatibilities() && $this->is_assignable() ) {
                
                //  
                //  We create a Toolset menu and a child submenu.
                //  Clicking the parent achieves the same result than clicking the child
                //  Maybe we add extra menu options in the future
                //  
                //  (Icon) Design with Toolset < $href >
                //   |
                //   +- $title < $href >
                //   

                $menu_data = $this->get_menu_data();
                if ( empty( $menu_data ) ) {
                    // If no menu is available, then don't render menu
                    return;
                }
                list( $title, $href ) = $menu_data;
                
                $args = array(
                    'id' => 'toolset_admin_bar_menu',
                    'title' => __( 'Design with Toolset', 'wpv-views' ),
                    'href' => $href,
                    'meta' => array( 'class' => 'toolset-edit-link' )
                );
                $wp_admin_bar->add_node( $args );

                $args = array(
                    'parent' => 'toolset_admin_bar_menu',
                    'id' => 'toolset_design_this_item',
                    'title' => $title,
                    'href' => $href,
                );
                $wp_admin_bar->add_node( $args );
				
				$settings_href = $this->get_settings_href();
                $args = array(
                    'parent' => 'toolset_admin_bar_menu',
                    'id' => 'toolset_remove_this_menu',
                    'title' => __( 'Remove this menu', 'wpv-views' ),
                    'href' => $settings_href,
                );
                $wp_admin_bar->add_node( $args );

                $this->done = true;
            }
        }
		
		/**
		* Disable the Admin Bar Menu entry when the 'show_admin_bar_shortcut' entry on the 'toolset_options' option has an 'off' value
		*
		* @since 1.7
		*/
		public function admin_bar_menu_disable( $state ) {
			$toolset_options = get_option( 'toolset_options', array() );
			$toolset_admin_bar_menu_remove = ( isset( $toolset_options['show_admin_bar_shortcut'] ) && $toolset_options['show_admin_bar_shortcut'] == 'off' ) ? true : false;
			if ( $toolset_admin_bar_menu_remove ) {
				$state = true;
			}
			return $state;
		}

        /**
         * User is admin or similar?
         * @return boolean
         */
        private function has_capatibilities() {
            $manage_options = current_user_can( 'manage_options' );

            $has_layouts = $this->is_layouts_available();

            $has_views = $this->is_views_available();

            return $manage_options && ( $has_layouts || $has_views );
        }

        /**
         * Can you assign what you are seeing right now to a Layout, Content Template or WordPress Archive?
         * @return boolean
         */
        private function is_assignable() {

            $context = $this->get_context();
            if ( ! $context ) { return false; }
            list( $type, $class ) = explode( '|', $context );
            
            if ( is_admin() ) {                
                
                /*
                global $post_type;
                $screen = get_current_screen();
                if( preg_match( '/^(edit|edit-tags|post)$/', $screen->base ) && empty($screen->action) ) {

                    if( 'edit' === $screen->base ) {
                        
                        // $post_type | archive
                        
                        if ( 'page' === $post_type ) {
                            return false;
                        }

                        $post_type_object = get_post_type_object( $screen->post_type );
                        if ( ! ( $post_type_object->publicly_queryable && $post_type_object->has_archive ) ) {
                            return false;
                        }
                        
                    } else if( 'edit-tags' === $screen->base ) {
                        
                        // $taxonomy | archive

                        $taxonomy = get_taxonomy( $screen->taxonomy );
                        if ( !( $taxonomy->public ) ) {
                            return false;
                        }

                    } else if( 'post' === $screen->base ) {
                        
                        // $post_type | page
                        
                        $post_type_object = get_post_type_object( $screen->post_type );
                        if ( ! $post_type_object->publicly_queryable ) {
                            return false;
                        }

                    }

                }
                 */
                
            } else {
                
                // Backend
                if( 'page' === $class && '404' === $type ) {
                    
                    return $this->is_layouts_available() && ( WPDD_Layouts_Users_Profiles::user_can_create() && WPDD_Layouts_Users_Profiles::user_can_assign() || WPDD_Layouts_Users_Profiles::user_can_edit() );
                    
                } else if ( 'page' === $class ) {

                    $post_type_object = get_post_type_object( $type );
                    $is_cpt = $post_type_object != null;
                    if( ! $is_cpt /* || ! $post_type_object->publicly_queryable */ ) {
                        return false;
                    }

                } else if ( 'archive' === $class && in_array( $type, self::$default_wordpress_archives ) ) {
                    // DO NOTHING
                } else if ( 'archive' === $class && 'page' === $type ) {
                    return false;
                } else if ( 'archive' === $class ) {

                    $taxonomy = get_taxonomy( $type );
                    $is_tax = $taxonomy !== false;
                    if ( $is_tax && ! $taxonomy->public ) {
                        return false;
                    }
                    
                    $post_type_object = get_post_type_object( $type );
                    $is_cpt = $post_type_object != null;
                    if( $is_cpt && ( ! $post_type_object->publicly_queryable || ! $post_type_object->has_archive ) ) {
                        return false;
                    }

                }                

            }
            
            return true;
        }

        private function is_layouts_available() {
            global $wpddlayout;

            // class WPDDL_Admin_Pages exists only in full version
            return class_exists( 'WPDDL_Admin_Pages' ) && isset( $wpddlayout ) && is_object( $wpddlayout );
        }

        private function is_views_available() {
            global $WP_Views;

            // class WP_Views_plugin exists only in full version
            return class_exists( 'WP_Views_plugin' ) && isset( $WP_Views ) && is_object( $WP_Views );
        }

        /**
         * Get the best plugin available
         * @return string (layouts|views|)
         */
        private function get_default_plugin() {
            // Layouts always has precedence
            if ( $this->is_layouts_available() ) {
                return 'layouts';
            } else if ( $this->is_views_available() ) {
                return 'views';
            } else {
                // Other toolset plugins may be present
                return null;
            }
        }
		
		private function get_settings_href() {
            $plugin_name = $this->get_default_plugin();
            
            if( 'layouts' === $plugin_name ) {
                return admin_url( 'admin.php?page=dd_layout_settings' ).'#toolset-admin-bar-settings';
            } else /* 'views' */ {
                return admin_url( 'admin.php?page=views-settings' ).'#toolset-admin-bar-settings';
            }
        }
      
        /**
         * Finds the right action depending on what you're seeing and have done
         * @returns array ($title, $href) or null (do not show menu)
         */
        private function get_menu_data() {
            
            $context = $this->get_context();
            if ( ! $context ) {
                // No context => No menu
                return null;
            }
            
            // Get type {post types, taxonomies, wordpress archives slugs, 404} and class {page, archive}
            list( $type, $class ) = explode( '|', $context );

            // We are using the best plugin available by default, unless state otherwise below
            $plugin_used = $this->get_default_plugin();
            
            $layout_id = 0;
            $ct_id = 0;
            $wpa_id = 0;
            $post_id = 0;
            $edit_link = null;
                        
            $is_new = true;
            // warning! syntax sugar ahead
            
            // Layouts - Edit Link
            if ( $is_new && $this->is_layouts_available() && WPDD_Layouts_Users_Profiles::user_can_edit() ) {
                
                global $wpddlayout;
                
                if( is_admin() ) {
                    
                    /*                    
                    // Only individual pages, post type pages, post type archives 
                    // and taxonomy archives are editable from backend
                    //
                    
                    $screen = get_current_screen();
                    if( preg_match( '/^(edit|edit-tags|post)$/', $screen->base ) ) {
                        // Exists layout? $layout_id?
                            
                        if( 'edit' === $screen->base ) {
                            // $post_type | archive
                            
                            $post_type_object = get_post_type_object( $screen->post_type );
                            $option_type_name = WPDD_layout_post_loop_cell_manager::OPTION_TYPES_PREFIX . $post_type_object->name;
                            if ( $post_type_object && property_exists( $post_type_object, 'public' ) && $post_type_object->public && $wpddlayout->layout_post_loop_cell_manager->get_option( $option_type_name ) ) {
                                $layout_id = (int) $wpddlayout->layout_post_loop_cell_manager->get_option( $option_type_name );
                            }
                            
                        } else if( 'edit-tags' === $screen->base ) {
                            // $taxonomy | archive
                            
                            $option_type_name = WPDD_layout_post_loop_cell_manager::OPTION_TAXONOMY_PREFIX . $screen->taxonomy;
                            if ( $wpddlayout->layout_post_loop_cell_manager->get_option( $option_type_name ) ) {
                                $layout_id = (int) $wpddlayout->layout_post_loop_cell_manager->get_option( $option_type_name );
                            }
                            
                        } else if( 'post' === $screen->base ) {
                            // $post_type | page
                            
                            // Individual
                            $layout_slug = get_post_meta( (int) $_GET['post'], WPDDL_LAYOUTS_META_KEY, true );
                            if( ! empty( $layout_slug ) ) {
                                $layout_id = WPDD_Layouts::get_layout_id_by_slug( $layout_slug );
                                
                            }
                            
                            // Multiple
                            if( (int) $layout_id == 0 ) {
                                $layout_object = $wpddlayout->post_types_manager->get_layout_to_type_object( $type );
                                $layout_id = $layout_object && property_exists( $layout_object, 'layout_id' ) && $layout_object->layout_id > 0 
                                        ? $layout_object->layout_id 
                                        : 0;
                            }
                            
                        }
                        
                    }
                    */
                    
                } else if ( (int) $wpddlayout->get_rendered_layout_id() > 0 ) {
                    
                    $layout_id = $wpddlayout->get_rendered_layout_id();
                    
                }
                
                $is_new =  $layout_id > 0 ? false : true;
                $plugin_used = ! $is_new ? 'layouts' : $plugin_used;
                
            } 
            
            // Views - Edit Link
            if ( $is_new && $this->is_views_available() ) {
                
                global $WPV_settings;
                
                if( is_admin() ) {
                    
                    /*
                    // Same as Layouts
                    $screen = get_current_screen();
                    if( preg_match( '/^(edit|edit-tags|post)$/', $screen->base ) ) {
                        // Exists layout? $layout_id?
                            
                        if( 'edit' === $screen->base ) {
                            // $post_type | archive
                            
                            if ( isset( $WPV_settings['view_cpt_' . $type] ) && $WPV_settings['views_template_for_' . $type] > 0 ) {
                                $wpa_id = $WPV_settings['view_cpt_' . $type];
                            }
                            
                        } else if( 'edit-tags' === $screen->base ) {
                            // $taxonomy | archive
                            
                            if ( isset( $WPV_settings['view_taxonomy_loop_' . $type] ) && $WPV_settings['view_taxonomy_loop_' . $type] > 0 ) {
                                $wpa_id = $WPV_settings['view_taxonomy_loop_' . $type];
                            }
                            
                        } else if( 'post' === $screen->base ) {
                            // $post_type | page
                            
                            // Individual
                            if ( isset( $_GET['post'] ) && (int) $_GET['post'] > 0 ) {
                                $ct_id = (int) get_post_meta( (int) $_GET['post'], '_views_template', true );
                            }
                            
                            // Multiple
                            if ( (int) $ct_id == 0 
                                    && isset( $WPV_settings['views_template_for_' . $type] ) 
                                    && $WPV_settings['views_template_for_' . $type] > 0 
                                    ) {
                                $ct_id = $WPV_settings['views_template_for_' . $type];
                            }
                            
                        }
                        
                    }
                    
                    if ( ( int ) $wpa_id > 0 || ( int ) $ct_id > 0 ) {
                        $is_new = false;
                    }
                    */
                } else {
                    
                    if ( 'archive' === $class && 'page' != $type ) {
                        /* WordPress Archive */
                        
                        // WordPress Loop Archives
                        if( in_array( $type, self::$default_wordpress_archives ) 
                                && isset( $WPV_settings['view_'.$type.'-page'] )
                                && (int) $WPV_settings['view_'.$type.'-page'] > 0
                                ) {
                            $wpa_id = (int) $WPV_settings['view_'.$type.'-page'];
                        }
                        
                        // Taxonomy Archive
                        if( ! $wpa_id ) {
                            $taxonomy = get_taxonomy( $type );
                            $is_tax = $taxonomy !== false;
                            if( $is_tax 
                                    && isset( $WPV_settings['view_taxonomy_loop_' . $type] ) 
                                    && (int) $WPV_settings['view_taxonomy_loop_' . $type] > 0 
                                    ) {
                                $wpa_id = $WPV_settings['view_taxonomy_loop_' . $type];
                            }
                        }
                        
                        // Post Type Archive
                        if( ! $wpa_id ) {
                            $post_type_object = get_post_type_object( $type );
                            $is_cpt = $post_type_object != null;
                            if( $is_cpt && isset( $WPV_settings['view_cpt_' . $type] ) 
                                    && $WPV_settings['view_cpt_' . $type] > 0 
                                    ) {
                                $wpa_id = $WPV_settings['view_cpt_' . $type];
                            }
                        }
                        
                        if ( (int) $wpa_id > 0 ) {
                            $is_new = false;
                        }

                    } else if( 'page' === $class && ! is_404() ) {
                        /* Content Template */
                        
                        // Individual
                        $ct_id = (int) get_post_meta( get_the_ID(), '_views_template', true );
                        
                        // Multiple
                        if( (int) $ct_id == 0 ) {
                            
                            // This doesn't satisfies expectations.
                            // You cannot edit content templates you're not seeing, even if they're assigned to the current post type.
                            // FIXME: Define the right behavior.
                            // My proposal: if there is a CT assigned to post type, suggest "bind this and edit template" or similar approach
                            
                            /*
                            if( isset( $WPV_settings['views_template_for_'.$type] ) && $WPV_settings['views_template_for_'.$type] > 0 ) {
                                $ct_id = $WPV_settings['views_template_for_'.$type];
                            }
                            */
                        }

                        if ( (int) $ct_id > 0 ) {
                            $is_new = false;
                        }

                    }
                    
                }
                
                $plugin_used = ! $is_new ? 'views' : $plugin_used;
                
            }
            
            // $plugin_used - Create Link
            if ( $is_new ) {
                if( is_admin() ) {
                    
                    /*
                    $screen = get_current_screen();
                    if( $screen->id == 'post' ) {
                        $post_id = (int) $_GET['post'];
                    }
                    */
                    
                } else {
                    $post_id = get_the_ID();
                }
            }
            
            $title =        $this->get_title    ( $plugin_used, $is_new, $type, $class, max( array( $layout_id, $ct_id, $wpa_id, $post_id ) ) );
            $edit_link =    $this->get_edit_link( $plugin_used, $is_new, $type, $class, max( array( $layout_id, $ct_id, $wpa_id, $post_id ) ) );
            
            if ( $edit_link !== null ) {
                return array( $title, $edit_link );
            } else {
                // No valid data => No menu
                return null;
            }
        }

        /**
         * Returns an string with the context where the link is going to be display
         * It is going to be like "post_type|archive" or null if link should not be displayed
         * @return string {post_type or archive_type or taxonomy or 404}|{page or archive}
         */
        private function get_context() {

            // Rule of thumb: if there is a list of posts, it is an archive
            
            // null means we will not show the link
            $context = null;

            if ( is_admin() ) {
                
                /*
                // There are less places inside the admin to define Layouts/Templates
                
                global $post_type;
                                
                $screen = get_current_screen();
                
                if ( $screen->base == 'edit' && $post_type !== 'page' ) {
                    // list of posts page => create an archive ( WordPress Archive )
                    return "$post_type|archive";
                } else if ( $screen->base == 'post' && empty( $screen->action ) ) {
                    // post editor page => create a page ( Content Template )
                    return "$post_type|page";
                } else if ( $screen->base == 'edit-tags' ) {
                    // taxonomy page => always an archive ( WordPress Archive )
                    return "{$screen->taxonomy}|archive";
                }
                */
                
            } else {
                
                global $post, $wp_query;

                if ( is_home() ) {
                    // Blog posts index
                    $context = 'home-blog|archive';
                } else if ( is_search() ) {
                    $context = 'search|archive';
                } else if ( is_author() ) {
                    $context = 'author|archive';
                } else if ( is_year() ) {
                    $context = 'year|archive';
                } else if ( is_month() ) {
                    $context = 'month|archive';
                } else if ( is_day() ) {
                    $context = 'day|archive';
                } else if ( is_category() ) {
                    $context = 'category|archive';
                } else if ( is_tag() ) {
                    $context = 'post_tag|archive';
                } else if ( is_tax() ) {
					$term = $wp_query->get_queried_object();
					if (
						$term 
						&& isset( $term->taxonomy )
					) {
						$context = $term->taxonomy . '|archive';
					}                    
                } else if ( is_post_type_archive() ) {
					$post_type = $wp_query->get('post_type');
					if ( is_array( $post_type ) ) {
						$post_type = reset( $post_type );
					}
                    $context = $post_type . '|archive';
                } else if ( is_404() ) {
                    // Special WordPress Error 404 Page
                    $context = '404|page';
                } else if ( is_object( $post ) && get_class( $post ) === 'WP_Post' ) {
                    $context = get_post_type() . '|page';
                }
                
            }

            return $context;
        }
        
        /**
         * Get title for menu subitem
         * @param string $plugin are we using 'layouts' or 'views'?
         * @param boolean $is_new are we creating a new object?
         * @param string $type post_type, taxonomy or wp slug
         * @param string $class (single) page or archive
         * @param int $post_id must be layout or template id if !$is_new, else post
         * @return string title for menu subitem
         */
        private function get_title( $plugin, $is_new, $type, $class, $post_id = null) {

            if ( $is_new ) {
                /* Create */
                // "Create a new 'Layout for Restaurant archives'"
                // "Create a new 'Content Template for Restaurants'"
                // "Create a new 'WordPress Archive for Restaurant archives'"

                $create_a_new = __( 'Create a new', 'wpv-views' );
                $object = $this->get_name_auto( $plugin, $type, $class, $post_id );
                
                return trim( sprintf( '%s %s', $create_a_new, $object ) );
                
            } else {
                /* Edit */
                // "Edit 'Restaurants' Layout"
                // "Edit 'Layout for Restaurants' Layout" => "Edit 'Layout for Restaurants'"
                // "Edit 'Layout for Restaurant archives' Layout" => "Edit 'Layout for Restaurant' archives"

                $edit = __( 'Edit', 'wpv-views' );

                // Layout or Content Template or WordPress Archive
                $layouts = __( 'Layout', 'wpv-views' );
                $views = 'archive' === $class ? __( 'Archive', 'wpv-views' ) : __( 'Template', 'wpv-views' );
                $artifact = 'layouts' === $plugin ? $layouts : $views;

                // avoid "'Layout for Restaurant archives' Layout"
                // get   "'Layout for Restaurants'" instead
                $post_title = get_the_title( $post_id );
                $object = strpos( $post_title, $artifact ) === false ? sprintf( '%s %s', $post_title, $artifact ) : $post_title;

                return trim( sprintf( '%s %s', $edit, $object ) );
                
            }

        }

        /**
         * Get a valid and self-defining title for a Layout, Content Template or WordPress Archive
         *
         * @param string $plugin layouts or views
         * @param string $type post_type, taxonomy or wp slug
         * @param string $class page or archive
         * @param int|null $post_id
         *
         * @return string
         *
         * @since unknown
         */
        public function get_name_auto( $plugin, $type, $class, $post_id = null ) {
            // Examples:
            // Layout for Restaurants
            // Layout for Restaurant archives
            // Content Template for Restaurants
            // WordPress Archive for Restaurants 

            /* Layout or Content Template or WordPress Archive */
            $layouts = __( 'Layout', 'wpv-views' );
            $views = 'archive' === $class ? __( 'Archive', 'wpv-views' ) : __( 'Template', 'wpv-views' );
            $artifact = 'layouts' === $plugin ? $layouts : $views;
            
            /* for */
            $for = __( 'for', 'wpv-views' );

            /* selection */
            $selection = '';
            
            if ( 'page' === $class && '404' === $type && 'layouts' === $plugin ) {
                $selection = __( 'Error 404 page', 'wpv-views' );
            } else if ( 'page' === $type ) {
                $selection = get_the_title( $post_id );
            } else if ( 'page' === $class ) {
                $post_type = get_post_type_object( $type );
                $selection = ucfirst( $post_type->label );
            } else if ( 'archive' === $class && in_array( $type, self::$default_wordpress_archives ) ) {
                $selection = sprintf( '%s %s', ucfirst( $type ), __( 'Archives', 'wpv-views' ) );
            /*
            } else if ( 'archive' === $class && preg_match( '/^(category|post_tag)$/', $type ) ) {
                $taxonomy = get_taxonomy( $type );
                $selection = 'layouts' === $plugin ? sprintf( '%s %s', ucfirst( $taxonomy->labels->singular_name ), __( 'Archives', 'wpv-views' ) ) : ucfirst( $taxonomy->labels->name );
            */
            } else if ( 'archive' === $class ) {
                $post_type = get_post_type_object( $type );
                $is_cpt = $post_type != null;

                $taxonomy = get_taxonomy( $type );
                $is_tax = $taxonomy !== false;
                
                if ( $is_cpt ) {
                    $selection = 'layouts' === $plugin ? sprintf( '%s %s', ucfirst( $post_type->labels->singular_name ), __( 'Archives', 'wpv-views' ) ) : ucfirst( $post_type->labels->name );
                } else if ( $is_tax ) {
                    $selection = 'layouts' === $plugin ? sprintf( '%s %s', ucfirst( $taxonomy->labels->singular_name ), __( 'Archives', 'wpv-views' ) ) : ucfirst( $taxonomy->labels->name );
                } else {
                    $selection = __( 'Unsupported post type archives', 'wpv-views' );
                }
                
            } else {
                $selection = __( 'Unsupported page', 'wpv-views' );
            }

            return trim( sprintf( '%s %s %s', $artifact, $for, $selection ) );
        }
        
        public function get_edit_link( $plugin, $is_new, $type, $class, $post_id = null ) {
            $edit_link = null;
            
            if( 'layouts' === $plugin ) {
                
                if( $is_new && WPDD_Layouts_Users_Profiles::user_can_create() && WPDD_Layouts_Users_Profiles::user_can_assign() ) {
                    $edit_link = wp_nonce_url( admin_url( sprintf( 'admin.php?page=dd_layouts_create_auto&type=%s&class=%s&post=%s', $type, $class, $post_id ) ), 'create_auto' );
                } else if( $post_id > 0 && WPDD_Layouts_Users_Profiles::user_can_edit() ) {
                    // Layouts editor
                    $edit_link = admin_url( sprintf( 'admin.php?page=dd_layouts_edit&layout_id=%s&action=edit', $post_id ) );
                }

            } else if ( 'views' === $plugin && '404' != $type /* No support for Error 404 page */ ) {
                    
                if ( $is_new ) {
                    $edit_link = wp_nonce_url( admin_url( sprintf( 'admin.php?page=views_create_auto&type=%s&class=%s&post=%s', $type, $class, $post_id ) ), 'create_auto' );
                } else if( $post_id > 0 ) {
                    
                    if( 'archive' === $class ) {
                        // Views' WordPress Archive editor
                        $edit_link = admin_url( sprintf( 'admin.php?page=view-archives-editor&view_id=%s', $post_id ) );
                    } else if( 'page' === $class ) {
                        // Views' Content Temaplate editor
                        //$edit_link = admin_url( sprintf( 'post.php?action=edit&post=%s', $post_id ) );
                        $edit_link = esc_url_raw(
							add_query_arg(
								array( 'page' => WPV_CT_EDITOR_PAGE_NAME, 'ct_id' => esc_attr( $post_id ), 'action' => 'edit' ),
								admin_url( 'admin.php' )
							)
						);
                    }

                }
            }
            
            return $edit_link;
        }
        
    }

    // We have checked if @class Toolset_Admin_Bar_Menu already existed.
    // After that, we've defined the class. Now, we instantiate it once.
    // This works lìke a singleton.
    // But the class itself is also a singleton. Using design patterns 
    // clarifies and prevents against changes in future.
    global $toolset_admin_bar_menu;
    $toolset_admin_bar_menu = Toolset_Admin_Bar_Menu::get_instance();
}

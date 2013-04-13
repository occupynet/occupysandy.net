<?php

class SMM_Options {
	
	private $sections;
	private $checkboxes;
	private $settings;
	
	/**
	 * Construct
	 *
	 * @since 1.0
	 */
	public function __construct() {
		
		$this->checkboxes = array();
		$this->settings = array();
		$this->get_settings();
		
		$this->sections['feeds'] = __( 'Included Feeds', BNM_LOCALE );
		$this->sections['cache'] = __( 'Feed Cache', BNM_LOCALE );
		$this->sections['display'] = __( 'Display Settings', BNM_LOCALE );
		
		add_action( 'admin_menu', array( &$this, 'add_pages' ) );
		add_action( 'admin_init', array( &$this, 'register_settings' ) );
		
		if ( ! get_option( 'smm_options' ) )
			$this->initialize_settings();
		
	}
	
	/**
	 * Add options page
	 *
	 * @since 1.0
	 */
	public function add_pages() {
		
		$admin_page = add_options_page( 'Social Media Mashup', 'Social Media Mashup', 'manage_options', 'smm-options', array( &$this, 'display_page' ) );
		
		add_action( 'admin_print_styles-' . $admin_page, array( &$this, 'admin_styles' ) );
		
	}
	
	/**
	 * Add admin stylesheet
	 *
	 * @since 1.0
	 */
	public function admin_styles() {
		
		wp_register_style( 'smm-admin', SMM_URL . '/admin-style.css' );
		wp_enqueue_style( 'smm-admin' );
		
	}
	
	/**
	 * Create settings field
	 *
	 * @since 1.0
	 */
	public function create_setting( $args = array() ) {
		
		$defaults = array(
			'id'      => 'default_field',
			'title'   => __( 'Default Field', BNM_LOCALE ),
			'desc'    => __( 'This is a default description.', BNM_LOCALE ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'general',
			'choices' => array(),
			'class'   => ''
		);
			
		extract( wp_parse_args( $args, $defaults ) );
		
		$field_args = array(
			'type'      => $type,
			'id'        => $id,
			'desc'      => $desc,
			'std'       => $std,
			'choices'   => $choices,
			'label_for' => $id,
			'class'     => $class
		);
		
		if ( $type == 'checkbox' )
			$this->checkboxes[] = $id;
		
		add_settings_field( $id, $title, array( $this, 'display_setting' ), 'smm-options', $section, $field_args );
	}
	
	/**
	 * Display options page
	 *
	 * @since 1.0
	 */
	public function display_page() {
		
		echo '<div class="wrap">
	<div class="icon32" id="icon-options-general"></div>
	<h2>' . __( 'Social Media Mashup', BNM_LOCALE ) . '</h2>';
	
	if ( ! is_writable( SMM_DIR . '/smm-cache' ) )
		echo '<div class="error"><p>' . sprintf( __( '%sWarning:%s The %s folder in this plugin is not writable by the server. Feed caching is disabled.', BNM_LOCALE ), '<strong>', '</strong>', '<code>smm-cache</code>' ) . '</p></div>';
	
	echo '<div class="has-right-sidebar">
		<div id="poststuff">';
		$this->sidebar();
		echo '</div>';
		
		echo '<div style="float: left; width: 75%;">
		<form action="options.php" method="post">';
	
		settings_fields( 'smm_options' );
		do_settings_sections( 'smm-options' );
		
		echo '
		<p class="submit"><input name="Submit" type="submit" class="button-primary" value="' . __( 'Save Changes', BNM_LOCALE ) . '" /></p>
		
		</div>
		</form>
		</div>
</div>';
		
	}
	
	/**
	 * Description for section
	 *
	 * @since 1.0
	 */
	public function display_section() {
		// code
	}
	
	/**
	 * HTML output for text field
	 *
	 * @since 1.0
	 */
	public function display_setting( $args = array() ) {
		
		extract( $args );
		
		$options = get_option( 'smm_options' );
		
		if ( ! isset( $options[$id] ) && $type != 'checkbox' )
			$options[$id] = $std;
		elseif ( ! isset( $options[$id] ) )
			$options[$id] = 0;
		
		$field_class = '';
		if ( $class != '' )
			$field_class = ' ' . $class;
		
		switch ( $type ) {
			
			case 'checkbox':
				
				echo '<input class="checkbox' . $field_class . '" type="checkbox" id="' . $id . '" name="smm_options[' . $id . ']" value="1" ' . checked( $options[$id], 1, false ) . ' /> <label for="' . $id . '">' . $desc . '</label>';
				
				break;
			
			case 'select':
				echo '<select class="select' . $field_class . '" name="smm_options[' . $id . ']">';
				
				foreach ( $choices as $value => $label )
					echo '<option value="' . esc_attr( $value ) . '"' . selected( $options[$id], $value, false ) . '>' . $label . '</option>';
				
				echo '</select>';
				
				if ( $desc != '' )
					echo ' <span class="description">' . $desc . '</span>';
				
				break;
			
			case 'radio':
				$i = 0;
				foreach ( $choices as $value => $label ) {
					echo '<input class="radio' . $field_class . '" type="radio" name="smm_options[' . $id . ']" id="' . $id . $i . '" value="' . esc_attr( $value ) . '" ' . checked( $options[$id], $value, false ) . '> <label for="' . $id . $i . '">' . $label . '</label>';
					if ( $i < count( $options ) - 1 )
						echo '<br />';
					$i++;
				}
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'textarea':
				echo '<textarea class="' . $field_class . '" id="' . $id . '" name="smm_options[' . $id . ']" placeholder="' . $std . '" rows="5" cols="30">' . wp_htmledit_pre( $options[$id] ) . '</textarea>';
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'password':
				echo '<input class="regular-text' . $field_class . '" type="password" id="' . $id . '" name="smm_options[' . $id . ']" value="' . esc_attr( $options[$id] ) . '" />';
				
				if ( $desc != '' )
					echo ' <span class="description">' . $desc . '</span>';
				
				break;
			
			case 'html':
		 		echo $desc;
		 		break;
			
			case 'text':
			default:
		 		echo '<input class="regular-text' . $field_class . '" type="text" id="' . $id . '" name="smm_options[' . $id . ']" placeholder="' . $std . '" value="' . esc_attr( $options[$id] ) . '" />';
		 		
		 		if ( $desc != '' )
		 			echo ' <span class="description">' . $desc . '</span>';
		 		
		 		break;
		 	
		}
		
	}
	
	/**
	 * Settings and defaults
	 * 
	 * @since 1.0
	 */
	public function get_settings() {
		
		/* Feeds
		===========================================*/
		
		$this->settings['blog'] = array(
			'title'   => __( 'Include This Site', BNM_LOCALE ),
			'desc'    => __( "Include this site's RSS feed in the mashup", BNM_LOCALE ),
			'std'     => '1',
			'type'    => 'checkbox',
			'section' => 'feeds'
		);
		
		// http://twitter.com/statuses/user_timeline/___________.rss
		$this->settings['twitter'] = array(
			'title'   => __( 'Twitter user ID', BNM_LOCALE ),
			'desc'    => sprintf( __( 'This is not your username. %sGet the ID for your username here.%s', BNM_LOCALE ), '<a href="http://www.idfromuser.com/" target="_blank">', '</a>' ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'feeds'
		);
		
		// http://www.facebook.com/feeds/page.php?format=rss20&id=___________
		$this->settings['facebook'] = array(
			'title'   => __( 'Facebook page ID', BNM_LOCALE ),
			'desc'    => sprintf( __( '%sNote:%s User walls are not publicly available as RSS feeds.', BNM_LOCALE ), '<strong>', '</strong>' ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'feeds'
		);
		
		// http://plusfeed.appspot.com/___________
		$this->settings['google'] = array(
			'title'   => __( 'Google+ profile ID', BNM_LOCALE ),
			'desc'    => sprintf( __( 'This is in the URL of your Google+ profile. Example: %s112668640050600254908%s', BNM_LOCALE ), '<strong>', '</strong>' ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'feeds'
		);
		
		// http://api.flickr.com/services/feeds/photos_public.gne?id=___________&lang=en-us&format=rss_200
		$this->settings['flickr'] = array(
			'title'   => __( 'Flickr ID', BNM_LOCALE ),
			'desc'    => sprintf( __( 'This is not your username. %sGet the ID for your username here.%s', BNM_LOCALE ), '<a href="http://idgettr.com/" target="_blank">', '</a>' ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'feeds'
		);
		
		// http://gdata.youtube.com/feeds/base/users/___________/uploads?alt=rss&v=2&orderby=published&client=ytapi-youtube-profile
		$this->settings['youtube'] = array(
			'title'   => __( 'YouTube username', BNM_LOCALE ),
			'desc'    => __( '', BNM_LOCALE ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'feeds'
		);
		
		$this->settings['rss1'] = array(
			'title'   => __( 'Custom RSS feed', BNM_LOCALE ),
			'desc'    => __( '', BNM_LOCALE ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'feeds'
		);
		
		$this->settings['rss2'] = array(
			'title'   => __( 'Custom RSS feed', BNM_LOCALE ),
			'desc'    => __( '', BNM_LOCALE ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'feeds'
		);
		
		$this->settings['rss3'] = array(
			'title'   => __( 'Custom RSS feed', BNM_LOCALE ),
			'desc'    => __( '', BNM_LOCALE ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'feeds'
		);
		
		/* Feed Cache
		===========================================*/
		
		if ( is_writable( SMM_DIR . '/smm-cache' ) ) {
			$this->settings['cache_time'] = array(
				'title'   => __( 'Feed cache duration', BNM_LOCALE ),
				'desc'    => '<br />' . sprintf( __( 'Specify the amount of time (in minutes) to cache the feeds. Set to %s to disable caching. %sTurning off the cache could negatively impact performance.', BNM_LOCALE ), '<strong>0</strong>', '<br /><strong>' ) . '</strong>',
				'std'     => '60',
				'type'    => 'text',
				'section' => 'cache'
			);
		}
		else {
			$this->settings['cache_time'] = array(
				'title' => __( 'Feed cache duration', BNM_LOCALE ),
				'desc'    => '<em>' . sprintf( __( 'The %s folder in this plugin is not writable by the server. Feed caching is disabled.', BNM_LOCALE ), '<code>smm-cache</code>' ) . '</em>',
				'std'     => '',
				'type'    => 'html',
				'section' => 'cache'
			);
		}
		
		/* Display Settings
		===========================================*/
		
		$this->settings['show_source'] = array(
			'title'   => __( 'Show feed source', BNM_LOCALE ),
			'desc'    => __( '', BNM_LOCALE ),
			'std'     => 'rss',
			'type'    => 'radio',
			'section' => 'display',
			'choices' => array(
				'all' => 'All items',
				'rss' => 'RSS feeds only',
				'none' => 'None'
			)
		);
		
		$this->settings['show_icons'] = array(
			'title'   => __( 'Feed icons', BNM_LOCALE ),
			'desc'    => __( '', BNM_LOCALE ),
			'std'     => 'none',
			'type'    => 'radio',
			'section' => 'display',
			'class'   => 'icons',
			'choices' => array(
				'grey' => __( 'Grey', BNM_LOCALE ),
				'glossy' => __( 'Glossy', BNM_LOCALE ),
				'mini' => __( 'Mini', BNM_LOCALE ),
				'none' => __( 'None', BNM_LOCALE )
			)
		);
		
		$this->settings['use_styles'] = array(
			'title'   => __( 'Use included stylesheet', BNM_LOCALE ),
			'desc'    => __( 'Use the stylesheet included with the plugin', BNM_LOCALE ),
			'std'     => '1',
			'type'    => 'checkbox',
			'section' => 'display'
		);
		
	}
	
	/**
	 * Initialize settings to their default values
	 * 
	 * @since 1.0
	 */
	public function initialize_settings() {
		
		$default_settings = array();
		foreach ( $this->settings as $id => $setting )
			$default_settings[$id] = $setting['std'];
		
		update_option( 'smm_options', $default_settings );
		
	}
	
	/**
	* Register settings
	*
	* @since 1.0
	*/
	public function register_settings() {
		
		register_setting( 'smm_options', 'smm_options', array ( &$this, 'validate_settings' ) );
		
		foreach ( $this->sections as $slug => $title )
			add_settings_section( $slug, $title, array( &$this, 'display_section' ), 'smm-options' );
		
		$this->get_settings();
		
		foreach ( $this->settings as $id => $setting ) {
			$setting['id'] = $id;
			$this->create_setting( $setting );
		}
		
	}
	
	/**
	* Display BNM goodies in the sidebar
	*
	* @since 1.0
	*/
	public function sidebar() {
		
		echo '<div id="side-info-column" class="inner-sidebar" style="width: 25%;">';
		
		/* Instructions
		========================================================*/
		echo '<div class="postbox">
				<h3 class="hndle">' . __( 'How to Use', BNM_LOCALE ) . '</h3>
				<div class="inside">';
		
		echo '<h4>' . __( 'Shortcode', BNM_LOCALE ) . '</h4>
		<p>' . __( 'To insert a mashup into any post or page, use this shortcode in the content area:', BNM_LOCALE ) . '</p>
		<pre class="smm-pre">[social-media-mashup count="5"]</pre>
		<p>' . sprintf( __( 'Change %s to customize the number of items displayed.', BNM_LOCALE ), '<code>count</code>' ) . '</p>
		<h4>' . __( 'Template Tag', BNM_LOCALE ) . '</h4>
		<p>' . __( 'Insert this code into your theme to display a mashup:', BNM_LOCALE ) . '</p>' . 
		"<pre class='smm-pre'>&lt;?php\nif (function_exists('social_media_mashup'))\n\tsocial_media_mashup(5);\n?&gt;</pre>" . 
		'<p>' . sprintf( __( 'Change %s to customize the number of items displayed.', BNM_LOCALE ), '<code>5</code>' ) . '</p>';
		
		echo '</div>
			</div>';
		
		/* About BNM
		========================================================*/
		echo '<div class="postbox">
				<h3 class="hndle">' . sprintf( __( 'About %s', BNM_LOCALE ), 'Brave New Media' ) . '</h3>
				<div class="inside">';
		
		echo '<p>' . sprintf( __( 'Based in Minneapolis, %s is a content development and management company founded in 1998. Rooted in design and video and around since the inception of the internet, %s works with local, national and international companies in creating compelling stories that support their brands in all digital media.', BNM_LOCALE ), 'Brave New Media', 'Brave New Media' ) . '</p>
		<p><a href="http://bravenewmedia.net/" target="_blank" class="button">' . __( 'Visit Our Website', BNM_LOCALE ) . '</a>
		<a href="http://bnm.zendesk.com/" target="_blank" class="button">' . __( 'Get Support', BNM_LOCALE ) . '</a></p>';
		
		echo '</div>
			</div>';
		
		/* BNM Blog
		========================================================*/
		echo '<div class="postbox">
				<h3 class="hndle">' . sprintf( __( 'The %s Blog', BNM_LOCALE ), 'Brave New Media' ) . '</h3>
				<div class="inside">';
		
		if ( ! class_exists( 'SimplePie' ) )
			require_once( SMM_DIR . '/simplepie.inc' );
		
		$feed = new SimplePie();
		$feed->set_feed_url( 'http://blog.bravenewmedia.net/feed/' );
		date_default_timezone_set( get_option( 'America/Chicago' ) );
		$feed->encode_instead_of_strip( false );
		$feed->enable_cache( false );
		$feed->set_cache_location( SMM_DIR . '/cache' );
		$feed->init();
		$feed->handle_content_type();
		
		echo '<div class="rss-widget"><ul>';
		
		foreach ( $feed->get_items( 0, 3 ) as $item )
			echo '<li><a class="rsswidget" href="' . $item->get_permalink() . '" target="_blank">' . $item->get_title() . '</a> <span class="rss-date">' . $item->get_date( 'F j, Y' ) . '</span></li>';
		
		echo '</ul></div>';
		
		echo '</div>
			</div>';
		
		/* Credits
		========================================================*/
		echo '<div class="postbox credits">
				<h3 class="hndle">' . __( 'Credits', BNM_LOCALE ) . '</h3>
				<div class="inside">';
		
		echo '<ul>
			<li>' . __( 'Author:', BNM_LOCALE ) . ' <a href="http://profiles.wordpress.org/users/bravenewmedia/" target="_blank">bravenewmedia</a></li>
			<li>' . __( 'Contributor:', BNM_LOCALE ) . ' <a href="http://profiles.wordpress.org/users/aliso/" target="_blank">aliso</a></li>
		</ul>
		
		<h4>' . __( 'Special Thanks', BNM_LOCALE ) . '</h4>
		<ul>
			<li>' . sprintf( __( 'Icons by %s', BNM_LOCALE ), '<a href="http://picons.me/" target="_blank">Picons.me</a>' ) . '</li>
			<li>' . sprintf( __( 'Icons by %s', BNM_LOCALE ), '<a href="http://www.fasticon.com/" target="_blank">FastIcon.com</a>' ) . '</li>
			<li>' . sprintf( __( 'Icons by %s', BNM_LOCALE ), '<a href="http://www.komodomedia.com/blog/2008/12/social-media-mini-iconpack/">Komodo Media</a>' ) . '</li>
			<li>' . sprintf( __( 'Settings API class by %s', BNM_LOCALE ), '<a href="http://alisothegeek.com/2011/01/wordpress-settings-api-tutorial-1/" target="_blank">Aliso the Geek</a>' ) . '</li>
			<li>' . sprintf( __( 'RSS feed mashing by %s', BNM_LOCALE ), '<a href="http://simplepie.org/" target="_blank">SimplePie</a>' ) . '</li>
		</ul>';
		
		echo '</div>
			</div>';
			
		/* That's it!
		========================================================*/
		
		echo '</div>';
		
	}
	
	/**
	* Validate settings
	*
	* @since 1.0
	*/
	public function validate_settings( $input ) {
		
		$options = get_option( 'smm_options' );
		
		foreach ( $this->checkboxes as $id ) {
			if ( isset( $options[$id] ) && ! isset( $input[$id] ) )
				unset( $options[$id] );
		}
		
		return $input;
		
	}
	
}

$SMM_Options = new SMM_Options();

function smm_option( $option ) {
	$options = get_option( 'smm_options' );
	if ( isset( $options[$option] ) )
		return $options[$option];
	else
		return false;
}
?>
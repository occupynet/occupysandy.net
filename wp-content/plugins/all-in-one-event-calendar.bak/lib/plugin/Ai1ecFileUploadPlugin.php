<?php

/**
 *
 * @author Nicola
 *
 *
 */

class Ai1ecFileUploadPlugin extends Ai1ec_Connector_Plugin {

	const NAME_OF_FILE_INPUT = 'ai1ec_file_input';

	const NAME_OF_SUBMIT = 'ai1ec_file_submit';

	const NAME_OF_TEXTAREA = 'ai1ec_upload_textarea';

	protected $variables = array(
		"title" => "File Upload",
		"id"    => "file_upload"
	);

	/**
	 * @var int
	 */
	private $count;

	/**
	 *
	 * @see Ai1ec_Connector_Plugin::display_admin_notices()
	 *
	 */
	public function display_admin_notices() {

	}

	/**
	 *
	 * @see Ai1ec_Connector_Plugin::run_uninstall_procedures()
	 *
	 */
	public function run_uninstall_procedures() {

	}

	/**
	 *
	 * @see Ai1ec_Connector_Plugin::render_tab_content()
	 *
	 */
	public function render_tab_content() {
		global $ai1ec_view_helper;
		$this->render_opening_div_of_tab();
		$file_input = Ai1ec_Helper_Factory::create_input_instance();
		$file_input->set_type( 'file' );
		$file_input->set_id( self::NAME_OF_FILE_INPUT );
		$file_input->set_name( self::NAME_OF_FILE_INPUT );
		$submit = Ai1ec_Helper_Factory::create_input_instance();
		$submit->set_type( 'submit' );
		$submit->add_class( 'button-primary' );
		$submit->set_name( self::NAME_OF_SUBMIT );
		$submit->set_id( self::NAME_OF_SUBMIT );
		$submit->set_value( __( 'Submit Events', AI1EC_PLUGIN_NAME ) );
		$textarea = Ai1ec_Helper_Factory::create_generic_html_tag( 'textarea' );
		$textarea->set_attribute( 'name', self::NAME_OF_TEXTAREA );
		$textarea->set_attribute( 'rows', 6 );
		$textarea->set_id( self::NAME_OF_TEXTAREA );
		$facebook_tab = Ai1ec_Facebook_Factory::get_facebook_tab_instance();
		$category_select = $facebook_tab->create_select_category(
			'ai1ec_file_upload_feed_category' );
		$message = false;
		if ( isset( $this->count ) ) {
			$text = __( 'No events were found', AI1EC_PLUGIN_NAME );
			if ( $this->count > 0 ) {
				$text = sprintf(
					_n(
						'Imported %s event',
						'Imported %s events',
						$this->count,
						AI1EC_PLUGIN_NAME
					),
					$this->count
				);
			}
			$message = Ai1ec_Helper_Factory::create_bootstrap_message_instance(
				$text
			);
		}

		$args = array(
			"category_select" => $category_select,
			"submit"          => $submit,
			"file_input"      => $file_input,
			"textarea"        => $textarea
		);
		if( false !== $message ) {
			$args['message'] = $message;
		}
		$ai1ec_view_helper->display_admin(
			'plugins/file_upload/file_upload.php',
			$args
		);
		$this->render_closing_div_of_tab();
	}

	/**
	 *
	 * @see Ai1ec_Connector_Plugin::handle_feeds_page_post()
	 *
	 */
	public function handle_feeds_page_post() {
		if (isset( $_POST[self::NAME_OF_SUBMIT] )) {
			$count = 0;

			if( ! empty( $_FILES[self::NAME_OF_FILE_INPUT]['name'] ) ) {
				$count += $this->import_from_file();
			}
			if( ! empty( $_POST[self::NAME_OF_TEXTAREA] ) ) {
				// Bug http://core.trac.wordpress.org/ticket/18322
				// Double quotes are auto escaped from wordpress
				$count += $this->import_from_string( stripslashes( $_POST[self::NAME_OF_TEXTAREA] ) );
			}
			$this->count = $count;
		}
	}

	/**
	 * Tries to import data treating it either as csv or as ics.
	 *
	 * @param string $data
	 * @return int the number of imported objetcs
	 */
	private function import_from_string( $data ) {
		global $ai1ec_importer_helper;

		$count    = 0;
		$id       = __(
			'textarea_import',
			AI1EC_PLUGIN_NAME
		) . '-' . date( 'Y-m-d-H:i:s' );
		$feed     = $this->create_feed_instance( $id );
		$comments = isset( $_POST['ai1ec_file_upload_comments_enabled'] )
			? 'open'
			: 'closed';
		$show_map = isset( $_POST['ai1ec_file_upload_map_display_enabled'] )
			? 1
			: 0;

		$iCalcnv  = new iCalcnv();

		$iCalcnv->setConfig( array(
				'outputobj'       => true,
				'string_to_parse' => $data,
		) );

		$v     = $iCalcnv->csv2iCal();
		$count = $ai1ec_importer_helper->add_vcalendar_events_to_db(
			$v,
			$feed,
			$comments,
			$show_map
		);

		if ( 0 === $count ) {
			// create new instance
			$v = new vcalendar();
			$v->parse( $data );
			$count = $ai1ec_importer_helper->add_vcalendar_events_to_db(
				$v,
				$feed,
				$comments,
				$show_map
			);
		}

		return $count;
	}

	/**
	 * Tries to import data from an ics or csv file
	 *
	 * @return int
	 */
	private function import_from_file() {
		global $ai1ec_importer_helper;
		$v = false;
		$file_extension = strtolower( substr( $_FILES[self::NAME_OF_FILE_INPUT]['name'], -3 ) );
		if( $file_extension === 'csv' ) {
			$iCalcnv = new iCalcnv();
			$iCalcnv->setConfig(
				array(
					'inputfilename' => basename(
						$_FILES[self::NAME_OF_FILE_INPUT]['tmp_name']
					),
					'inputdirectory' => dirname(
						$_FILES[self::NAME_OF_FILE_INPUT]['tmp_name']
					),
					'outputobj' => TRUE,
					'extension_check' => FALSE
				)
			);

			$v = $iCalcnv->csv2iCal();
		} else if ( $file_extension === 'ics' ) {
			// create new instance
			$v = new vcalendar();
			$v->parse( file_get_contents( $_FILES[self::NAME_OF_FILE_INPUT]['tmp_name'] ) );
		}
		$id       = $_FILES[self::NAME_OF_FILE_INPUT]['name'] .
			'-' . date( 'Y-m-d-H:i:s' );
		$feed     = $this->create_feed_instance( $id );
		$comments = ( isset( $_POST['ai1ec_file_upload_comments_enabled'] ) )
			? 'open'
			: 'closed' ;
		$show_map = ( isset( $_POST['ai1ec_file_upload_map_display_enabled'] ) )
			? 1
			: 0;
		$count    = $ai1ec_importer_helper->add_vcalendar_events_to_db(
			$v,
			$feed,
			$comments,
			$show_map
		);
		return $count;
	}

	/**
	 * Create a feed instance
	 *
	 * @param string $id
	 * @return stdClass
	 */
	private function create_feed_instance( $id ) {
		$feed = new stdClass();
		$feed->feed_category = $_POST['ai1ec_file_upload_feed_category'];
		$feed->feed_tags = $_POST['ai1ec_file_upload_feed_tags'];
		$feed->feed_url = $id;
		$feed->feed_id = $id;
		$feed->feed_imported_file = true;
		return $feed;
	}
}

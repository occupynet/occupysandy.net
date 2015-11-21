<?php
/**
 * The concrete command that compiles CSS.
*
* @author     Time.ly Network Inc.
* @since      2.1
*
* @package    AI1EC
* @subpackage AI1EC.Command
*/
class Ai1ec_Command_Compile_Core_Css extends Ai1ec_Command {
	
	/*
	 * (non-PHPdoc) @see Ai1ec_Command::is_this_to_execute()
	 */
	public function is_this_to_execute() {
		if ( isset( $_GET['ai1ec_compile_css'] ) &&
			$_SERVER['SERVER_ADDR'] === $_SERVER['REMOTE_ADDR'] &&
			AI1EC_DEBUG
		) {
			return true;
		}
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see Ai1ec_Command::set_render_strategy()
	*/
	public function set_render_strategy( Ai1ec_Request_Parser $request ) {
		$this->_render_strategy = $this->_registry->get(
			'http.response.render.strategy.void'
		);
	}

	/* (non-PHPdoc)
	 * @see Ai1ec_Command::do_execute()
	*/
	public function do_execute() {
		$message = $this->_process_files();
		echo $message;
		return Ai1ec_Http_Response_Helper::stop( 0 );
	}

	/**
	 * Returns calendar theme structure.
	 *
	 * @param string $stylesheet Calendar stylesheet. Expects one of
	 *                           ['vortex','plana','umbra','gamma'].
	 * @return array Calendar themes.
	 *
	 * @throws Ai1ec_Invalid_Argument_Exception
	 */
	protected function _get_theme( $stylesheet ) {
		return $this->_registry->get(
			'filesystem.misc'
		)->build_theme_structure( $stylesheet );
	}

	/**
	 * Returns PHP code with hashmap array.
	 *
	 * @param $hashmap Array with compilation hashes.
	 *
	 * @return string PHP code.
	 */
	protected function _get_hashmap_array( $hashmap ) {
		return '<?php return ' . var_export( $hashmap, true ) . ';';
	}

	protected function _process_files() {
		$less   = $frontend = $this->_registry->get( 'less.lessphp' );
		$option = $this->_registry->get( 'model.option' );
		$theme  = $this->_get_theme( $_GET['theme'] );

		if ( isset( $_GET['switch'] ) ) {
			$option->delete( 'ai1ec_less_variables' );
			$option->set( 'ai1ec_current_theme', $theme );
			return 'Theme switched to "' . $theme['stylesheet'] . '".';
		}

		$css      = $less->parse_less_files( null, true );
		$hashmap  = $less->get_less_hashmap();
		$hashmap  = $this->_get_hashmap_array( $hashmap );
		$filename = $theme['theme_dir'] . DIRECTORY_SEPARATOR .
					'css' . DIRECTORY_SEPARATOR . 'ai1ec_parsed_css.css';
		$hashmap_file = $theme['theme_dir'] . DIRECTORY_SEPARATOR .
					'less.sha1.map.php';

		$css_written     = file_put_contents( $filename, $css );
		$hashmap_written = file_put_contents( $hashmap_file, $hashmap );
		if (
			false === $css_written ||
			false === $hashmap_written
		) {
			return 'There has been an error writing theme CSS';
		}

		return 'Theme CSS compiled succesfully and written in ' .
					$filename . ' and classmap stored in ' . $hashmap_file;
	}
}
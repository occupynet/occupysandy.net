<?php
/**
*
*/
interface Ai1ec_Scripts {
	/**
	 * Defines a simple module that can be later imported by require js. Useful for translations and so on.
	 *
	 * @param string $handle The script handle that was registered or used in script-loader
	 * @param string $object_name Name for the created requirejs module. This is passed directly so it should be qualified JS variable /[a-zA-Z0-9_]+/
	 * @param array $l10n Associative PHP array containing the translated strings. HTML entities will be converted and the array will be JSON encoded.
	 * @param boolean $frontend Whether the sript is for the frontend or the back end
	 * 
	 * @return bool Whether the localization was added successfully.
	 */
	public function localize_script_for_requirejs( $handle, $object_name, $l10n, $frontend = false );
	/**
	 * Enqueue a script from the admin resources directory (app/view/admin/js).
	 *
	 * @param string $name Unique identifer for the script
	 * @param string $file Filename of the script
	 * @param array $deps Dependencies of the script
	 * @param bool $in_footer Whether to add the script to the footer of the page
	 *
	 * @return void
	 */
	public function enqueue_admin_script( $name, $file, $deps = array(), $in_footer = FALSE );
}
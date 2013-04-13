<?php
require_once('fusiontable.class.php');

class OccupySandyBackend {
	var $ft;
	function __construct () {
		add_action( 'admin_menu', array(&$this, 'admin_menu') );
		add_action( 'admin_init', array(&$this, 'admin_init') );
		
		$apiKey = get_option('os_fusiontables_apikey', null);
		$tableId = get_option('os_fusiontables_tableid', null);

		if (!is_null($apiKey) and (strlen($apiKey) > 0)) :
			$this->ft = new FusionTable($apiKey, $tableId);
		endif;
	}

	function admin_menu () {
		add_submenu_page('options-general.php', 'Settings', 'FusionTables Backend', 0, 'os_options_fusiontables_backend', array(&$this, 'options_page'));
	}

	function admin_init () {
		global $wpdb;

		// Register FusionTables Backend settings
		register_setting( 'os-fusiontables-settings', 'os_fusiontables_apikey' );
		register_setting( 'os-fusiontables-settings', 'os_fusiontables_tableid' );
		register_setting( 'os-fusiontables-settings', 'os_fusiontables_wipe_querycache' );
	} /* if */

	function options_page () {
		$apiKey = get_option('os_fusiontables_apikey', null);
		$tableId = get_option('os_fusiontables_tableid', null);
		$wipeCache = get_option('os_fusiontables_wipe_querycache', null);

		// Request logged to wipe query cache?
		if (strlen($wipeCache) > 0) :
			update_option('os_fusiontables_wipe_querycache', false);

			if ($this->ft->has_cache()) :
				$this->ft->cache()->wipe(null, $wipeCache);
			endif;
		endif;

		?>
		<div class="wrap">
		<div id="icon-options-general" class="icon32"><br/></div>
		<h2>FusionTables Backend Settings</h2>
		<form method="post" action="options.php">
		<?php settings_fields('os-fusiontables-settings'); ?>
		<table class="form-table">
		<tbody>
		<tr style="vertical-align: top"><th>API Key:</th> <td><input type="text" name="os_fusiontables_apikey" value="<?php echo esc_attr($apiKey); ?>" placeholder="API key" size="127" />
		<p style="font-size: smaller; color: #333; max-width: 50em; font-style: italic;">From <a href="https://developers.google.com/fusiontables/docs/v1/using#auth">Fusion Tables documentation:</a> <q>Requests to the Fusion Tables API for public data must be accompanied by an identifier, which can be an API key or an auth token. To acquire an API key, visit the APIs Console. In the Services pane, activate the Fusion Tables API; if the Terms of Service appear, read and accept them. Next, go to the API Access pane. The API key is near the bottom of that pane, in the section titled <q>Simple API Access.</q></q></p></tr>
		<tr><th>Default Table:</th> <td><input type="text" name="os_fusiontables_tableid" value="<?php echo esc_attr($tableId); ?>" placeholder="table_name" size="127" /></tr>

<?php
		if ($this->ft->has_cache()) :
?>
		<tr><th>Wipe Cache:</th> <td><input type="checkbox" name="os_fusiontables_wipe_querycache" value="<?php echo esc_attr($apiKey); ?>" /> Wipe cache</td></tr>
<?php
		endif;
?>
		</tbody>
		</table>
		<p class="submit"><input class="button-primary" type="submit" name="Submit" value="<?php print __('Save Changes'); ?>" /></p>
		</form>

		<?php if ($this->ft->has_cache()) : ?>
		<h3>Cache Status</h3>
		<?php $this->ft->cache()->dump(); ?>
		<?php endif; ?>

		<h3>Test Output</h3>
		<?php
		if ($this->has_data()) :
			$data = $this->get_data(array('raw' => true));
			if (!is_wp_error($data)) :
			?>
			<table>
			<thead>
			<tr>
			<?php foreach ($data->columns as $col) : ?>
			<th scope="col"><?php print esc_html($col); ?></th>
			<?php endforeach; ?>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ($data->rows as $row) :
			?>
			<tr style="vertical-align: top"><?php
				foreach ($row as $cell) :
					?><td><?php ob_start(); var_dump($cell); $html = ob_get_clean(); print esc_html($html); ?></td><?php
				endforeach;
			endforeach; ?></tr>
			</tbody>
			</table>
			<?php
			else :
			?>
		<div style="background-color: yellow; margin: 1.0em 5.0em; color: black; padding: 15px; border-radius: 15px;">
		<p><strong>API Query Failure:</strong> The query function returned a <code><?php print $data->get_error_code(); ?></code></q> error.</p>
		<p>Error message: <?php print $data->get_error_message(); ?></p>
		<pre><?php ob_start(); var_dump($data->get_error_data()); $html = ob_get_clean(); print esc_html($html); ?></pre>
		</div>
			<?php
			endif;
		else : 
		?>
		<div style="background-color: red; margin: 1.0em 5.0em; color: white; padding: 15px; border-radius: 15px;">
		<p><strong>API Connection Failure:</strong> I don&#8217;t have all the information I need to connect to Google FusionTables API. Please enter an API Key and a Default Table above.</p>
		</div>
		<?php endif; ?>
		</div> <!-- class="wrap" -->
		<?php
	}

	function has_data () {
		return ($this->ft InstanceOf FusionTable);
	}

	function get_data ($params = array()) {
		$ret = NULL;
		if ($this->has_data()) :
			$ret = $this->ft->data($params);
		endif;
		return $ret;
	}
}

$GLOBALS['OccupySandyBackend'] = new OccupySandyBackend;

// For better WordPress templating style.
function get_occupy_sandy_data ($params = array()) {
	global $OccupySandyBackend;
	return $OccupySandyBackend->get_data($params);
}


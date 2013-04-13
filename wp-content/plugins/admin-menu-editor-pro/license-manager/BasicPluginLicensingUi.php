<?php

class Wslm_BasicPluginLicensingUI {
	private $licenseManager;
	private $pluginFile;
	private $slug;
	private $requiredCapability = 'update_plugins';

	private $triedLicenseKey = null;
	/**
	 * @var Wslm_ProductLicense
	 */
	private $triedLicense = null;
	private $currentTab = 'current-license';

	public function __construct(Wslm_LicenseManagerClient $licenseManager, $pluginFile) {
		$this->licenseManager = $licenseManager;
		$this->pluginFile = $pluginFile;
		$this->slug = $this->licenseManager->getProductSlug();

		$basename = plugin_basename($this->pluginFile);
		add_filter(
			'plugin_action_links_' . $basename,
			array($this, 'addLicenseActionLink')
		);
		add_filter(
			'network_admin_plugin_action_links_' . $basename,
			array($this, 'addLicenseActionLink')
		);

		add_action('wp_ajax_' . $this->getAjaxActionName(), array($this, 'printUi'));

		add_action('after_plugin_row_' . $basename, array($this, 'printPluginRowNotice'), 10, 0);
	}

	public function addLicenseActionLink($links) {
		if ( $this->currentUserCanManageLicense() ) {
			$links['licenses'] = $this->makeLicenseLink();
		}
		return $links;
	}

	private function currentUserCanManageLicense() {
		return apply_filters(
			'wslm_current_user_can_manage_license-' . $this->slug,
			current_user_can($this->requiredCapability)
		);
	}

	private function makeLicenseLink($linkText = 'License') {
		return sprintf(
			'<a href="%s" class="thickbox" title="%s">%s</a>',
			esc_attr(add_query_arg(
				array( 'TB_iframe' => true, ),
				$this->getLicensingPageUrl()
			)),
			esc_attr($this->getPageTitle()),
			apply_filters('wslm_action_link_text-' . $this->slug, $linkText)
		);
	}

	private function getLicensingPageUrl() {
		$url = add_query_arg('action', $this->getAjaxActionName(), admin_url('admin-ajax.php'));
		$url = wp_nonce_url($url, 'show_license'); //Assumes the default license action = "show_license".
		return $url;
	}

	private function getAjaxActionName() {
		return 'show_license_ui-' . $this->slug;
	}

	private function getPageTitle() {
		return apply_filters('wslm_license_ui_title-' . $this->slug, 'Manage Licenses');
	}

	public function printUi() {
		if ( !$this->currentUserCanManageLicense() ) {
			wp_die("You don't have sufficient permissions to manage licenses for this product.");
		}

		$action = isset($_REQUEST['license_action']) ? strval($_REQUEST['license_action']) : '';
		if ( empty($action) ) {
			$action = 'show_license';
		}
		check_admin_referer($action);

		$this->triedLicenseKey = isset($_POST['license_key']) ? strval($_POST['license_key']) : $this->licenseManager->getLicenseKey();

		$this->printHeader();
		$this->dispatchAction($action);
		$this->printLogo();
		$this->printTabList();
		?>
		<div class="wrap" id="wslm-section-holder">
			<div id="section-current-license" class="wslm-section">
				<?php $this->tabCurrentLicense(); ?>
			</div>
			<div id="section-manage-sites" class="wslm-section hidden">
				<?php $this->tabManageSites(); ?>
			</div>
		</div> <!-- #wslm-section-holder -->
		<?php

		exit();
	}

	private function dispatchAction($action) {
		do_action('wslm_ui_action-' . $action . '-' . $this->slug);
		$method = 'action' . str_replace(' ', '', ucwords(str_replace('_', ' ', $action)));
		if ( method_exists($this, $method) ) {
			$this->$method();
		} else {
			$this->printNotice(
				sprintf('Unknown action "%s"', htmlentities($action)),
				'error'
			);
		}
	}

	private function actionShowLicense() {
		//Don't need to do anything special in this case, I think.
		//Maybe request the site list if we have a license key.
		$this->licenseManager->checkForLicenseUpdates();
		$this->triedLicenseKey = $this->licenseManager->getLicenseKey();
		$this->triedLicense = $this->licenseManager->getLicense();
	}

	private function actionLicenseThisSite() {
		if ( empty($this->triedLicenseKey) ) {
			$this->printNotice('The license key must not be empty.', 'error');
			return;
		}
		$result = $this->licenseManager->licenseThisSite($this->triedLicenseKey);
		if ( is_wp_error($result) ) {
			$this->printError($result);
			//If the license key exists but the site can't be licensed for some reason,
			//the API response may include the license details.
			$this->triedLicense = $result->get_error_data('license');
		} else {
			$this->printNotice('Success! This site is now licensed.');
			$this->triedLicense = $result;
		}
	}

	private function actionUnlicenseThisSite() {
		$result = $this->licenseManager->unlicenseThisSite();
		if ( is_wp_error($result) ) {
			$this->printError($result);
		} else {
			$this->printNotice('Success! The existing license has been removed from this site.');
		}
	}

	private function actionUnlicenseOtherSite() {
		$this->currentTab = 'manage-sites';

		$siteUrl = isset($_POST['site_url']) ? strval($_POST['site_url']) : '';
		if ( empty($siteUrl) || empty($this->triedLicenseKey) ) {
			$this->printNotice('Please specify both the site URL and license key.', 'error');
			return;
		}

		$result = $this->licenseManager->unlicenseSite($siteUrl, $this->triedLicenseKey);
		if ( is_wp_error($result) ) {
			$this->printError($result);
			$this->triedLicense = $result->get_error_data('license');
		} else {
			$this->printNotice(
				'Success! This license key is no longer associated with ' . htmlentities($siteUrl)
			);
			$this->triedLicense = $result;
		}
	}

	private function actionShowLicensedSites() {
		$this->currentTab = 'manage-sites';
		if ( empty($this->triedLicenseKey) ) {
			$this->printNotice('License key must not be empty.', 'error');
			return;
		}

		$result = $this->licenseManager->requestLicenseDetails($this->triedLicenseKey);
		if ( is_wp_error($result) ) {
			$this->printError($result);
		} else {
			$this->triedLicense = $result;
		}
	}

	private function tabCurrentLicense() {
		//Display license information
		$currentLicense = $this->licenseManager->getLicense();
		echo '<h3>Current License</h3>';
		if ( $currentLicense->isValid() ) {
			$this->printLicenseDetails(
				'Valid License',
				'This site is currently licensed and qualifies for automatic upgrades &amp; support for this product.
				If you no longer wish to use this product on this site you can remove the license.'
			)
			?>
			<form method="post" action="<?php echo esc_attr($this->getLicensingPageUrl()); ?>">
				<input type="hidden" name="license_action" value="unlicense_this_site" />
				<?php wp_nonce_field('unlicense_this_site'); ?>
				<?php submit_button('Remove License', 'secondary', 'submit', false); ?>
			</form>
			<?php
			$this->printLicenseKeyForm(
				'Change License Key',
				'Want to use a different license key? Enter it below.',
				'Change Key',
				'secondary'
			);
		} else {
			if ( $currentLicense->getStatus() === 'no_license_yet' ) {
				$this->printLicenseDetails(
					'No License Yet',
					'This site is currently not licensed. Please enter your license key below.'
				);
				$this->printLicenseKeyForm();
			} else {
				$this->printLicenseDetails(
					'Invalid license (' . htmlentities($currentLicense->getStatus()) . ')',
					'The current license is not valid. Please enter a valid license key below.'
				);
				$this->printLicenseKeyForm();
			}
		}
	}

	private function printLicenseDetails($status, $message = '') {
		$currentKey = $this->licenseManager->getLicenseKey();
		$currentToken = $this->licenseManager->getSiteToken();
		?>
		<p>
			<span class="license-status">
				<label>Status:</label> <?php echo $status; ?>
			</span>
		</p>

		<?php
		if ( !empty($currentKey) ) {
			?><p><label>License key:</label> <?php echo htmlentities($currentKey); ?></p><?php
		}
		if ( !empty($currentToken) ) {
			?><p><label>Site token:</label> <?php echo htmlentities($currentToken); ?></p><?php
		}

		if ( !empty($message) ) {
			echo '<p>', $message, '</p>';
		}
	}

	private function tabManageSites() {
		if ( isset($this->triedLicense, $this->triedLicense->sites) ) {

			?>
			<h3>Sites Associated With License Key "<?php echo htmlentities($this->triedLicenseKey); ?>"</h3>
			<?php
			if ( !empty($this->triedLicense->sites) ):
			?>
			<table class="widefat">
				<?php foreach($this->triedLicense->sites as $site): ?>
				<tr>
					<td>
						<?php echo htmlentities($site->site_url); ?><br>
						Token: <?php echo htmlentities($site->token); ?>
					</td>
					<td style="vertical-align: middle; width: 11em;">
						<form method="post" action="<?php echo esc_attr($this->getLicensingPageUrl()); ?>">
							<input type="hidden" name="site_url" value="<?php echo esc_attr($site->site_url); ?>" />
							<input type="hidden" name="license_key" value="<?php echo esc_attr($this->triedLicenseKey); ?>" />
							<input type="hidden" name="license_action" value="unlicense_other_site" />
							<?php wp_nonce_field('unlicense_other_site'); ?>
							<?php submit_button('Remove License', 'secondary', 'submit', false); ?>
						</form>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
			<?php
			else:
			?>
				There are currently no sites using this license key.
			<?php
			endif;

		} else {
			$this->printLicenseKeyForm(
				'',
				'To view sites currently associated with a license, enter your license key below.',
				'Show Licensed Sites',
				'primary',
				'show_licensed_sites'
			);
		}
	}

	private function printLicenseKeyForm(
		$formCaption = 'Enter a License Key',
		$formDescription = '',
		$buttonTitle = 'Activate Key',
		$buttonType = 'primary',
		$licenseAction = 'license_this_site'
	) {
		?>
		<h3><?php echo $formCaption; ?></h3>
		<?php
		if ( !empty($formDescription) ) {
			echo '<p>', $formDescription, '</p>';
		}
		?>
		<form method="post" action="<?php echo esc_attr($this->getLicensingPageUrl()); ?>">
			<input type="hidden" name="license_action" value="<?php echo esc_attr($licenseAction); ?>" />
			<?php wp_nonce_field($licenseAction); ?>
			<!--suppress HtmlFormInputWithoutLabel -->
			<input type="text" name="license_key" size="36" />
			<?php submit_button($buttonTitle, $buttonType, 'submit', false); ?>
		</form>
		<?php
	}

	private function printError(WP_Error $error) {
		foreach ($error->get_error_codes() as $code) {
			foreach ($error->get_error_messages($code) as $message) {
				if ( !empty($message) ) {
					$this->printNotice(
						$message . "\n<br>Error code: <code>" . htmlentities($code) . '</code>',
						'error'
					);
				}
			}
		}
	}

	private function printNotice($message, $class = 'updated') {
		printf('<div class="%s"><p>%s</p></div>', esc_attr($class), $message);
	}

	private function printHeader() {
		?>
		<!DOCTYPE html>
		<!--[if IE 8]>
		<html xmlns="http://www.w3.org/1999/xhtml" class="ie8" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
		<![endif]-->
		<!--[if !(IE 8) ]><!-->
		<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
		<!--<![endif]-->
		<head>
			<meta http-equiv="Content-Type" content="<?php
				bloginfo('html_type');
				echo '; charset', '=' , get_option('blog_charset');
			?>" />
			<title><?php echo esc_html($this->getPageTitle()); ?></title>
			<?php
				wp_admin_css( 'global' );
				wp_admin_css( 'admin' );
				wp_admin_css();
				wp_admin_css( 'colors' );

				wp_enqueue_style(
					'wslm-basic-ui',
					plugins_url('/ui.css', __FILE__),
					array(),
					'1.0'
				);
				wp_enqueue_script('jquery');

				do_action('admin_print_styles');
				do_action('admin_print_scripts');
				do_action('admin_head');
			?>
		</head>
		<body class="wp-admin iframe no-js" id="licensing-information">
		<script type="text/javascript">
		//<![CDATA[
		(function(){
			var c = document.body.className;
			c = c.replace(/no-js/, 'js');
			document.body.className = c;
		})();
		//]]>
		</script>
		<?php
	}

	private function printLogo() {
		//Logo (optional)
		echo '<div id="wslm-product-logo">';
		do_action('wslm_license_ui_logo-' . $this->slug);
		echo '</div>';
	}

	private function printTabList() {
		//Tabs
		$tabs = array(
			'current-license' => 'Current License',
			'manage-sites' => 'Manage Sites',
		);
		?>
		<div id="plugin-information-header">
			<ul id="sidemenu">
				<?php
				foreach($tabs as $name => $caption) {
					printf('<li><a name="%s" href="#">%s</a></li>', esc_attr($name), $caption);
				}
				?>
			</ul>
		</div>

		<script type="text/javascript">
			jQuery(function($) {
				var tabSelector = jQuery('#sidemenu');

				function selectTab(tab) {
					//Flip the tab
					tabSelector.find('a.current').removeClass('current');
					tabSelector.find('a[name="' + tab + '"]').addClass('current');
					//Flip the content.
					$('#wslm-section-holder').find('div.wslm-section').hide(); //Hide 'em all
					$('#section-' + tab).show();
				}

				selectTab('<?php echo esc_js($this->currentTab); ?>');
				tabSelector.find('a').click( function() {
					var tab = $(this).attr('name');
					selectTab(tab);
					return false;
				});
			});
		</script>
		<?php
	}

	public function printPluginRowNotice() {
		//If the plugin doesn't have a valid license, output a notice under the plugin row in "Plugins".
		$license = $this->licenseManager->getLicense();
		if ( !$this->currentUserCanManageLicense() || $license->isValid() ) {
			return;
		}

		$messages = array(
			'no_license_yet' => "License is not set yet. Please enter your license key to enable automatic updates.",
			'expired' => 'The license associated with this site has expired.',
			'not_found' => 'The current license key or site token is invalid.',
			'bad_site' => 'The current license is associated with a different site. Please re-enter your license key.',
		);
		$status = $license->getStatus();
		$notice = isset($messages[$status]) ? $messages[$status] : 'The current license is invalid.';

		$licenseLink = $this->makeLicenseLink(apply_filters(
			'wslm_plugin_row_link_text-' . $this->slug,
			'Enter License Key'
		));
		echo '</tr>'; //Required - WP runs this action inside the plugin row.
		?>
		<tr class="plugin-update-tr">
			<td class="plugin-update" colspan="3">
				<div class="update-message">
					<?php echo $licenseLink; ?> | <?php echo $notice; ?>
				</div>
			</td>
		<?php
		//(Intentionally leaving our <tr> unclosed. It will match up with the </tr> that WP outputs.)
	}
}
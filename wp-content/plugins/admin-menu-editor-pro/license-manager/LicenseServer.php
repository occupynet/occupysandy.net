<?php

class Wslm_LicenseServer {
	protected $tablePrefix = 'wp_';
	/** @var wpdb */
	protected $wpdb;
	private $get = array();
	private $post = array();

	public function __construct($tablePrefix = null) {
		$this->wpdb = $GLOBALS['wpdb'];
		$this->tablePrefix = ($tablePrefix !== null) ? $tablePrefix : $this->wpdb->prefix;
		$this->get = $_GET;
		$this->post = $_POST;

		add_action('init', array($this, 'addRewriteRules'));
		add_filter('query_vars', array($this, 'addQueryVars'));

		add_action('template_redirect', array($this, 'dispatchRequest'), 5);
	}

	/**
	 * Generate a new license.
	 *
	 * The $licenseData array determines the properties of the new license.
	 * The supported fields are:
	 * 		'product_slug' - The product associated with the license. Required.
	 * 		'product_id'   - A numeric product ID.
	 * 		'customer_id'  - A numeric customer ID.
	 * 		'max_sites'    - On how many sites can the license be activated. Null = no limit.
	 * 		'expires_on'   - When the license expires (e.g. "2015-01-31 12:00"). Null = never expire.
	 *
	 * The 'product_id' and 'customer_id' fields are primarily intended for your own use,
	 * i.e. to help integrate this licensing library with your existing store. The license
	 * server doesn't actually use them for anything.
	 *
	 * @param array $licenseData
	 * @return Wslm_ProductLicense
	 * @throws InvalidArgumentException
	 */
	public function generateLicense($licenseData) {
		$licenseData = array_merge(array(
			'product_id'   => 0,
			'customer_id'  => 0,
			'max_sites'    => null,
			'expires_on'   => null,
			'issued_on'    => date('Y-m-d H:i:s'),
			'license_key'  => $this->generateRandomString(32),
		), $licenseData);

		if ( empty($licenseData['product_slug']) ) {
			throw new InvalidArgumentException("Product slug must not be empty");
		}

		return $this->saveLicense($licenseData);
	}

	/**
	 * Delete a license and associated tokens.
	 *
	 * @param Wslm_ProductLicense $license
	 */
	public function deleteLicense($license) {
		$this->wpdb->delete(
			$this->tablePrefix . 'licenses',
			array('license_id' => $license['license_id'])
		);
		//Delete associated tokens.
		$this->wpdb->delete(
			$this->tablePrefix . 'tokens',
			array('license_id' => $license['license_id'])
		);
	}

	/**
	 * Update or insert a license.
	 *
	 * @param Wslm_ProductLicense|array $license
	 * @return Wslm_ProductLicense
	 */
	public function saveLicense($license) {
		if ( is_array($license) ) {
			$license = new Wslm_ProductLicense($license);
		}
		$data = $license->getData();

		//The license object might have some virtual or computed fields that don't exist in the DB.
		//If we try to update/insert those, we'll get an SQL error. So lets filter the data array
		//to ensure only valid fields are included in the query.
		$licenseDbFields = array(
			'license_id', 'license_key', 'product_id', 'product_slug', 'customer_id',
			'status', 'issued_on', 'expires_on', 'max_sites'
		);
		$licenseDbFields = apply_filters('wslm_license_db_fields', $licenseDbFields);
		$data = array_intersect_key($data, array_flip($licenseDbFields));

		if ( is_numeric($data['expires_on']) ) {
			$data['expires_on'] = date('Y-m-d H:i:s', $data['expires_on']);
		}

		if ( $license->get('license_id') === null ) {
			//wpdb converts null values to "0" which is not what we want.
			//When inserting, we can simply strip them and let the DB fill in the blanks with NULLs.
			$data = array_filter($data, __CLASS__ . '::isNotNull');
			$this->wpdb->insert($this->tablePrefix . 'licenses', $data);
			$license['license_id'] = $this->wpdb->insert_id;
		} else {
			//When updating, we need to check for nulls and treat them differently,
			//so we can't use $wpdb->update here.
			$query = "UPDATE {$this->tablePrefix}licenses SET ";
			$expressions = array();
			foreach($data as $field => $value) {
				$expressions[] = $field . ' = ' . (($value === null) ? 'NULL' : $this->wpdb->prepare('%s', $value));
			}
			$query .= implode(', ', $expressions);
			$query .= ' WHERE license_id = ' . absint($license['license_id']);
			$this->wpdb->query($query);
		}
		return $license;
	}

	public static function isNotNull($value) {
		return $value !== null;
	}

	public function dispatchRequest() {
		if ( get_query_var('licensing_api') != '1' ) {
			return;
		}

		$action = get_query_var('license_action');
		if ( empty($action) ) {
			$action = 'get_license';
		}
		$productSlug = get_query_var('license_product');
		$licenseKey = get_query_var('license_key');
		$token = get_query_var('license_token');

		switch ($action) {
			case 'get_license':
				$this->actionGetLicense($productSlug, $licenseKey, $token);
				break;
			case 'license_site':
				$this->actionLicenseSite($productSlug, $licenseKey);
				break;
			case 'unlicense_site':
				$this->actionUnlicenseSite($productSlug, $licenseKey, $token);
				break;
			default:
				$this->outputError('invalid_action', 'Unsupported API action "' . $action . '"', 400);
				break;
		}

		exit;
	}

	protected function actionGetLicense($productSlug, $licenseKey = null, $token = null) {
		$this->requireRequestMethod('GET');
		$license = $this->validateLicenseRequest($productSlug, $licenseKey, $token, $this->get);
		$this->outputResponse(array(
			'license' => $this->prepareLicenseForOutput($license, !empty($token)),
		));
	}

	/**
	 * Check if the specified license exists, and quit with an API error if not.
	 * Returns the requested license on success.
	 *
	 * @param string $productSlug
	 * @param string|null $licenseKey
	 * @param string|null $token
	 * @param array $params
	 * @return Wslm_ProductLicense
	 */
	protected function validateLicenseRequest($productSlug, $licenseKey, $token = null, $params = array()) {
		$usingToken = !empty($token);
		if ( $usingToken ) {
			$siteUrl = $this->sanitizeSiteUrl(isset($params['site_url']) ? strval($params['site_url']) : '');
		} else {
			$siteUrl = null;
		}

		$license = $this->verifyLicenseExists($productSlug, $licenseKey, $token, $siteUrl);
		if ( is_wp_error($license) ) {
			$this->outputError(
				$license->get_error_code(),
				$license->get_error_message(),
				$license->get_error_data()
			);
			exit;
		} else {
			return $license;
		}

	}

	/**
	 * Check if the specified license key or token exists and return the corresponding license.
	 *
	 * If you specify both a token and a site URL this method will also verify that the token
	 * matches the site URL.
	 *
	 * @param string $productSlug
	 * @param string $licenseKey
	 * @param string|null $token Takes precedence over the license key.
	 * @param string|null $siteUrl
	 * @return Wslm_ProductLicense|WP_Error A license object, or WP_Error if the license doesn't exist or doesn't match the URL.
	 */
	public function verifyLicenseExists($productSlug, $licenseKey, $token = null, $siteUrl = null) {
		if ( empty($licenseKey) && empty($token) ) {
			return new WP_Error('not_found', 'You must specify a license key or a site token.', 400);
		}

		$license = $this->loadLicense($licenseKey, $token);
		if ( empty($license) ) {
			if ( !empty($token) ) {
				return new WP_Error('not_found', 'Invalid site token.', 404);
			} else {
				return new WP_Error('not_found', 'Invalid license key. Please verify the key or contact the developer for assistance.', 404);
			}
		}

		if ( $license['product_slug'] != $productSlug ) {
			return new WP_Error('not_found', 'This license key is for a different product.', 404);
		}

		//Make sure the site token was actually issued to that site and not another one.
		if ( $siteUrl !== null && $token !== null ) {
			$siteUrl = $this->sanitizeSiteUrl($siteUrl);
			if ( !$this->isValidUrl($siteUrl) ) {
				return new WP_Error('invalid_site_url', 'You must specify a valid site URL when using a site token.', 400);
			}
			if ( $siteUrl != $this->sanitizeSiteUrl($license['site_url']) ) {
				return new WP_Error('wrong_site', 'This token is associated with a different site.', 400);
			}
		}

		return $license;
	}

	/**
	 * Retrieve a license by license key or token.
	 *
	 * If you specify a token, this method will ignore $licenseKey and just
	 * look for the token. The returned license object will also include
	 * the URL of the site associated with that token in a 'site_url' field.
	 *
	 * @param string|null $licenseKey
	 * @param string|null $token
	 * @throws InvalidArgumentException
	 * @return Wslm_ProductLicense|null A license object, or null if the license doesn't exist.
	 */
	public function loadLicense($licenseKey, $token = null) {
		if ( !empty($token) ) {
			$query = $this->wpdb->prepare(
				"SELECT licenses.*, tokens.site_url
				 FROM
				 	`{$this->tablePrefix}licenses` AS licenses
				 	JOIN `{$this->tablePrefix}tokens` AS tokens
				 	ON licenses.license_id = tokens.license_id
				 WHERE tokens.token = %s",
				$token
			);
		} else if ( !empty($licenseKey) ) {
			$query = $this->wpdb->prepare(
				"SELECT licenses.* FROM `{$this->tablePrefix}licenses` AS licenses
				 WHERE license_key = %s",
				$licenseKey
			);
		} else {
			throw new InvalidArgumentException('You must specify a license key or a site token.');
		}

		$license = $this->wpdb->get_row($query, ARRAY_A);
		if ( !empty($license) ) {
			//Also include the list of sites associated with this license.
			$license['sites'] = $this->loadLicenseSites($license['license_id']);
			$license = new Wslm_ProductLicense($license);
		} else {
			$license = null;
		}
		return $license;
	}

	protected function loadLicenseSites($licenseId) {
		$licensedSites = $this->wpdb->get_results($this->wpdb->prepare(
			"SELECT site_url, token, issued_on
			 FROM {$this->tablePrefix}tokens
			 WHERE license_id = %s",
			$licenseId
		), ARRAY_A);
		return $licensedSites;
	}

	/**
	 * @param Wslm_ProductLicense $license
	 * @param bool $usingToken
	 * @return array
	 */
	public function prepareLicenseForOutput($license, $usingToken = false) {
		$data = $license->getData();
		$data['status'] = $license->getStatus();

		//Ensure timestamps are formatted consistently.
		foreach(array('issued_on', 'expires_on') as $datetimeField) {
			if ( isset($data[$datetimeField]) ) {
				$data[$datetimeField] = gmdate('c', strtotime($data[$datetimeField]));
			}
		}

		$visibleFields = array_fill_keys(array(
			'license_key', 'product_slug', 'status', 'issued_on', 'max_sites',
			'expires_on', 'sites', 'site_url', 'error',
		), true);
		if ( $usingToken ) {
			$visibleFields = array_merge($visibleFields, array(
				'license_key' => false,
				'sites' => false,
			));
		}
		$visibleFields = apply_filters('wslm_api_visible_license_fields', $visibleFields);
		$data = array_intersect_key($data, array_filter($visibleFields));
		return $data;
	}

	protected function actionLicenseSite($productSlug, $licenseKey) {
		$this->requireRequestMethod('POST');
		$license = $this->validateLicenseRequest($productSlug, $licenseKey);

		//Is the license still valid?
		if ( !$license->isValid() ) {
			if ( $license->getStatus() == 'expired' ) {
				$this->outputError('expired_license', 'This license key has expired.', 400);
			} else {
				$this->outputError('invalid_license', 'This license key is invalid or has expired.', 400);
			}
			return;
		}

		$siteUrl = isset($this->post['site_url']) ? strval($this->post['site_url']) : '';
		if ( !$this->isValidUrl($siteUrl) ) {
			$this->outputError('site_url_expected', "Missing or invalid site URL.", 400);
			return;
		}
		$siteUrl = $this->sanitizeSiteUrl($siteUrl);

		//Maybe the site is already licensed?
		$token = $this->wpdb->get_var($this->wpdb->prepare(
			"SELECT token FROM {$this->tablePrefix}tokens WHERE site_url = %s AND license_id = %d",
			$siteUrl, $license['license_id']
		));
		if ( !empty($token) ) {
			$this->outputResponse(array(
				'site_token' => $token,
				'license' => $this->prepareLicenseForOutput($license),
			));
			return;
		}

		//Check the number of sites already licensed and see if we can add another one.
		if ( $license['max_sites'] !== null ) {
			$licensedSiteCount = $this->wpdb->get_var($this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->tablePrefix}tokens WHERE license_id = %d",
				$license['license_id']
			));
			if ( intval($licensedSiteCount) >= intval($license['max_sites']) ) {
				$this->outputError(
					'max_sites_reached',
					"You have already reached the maximum number of sites allowed by your license.",
					400
				);
				return;
			}
		}

		//If the site was already associated with another key, remove that association. Only one key per site.
		$otherToken = $this->wpdb->get_var($this->wpdb->prepare(
			"SELECT tokens.token
			 FROM {$this->tablePrefix}tokens AS tokens
				JOIN {$this->tablePrefix}licenses AS licenses
				ON tokens.license_id = licenses.license_id
			 WHERE
				tokens.site_url = %s
				AND licenses.product_slug = %s
				AND licenses.license_id <> %d",
			$siteUrl, $productSlug, $license['license_id']
		));
		if ( !empty($otherToken) ) {
			$this->wpdb->delete($this->tablePrefix . 'tokens', array('token' => $otherToken));
		}

		//Everything checks out, lets create a new token.
		$token = $this->generateRandomString(32);
		$this->wpdb->insert(
			$this->tablePrefix . 'tokens',
			array(
				'license_id' => $license['license_id'],
				'token' => $token,
				'site_url' => $siteUrl,
				'issued_on' => date('Y-m-d H:i:s'),
			)
		);

		//Reload the license to ensure it includes the changes we just made.
		$license = $this->loadLicense($licenseKey);
		$this->outputResponse(array(
			'site_token' => $token,
			'license' => $this->prepareLicenseForOutput($license),
		));
	}

	protected function generateRandomString($length, $alphabet = null) {
		if ( $alphabet === null ) {
			$alphabet = 'ABDEFGHIJKLMNOPQRSTVWXYZ0123456789';
			//U and C intentionally left out to lessen the chances of generating an obscene string.
		}
		$maxIndex = strlen($alphabet) - 1;
		$str = '';
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($alphabet, rand(0, $maxIndex), 1);
		}
		return $str;
	}

	protected function actionUnlicenseSite($productSlug, $licenseKey = null, $token = null) {
		$this->requireRequestMethod('POST');
		$license = $this->validateLicenseRequest($productSlug, $licenseKey, $token, $this->post);

		$siteUrl = $this->sanitizeSiteUrl(isset($this->post['site_url']) ? strval($this->post['site_url']) : '');
		$usingToken = !empty($token);

		$response = array( 'license' => $this->prepareLicenseForOutput($license, $usingToken), );

		if ( !$usingToken ) {
			$token = $this->wpdb->get_var($this->wpdb->prepare(
				"SELECT token FROM `{$this->tablePrefix}tokens` WHERE site_url = %s AND license_id = %d",
				$siteUrl, $license['license_id']
			));
		}

		if ( empty($token) ) {
			//The user tried to un-license a site that wasn't licensed in the first place. Still,
			//the desired end state - site not licensed - has ben achieved, so treat it as a success.
			$response['notice'] = "The specified site wasn't licensed in the first place.";
		} else {
			$this->wpdb->delete(
				$this->tablePrefix . 'tokens',
				array(
					'token' => $token,
					'license_id' => $license['license_id'],
				)
			);

			//Reload the license to ensure the site list is correct.
			$license = $this->loadLicense($license['license_key']);
			$response['license'] = $this->prepareLicenseForOutput($license, $usingToken);

			$response = array_merge($response, array(
				'site_token_removed' => $token,
				'site_url' => $siteUrl
			));
		}
		$this->outputResponse($response);
	}

	protected function requireRequestMethod($httpVerbs) {
		$httpVerbs =  array_map('strtoupper', (array)$httpVerbs);
		if ( !in_array(strtoupper($_SERVER['REQUEST_METHOD']), $httpVerbs) ) {
			header('Allow: '. implode(', ', $httpVerbs));
			$this->outputError(
				'invalid_method',
				'This resource does not support the ' . $_SERVER['REQUEST_METHOD'] . ' method.',
				405
			);
			exit;
		}
	}

	protected function outputError($code, $message, $httpStatus = null) {
		$httpStatus = (isset($httpStatus) && is_numeric($httpStatus)) ? $httpStatus : 500;
		$this->outputResponse(array(
			'error' => array('code' => $code, 'message' => $message),
		), $httpStatus);
	}

	private function outputResponse($body, $httpStatus = 200) {
		status_header($httpStatus);
		header('Content-Type: application/json');
		echo wsh_pretty_json(json_encode($body));
	}

	private function isValidUrl($url) {
		$parts = @parse_url($url);
		return !empty($url) && !empty($parts) && isset($parts['host']);
	}

	private function sanitizeSiteUrl($url) {
		return untrailingslashit($url);
	}

	/**
	 * Retrieve a customer's licenses. You can optionally specify a slug to retrieve only
	 * licenses for that product.
	 *
	 * @param int $customerId
	 * @param string|null $productSlug
	 * @return Wslm_ProductLicense[]
	 */
	public function getCustomerLicenses($customerId, $productSlug = null) {
		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->tablePrefix}licenses WHERE customer_id = %s",
			$customerId
		);
		if ( $productSlug !== null ) {
			$query .= $this->wpdb->prepare(' AND product_slug=%s', $productSlug);
		}
		$query .= ' ORDER BY issued_on DESC';

		$rows = $this->wpdb->get_results($query, ARRAY_A);
		$licenses = array();
		if ( is_array($rows) ) {
			foreach($rows as $row) {
				$licenses[] = new Wslm_ProductLicense($row);
			}
		}
		return $licenses;
	}

	public function addRewriteRules() {
		$apiRewriteRules = array(
			'licensing_api/products/([^/\?]+)/licenses/bytoken/([^/\?]+)(?:/([a-z0-9_]+))?' =>
			'index.php?licensing_api=1&license_product=$matches[1]&license_token=$matches[2]&license_action=$matches[3]',

			'licensing_api/products/([^/\?]+)/licenses/(?!bytoken)([^/\?]+)(?:/([a-z0-9_]+))?' =>
			'index.php?licensing_api=1&license_product=$matches[1]&license_key=$matches[2]&license_action=$matches[3]',
		);

		//Add the rules only if they don't exist yet.
		$wp_rewrite = $GLOBALS['wp_rewrite']; /** @var WP_Rewrite $wp_rewrite */
		$missingRules = array_diff_assoc($apiRewriteRules, $wp_rewrite->wp_rewrite_rules());
		if ( !empty($missingRules) ) {
			foreach ($apiRewriteRules as $pattern => $redirect) {
				add_rewrite_rule($pattern, $redirect, 'top');
			}
			flush_rewrite_rules();
		}
	}

	public function addQueryVars($queryVariables) {
		$licensingVariables = array(
			'licensing_api', 'license_product', 'license_key',
			'license_token', 'license_action'
		);
		$queryVariables = array_merge($queryVariables, $licensingVariables);
		return $queryVariables;
	}
}

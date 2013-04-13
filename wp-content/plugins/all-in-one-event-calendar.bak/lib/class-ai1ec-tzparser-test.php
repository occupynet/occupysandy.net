<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'class-ai1ec-tzparser.php';

class Ai1ec_Tzparser_Test
{

	public function __construct() {
		$parser		= Ai1ec_Tzparser::instance();
		$methodList = get_class_methods($this);
		foreach ( $methodList as $method ) {
			if ( 0 === strncmp( $method, 'test', 4 ) ) {
				$nameList = $this->{$method}();
				foreach ( $nameList as $search => $match ) {
					if ( is_int( $search ) ) {
						$search = $match;
					}
					$result = $parser->get_name( $search );
					if ( $result !== $match ) {
						echo 'Invalid [', $search, '] mapping to [', $result,
						'] instead of [', $match, '].', PHP_EOL;
					} else {
						echo 'Recognized [', $search, '] as [', $result,
						'].', PHP_EOL;
					}
				}
			}
		}
	}

	public function testGeneric() {
		return array(
			'Europe/Vilnius',
			'America/New York' => 'America/New_York',
		);
	}

	public function testLegacy() {
		return array(
			'America/Buenos_Aires',
			'America/Fort_Wayne',
			'America/Halifax',
			'America/Indianapolis',
			'America/Louisville',
			'Etc/GMT-1',
			'Australia/South',
			'Australia/Victoria',
		);
	}

	public function testMeta() {
		return array(
			'Eastern Time' => 'America/New_York',
			'Pacific Time (US & Canada)' => 'America/Los_Angeles',
		);
	}

	public function testUtcPrefix() {
		return array(
			'(UTC-08:00) Pacific Time (US & Canada)' => 'America/Los_Angeles',
		);
	}

}

$tester = new Ai1ec_Tzparser_Test();

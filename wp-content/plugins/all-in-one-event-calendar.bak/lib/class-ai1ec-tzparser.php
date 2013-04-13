<?php

/**
 * Timezone names parser
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2012-07-24
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Lib
 */
class Ai1ec_Tzparser
{

	/**
	 * @var array Map of timezone names and their Olson TZ counterparts
	 */
	protected $_zones = array(
		'Z'                                => 'UTC',
		'AUS Central Standard Time'        => 'Australia/Darwin',
		'AUS Eastern Standard Time'        => 'Australia/Sydney',
		'Acre'                             => 'America/Rio_Branco',
		'Afghanistan'                      => 'Asia/Kabul',
		'Afghanistan Standard Time'        => 'Asia/Kabul',
		'Africa_Central'                   => 'Africa/Maputo',
		'Africa_Eastern'                   => 'Africa/Nairobi',
		'Africa_FarWestern'                => 'Africa/El_Aaiun',
		'Africa_Southern'                  => 'Africa/Johannesburg',
		'Africa_Western'                   => 'Africa/Lagos',
		'Aktyubinsk'                       => 'Asia/Aqtobe',
		'Alaska'                           => 'America/Juneau',
		'Alaska_Hawaii'                    => 'America/Anchorage',
		'Alaskan Standard Time'            => 'America/Anchorage',
		'Almaty'                           => 'Asia/Almaty',
		'Amazon'                           => 'America/Manaus',
		'America_Central'                  => 'America/Chicago',
		'America_Eastern'                  => 'America/New_York',
		'America_Mountain'                 => 'America/Denver',
		'America_Pacific'                  => 'America/Los_Angeles',
		'Anadyr'                           => 'Asia/Anadyr',
		'Aqtau'                            => 'Asia/Aqtau',
		'Aqtobe'                           => 'Asia/Aqtobe',
		'Arab Standard Time'               => 'Asia/Riyadh',
		'Arabian'                          => 'Asia/Riyadh',
		'Arabian Standard Time'            => 'Asia/Dubai',
		'Arabic Standard Time'             => 'Asia/Baghdad',
		'Argentina'                        => 'America/Buenos_Aires',
		'Argentina Standard Time'          => 'America/Buenos_Aires',
		'Argentina_Western'                => 'America/Mendoza',
		'Armenia'                          => 'Asia/Yerevan',
		'Armenian Standard Time'           => 'Asia/Yerevan',
		'Ashkhabad'                        => 'Asia/Ashgabat',
		'Atlantic'                         => 'America/Halifax',
		'Atlantic Standard Time'           => 'America/Halifax',
		'Australia_Central'                => 'Australia/Adelaide',
		'Australia_CentralWestern'         => 'Australia/Eucla',
		'Australia_Eastern'                => 'Australia/Sydney',
		'Australia_Western'                => 'Australia/Perth',
		'Azerbaijan'                       => 'Asia/Baku',
		'Azerbaijan Standard Time'         => 'Asia/Baku',
		'Azores'                           => 'Atlantic/Azores',
		'Azores Standard Time'             => 'Atlantic/Azores',
		'Baku'                             => 'Asia/Baku',
		'Bangladesh'                       => 'Asia/Dhaka',
		'Bering'                           => 'America/Adak',
		'Bhutan'                           => 'Asia/Thimphu',
		'Bolivia'                          => 'America/La_Paz',
		'Borneo'                           => 'Asia/Kuching',
		'Brasilia'                         => 'America/Sao_Paulo',
		'British'                          => 'Europe/London',
		'Brunei'                           => 'Asia/Brunei',
		'Canada Central Standard Time'     => 'America/Regina',
		'Cape Verde Standard Time'         => 'Atlantic/Cape_Verde',
		'Cape_Verde'                       => 'Atlantic/Cape_Verde',
		'Caucasus Standard Time'           => 'Asia/Yerevan',
		'Cen. Australia Standard Time'     => 'Australia/Adelaide',
		'Central America Standard Time'    => 'America/Guatemala',
		'Central Asia Standard Time'       => 'Asia/Dhaka',
		'Central Brazilian Standard Time'  => 'America/Manaus',
		'Central Europe Standard Time'     => 'Europe/Budapest',
		'Central European Standard Time'   => 'Europe/Warsaw',
		'Central Pacific Standard Time'    => 'Pacific/Guadalcanal',
		'Central Standard Time'            => 'America/Chicago',
		'Central Standard Time (Mexico)'   => 'America/Mexico_City',
		'Chamorro'                         => 'Pacific/Saipan',
		'Changbai'                         => 'Asia/Harbin',
		'Chatham'                          => 'Pacific/Chatham',
		'Chile'                            => 'America/Santiago',
		'China'                            => 'Asia/Shanghai',
		'China Standard Time'              => 'Asia/Shanghai',
		'Choibalsan'                       => 'Asia/Choibalsan',
		'Christmas'                        => 'Indian/Christmas',
		'Cocos'                            => 'Indian/Cocos',
		'Colombia'                         => 'America/Bogota',
		'Cook'                             => 'Pacific/Rarotonga',
		'Cuba'                             => 'America/Havana',
		'Dacca'                            => 'Asia/Dhaka',
		'Dateline Standard Time'           => 'Etc/GMT+12',
		'Davis'                            => 'Antarctica/Davis',
		'Dominican'                        => 'America/Santo_Domingo',
		'DumontDUrville'                   => 'Antarctica/DumontDUrville',
		'Dushanbe'                         => 'Asia/Dushanbe',
		'Dutch_Guiana'                     => 'America/Paramaribo',
		'E. Africa Standard Time'          => 'Africa/Nairobi',
		'E. Australia Standard Time'       => 'Australia/Brisbane',
		'E. Europe Standard Time'          => 'Europe/Minsk',
		'E. South America Standard Time'   => 'America/Sao_Paulo',
		'East_Timor'                       => 'Asia/Dili',
		'Easter'                           => 'Pacific/Easter',
		'Eastern Standard Time'            => 'America/New_York',
		'Ecuador'                          => 'America/Guayaquil',
		'Egypt Standard Time'              => 'Africa/Cairo',
		'Ekaterinburg Standard Time'       => 'Asia/Yekaterinburg',
		'Europe_Central'                   => 'Europe/Paris',
		'Europe_Eastern'                   => 'Europe/Bucharest',
		'Europe_Western'                   => 'Atlantic/Canary',
		'FLE Standard Time'                => 'Europe/Kiev',
		'Falkland'                         => 'Atlantic/Stanley',
		'Fiji'                             => 'Pacific/Fiji',
		'Fiji Standard Time'               => 'Pacific/Fiji',
		'French_Guiana'                    => 'America/Cayenne',
		'French_Southern'                  => 'Indian/Kerguelen',
		'Frunze'                           => 'Asia/Bishkek',
		'GMT'                              => 'Atlantic/Reykjavik',
		'GMT Standard Time'                => 'Europe/London',
		'GTB Standard Time'                => 'Europe/Istanbul',
		'Galapagos'                        => 'Pacific/Galapagos',
		'Gambier'                          => 'Pacific/Gambier',
		'Georgia'                          => 'Asia/Tbilisi',
		'Georgian Standard Time'           => 'Etc/GMT-3',
		'Gilbert_Islands'                  => 'Pacific/Tarawa',
		'Goose_Bay'                        => 'America/Goose_Bay',
		'Greenland Standard Time'          => 'America/Godthab',
		'Greenland_Central'                => 'America/Scoresbysund',
		'Greenland_Eastern'                => 'America/Scoresbysund',
		'Greenland_Western'                => 'America/Godthab',
		'Greenwich Standard Time'          => 'Atlantic/Reykjavik',
		'Guam'                             => 'Pacific/Guam',
		'Gulf'                             => 'Asia/Dubai',
		'Guyana'                           => 'America/Guyana',
		'Hawaii_Aleutian'                  => 'Pacific/Honolulu',
		'Hawaiian Standard Time'           => 'Pacific/Honolulu',
		'Hong_Kong'                        => 'Asia/Hong_Kong',
		'Hovd'                             => 'Asia/Hovd',
		'India'                            => 'Asia/Calcutta',
		'India Standard Time'              => 'Asia/Calcutta',
		'Indian_Ocean'                     => 'Indian/Chagos',
		'Indochina'                        => 'Asia/Saigon',
		'Indonesia_Central'                => 'Asia/Makassar',
		'Indonesia_Eastern'                => 'Asia/Jayapura',
		'Indonesia_Western'                => 'Asia/Jakarta',
		'Iran'                             => 'Asia/Tehran',
		'Iran Standard Time'               => 'Asia/Tehran',
		'Irish'                            => 'Europe/Dublin',
		'Irkutsk'                          => 'Asia/Irkutsk',
		'Israel'                           => 'Asia/Jerusalem',
		'Israel Standard Time'             => 'Asia/Jerusalem',
		'Japan'                            => 'Asia/Tokyo',
		'Jordan Standard Time'             => 'Asia/Amman',
		'Kamchatka'                        => 'Asia/Kamchatka',
		'Karachi'                          => 'Asia/Karachi',
		'Kashgar'                          => 'Asia/Kashgar',
		'Kazakhstan_Eastern'               => 'Asia/Almaty',
		'Kazakhstan_Western'               => 'Asia/Aqtobe',
		'Kizilorda'                        => 'Asia/Qyzylorda',
		'Korea'                            => 'Asia/Seoul',
		'Korea Standard Time'              => 'Asia/Seoul',
		'Kosrae'                           => 'Pacific/Kosrae',
		'Krasnoyarsk'                      => 'Asia/Krasnoyarsk',
		'Kuybyshev'                        => 'Europe/Samara',
		'Kwajalein'                        => 'Pacific/Kwajalein',
		'Kyrgystan'                        => 'Asia/Bishkek',
		'Lanka'                            => 'Asia/Colombo',
		'Liberia'                          => 'Africa/Monrovia',
		'Line_Islands'                     => 'Pacific/Kiritimati',
		'Long_Shu'                         => 'Asia/Chongqing',
		'Lord_Howe'                        => 'Australia/Lord_Howe',
		'Macau'                            => 'Asia/Macau',
		'Magadan'                          => 'Asia/Magadan',
		'Malaya'                           => 'Asia/Kuala_Lumpur',
		'Malaysia'                         => 'Asia/Kuching',
		'Maldives'                         => 'Indian/Maldives',
		'Marquesas'                        => 'Pacific/Marquesas',
		'Marshall_Islands'                 => 'Pacific/Majuro',
		'Mauritius'                        => 'Indian/Mauritius',
		'Mauritius Standard Time'          => 'Indian/Mauritius',
		'Mawson'                           => 'Antarctica/Mawson',
		'Mexico Standard Time'             => 'America/Mexico_City',
		'Mexico Standard Time 2'           => 'America/Chihuahua',
		'Mid-Atlantic Standard Time'       => 'Atlantic/South_Georgia',
		'Middle East Standard Time'        => 'Asia/Beirut',
		'Mongolia'                         => 'Asia/Ulaanbaatar',
		'Montevideo Standard Time'         => 'America/Montevideo',
		'Morocco Standard Time'            => 'Africa/Casablanca',
		'Moscow'                           => 'Europe/Moscow',
		'Mountain Standard Time'           => 'America/Denver',
		'Mountain Standard Time (Mexico)'  => 'America/Chihuahua',
		'Myanmar'                          => 'Asia/Rangoon',
		'Myanmar Standard Time'            => 'Asia/Rangoon',
		'N. Central Asia Standard Time'    => 'Asia/Novosibirsk',
		'Namibia Standard Time'            => 'Africa/Windhoek',
		'Nauru'                            => 'Pacific/Nauru',
		'Nepal'                            => 'Asia/Katmandu',
		'Nepal Standard Time'              => 'Asia/Katmandu',
		'New Zealand Standard Time'        => 'Pacific/Auckland',
		'New_Caledonia'                    => 'Pacific/Noumea',
		'New_Zealand'                      => 'Pacific/Auckland',
		'Newfoundland'                     => 'America/St_Johns',
		'Newfoundland Standard Time'       => 'America/St_Johns',
		'Niue'                             => 'Pacific/Niue',
		'Norfolk'                          => 'Pacific/Norfolk',
		'Noronha'                          => 'America/Noronha',
		'North Asia East Standard Time'    => 'Asia/Irkutsk',
		'North Asia Standard Time'         => 'Asia/Krasnoyarsk',
		'North_Mariana'                    => 'Pacific/Saipan',
		'Novosibirsk'                      => 'Asia/Novosibirsk',
		'Omsk'                             => 'Asia/Omsk',
		'Oral'                             => 'Asia/Oral',
		'Pacific SA Standard Time'         => 'America/Santiago',
		'Pacific Standard Time'            => 'America/Los_Angeles',
		'Pacific Standard Time (Mexico)'   => 'America/Tijuana',
		'Pakistan'                         => 'Asia/Karachi',
		'Pakistan Standard Time'           => 'Asia/Karachi',
		'Palau'                            => 'Pacific/Palau',
		'Papua_New_Guinea'                 => 'Pacific/Port_Moresby',
		'Paraguay'                         => 'America/Asuncion',
		'Peru'                             => 'America/Lima',
		'Philippines'                      => 'Asia/Manila',
		'Phoenix_Islands'                  => 'Pacific/Enderbury',
		'Pierre_Miquelon'                  => 'America/Miquelon',
		'Pitcairn'                         => 'Pacific/Pitcairn',
		'Ponape'                           => 'Pacific/Ponape',
		'Qyzylorda'                        => 'Asia/Qyzylorda',
		'Reunion'                          => 'Indian/Reunion',
		'Romance Standard Time'            => 'Europe/Paris',
		'Rothera'                          => 'Antarctica/Rothera',
		'Russian Standard Time'            => 'Europe/Moscow',
		'SA Eastern Standard Time'         => 'Etc/GMT+3',
		'SA Pacific Standard Time'         => 'America/Bogota',
		'SA Western Standard Time'         => 'America/La_Paz',
		'SE Asia Standard Time'            => 'Asia/Bangkok',
		'Sakhalin'                         => 'Asia/Sakhalin',
		'Samara'                           => 'Europe/Samara',
		'Samarkand'                        => 'Asia/Samarkand',
		'Samoa'                            => 'Pacific/Apia',
		'Samoa Standard Time'              => 'Pacific/Apia',
		'Seychelles'                       => 'Indian/Mahe',
		'Shevchenko'                       => 'Asia/Aqtau',
		'Singapore'                        => 'Asia/Singapore',
		'Singapore Standard Time'          => 'Asia/Singapore',
		'Solomon'                          => 'Pacific/Guadalcanal',
		'South Africa Standard Time'       => 'Africa/Johannesburg',
		'South_Georgia'                    => 'Atlantic/South_Georgia',
		'Sri Lanka Standard Time'          => 'Asia/Colombo',
		'Suriname'                         => 'America/Paramaribo',
		'Sverdlovsk'                       => 'Asia/Yekaterinburg',
		'Syowa'                            => 'Antarctica/Syowa',
		'Tahiti'                           => 'Pacific/Tahiti',
		'Taipei'                           => 'Asia/Taipei',
		'Taipei Standard Time'             => 'Asia/Taipei',
		'Tajikistan'                       => 'Asia/Dushanbe',
		'Tashkent'                         => 'Asia/Tashkent',
		'Tasmania Standard Time'           => 'Australia/Hobart',
		'Tbilisi'                          => 'Asia/Tbilisi',
		'Tokelau'                          => 'Pacific/Fakaofo',
		'Tokyo Standard Time'              => 'Asia/Tokyo',
		'Tonga'                            => 'Pacific/Tongatapu',
		'Tonga Standard Time'              => 'Pacific/Tongatapu',
		'Truk'                             => 'Pacific/Truk',
		'Turkey'                           => 'Europe/Istanbul',
		'Turkmenistan'                     => 'Asia/Ashgabat',
		'Tuvalu'                           => 'Pacific/Funafuti',
		'US/Eastern'                       => 'America/New_York',
		'US Eastern Standard Time'         => 'Etc/GMT+5',
		'US Mountain Standard Time'        => 'America/Phoenix',
		'Uralsk'                           => 'Asia/Oral',
		'Uruguay'                          => 'America/Montevideo',
		'Urumqi'                           => 'Asia/Urumqi',
		'Uzbekistan'                       => 'Asia/Tashkent',
		'Vanuatu'                          => 'Pacific/Efate',
		'Venezuela'                        => 'America/Caracas',
		'Venezuela Standard Time'          => 'America/Caracas',
		'Vladivostok'                      => 'Asia/Vladivostok',
		'Vladivostok Standard Time'        => 'Asia/Vladivostok',
		'Volgograd'                        => 'Europe/Volgograd',
		'Vostok'                           => 'Antarctica/Vostok',
		'W. Australia Standard Time'       => 'Australia/Perth',
		'W. Central Africa Standard Time'  => 'Africa/Lagos',
		'W. Europe Standard Time'          => 'Europe/Berlin',
		'Wake'                             => 'Pacific/Wake',
		'Wallis'                           => 'Pacific/Wallis',
		'West Asia Standard Time'          => 'Asia/Tashkent',
		'West Pacific Standard Time'       => 'Pacific/Port_Moresby',
		'Yakutsk'                          => 'Asia/Yakutsk',
		'Yakutsk Standard Time'            => 'Asia/Yakutsk',
		'Yekaterinburg'                    => 'Asia/Yekaterinburg',
		'Yerevan'                          => 'Asia/Yerevan',
		'Yukon'                            => 'America/Yakutat',
	);

	/**
	 * @var array a map of timezones which are valid for DateTimeZone but return
	 * false when used in strtotime
	 */
	protected $invalid_legacy_for_strotime = array(
		'US/Eastern' => true,
	);

	/**
	 * @var array|bool Map of DateTimeZone known identifiers or false
	 */
	protected $_tz_identifiers = NULL;

	/**
	 * @var Ai1ec_Tzparser Reference for self instance
	 */
	static protected $_instance = NULL;

	/**
	 * instance method
	 *
	 * Singleton factory method.
	 *
	 * @return Ai1ec_Tzparser Instance of self
	 */
	static public function instance( ) {
		if ( NULL === self::$_instance ) {
			self::$_instance = new self( );
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * Use this in debug mode to parse XML file always.
	 *
	 * @return void Constructor does not return
	 */
	public function __construct( $file = NULL ) {
		if ( NULL !== $file ) {
			$this->load_file( $file );
		}
		$this->_init_identifiers( );
	}

	/**
	 * get_name method
	 *
	 * Method to check for valid zone identifier.
	 *
	 * @uses DateTimeZone::listIdentifiers() To check if zone is known
	 * @uses DateTimeZone::__construct()     To check for legacy valid timezones
	 * @uses Ai1ec_Tzparser::guess_zone()    To guess unknown zone
	 *
	 * @param string $zone Zone identifier to check
	 *
	 * @return string|bool Valid Olson TZ identifier or false on failure
	 */
	public function get_name( $zone ) {
		if ( false === $this->tz_identifiers ) {
			return $zone; // anything should do, as zones are not supported
		}
		if ( ! isset( $this->tz_identifiers[$zone] ) ) {
			$valid_legacy = false;
			try {
				new DateTimeZone( $zone ); // throw away instantly
				$valid_legacy = true;
			} catch ( Exception $excpt ) {
				$valid_legacy = false;
			}
			if (
				true === $valid_legacy &&
				! isset( $this->invalid_legacy_for_strotime[$zone] )
			) {
				$this->tz_identifiers[$zone] = $zone;
				unset( $valid_legacy );
				return $zone;
			}
			unset( $valid_legacy );
			return $this->guess_zone( $zone );
		}
		return $zone;
	}

	/**
	 * guess_zone method
	 *
	 * Attempt to find Olson zone name given unknown zone identifier.
	 *
	 * @param string $meta_name Zone identifier to decode
	 *
	 * @return string|bool Olson timezone identifier or false on failure
	 */
	public function guess_zone( $meta_name ) {
		if ( isset( $this->_zones[$meta_name] ) ) {
			return $this->_zones[$meta_name];
		}
		$name_variants = array(
			strtr( $meta_name, ' ', '_' ),
			strtr( $meta_name, '_', ' ' ),
		);
		if ( false !== ( $parenthesis_pos = strpos( $meta_name, '(' ) ) ) {
			foreach ( $name_variants as $name ) {
				$name_variants[] = substr( $name, 0, $parenthesis_pos - 1 );
			}
		}
		foreach ( $name_variants as $name ) {
			if ( isset( $this->_zones[$name] ) ) {
				// cache to avoid future lookups and return
				$this->_zones[$meta_name] = $this->_zones[$name];
				return $this->_zones[$name];
			}
		}
		if (
			isset( $meta_name{0} ) &&
			'(' === $meta_name{0} &&
			$closing_pos = strpos( $meta_name, ')' )
		) {
			$meta_name = trim( substr( $meta_name, $closing_pos + 1 ) );
			return $this->guess_zone( $meta_name );
		}
		if (
			false === strpos( $meta_name, ' Standard ' ) &&
			false !== ( $time_pos = strpos( $meta_name, ' Time' ) )
		) {
			$meta_name = substr( $meta_name, 0, $time_pos ) .
				' Standard' . substr( $meta_name, $time_pos );
			return $this->guess_zone( $meta_name );
		}
		return false;
	}

	/**
	 * load_file method
	 *
	 * Method to parse XML file with zone data.
	 *
	 * @uses simplexml_load_file() To read XML input
	 *
	 * @param string $file File name with zone data
	 *
	 * @return bool Success
	 */
	public function load_file( $file ) {
		if ( ! is_file( $file ) ) {
			return false;
		}
		$input_xml = simplexml_load_file( $file );
		if ( false === $input_xml ) {
			return false;
		}
		$zone_list = $input_xml->xpath('timezoneData/mapTimezones');
		foreach ( $zone_list as $map_list ) {
			foreach ( $map_list->xpath('mapZone') as $zone_xml ) {
				$attrs	 = $zone_xml->attributes();
				$keyword = $zone_name = $territory = NULL;
				foreach ( $attrs as $key => $value ) {
					if ( 'other' === $key ) {
						$keyword   = (string)$value;
					} elseif ( 'type' === $key ) {
						$zone_name = (string)$value;
					} elseif ( 'territory' === $key ) {
						$territory = (string)$value;
					}
				}
				if ( NULL === $territory || 0 === strcmp( '001', $territory ) ) {
					$this->_zones[$keyword] = $zone_name;
				}
				unset( $attrs, $keyword, $zone_name, $territory );
			}
			unset( $zone_xml );
		}
		unset( $input_xml, $zone_list, $map_list );
		return true;
	}

	/**
	 * generate_map method
	 *
	 * Development method to generate zone map for re-inclusion in file.
	 *
	 * @return void Method does not return
	 */
	public function generate_map( ) {
		ksort( $this->_zones );
		echo "\tarray(\n";
		$limit_length = 32;
		foreach ( $this->_zones as $title => $name ) {
			$spaces = ' ';
			$length = strlen( $title );
			if ( $length < $limit_length ) {
				$spaces = str_repeat( $spaces, $limit_length - $length );
			}
			echo "\t\t'{$title}' {$spaces}=> '{$name}',\n";
		}
		echo "\t);\n";
	}

	/**
	 * _init_identifiers method
	 *
	 * Method to prepare easy to use DateTimeZone accepted identifiers
	 * list to speed up future lookups.
	 *
	 * @return bool Success
	 */
	protected function _init_identifiers( ) {
		$this->tz_identifiers = DateTimeZone::listIdentifiers();
		if ( ! is_array( $this->tz_identifiers ) ) {
			return false;
		}
		$mapped_identifiers = array();
		foreach ( $this->tz_identifiers as $identifier ) {
			$identifier = (string)$identifier;
			$mapped_identifiers[(string)$identifier] = true;
			$this->_zones[$identifier] = $identifier;
		}
		$this->tz_identifiers = $mapped_identifiers;
		unset( $mapped_identifiers );
		return true;
	}

}

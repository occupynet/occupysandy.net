<?php

/**
 * The get repeat box snippet.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.View
 */
class Ai1ec_View_Admin_Get_repeat_Box extends Ai1ec_Base {
	/**
	 * get_repeat_box function
	 *
	 * @return string
	 **/
	public function get_repeat_box() {
		$time_system = $this->_registry->get( 'date.system' );
		$loader = $this->_registry->get( 'theme.loader' );
		$repeat  = (int) $_REQUEST["repeat"];
		$repeat  = $repeat == 1 ? 1 : 0;
		$post_id = (int) $_REQUEST["post_id"];
		$count   = 100;
		$end     = 0;
		$until   = $time_system->current_time( true );

		// try getting the event
		try {
			$event = $this->_registry->get( 'model.event', $post_id );
			$rule = '';

			if ( $repeat ) {
				$rule = $event->get( 'recurrence_rules' )
					? $event->get( 'recurrence_rules' )
					: '';
			} else {
				$rule = $event->get( 'exception_rules' ) ?
					$event->get( 'exception_rules' )
					: '';
			}

			$rule = $this->_registry->get( 'recurrence.rule' )->filter_rule( $rule );

			$rc = new SG_iCal_Recurrence(
				new SG_iCal_Line( 'RRULE:' . $rule )
			);

			if ( $until = $rc->getUntil() ) {
				$until = ( is_numeric( $until ) )
				? $until
				: strtotime( $until );
				$end = 2;
			} elseif ( $count = $rc->getCount() ) {
				$count = ( is_numeric( $count ) ) ? $count : 100;
				$end = 1;
			}
		} catch( Ai1ec_Event_Not_Found_Exception $e ) {
			$rule = '';
			$rc   = new SG_iCal_Recurrence(
				new SG_iCal_Line( 'RRULE:' )
			);
		}

		$args = array(
			'row_daily'       => $this->row_daily(
				false,
				$rc->getInterval() ? $rc->getInterval() : 1
			),
			'row_weekly'      => $this->row_weekly(
				false,
				$rc->getInterval() ? $rc->getInterval() : 1,
				is_array( $rc->getByDay() ) ? $rc->getByDay() : array()
			),
			'row_monthly'     => $this->row_monthly(
				false,
				$rc->getInterval() ? $rc->getInterval() : 1,
				! $this->_is_monthday_empty( $rc ),
				$rc->getByMonthDay() ? $rc->getByMonthDay() : array(),
				$rc->getByDay() ? $rc->getByDay() : array()
			),
			'row_yearly'      => $this->row_yearly(
				false,
				$rc->getInterval() ? $rc->getInterval() : 1,
				is_array( $rc->getByMonth() ) ? $rc->getByMonth() : array()
			),
			'row_custom'      => $this->row_custom(
				false,
				$this->get_date_array_from_rule( $rule )
			),
			'count'           => $this->create_count_input(
				'ai1ec_count',
				$count
			) . Ai1ec_I18n::__( 'times' ),
			'end'             => $this->create_end_dropdown( $end ),
			'until'           => $until,
			'repeat'          => $repeat,
			'ending_type'     => $end,
			'selected_tab'    => $rc->getFreq()
				? strtolower( $rc->getFreq() )
				: 'custom',
		);
		$output = array(
			'error'   => false,
			'message' => $loader->get_file(
				'box_repeat.php',
				$args,
				true
			)->get_content(),
			'repeat'  => $repeat,
		);
		$json_strategy = $this->_registry->get( 'http.response.render.strategy.json' );
		$json_strategy->render( array( 'data' => $output ) );
	}

	/**
	 * get_weekday_by_id function
	 *
	 * Returns weekday name in English
	 *
	 * @param int $day_id Day ID
	 *
	 * @return string
	 **/
	public function get_weekday_by_id( $day_id, $by_value = false ) {
		// do not translate this !!!
		$week_days = array(
			0 => 'SU',
			1 => 'MO',
			2 => 'TU',
			3 => 'WE',
			4 => 'TH',
			5 => 'FR',
			6 => 'SA',
		);

		if ( $by_value ) {
			while ( $_name = current( $week_days ) ) {
				if ( $_name == $day_id ) {
					return key( $week_days );
				}
				next( $week_days );
			}
			return false;
		}
		return $week_days[$day_id];
	}

	/**
	 * convert_rrule_to_text method
	 *
	 * Convert a `recurrence rule' to text to display it on screen
	 *
	 * @return void
	 **/
	public function convert_rrule_to_text() {
		$error   = false;
		$message = '';
		// check to see if RRULE is set
		if ( isset( $_REQUEST['rrule'] ) ) {
			// check to see if rrule is empty
			if ( empty( $_REQUEST['rrule'] ) ) {
				$error   = true;
				$message = Ai1ec_I18n::__(
					'Recurrence rule cannot be empty.'
				);
			} else {
				//list( $rule, $value ) = explode( '=', $_REQUEST['rrule'], 2 );
				//if ( in_array( array(), $rule ) ) {
				//	$message = $this->_registry->get( 'recurrence.date' );
				//
				//} else {
					$rrule = $this->_registry->get( 'recurrence.rule' );
					// convert rrule to text
					$message = ucfirst(
						$rrule->rrule_to_text( $_REQUEST['rrule'] )
					);
					//}
			}
		} else {
			$error   = true;
			$message = Ai1ec_I18n::__(
				'Recurrence rule was not provided.'
			);
		}
		$output = array(
			'error' 	=> $error,
			'message'	=> get_magic_quotes_gpc()
			? stripslashes( $message )
			: $message,
		);

		$json_strategy = $this->_registry->get( 'http.response.render.strategy.json' );
		$json_strategy->render( array( 'data' => $output ) );
	}

	/**
	 * create_end_dropdown function
	 *
	 * Outputs the dropdown list for the recurrence end option.
	 *
	 * @param int $selected The index of the selected option, if any
	 * @return void
	 **/
	protected function create_end_dropdown( $selected = NULL ) {
		ob_start();

		$options = array(
			0 => Ai1ec_I18n::__( 'Never' ),
			1 => Ai1ec_I18n::__( 'After' ),
			2 => Ai1ec_I18n::__( 'On date' ),
		);

		?>
		<select name="ai1ec_end" id="ai1ec_end">
					<?php foreach( $options as $key => $val ): ?>
						<option value="<?php echo $key ?>"
				<?php if( $key === $selected ) echo 'selected="selected"' ?>>
							<?php echo $val ?>
						</option>
					<?php endforeach ?>
				</select>
		<?php

				$output = ob_get_contents();
				ob_end_clean();

				return $output;
	}

	/**
	 * row_daily function
	 *
	 * Returns daily selector
	 *
	 * @return void
	 **/
	protected function row_daily( $visible = false, $selected = 1 ) {
		$loader = $this->_registry->get( 'theme.loader' );

		$args = array(
			'visible'  => $visible,
			'count'    => $this->create_count_input(
				'ai1ec_daily_count',
				$selected,
				365
			) . Ai1ec_I18n::__( 'day(s)' ),
		);
		return $loader->get_file( 'row_daily.php', $args, true )
			->get_content();
	}

	/**
	 * row_custom function
	 *
	 * Returns custom dates selector
	 *
	 * @return void
	 **/
	protected function row_custom( $visible = false, $dates = array() ) {
		$loader = $this->_registry->get( 'theme.loader' );

		$args = array(
			'visible'        => $visible,
			'selected_dates' => implode( ',', $dates )
		);
		return $loader->get_file( 'row_custom.php', $args, true )
			->get_content();
	}

	/**
	 * Generates and returns "End after X times" input
	 *
	 * @param Integer|NULL $count Initial value of range input
	 *
	 * @return String Repeat dropdown
	 */
	protected function create_count_input( $name, $count = 100, $max = 365 ) {
		ob_start();

		if ( ! $count ) {
			$count = 100;
		}
		?>
	<input type="range" name="<?php echo $name ?>" id="<?php echo $name ?>"
		min="1" max="<?php echo $max ?>"
		<?php if ( $count ) echo 'value="' . $count . '"' ?> />
	<?php
		return ob_get_clean();
	}

	/**
	 * row_weekly function
	 *
	 * Returns weekly selector
	 *
	 * @return void
	 **/
	protected function row_weekly(
		$visible        = false,
		$count          = 1,
		array $selected = array()
	) {
		global $wp_locale;
		$start_of_week = $this->_registry->get( 'model.option' )
			->get( 'start_of_week', 1 );
		$loader = $this->_registry->get( 'theme.loader' );

		$options = array();
		// get days from start_of_week until the last day
		for ( $i = $start_of_week; $i <= 6; ++$i ) {
			$options[$this->get_weekday_by_id( $i )] = $wp_locale
				->weekday_initial[$wp_locale->weekday[$i]];
		}

		// get days from 0 until start_of_week
		if ( $start_of_week > 0 ) {
			for ( $i = 0; $i < $start_of_week; $i++ ) {
				$options[$this->get_weekday_by_id( $i )] = $wp_locale
					->weekday_initial[$wp_locale->weekday[$i]];
			}
		}

		$args = array(
			'visible'    => $visible,
			'count'      => $this->create_count_input(
				'ai1ec_weekly_count',
				$count,
				52
			) . Ai1ec_I18n::__( 'week(s)' ),
			'week_days'  => $this->create_list_element(
				'ai1ec_weekly_date_select',
				$options,
				$selected
			)
		);
		return $loader->get_file( 'row_weekly.php', $args, true )
			->get_content();
	}

	/**
	 * Creates a grid of weekday, day, or month selection buttons.
	 *
	 * @return string
	 */
	protected function create_list_element(
		$name,
		array $options  = array(),
		array $selected = array()
	) {
		ob_start();
		?>
<div class="ai1ec-btn-group-grid" id="<?php echo $name; ?>">
	<?php foreach ( $options as $key => $val ) : ?>
		<div class="ai1ec-pull-left">
			<a class="ai1ec-btn ai1ec-btn-default ai1ec-btn-block
				<?php echo in_array( $key, $selected ) ? 'ai1ec-active' : ''; ?>">
				<?php echo $val; ?>
			</a>
			<input type="hidden" name="<?php echo $name . '_' . $key; ?>"
				value="<?php echo $key; ?>">
		</div class="ai1ec-pull-left">
	<?php endforeach; ?>
</div>
<input type="hidden" name="<?php echo $name; ?>"
	value="<?php echo implode( ',', $selected ) ?>">
<?php
		return ob_get_clean();
	}

	/**
	 * row_monthly function
	 *
	 * Returns monthly selector
	 *
	 * @return void
	 **/
	protected function row_monthly(
		$visible    = false,
		$count      = 1,
		$bymonthday = true,
		$month      = array(),
		$day        = array()
	) {
		global $wp_locale;
		$start_of_week = $this->_registry->get( 'model.option' )
			->get( 'start_of_week', 1 );
		$loader = $this->_registry->get( 'theme.loader' );

		$options_wd = array();
		// get days from start_of_week until the last day
		for ( $i = $start_of_week; $i <= 6; ++$i ) {
			$options_wd[$this->get_weekday_by_id( $i )] = $wp_locale
				->weekday[$i];
		}

		// get days from 0 until start_of_week
		if ( $start_of_week > 0 ) {
			for ( $i = 0; $i < $start_of_week; $i++ ) {
				$options_wd[$this->get_weekday_by_id( $i )] = $wp_locale
					->weekday[$i];
			}
		}

		// get options like 1st/2nd/3rd for "day number"
		$options_dn = array( 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5 );
		foreach ( $options_dn as $_dn ) {
			$options_dn[$_dn] = $this->_registry->get(
				'date.time',
				strtotime( $_dn . '-01-1998 12:00:00' )
			)->format_i18n( 'jS' );
		}
		$options_dn['-1'] = Ai1ec_I18n::__( 'last' );

		$byday_checked       = $bymonthday ? '' : 'checked';
		$byday_expanded      = $bymonthday ? 'ai1ec-collapse' : 'ai1ec-in';
		$bymonthday_checked  = $bymonthday ? 'checked' : '';
		$bymonthday_expanded = $bymonthday ? 'ai1ec-in' : 'ai1ec-collapse';

		$args = array(
			'visible'              => $visible,
			'count'                => $this->create_count_input(
				'ai1ec_monthly_count',
				$count,
				12
			) . Ai1ec_I18n::__( 'month(s)' ),
			'month'                => $this->create_monthly_date_select(
				$month
			),
			'day_nums'             => $this->create_select_element(
				'ai1ec_monthly_byday_num',
				$options_dn,
				$this->_get_day_number_from_byday( $day )
			),
			'week_days'            => $this->create_select_element(
				'ai1ec_monthly_byday_weekday',
				$options_wd,
				$this->_get_day_shortname_from_byday( $day )
			),
			'bymonthday_checked'  => $bymonthday_checked,
			'byday_checked'       => $byday_checked,
			'bymonthday_expanded' => $bymonthday_expanded,
			'byday_expanded'      => $byday_expanded,
		);
		return $loader->get_file( 'row_monthly.php', $args, true )
			->get_content();
	}

	/**
	 * Creates selector for dates in monthly repeat tab.
	 *
	 * @return void
	 */
	protected function create_monthly_date_select( $selected = array() ) {
		$options = array();
		for ( $i = 1; $i <= 31; ++$i ) {
			$options[$i] = $i;
		}
		return $this->create_list_element(
			'ai1ec_montly_date_select',
			$options,
			$selected
		);
	}

	/**
	 * create_on_the_select function
	 *
	 *
	 *
	 * @return string
	 **/
	protected function create_on_the_select(
		$f_selected = false,
		$s_selected = false
	) {
		$ret = '';

		$first_options = array(
			'0' => Ai1ec_I18n::__( 'first' ),
			'1' => Ai1ec_I18n::__( 'second' ),
			'2' => Ai1ec_I18n::__( 'third' ),
			'3' => Ai1ec_I18n::__( 'fourth' ),
			'4' => '------',
			'5' => Ai1ec_I18n::__( 'last' )
		);
		$ret = $this->create_select_element(
			'ai1ec_monthly_each_select',
			$first_options,
			$f_selected,
			array( 4 )
		);

		$second_options = array(
			'0'   => Ai1ec_I18n::__( 'Sunday' ),
			'1'   => Ai1ec_I18n::__( 'Monday' ),
			'2'   => Ai1ec_I18n::__( 'Tuesday' ),
			'3'   => Ai1ec_I18n::__( 'Wednesday' ),
			'4'   => Ai1ec_I18n::__( 'Thursday' ),
			'5'   => Ai1ec_I18n::__( 'Friday' ),
			'6'   => Ai1ec_I18n::__( 'Saturday' ),
			'7'   => '--------',
			'8'   => Ai1ec_I18n::__( 'day' ),
			'9'   => Ai1ec_I18n::__( 'weekday' ),
			'10'  => Ai1ec_I18n::__( 'weekend day' )
		);

		return $ret . $this->create_select_element(
			'ai1ec_monthly_on_the_select',
			$second_options,
			$s_selected,
			array( 7 )
		);
	}

	/**
	 * create_select_element function
	 *
	 * Render HTML <select> element
	 *
	 * @param string $name          Name of element to be rendered
	 * @param array  $options       Select <option> values as key=>value pairs
	 * @param string $selected      Key to be marked as selected [optional=false]
	 * @param array  $disabled_keys List of options to disable [optional=array]
	 *
	 * @return string Rendered <select> HTML element
	 **/
	protected function create_select_element(
		$name,
		array $options       = array(),
		$selected            = false,
		array $disabled_keys = array()
	) {
		ob_start();
		?>
	<select name="<?php echo $name ?>" id="<?php echo $name ?>">
				<?php foreach( $options as $key => $val ): ?>
					<option value="<?php echo $key ?>"
			<?php echo $key === $selected ? 'selected="selected"' : '' ?>
			<?php echo in_array( $key, $disabled_keys ) ? 'disabled' : '' ?>>
						<?php echo $val ?>
					</option>
				<?php endforeach ?>
			</select>
	<?php
			return ob_get_clean();
	}

	/**
	 * row_yearly function
	 *
	 * Returns yearly selector
	 *
	 * @return void
	 **/
	protected function row_yearly(
		$visible = false,
		$count   = 1,
		$year    = array(),
		$first   = false,
		$second  = false
	) {
		$loader = $this->_registry->get( 'theme.loader' );

		$args = array(
			'visible'              => $visible,
			'count'                => $this->create_count_input(
				'ai1ec_yearly_count',
				$count,
				10
			) . Ai1ec_I18n::__( 'year(s)' ),
			'year'                 => $this->create_yearly_date_select( $year ),
			'on_the_select'        => $this->create_on_the_select(
				$first,
				$second
			),
		);
		return $loader->get_file( 'row_yearly.php', $args, true )
			->get_content();
	}

	/**
	 * create_yearly_date_select function
	 *
	 *
	 *
	 * @return void
	 **/
	protected function create_yearly_date_select( $selected = array() ) {
		global $wp_locale;
		$options = array();
		for ( $i = 1; $i <= 12; ++$i ) {
			$options[$i] = $wp_locale->month_abbrev[
			$wp_locale->month[sprintf( '%02d', $i )]
			];
		}
		return $this->create_list_element(
			'ai1ec_yearly_date_select',
			$options,
			$selected
		);
	}

	/**
	 * Converts recurrence rule to array of string of dates.
	 *
	 * @param string $rule RUle.
	 *
	 * @return array Array of dates or empty array.
	 * @throws Ai1ec_Bootstrap_Exception
	 */
	protected function get_date_array_from_rule( $rule ) {
		if (
			'RDATE' !== substr( $rule, 0, 5 ) &&
			'EXDATE' !== substr( $rule, 0, 6 )
		) {
			return array();
		}
		$line             = new SG_iCal_Line( 'RRULE:' . $rule );
		$dates            = $line->getDataAsArray();
		$dates_as_strings = array();
		foreach ( $dates as $date ) {
			$date = str_replace( array( 'RDATE=', 'EXDATE=' ), '', $date );
			$date = $this->_registry->get( 'date.time', $date );
			$dates_as_strings[] = $date->format('m/d/Y');
		}
		return $dates_as_strings;
	}

	/**
	 * Returns whether recurrence rule has non null ByMonthDay.
	 *
	 * @param SG_iCal_Recurrence $rc iCal class.
	 *
	 * @return bool True or false.
	 */
	protected function _is_monthday_empty( SG_iCal_Recurrence $rc ) {
		return false === $rc->getByMonthDay();
	}

	/**
	 * Returns day number from by day array.
	 *
	 * @param array $day
	 *
	 * @return bool|int Day of false if empty array.
	 */
	protected function _get_day_number_from_byday( array $day ) {
		return isset( $day[0] ) ? (int) $day[0] : false;
	}

	/**
	 * Returns string part from "ByDay" recurrence rule.
	 *
	 * @param array $day Element to parse.
	 *
	 * @return bool|string False if empty or not matched, otherwise short day
	 *                     name.
	 */
	protected function _get_day_shortname_from_byday( $day ) {
		if ( empty( $day ) ) {
			return false;
		}
		$value = $day[0];
		if ( preg_match('/[-]?\d([A-Z]+)/', $value, $matches ) ) {
			return $matches[1];
		}
		return false;
	}
}

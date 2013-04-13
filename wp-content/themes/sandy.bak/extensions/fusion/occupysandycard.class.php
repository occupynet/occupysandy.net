<?php
class OccupySandyCard {
	private $row = array();
	private $cols = array();
	private $cardTitle;
	private $htmlClasses;

	function __construct ($row = array(), $cols = array()) {
		$this->row = $row;
		$this->cols = $cols;

		$this->parseData();

		$this->htmlClasses = apply_filters('occupysandy_card_classes', $this->htmlClasses, $this);
		$this->cardTitle = apply_filters('occupysandy_card_title', $this->cardTitle, $this);
	}

	function parseData () {
		$this->htmlClasses = array('type' => array(), 'region' => array(), 'state' => array());
		$this->cardTitle = '';

		if ($this->is_distro_center()) :
			$this->htmlClasses['type'][] = 'hub';
			$this->cardTitle = 'Main Distribution Center';
		endif;
		
		if ($this->is_drop_off()) :
			$this->htmlClasses['type'][] = 'dropoff';

			// We may have already gotten a title, if this is a distro center.
			if (strlen($this->cardTitle) < 1) :
				$this->cardTitle = 'Drop-Off '.($this->is_volunteer() ? '+ Volunteer' : 'Only');
			endif;
		endif;

		if ($this->is_volunteer()) :
			$this->htmlClasses['type'][] = 'volunteer';
			
			// We may have already gotten a title, if this is a distro center or a drop-off+volunteer location
			if (strlen($this->cardTitle) < 1) :
				$this->cardTitle = 'Volunteer Only';
			endif;
		endif;

		if (strlen($this->cardTitle) < 1) :	
			if (strlen($this->field('type')) > 0) :
				$this->cardTitle = $this->field('type');
			else :
				$this->cardTitle = 'Unknown';
			endif;
		endif;

		if (count($this->htmlClasses['type']) == 0) :
			$this->htmlClasses['type'][] = 'unknown';
		endif;

		
		$this->htmlClasses['region'] = array();
		$region = $this->field('Region');
		if (strlen($region) > 0) :
			$this->htmlClasses['region'][$region] = 'region-'.sanitize_title($region);
		else :
			$this->htmlClasses['region']['Other'] = 'region-other';
		endif;

		$state = $this->get_state();
		if (strlen($state) > 0) :
			$this->htmlClasses['state'][$state] = 'state-'.strtoupper(sanitize_title($state));
		else :
			$this->htmlClasses['state']['NY'] = 'state-NY';
		endif;
	}

	function normalize_state ($state) {
		$map = array(
		"new-york" => 'NY',
		'new-jersey' => 'NJ',
		'pennsylvania' => 'PA',
		);
		
		// Normalize key.
		$key = sanitize_title(preg_replace('/\s+/', ' ', $state));

		return (isset($map[$key]) ? $map[$key] : $state);
	}
	
	function columns () {
		return $this->cols;
	}
	function has_field ($i) {
		if (!isset($this->row[$i])) :
			$found = array_search($i, $this->cols);
			if (false !== $found) :
				$i = $found;
			endif;
		endif;
		return (isset($this->row[$i]));
	}

	function get_values ($what) {
		
		$values = null;
		if (method_exists($this, $what)) :
			$values = $this->{$what}();
		endif;
		
		if (!is_array($values)) :
			if (is_null($values)) :
				$values = array();
			else :
				$values = array($values);
			endif;
		endif;
		
		if (count($values) == 0) :
			$values[] = $this->field($what);
		endif;
		
		return $values;
	}
	
	function field ($i) {
		if (!isset($this->row[$i])) :
			$found = array_search($i, $this->cols);
			if (false !== $found) :
				$i = $found;
			endif;
		endif;

		$ret = NULL;
		if (isset($this->row[$i])) :
			$ret = $this->row[$i];
		endif;
		return $ret;
	}

	function has_type ($what) {
		$type = $this->field('type');
		return (0<preg_match('/\b'.$what.'\b/i', $type));	
	}
	function is_drop_off () {
		return ($this->has_type('drop-?off') or $this->is_distro_center());
	}
	function is_volunteer () {
		return ($this->has_type('volunteer') or $this->is_distro_center());
	}
	function is_distro_center () {
		return $this->has_type('main distribution center');
	}
	function is_other_type () {
		return !($this->is_drop_off() or $this->is_volunteer() or $this->is_distro_center());
	}

	function get_state () {
		global $os_regionToState;

		$ret = $this->field('State');
		if (is_null($ret) or (strlen($ret) == 0)) :
			$region = $this->field('Region');
			if (preg_match('/\b(New Jersey|NJ)\b/i', $region)) :
				$ret = 'NJ';
			elseif (!is_null($region) and (strlen($region) > 0)) :
				$ret = 'NY'; // Assume NY if (1) we have a region but (2) it's not in our list.
				$index = strtolower(trim(preg_replace('/\s+/', ' ', $region)));
				if (isset($os_regionToState[$index])) :
					$ret = $os_regionToState[$index];
				endif;
			endif;
		endif;
		
		// Normalize state names
		return $this->normalize_state($ret);
	}

	function get_type_classes () { return $this->htmlClasses['type']; }
	function get_region_classes () { return $this->htmlClasses['region']; }
	function get_state_classes () { return $this->htmlClasses['state']; }
	function get_card_class () { return implode(" ", array_merge($this->get_type_classes(), $this->get_region_classes(), $this->get_state_classes())); }
	function get_card_heading () { return $this->cardTitle; }
	function get_title () { return $this->field('Title'); }
	function get_address () { return $this->field('Address'); }
	function get_status () { return $this->field('Status'); }
	function get_times () { return $this->field('DateAndTimes'); }
	function get_contact () { return $this->field('Contact Info'); }
	function get_link () { return $this->field('Link'); }
	function get_description () { return $this->field('Description'); }
	function get_coordinates () {
		return array("lat" => $this->field('Latitude'), "long" => $this->field('Longitude'));
	}
	function get_timestamp ($fmt = 'r') {
		$time = $this->field('Timestamp');
		$ts = strtotime($time);
		if ($ts > 0) : // Can we get a legit Unix-epoch timestamp?
			$ret = date($fmt, $ts);
		else : // Sigh.
			$ret = $time;
		endif;
		return $ret;
	}
}


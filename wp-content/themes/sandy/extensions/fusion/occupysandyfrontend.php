<?php
// Required classes.

require_once('occupysandycard.class.php');

class OccupySandyFrontend {
	function __construct () {
		add_shortcode('location-cards', array(&$this, 'location_cards'));
	} /* OccupySandyFrontend */
	
	function location_cards ($atts, $content) {
		global $wpdb;

		$atts = shortcode_atts(array(
			"for" => null,
			"type" => null,
			"with" => null,
			"of" => null,
			"using" => 'inline',
		), $atts);
		
		$mm = array();
		if (!is_null($atts['for'])) :
			$mm['Region'] = $atts['for'];
		endif;
		if (!is_null($atts['type'])) :
			$mm['type'] = $atts['type'];
		endif;
		if (!is_null($atts['with'])) :
			if (!is_null($atts['of'])) :
				$col = $atts['with'];
				
				// Normalize & quote if necessary
				if (preg_match('/[^A-Za-z0-9]/', $col)) :
					$col = $wpdb->escape($col);
					$col = "'${col}'";
				endif;
				
				$values = explode("/", $atts['of']);
				$mm[$col] = $values;
			endif;
		endif;
		
		ob_start();
		the_occupy_sandy_cards(array(
		"matches" => $mm,
		"template-class" => $atts['using'],
		));
		$html = ob_get_clean();

		return $html;
	}
}
$GLOBALS['OccupySandyFrontend'] = new OccupySandyFrontend;

// Template functions.
function get_occupy_sandy_cards ($params = array()) {
	$params = wp_parse_args($params, array(
	"raw" => true,
	));
	$params['raw'] = true; // Required.

	$data = get_occupy_sandy_data($params);
	if (is_wp_error($data)) :
		$ret = $data;
	else :
		$ret = array();
		if (!is_null($data->rows)) :
			foreach ($data->rows as $datum) :
				$ret[] = new OccupySandyCard($datum, $data->columns);
			endforeach;
		endif;
	endif;
	return $ret;
}

function the_occupy_sandy_cards ($params = array()) {
	global $OccupySandyCard;
	global $wpdb;
	
	$params = wp_parse_args($params, array(
	"matches" => null,
	"template-class" => null,
	));
	
	if (is_array($params['matches'])) :
		$whereClauses = array();
		foreach ($params['matches'] as $col => $value) :
			if (!is_array($value)) :
				$value = array($value);
			endif;
			
			if (count($value) > 1) :
				$operator = 'IN';
				$operand = "(";
				if (count($value) > 0) :
				$operand .= "'" . implode("', '", array_map(function ($v) {
						return $GLOBALS['wpdb']->escape(trim($v));
				}, $value)) . "'";
				endif;
				$operand .= ")";
			else :
				$operator = '=';
				$operand = "'".$wpdb->escape(reset($value))."'";
			endif;
			
			$whereClauses[] = "$col $operator $operand";
		endforeach;
		$params['where'] = implode(' AND ', $whereClauses);
	endif;                
	
	$cards = get_occupy_sandy_cards($params);
	if (is_wp_error($cards)) :
		$cards = array($cards);
	endif;

	foreach ($cards as $card) :
		$OccupySandyCard = $card;

		if (is_null($params['template-class'])) :
			$primeClass = reset(explode(" ", $card->get_card_class()));
		else :
			$primeClass = $params['template-class'];
		endif;
		get_template_part('card', $primeClass);
	endforeach;
}

function get_the_occupy_sandy_card () { global $OccupySandyCard; return $OccupySandyCard; }

function get_occupy_sandy_possible_values_for ($fieldName, $params = array()) {
	$cards = get_occupy_sandy_cards($params);
	$ret = array();
	foreach ($cards as $card) :
		$value = NULL;
		if (method_exists($card, $fieldName)) :
			$value = $card->{$fieldName}();
		endif;

		if (!is_array($value)) :
			if (is_null($value)) :
				$value = array();
			else :
				$value = array($value);
			endif;
		endif;

		if (count($value) == 0) :
			$value[] = $card->field($fieldName);
		endif;

		foreach ($value as $idx => $v) :
			if (!is_numeric($idx)) :
				$i = urlencode($idx) . '/' . urlencode($v);
			else :
				$i = urlencode($v);
			endif;
			if (!isset($ret[$i])) : $ret[$i] = 0; endif;
			$ret[$i] += 1;
		endforeach;
	endforeach;
	return $ret;
}

function get_mapped_occupy_sandy_possible_values_for ($from, $to, $params = array(), $cards = null) {
	if (is_null($cards)) :
		$cards = get_occupy_sandy_cards($params);
	endif;
	
	$ret = array();
	foreach ($cards as $card) :
		$fromValue = $card->get_values($from);
		
		foreach ($fromValue as $idx => $v) :
			$i = urlencode($v);

			if (!isset($ret[$i])) : $ret[$i] = array(); endif;

			if (!is_numeric($idx)) :
				$ret[$i]['label'] = $idx;
			endif;

			if (!isset($ret[$i]['values'])) : $ret[$i]['values'] = array(); endif;
			if (!isset($ret[$i]['N'])) : $ret[$i]['N'] = 0; endif;
			
			$toValues = $card->get_values($to);
			$ret[$i]['values'] = array_merge($ret[$i]['values'], $toValues);
			$ret[$i]['N'] += 1;
		endforeach;
	endforeach;
	return $ret;
}



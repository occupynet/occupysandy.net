<?php
class Slickr_Flickr_Tooltip {

    const HELP = '<span class="dashicons dashicons-editor-help"></span>';      

	private $labels = array();
	private $tabindex;
		
	function __construct($labels) {
		$this->labels = is_array($labels) ? $labels : array();
		$this->tabindex = 100;
	}

	function heading($label, $args=false) {
		$heading = array_key_exists($label,$this->labels) ? (__($this->labels[$label]['heading']).self::HELP) : '';
		return $args ? $this->apply_args ($heading, $args) : $heading; 
	}

	function text($label, $args=false) {
		$text =  array_key_exists($label,$this->labels) ? __($this->labels[$label]['tip']) : ''; 
		return $args ? $this->apply_args ($text, $args) : $text; 
	}

	function label($label, $text_only=false) {
		return $text_only ? $this->heading($label) : $this->tip($label); 
	}

	function tip($label,$args=false) {
		$heading = $this->heading($label, $args); 
		return $heading ? sprintf('<a href="#" class="diy-tooltip" tabindex="%3$s">%1$s<span class="tip">%2$s</span></a>',
			$heading, $this->text($label, $args), $this->tabindex++) : ucfirst($label);
	}

	function apply_args($content, $args = false) {
		if ($args && (strpos($content, '$s') !== FALSE)) {
			if (is_array($args))
				switch (count($args)) {
					case 4: $content = sprintf($content, $args[0], $args[1], $args[2], $args[3]);
					case 3: $content = sprintf($content, $args[0], $args[1], $args[2]);
					case 2: $content = sprintf($content, $args[0], $args[1]);
					default: $content = sprintf($content, $args[0]);
				}
			else
				$content = sprintf($content, $args);
		}
		return $content;
	}

}

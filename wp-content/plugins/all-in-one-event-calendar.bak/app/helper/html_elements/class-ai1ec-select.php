<?php
/**
 *
 * @author Timely Network Inc
 *
 * This class is responsible for rendering an HTML select element.
 */

class Ai1ec_Select extends Ai1ec_Html_Element {
	/**
	 * @var string
	 */
	private $value;
	/**
	 * @var array
	 */
	private $options = array();

	public function __construct( $id ) {
		parent::__construct();
		$this->id = $id;
	}

	/**
	 * @param string $value
	 */
	public function set_value( $value ) {
		$this->value = $value;
	}

	/**
	 *
	 * @param string $text
	 * @param string $value
	 */
	public function add_option( $text, $value = null ) {
		if( null === $value ) {
			$value = $text;
		}
		$this->options[$value] = $text;
	}

	/**
	 *
	 * @see Ai1ec_Renderable::render()
	 *
	 */
	public function render() {
		$options = $this->render_options();
		echo <<<HTML
		<select id="{$this->id}" name="{$this->id}">
			$options
		</select>
HTML;
	}

	/**
	 *
	 * @return string
	 */
	private function render_options() {
		if( empty( $this->options ) ) {
			return '';
		}
		$html = '';
		foreach( $this->options as $value => $text ) {
			$selected = $this->value === $value ? 'selected' : '';
			$value = $this->template_adapter->escape_attribute( $value );
			$html .= "<option $selected value='$value'>$text</option>\n";
		}
		return $html;
	}
}

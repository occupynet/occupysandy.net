<?php

/**
 *
 * @author Timely Network Inc
 *
 * This class is repsonsible for rendering a Bootstrap-colorpicker component.
 */

class Ai1ec_Bootstrap_Colorpicker extends Ai1ec_Html_Element {

	/**
	 * @var string
	 */
	private $value;

	/**
	 * @var string
	 */
	private $label;
	/**
	 * @var string
	 */
	private $format = 'hex';
	/**
	 * @var boolean
	 */
	private $readonly = false;

	/**
	 * @param boolean $readonly
	 */
	public function set_readonly( $readonly ) {
		$this->readonly = $readonly;
	}

	/**
	 * @param string $format
	 */
	public function set_format( $format ) {
		$this->format = $format;
	}

	/**
	 * @param string $label
	 */
	public function set_label( $label ) {
		$this->label = $label;
	}

	public function __construct( $color, $id ) {
		// Call the parent to set the template adapter.
		parent::__construct();
		$this->value = $color;
		$this->id = $id;
	}

	/**
	 *
	 * @see Ai1ec_Renderable::render()
	 *
	 */
	public function render() {
		$label = $this->label;
		$id    = $this->template_adapter->escape_attribute( $this->id );
		$value = $this->template_adapter->escape_attribute( $this->value );
		$label = isset( $this->label ) ?
		         "<label class='control-label' for='$id'>$label</label>" :
		         '';
		$readonly = $this->readonly === true ? 'readonly' : '';
		echo <<<HTML
<div class="control-group">
	$label
	<div class="controls">
		<div class="input-append color colorpickers" data-color="$value" data-color-format="{$this->format}">
			<input type="text" id="$id" name="$id" class="span2" $readonly value="$value"
				/><span class="add-on"><i style="background-color: $value"></i></span>
		</div>
	</div>
</div>
HTML;
	}
}

<?php
/**
 *
 * @author Timely Network Inc
 *
 * This class represent a LESS variable of type font.
 */

class Ai1ec_Less_Variable_Font extends Ai1ec_Less_Variable {
	/**
	 *
	 * @var array
	 */
	private $fonts = array(
		'Arial'               => 'Arial, Helvetica, sans-serif',
		'Arial Black'         => '"Arial Black", Gadget, sans-serif',
		'Comic Sans MS'       => '"Comic Sans MS", cursive',
		'Courier New'         => '"Courier New", monospace',
		'Font Awesome'        => 'FontAwesome',
		'Georgia'             => 'Georgia, Georgia, serif',
		'Helvetica Neue'      => '"Helvetica Neue", Helvetica, Arial, sans-serif',
		'Impact'              => 'Impact, Charcoal, sans-serif',
		'League Gothic'       => '"League Gothic", Impact, "Arial Black", Arial, sans-serif',
		'Lucida Console'      => '"Lucida Console", Monaco, monospace',
		'Lucida Sans Unicode' => '"Lucida Sans Unicode", Lucida Grande, sans-serif',
		'MS Sans Serif'       => '"MS Sans Serif", Geneva, sans-serif',
		'MS Serif'            => '"MS Serif", "New York", serif',
		'Palatino'            => '"Palatino Linotype", "Book Antiqua", Palatino, serif',
		'Tahoma'              => 'Tahoma, Geneva, sans-serif',
		'Times New Roman'     => '"Times New Roman", Times, serif',
		'Trebuchet Ms'        => '"Trebuchet MS", "Lucida Grande", sans-serif',
		'Verdana'             => 'Verdana, Geneva, sans-serif',
	);

	/**
	 * (non-PHPdoc)
	 * add the fonts
	 * @see Ai1ec_Less_Variable::set_up_renderable()
	 */
	public function set_up_renderable( Ai1ec_Renderable $renderable ) {
		foreach( $this->fonts as $text => $key ) {
			$renderable->add_option( $text, $key );
		}
		$renderable->set_value( $this->value );
		return $renderable;
	}

	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Less_Variable::render()
	 */
	public function render() {
		$label = $this->description;
		$id = $this->template_adapter->escape_attribute( $this->id );
		echo <<<HTML
<div class="control-group">
	<label class="control-label" for="$id">$label</label>
	<div class="controls">
HTML;
		$this->renderable->render();
		echo '</div></div>';
	}
}

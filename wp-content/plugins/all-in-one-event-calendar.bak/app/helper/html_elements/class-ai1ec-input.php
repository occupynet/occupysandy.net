<?php
/**
 *
 * @author Timely Network Inc
 *
 * This class is responsible for rendering an input field.
 */

class Ai1ec_Input extends Ai1ec_Html_Element {
	/**
	 * @var string
	 */
	private $value = '';
	/**
	 * @var string
	 */
	private $type = 'text';
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @param string $type
	 */
	public function set_type( $type ) {
		$this->type = $type;
	}

	/**
	 * @param string $name
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * @param string $value
	 */
	public function set_value( $value ) {
		$this->value = $value;
	}

	/**
	 *
	 * @see Ai1ec_Renderable::render()
	 *
	 */
	public function render() {
		$class = $this->create_class_markup();
		$id    = $this->create_attribute_markup( 'id', $this->id );
		$value = $this->create_attribute_markup( 'value' , $this->value );
		$type  = $this->create_attribute_markup( 'type' , $this->type );
		$name  = $this->create_attribute_markup( 'name' , $this->name );
		echo "<input $type $value $id $class $name />";
	}
}

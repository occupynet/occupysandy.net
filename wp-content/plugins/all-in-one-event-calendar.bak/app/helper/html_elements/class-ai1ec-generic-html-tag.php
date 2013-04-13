<?php

/**
 * @author Timely Network Inc
 *
 * This class is responsible for rendering a generic HTML tag.
 */
class Ai1ec_Generic_Html_Tag extends Ai1ec_Html_Element_Can_Have_Children {

	/**
	 * @var string the tag type
	 */
	private $type;

	/**
	 * @var string
	 */
	private $text;

	/**
	 * @var array
	 */
	private $attributes = array();
	
	/**
	 * @var boolean
	 */
	private $prepend_text = true;

	/**
	 * @param boolean $prepend_text
	 */
	public function set_prepend_text( $prepend_text ) {
		$this->prepend_text = $prepend_text;
	}

	/**
	 * @param string $text
	 */
	public function set_text( $text ) {
		$this->text = $text;
	}

	/**
	 * @param string $type
	 */
	public function set_type( $type ) {
		$this->type = $type;
	}

	/**
	 * Adds the passed attribute name & value to the link's attributes.
	 *
	 * @param string       $name
	 * @param string|array $value
	 */
	public function set_attribute( $name, $value ) {
		$value = ( array ) $value;
		// Let's check if we have a value
		if( isset( $this->attributes[$name] ) ) {
			// Let's check if it's an array
			$this->attributes[$name] = array_unique(
				array_merge( $this->attributes[$name], $value )
			);
		} else {
			$this->attributes[$name] = $value;
		}
	}

	/**
	 * Adds the given name="value"-formatted attribute expression to the link's
	 * set of attributes.
	 *
	 * @param string $expr Attribute name-value pair in name="value" format
	 */
	public function set_attribute_expr( $expr ) {
		preg_match( '/^([\w\-_]+)=[\'"]([^\'"]*)[\'"]$/', $expr, $matches );
		$name = $matches[1];
		$value = $matches[2];
		$this->set_attribute( $name, $value );
	}

	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Renderable::render()
	 */
	public function render() {
		$class = $this->create_class_markup();
		$id = $this->create_attribute_markup( 'id', $this->id );
		$attributes = $this->render_attributes_markup();
		echo "<{$this->type} $class $id $attributes>";
		echo $this->text;
		foreach( $this->container->renderables as $renderable ) {
			$renderable->render();
		}
		echo "</{$this->type}>";
	}

	/**
	 * Renders the markup for the attributes of the tag
	 *
	 * @return string
	 */
	private function render_attributes_markup() {
		$html = array();
		foreach( $this->attributes as $name => $values ) {
			$values = $this->template_adapter->escape_attribute(
				implode( ' ', $values )
			);
			$html[] = "$name='$values'";
		}
		return implode( ' ', $html );
	}
	public function __construct( $type ) {
		parent::__construct();
		$this->type = $type;
	}
}

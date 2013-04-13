<?php

/**
 * @author Timely Network Inc
 */

abstract class Ai1ec_Less_Variable extends Ai1ec_Html_Element {
	/**
	 * @var string
	 */
	protected $id;
	/**
	 * @var string
	 */
	protected $description;
	/**
	 * @var string
	 */
	protected $value;
	/**
	 * @var Ai1ec_Renderable
	 */
	protected $renderable;

	/**
	 * Take a renderbale and configures it. After that it returns the renderable.
	 * This is used to configure the renderable according to the less variable type.
	 *
	 * @param Ai1ec_Renderable $renderable
	 * @return Ai1ec_Renderable
	 */
	abstract public function set_up_renderable( Ai1ec_Renderable $renderable );

	public function __construct( array $params, Ai1ec_Renderable $renderable ) {
		parent::__construct();
		$this->id          = $params['id'];
		$this->description = $params['description'];
		$this->value       = $params['value'];
		// Customize the element to suit the needs of the variable
		$renderable = $this->set_up_renderable( $renderable );
		$this->renderable  = $renderable;
	}

	/* (non-PHPdoc)
	 * @see Ai1ec_Renderable::render()
	 */
	public function render() {
		$this->renderable->render();
	}
}

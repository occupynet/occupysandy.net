<?php
/**
 *
 * @author Timely Network Inc
 *
 */
abstract class Ai1ec_Html_Element_Can_Have_Children extends Ai1ec_Html_Element implements Ai1ec_Container {

	/**
	 *
	 * @var Ai1ec_Base_Container
	 */
	protected $container;

	public function __construct() {
		parent::__construct();
		$this->container  = new Ai1ec_Base_Container();
	}

	/**
	 * Adds a renderable child to the element
	 *
	 * @param Ai1ec_Renderable $renderable
	 */
	public function add_renderable_children( Ai1ec_Renderable $renderable ) {
		$this->container->add_renderable_children( $renderable );
	}

}

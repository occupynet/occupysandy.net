<?php

/**
* This is the abstract class which parents of the composite must extend
*/

class Ai1ec_Base_Container implements Ai1ec_Container {
	/**
	 *
	 * @var array
	 */
	public $renderables = array();


	public function add_renderable_children( Ai1ec_Renderable $renderable ) {
		$this->renderables[] = $renderable;
	}
}
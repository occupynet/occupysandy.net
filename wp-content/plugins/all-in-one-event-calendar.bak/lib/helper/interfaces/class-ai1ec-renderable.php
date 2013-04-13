<?php

/**
* This is the interface around wich i build a composit pattern to represent html
*/

interface Ai1ec_Renderable {
	/**
	 * This is the main function, it just renders the method for the element,
	 * taking care of childrens ( if any )
	 */
	public function render();
}
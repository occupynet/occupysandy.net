<?php
/**
 * 
 * @author nicola
 *
 * This interface is implemented by those classes which are containers
 */
interface Ai1ec_Container {
	
	/**
	 * Adds a renderable child to the element
	 *
	 * @param Ai1ec_Renderable $renderable
	 */
	public function add_renderable_children( Ai1ec_Renderable $renderable );
}
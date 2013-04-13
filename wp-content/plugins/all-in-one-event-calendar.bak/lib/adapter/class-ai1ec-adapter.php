<?php

/**
 * Base adapter class-factory
 *
 * Class is used to create adapter instance.
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2012.07.20
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Lib.Adapter
 */
class Ai1ec_Adapter
{

	/**
	 * Get instance of query manager class
	 *
	 * @return Ai1ec_Adapter_Query_Interface
	 */
	static public function query_manager() {
		return Ai1ec_Adapters_Factory::create_query_adapter_instance();
	}

}
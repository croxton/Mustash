<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stash_structure_pi
 * Mustash cache-breaking plugin
 *
 * @package		Mustash
 * @author  	Mark Croxton
 */

class Stash_structure_pi extends Mustash_plugin {

	/**
	 * Name
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $name = 'Structure';

	/**
	 * Version
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $version = '2.0.0';

	/**
	 * Extension hook priority
	 *
	 * @var 	integer
	 * @access 	public
	 */
	public $priority = '10';

	/**
	 * Required modules
	 *
	 * @var 	integer
	 * @access 	protected
	 */
	protected $dependencies = array('Structure');

	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		// add hook
		$this->register_hook('structure_reorder_end');
	}

	/**
	 * Set groups for this object
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function set_groups()
	{
		return array();
	}

	/*
	================================================================
	Hooks
	================================================================
	*/

	/**
	 * Hook: structure_reorder_end
	 *
	 * @access	public
	 * @param	array
	 * @param	array
	 * @return	void
	 */
	public function structure_reorder_end($data, $site_pages)
	{		
		$this->flush_cache(__FUNCTION__);
	}
}

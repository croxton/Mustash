<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stash_navee_pi
 * Mustash cache-breaking plugin
 *
 * @package		Mustash
 * @author  	Mark Croxton
 */

class Stash_navee_pi extends Mustash_plugin {

	/**
	 * Name
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $name = 'Navee';

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
	protected $dependencies = array('Navee');

	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		// add hook
		$this->register_hook('navee_clear_cache');
	}

	/**
	 * Set groups for this object
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function set_groups()
	{
		return stash_array_column($this->_get_navs(), 'nav_name', 'navigation_id');
	}

	/*
	================================================================
	Hooks
	================================================================
	*/

	/**
	 * Hook: navee_clear_cache
	 *
	 * @access	public
	 * @param	object $forum the discussion forum object instance
	 * @param	array 
	 * @return	void
	 */
	public function navee_clear_cache($data)
	{	
		if ( $data['site_id'] === $this->site_id)
		{
			$nav_id = isset($data['navigation_id']) ? $data['navigation_id'] : FALSE;
			$this->flush_cache(__FUNCTION__, $nav_id);
		}
	}

	/*
	================================================================
	Model
	================================================================
	*/

	/**
	 * Get a list of Navee menus
	 *
	 * @access	private
	 * @return	string
	 */
	private function _get_navs()
	{
		$result = ee()->db->select('navigation_id, nav_name')
							   ->from('navee_navs')
							   ->where('site_id', $this->site_id)
							   ->order_by('nav_name ASC')
							   ->get();

		if ($result->num_rows() == 0) 
		{
			return FALSE;
		}
		return $result->result_array();
	}

}

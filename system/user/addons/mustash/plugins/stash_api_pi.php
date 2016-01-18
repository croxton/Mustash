<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stash_api_pi
 * Mustash cache-breaking plugin
 *
 * @package		Mustash
 * @author  	Mark Croxton
 */

class Stash_api_pi extends Mustash_plugin {

	/**
	 * Name
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $name = 'API';

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
	protected $dependencies = array('Stash');

	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		// get custom hooks from settings
		$settings = ee()->mustash_lib->get_settings();

		if ($settings['api_hooks'])
		{
			$api_hooks = explode(',', $settings['api_hooks']);

			foreach($api_hooks as $hook) 
			{
				$this->register_hook($hook);
			}
		}
	}

	/**
	 * Flush the cache for a given hook
	 *
	 * @param  string
	 * @return boolean
	 */
	public function run($api_hook)
	{
		foreach ($this->hooks as $hook) 
		{
			if ($api_hook == $hook->name)
			{
				return $this->flush_cache($hook->name);
			}
		}

		return FALSE;
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

	/**
	 * Override parent::install, we don't want to install our custom hooks
	 * 
	 * @return void
	 */
	public function install()
	{
	}

	/**
	 * Override parent::uninstall
	 * 
	 * @return void
	 */
	public function uninstall()
	{	
	}
	
}

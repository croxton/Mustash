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
	public $version = '1.0.0';

	/**
	 * Extension hook priority
	 *
	 * @var 	integer
	 * @access 	public
	 */
	public $priority = '10';

	/**
	 * Extension hooks and associated markers
	 *
	 * @var 	array
	 * @access 	protected
	 */
	protected $hooks = array();

	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		// get custom hooks from settings
		$settings = $this->EE->mustash_lib->get_settings();

		if ($settings['api_hooks'])
		{
			$this->hooks = explode(',', $settings['api_hooks']);
		}
	}

	/**
	 * Flush the cache for a given hook
	 * 
	 * @return boolean
	 */
	public function run($hook)
	{
		if (in_array($hook, $this->hooks))
		{
			return $this->flush_cache($hook);
		}
		return FALSE;
	}

	/**
	 * Get groups for this object
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_groups()
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

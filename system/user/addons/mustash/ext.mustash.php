<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include base class
if ( ! class_exists('Mustash_base'))
{
	require_once(PATH_THIRD . 'mustash/base.mustash.php');
}

 /**
 * Mustash extension class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2015, hallmarkdesign
 * @link		https://github.com/croxton/Stash/wiki/Mustash
 * @since		1.0
 * @filesource 	./system/user/addons/mustash/ext.mustash.php
 */
class Mustash_ext extends Mustash_base {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------


	/**
	 * Settings
	 *
	 * @var        array
	 * @access     public
	 */
	public $settings = array();

	/**
	 * Plugin hooks
	 *
	 * @var        array
	 * @access     private
	 */
	private static $plugin_hooks = array();
		
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = array())
	{
		// Call base constructor
		parent::__construct();

		// required extension properties
		$this->name				= $this->mod_name;
		$this->version			= $this->mod_version;
		$this->description		= $this->mod_description;
		$this->docs_url			= $this->mod_docs_url;
		$this->settings_exist	= 'y';

		// populate static plugin hooks array on first instantiation of this class
		if ( empty(self::$plugin_hooks))
		{
			$query = ee()->db->from('extensions')
								  ->where('class', __CLASS__)
								  ->where('enabled', 'y')
								  ->order_by('priority', 'asc')
								  ->get();

			if ($query->num_rows() > 0)
			{
				$result = $query->result_array();

				foreach($result as $row)
				{
					self::$plugin_hooks[$row['hook']][] = $row['method'];
				}
			}
		}
	}

	/**
	 * Call magic method
	 *
	 * @param string	 $name The method name being called
	 * @param array		 $arguments The method call arguments
	 */
	public function __call($name, $arguments)
	{
		if (strpos($name, ':') !== FALSE)
		{
			ee()->load->library('mustash_lib');

			// parse out the plugin method from the called extension
			$plugin = explode(':', $name);
			$plugin_method =  $plugin[1];

			// EE will only call the first instance of a hook for a given class,
			// but in some cases there can be more than one plugin using a hook.
			// Therefore we need to manually call all plugins that use the same hook.
			if (isset(self::$plugin_hooks[$plugin_method]))
			{
				foreach(self::$plugin_hooks[$plugin_method] as $p)
				{
					$plugin = explode(':', $p);
					$plugin_class = "stash_" . $plugin[0] . "_pi";

					// load and instantiate the plugin
					$plugin_instance = ee()->mustash_lib->plugin($plugin_class);	

					// invoke the plugin method, using Reflection to preserve $this
					$method = new ReflectionMethod($plugin_class, $plugin_method);
					return $method->invokeArgs($plugin_instance, $arguments);
				}
			}
		}
	}

	// ------------------------------------------------------

	/**
	 * Activate Extension
	 * 
	 * @return void
	 */
	public function activate_extension()
	{
		
	}

	// ------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * @return void
	 */
	public function disable_extension()
	{
		ee()->db->where('class', __CLASS__);
		ee()->db->delete('extensions');
	}
	
	// ------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * @param 	string	String value of current version
	 * @return 	mixed	void on update / FALSE if none
	 */
	public function update_extension($current = '')
	{
		if ($current == '' OR (version_compare($current, $this->mod_version) === 0))
		{
			return FALSE; // up to date
		}

		// update table row with current version
		ee()->db->where('class', __CLASS__);
		ee()->db->update('extensions', array('version' => $this->mod_version));
	}
	
}
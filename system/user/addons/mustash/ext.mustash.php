<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . 'mustash/config.php';

 /**
 * Mustash extension class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2014, hallmarkdesign
 * @link		http://hallmark-design.co.uk/code/mustash
 * @since		1.0
 * @filesource 	./system/user/addons/mustash/ext.mustash.php
 */
class Mustash_ext 
{
	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------
 	
	public $name			= MUSTASH_NAME;
	public $class_name 		= MUSTASH_CLASS_NAME;
	public $version			= MUSTASH_VERSION;
	public $description		= MUSTASH_DESC;
	public $docs_url		= MUSTASH_DOCS_URL;
	public $mod_name		= MUSTASH_MOD_URL;

	/**
	 * Settings
	 *
	 * @var        array
	 * @access     public
	 */
	public $settings = array();

	/**
	 * Do settings exist?
	 *
	 * @var        string	y|n
	 * @access     public
	 */
	public $settings_exist	= 'y';

	/**
	 * Extension hooks
	 *
	 * @var        array
	 * @access     private
	 */
	private $hooks = array('cp_menu_array');

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
		$this->ext_class_name = MUSTASH_CLASS_NAME . '_ext';
		#ee()->load->library('mustash_lib');
		ee()->lang->loadfile('mustash');
		$this->query_base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->mod_name.AMP.'method=';

		// populate static plugin hooks array on first instantiation of this class
		if ( empty(self::$plugin_hooks))
		{
			$query = ee()->db->from('extensions')
								  ->where('class', $this->ext_class_name)
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
		foreach ($this->hooks AS $hook)
		{
			$this->_add_hook($hook);
		}
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
	 * Add extension hook
	 *
	 * @access     private
	 * @param      string
	 * @return     void
	 */
	private function _add_hook($name)
	{
		ee()->db->insert('extensions',
			array(
				'class'    => __CLASS__,
				'method'   => $name,
				'hook'     => $name,
				'settings' => '',
				'priority' => 10,
				'version'  => $this->version,
				'enabled'  => 'y'
			)
		);
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
		if ($current == '' OR (version_compare($current, $this->version) === 0))
		{
			return FALSE; // up to date
		}

		// update table row with current version
		ee()->db->where('class', __CLASS__);
		ee()->db->update('extensions', array('version' => $this->version));
	}
	

	// ------------------------------------------------------
	
	/**
	 * Seetings form
	 *
	 * Redirect to Control Panel module settings method
	 *
	 * @access     public
	 * @param      array
	 * @return     array
	 */
	public function settings_form()
	{
		ee()->functions->redirect(BASE.AMP.$this->query_base.'settings');
	}

	// ------------------------------------------------------
	
	/**
	 * Method for cp_menu_array hook
	 *
	 * Add a Stash menu to the main menu bar
	 *
	 * @access     public
	 * @param      array
	 * @return     array
	 */
	public function cp_menu_array($menu)
	{
        // get the latest version of $menu if it's been altered by other extensions on this hook
        if (isset(ee()->extensions->last_call) && ee()->extensions->last_call)
        {
            $menu = ee()->extensions->last_call;
        } 

		// let's see if the logged in user has permission to access the Mustash module
		$pass = FALSE;

		if (ee()->session->userdata('group_id') == 1) 
		{
			$pass = TRUE; // Superadmin
		} 
		else
		{
			if ($allowed_modules = array_keys(ee()->session->userdata('assigned_modules')))
			{
				$query = ee()->db->select('module_name')
							 		  ->where_in('module_id', $allowed_modules)
							 		  ->get('modules');

				if ($query->num_rows() > 0)
				{
					foreach ($query->result_array() as $row)
					{
						if ($row['module_name'] == $this->class_name)
						{
							$pass = TRUE;
							break;
						}
					}
				}
			}
		}

		if ($pass)
		{
			ee()->load->library('mustash_lib');

			$mcp_uri = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=mustash'.AMP.'method=';

			$stash_menu = array(
				'stash_variables' => $mcp_uri.'variables'
			);

			$areas = array('bundles', 'rules', 'settings');

			// only show those areas the member group has been granted access to
			foreach ($areas as $area)
			{
				if ( ee()->mustash_lib->can_access($area))
				{
					$stash_menu += array(
						'stash_'.$area => $mcp_uri.$area
					);
				}
			}

			$menu += array(
				'stash_menu' => $stash_menu
			);
		}

		return $menu;
	}

}
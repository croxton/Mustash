<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . 'mustash/config.php';

 /**
 * Mustash extension class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2013, hallmarkdesign
 * @link		http://hallmark-design.co.uk/code/mustash
 * @since		1.0
 * @filesource 	./system/expressionengine/third_party/mustash/ext.mustash.php
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
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = array())
	{
		$this->EE =& get_instance();

		$this->ext_class_name = MUSTASH_CLASS_NAME . '_ext';
		#$this->EE->load->library('mustash_lib');
		$this->EE->lang->loadfile('mustash');
		$this->query_base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->mod_name.AMP.'method=';
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
			$this->EE->load->library('mustash_lib');

			// parse out the plugin class and method
			$plugin = explode(':', $name);
			$plugin_class = "stash_" . $plugin[0] . "_pi";
			$plugin_method =  $plugin[1];

			// load and instantiate the plugin
			$plugin_instance = $this->EE->mustash_lib->plugin($plugin_class);	

			// invoke the plugin method, using Reflection to preserve $this
			$method = new ReflectionMethod($plugin_class, $plugin_method);
			return $method->invokeArgs($plugin_instance, $arguments);
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
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
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
		$this->EE->db->insert('extensions',
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
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update('extensions', array('version' => $this->version));
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
		$this->EE->functions->redirect(BASE.AMP.$this->query_base.'settings');
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
		// let's see if the logged in user has permission to access the Mustash module
		$pass = FALSE;

		if ($this->EE->session->userdata('group_id') == 1) 
		{
			$pass = TRUE; // Superadmin
		} 
		else
		{
			if ($allowed_modules = array_keys($this->EE->session->userdata('assigned_modules')))
			{
				$query = $this->EE->db->select('module_name')
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
			$this->EE->load->library('mustash_lib');

			$mcp_uri = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=mustash'.AMP.'method=';

			$stash_menu = array(
				'stash_variables' => $mcp_uri.'variables'
			);

			$areas = array('bundles', 'rules', 'settings');

			// only show those areas the member group has been granted access to
			foreach ($areas as $area)
			{
				if ( $this->EE->mustash_lib->can_access($area))
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
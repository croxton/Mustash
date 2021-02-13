<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// inlcude plugin class
if ( ! class_exists('Mustash_plugin'))
{
	require_once PATH_THIRD . 'mustash/libraries/Mustash_plugin.php';
}

// include base class
if ( ! class_exists('Mustash_base'))
{
	require_once(PATH_THIRD . 'mustash/base.mustash.php');
}

/**
 * Mustash Library
 *
 * Contains generic methods for Mustash CP interface
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2015, hallmarkdesign
 * @link		https://github.com/croxton/Stash/wiki/Mustash
 * @since		1.0
 * @filesource 	./system/user/addons/mustash/Mustash_lib.php
 */
class Mustash_lib extends Mustash_base {

	/**
	 * plugins
	 * @var array
	 */
	private static $plugins = array();

	public function __construct()
	{
		parent::__construct();

		ee()->load->model('mustash_settings_model', 'mustash_settings');
		ee()->load->model('mustash_model');
		$this->settings = $this->get_settings();
	}
	
	public function get_settings()
	{
		if (!isset(ee()->session->cache['mustash']['settings'])) 
		{	
			ee()->session->cache['mustash']['settings'] = ee()->mustash_settings->get_settings();
		}
		return ee()->session->cache['mustash']['settings'];
	}

	public function update_settings(array $data)
	{
		// validate settings fields and update the model
		if (ee()->mustash_settings->update_settings($data))
		{
			// uninstall all existing plugins
			$plugins = $this->get_all_plugins();
			
			foreach($plugins as $p)
			{
				$this->plugin($p)->uninstall();
			}

			// (re)install any that have been selected
			if ( isset($data['enabled_plugins']) && is_array($data['enabled_plugins']) )
			{
				foreach($data['enabled_plugins'] as $p)
				{
					$this->plugin($p)->install();
				}
			}

			return TRUE;
		}
	}

	// --------------------------------------------------------------------
	
	/**
	 * load a "plugin"
	 *
	 * @access	public
	 * @param	string
	 * @return	object
	 */	
	public function plugin($plugin) 
	{
    	if( ! isset(self::$plugins[$plugin]))
		{	
			// load and instantiate the plugin if it doesn't exist already
			require_once PATH_THIRD. $this->package . '/plugins/' . $plugin .'.php';
			self::$plugins[$plugin] = new $plugin;
		}
		// check that we have an object that extends Mustash_plugin
		if(self::$plugins[$plugin] instanceof Mustash_plugin)
		{
			return self::$plugins[$plugin];
		}
		else
		{
			throw new RuntimeException('Plugins must extend Mustash_plugin');
		}
  	}

	/**
	 * Gets all plugins in the plugins directory
	 * @return array
	 */
  	public function get_all_plugins()
  	{
  		ee()->load->helper('directory');
		$plugins = directory_map(PATH_THIRD . $this->package . '/plugins', 1);

		# PHP 5.3+ only
		#$plugins = preg_filter('/^(stash_[a-zA-Z0-9_-]+_pi)'.EXT.'$/i', '$1', $plugins);
		$result = preg_replace('/^(stash_[a-zA-Z0-9_-]+_pi)\.php$/i', '$1', $plugins);
		$plugins = array_diff($result, $plugins);

		return $plugins;
  	}


  	/**
	 * Gets an array of installed plugin objects
	 * @return array
	 */
  	public function get_installed_plugins()
  	{
  		$plugins = array();

  		foreach($this->settings['enabled_plugins'] as $p)
  		{
  			$plugins[] = $this->plugin($p);
  		}

  		return $plugins;
  	}


	/**
	 * Wrapper that runs all the tests to ensure system stability
	 * @return array;
	 */
	public function error_check()
	{
		$errors = array();
		if($this->settings['license_number'] == '')
		{
			$errors['license_number'] = 'missing_license_number';
		}
		return $errors;
	}
	

	public function scope_select_options($label='', $label_value='')
	{
		$list = array();

		if ( ! empty($label) && ! empty($label_value) )
		{
			$list = array($label_value => lang($label));
		}

		$list += array(
	   		'site' => lang('var_scope_global'),
	   		'user' 	=> lang('var_scope_user')
		);	
			
		return $list;		
	}

	public function bundle_select_options($label='', $label_value='')
	{
		$list = array();

		if ( ! empty($label) && ! empty($label_value) )
		{
			$list = array($label_value => lang($label));
		}

		if ($bundles = ee()->mustash_model->list_bundles())
		{
			$list += $bundles;
		}

		return $list;
	}
	
	public function is_installed_module($module_name)
	{
		$data = ee()->db->select('module_name')->from('modules')->like('module_name', $module_name)->get();
		if($data->num_rows == '1')
		{
			return TRUE;
		}
	}

	/**
	 * Load extra JS / CSS
	 *
	 * @access     public
	 * @param      array
	 * @return     void
	 */
	public function load_assets($assets)
	{
		foreach ($assets AS $file)
		{
			if (substr($file, -3) == 'css')
			{
				ee()->cp->load_package_css(substr($file, 0, -4));
			}
			elseif (substr($file, -2) == 'js')
			{
				ee()->cp->load_package_js(substr($file, 0, -3));
			}
		}
	}

	/**
	 * Prune cache - called by cronbtab
	 *
	 * @access     public
	 * @return     boolean
	 */
	public function prune() 
	{
		return ee()->mustash_model->prune_keys();
	}

	/**
	 * Check that the logged in user has permission to access 
	 * a particular area of the control panel interface
	 *
	 * @access     public
	 * @param      string
	 * @return     void
	 */
	public function can_access($area) 
	{
		if ( isset($this->settings['can_manage_'.$area]))
		{
			if (ee()->session->userdata['group_id'] == 1) 
			{
				// superadmins get a free pass...
				return TRUE;
			}
			else
			{
				return in_array(ee()->session->userdata['group_id'], $this->settings['can_manage_'.$area]);
			}
		}
		return FALSE;
	}

	// ------------------------------------------------------
	//  Wrappers for CRUD model methods
	// ------------------------------------------------------
	
	public function get_variables($where=array(), $perpage=20, $offset=0, $order=NULL)
	{
		return ee()->mustash_model->get_variables($where, $perpage, $offset, $order);
	}

	public function get_total_variables($where=array(), $perpage=20, $offset=0, $order=NULL)
	{
		return ee()->mustash_model->get_total_variables($where, $perpage, $offset, $order);
	}

	public function get_bundles($perpage=20, $offset=0, $order=NULL)
	{
		return ee()->mustash_model->get_bundles($perpage, $offset, $order);
	}

	public function get_total_bundles($perpage=20, $offset=0, $order=NULL)
	{
		return ee()->mustash_model->get_total_bundles($perpage, $offset, $order);
	}

	public function get_variable($id)
	{
		return ee()->mustash_model->get_variable($id);
	}

	public function update_variable($id, $parameters=NULL)
	{
		return ee()->mustash_model->update_variable($id, $parameters);
	}

	public function clear_variables($ids)
	{
		return ee()->mustash_model->clear_variables($ids);
	}

	public function clear_matching_variables($bundle_id = FALSE, $scope = NULL, $regex = NULL, $invalidate = 0)
	{
		return ee()->mustash_model->clear_matching_variables($bundle_id, $scope, $regex, $invalidate);
	}

	public function flush_cache($site_id = 1)
	{
		return ee()->mustash_model->flush_cache($site_id);
	}

	public function get_bundle($id)
	{
		return ee()->mustash_model->get_bundle($id);
	}

	public function add_bundle($data)
	{
		return ee()->mustash_model->add_bundle($data);
	}

	public function update_bundle($id, $data)
	{
		return ee()->mustash_model->update_bundle($id, $data);
	}

	public function delete_bundles($ids)
	{
		return ee()->mustash_model->delete_bundles($ids);
	}

	public function is_bundle_name_unique($bundle)
	{
		return ee()->mustash_model->is_bundle_name_unique($bundle);
	}

	public function get_rules()
	{
		return ee()->mustash_model->get_rules();
	}

	public function update_rules($rules)
	{
		return ee()->mustash_model->update_rules($rules);
	}
}

/* End of file Mustash_lib.php */
/* Location: ./system/user/addons/mustash/libraries/Mustash_lib.php */

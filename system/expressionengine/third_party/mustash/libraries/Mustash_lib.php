<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Mustash Library
 *
 * Contains generic methods for Mustash CP interface
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2014, hallmarkdesign
 * @link		http://hallmark-design.co.uk/code/mustash
 * @since		1.0
 * @filesource 	./system/expressionengine/third_party/mustash/Mustash_lib.php
 */
class Mustash_lib
{
	/**
	 * Preceeds URLs 
	 * @var mixed
	 */
	private $url_base = FALSE;

	/**
	 * plugins
	 * @var array
	 */
	private static $plugins = array();

	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->model('mustash_settings_model', 'mustash_settings');
		$this->EE->load->model('mustash_model');
		$this->settings = $this->get_settings();

		// config
		$path = dirname(dirname(realpath(__FILE__)));
		include $path.'/config'.EXT;
		$this->name = $config['name'];
		$this->version = $config['version'];
		$this->mod_name = $config['mod_url_name'];
		$this->ext_class_name = $config['ext_class_name'];
	}
	
	public function get_settings()
	{
		if (!isset($this->EE->session->cache['mustash']['settings'])) 
		{	
			$this->EE->session->cache['mustash']['settings'] = $this->EE->mustash_settings->get_settings();
		}
		return $this->EE->session->cache['mustash']['settings'];
	}

	public function update_settings(array $data)
	{
		// validate settings fields and update the model
		if ($this->EE->mustash_settings->update_settings($data))
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
				// get hooks for each plugin
				$hooks = array();
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
			require_once PATH_THIRD. $this->mod_name . '/plugins/' . $plugin .EXT;
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
  		$this->EE->load->helper('directory');
		$plugins = directory_map(PATH_THIRD . $this->mod_name . '/plugins', 1);

		# PHP 5.3+ only
		#$plugins = preg_filter('/^(stash_[a-zA-Z0-9_-]+_pi)'.EXT.'$/i', '$1', $plugins);
		$result = preg_replace('/^(stash_[a-zA-Z0-9_-]+_pi)'.EXT.'$/i', '$1', $plugins);
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
	 * Sets up the right menu options
	 * @return multitype:string
	 */
	public function variables_right_menu()
	{
		$menu = array(
			'variables' => $this->url_base.'variables',
			'clear_cache' => $this->url_base.'clear_cache_confirm',
		);
		return $menu;
	}

	/**
	 * Sets up the right menu options
	 * @return multitype:string
	 */
	public function bundles_right_menu()
	{
		$menu = array(
			'bundles' => $this->url_base.'bundles',
			'add_bundle' => $this->url_base.'add_bundle',
		);
		return $menu;
	}

	/**
	 * Sets up the right menu options
	 * @return multitype:string
	 */
	public function settings_right_menu()
	{
		$menu = array(
			'stash_settings' => $this->url_base.'settings',
			'stash_rewrite_rules' => $this->url_base.'rewrite',
		);
		return $menu;
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
	
	/**
	 * Wrapper to handle CP URL creation
	 * @param string $method
	 */
	public function _create_url($method)
	{
		return $this->url_base.$method;
	}

	/**
	 * Creates the value for $url_base
	 * @param string $url_base
	 */
	public function set_url_base($url_base)
	{
		$this->url_base = $url_base;
	}
	
	public function perpage_select_options()
	{
		return array(
			   '10' => '10 '.lang('results'),
			   '25' => '25 '.lang('results'),
			   '75' => '75 '.lang('results'),
			   '100' => '100 '.lang('results'),
			   '150' => '150 '.lang('results')
		);		
	}

	public function scope_select_options($label='filter_by_scope', $label_value='')
	{
		return array(
			   $label_value => lang($label),
			   'site' => lang('var_scope_global'),
			   'user' 	=> lang('var_scope_user')
		);				
	}


	public function bundle_select_options($label='filter_by_bundle', $label_value='')
	{
		$list = array($label_value => lang($label));

		if ($bundles = $this->EE->mustash_model->list_bundles())
		{
			$list += $bundles;
		}
		return $list;
	}		
	
	public function create_pagination($method, $total, $per_page)
	{
		$config = array();
		$config['page_query_string'] = TRUE;
		$config['base_url'] = $this->url_base.$method;
		$config['total_rows'] = $total;
		$config['per_page'] = $per_page;
		$config['page_query_string'] = TRUE;
		$config['full_tag_open'] = '<p id="paginationLinks">';
		$config['full_tag_close'] = '</p>';
		$config['prev_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif" width="13" height="13" alt="&lt;" />';
		$config['next_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif" width="13" height="13" alt="&gt;" />';
		$config['first_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif" width="13" height="13" alt="&lt; &lt;" />';
		$config['last_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif" width="13" height="13" alt="&gt; &gt;" />';
		$config['query_string_segment'] = 'offset';

		$this->EE->pagination->initialize($config);
		return $this->EE->pagination->create_links();		
	}
	
	public function is_installed_module($module_name)
	{
		$data = $this->EE->db->select('module_name')->from('modules')->like('module_name', $module_name)->get();
		if($data->num_rows == '1')
		{
			return TRUE;
		}
	}


	/**
	 * Load assets: extra JS and CSS
	 *
	 * @access     public
	 * @return     void
	 */
	public function load_assets($assets=array())
	{
		// -------------------------------------
		//  Define placeholder
		// -------------------------------------

		$header = array();
		$footer = array();

		#$header[] = '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>';

		// -------------------------------------
		//  Loop through assets
		// -------------------------------------

		$asset_url = ((defined('URL_THIRD_THEMES'))
		           ? URL_THIRD_THEMES
		           : $this->EE->config->item('theme_folder_url') . 'third_party/')
		           . $this->mod_name . '/';

		foreach ($assets AS $file)
		{
			// location on server
			$file_url = $asset_url.$file.'?v='.$this->version;

			if (substr($file, -3) == 'css')
			{
				$header[] = '<link charset="utf-8" type="text/css" href="'.$file_url.'" rel="stylesheet" media="screen" />';
			}
			elseif (substr($file, -2) == 'js')
			{
				$footer[] = '<script charset="utf-8" type="text/javascript" src="'.$file_url.'"></script>';
			}
		}

		// -------------------------------------
		//  Add combined assets to header
		// -------------------------------------

		if ($header)
		{
			$this->EE->cp->add_to_head(
				NL."<!-- {$this->mod_name} assets -->".NL.
				implode(NL, $header).
				NL."<!-- / {$this->mod_name} assets -->".NL
			);
		}

		if ($footer)
		{
			$this->EE->cp->add_to_foot(
				NL."<!-- {$this->mod_name} assets -->".NL.
				implode(NL, $footer).
				NL."<!-- / {$this->mod_name} assets -->".NL
			);
		}
	}

	/**
	 * Prune cache every 15 seconds up to 4 times
	 * Designed to work with a cronbtab called every minute
	 *
	 * @access     public
	 * @return     boolean
	 */
	public function prune() 
	{
		// unlock file-based sessions to allow other requests to continue
		session_write_close();

		// make sure we have long enough to do this
		set_time_limit(60);

		$count = 3;
		while($count >= 0) {
			--$count;
			if ($this->EE->mustash_model->prune_keys())
			{
				sleep(15);
			}
			else
			{
				return FALSE;
			}
		}
		return TRUE;
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
			if ($this->EE->session->userdata['group_id'] == 1) 
			{
				// superadmins get a free pass...
				return TRUE;
			}
			else
			{
				return in_array($this->EE->session->userdata['group_id'], $this->settings['can_manage_'.$area]);
			}
		}
		return FALSE;
	}

	// ------------------------------------------------------
	//  Wrappers for CRUD model methods
	// ------------------------------------------------------
	
	public function get_variables($where=array(), $perpage=20, $offset=0, $order=NULL)
	{
		return $this->EE->mustash_model->get_variables($where, $perpage, $offset, $order);
	}

	public function get_total_variables($where=array(), $perpage=20, $offset=0, $order=NULL)
	{
		return $this->EE->mustash_model->get_total_variables($where, $perpage, $offset, $order);
	}

	public function get_bundles($perpage=20, $offset=0, $order=NULL)
	{
		return $this->EE->mustash_model->get_bundles($perpage, $offset, $order);
	}

	public function get_total_bundles($perpage=20, $offset=0, $order=NULL)
	{
		return $this->EE->mustash_model->get_total_bundles($perpage, $offset, $order);
	}

	public function get_variable($id)
	{
		return $this->EE->mustash_model->get_variable($id);
	}

	public function update_variable($id, $parameters=NULL)
	{
		return $this->EE->mustash_model->update_variable($id, $parameters);
	}

	public function clear_variables($ids)
	{
		return $this->EE->mustash_model->clear_variables($ids);
	}

	public function clear_matching_variables($bundle_id = FALSE, $scope = NULL, $regex = NULL, $invalidate = 0)
	{
		return $this->EE->mustash_model->clear_matching_variables($bundle_id, $scope, $regex, $invalidate);
	}

	public function get_bundle($id)
	{
		return $this->EE->mustash_model->get_bundle($id);
	}

	public function add_bundle($data)
	{
		return $this->EE->mustash_model->add_bundle($data);
	}

	public function update_bundle($id, $data)
	{
		return $this->EE->mustash_model->update_bundle($id, $data);
	}

	public function delete_bundle($id)
	{
		return $this->EE->mustash_model->delete_bundle($id);
	}

	public function is_bundle_name_unique($bundle)
	{
		return $this->EE->mustash_model->is_bundle_name_unique($bundle);
	}

	public function get_rules()
	{
		return $this->EE->mustash_model->get_rules();
	}

	public function update_rules($rules)
	{
		return $this->EE->mustash_model->update_rules($rules);
	}
}

// --------------------------------------------------------------------------

/**
 * Mustash_plugin Class
 *
 * Abstract class for plugins
 *
 * @package	Mustash
 */
abstract class Mustash_plugin {
	
	public $EE, $name, $short_name, $version, $priority;
	protected $hooks = array();
	protected $groups = array();
	protected $ext_class_name;
	protected $ext_version = MUSTASH_VERSION;
	protected $site_id;

	public function __construct() 
	{
		$this->EE = get_instance();
		$this->ext_class_name = MUSTASH_CLASS_NAME . '_ext';

		# PHP 5.3+ only
		#$this->short_name = preg_filter('/^Stash_([a-zA-Z0-9_-]+)_pi$/i', '$1', get_class($this));
		$this->short_name = preg_replace('/^Stash_([a-zA-Z0-9_-]+)_pi$/i', '$1', get_class($this));

		$this->site_id = $this->EE->config->item('site_id');
	}

	/**
	 * Activate plugin hooks
	 * 
	 * @return void
	 */
	public function install()
	{
		foreach ($this->hooks AS $hook => $markers)
		{
			if ( ! is_array($markers))
			{
				$hook = $markers;
			}
			if ( $hook !== '@all')
			{
				$this->add_hook($hook);
			}
		}
	}

	/**
	 * Remove plugin hooks
	 * 
	 * @return void
	 */
	public function uninstall()
	{
		foreach ($this->hooks AS $hook => $markers)
		{
			if ( ! is_array($markers))
			{
				$hook = $markers;
			}
			$this->remove_hook($hook);
		}
	}

	/**
	 * get the extension hooks this plugin implements
	 *
	 * @access     public
	 * @return     array
	 */
	public function get_hooks() 
	{
		return $this->hooks;
	}

	/**
	 * Add extension hook to the Mustash extension class
	 * Prefix the method with the plugin class short name, so we know how to find it
	 *
	 * @access     protected
	 * @param      string
	 * @return     void
	 */
	protected function add_hook($name)
	{
		$this->EE->db->insert('extensions',
			array(
				'class'    => $this->ext_class_name,
				'method'   => $this->short_name . ":" . $name,
				'hook'     => $name,
				'settings' => '',
				'priority' => $this->priority,
				'version'  => $this->version,
				'enabled'  => 'y'
			)
		);
	}

	/**
	 * remove extension hook from the Mustash extension class
	 *
	 * @access     protected
	 * @param      string
	 * @return     void
	 */
	protected function remove_hook($name)
	{
		$this->EE->db->delete('extensions',
			array(
				'class'    => $this->ext_class_name,
				'hook'     => $name
			)
		);
	}

	/**
	 * retrieve and parse rules for a given plugin / hook
	 *
	 * @access     protected
	 * @param      string
	 * @param      array
	 * @return     bool/array
	 */
	protected function get_rules($hook = NULL, $markers = array())
	{
		$this->EE->load->model('mustash_model');

		// automatically check for rules attached to an @all hook for this plugin
		if ( ! is_null($hook))
		{
			$hook = array($hook, '@all');
		}

		$rules = $this->EE->mustash_model->get_rules($this->short_name, $hook);

		if ( ! empty($rules))
		{
			// parse markers
			if ( ! empty($markers))
			{
				foreach($rules as &$rule)
				{
					if ( ! is_null($rule['pattern']))
					{
						$rule['pattern'] = $this->parse_markers($rule['pattern'], $markers);
					}
				}
			}

			return $rules;
		}

		return FALSE;
	}

	/**
	 * Run each cache-breaking rule, checking that the rule group matches the group being edited
	 *
	 * @access	protected
	 * @param	array
	 * @param	integer/bool
	 * @return	void
	 */
	protected function run_rules($rules, $group_id = FALSE)
	{
		// run rules
		foreach($rules as $r)
		{
			// is the rule limited to a specific group?
			if ( ! $r['group_id'] || ! $group_id || $r['group_id'] == $group_id)
			{
				$this->destroy($r['bundle_id'], $r['scope'], $r['site_id'], $r['pattern']);
			}
		}
	}

	/**
	 * Retrieve, parse and run a ruleset for a specific hook
	 *
	 * @access	protected
	 * @param	string
	 * @param	integer/bool
	 * @param	array
	 * @return	boolean
	 */
	protected function flush_cache($hook, $group_id=FALSE, $markers=array())
	{
		// @TODO: check this is a valid hook for this plugin?

		// get rules for this hook
		if ($rules = $this->get_rules($hook, $markers))
		{
			// flush cache
			$this->run_rules($rules, $group_id);

			return TRUE;
		}
		return FALSE;
	}

	/**
	 * parse markers in a rule pattern
	 *
	 * @param      string
	 * @param      array
	 * @access     protected
	 * @return     string
	 */
	protected function parse_markers($template, $markers)
	{
		foreach($markers as $key => $value)
		{
			$template = str_replace(LD.$key.RD, $value, $template);
		}
		return $template;
	}

	/**
	 * Delete one or multiple cached stash variables
	 *
	 * @access     protected
	 * @return     array
	 */
	protected function destroy($bundle_id = FALSE, $session_id=NULL, $site_id = NULL, $regex = NULL) 
	{
		$this->EE->load->add_package_path(PATH_THIRD.'stash/', TRUE);
		$this->EE->load->model('stash_model');

		// prep regex
		if ( ! is_null($regex))
		{	
			if ( ! preg_match('/^#(.*)#$/', $regex))
			{
				$regex = '^' . $regex . '$'; // match an exact key
			}
			else
			{
				$regex = trim($regex, '#');
			}
		}

		// add the current site id and pass througb to stash model
		return $this->EE->stash_model->delete_matching_keys(
			$bundle_id, 
			$session_id, 
			is_null($site_id) ? $this->EE->config->item('site_id') : $site_id, 
			$regex
		);
	}

	/**
	 * Get groups for the plugin
	 *
	 * @access     public
	 * @return     array
	 */
	public function get_groups()
	{
		if (empty($this->groups))
		{
			$this->groups = (array) $this->set_groups();
		}

		return $this->groups;
	}

	abstract protected function set_groups();
}

/* End of file Mustash_lib.php */
/* Location: ./system/expressionengine/third_party/mustash/libraries/Mustash_lib.php */

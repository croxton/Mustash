<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include base class
if ( ! class_exists('Mustash_base'))
{
	require_once(PATH_THIRD . 'mustash/base.mustash.php');
}

// include hook class
if ( ! class_exists('Mustash_hook'))
{
	require_once(PATH_THIRD . 'mustash/libraries/Mustash_hook.php');
}

/**
 * Mustash_plugin class
 *
 * Abstract class extended by Mustash plugins
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2015, hallmarkdesign
 * @link		https://github.com/croxton/Stash/wiki/Mustash
 * @since		1.0
 * @filesource 	./system/user/addons/mustash/libraries/Mustash_plugin.php
 */
abstract class Mustash_plugin extends Mustash_base {
	
	public $name, $short_name, $version, $priority;
	protected $hooks = array();
	protected $groups = array();
	protected $dependencies = array();
	protected $ext_class_name;
	protected $ext_version;

	public function __construct() 
	{
		parent::__construct();

		$this->ext_class_name = $this->mod_name . '_ext';

		# PHP 5.3+ only
		#$this->short_name = preg_filter('/^Stash_([a-zA-Z0-9_-]+)_pi$/i', '$1', get_class($this));
		$this->short_name = preg_replace('/^Stash_([a-zA-Z0-9_-]+)_pi$/i', '$1', get_class($this));
	}

	/**
	 * Activate plugin hooks
	 * 
	 * @return void
	 */
	public function install()
	{
		foreach ($this->hooks AS $hook)
		{
			if ( $hook->name !== '@all' && $hook->has_trigger())
			{
				$this->add_hook($hook->name);
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
		foreach ($this->hooks AS $hook)
		{
			if ( $hook->name !== '@all' && $hook->has_trigger())
			{
				$this->remove_hook($hook->name);
			}
		}
	}

	/**
	 * check that any first or third-party modules that this plugin relies on actually exist
	 *
	 * @access     public
	 * @return     bool
	 */
	public function dependencies_are_installed()
	{
		if ( empty($this->dependencies))
		{
			return TRUE;
		}

		$installed_modules = ee()->mustash_model->get_modules();

		foreach ($this->dependencies as $module) 
		{
			if ( ! in_array($module, $installed_modules))
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Register a Mustash_hook with this plugin
	 *
	 * @access     protected
	 * @param      string $name The hook name
	 * @param      array $markers An array of markers to parse in rules attached to the hook
	 * @param      boolean $visible Visible to the end-user who creates rules
	 * @param      boolean $trigger Can be triggered by editing events in EE
	 * @return     void
	 */
	protected function register_hook($name, $markers = array(), $visible = TRUE, $trigger = TRUE)
	{
		$this->hooks[] = new Mustash_hook($name, $markers, $visible, $trigger);
	}

	/**
	 * get an array of *visible* extension hooks this plugin implements
	 *
	 * @access     public
	 * @return     array
	 */
	public function get_hooks() 
	{
		$visible_hooks = array();

		foreach($this->hooks AS $hook) 
		{
			if ($hook->is_visible()) 
			{
				$visible_hooks[] = $hook;
			}
		}
		return $visible_hooks;
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
		ee()->db->insert('extensions',
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
		ee()->db->delete('extensions',
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
		ee()->load->model('mustash_model');

		// automatically check for rules attached to an @all hook for this plugin
		if ( ! is_null($hook))
		{
			$hook = array($hook, '@all');
		}

		$rules = ee()->mustash_model->get_rules($this->short_name, $hook);

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
		ee()->load->add_package_path(PATH_THIRD.'stash/', TRUE);
		ee()->load->model('stash_model');

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

		// stagger the invalidation of matching variables over a specific time period?
		$invalidation_period  = ee()->config->item('stash_invalidation_period') ? ee()->config->item('stash_invalidation_period') : 0; // seconds

		// add the current site id and pass througb to stash model
		return ee()->stash_model->delete_matching_keys(
			$bundle_id, 
			$session_id, 
			is_null($site_id) ? $this->site_id : $site_id, 
			$regex,
			$invalidation_period
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

/* End of file Mustash_plugin.php */
/* Location: ./system/user/addons/mustash/libraries/Mustash_plugin.php */
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Mustash - settings model
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2015, hallmarkdesign
 * @link		https://github.com/croxton/Stash/wiki/Mustash
 * @since		1.0
 * @filesource 	./system/user/addons/mustash/models/mustash_settings_model.php
 */
class Mustash_settings_model extends CI_Model {
	
	/**
	 * The table we're operating on
	 * 
	 * @var string $_table
	 */	
	private $_table = 'stash_settings';
	
	/**
	 * Default configuration
	 * 
	 * @var array $_defaults
	 */		
	public $_defaults = array(
		'license_number' => '',
		'list_limit' => '20',
		'date_format' => '%d %M %Y %H:%i',
		'can_manage_bundles' => array('1'),
		'can_manage_rules' => array('1'),
		'can_manage_settings' => array('1'),
		'enabled_plugins' => array(),
		'api_key'   => '',
		'api_hooks' => '',
	);
	
	/**
	 * Setting keys that should be serialized
	 * 
	 * @var array $_serialized
	 */		
	private $_serialized = array(
		'can_manage_bundles',
		'can_manage_rules',
		'can_manage_settings',
		'enabled_plugins'
	);

	private $_encrypted = array(
		'api_key'
	);	
	
	/**
	 * Setting keys that should be numeric
	 * 
	 * @var array $_serialized
	 */		
	private $_val_numeric = array(
		'list_limit'
	);
	
	
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Get all settings
	 * 
	 * @return array
	 */
	public function get_settings()
	{	
		$settings = array();

		$this->db->select('setting_key, setting_value, serialized')
				 ->from($this->_table);
		$query = $this->db->get();	

		if ($query->num_rows() > 0)
		{
			$_settings = $query->result_array();
				
			foreach ($_settings AS $setting)
			{
				$settings[$setting['setting_key']] = ($setting['serialized'] == '1' ? unserialize($setting['setting_value']) : $setting['setting_value']);
			}
		}
		
		// set default values if settings have not been created before
		foreach ($this->_defaults as $key => $default_value)
		{			
			if ( ! array_key_exists($key, $settings))
			{
				$settings[$key] = $default_value;
			}

			// return empty array for NULL settings that should be arrays
			if (in_array($key, $this->_serialized))
			{
				if ( ! is_array($settings[$key]))
				{
					$settings[$key] = array();
				}
			}
		}

		return $settings;
	}
	
	/**
	 * Get a specific setting
	 * 
	 * @param string $setting
	 * @return string
	 */
	public function get_setting($setting)
	{
		$data = $this->db->get_where($this->_table, array('setting_key' => $setting))->result_array();
		if(isset($data['0']))
		{
			$data = $data['0'];
			if($data['serialized'] == '1')
			{
				$data['setting_value'] = unserialize($data['setting_value']);
				if(!$data['setting_value'])
				{
					$data['setting_value'] = array();
				}
			}
			return $data['setting_value'];
		}
	}	

	/**
	 * Check if a specific setting exists
	 * 
	 * @param string $setting
	 * @return boolean
	 */
	public function setting_exists($setting)
	{
		$this->db->select('COUNT(id) as cnt')
    			 ->from($this->_table)
    			 ->where('setting_key', $setting);

		return ($this->db->count_all_results() > 0) ? TRUE : FALSE;
	}

	/**
	 * Update settings
	 * 
	 * @param array $data
	 * @return boolean
	 */	
	public function update_settings(array $data)
	{
		foreach($this->_defaults AS $key => $default_value)
		{
			$serialized = FALSE;
			$value = NULL;

			// has data been posted?
			if (isset($data[$key]))
			{
				$value = $data[$key];

				if(in_array($key, $this->_serialized) && is_array($data[$key]))
				{
					$value = serialize($data[$key]);
					$serialized = TRUE;
				}
				elseif(in_array($key, $this->_val_numeric))
				{
					if( ! is_numeric($data[$key]) || $data[$key] <= '0')	
					{
						$value = $default_value;
					}
				}
				elseif(in_array($key, $this->_encrypted) && $value != '')
				{
					$value = ee('Encrypt')->encode($value);
				}
			}
			
			$this->update_setting($key, $value, $serialized);
		}
		
		return TRUE;
	}

	/**
	 * Insert a setting
	 * 
	 * @param array $data
	 * @return boolean
	 */	
	public function add_setting(array $data)
	{
		return $this->db->insert($this->_table, $data); 
	}	
	
	/**
	 * Updates the value of a setting
	 * 
	 * @param string $key
	 * @param string $value
	 * @param boolean $serialized
	 */
	public function update_setting($key, $value, $serialized=FALSE)
	{
		// make sure it's a valid setting key
		if(array_key_exists($key, $this->_defaults))
		{
			$data = array(
				'setting_value' => $value,
				'serialized' 	=> ($serialized ? 1 : 0)
			);

			// insert or update?
			if( ! $this->setting_exists($key))
			{
				$data['setting_key'] = $key;
				return $this->add_setting($data);
			}
			else
			{
				$this->db->where('setting_key', $key);
				return $this->db->update($this->_table, $data);
			}
		}
		return FALSE;
	}
}
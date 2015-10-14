<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Mustash - update class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2014, hallmarkdesign
 * @link		http://hallmark-design.co.uk/code/mustash
 * @since		1.0
 * @filesource 	./system/user/addons/mustash/upd.mustash.php
 */
class Mustash_upd 
{ 
    public $class = '';  
    public $settings_table = ''; 
         
    public function __construct() 
    { 
		$path = dirname(realpath(__FILE__));
		include $path.'/config.php';
		$this->class = $config['class_name'];
		$this->rules_table = $config['rules_table'];
		$this->version = $config['version'];
    } 
    
	public function install() 
	{
		$sql = array();

		// install module 
		ee()->db->insert(
			'modules',
			array(
				'module_name' => $this->class,
				'module_version' => $this->version, 
				'has_cp_backend' => 'y',
				'has_publish_fields' => 'n'
			)
		);

		// add the rules table
		$sql[] = "
		CREATE TABLE `{ee()->db->dbprefix}stash_rules` (
			`id` int(11) unsigned NOT NULL auto_increment,
			`site_id` int(4) unsigned NOT NULL default '1',
			`plugin` varchar(64) NOT NULL,
	 		`hook` varchar(64) NOT NULL,
	 		`group_id` int(11) unsigned default NULL,
			`bundle_id` int(11) unsigned default NULL,
			`scope` ENUM('site', 'user') default NULL,
			`pattern` varchar(255) default NULL,
			`notes` TEXT default NULL,
			`ord` int(11) unsigned NOT NULL default '0',
			PRIMARY KEY  (`id`),
			KEY `plugin` (`plugin`),
			KEY `site_id` (`site_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";

		// add the settings table
		$sql[] = "
		CREATE TABLE `{ee()->db->dbprefix}stash_settings` (
			`id` int(11) unsigned NOT NULL auto_increment,
			`setting_key` varchar(32) NOT NULL default '',
			`setting_value` text,
			`serialized` int(1) unsigned NOT NULL default '0',
			PRIMARY KEY  (`id`),
			UNIQUE KEY `setting_key` (`setting_key`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";

		// add the ACT for the API
		$sql[] = "INSERT INTO exp_actions (class, method) VALUES ('{$this->class}', 'api')";

		// run the queries one by one
		foreach ($sql as $query)
		{
			ee()->db->query($query);
		}	
		
		return TRUE;
	} 

	public function uninstall()
	{
		ee()->load->dbforge();
	
		ee()->db->select('module_id');
		$query = ee()->db->get_where('modules', array('module_name' => $this->class));
	
		ee()->db->where('module_id', $query->row('module_id'));
		ee()->db->delete('module_member_groups');
	
		ee()->db->where('module_name', $this->class);
		ee()->db->delete('modules');

		ee()->db->where('class', $this->class);
		ee()->db->delete('actions');

		ee()->dbforge->drop_table('stash_settings');
		ee()->dbforge->drop_table('stash_rules');
	
		return TRUE;
	}

	public function update($current = '')
	{
		if ($current == $this->version)
		{
			return FALSE;
		}

		$sql = array();

		// Update to 2.0.0
        if (version_compare($current, '2.0.0', '<'))
        {
			$sql[] = "ALTER TABLE `exp_stash_rules` ADD `notes` TEXT NULL AFTER `pattern`";	
		}

		foreach ($sql as $query)
        {
            ee()->db->query($query);
        }  

		// update version number
		return TRUE;
	}

}
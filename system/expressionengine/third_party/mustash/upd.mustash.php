<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Mustash - update class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2013, hallmarkdesign
 * @link		http://hallmark-design.co.uk/code/mustash
 * @since		1.0
 * @filesource 	./system/expressionengine/third_party/mustash/upd.mustash.php
 */
class Mustash_upd 
{ 
    public $class = '';  
    public $settings_table = ''; 
         
    public function __construct() 
    { 
		$this->EE =& get_instance();
		$path = dirname(realpath(__FILE__));
		include $path.'/config'.EXT;
		$this->class = $config['class_name'];
		$this->rules_table = $config['rules_table'];
		$this->version = $config['version'];
    } 
    
	public function install() 
	{
		$sql = array();

		// install module 
		$this->EE->db->insert(
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
		CREATE TABLE `{$this->EE->db->dbprefix}stash_rules` (
			`id` int(11) unsigned NOT NULL auto_increment,
			`site_id` int(4) unsigned NOT NULL default '1',
			`plugin` varchar(64) NOT NULL,
	 		`hook` varchar(64) NOT NULL,
	 		`group_id` int(11) unsigned default NULL,
			`bundle_id` int(11) unsigned default NULL,
			`scope` ENUM('site', 'user') default NULL,
			`pattern` varchar(255) default NULL,
			`ord` int(11) unsigned NOT NULL default '0',
			PRIMARY KEY  (`id`),
			KEY `plugin` (`plugin`),
			KEY `site_id` (`site_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";

		// add the settings table
		$sql[] = "
		CREATE TABLE `{$this->EE->db->dbprefix}stash_settings` (
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
			$this->EE->db->query($query);
		}	
		
		return TRUE;
	} 

	public function uninstall()
	{
		$this->EE->load->dbforge();
	
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => $this->class));
	
		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');
	
		$this->EE->db->where('module_name', $this->class);
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', $this->class);
		$this->EE->db->delete('actions');

		$this->EE->dbforge->drop_table('stash_settings');
		$this->EE->dbforge->drop_table('stash_rules');
	
		return TRUE;
	}

	public function update($current = '')
	{
		if ($current == $this->version)
		{
			return FALSE;
		}	

		return TRUE;
	}

}
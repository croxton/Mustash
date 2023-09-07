<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include base class
if ( ! class_exists('Mustash_base'))
{
	require_once(PATH_THIRD . 'mustash/base.mustash.php');
}

 /**
 * Mustash - update class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2015, hallmarkdesign
 * @link		https://github.com/croxton/Stash/wiki/Mustash
 * @since		1.0
 * @filesource 	./system/user/addons/mustash/upd.mustash.php
 */
class Mustash_upd extends Mustash_base { 
         
    public function __construct() 
    { 
		parent::__construct();
    } 
    
	public function install() 
	{
		$sql = array();

		// install module 
		ee()->db->insert(
			'modules',
			array(
				'module_name' => $this->mod_class_name,
				'module_version' => $this->mod_version, 
				'has_cp_backend' => 'y',
				'has_publish_fields' => 'n'
			)
		);

		// add the rules table
		$sql[] = "
		CREATE TABLE `".ee()->db->dbprefix."stash_rules` (
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
		CREATE TABLE `".ee()->db->dbprefix."stash_settings` (
			`id` int(11) unsigned NOT NULL auto_increment,
			`setting_key` varchar(32) NOT NULL default '',
			`setting_value` text,
			`serialized` int(1) unsigned NOT NULL default '0',
			PRIMARY KEY  (`id`),
			UNIQUE KEY `setting_key` (`setting_key`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";

		// add the ACT for the API
		$sql[] = "INSERT INTO exp_actions (class, method) VALUES ('{$this->mod_class_name}', 'api')";

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
		$query = ee()->db->get_where('modules', array('module_name' => $this->mod_class_name));
	
		ee()->db->where('module_id', $query->row('module_id'));

        if (version_compare(APP_VER, '6.0', '>='))
        {
            ee()->db->delete('module_member_roles');
        }
        else
        {
            ee()->db->delete('module_member_groups');
        }

		ee()->db->where('module_name', $this->mod_class_name);
		ee()->db->delete('modules');

		ee()->db->where('class', $this->mod_class_name);
		ee()->db->delete('actions');

		ee()->dbforge->drop_table('stash_settings');
		ee()->dbforge->drop_table('stash_rules');
	
		return TRUE;
	}

	public function update($current = '')
	{
		if ($current == $this->mod_version)
		{
			return FALSE;
		}

		$sql = array();

		// Update to 2.0.0
        if (version_compare($current, '2.0.0', '<'))
        {
			$sql[] = "ALTER TABLE `".ee()->db->dbprefix."stash_rules` ADD `notes` TEXT NULL AFTER `pattern`";	
		}

		foreach ($sql as $query)
        {
            ee()->db->query($query);
        }  

		// update version number
		return TRUE;
	}

}
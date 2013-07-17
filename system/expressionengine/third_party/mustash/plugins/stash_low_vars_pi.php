<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stash_low_vars_pi
 * Mustash cache-breaking plugin
 *
 * @package		Mustash
 * @author  	Mark Croxton
 */

class Stash_low_vars_pi extends Mustash_plugin {

	/**
	 * Name
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $name = 'Low Variables';

	/**
	 * Version
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $version = '1.0.0';

	/**
	 * Extension hook priority
	 *
	 * @var 	integer
	 * @access 	public
	 */
	public $priority = '10';

	/**
	 * Extension hooks
	 *
	 * @var 	array
	 * @access 	protected
	 */
	protected $hooks = array(
		'@all',	
		'low_variables_post_save',
		'low_variables_delete'
	);

	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get groups for this object
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_groups()
	{
		$groups = array();

		if ($low_groups = $this->_get_low_var_groups())
		{
			foreach ($low_groups as $group)
			{
				$groups[$group['group_id']] = $group['group_label'];
			}
		}

		return $groups;
	}

	/*
	================================================================
	Hooks
	================================================================
	*/

	/**
	 * Hook: low_variables_post_save
	 *
	 * @access	public
	 * @param	array variable ids of the variables that were saved
	 * @param	array variable ids of the variables that were submitted but not saved
	 * @return	void
	 */
	public function low_variables_post_save($var_ids, $skipped)
	{
		$this->_flush(__FUNCTION__, $var_ids);
	}

	/**
	 * Hook: low_variables_delete
	 *
	 * @access	public
	 * @param	array variable ids of the variables that are about to be deleted
	 * @return	void
	 */
	public function low_variables_delete($var_ids)
	{
		$this->_flush(__FUNCTION__, $var_ids);
	}

	/*
	================================================================
	Utility
	================================================================
	*/

	/**
	 * Common cache-clearing utility method
	 *
	 * @access	private
	 * @param	array variable ids of the variables that are about to be deleted
	 * @return	void
	 */
	private function _flush($hook, $var_ids)
	{
		$groups_cleared = array();

		foreach($var_ids as $id)
		{
			// lookup the group for this var
			$group_id = $this->_get_low_var_group_id($id);

			if ( ! in_array($group_id, $groups_cleared))
			{
				// flush cache for each id, filtered by group
				$this->flush_cache($hook, $group_id);

				$groups_cleared[] = $group_id;
			}
		}
	}

	/*
	================================================================
	Model
	================================================================
	*/

	/**
	 * Get an array of Low Variable groups
	 *
	 * @access	private
	 * @return	string
	 */
	private function _get_low_var_groups()
	{
		$result = $this->EE->db->select('group_id, group_label')
		       ->from('low_variable_groups')
		       ->where('site_id', $this->site_id)
		       ->order_by('group_order', 'asc')
		       ->get();


		if ($result->num_rows() == 0) 
		{
			return FALSE;
		}

		return $result->result_array();

    }

    /**
	 * Get the Low Var group id for a given id
	 *
	 * @access	private
	 * @return	string
	 */
    private function _get_low_var_group_id($id)
	{
		$result = $this->EE->db->select('group_id')
				 		   ->from('low_variables')
				 		   ->where('variable_id', $id)
						   ->limit(1)
				 		   ->get();
 		   
		if ($result->num_rows() == 1) 
		{
			return $result->row('group_id');
		}
		else
		{
			return FALSE;
		}

    }
}

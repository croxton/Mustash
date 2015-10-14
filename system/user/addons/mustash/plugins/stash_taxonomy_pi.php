<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stash_taxonomy_pi
 * Mustash cache-breaking plugin
 *
 * @package		Mustash
 * @author  	Mark Croxton
 */

class Stash_taxonomy_pi extends Mustash_plugin {

	/**
	 * Name
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $name = 'Taxonomy';

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
		'taxonomy_updated',
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
	 * Set groups for this object
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function set_groups()
	{
		return stash_array_column($this->_get_trees(), 'label', 'id');
	}

	/*
	================================================================
	Hooks
	================================================================
	*/

	/**
	 * Hook: taxonomy_updated
	 *
	 * @access	public
	 * @param	integer the tree ID that was edited
	 * @param	string update_node|reorder_nodes|delete_branch|delete_node
	 * @param	array 
	 * @return	void
	 */
	public function taxonomy_updated($tree_id, $update_type, $data)
	{	
		$this->flush_cache(__FUNCTION__, $tree_id);
	}

	/*
	================================================================
	Model
	================================================================
	*/

	/**
	 * Get a list of Taxonomy trees
	 *
	 * @access	private
	 * @return	string
	 */
	private function _get_trees()
	{
		$result = ee()->db->select('id, label')
							   ->from('taxonomy_trees')
							   ->where('site_id', $this->site_id)
							   ->order_by('label ASC')
							   ->get();

		if ($result->num_rows() == 0) 
		{
			return FALSE;
		}
		return $result->result_array();
	}

}

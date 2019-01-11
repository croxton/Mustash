<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stash_categories_pi
 * Mustash cache-breaking plugin
 *
 * @package		Mustash
 * @author  	Mark Croxton
 */

class Stash_categories_pi extends Mustash_plugin {

	/**
	 * Name
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $name = 'Categories';

	/**
	 * Version
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $version = '2.0.0';

	/**
	 * Extension hook priority
	 *
	 * @var 	integer
	 * @access 	public
	 */
	public $priority = '10';

	/**
	 * Required modules
	 *
	 * @var 	integer
	 * @access 	protected
	 */
	protected $dependencies = array('Channel');

	/**
	 * Category reorder event
	 *
	 * @var 	boolean
	 * @access 	private
	 */
	private static $reorder = FALSE;

	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		// markers shared by all hooks
		$shared_markers = array(
			'cat_id',
			'cat_url_title',
			'parent_id',
			'group_id',
			'group_name',
		);

		// add hooks
		$this->register_hook('@all', $shared_markers);
		$this->register_hook('after_category_insert', $shared_markers);
		$this->register_hook('after_category_update', $shared_markers);
		$this->register_hook('after_category_delete', $shared_markers);

		// fake end-user hook, called on category update
		$this->register_hook('after_category_reorder', $shared_markers, TRUE, FALSE);
	}

	/**
	 * Set groups for this object
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function set_groups()
	{
		return stash_array_column($this->_get_cat_groups(), 'group_name', 'group_id');
	}

	/*
	================================================================
	Hooks
	================================================================
	*/

	/**
	 * Hook: after_category_insert
	 *
	 * @access	public
	 * @param	object 
	 * @param	array
	 * @return	void
	 */
	public function after_category_insert($cat_obj, $data)
	{
		$markers = $this->_prep_markers($data);
		$this->flush_cache(__FUNCTION__, $data['group_id'], $markers);
	}

	/**
	 * Hook: after_category_update
	 *
	 * @access	public
	 * @param	object 
	 * @param	array
	 * @param	array
	 * @return	void
	 */
	public function after_category_update($cat_obj, $data, $data_original)
	{
		if (isset($data_original['cat_order']))
		{
			// the category tree has been reordered
			// => trigger our 'after_category_reorder' hook
			$this->after_category_reorder($data);
		}
		else
		{
			// a single category was edited

			// we want to flush variables associated with the values of this category *before* it was edited
			$data = array_merge($data, $data_original);

			// prep markers and flush cache
			$markers = $this->_prep_markers($data);
			$this->flush_cache(__FUNCTION__, $data['group_id'], $markers);
		}
	}

	/**
	 * Hook: after_category_reorder
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	public function after_category_reorder($data)
	{
		if (FALSE == self::$reorder) 
		{
			// only run once for a reorder
			self::$reorder = TRUE;

			$markers = $this->_prep_markers($data);
			$this->flush_cache(__FUNCTION__, $data['group_id'], $markers);
		}
	}

	/**
	 * Hook: after_category_delete
	 *
	 * @access	public
	 * @param	object 
	 * @param	array
	 * @return	void
	 */
	public function after_category_delete($cat_obj, $data)
	{
		$markers = $this->_prep_markers($data);
		$this->flush_cache(__FUNCTION__, $data['group_id'], $markers);
	}

	/**
	 * Prep markers for rule parsing
	 *
	 * @access	private
	 * @param	array
	 * @return	array
	 */
	private function _prep_markers($data) 
	{
		/* $data:
		Array
		(
		    [cat_id] => 4
		    [site_id] => 1
		    [group_id] => 1
		    [parent_id] => 0
		    [cat_name] => test2
		    [cat_url_title] => test
		    [cat_description] => 
		    [cat_image] => 
		    [cat_order] => 1
		    [cat_image_select] => none
		)
		*/
		return array(
			'cat_id' 		=> $data['cat_id'],
			'cat_url_title' => $data['cat_url_title'],
			'parent_id'		=> $data['parent_id'],
			'group_id'		=> $data['group_id'],
			'group_name'	=> $this->_get_cat_group_name($data['group_id']),
		);
	}

	/*
	================================================================
	Model
	================================================================
	*/

	/**
	 * Get an array of category groups
	 *
	 * @access	private
	 * @return	string
	 */
	private function _get_cat_groups()
	{
		$result = ee()->db->select('group_id, group_name')
		       ->from('category_groups')
		       ->where('site_id', $this->site_id)
		       ->order_by('sort_order', 'asc')
		       ->get();

		if ($result->num_rows() == 0) 
		{
			return FALSE;
		}

		return $result->result_array();
    }

    /**
	 * Get a category group name
	 *
	 * @access	private
	 * @param	integer
	 * @return	string
	 */
	private function _get_cat_group_name($group_id)
	{
		$result = ee()->db->select('group_name')
		       ->from('category_groups')
		       ->where('group_id', $group_id)
		       ->where('site_id', $this->site_id)
		       ->get();

		if ($result->num_rows() == 0) 
		{
			return FALSE;
		}

		$row = $result->row(); 
		return $row->group_name;
    }

}

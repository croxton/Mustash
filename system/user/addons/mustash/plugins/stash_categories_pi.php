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
	public $version = '1.0.1';

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
		'@all' => array(
			'cat_id',
			'cat_url_title',
			'parent_id',
			'group_id'
		),	
		'category_save'=> array(
			'cat_id',
			'cat_url_title',
			'parent_id',
			'group_id'
		),
		'category_delete'=> array(
			'cat_id',
			'cat_url_title',
			'parent_id',
			'group_id'
		),
		'category_reorder'=> array(
			'cat_id',
			'cat_url_title',
			'parent_id',
			'group_id'
		),
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
		return stash_array_column($this->_get_cat_groups(), 'group_name', 'group_id');
	}

	/*
	================================================================
	Hooks
	================================================================
	*/

	/**
	 * Hook: category_save
	 *
	 * @access	public
	 * @param	integer category id
	 * @param	array category data
	 * @return	void
	 */
	public function category_save($cat_id, $data)
	{
		// prep markers
		$markers = array(
			'cat_id' 		=> $cat_id,
			'cat_url_title' => $data['cat_url_title'],
			'parent_id'		=> $data['parent_id'],
			'group_id'		=> $data['group_id'],
		);

		$this->flush_cache(__FUNCTION__, $data['group_id'], $markers);
	}

	/**
	 * Hook: category_delete
	 *
	 * @access	public
	 * @param	array category ids
	 * @return	void
	 */
	public function category_delete($cat_ids)
	{
		foreach($cat_ids as $cat_id)
		{
			// hydrate the category
			$cat = $this->_get_cat($cat_id);

			// prep markers
			$markers = array(
				'cat_id' 		=> $cat_id,
				'cat_url_title' => $cat->cat_url_title,
				'parent_id'		=> $cat->parent_id,
				'group_id'		=> $cat->group_id,
			);

			$this->flush_cache(__FUNCTION__, $cat->group_id, $markers);
		}
	}

	/**
	 * Hook: category_reorder
	 *
	 * @access	public
	 * @param	integer category id
	 * @return	void
	 */
	public function category_reorder($cat_id)
	{
		// hydrate the category
		$cat = $this->_get_cat($cat_id);

		// prep markers
		$markers = array(
			'cat_id' 		=> $cat_id,
			'cat_url_title' => $cat->cat_url_title,
			'parent_id'		=> $cat->parent_id,
			'group_id'		=> $cat->group_id,
		);

		$this->flush_cache(__FUNCTION__, $cat->group_id, $markers);
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
		$result = $this->EE->db->select('group_id, group_name')
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
	 * Hydrate a category
	 *
	 * @access	private
	 * @return	string
	 */
	private function _get_cat($cat_id)
	{
		$result = $this->EE->db->where('cat_id', $cat_id)->get('categories');

		if ($result->num_rows() == 0) 
		{
			return FALSE;
		}

		return $result->row();
    }

}

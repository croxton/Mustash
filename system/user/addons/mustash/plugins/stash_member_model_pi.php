<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stash_member_model_pi
 * Mustash cache-breaking plugin
 *
 * @package		Mustash
 * @author  	Mark Croxton
 */

class Stash_member_model_pi extends Mustash_plugin {

	/**
	 * Name
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $name = 'Member Model';

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

		'@all' => array(
			'member_id',
			'screen_name',
			'username',
			'group_id',
			'group_title'
		),	

		 // EE 2.6.0+
		'member_create_end' => array(
			'member_id',
			'screen_name',
			'username',
			'group_id',
			'group_title'
		),
		// EE 2.6.0+
		'member_update_end' => array(
			'member_id',
			'screen_name',
			'username',
			'group_id',
			'group_title'
		),
		 // EE 2.4.0+
		'member_delete' => array(
			'member_id',
			'screen_name',
			'username',
			'group_id',
			'group_title'
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
		$this->EE->load->model('member_model');
	}

	/**
	 * Set groups for this object
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function set_groups()
	{
		return stash_array_column($this->_get_cp_member_groups(), 'group_title', 'group_id');
	}

	/*
	================================================================
	Hooks
	================================================================
	*/

	/**
	 * Hook: member_create_end
	 *
	 * @access	public
	 * @param	integer
	 * @param	array
	 * @param	array
	 * @return	void
	 */
	public function member_create_end($member_id, $data, $cdata)
	{		
		// get the group title
		$group = $this->EE->member_model->get_member_groups(array(), array('group_id' => $data['group_id']), 1)->row();

		// prep marker data
		$markers = array(
			'member_id' 	=> $member_id,
			'screen_name'	=> $data['screen_name'],
			'username'		=> $data['username'],
		    'group_id'		=> $data['group_id'],
		    'group_title'	=> $group->group_title
		);

		// flush cache
		$this->flush_cache(__FUNCTION__, $data['group_id'], $markers);
	}


	/**
	 * Hook: member_update_end
	 *
	 * @access	public
	 * @param	integer
	 * @param	array
	 * @return	void
	 */
	public function member_update_end($member_id, $data)
	{
		// hydrate the member 
		$member = $this->EE->member_model->get_member_data($member_id, array('group_id', 'screen_name', 'username'))->row();

		// get the group title
		$group = $this->EE->member_model->get_member_groups(array(), array('group_id' => $member->group_id), 1)->row();

		// prep marker data
		$markers = array(
			'member_id' 	=> $member_id,
			'screen_name'	=> $member->screen_name,
			'username'		=> $member->username,
		    'group_id'		=> $member->group_id,
		    'group_title'	=> $group->group_title
		);

		// flush cache
		$this->flush_cache(__FUNCTION__, $member->group_id, $markers);
	}

	/**
	 * Hook: member_delete
	 *
	 * @access	public
	 * @param	array
	 * @return	array
	 */
	public function member_delete($member_ids)
	{
		foreach($member_ids as $member_id)
		{
			// hydrate the member 
			$member = $this->EE->member_model->get_member_data($member_id, array('group_id', 'screen_name', 'username'))->row();

			// get their group title
			$group = $this->EE->member_model->get_member_groups(array(), array('group_id' => $member->group_id), 1)->row();

			// prep marker data
			$markers = array(
				'member_id' 	=> $member_id,
				'screen_name'	=> $member->screen_name,
				'username'		=> $member->username,
			    'group_id'		=> $member->group_id,
			    'group_title'	=> $group->group_title
			);

			// flush cache
			$this->flush_cache(__FUNCTION__, $member->group_id, $markers);
		}

		return $member_ids;
	}

	/*
	================================================================
	Model
	================================================================
	*/

	/**
	 * Get a list of member groups that can access the control panel
	 *
	 * @access	private
	 * @return	array
	 */
	private function _get_cp_member_groups()
	{
		$result = $this->EE->db->select('group_id, group_title')
			       ->from('member_groups')
			       ->where('can_access_cp', 'y')
			       ->order_by('group_title', 'asc')
			       ->get();

		if ($result->num_rows() == 0) 
		{
			return FALSE;
		}
		return $result->result_array();
	}
}




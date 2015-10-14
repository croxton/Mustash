<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stash_forum_pi
 * Mustash cache-breaking plugin
 *
 * @package		Mustash
 * @author  	Mark Croxton
 */

class Stash_forum_pi extends Mustash_plugin {

	/**
	 * Name
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $name = 'Forum';

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
		'forum_submit_post_end' => array(
			'topic_id', 
			'forum_id',
			'board_id',
 			'author_id'
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
		return stash_array_column($this->_get_forums_by_cat(), 'forum_label', 'forum_id');
	}

	/*
	================================================================
	Hooks
	================================================================
	*/

	/**
	 * Hook: forum_submit_post_end
	 *
	 * @access	public
	 * @param	object $forum the discussion forum object instance
	 * @param	array 
	 * @return	void
	 */
	public function forum_submit_post_end($forum, $data)
	{	
		/*
		$data:
		Array
		(
		    [topic_id] => 1
		    [forum_id] => 2
		    [body] => dsfas
		    [parse_smileys] => y
		    [author_id] => 1
		    [ip_address] => 192.168.0.6
		    [post_date] => 1374070627
		    [board_id] => 1
		    [post_id] => 2
		)
		*/

		// prep marker data
		$markers = array(
			'topic_id' 	=> $data['topic_id'],
			'forum_id'	=> $data['forum_id'],
			'board_id'	=> $data['board_id'],
		    'author_id'	=> $data['author_id']
		);
	
		$this->flush_cache(__FUNCTION__, $data['forum_id'], $markers);
	}

	/*
	================================================================
	Model
	================================================================
	*/

	/**
	 * Get an ordered list of forums by category
	 *
	 * @access	private
	 * @return	string
	 */
	private function _get_forums_by_cat()
	{
		$result = $this->EE->db->select('f2.forum_id as forum_id, CONCAT(f1.forum_name, ": ", f2.forum_name) AS forum_label', FALSE)
							   ->from('forums as f1')
							   ->join('forums as f2', 'f1.forum_id = f2.forum_parent')
							   ->join('forum_boards as fb', 'fb.board_id = f1.board_id', 'inner')
							   ->where('f1.forum_is_cat', 'y')
							   ->where('fb.board_site_id', $this->site_id)
							   ->order_by('f1.forum_order ASC, f2.forum_order ASC')
							   ->get();

		if ($result->num_rows() == 0) 
		{
			return FALSE;
		}
		return $result->result_array();
	}
}

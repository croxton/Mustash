<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stash_comments_pi
 * Mustash cache-breaking plugin
 *
 * @TODO
 * -----
 * 3rd November 2015: when Ellislab get around to rewriting the Comment module (front-end) to use the new model, 
 * this plugin will need to be rewritten to use the new hooks
 *
 * @package		Mustash
 * @author  	Mark Croxton
 */

class Stash_comments_pi extends Mustash_plugin {

	/**
	 * Name
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $name = 'Comments';

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
	protected $dependencies = array('Comment');

	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		ee()->load->model('mustash_channel_data');

		// markers shared by all hooks
		$shared_markers = array(
			'channel_id',
			'channel_name',
			'author_id',
		    'entry_id',
		    'url_title'
		);

		// add hooks
		$this->register_hook('after_comment_insert', $shared_markers);
		$this->register_hook('after_comment_update', $shared_markers);
		$this->register_hook('after_comment_delete', $shared_markers);
	}

	/**
	 * Set groups for this object
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function set_groups()
	{
		return ee()->mustash_channel_data->get_channels();
	}

	/*
	================================================================
	Hooks
	================================================================
	*/

	/**
	 * Hook: after_comment_insert
	 *
	 * @access	public
	 * @param	EllisLab\ExpressionEngine\Model\Comment\Comment
	 * @param	array
	 * @return	void
	 */
	public function after_comment_insert($comment_obj, $data)
	{
		// prep marker data
		$markers = $this->_prep_markers($data);

		// flush cache
		$this->flush_cache(__FUNCTION__, $data['channel_id'], $markers);
	}

	/**
	 * Hook: after_comment_update
	 *
	 * @access	public
	 * @param	EllisLab\ExpressionEngine\Model\Comment\Comment
	 * @param	array
	 * @return	void
	 */
	public function after_comment_update($comment_obj, $data, $data_original)
	{
		// we want to flush variables associated with the values of this entry *before* it was edited 
		$data = array_merge($data, $data_original);

		// prep marker data
		$markers = $this->_prep_markers($data);

		// flush cache
		$this->flush_cache(__FUNCTION__, $data['channel_id'], $markers);
	}


	/**
	 * Hook: after_comment_delete
	 *
	 * @access	public
	 * @param	EllisLab\ExpressionEngine\Model\Comment\Comment
	 * @param	array
	 * @return	void
	 */
	public function after_comment_delete($comment_obj, $data)
	{
		// prep marker data
		$markers = $this->_prep_markers($data);

		// flush cache
		$this->flush_cache(__FUNCTION__, $data['channel_id'], $markers);
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
		    [comment_id] => 1
		    [site_id] => 1
		    [entry_id] => 1
		    [channel_id] => 1
		    [author_id] => 5
		    [status] => o
		    [name] => mark
		    [email] => mcroxton@gmail.com
		    [url] => 
		    [location] => 
		    [ip_address] => ::1
		    [comment_date] => 1447172097
		    [edit_date] => 
		    [comment] => asdada
		)
		*/
	
		// prep marker data
		$markers = array(
			'channel_id' 	=> $data['channel_id'],
			'channel_name'	=> ee()->mustash_channel_data->get_channel_name($data['channel_id']),
			'author_id'		=> isset($data['author_id']) ? $data['author_id'] : '0',
		    'entry_id'		=> $data['entry_id'],
		    'url_title'		=> ee()->mustash_channel_data->get_url_title($data['entry_id']),
		);

		return $markers;
	}
}

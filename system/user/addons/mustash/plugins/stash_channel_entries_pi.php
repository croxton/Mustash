<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stash_channel_entries_pi
 * Mustash cache-breaking plugin
 *
 * @package		Mustash
 * @author  	Mark Croxton
 */

class Stash_channel_entries_pi extends Mustash_plugin {

	/**
	 * Name
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $name = 'Channel Entries';

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
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();	

		// load model
		ee()->load->model('mustash_channel_data');

		// markers shared by all hooks
		$shared_markers = array(
			'channel_id',
			'channel_name',
			'author_id',
		    'entry_id',
		    'url_title',
		    'year',
		    'month',
		    'day',
		);

		// add hooks
		$this->register_hook('@all', $shared_markers);
		$this->register_hook('after_channel_entry_insert', $shared_markers);
		$this->register_hook('after_channel_entry_update', $shared_markers);
		$this->register_hook('after_channel_entry_delete', $shared_markers);
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
	 * Hook: after_channel_entry_insert
	 *
	 * @access	public
	 * @param	EllisLab\ExpressionEngine\Model\Channel\ChannelEntry
	 * @param	array
	 * @return	void
	 */
	public function after_channel_entry_insert($entry_obj, $data)
	{
		// prep marker data
		$markers = $this->_prep_markers($data);

		// flush cache
		$this->flush_cache(__FUNCTION__, $data['channel_id'], $markers);
	}

	/**
	 * Hook: after_channel_entry_update
	 *
	 * @access	public
	 * @param	EllisLab\ExpressionEngine\Model\Channel\ChannelEntry
	 * @param	array 
	 * @param	array
	 * @return	void
	 */
	public function after_channel_entry_update($entry_obj, $data, $data_original)
	{
		// we want to flush variables associated with the values of this entry *before* it was edited 
		$data = array_merge($data, $data_original);

		// prep marker data
		$markers = $this->_prep_markers($data);

		// flush cache
		$this->flush_cache(__FUNCTION__, $data['channel_id'], $markers);
	}

	/**
	 * Hook: after_channel_entry_delete
	 *
	 * @access	public
	 * @param	EllisLab\ExpressionEngine\Model\Channel\ChannelEntry
	 * @param	array
	 * @return	void
	 */
	public function after_channel_entry_delete($entry_obj, $entry_data)
	{
		// prep marker data
		$markers = $this->_prep_markers($entry_data);

		// flush cache
		$this->flush_cache(__FUNCTION__, $entry_data['channel_id'], $markers);
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
		    [entry_id] => 1
		    [site_id] => 1
		    [channel_id] => 1
		    [author_id] => 1
		    [forum_topic_id] => 
		    [ip_address] => ::1
		    [title] => Test blog post
		    [url_title] => test-blog-post
		    [status] => open
		    [versioning_enabled] => 1
		    [view_count_one] => 
		    [view_count_two] => 
		    [view_count_three] => 
		    [view_count_four] => 
		    [allow_comments] => 1
		    [sticky] => 
		    [entry_date] => 1446052920
		    [year] => 2015
		    [month] => 10
		    [day] => 28
		    [expiration_date] => 0
		    [comment_expiration_date] => 0
		    [edit_date] => 
		    [recent_comment_date] => 
		    [comment_total] => 
		    [in_hook] => Array
		        (
		        )

		    [field_id_1] => ​Some content
		)
		*/
	
		// prep marker data
		$markers = array(
			'channel_id' 	=> $data['channel_id'],
			'channel_name'	=> ee()->mustash_channel_data->get_channel_name($data['channel_id']),
			'author_id'		=> $data['author_id'],
		    'entry_id'		=> $data['entry_id'],
		    'url_title'		=> $data['url_title'],
		    'year'			=> $data['year'],
		    'month'			=> $data['month'],
		    'day'			=> $data['day'],
		);

		return $markers;
	}
}

<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
	public $version = '1.0.0';

	/**
	 * Extension hook priority
	 *
	 * @var 	integer
	 * @access 	public
	 */
	public $priority = '10';

	/**
	 * Extension hooks and associated markers
	 *
	 * @var 	array
	 * @access 	protected
	 */
	protected $hooks = array(
		'entry_submission_end' => array(
			'channel_id',
			'channel_name',
			'author_id',
		    'entry_id',
		    'url_title',
		),
		'update_multi_entries_loop' => array(
			'channel_id',
			'channel_name',
			'author_id',
			'entry_id',
			'url_title',
		),
		'delete_entries_loop' => array(
			'channel_id',
			'channel_name',
		    'entry_id',
		)
	);

	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();	
		$this->EE->load->model('mustash_channel_data');
	}

	/**
	 * Get groups for this object
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_groups()
	{
		return $this->EE->mustash_channel_data->get_channels();
	}

	/*
	================================================================
	Hooks
	================================================================
	*/

	/**
	 * Hook: entry_submission_end
	 *
	 * @access	public
	 * @param	integer
	 * @param	array
	 * @param	array
	 * @return	void
	 */
	public function entry_submission_end($entry_id, $meta, $data)
	{
		/* $meta:
		Array
		(
		    [channel_id] => 7
		    [author_id] => 2
		    [site_id] => 1
		    [ip_address] => 192.168.0.6
		    [title] => Mike Swangard
		    [url_title] => mike-swangard
		    [entry_date] => 1363267553
		    [edit_date] => 20130327162354
		    [versioning_enabled] => y
		    [year] => 2013
		    [month] => 03
		    [day] => 14
		    [expiration_date] => 0
		    [comment_expiration_date] => 0
		    [sticky] => n
		    [status] => open
		    [allow_comments] => y
		)
		*/

		// prep marker data
		$markers = array(
			'channel_id' 	=> $meta['channel_id'],
			'channel_name'	=> $this->EE->mustash_channel_data->get_channel_name($meta['channel_id']),
			'author_id'		=> $this->EE->mustash_channel_data->get_author_id($entry_id),
		    'entry_id'		=> $entry_id,
		    'url_title'		=> $meta['url_title']
		);

		// flush cache
		$this->flush_cache(__FUNCTION__, $meta['channel_id'], $markers);
	}

	/**
	 * Hook: update_multi_entries_loop
	 *
	 * @access	public
	 * @param	integer
	 * @param	array
	 * @return	void
	 */
	public function update_multi_entries_loop($entry_id, $data)
	{
		/* $data:
		Array
		(
		    [title] => Blog entry 1
		    [url_title] => blog-entry-1
		    [entry_date] => 1357733074
		    [edit_date] => 20130605171636
		    [status] => open
		    [sticky] => n
		    [allow_comments] => y
		    [year] => 2013
		    [month] => 01
		    [day] => 09
		)
		*/

		// get missing channel data
		$channel = $this->_get_channel($entry_id);

		// prep marker data
		$markers = array(
			'channel_id' 	=> $channel['channel_id'],
			'channel_name'	=> $channel['channel_name'],
			'author_id'		=> $this->EE->mustash_channel_data->get_author_id($entry_id),
		    'entry_id'		=> $entry_id,
		    'url_title'		=> $data['url_title']
		);

		// flush cache
		$this->flush_cache(__FUNCTION__, $channel['channel_id'], $markers);
	}

	/**
	 * Hook: delete_entries_loop
	 *
	 * @access	public
	 * @param	integer
	 * @param	integer
	 * @return	void
	 */
	public function delete_entries_loop($entry_id, $channel_id)
	{
		/*
		As this hook is called AFTER the entry has been deleted, 
		it is impossible to get any other data about the entry :(
		*/

		$markers = array(
			'channel_id' 	=> $channel_id,
			'channel_name'	=> $this->EE->mustash_channel_data->get_channel_name($channel_id),
		    'entry_id'		=> $entry_id
		);

		// flush cache
		$this->flush_cache(__FUNCTION__, $channel_id, $markers);
	}
}

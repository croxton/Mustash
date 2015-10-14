<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stash_low_reorder_pi
 * Mustash cache-breaking plugin
 *
 * @package		Mustash
 * @author  	Mark Croxton
 */

class Stash_low_reorder_pi extends Mustash_plugin {

	/**
	 * Name
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $name = 'Low Reorder';

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
		'low_reorder_post_sort' => array(
			'channel_id',
			'channel_name',
			'author_id',
	    	'entry_id',
	    	'url_title',
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
		ee()->load->model('mustash_channel_data');
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
	 * Hook: low_reorder_post_sort
	 *
	 * @access	public
	 * @param	array entry_ids that were affected.
	 * @param	bool whether the clear cache checkbox was checked or not.
	 * @return	void
	 */
	public function low_reorder_post_sort($entries, $clear_cache)
	{
		$channels_cleared = array();

		foreach($entries as $entry_id)
		{
			// get comment data
			$entry = ee()->mustash_channel_data->get_entry($entry_id);

			// prep marker data
			$markers = array(
				'channel_id' 	=> $entry['channel_id'],
				'channel_name'	=> $entry['channel_name'],
				'author_id'		=> $entry['author_id'],
			    'entry_id'		=> $entry_id,
			    'url_title'		=> $entry['url_title']
			);
		
			if ( ! in_array($entry['channel_id'], $channels_cleared))
			{
				// flush cache for each channel affected
				$this->flush_cache(__FUNCTION__, $entry['channel_id'], $markers);
				$channels_cleared[] = $entry['channel_id'];
			}
			
		}
	}
}

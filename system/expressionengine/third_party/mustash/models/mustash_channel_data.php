<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Mustash - channel data model
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2013, hallmarkdesign
 * @link		http://hallmark-design.co.uk/code/mustash/
 * @since		1.0
 * @filesource 	./system/expressionengine/third_party/mustash/models/mustash_channel_data.php
 */
class Mustash_channel_data extends CI_Model
{
	public $EE;
	protected $site_id;

    function __construct()
    {
        parent::__construct();
		$this->EE = get_instance();
		$this->site_id = $this->EE->config->item('site_id');
    }

    /**
	 * Get the channel name for a given channel id
	 *
	 * @access	public
	 * @param	integer
	 * @return	string
	 */
	function get_channel_name($channel_id)
	{
		$result = $this->EE->db->select('channel_name')
				 		   ->from('channels')
				 		   ->where('channel_id', $channel_id)
						   ->limit(1)
				 		   ->get();
 		   
		if ($result->num_rows() == 1) 
		{
			return $result->row('channel_name');
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Get the channel for a given entry id
	 *
	 * @access	public
	 * @param	integer
	 * @return	string
	 */
	function get_channel($entry_id)
	{
		$result = $this->EE->db->select('channels.channel_id, channels.channel_name')
				 		   ->from('channel_titles')
				 		   ->join('channels', 'channel_titles.channel_id = channels.channel_id', 'left')
				 		   ->where('channel_titles.entry_id', $entry_id)
						   ->limit(1)
				 		   ->get();
 		   
		if ($result->num_rows() == 1) 
		{
			return $result->row_array();
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Get the author id for a given entry id
	 *
	 * @access	public
	 * @param	integer
	 * @return	integer
	 */
	function get_author_id($entry_id)
	{
		$result = $this->EE->db->select('author_id')
				 		   ->from('channel_titles')
				 		   ->where('entry_id', $entry_id)
						   ->limit(1)
				 		   ->get();
 		   
		if ($result->num_rows() == 1) 
		{
			return $result->row('author_id');
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Get channels
	 *
	 * @access	public
	 * @return	array
	 */
	function get_channels()
	{
		$column = array();

		$result = $this->EE->db->select('*')
				 		    ->from('channels')
				 		    ->where('site_id', $this->site_id)
				 		    ->get();

		if ($result->num_rows() > 0) 
		{
			$column = array();

			foreach ($result->result_array() as $channel)
			{
				$column[$channel['channel_id']] = $channel['channel_title'];
			}
		}

		return $column;
	}

	/**
	 * Get the url title for a given entry id
	 *
	 * @access	public
	 * @param	integer
	 * @return	string
	 */
	function get_url_title($entry_id)
	{
		$result = $this->EE->db->select('url_title')
				 		   ->from('channel_titles')
				 		   ->where('entry_id', $entry_id)
						   ->limit(1)
				 		   ->get();
 		   
		if ($result->num_rows() == 1) 
		{
			return $result->row('url_title');
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Hydrate a comment for a given id
	 *
	 * @access	public
	 * @param	integer
	 * @return	string
	 */
	function get_comment($comment_id)
	{
		$result = $this->EE->db->select('comments.*, channels.channel_name')
						   ->from('comments')
				 		   ->join('channels', 'comments.channel_id = channels.channel_id', 'left')
				 		   ->where('comments.comment_id', $comment_id)
						   ->limit(1)
				 		   ->get();
 		   
		if ($result->num_rows() == 1) 
		{
			return $result->row_array();
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Hydrate an entry for a given id
	 *
	 * @access	public
	 * @param	integer
	 * @return	string
	 */
	function get_entry($entry_id)
	{
		$result = $this->EE->db->select('channel_titles.*, channels.channel_name')
						   ->from('channel_titles')
				 		   ->join('channels', 'channel_titles.channel_id = channels.channel_id', 'left')
				 		   ->where('channel_titles.entry_id', $entry_id)
						   ->limit(1)
				 		   ->get();
 		   
		if ($result->num_rows() == 1) 
		{
			return $result->row_array();
		}
		else
		{
			return FALSE;
		}
	}
}
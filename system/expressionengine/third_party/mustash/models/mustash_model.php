<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . 'stash/models/stash_model.php';

 /**
 * Mustash - model
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2014, hallmarkdesign
 * @link		http://hallmark-design.co.uk/code/mustash/
 * @since		1.0
 * @filesource 	./system/expressionengine/third_party/mustash/models/mustash_model.php
 */
class Mustash_model extends Stash_model
{
	public $EE;
	protected $site_id;

    function __construct()
    {
        parent::__construct();
		$this->EE = get_instance();

		// since this model is used in the CP we'll be only operating on the current selected site
		$this->site_id = $this->EE->config->item('site_id');
    }

    /*
	================================================================
	Variables
	================================================================
	*/

    /**
	 * Return a filtered list of variables
	 *
	 * @param array $where where conditions
	 * @param integer $perpage
	 * @param integer $offset
	 * @param integer $order
	 * @return array
	 */	
    function get_variables($where=array(), $perpage=20, $offset=0, $order=FALSE)
    {
    	$result = array();

    	$this->db->select('stash.id as id, key_name, key_label, created, expire, session_id, bundle_label')
    			 ->from('stash')
    			 ->join('stash_bundles', 'stash_bundles.id = stash.bundle_id', 'left')	
		 		 ->where('stash.site_id', $this->site_id)
		 		 ->where("(expire > {$this->EE->localize->now} OR expire=0)");

		// apply where conditions
		$this->_filter_variables($where);

		// apply order and sort
		if ($order)
		{
			if (is_array($order))
			{
				foreach($order as $key => $val)
				{
					$this->db->order_by($key, $val);
				}
			}
			elseif (is_string($order))
			{
				$this->db->order_by($order);
			}
		} 
		else
		{
			$this->db->order_by('stash.key_name', 'asc');
		}	 

		// pagination
		if ($perpage > 0)
		{
			$this->db->limit($perpage, $offset); 
		}

		$query = $this->db->get();

		if ($query->num_rows() > 0)
		{
			$result = $query->result_array();

			foreach($result as &$row)
			{
				if ($row['session_id'] == '_global')
				{
					$row['scope'] = 'site';
				} 
				else
				{
					$row['scope'] = 'user';
				}
			}
			unset($row);
		} 
		return $result;
    }


    /**
	 * Filter list variables
	 *
	 * @param array $where where conditions
	 * @return void
	 */	
    private function _filter_variables($where=array())
    {
    	// filter by scope
		if (isset($where['scope']) && $where['scope'] !== FALSE) 
		{
			if ($where['scope'] == 'site')
			{
				$this->db->where('session_id', '_global');
			}
			else
			{
				$this->db->where('session_id !=', '_global');
			}	
		}

		// filter by bundle_id
		if (isset($where['bundle_id']) && $where['bundle_id'] !== FALSE) 
		{	
			$this->db->where('stash_bundles.id', $where['bundle_id']);
		}

		// search by keyword(s)
		if ( ! empty($where['keywords']))
		{
			// prepare the array of keywords
			$keywords = trim($where['keywords']);
			
			// keyword search
			if (preg_match('/"(.*)"/Usi', $keywords))
			{
				// search for phrase enclosed in quotes
				$keywords = trim($keywords, '"');
				$keywords = array($keywords);
			}
			else
			{
				$keywords = explode(' ', trim($where['keywords']));
			}
		
			// add or_like match
			foreach($keywords as $word)
			{
				$this->db->like('key_name', $word);
			}	
		}

		// filter by one or more IDs
		if ( ! empty($where['id']))
		{
			if (is_array($where['id']))
			{
				$this->db->where_in('stash.id', $where['id']);
			}
			else
			{
				$this->db->where('stash.id', $where['id']);
			}
		}
    }

    /**
	 * Return the count of a filtered list of variables
	 *
	 * @param array $where where conditions
	 * @param integer $perpage
	 * @param offset $offset
	 * @param order $order
	 * @return integer
	 */	
    function get_total_variables($where=array(), $perpage=20, $offset=0, $order=NULL)
    {
    	$this->db->select('COUNT(id) as cnt')
    			 ->from('stash')
    			 ->join('stash_bundles', 'stash_bundles.id = stash.bundle_id', 'left')	
    			 ->where('stash.site_id', $this->site_id)
    			 ->where("(expire > {$this->EE->localize->now} OR expire=0)");

    	// apply where conditions
		$this->_filter_variables($where);
					 
		return $this->db->count_all_results();
    }

    /**
	 * Get a variable by id
	 *
	 * @param integer $id
	 * @return array
	 */
	function get_variable($id)
	{	
		$this->db->select('stash.*, stash_bundles.bundle_label')
				 ->from('stash')
				 ->join('stash_bundles', 'stash_bundles.id = stash.bundle_id', 'left')
				 ->where('stash.id', $id)
				 ->limit(1);
		$result = $this->db->get();
	
		if ($result->num_rows() == 1) 
		{	
			$row = $result->row_array();

			if ($row['session_id'] == '_global')
			{
				$row['scope'] = 'site';
			} 
			else
			{
				$row['scope'] = 'user';
			}			
			return $row;
		}
		else return FALSE;
	}

	/**
	 * Update a variable value, and any corresponding static cache file
	 *
	 * @param integer $id
	 * @param array $data
	 * @return boolean
	 */
	function update_variable($id, $data = array())
	{
		$this->db->where('id', $id); 	
		
		if ($result = $this->db->update('stash', $data))		 		   
		{	
			// update corresponding static file cache?
			$this->write_static_cache(
				$data['key_name'], 
				$data['bundle_id'], 
				$this->site_id, 
				$data['parameters']
			);	

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Delete one or more variables
	 *
	 * @param array $ids
	 * @return boolean
	 */
	function clear_variables($ids)
	{
		// hydrate the vars before we kill them so that we can delete any corresponding static cache files
		$query = $this->db->select('id, key_name, key_label, bundle_id, session_id')
						  ->where_in('id', $ids)
						  ->get('stash');

		if ($query->num_rows() > 0)
		{
			return $this->delete_cache($query->result(), $this->site_id);
		}

		return FALSE;			  
	}

	/**
	 * Delete multiple variables in the current site
	 *
	 * @param integer/boolean $bundle_id
	 * @param string $scope all|site|user
	 * @param string $regex a regular expression
	 * @param integer $invalidate delay until cached item expires (seconds)
	 * @return boolean
	 */
	function clear_matching_variables($bundle_id = FALSE, $scope = NULL, $regex = NULL, $invalidate = 0)
	{
		$session_id = NULL;

		if ( ! is_null($scope))
		{
			if ($scope === 'user' || $scope === 'site')
			{
				$session_id = $scope;
			}
		}
		return $this->delete_matching_keys($bundle_id, $session_id, $this->site_id, $regex, $invalidate);
	}


	/*
	================================================================
	Bundles
	================================================================
	*/

	function list_bundles()
    {	
    	$r = array();

		$query = $this->db->from('stash_bundles')
		 ->order_by('bundle_label', 'asc')
		 ->get();
				
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				$r[$row['id']] = $row['bundle_label'];
			}
		}
		
		return $r;
    }

     /**
	 * Return a list of bundles, with variables count for the current site
	 *
	 * @param integer $perpage
	 * @param integer $offset
	 * @param integer $order
	 * @return array
	 */	
    function get_bundles($perpage=20, $offset=0, $order=FALSE)
    {
    	$result = array();

    	// select bundles with a count of assigned variables in the current site
    	$this->db->select('*, (
	    		SELECT COUNT(id) 
	    			FROM  `' . $this->EE->db->dbprefix.'stash` 
	    			WHERE `' . $this->EE->db->dbprefix.'stash`.`bundle_id` = `'. $this->EE->db->dbprefix .'stash_bundles`.`id`
	    			AND   `' . $this->EE->db->dbprefix.'stash`.`site_id`   = ' . $this->site_id.'
	    			AND  (`' . $this->EE->db->dbprefix.'stash`.`expire`    > ' .$this->EE->localize->now . ' OR `'.$this->EE->db->dbprefix.'stash`.`expire` = 0)
	    		) as cnt')
    			 ->from('stash_bundles');

		// apply order and sort
		if ($order)
		{
			if (is_array($order))
			{
				foreach($order as $key => $val)
				{
					$this->db->order_by($key, $val);
				}
			}
			elseif (is_string($order))
			{
				$this->db->order_by($order);
			}
		} 
		else
		{
			$this->db->order_by('bundle_label', 'asc');
		}	 

		// pagination
		$this->db->limit($perpage, $offset); 

		$query = $this->db->get();

		if ($query->num_rows() > 0)
		{
			$result = $query->result_array();

		} 
		return $result;
    }

    /**
	 * Return the count of the list of bundles
	 *
	 * @param integer $perpage
	 * @param offset $offset
	 * @param order $order
	 * @return integer
	 */	
    function get_total_bundles($perpage=20, $offset=0, $order=NULL)
    {
    	return $this->db->select('COUNT(id) as cnt')
    			 		->from('stash_bundles')
						->count_all_results();
    }


    /**
	 * Get a bundle by id
	 *
	 * @param integer $id
	 * @return array
	 */
	function get_bundle($id)
	{	
		$this->db->from('stash_bundles')
				 ->where('id', $id)
				 ->limit(1);
		$result = $this->db->get();
	
		if ($result->num_rows() == 1) 
		{	
			return $result->row_array();
		}
		else return FALSE;
	}


	/**
	 * Inserts a new bundle record
	 *
	 * @param array $data
	 * @return boolean/integer
	 */
	public function add_bundle($data=NULL)
	{
		if ($data === NULL) return FALSE;
		
		// do insert, use table name
		if ($this->db->insert('stash_bundles', $data))
		{
			return $this->db->insert_id();
		}
		else return FALSE;
	}

	/**
	 * Update a bundle
	 *
	 * @param integer $id
	 * @param string $parameters
	 * @return boolean
	 */
	function update_bundle($id, $data)
	{	
		$this->db->where('id', $id);		 	
		
		if ($result = $this->db->update('stash_bundles', $data))		 		   
		{		
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Delete an unlocked bundle
	 *
	 * @param integer $id
	 * @return boolean
	 */
	function delete_bundle($id)
	{
		$this->db->where('id', $id)
				 ->where('is_locked', '0')
				 ->limit(1);	

		if ($this->EE->db->delete('stash_bundles')) 
		{	
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}


	/**
	 * Check if a bundle name is unique
	 *
	 * @param	string
	 * @return	bool
	 */
	function is_bundle_name_unique($bundle)
	{
		$query = $this->db->select('1', FALSE)
				 		  ->where('BINARY `bundle_name`=', $this->db->escape($bundle), FALSE)
						  ->get('stash_bundles');
						
		return $query->num_rows() == 0;
	}

	/*
	================================================================
	Rules
	================================================================
	*/

	/**
	 * Return a list of rules for a specific plugin / hook(s) in the current site
	 *
	 * @param string $plugin
	 * @param mixed $hook
	 * @return array
	 */	
    function get_rules($plugin = NULL, $hook = NULL)
    {
    	$result = array();

    	if ( ! is_null($plugin))
    	{
    		$this->db->where('plugin', $plugin);
    	}

    	if ( ! is_null($hook))
    	{
    		if ( is_array($hook))
    		{
    			$this->db->where_in('hook', $hook);
    		}
    		else
    		{
    			$this->db->where('hook', $hook);
    		}
    	}

    	$query = $this->db->from('stash_rules')
		 		 		  ->where('site_id', $this->site_id)
		 		 		  ->order_by('ord', 'asc')
		 		 		  ->get();

		if ($query->num_rows() > 0)
		{
			$result = $query->result_array();

			// make sure empty values are really NULL
			foreach($result as &$row)
			{
				if ( empty($row['scope']))
				{
					$row['scope'] = NULL;
				} 
				if ( empty($row['pattern']))
				{
					$row['pattern'] = NULL;
				} 
			}
		} 

		return $result; 		 
	}

	/**
	 * Update rules
	 *
	 * @param array $rules
	 * @return boolean
	 */
	function update_rules($rules)
	{	
		$this->db->where('site_id', $this->site_id);

		// remove rules for the current site
		if ($this->db->delete('stash_rules'))		 		   
		{	
			// insert new rules	for current site
			foreach($rules as $rule)
			{	
				// add the site id
				$rule['site_id'] = $this->site_id;

				if ( ! $this->db->insert('stash_rules', $rule))
				{
					return FALSE;
				}
			}
			return TRUE;
		}
		return FALSE;
	}

}

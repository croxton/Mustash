<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Mustash JSON responses class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2013, hallmarkdesign
 * @link		http://hallmark-design.co.uk/code/mustash
 * @since		1.0
 * @filesource 	./system/expressionengine/third_party/mustash/Mustash_json.php
 */
class Mustash_json
{
	/**
	 * database prefix
	 * @var string
	 */
	public $dbprefix = FALSE;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->settings = $this->EE->mustash_lib->get_settings();	
		$this->EE->load->helper('text');
		$this->dbprefix = $this->EE->db->dbprefix;
	}
	
	/**
	 * Creates the JSON for the variables list view
	 * @param int $perpage
	 * @param string $url_base
	 * @return string
	 */
	public function variables_ordering($perpage, $url_base, $piplength)
	{
		$col_map = array('id', 'key_name', 'created', 'expire', 'bundle_label', 'session_id');

		// register globals
		$keywords = ($this->EE->input->get_post('k_search')) ? $this->EE->input->get_post('k_search') : FALSE;
		$bundle_id = ($this->EE->input->get_post('bundle_id') && $this->EE->input->get_post('bundle_id') != '') ? $this->EE->input->get_post('bundle_id') : FALSE;
		$scope = ($this->EE->input->get_post('scope') && $this->EE->input->get_post('scope') != '') ? $this->EE->input->get_post('scope') : FALSE;
		$perpage = ($this->EE->input->get_post('perpage')) ? $this->EE->input->get_post('perpage') : $this->settings['list_limit'];
		$offset = ($this->EE->input->get_post('iDisplayStart')) ? $this->EE->input->get_post('iDisplayStart') : 0;
		$sEcho = $this->EE->input->get_post('sEcho');

		// where conditions
		$where = array(
			'keywords' 	=> $keywords,
			'bundle_id' => $bundle_id,
			'scope'		=> $scope,
		);
		
		// order / sort
		$order = array();
	
		if ($this->EE->input->get('iSortCol_0') !== FALSE)
		{
			for ( $i=0; $i < $this->EE->input->get('iSortingCols'); $i++ )
			{
				if (isset($col_map[$this->EE->input->get('iSortCol_'.$i)]))
				{
					$order[$col_map[$this->EE->input->get('iSortCol_'.$i)]] = ($this->EE->input->get('sSortDir_'.$i) == 'asc') ? 'asc' : 'desc';
				}
			}
		}
	
		$tdata = array();
		$i = 0;
	
		if (count($order) == 0)
		{
			$order = $this->dbprefix."stash.key_name ASC";
		}
		else
		{
			$sort = '';
			foreach($order AS $key => $value)
			{
				$sort = $key.' '.$value;
			}
			$order = $sort;
		}
		
		// the total count of variables overall
		$total = $this->EE->mustash_lib->get_total_variables();

		// the total count of variables being displayed
		$total_display	= $this->EE->mustash_lib->get_total_variables($where);

		// grab the variables
		$data = $this->EE->mustash_lib->get_variables($where, $perpage * $piplength, $offset, $order);

		// build the json response
		$j_response['sEcho'] = $sEcho;
		$j_response['iTotalRecords'] = $total;
		$j_response['iTotalDisplayRecords'] = $total_display;

		foreach ($data as $item)
		{
			$m[] = $item['id'];
			$m[] = '<a href="'.$url_base.'edit_variable&amp;id='.$item['id'].'" rel="'.addslashes($item['key_name']).'">'.$item['key_name'].'</a>';
			$m[] = stash_convert_timestamp($item['created']);
			$m[] = stash_convert_timestamp($item['expire']);
			$m[] = $item['bundle_label'];
			$m[] = ucfirst($item['scope']);
			$m[] = form_checkbox('toggle[]', $item['id'], '', ' class="toggle" id="delete_box_'.$item['id'].'"');
			$tdata[$i] = $m;
			$i++;
			unset($m);
		}
		$j_response['aaData'] = $tdata;

		return json_encode($j_response);
	}

	/**
	 * Creates the JSON for the bundles list view
	 * @param int $perpage
	 * @param string $url_base
	 * @return string
	 */
	public function bundles_ordering($perpage, $url_base, $piplength)
	{
		$col_map = array('id', 'bundle_label');

		// register globals
		$perpage = ($this->EE->input->get_post('perpage')) ? $this->EE->input->get_post('perpage') : $this->settings['list_limit'];
		$offset = ($this->EE->input->get_post('iDisplayStart')) ? $this->EE->input->get_post('iDisplayStart') : 0;
		$sEcho = $this->EE->input->get_post('sEcho');
		
		// order / sort
		$order = array();
	
		if ($this->EE->input->get('iSortCol_0') !== FALSE)
		{
			for ( $i=0; $i < $this->EE->input->get('iSortingCols'); $i++ )
			{
				if (isset($col_map[$this->EE->input->get('iSortCol_'.$i)]))
				{
					$order[$col_map[$this->EE->input->get('iSortCol_'.$i)]] = ($this->EE->input->get('sSortDir_'.$i) == 'asc') ? 'asc' : 'desc';
				}
			}
		}
	
		$tdata = array();
		$i = 0;
	
		if (count($order) == 0)
		{
			$order = $this->dbprefix."stash.bundle_label ASC";
		}
		else
		{
			$sort = '';
			foreach($order AS $key => $value)
			{
				$sort = $key.' '.$value;
			}
			$order = $sort;
		}
		
		// the total count of variables overall
		$total = $total_display = $this->EE->mustash_lib->get_total_bundles();

		// grab the variables
		$data = $this->EE->mustash_lib->get_bundles($perpage * $piplength, $offset, $order);

		// build the json response
		$j_response['sEcho'] = $sEcho;
		$j_response['iTotalRecords'] = $total;
		$j_response['iTotalDisplayRecords'] = $total_display;

		foreach ($data as $item)
		{
			$m[] = $item['id'];
			$m[] = ( $item['is_locked'] == 1 ? '<i class="stash_icon icon-lock"></i> ' : '').'<strong>'.$item['bundle_label'].'</strong>';
			$m[] = anchor($url_base.'variables&amp;bundle_id='.$item['id'], lang('view_variables')) . ' ('.$item['cnt'].')';
			$m[] = '<i class="stash_icon icon-refresh"></i> ' . anchor($url_base.'clear_cache&amp;bundle_id='.$item['id'], lang('delete_variables'));
			$m[] = ( $item['is_locked'] == 1 ? "&ndash;" : anchor($url_base.'edit_bundle&amp;bundle_id='.$item['id'], lang('edit_bundle')) );
			$m[] = ( $item['is_locked'] == 1 ? "&ndash;" : anchor($url_base.'delete_bundle_confirm&amp;bundle_id='.$item['id'], lang('delete_bundle')) );
			$tdata[$i] = $m;
			$i++;
			unset($m);
		}
		$j_response['aaData'] = $tdata;

		return json_encode($j_response);
	}
}
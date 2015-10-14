<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . 'mustash/config.php';

 /**
 * Mustash - control panel class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2014, hallmarkdesign
 * @link		http://hallmark-design.co.uk/code/mustash
 * @since		1.0
 * @filesource 	./system/expressionengine/third_party/mustash/mcp.mustash.php
 */
class mustash_mcp {

	public $url_base = '';
	public $perpage = '20';
	public $piplength = 4; // pipeline, for ajax pagination
	public $mod_name = MUSTASH_MOD_URL;

	protected $errors = array();
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		// load EE stuff
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		$this->EE->load->library('encrypt');

		// load Mustash libs
		$this->EE->load->library('mustash_lib');
		$this->EE->load->library('mustash_js');
		$this->EE->load->library('mustash_json');

		// load custom helper
		$this->EE->load->helper('mustash');

		$this->settings = $this->EE->mustash_lib->get_settings();

		$this->query_base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->mod_name.AMP.'method=';
		$this->url_base = BASE.AMP.$this->query_base;
		$this->EE->mustash_lib->set_url_base($this->url_base);

		// load local assets
		$this->EE->mustash_lib->load_assets(
			array(
				'styles/mustash.css',
			)
		);	

		// load external assets
		$this->EE->cp->add_to_head(
			NL."<!-- {$this->mod_name} assets -->".NL.
			'<link href="//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css" rel="stylesheet">'.
			NL."<!-- / {$this->mod_name} assets -->".NL
		);
	}
	
	public function index()
	{
		$this->EE->functions->redirect($this->url_base.'variables');
		exit;
	}	
	
	public function variables()
	{	
		$vars = array();
		$where = array();

		// register globals
		$bundle_id 	= intval($this->EE->input->get_post('bundle_id'));
		$scope 		= $this->EE->input->get_post('scope');
		$offset 	= intval($this->EE->input->get_post('offset'));

		if ($bundle_id)
		{
			$where = array('bundle_id' => $bundle_id);
		}

		if ($scope === 'site' || $scope === 'user')
		{
			$where = array('scope' => $scope);
		}
		else
		{
			$scope = '';
		}

		// get an unfiltered list of variables
		$vars['variables'] = $this->EE->mustash_lib->get_variables($where, $this->settings['list_limit'], $offset);
		$total = $this->EE->mustash_lib->get_total_variables();

		// include dependent js plugin(s)
		$this->EE->cp->add_js_script(array('plugin' => 'dataTables'));

		// set the page title and right nav
		$this->_set_variable('cp_page_title', $this->EE->lang->line('variables'));
		$this->EE->cp->set_right_nav($this->EE->mustash_lib->variables_right_menu());

		// inject the custom javascript for ajax filtering/sorting with datatables
		$js = $this->EE->mustash_js->get_variables_datatables(
			'variables_ajax_filter', 
			$this->piplength, 
			$this->settings['list_limit'],
			'"aaSorting": [[ 1, "asc" ]],'
		);
		$this->EE->javascript->output($js);		
		$this->EE->javascript->compile();

		// create pagination (fallback)
		$this->EE->load->library('pagination');
		$vars['pagination'] = $this->EE->mustash_lib->create_pagination(
				__FUNCTION__, 
				$total, 
				$this->settings['list_limit']
		);

		// set view variables
		$vars['total_entries'] = $total;
		$vars['perpage'] = $this->settings['list_limit'];
		$vars['date_selected'] = '';
		$vars['keywords'] = '';
		$vars['bundle_id'] = $bundle_id;
		$vars['scope'] = $scope;
		$vars['perpage_select_options'] = $this->EE->mustash_lib->perpage_select_options();
		$vars['scope_select_options'] = $this->EE->mustash_lib->scope_select_options();
		$vars['bundle_select_options'] = $this->EE->mustash_lib->bundle_select_options();

		// render the view
		return $this->_load_view('variables', $vars);			
	}
	
	public function variables_ajax_filter()
	{
		die($this->EE->mustash_json->variables_ordering($this->perpage, $this->url_base, $this->piplength));
	}


	public function edit_variable()
	{	
		// get the variable id
		if ( ! $id = intval($this->EE->input->get('id')))
		{
			$this->EE->functions->redirect($this->url_base.'variables');
			exit;
		}

		// get the variable data
		if ( ! $vars = $this->EE->mustash_lib->get_variable($id))
		{
			// var doesn't exist, bail
			$this->EE->functions->redirect($this->url_base.'variables');
			exit;
		}

		// update the variable?
		if(isset($_POST['update_variable']))
		{	
			$data = array(
				'key_name'	 => $vars['key_name'],
				'bundle_id'	 => $vars['bundle_id'],
				'parameters' => $this->EE->input->post('parameters')
			);

			if($this->EE->mustash_lib->update_variable($id, $data))
			{	
				$this->EE->logger->log_action($this->EE->lang->line('log_variable_updated'));
				$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('update_success'));
				$this->EE->functions->redirect($this->url_base.'variables');		
				exit;			
			}
			else
			{
				$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('update_fail'));
				$this->EE->functions->redirect($this->url_base.'variables');	
				exit;					
			}
		}

		// set the page title and right nav
		$this->_set_variable('cp_page_title', $this->EE->lang->line('edit_variable'));
		$this->EE->cp->set_right_nav($this->EE->mustash_lib->variables_right_menu());
	
		// render the view
		return $this->_load_view('edit_variable', $vars);		
	}

	public function delete_variables_confirm()
	{	
		// get the selected variables
		if( ! $damned = $this->EE->input->post('toggle'))
		{
			$this->EE->functions->redirect($this->url_base.'variables');
			exit;
		}

		$vars = array(
			'message' => $this->EE->lang->line('delete_confirm_message'),
		);

		// get data for the doomed variables
		$vars['variables'] = $this->EE->mustash_lib->get_variables(array('id' => $damned), 0, 0);

		// set the page title
		$this->_set_variable('cp_page_title', $this->EE->lang->line('clear_cache'));
		$this->EE->cp->set_right_nav($this->EE->mustash_lib->variables_right_menu());

		// render the view
		return $this->_load_view('delete_variables_confirm', $vars);	
	}


	public function delete_variables()
	{
		// get the selected variables
		if( ! $damned = $this->EE->input->post('toggle'))
		{
			$this->EE->functions->redirect($this->url_base.'variables');
			exit;
		}

		// delete them
		if ( $this->EE->mustash_lib->clear_variables($damned))
		{
			// successfully deleted all variables
			$this->EE->logger->log_action($this->EE->lang->line('log_variable_deleted'));
			$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('delete_success'));
		}
		else
		{
			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('delete_fail'));
		}
		
		$this->EE->functions->redirect($this->url_base.'variables');		
		exit;
	}	

	public function clear_cache_confirm()
	{	
		// set the page title
		$this->_set_variable('cp_page_title', $this->EE->lang->line('clear_cache'));
		$this->EE->cp->set_right_nav($this->EE->mustash_lib->variables_right_menu());

		$vars = array();

		// soft delete period?
		$vars['invalidate'] = $this->EE->config->item('stash_invalidation_period') ? $this->EE->config->item('stash_invalidation_period') : 300;

		// render the view
		return $this->_load_view('clear_cache_confirm', $vars);	
	}

	public function clear_cache()
	{
		// get the selected scope
		$scope = $this->EE->input->get_post('scope');

		// get selected bundle
		$bundle_id = $this->EE->input->get_post('bundle_id');

		// soft delete?
		$invalidate = 0;
		if ($this->EE->input->get_post('soft_delete'))
		{
			$value = $this->EE->input->get_post('invalidate');

			if (is_numeric($value) && $value > 0 && $value == round($value))
			{
				$invalidate = $value;
			}
		}

		if( ! $scope && ! $bundle_id)
		{
			$this->EE->functions->redirect($this->url_base.'variables');
			exit;
		}

		// do we want to flush the entire cache, immediately?
		if ($scope == 'all' && ! $bundle_id && $invalidate == 0)
		{
			// yes, we'll delete everything for this site
			if ( $this->EE->mustash_lib->flush_cache($this->EE->config->item('site_id')))
			{
				$this->EE->logger->log_action($this->EE->lang->line('log_clear_cache'));
				$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('clear_success'));
			}
			else
			{
				$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('clear_fail'));
			}

		}
		else
		{
			// no, we'll clear the cached items individually
			if ( $this->EE->mustash_lib->clear_matching_variables($bundle_id, $scope, NULL, $invalidate))
			{
				$this->EE->logger->log_action($this->EE->lang->line('log_clear_cache'));
				$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('clear_success'));
			}
			else
			{
				$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('clear_fail'));
			}
		}

		// after cache has cleared, redraw the variables screen with appropriate filters selected
		$redirect = $this->url_base.'variables';

		if ($bundle_id)
		{
			$redirect = $this->url_base.'bundles';
		}

		if ($scope === 'user' || $scope === 'site')
		{
			$redirect .= "&amp;scope=".$scope;
		}

		$this->EE->functions->redirect($redirect);	
		exit;
	}

	/* this is arguably bad UI - do we really need to prompt user? Probably not

	public function delete_bundle_vars_confirm()
	{	
		// get the selected bundle to clear
		if( ! $bundle_id = $this->EE->input->get('bundle_id'))
		{
			$this->EE->functions->redirect($this->url_base.'bundles');
			exit;
		}

		// hydrate the bundle
		if ($vars = $this->EE->mustash_lib->get_bundle($bundle_id))
		{
			$vars += array(
				'message' 	=> $this->EE->lang->line('delete_bundle_vars_confirm_message')
			);

			// set the page title
			$this->_set_variable('cp_page_title', $this->EE->lang->line('clear_cache'));

			// render the view
			return $this->_load_view('delete_bundle_vars_confirm', $vars);	
		}
		else
		{
			$this->EE->functions->redirect($this->url_base.'bundles');
			exit;
		}
	}
	*/

	public function bundles()
	{	
		if ( ! $this->EE->mustash_lib->can_access('bundles'))
		{
			show_error(lang('unauthorized_access'));
		}

		$vars = array();

		// register globals
		$offset = intval($this->EE->input->get_post('offset'));

		// get a list of bundles
		$vars['bundles'] = $this->EE->mustash_lib->get_bundles($this->settings['list_limit'], $offset);
		$total = $this->EE->mustash_lib->get_total_bundles();

		// include dependent js plugin(s)
		$this->EE->cp->add_js_script(array('plugin' => 'dataTables'));

		// set the page title and right nav
		$this->_set_variable('cp_page_title', $this->EE->lang->line('bundles'));

		$this->EE->cp->set_right_nav($this->EE->mustash_lib->bundles_right_menu());	

		// inject the custom javascript for ajax filtering/sorting with datatables
		$js = $this->EE->mustash_js->get_bundles_datatables(
			'bundles_ajax_filter', 
			$this->piplength, 
			$this->settings['list_limit'],
			'"aaSorting": [[ 1, "asc" ]],'
		);
		$this->EE->javascript->output($js);		
		$this->EE->javascript->compile();

		// create pagination
		$this->EE->load->library('pagination');
		$vars['pagination'] = $this->EE->mustash_lib->create_pagination(
				__FUNCTION__, 
				$total, 
				$this->settings['list_limit']
		);

		// set view variables
		$vars['total_entries'] = $total;
		$vars['perpage'] = $this->settings['list_limit'];
		$vars['date_selected'] = '';
		$vars['keywords'] = '';

		// render the view
		return $this->_load_view('bundles', $vars);			
	}


	public function bundles_ajax_filter()
	{
		die($this->EE->mustash_json->bundles_ordering($this->perpage, $this->url_base, $this->piplength));
	}


	public function add_bundle()
	{
		if ( ! $this->EE->mustash_lib->can_access('bundles'))
		{
			show_error(lang('unauthorized_access'));
		}

		// insert the variable?
		if(isset($_POST['insert_bundle']))
		{	
			$data = array(
				'bundle_name'  => $this->EE->input->post('bundle_name'),
				'bundle_label' => $this->EE->input->post('bundle_label')
			);

			if ( ! $data['bundle_name'])
			{
				$this->errors[] = lang('error_missing_bundle_name');
			}
			elseif( ! preg_match('/^[a-z0-9\-_:]+$/i', str_replace('%s', '', $data['bundle_name'])))
			{
				$this->errors[] = lang('error_invalid_bundle_name');
			}
			elseif ( ! $this->EE->mustash_lib->is_bundle_name_unique($data['bundle_name']))
			{
				$this->errors[] = lang('error_non_unique_bundle_name');
			}

			if ( ! $data['bundle_label'])
			{
				$this->errors[] = lang('error_missing_bundle_label');
			}

			if ( empty ($this->errors))
			{
				// remove spaces
				$data['bundle_name'] = str_replace('%s', '', $data['bundle_name']);

				if($this->EE->mustash_lib->add_bundle($data))
				{	
					$this->EE->logger->log_action($this->EE->lang->line('log_bundle_added'));
					$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('add_success'));
					$this->EE->functions->redirect($this->url_base.'bundles');		
					exit;			
				}
				else
				{
					$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('add_fail'));
					$this->EE->functions->redirect($this->url_base.'bundles');	
					exit;					
				}
			}
		}

		// set the page title and right nav
		$this->_set_variable('cp_page_title', $this->EE->lang->line('add_bundle'));
		$this->EE->cp->set_right_nav($this->EE->mustash_lib->bundles_right_menu());

		// render the view
		return $this->_load_view('edit_bundle');	
	}


	public function edit_bundle()
	{	
		if ( ! $this->EE->mustash_lib->can_access('bundles'))
		{
			show_error(lang('unauthorized_access'));
		}

		// get the variable
		if ( ! $id = intval($this->EE->input->get('bundle_id')))
		{
			$this->EE->functions->redirect($this->url_base.'bundles');
			exit;
		}

		// get data for the requested bundle
		$vars = $this->EE->mustash_lib->get_bundle($id);

		if ( ! $vars || ( isset( $vars['is_locked']) && $vars['is_locked'] === '1') ) 
		{
			// var doesn't exist or is locked
			$this->EE->functions->redirect($this->url_base.'bundles');
		}

		// update the bundle?
		if(isset($_POST['update_bundle']))
		{	
			$data = array(
				'bundle_name'  => $this->EE->input->post('bundle_name'),
				'bundle_label' => $this->EE->input->post('bundle_label')
			);

			if ( ! $data['bundle_name'])
			{
				$this->errors[] = lang('error_missing_bundle_name');
			}
			elseif( ! preg_match('/^[a-z0-9\-_:]+$/i', str_replace('%s', '', $data['bundle_name'])))
			{
				$this->errors[] = lang('error_invalid_bundle_name');
			}
			elseif( $vars['bundle_name'] !== $data['bundle_name'] )
			{
				// if the name has been edited, let's check that the new name is unique
				if ( ! $this->EE->mustash_lib->is_bundle_name_unique($data['bundle_name']))
				{
					$this->errors[] = lang('error_non_unique_bundle_name');
				}
			}

			if ( ! $data['bundle_label'])
			{
				$this->errors[] = lang('error_missing_bundle_label');
			}

			if ( empty ($this->errors))
			{
				// remove spaces
				$data['bundle_name'] = str_replace('%s', '', $data['bundle_name']);

				if($this->EE->mustash_lib->update_bundle($id, $data))
				{	
					$this->EE->logger->log_action($this->EE->lang->line('log_bundle_updated'));
					$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('update_success'));
					$this->EE->functions->redirect($this->url_base.'bundles');		
					exit;			
				}
				else
				{
					$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('update_fail'));
					$this->EE->functions->redirect($this->url_base.'bundles');	
					exit;					
				}
			}
		}

		// set the page title and right nav
		$this->_set_variable('cp_page_title', $this->EE->lang->line('edit_bundle'));
		$this->EE->cp->set_right_nav($this->EE->mustash_lib->bundles_right_menu());
	
		// render the view
		return $this->_load_view('edit_bundle', $vars);
	}


	public function delete_bundle_confirm()
	{
		if ( ! $this->EE->mustash_lib->can_access('bundles'))
		{
			show_error(lang('unauthorized_access'));
		}

		// get the selected bundle to clear
		if( ! $bundle_id = intval($this->EE->input->get('bundle_id')) )
		{
			$this->EE->functions->redirect($this->url_base.'bundles');
			exit;
		}

		// hydrate the bundle
		if ($vars = $this->EE->mustash_lib->get_bundle($bundle_id))
		{
			$vars += array(
				'message' 	=> $this->EE->lang->line('delete_bundle_confirm_message')
			);

			// set the page title
			$this->_set_variable('cp_page_title', $this->EE->lang->line('delete_bundle'));
			$this->EE->cp->set_right_nav($this->EE->mustash_lib->bundles_right_menu());

			// render the view
			return $this->_load_view('delete_bundle_confirm', $vars);	
		}
		else
		{
			$this->EE->functions->redirect($this->url_base.'bundles');
			exit;
		}
	}

	public function delete_bundle()
	{
		if ( ! $this->EE->mustash_lib->can_access('bundles'))
		{
			show_error(lang('unauthorized_access'));
		}

		// get the selected variables
		if( ! $bundle_id = intval( $this->EE->input->post('bundle_id') ))
		{
			$this->EE->functions->redirect($this->url_base.'bundles');
			exit;
		}

		// delete the bundle
		if ( ! $this->EE->mustash_lib->delete_bundle($bundle_id))
		{
			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('delete_fail'));
		}
		else
		{
			// successfully deleted
			$this->EE->logger->log_action($this->EE->lang->line('log_bundle_deleted'));
			$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('delete_success'));
		}

		$this->EE->functions->redirect($this->url_base.'bundles');		
		exit;
	}	


	public function rules()
	{
		if ( ! $this->EE->mustash_lib->can_access('rules'))
		{
			show_error(lang('unauthorized_access'));
		}

		$vars = array();

		if(isset($_POST['update_mustash_rules']))
		{	
			// update rules
			$hook  		= $this->EE->input->post('hook');
			$group  	= $this->EE->input->post('group');
			$bundle 	= $this->EE->input->post('bundle');
			$scope 		= $this->EE->input->post('scope');
			$pattern 	= $this->EE->input->post('pattern');

			$rules = array();

			// format the ruleset
			if ($hook)
			{
				foreach($hook as $index => $trigger)
				{
					if ( $trigger != "NULL" )
					{
						$trigger = explode('--', $trigger);

						$rules[] = array(
							'plugin' 	=> $trigger[0],
							'hook' 	 	=> $trigger[1],
							'group_id' 	=> ($group[$index]  == "NULL" ? NULL : $group[$index]),
							'bundle_id' => ($bundle[$index] == "NULL" ? NULL : $bundle[$index]), 
							'scope'  	=> ($scope[$index]  == "NULL" ? NULL : $scope[$index]), 
							'pattern'	=> $pattern[$index],
							'ord'		=> $index
						);
					}
				}
			}

			// update ruleset
			if($this->EE->mustash_lib->update_rules($rules))
			{	
				$this->EE->logger->log_action($this->EE->lang->line('log_rules_updated'));
				$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('update_success'));		
			}
			else
			{
				$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('update_fail'));					
			}

			// redirect
			$this->EE->functions->redirect($this->url_base.'rules');	
			exit;
		}


		// get existing rules
		$vars['rules'] = $this->EE->mustash_lib->get_rules();

		// show the first row if there are no rules
		if ( empty($vars['rules']))
		{
			$vars['rules'][] = array(
				'plugin' 	=> '',
				'hook' 		=> '',
				'group_id' 	=> '',
				'bundle_id' => '',
				'scope' 	=> '',
				'pattern' 	=> ''
			);
		}

		// get a list of installed plugins
		$vars['plugins'] = $this->EE->mustash_lib->get_installed_plugins();

		// list of scope
		$vars['scope_select_options'] = $this->EE->mustash_lib->scope_select_options('--', 'NULL');
		$vars['bundle_select_options'] = $this->EE->mustash_lib->bundle_select_options('--', 'NULL');


		// -------------------------------------
		//  Load CSS and JS
		// -------------------------------------

		// include jquery ui
		#$this->EE->cp->add_js_script(array('ui' => array('core')));

		$this->EE->mustash_lib->load_assets(
			array(
				'scripts/jquery.dynoTable.js',
				'scripts/jquery.chained.js',
				'scripts/stash_rules.js'
			)
		);

		// set the page title and right nav
		$this->_set_variable('cp_page_title', $this->EE->lang->line('rules'));

		return $this->_load_view('rules', $vars);
	}
	
	public function settings()
	{
		if ( ! $this->EE->mustash_lib->can_access('settings'))
		{
			show_error(lang('unauthorized_access'));
		}

		if(isset($_POST['update_mustash_settings']))
		{
			// validate
			if( $this->EE->input->post('license_number'))
			{
				// do update		
				if($this->EE->mustash_lib->update_settings($_POST))
				{	
					$this->EE->logger->log_action($this->EE->lang->line('log_settings_updated'));
					$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('settings_updated'));
					$this->EE->functions->redirect($this->url_base.'settings');		
					exit;			
				}
				else
				{
					$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('settings_update_fail'));
					$this->EE->functions->redirect($this->url_base.'settings');	
					exit;					
				}
			}
			else
			{
				$this->errors[] = lang('error_missing_license_number');
			}
		}
		
		$this->_set_variable('cp_page_title', $this->EE->lang->line('stash_settings'));
		$this->EE->cp->set_right_nav($this->EE->mustash_lib->settings_right_menu());
		
		$vars = array(
			'settings' => $this->settings
		);

		// decrypt API key
		$vars['api_key'] = $this->EE->encrypt->decode($this->settings['api_key']);

		// API URL
		$vars['api_url'] = $this->EE->config->config['site_url']
						  .'?ACT='
						  .$this->EE->cp->fetch_action_id('Mustash', 'api')
						  .'&key='.$vars['api_key']
						  .'&hook=[your hook here]';

		$vars['api_url_prune'] = $this->EE->config->config['site_url']
						  .'?ACT='
						  .$this->EE->cp->fetch_action_id('Mustash', 'api')
						  .'&key='.$vars['api_key']
						  .'&prune=1';

		// --------------------------------------------------------
		//  Get member groups, excluding superadmin, guests, pending and banned
		// --------------------------------------------------------

		$query = $this->EE->db->select('group_id, group_title')
		       ->from('member_groups')
		       ->where_not_in('group_id', array(1, 2, 3, 4))
		       ->order_by('group_title', 'asc')
		       ->get();

		$vars['member_groups'] = stash_array_column($query->result_array(), 'group_title', 'group_id');

		// --------------------------------------------------------
		// Look for plugins
		// --------------------------------------------------------
		$plugins = $this->EE->mustash_lib->get_all_plugins();

		$vars['plugin_options'] = array();

		foreach($plugins as $p)
		{
			$vars['plugin_options'][$p] = $this->EE->mustash_lib->plugin($p)->name;
		}

		// -------------------------------------
		//  Load CSS and JS
		// -------------------------------------

		$this->EE->mustash_lib->load_assets(
			array(
				'scripts/tagmanager.js',
				'styles/tagmanager.css'
			)
		);

		return $this->_load_view('settings', $vars);
	}


	// --------------------------------------------------------------------
  
	public function rewrite()
	{
		if ( ! $this->EE->mustash_lib->can_access('settings'))
		{
			show_error(lang('unauthorized_access'));
		}

		// Set title
		$this->_set_variable('cp_page_title', $this->EE->lang->line('stash_rewrite_rules'));
		$this->EE->cp->set_right_nav($this->EE->mustash_lib->settings_right_menu());

		if ( $this->EE->config->item('stash_static_basepath') && $this->EE->config->item('stash_static_url'))
		{
			// Get config items, tidy up for .htaccess
			$vars = array(
				'cache_path' => str_replace(' ', '\ ', rtrim($this->EE->config->item('stash_static_basepath'), ' \t./').'/'),
		  		'cache_url'  => rtrim($this->EE->config->item('stash_static_url'), ' \t./').'/'
			);
		  
		  	// Get sites
		  	$query = $this->EE->db->get('sites');
		  	$vars['sites'] = $query->result();

		  	return $this->_load_view('rewrite', $vars);
		}
		else
		{
			$this->errors[] = lang('error_missing_static_config');
			return $this->_load_view('errors');
		}
	}


	// --------------------------------------------------------------------

	private function _load_view($view, $vars=array())
	{
		// set common view variables
		$vars['url_base'] = $this->url_base;
		$vars['query_base'] = $this->query_base;	
		$vars['settings'] = $this->settings;
		$vars['errors'] = $this->errors;
		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->mod_name, $this->EE->lang->line('mustash_module_name'));

		// load the view
		return $this->EE->load->view($view, $vars, TRUE);
	}

	private function _set_variable($var, $value)
	{
        if (version_compare(APP_VER, '2.6', '>=')) 
        {
            $this->EE->view->$var = $value;
        } 
        else 
        {
            $this->EE->cp->set_variable($var, $value);
        } 
	}

}
// END CLASS

/* End of file mcp.mustash.php */
/* Location: ./system/expressionengine/third_party/mustash/mcp.mustash.php */
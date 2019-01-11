<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use EllisLab\ExpressionEngine\Library\CP\Table;

/**
 * Mustash - control panel class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2015, hallmarkdesign
 * @link		https://github.com/croxton/Stash/wiki/Mustash
 * @since		1.0
 * @filesource 	./system/user/addons/mustash/mcp.mustash.php
 */
class mustash_mcp {

	public $url_base = '';

	protected $errors = array();
	
	public function __construct()
	{
		// load EE stuff
		ee()->load->library('encrypt');

		// load Mustash dependencies
		ee()->load->library('mustash_lib');
		ee()->load->helper('mustash');

		$this->settings = ee()->mustash_lib->get_settings();
		$this->url_base = 'addons/settings/mustash';

		// load local assets
		ee()->mustash_lib->load_assets(
			array(
				'mustash.css',
			)
		);	
	}
	
	public function index()
	{
		return $this->variables();
	}	
	
	public function variables()
	{	
	 	/* ----------------------------------------------------------------------------- 
     	   Bulk actions
     	   ----------------------------------------------------------------------------- */
		if (ee()->input->post('bulk_action') == 'remove')
		{
			$this->delete_variables(ee()->input->post('toggle'));
		}

		/* ----------------------------------------------------------------------------- 
     	   Setup defaults
     	   ----------------------------------------------------------------------------- */
		$base_url = ee('CP/URL', $this->url_base);
		$vars = array();
		$where = array();
		$order = FALSE;

	 	/* ----------------------------------------------------------------------------- 
     	   Add filters
     	   ----------------------------------------------------------------------------- */

		// scope filter
		$scope_filter_options = ee()->mustash_lib->scope_select_options();
		$scope_filter = ee('CP/Filter')->make('scope', 'filter_by_scope', $scope_filter_options);
		$scope_filter->disableCustomValue();

		// bundle filter
		$bundle_filter_options = ee()->mustash_lib->bundle_select_options();
		$bundle_filter = ee('CP/Filter')->make('bundle_id', 'filter_by_bundle', $bundle_filter_options);
		$bundle_filter->disableCustomValue();

		// build filters and render
		$filters = ee('CP/Filter')
			->add($scope_filter)
			->add($bundle_filter);

		$vars['filters'] = $filters->render($base_url);


	 	/* ----------------------------------------------------------------------------- 
     	   Register globals
     	   ----------------------------------------------------------------------------- */

		// bundle ID
		$bundle_id 	= ((int) ee()->input->get('bundle_id')) ?: 0;

		// scope
		$scope 		= ee()->input->get('scope');
		if ( ! in_array($scope, array('user', 'site')) )
		{
			$scope = FALSE;
		}

		// sort column
		$sort_col 	= ee()->input->get('sort_col');
		if ( ! in_array($sort_col, array('id', 'key_name', 'created', 'expire', 'bundle_name', 'session_id')) )
		{
			$sort_col = 'key_name';
		}

		// sort direction
		$sort_dir 	= ee()->input->get('sort_dir');
		if ( ! in_array($sort_dir, array('asc', 'desc')) )
		{
			$sort_dir = 'asc';
		}

		// pagination
		$page 		= ((int) ee()->input->get('page')) ?: 1;
		$perpage 	= $this->settings['list_limit'];
		$offset 	= ($page - 1) * $perpage; // Offset is 0 indexed

		// add filters
		if ($bundle_id)
		{
			$where += array('bundle_id' => $bundle_id);
			$base_url->setQueryStringVariable('bundle_id', $bundle_id);
		}

		if ($scope)
		{
			$where += array('scope' => $scope);
			$base_url->setQueryStringVariable('scope', $scope);
		}

		// keywords
		if ($search = ee()->input->get_post('search', TRUE))
		{
			$where += array('keywords' => $search);
			$base_url->setQueryStringVariable('search', $search);
			$vars['search'] = htmlentities($search);
		}

		if ($sort_col && $sort_dir)
		{
			$order = array($sort_col => $sort_dir);
			$base_url->setQueryStringVariable('sort_col', $sort_col);
			$base_url->setQueryStringVariable('sort_dir', $sort_dir);
		}

	 	/* ----------------------------------------------------------------------------- 
     	   Generate data
     	   ----------------------------------------------------------------------------- */

		// get a filtered list of variables
		$variables = ee()->mustash_lib->get_variables($where, $perpage, $offset, $order);

		// get total count of filtered variables
		$total = ee()->mustash_lib->get_total_variables($where, $perpage, $offset, $order);


		/* ----------------------------------------------------------------------------- 
     	   Pagination
     	   ----------------------------------------------------------------------------- */

		$vars['pagination'] = ee('CP/Pagination', $total)
			->perPage($perpage)
			->currentPage($page)
			->render($base_url);

		/* ----------------------------------------------------------------------------- 
     	   Construct table
     	   ----------------------------------------------------------------------------- */

		$table = ee('CP/Table', array(
			'limit'			=> $perpage,
			'sort_col'		=> $sort_col,
			'sort_dir'		=> $sort_dir
		));
		$table->setColumns(
			array(
				'id',
				'key_name',
				'created',
				'expire',
				'bundle_name',
				'session_id' => array(
					'encode' => FALSE
				),
				array(
			      'type'  => Table::COL_CHECKBOX
			    )
			)
		);

		$table->setNoResultsText('no_matching_variables');

		$data = array();
		if(count($variables) >= '1')
		{
			foreach($variables as $v)
			{		
				$data[] = array(
					$v['id'],

					array(
						'href' => ee('CP/URL', $this->url_base.'/edit_variable')->setQueryStringVariable('id', $v['id']),
	        			'content' => $v['key_name']
					),

					html_entity_decode(stash_convert_timestamp($v['created'])),
					html_entity_decode(stash_convert_timestamp($v['expire'])),
					$v['bundle_label'],
					$v['scope'] == 'site' 
						? '<span class="st-info">'.ucfirst($v['scope']).'</span>' 
						: '<span class="st-draft">'.ucfirst($v['scope']).'</span>',
					array(
						'name'  => 'toggle[]',
						'value' => $v['id'],
						'data' => array(
							'confirm' => lang('variable') . ': <b>' . htmlentities($v['key_name'], ENT_QUOTES) . '</b>'
						)
					)
				);
			}
		}
		
		$table->setData($data);

		/* ----------------------------------------------------------------------------- 
     	   Confirm remove modal
     	   ----------------------------------------------------------------------------- */

		ee()->javascript->set_global('lang.remove_confirm', lang('entry') . ': <b>### ' . lang('entries') . '</b>');
		ee()->cp->add_js_script(array(
			'file' => array(
				'cp/confirm_remove',
			),
		));
	
		/* ----------------------------------------------------------------------------- 
     	   Construct view
     	   ----------------------------------------------------------------------------- */

		// body	
		$vars['cp_heading'] = lang('variables');
		$vars['cp_subheading'] = sprintf(lang('found_variables_total'), $total);
		$vars['total'] = $total;

		// rendered table
		$tableData = $table->viewData($base_url);

		$view = ee('View')->make('_shared/table');
		$vars['table'] = $view->render($tableData);

		// urls
		$vars['form_url'] = $tableData['base_url'];
		$vars['clear_cache_url'] = ee('CP/URL', $this->url_base.'/clear_cache_confirm');

		return $this->_load_view(
			'mustash:variables', 
			$vars, 
			lang('variables')
		);
	}

	public function edit_variable()
	{	
		// get the variable id
		if ( ! $id = (int) ee()->input->get('id') )
		{
			ee()->functions->redirect( ee('CP/URL', $this->url_base) );
			exit;
		}

		// get the variable data
		if ( ! $vars = ee()->mustash_lib->get_variable($id))
		{
			// var doesn't exist, bail
			ee()->functions->redirect( ee('CP/URL', $this->url_base) );
			exit;
		}

		// update the variable?
		if(isset($_POST['update_variable']))
		{	
			$data = array(
				'key_name'	 => $vars['key_name'],
				'bundle_id'	 => $vars['bundle_id'],
				'parameters' => ee()->input->post('parameters')
			);

			if(ee()->mustash_lib->update_variable($id, $data))
			{	
				// updated successfully
				ee()->logger->log_action(ee()->lang->line('log_variable_updated'));

				ee('CP/Alert')->makeInline('entries-form')
					->asSuccess()
					->withTitle(lang('success'))
					->addToBody(lang('update_success'))
					->defer();

				ee()->functions->redirect( ee('CP/URL', $this->url_base) );		
				exit;			
			}
			else
			{
				// failed to update
				ee('CP/Alert')->makeInline('entries-form')
					->asWarning()
					->withTitle(lang('warning'))
					->addToBody(lang('update_fail'))
					->defer();

				ee()->functions->redirect( ee('CP/URL', $this->url_base) );	
				exit;					
			}
		}

		// add form URL
		$vars['form_url'] = ee('CP/URL', $this->url_base.'/edit_variable')->setQueryStringVariable('id', $id);
	
		// render the view
		return $this->_load_view(
			'mustash:edit_variable', 
			$vars, 
			lang('edit_variable')
		);		
	}

	public function delete_variables($damned)
	{
		// delete them
		if ( ee()->mustash_lib->clear_variables($damned))
		{
			// successfully deleted all variables
			ee()->logger->log_action(ee()->lang->line('log_variable_deleted'));

			ee('CP/Alert')->makeInline('entries-form')
				->asSuccess()
				->withTitle(lang('success'))
				->addToBody(lang('delete_success'))
				->defer();
		}
		else
		{
			ee('CP/Alert')->makeInline('entries-form')
				->asWarning()
				->withTitle(lang('warning'))
				->addToBody(lang('delete_fail'))
				->defer();
		}
		
		ee()->functions->redirect( ee('CP/URL', $this->url_base, ee()->cp->get_url_state()) );		

	}	

	public function clear_cache_confirm()
	{	
		$vars = array();

		// add form URL
		$vars['form_url'] = ee('CP/URL', $this->url_base.'/clear_cache');

		// soft delete period?
		$vars['invalidate'] = ee()->config->item('stash_invalidation_period') ? ee()->config->item('stash_invalidation_period') : 300;

		return $this->_load_view(
			'mustash:clear_cache_confirm', 
			$vars, 
			lang('clear_cache')
		);
	}

	public function clear_cache()
	{
		// bundle ID
		$bundle_id = ((int) ee()->input->get_post('bundle_id')) ? : FALSE;

		// scope
		$scope = ee()->input->get_post('scope');
		if ( ! in_array($scope, array('all', 'user', 'site')) )
		{
			$scope = FALSE;
		}

		// soft delete?
		$invalidate = 0;
		if (ee()->input->get_post('soft_delete') == 'y')
		{
			$value = ee()->input->get_post('invalidate');

			if (is_numeric($value) && $value > 0 && $value == round($value))
			{
				$invalidate = $value;
			}
		}

		if( ! $scope && ! $bundle_id)
		{
			ee()->functions->redirect( ee('CP/URL', $this->url_base) );
			exit;
		}

		// do we want to flush the entire cache, immediately?
		if ($scope == 'all' && ! $bundle_id && $invalidate == 0)
		{
			// yes, we'll delete everything for this site
			if ( ee()->mustash_lib->flush_cache(ee()->config->item('site_id')))
			{
				ee()->logger->log_action(ee()->lang->line('log_clear_cache'));

				ee('CP/Alert')->makeInline('entries-form')
					->asSuccess()
					->withTitle(lang('success'))
					->addToBody(lang('clear_success'))
					->defer();
			}
			else
			{
				ee('CP/Alert')->makeInline('entries-form')
					->asWarning()
					->withTitle(lang('warning'))
					->addToBody(lang('clear_fail'))
					->defer();
			}
		}
		else
		{
			// no, we'll clear the cached items individually
			if ( ee()->mustash_lib->clear_matching_variables($bundle_id, $scope, NULL, $invalidate))
			{
				ee()->logger->log_action(ee()->lang->line('log_clear_cache'));

				ee('CP/Alert')->makeInline('entries-form')
					->asSuccess()
					->withTitle(lang('success'))
					->addToBody(lang('clear_success'))
					->defer();
			}
			else
			{
				ee('CP/Alert')->makeInline('entries-form')
					->asWarning()
					->withTitle(lang('warning'))
					->addToBody(lang('clear_fail'))
					->defer();
			}
		}

		// after cache has cleared, redraw the variables screen with appropriate filters selected
		$redirect = ee('CP/URL', $this->url_base);

		if ($bundle_id)
		{
			$redirect = ee('CP/URL', $this->url_base.'/bundles');
		}

		if ($scope === 'user' || $scope === 'site')
		{
			$redirect->setQueryStringVariable('scope', $scope);
		}

		ee()->functions->redirect($redirect);	
	}

	public function bundles()
	{

		/* ----------------------------------------------------------------------------- 
     	   Access control
     	   ----------------------------------------------------------------------------- */

		if ( ! ee()->mustash_lib->can_access('bundles'))
		{
			show_error(lang('unauthorized_access'));
		}

		/* ----------------------------------------------------------------------------- 
     	   Bulk actions
     	   ----------------------------------------------------------------------------- */

		if (ee()->input->post('bulk_action') == 'remove')
		{
			$this->_delete_bundles(ee()->input->post('toggle'));
		}

		/* ----------------------------------------------------------------------------- 
     	   Setup defaults
     	   ----------------------------------------------------------------------------- */
		$base_url = ee('CP/URL', $this->url_base.'/bundles');
		$vars = array();
		$where = array();
		$order = FALSE;

		/* ----------------------------------------------------------------------------- 
     	   Register globals
     	   ----------------------------------------------------------------------------- */

		// pagination
		$page 		= ((int) ee()->input->get('page')) ?: 1;
		$perpage 	= $this->settings['list_limit'];
		$offset 	= ($page - 1) * $perpage; // Offset is 0 indexed   

		// sort column
		$sort_col 	= ee()->input->get('sort_col');
		if ( ! in_array($sort_col, array('id', 'bundle_label')) )
		{
			$sort_col = 'id';
		}

		// sort direction
		$sort_dir 	= ee()->input->get('sort_dir');
		if ( ! in_array($sort_dir, array('asc', 'desc')) )
		{
			$sort_dir = 'asc';
		}

		if ($sort_col && $sort_dir)
		{
			$order = array($sort_col => $sort_dir);
			$base_url->setQueryStringVariable('sort_col', $sort_col);
			$base_url->setQueryStringVariable('sort_dir', $sort_dir);
		}

		/* ----------------------------------------------------------------------------- 
     	   Generate data
     	   ----------------------------------------------------------------------------- */

		$bundles = ee()->mustash_lib->get_bundles($perpage, $offset, $order);
		$total = ee()->mustash_lib->get_total_bundles();

		/* ----------------------------------------------------------------------------- 
     	   Pagination
     	   ----------------------------------------------------------------------------- */

		$vars['pagination'] = ee('CP/Pagination', $total)
			->perPage($perpage)
			->currentPage($page)
			->render($base_url);

		/* ----------------------------------------------------------------------------- 
     	   Construct table
     	   ----------------------------------------------------------------------------- */

		$table = ee('CP/Table', array(
			'limit'			=> $perpage,
			'sort_col'		=> $sort_col,
			'sort_dir'		=> $sort_dir
		));
		$table->setColumns(
			array(
				'id',
				'bundle_label',
				'variables' => array(
					'sort' => FALSE
				),
				'manage' => array(
					'type'	=> Table::COL_TOOLBAR
				),
				array(
			      'type'  => Table::COL_CHECKBOX
			    )
			)
		);

		$table->setNoResultsText('no_matching_bundles');

		$data = array();
		if(count($bundles) >= '1')
		{
			foreach($bundles as $b)
			{	
				// toolbar items
				$toolbar_items = array(
					'sync' => array(
						'href' => ee('CP/URL', $this->url_base.'/clear_cache')->setQueryStringVariable('bundle_id', $b['id']),
						'title' => lang('delete_variables'),

					),
				);

				if ( $b['is_locked'] != 1 ) 
				{
					$toolbar_items += array(
						'edit' => array(
							'href' => ee('CP/URL', $this->url_base.'/edit_bundle')->setQueryStringVariable('bundle_id', $b['id']),
							'title' => lang('edit_bundle')
						)
					);
				}

				$data[] = array(

					$b['id'],

					array(
						'content' => $b['bundle_label']
					),

					array(
						'content' => $b['cnt'] . ' ' . lang('variables'),
						'href' => ee('CP/URL', $this->url_base)->setQueryStringVariable('bundle_id', $b['id']),
					),
					
					array('toolbar_items' => $toolbar_items),

					array(
						'name'  => 'toggle[]',
						'value' => $b['id'],
						'data' => array(
							'confirm' => lang('bundle') . ': <b>' . htmlentities($b['bundle_label'], ENT_QUOTES) . '</b>'
						),
						// Cannot delete default bundles
						'disabled' => ($b['is_locked'] == 1) ? 'disabled' : NULL
					)

				);
			}
		}
		
		$table->setData($data);

		/* ----------------------------------------------------------------------------- 
     	   Confirm remove modal
     	   ----------------------------------------------------------------------------- */

		ee()->javascript->set_global('lang.remove_confirm', lang('entry') . ': <b>### ' . lang('entries') . '</b>');
		ee()->cp->add_js_script(array(
			'file' => array(
				'cp/confirm_remove',
			),
		));


		/* ----------------------------------------------------------------------------- 
     	   Construct view
     	   ----------------------------------------------------------------------------- */

		// body	
		$vars['cp_heading'] = lang('bundles');
		$vars['total'] = $total;

		// rendered table
		$tableData = $table->viewData($base_url);

		$view = ee('View')->make('_shared/table');
		$vars['table'] = $view->render($tableData);

		// urls
		$vars['form_url'] = $tableData['base_url'];
		$vars['add_bundle_url'] = ee('CP/URL', $this->url_base.'/add_bundle');

		return $this->_load_view(
			'mustash:bundles', 
			$vars, 
			lang('bundles')
		);
	}

	public function add_bundle()
	{
		if ( ! ee()->mustash_lib->can_access('bundles'))
		{
			show_error(lang('unauthorized_access'));
		}

		$vars = array();

		// insert the variable?
		if(isset($_POST['insert_bundle']))
		{	
			$data = array(
				'bundle_name'  => ee()->input->post('bundle_name'),
				'bundle_label' => ee()->input->post('bundle_label')
			);

			if ( ! $data['bundle_name'])
			{
				$this->errors['bundle_name'] = lang('error_missing_bundle_name');
			}
			elseif( ! preg_match('/^[a-z0-9\-_:]+$/i', str_replace('%s', '', $data['bundle_name'])))
			{
				$this->errors['bundle_name'] = lang('error_invalid_bundle_name');
			}
			elseif ( ! ee()->mustash_lib->is_bundle_name_unique($data['bundle_name']))
			{
				$this->errors['bundle_name'] = lang('error_non_unique_bundle_name');
				$this->invalid[] = 'bundle_name';
			}

			if ( ! $data['bundle_label'])
			{
				$this->errors['bundle_label'] = lang('error_missing_bundle_label');
				$this->invalid[] = 'bundle_label';
			}

			if ( empty ($this->errors))
			{
				// remove spaces
				$data['bundle_name'] = str_replace('%s', '', $data['bundle_name']);

				if(ee()->mustash_lib->add_bundle($data))
				{	
					ee()->logger->log_action(ee()->lang->line('log_bundle_added'));

					ee('CP/Alert')->makeInline('entries-form')
								  ->asSuccess()
								  ->withTitle(lang('success'))
								  ->addToBody(lang('add_success'))
								  ->defer();

					ee()->functions->redirect(ee('CP/URL', $this->url_base.'/bundles'));		
					exit;			
				}
				else
				{
					ee('CP/Alert')->makeInline('entries-form')
								  ->asWarning()
								  ->withTitle(lang('warning'))
								  ->addToBody(lang('add_fail'))
								  ->defer();

					ee()->functions->redirect(ee('CP/URL', $this->url_base.'/bundles'));	
					exit;					
				}
			}
		}

		// body	
		$vars['cp_heading'] = lang('add_bundle');

		// urls
		$vars['form_url'] = ee('CP/URL', $this->url_base.'/add_bundle');

		return $this->_load_view(
			'mustash:edit_bundle', 
			$vars, 
			lang('add_bundle'),
			array(  
				ee('CP/URL', $this->url_base.'/bundles')->compile() => lang('bundles') 
			)
		);
	}

	public function edit_bundle()
	{	
		if ( ! ee()->mustash_lib->can_access('bundles'))
		{
			show_error(lang('unauthorized_access'));
		}

		// get the variable
		if ( ! $id = (int) ee()->input->get('bundle_id') )
		{
			ee()->functions->redirect( ee('CP/URL', $this->url_base.'/bundles') );
			exit;
		}

		// get data for the requested bundle
		$vars = ee()->mustash_lib->get_bundle($id);

		if ( ! $vars || ( isset( $vars['is_locked']) && $vars['is_locked'] === '1') ) 
		{
			// var doesn't exist or is locked
			ee()->functions->redirect( ee('CP/URL', $this->url_base.'/bundles') );
		}

		// update the bundle?
		if(isset($_POST['update_bundle']))
		{	
			$data = array(
				'bundle_name'  => ee()->input->post('bundle_name'),
				'bundle_label' => ee()->input->post('bundle_label')
			);

			if ( ! $data['bundle_name'])
			{
				$this->errors['bundle_name'] = lang('error_missing_bundle_name');
			}
			elseif( ! preg_match('/^[a-z0-9\-_:]+$/i', str_replace('%s', '', $data['bundle_name'])))
			{
				$this->errors['bundle_name'] = lang('error_invalid_bundle_name');
			}
			elseif( $vars['bundle_name'] !== $data['bundle_name'] )
			{
				// if the name has been edited, let's check that the new name is unique
				if ( ! ee()->mustash_lib->is_bundle_name_unique($data['bundle_name']))
				{
					$this->errors['bundle_name'] = lang('error_non_unique_bundle_name');
				}
			}

			if ( ! $data['bundle_label'])
			{
				$this->errors['bundle_label'] = lang('error_missing_bundle_label');
			}

			if ( empty ($this->errors))
			{
				// remove spaces
				$data['bundle_name'] = str_replace('%s', '', $data['bundle_name']);

				if(ee()->mustash_lib->update_bundle($id, $data))
				{	
					ee()->logger->log_action(ee()->lang->line('log_bundle_updated'));

					ee('CP/Alert')->makeInline('entries-form')
								  ->asSuccess()
								  ->withTitle(lang('success'))
								  ->addToBody(lang('update_success'))
								  ->defer();

					ee()->functions->redirect(ee('CP/URL', $this->url_base.'/bundles'));		
					exit;			
				}
				else
				{
					ee('CP/Alert')->makeInline('entries-form')
								  ->asWarning()
								  ->withTitle(lang('warning'))
								  ->addToBody(lang('update_fail'))
								  ->defer();

					ee()->functions->redirect(ee('CP/URL', $this->url_base.'/bundles'));	
					exit;					
				}
			}
		}

		// body	
		$vars['cp_heading'] = lang('edit_bundle');

		// urls
		$vars['form_url'] = ee('CP/URL', $this->url_base.'/edit_bundle')->setQueryStringVariable('bundle_id', $id);

		return $this->_load_view(
			'mustash:edit_bundle', 
			$vars, 
			lang('edit_bundle'),
			array(  
				ee('CP/URL', $this->url_base.'/bundles')->compile() => lang('bundles') 
			)
		);
	}

	private function _delete_bundles($damned=array())
	{
		// delete them
		if ( ee()->mustash_lib->delete_bundles($damned))
		{
			// successfully deleted all variables
			ee()->logger->log_action(ee()->lang->line('log_bundles_deleted'));

			ee('CP/Alert')->makeInline('entries-form')
				->asSuccess()
				->withTitle(lang('success'))
				->addToBody(lang('delete_success'))
				->defer();
		}
		else
		{
			ee('CP/Alert')->makeInline('entries-form')
				->asWarning()
				->withTitle(lang('warning'))
				->addToBody(lang('delete_fail'))
				->defer();
		}
		
		ee()->functions->redirect( ee('CP/URL', $this->url_base.'/bundles', ee()->cp->get_url_state()) );		

	}

	public function rules()
	{
		if ( ! ee()->mustash_lib->can_access('rules'))
		{
			show_error(lang('unauthorized_access'));
		}

		$vars = array();

		if(isset($_POST['update_mustash_rules']))
		{	
			// update rules
			$hook  		= ee()->input->post('hook');
			$group  	= ee()->input->post('group');
			$bundle 	= ee()->input->post('bundle');
			$scope 		= ee()->input->post('scope');
			$pattern 	= ee()->input->post('pattern');
			$notes 		= ee()->input->post('notes');

			$rules = array();

			// format the ruleset
			if ($hook)
			{
				foreach($hook as $index => $trigger)
				{
					if ( $trigger != "NULL" )
					{
						$trigger = explode('--', $trigger);

						// group_id
						$group_id = NULL;
						if (isset($group[$index]) && $group[$index] != "NULL") {
							$group_id = $group[$index];
						}

						// bundle_id
						$bundle_id = NULL;
						if (isset($bundle[$index]) && $bundle[$index] != "NULL") {
							$bundle_id = $bundle[$index];
						}

						// scope
						$scope_value = NULL;
						if (isset($scope[$index]) && $scope[$index] != "NULL") {
							$scope_value = $scope[$index];
						}

						$rules[] = array(
							'plugin' 	=> $trigger[0],
							'hook' 	 	=> $trigger[1],
							'group_id'	=> $group_id,
							'bundle_id'	=> $bundle_id, 
							'scope'		=> $scope_value,
							'pattern'	=> $pattern[$index],
							'notes'		=> $notes[$index],
							'ord'		=> $index
						);
					}
		
				}
			}

			// update ruleset
			if(ee()->mustash_lib->update_rules($rules))
			{	
				ee()->logger->log_action(ee()->lang->line('log_rules_updated'));
				ee('CP/Alert')->makeInline('entries-form')
								  ->asSuccess()
								  ->withTitle(lang('success'))
								  ->addToBody(lang('update_success'))
								  ->defer();		
			}
			else
			{
				ee('CP/Alert')->makeInline('entries-form')
								  ->asWarning()
								  ->withTitle(lang('warning'))
								  ->addToBody(lang('update_fail'))
								  ->defer();					
			}

			// redirect
			ee()->functions->redirect( ee('CP/URL', $this->url_base.'/rules', ee()->cp->get_url_state()) );	
			exit;
		}


		// get existing rules
		$vars['rules'] = ee()->mustash_lib->get_rules();

		// show the first row if there are no rules
		if ( empty($vars['rules']))
		{
			$vars['rules'][] = array(
				'plugin' 	=> '',
				'hook' 		=> '',
				'group_id' 	=> '',
				'bundle_id' => '',
				'scope' 	=> '',
				'pattern' 	=> '',
				'notes' 	=> ''
			);
		}

		// get a list of installed plugins
		$vars['plugins'] = ee()->mustash_lib->get_installed_plugins();

		// list of scope
		$vars['scope_select_options'] = ee()->mustash_lib->scope_select_options('--', 'NULL');
		$vars['bundle_select_options'] = ee()->mustash_lib->bundle_select_options('--', 'NULL');


		// -------------------------------------
		//  Load CSS and JS
		// -------------------------------------
		// 
		ee()->mustash_lib->load_assets(
			array(
				'jquery.dynoTable.js',
				'jquery.chained.js',
				'stash_rules.js'
			)
		);	

		// set the page title
		$vars['cp_heading'] = lang('rules');

		// URLs
		$vars['form_url'] = ee('CP/URL', $this->url_base.'/rules');

		return $this->_load_view(
			'mustash:rules', 
			$vars, 
			lang('rules')
		);
	}
	
	public function settings()
	{
		if ( ! ee()->mustash_lib->can_access('settings'))
		{
			show_error(lang('unauthorized_access'));
		}

		if(isset($_POST['update_mustash_settings']))
		{
			// validate
			if( ee()->input->post('license_number'))
			{
				// do update		
				if(ee()->mustash_lib->update_settings($_POST))
				{	
					ee()->logger->log_action(ee()->lang->line('log_settings_updated'));

					ee('CP/Alert')->makeInline('entries-form')
								  ->asSuccess()
								  ->withTitle(lang('success'))
								  ->addToBody(lang('settings_updated'))
								  ->defer();				

					ee()->functions->redirect( ee('CP/URL', $this->url_base.'/settings', ee()->cp->get_url_state()) );
					exit;			
				}
				else
				{
					ee('CP/Alert')->makeInline('entries-form')
								  ->asWarning()
								  ->withTitle(lang('warning'))
								  ->addToBody(lang('settings_update_fail'))
								  ->defer();	

					ee()->functions->redirect( ee('CP/URL', $this->url_base.'/settings', ee()->cp->get_url_state()) );	
					exit;					
				}
			}
			else
			{
				$this->errors['license_number'] = lang('error_missing_license_number');
			}
		}
		
		$vars = array(
			'settings' 	=> $this->settings,
			'form_url'	=> ee('CP/URL', $this->url_base.'/settings')
		);

		// decrypt API key
		$vars['api_key'] = ee()->encrypt->decode($this->settings['api_key']);

		// API URL
		$vars['api_url'] = ee()->config->config['site_url']
						  .'?ACT='
						  .ee()->cp->fetch_action_id('Mustash', 'api')
						  .'&key='.$vars['api_key']
						  .'&hook=[your hook here]';

		$vars['api_url_prune'] = ee()->config->config['site_url']
						  .'?ACT='
						  .ee()->cp->fetch_action_id('Mustash', 'api')
						  .'&key='.$vars['api_key']
						  .'&prune=1';

		// --------------------------------------------------------
		//  Get member groups, excluding superadmin, guests, pending and banned
		// --------------------------------------------------------

		$query = ee()->db->select('group_id, group_title')
		       ->from('member_groups')
		       ->where_not_in('group_id', array(1, 2, 3, 4))
		       ->order_by('group_title', 'asc')
		       ->get();

		$vars['member_groups'] = stash_array_column($query->result_array(), 'group_title', 'group_id');

		// --------------------------------------------------------
		// Look for plugins
		// --------------------------------------------------------
		$plugins = ee()->mustash_lib->get_all_plugins();

		$vars['plugin_options'] = array();

		foreach($plugins as $p)
		{
			$vars['plugin_options'][$p] = ee()->mustash_lib->plugin($p);
		}

		// -------------------------------------
		//  Load CSS and JS
		// -------------------------------------

		ee()->mustash_lib->load_assets(
			array(
				'tagmanager.js',
				'tagmanager.css'
			)
		);

		ee()->cp->add_to_foot(
			"<script>
			$(document).ready(function() {
			  $('.tm-input').tagsManager({
			  	prefilled : '{$this->settings['api_hooks']}',
			    blinkBGColor_1: '#FFFF9C',
			    blinkBGColor_2: '#CDE69C',
			    hiddenTagListName: 'api_hooks'
			  });
			});
			</script>"
		);

		// -------------------------------------
		//  Load view
		// -------------------------------------
		return $this->_load_view(
			'mustash:settings', 
			$vars, 
			lang('stash_settings')
		);
	}


	// --------------------------------------------------------------------
  
	public function rewrite()
	{
		if ( ! ee()->mustash_lib->can_access('settings'))
		{
			show_error(lang('unauthorized_access'));
		}

		$vars = array(
			'cache_path' => '[stash_static_basepath]',
			'cache_url' => '[stash_static_url]'
		);

		// Get sites
		$query = ee()->db->get('sites');
		$vars['sites'] = $query->result();

		if ( ee()->config->item('stash_static_basepath') && ee()->config->item('stash_static_url'))
		{
			// Get config items, tidy up for .htaccess
			$vars['cache_path'] = str_replace(' ', '\ ', rtrim(ee()->config->item('stash_static_basepath'), ' \t./').'/');
		  	$vars['cache_url']  = rtrim(ee()->config->item('stash_static_url'), ' \t./').'/';
		}
		else
		{
			$this->errors['general'] = lang('error_missing_static_config');
		}

		// -------------------------------------
		//  Load view
		// -------------------------------------
		return $this->_load_view(
			'mustash:rewrite', 
			$vars, 
			lang('stash_rewrite_rules'),
			array(  
				ee('CP/URL', $this->url_base.'/settings')->compile() => lang('stash_settings') 
			)
		);
	}


	// --------------------------------------------------------------------

	private function _load_view($view, $vars=array(), $heading='', $breadcrumb=array())
	{	

		/* ----------------------------------------------------------------------------- 
     	   override main header
     	   ----------------------------------------------------------------------------- */

		ee()->view->header = array(
			'title' => ee()->mustash_lib->mod_name,
			'form_url' => ee('CP/URL', $this->url_base),
			'search_button_value' => lang('search_variables')
		);

		/* ----------------------------------------------------------------------------- 
     	   Sidebar menu
     	   ----------------------------------------------------------------------------- */

		$sidebar = ee('CP/Sidebar')->make();

		// variables
		$nav = $sidebar->addHeader(lang('nav_stash_variables'), ee('CP/URL', $this->url_base));

		// variables submenu
		$nav_list = $nav->addBasicList();
		$nav_list->addItem(lang('delete_variables'), ee('CP/URL', $this->url_base.'/clear_cache_confirm'));

		// manually highlight this nav item for the following functions:
		if ( in_array( debug_backtrace()[1]['function'], array('edit_variable') ))
		{
			$nav->isActive();
		}

		// member access to areas
		$areas = array('bundles', 'rules', 'settings');

		// only show those areas the member group has been granted access to
		foreach ($areas as $area)
		{
			if ( ee()->mustash_lib->can_access($area))
			{
				$nav = $sidebar->addHeader( lang('nav_stash_'.$area), ee('CP/URL', $this->url_base. '/'. $area) );

				// bundles
				if ($area == 'bundles')
				{
					// add button
					$nav->withButton(lang('new'), ee('CP/URL', $this->url_base. '/add_bundle'));

					// highlight on sub-pages
					if ( in_array( debug_backtrace()[1]['function'], array('add_bundle', 'edit_bundle') ))
					{
						$nav->isActive();
					}
				}
				
				// settings
				if ($area == 'settings')
				{
					// add submenu
					$nav_list = $nav->addBasicList();
					$nav_list->addItem(lang('stash_rewrite_rules'), ee('CP/URL', $this->url_base . '/rewrite'));
				}
			}
		}

		/* ----------------------------------------------------------------------------- 
     	   Render view
     	   ----------------------------------------------------------------------------- */

     	// set common view variables
		$vars['url_base'] = $this->url_base;
		$vars['settings'] = $this->settings;

		if ( ! isset($vars['cp_heading'])) 
		{
			$vars['cp_heading'] = $heading;
		}

		// add errors
		if ( count($this->errors) > 0)
		{
			$alert = ee('CP/Alert')->makeInline('entries-form')
						  ->asIssue()
						  ->withTitle(lang('error'));	
			foreach($this->errors as $msg) 
			{
				$alert->addToBody($msg);
			}

			$alert->now();
		}

		// add errors to view
		$vars['errors'] = $this->errors;

		// render body 
		$v = ee('View')->make($view);
		$body = $v->render($vars);

		// breadcrumb base
		$breadcrumb_base = array(
			ee('CP/URL', $this->url_base)->compile() => ee()->mustash_lib->mod_name
		);
		
		return array(
			'heading'  	 => $heading,
		  	'body'       => $body,
		  	'breadcrumb' => $breadcrumb_base + $breadcrumb,
		);
	}

}
// END CLASS

/* End of file mcp.mustash.php */
/* Location: ./system/user/addons/mustash/mcp.mustash.php */
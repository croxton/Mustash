<?php
$lang = array(

// Table
'key_name'						=> 'Name',
'created'						=> 'Date created',
'expire'						=> 'Date expires',
'bundle_name' 					=> 'Bundle',
'session_id'					=> 'Scope',

// Required
'mustash_module_name'		 	=> 'Mustash',
'mustash_module_description' 	=> 'Manage Stash variables and cache-breaking rules.',

// Control Panel menu
'nav_stash_menu'				=> 'Mustash',
'nav_stash_variables' 			=> 'Variables',
'nav_stash_rules' 				=> 'Cache breaking rules',
'nav_stash_bundles' 			=> 'Bundles',
'nav_stash_settings' 			=> 'Settings',

// Alerts
'add_success' 					=> 'Added successfully',
'add_fail' 						=> 'Add failed',
'update_success' 				=> 'Changes saved',
'update_fail' 					=> 'Update failed',
'delete_success'				=> 'Deleted successfully',
'delete_fail'					=> 'Delete failed',
'clear_success'					=> 'Cache was cleared successfully',
'clear_fail'					=> 'Cache could not be cleared',
'settings_updated'				=> 'Settings have been updated sucessfully',
'warning'						=> 'Warning',

// Log messages
'log_settings_updated' 			=> 'Mustash: settings updated',
'log_variable_updated' 			=> 'Mustash: variable updated',
'log_variable_deleted' 			=> 'Mustash: variable deleted',
'log_clear_cache' 	   			=> 'Mustash: cache cleared',
'log_bundle_added'	   			=> 'Mustash: bundle added',
'log_bundle_updated'   			=> 'Mustash: bundle updated',
'log_bundles_deleted'   		=> 'Mustash: one or more bundles were deleted',
'log_rules_updated'	   			=> 'Mustash: rules updated',

// bundles
'bundle' 			  			=> 'Bundle',
'bundles' 			  			=> 'Bundles',
'add_bundle' 					=> 'Create bundle',
'edit_bundle' 					=> 'Edit bundle',
'delete_bundle' 				=> 'Delete bundle',
'bundle_name_help' 				=> 'Name of the bundle you can use in the bundle parameter, e.g. my_bundle',
'bundle_label' 					=> 'Bundle label',
'bundle_label_help'		 		=> 'Label of the bundle as displayed on the Mustash bundle page',
'delete_bundle_confirm_message' => 'The following bundle, and any cached variables in this bundle, will be deleted:',
'view_variables' 				=> 'View variables',

// settings
'stash_settings' 				=> 'Stash settings',
'stash_rewrite_rules' 			=> 'Static cache rewrite rules',

'license_number' 				=> 'License number',
'license_number_help' 			=> 'Enter the license number you received.',
	
'plugins' 						=> 'Plugins',
'plugins_help'					=> 'Select the plugins to use for triggering cache-breaking rules.',
	
'api_key' 						=> 'API key',
'api_key_help'					=> 'Enter an API key if you wish to trigger rules using the API URL. It should be a random string up to 32 characters long.',
	
'api_hooks' 	 				=> 'API hooks',
'api_hooks_help' 				=> 'Enter a list of custom hooks you wish to trigger via the API URL. E.g. \'post_deploy\'. 
									You must enter at least one hook and create at least one associated rule in order to use the API. 
									Use only alphanumeric characters, underscores and hyphens.',

'api_url' 	 					=> 'API URL - hooks',
'api_url_help'		 			=> 'The URL used to trigger custom hooks.',

'api_url_prune' 	 			=> 'API URL - pruning',
'api_url_prune_help'		 	=> 'The URL used to trigger cache pruning.',

'can_manage_settings'	 		=> 'Can manage settings',
'can_manage_settings_help' 		=> 'Select roles allowed to manage settings (must be user\'s primary role).',
	
'can_manage_rules' 				=> 'Can manage cache-breaking rules',
'can_manage_rules_help' 		=> 'Select roles allowed to manage cache-breaking rules (must be user\'s primary role).',
	
'can_manage_bundles' 			=> 'Can manage bundles',
'can_manage_bundles_help' 		=> 'Select roles allowed to manage bundles (must be user\'s primary role)',
			
'list_limit' 					=> 'List limit',
'list_limit_help' 				=> 'How many rows of results do you wish to display per page in the Variables and Bundles tables?',
'date_format' 					=> 'Date format',
'date_format_help' 				=> 'The <a href="https://docs.expressionengine.com/latest/templates/date_variable_formatting.html#date-formatting-codes" target="_blank">date format</a> to use when displaying dates</a>.',
'global_config' 				=> 'Global Configuration',

// variables
'search_variables'				=> 'Search variables',
'delete_selected'				=> 'Delete selected',
'no_matching_variables' 		=> 'No matching variables',
'var_name' 						=> 'Name',
'var_label' 					=> 'Label',
'var_date_created' 				=> 'Date created',
'var_date_expire' 				=> 'Date expires',
'var_scope' 					=> 'Scope',
'var_scope_global' 				=> 'Site',
'var_scope_user' 				=> 'User',
'var_bundle' 					=> 'Bundle',
'var_session_id' 				=> 'Session ID',
'var_parameters' 				=> 'Value',
	
'variable' 						=> 'Variable',	
'variables' 					=> 'Variables',
'delete_all' 					=> 'Clear all cached variables',
'delete_variables' 				=> 'Clear cache',
'edit_variable' 				=> 'Edit variable',
'delete_confirm' 				=> 'Delete confirm',
'delete_confirm_message' 		=> 'The following cached variables will be deleted:',
'found_variables_total'			=> 'Found %s variables',
	
// filters
'filters_col' 				    => 'Filters',
'filter_by_scope' 				=> 'Scope',
'filter_by_bundle' 				=> 'Bundle',

// clear cache
'clear_mode'					=> 'Caches to clear', 
'clear_mode_help'				=> 'Selected caches will be cleared.', 
'clear_options'					=> 'Options', 
'clear_cache' 					=> 'Clear cached variables',
'clear' 						=> 'Clear',
'clear_all_vars' 				=> 'Clear ALL variables',
'clear_site_vars' 				=> 'Clear site-scoped variables',
'clear_user_vars' 				=> 'Clear user-scoped variables',
'clear_soft' 					=> 'Soft delete?',
'clear_soft_help' 				=> 'Instead of clearing the cache immediately, the cache will be cleared gradually over the period of time specified below.',
'clear_soft_period' 			=> 'Soft delete period',
'clear_soft_period_help' 		=> 'Specify the duration in seconds.',
'clear_soft_seconds' 			=> 'Seconds',

// Rules
'rules' 						=> 'Stash cache-breaking rules',
'add_rule' 						=> 'Add rule',
'remove_rule' 					=> 'Remove rule',
'need_help' 					=> 'Need help?',

'rules' 						=> 'Rules',
'rules_help' 					=> 'Cache-breaking rules can be used to clear specific variables, or groups of variables, after creating or editing content.',

'hook' 							=> 'Hook',
'hook_help' 					=> 'Expressionengine hooks are triggered by specific events in ExpressionEngine: for example, after publishing, editing or deleting an entry.
    								Many third party add-ons provide their own hooks, and you can also create custom hooks for use with the Mustash API.
    								Start your rule by selecting the hook you want to use as the trigger event.
    								You may use the same hook multiple times for different rules.<br>
    								<br>
    								<strong>When your hook is triggered ALL variables in the current site will be cleared, UNLESS you use the following fields to limit the action of each rule:</strong>',

'group' 						=> 'Group',
'group_help' 					=> 'A group is the parent grouping of the item that is being edited.
    								For example, an entry might be published in the \'blog\' channel, so it\'s parent group would be \'blog\'.
    								Choose a group to limit your rule to items that you edit within that group only.',	

'bundle' 						=> 'Bundle',
'bundle_help' 					=> 'Choose a bundle name to limit your rule to variables assigned to that bundle only.',

'scope' 						=> 'Scope',
'scope_help' 					=> ' 
									Choose a scope to limit your rule to variables within that scope only.
',

'pattern' 						=> 'Pattern',
'pattern_help' 					=> 'The pattern field allows you to enter a string or regular expression to match variable names.
									Some hooks allow you to use {markers} in the pattern.',	

'example_patterns' 				=> 'Example patterns',
'available_markers' 			=> 'Available markers',

'no_markers_defined'			=> 'No markers defined',

'all_hooks'						=> 'all hooks',

// API
'api_success' 					=> 'Success',
'api_no_prune' 					=> 'Nothing to prune',
'api_fail' 						=> 'Internal server error',
'api_disabled'					=> 'API disabled',
'api_bad_key' 					=> 'Forbidden',
'api_bad_method' 				=> 'Invalid hook',

// errors
'error_missing_license_number' 	=> 'Please enter your license number.',
'error_missing_bundle_name' 	=> 'Please enter a bundle name.',
'error_missing_bundle_label' 	=> 'Please enter a bundle label.',
'error_invalid_bundle_name' 	=> 'Please enter a valid bundle name. Use only alphanumeric characters, underscores and hyphens.',
'error_non_unique_bundle_name' 	=> 'A bundle already exists with that name. Please enter a unique bundle name.',
'error_missing_static_config' 	=> 'Please set \'stash_static_basepath\' and \'stash_static_url\' in your config.',

// save
'save_variable' => 'Save variable',
'save_bundle' => 'Save bundle',
'save_rules' => 'Save rules',
'save_settings' => 'Save settings',
'clearing_cache' => 'Clearing cache…',

''=>''

);
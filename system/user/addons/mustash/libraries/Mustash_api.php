<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Mustash API server class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2015, hallmarkdesign
 * @link		https://github.com/croxton/Stash/wiki/Mustash
 * @since		1.0
 * @filesource 	./system/user/addons/mustash/Mustash_api.php
 */

class Mustash_api {

	public $settings;
	public $hook = FALSE;
	public $prune = FALSE;
	public $plugin = 'stash_api_pi';
	
	public function __construct()
	{
		ee()->load->library('mustash_lib');
		ee()->load->library('encrypt');	
		$this->settings = ee()->mustash_lib->get_settings();
		ee()->lang->loadfile('mustash');
		$this->_init();
	}

	public function run()
	{
		if ($this->prune)
		{
			if (ee()->mustash_lib->prune())
			{
				$this->response('api_success', 200);
			}
			else
			{
				$this->response('api_no_prune', 200);
			}
		}
		else
		{
			// load and instantiate the plugin
			$plugin = ee()->mustash_lib->plugin($this->plugin);

			if ($plugin->run($this->hook))
			{
				$this->response('api_success', 200);
			}
			else
			{
				$this->response('api_fail', 500);
			}
		}
	}

	private function _init()
	{	
		// API enabled?
		if( ! in_array($this->plugin, $this->settings['enabled_plugins']))
		{
			$this->response('api_disabled', 400);
			exit;
		}

		// API Key?
		$this->key = ee()->input->get_post('key');

		if(!$this->key || $this->key == '' || $this->key != ee()->encrypt->decode($this->settings['api_key']))
		{
			$this->response('api_bad_key', 403);
			exit;			
		}

		// Prune cache?
		$this->prune = ee()->input->get_post('prune');
		
		// Requested hook?
		$this->hook = ee()->input->get_post('hook');

		if( ! $this->prune && (! $this->hook || empty($this->hook) || ! in_array($this->hook, explode(',', $this->settings['api_hooks']))) )
		{
			$this->response('api_bad_method', 400);
			exit;				
		}
	}

	/**
	 * Response
	 * 
	 * @param string $output
	 * @param integer $http_code
	 * @return void
	 */
	public function response($output, $http_code)
	{
		$return = json_encode(array('status'  => $http_code, 'message' => ee()->lang->line($output)));
		header('HTTP/1.1: ' . $http_code);
		header('Status: ' . $http_code);
		header('Content-Length: ' . strlen($return));
		exit($return);
	}
}
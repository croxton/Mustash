<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Mustash API server class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2013, hallmarkdesign
 * @link		http://hallmark-design.co.uk/code/mustash
 * @since		1.0
 * @filesource 	./system/expressionengine/third_party/mustash/Mustash_api.php
 */

class Mustash_api {

	public $settings;
	public $hook;
	public $plugin = 'stash_api_pi';
	
	public function __construct()
	{
		$this->EE = get_instance();

		$this->EE->load->library('mustash_lib');
		$this->EE->load->library('encrypt');	
		$this->settings = $this->EE->mustash_lib->get_settings();
		$this->EE->lang->loadfile('mustash');
		$this->_init();
	}

	public function run()
	{
		// load and instantiate the plugin
		$plugin = $this->EE->mustash_lib->plugin($this->plugin);

		if ($plugin->run($this->hook))
		{
			$this->response('api_success', 200);
		}
		else
		{
			$this->response('api_fail', 500);
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
		$this->key = $this->EE->input->get_post('key');

		if(!$this->key || $this->key == '' || $this->key != $this->EE->encrypt->decode($this->settings['api_key']))
		{
			$this->response('api_bad_key', 403);
			exit;			
		}
		
		// Requested hook?
		$this->hook = $this->EE->input->get_post('hook');

		if( ! $this->hook || empty($this->hook) || ! in_array($this->hook, explode(',', $this->settings['api_hooks'])) )
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
		$return = json_encode(array('status'  => $http_code, 'message' => $this->EE->lang->line($output)));
		header('HTTP/1.1: ' . $http_code);
		header('Status: ' . $http_code);
		header('Content-Length: ' . strlen($return));
		exit($return);
	}
}
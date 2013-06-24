<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Mustash module class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2013, hallmarkdesign
 * @link		http://hallmark-design.co.uk/code/mustash
 * @since		1.0
 * @filesource 	./system/expressionengine/third_party/mustash/mod.mustash.php
 */

class Mustash {
	
	public function __construct()
	{
		$this->EE = get_instance();
	}

	public function api()
	{
		$this->EE->load->library('Mustash_api');
		$this->EE->mustash_api->run();
		exit;
	} 	

}
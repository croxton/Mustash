<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Mustash module class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2014, hallmarkdesign
 * @link		http://hallmark-design.co.uk/code/mustash
 * @since		1.0
 * @filesource 	./system/user/addons/mustash/mod.mustash.php
 */

class Mustash {
	
	public function __construct()
	{

	}

	public function api()
	{
		ee()->load->library('Mustash_api');
		ee()->mustash_api->run();
		exit;
	} 	

}
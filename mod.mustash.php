<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Mustash module class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2015, hallmarkdesign
 * @link		https://github.com/croxton/Stash/wiki/Mustash
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
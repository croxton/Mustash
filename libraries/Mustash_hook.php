<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Mustash_hook class
 *
 * Mustash plugins hooks
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2015, hallmarkdesign
 * @link		https://github.com/croxton/Stash/wiki/Mustash
 * @since		1.0
 * @filesource 	./system/user/addons/mustash/libraries/Mustash_hook.php
 */
class Mustash_hook {

	public $name;

	private $markers = array();
	private $visible;
	private $trigger;

	public function __construct($name, $markers, $visible, $trigger) 
	{
		$this->name = $name;
		$this->markers = $markers;
		$this->visible = $visible;
		$this->trigger = $trigger;
	}

	public function __toString()
    {
        return $this->name;
    }

	/**
	 * Get markers parsed for this hook
	 * 
	 * @return array
	 */
	public function get_markers() 
	{
		return $this->markers;
	}

	/**
	 * Is the hook visible to end users when creating rules?
	 * 
	 * @return bool
	 */
	public function is_visible() 
	{
		return $this->visible;
	}

	/**
	 * Allow the hook to be triggered by EE editing events?
	 * 
	 * @return bool
	 */
	public function has_trigger() 
	{
		return $this->trigger;
	}
}
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Mustash Base Class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2015, hallmarkdesign
 * @link		https://github.com/croxton/Stash/wiki/Mustash
 * @since		2.0
 * @filesource 	./system/user/addons/mustash/base.mustash.php
 */
abstract class Mustash_base {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Add-on version
	 *
	 * @var        string
	 * @access     public
	 */
	public $mod_name;

	/**
	 * Add-on version
	 *
	 * @var        string
	 * @access     public
	 */
	public $mod_version;

	/**
	 * Add-on description
	 *
	 * @var        string
	 * @access     public
	 */
	public $mod_description;

	/**
	 * Add-on documentation URL
	 *
	 * @var        string
	 * @access     public
	 */
	public $docs_url;

	/**
	 * Main class shortcut
	 *
	 * @var        string
	 * @access     protected
	 */
	protected $mod_class_name;


	/**
	 * Extension settings
	 *
	 * @var        array
	 * @access     public
	 */
	public $settings;

	// --------------------------------------------------------------------

	/**
	 * Package name
	 *
	 * @var        string
	 * @access     protected
	 */
	protected $package = 'mustash';

	/**
	 * This add-on's info based on setup file
	 *
	 * @access      protected
	 * @var         object
	 */
	protected $info;


	/**
	 * Site id shortcut
	 *
	 * @var        int
	 * @access     protected
	 */
	protected $site_id;


	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access     public
	 * @return     void
	 */
	public function __construct()
	{
		// -------------------------------------
		//  Set common properties
		// -------------------------------------

		$this->info = ee('App')->get($this->package);
		$this->site_id = ee()->config->item('site_id');

		$this->mod_name = $this->info->getName();
		$this->mod_version = $this->info->getVersion();
		$this->mod_description = $this->info->get('description');
		$this->mod_docs_url = $this->info->get('docs_url');

		$this->mod_class_name = ucfirst($this->package);
		
	}

	// --------------------------------------------------------------------

}

/* End of file base.mustash.php */
/* Location: ./system/user/addons/mustash/base.mustash.php */
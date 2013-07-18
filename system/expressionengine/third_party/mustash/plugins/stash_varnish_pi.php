<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stash_varnish_pi
 * Mustash cache-breaking plugin
 * 
 * This plugin is triggered by the stash_delete hook and so can be thought 
 * of any extension to all other plugins. It will attempt to purge urls in 
 * the Varnish cache when corresponding Stash cached items are cleared.
 * That means you must be using Stash full-page caching in some form for it to work.
 *
 * @package		Mustash
 * @author  	Mark Croxton
 */

class Stash_varnish_pi extends Mustash_plugin {

	/**
	 * Name
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $name = 'Varnish';

	/**
	 * Version
	 *
	 * @var 	string
	 * @access 	public
	 */
	public $version = '1.0.0';

	/**
	 * Extension hook priority
	 *
	 * @var 	integer
	 * @access 	public
	 */
	public $priority = '10';

	/**
	 * Extension hooks and associated markers
	 *
	 * @var 	array
	 * @access 	protected
	 */
	protected $hooks = array(
		'stash_delete'
	);

	/**
	 * Varnish port
	 *
	 * @var 	array
	 * @access 	protected
	 */
	protected $port = 80;

	/**
	 * Varnish header
	 *
	 * @var 	array
	 * @access 	protected
	 */
	protected $header = 'EE_PURGE';

	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();	

		// allow options to be overriden by config values
		$this->port 	= $this->EE->config->item('varnish_port')   ? $this->EE->config->item('varnish_port')   : $this->port;
		$this->header 	= $this->EE->config->item('varnish_header') ? $this->EE->config->item('varnish_header') : $this->header;
	}	

	/**
	 * Set groups for this object
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function set_groups()
	{
		return array();
	}

	/*
	================================================================
	Hooks
	================================================================
	*/

	/**
	 * Hook: stash_delete
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	public function stash_delete($data)
	{
		// we're only interested in global cached variables
		if ( is_null($data['session_id'])  
			 ||	$data['session_id'] === '_global'  
			 ||	$data['session_id'] === 'site' 
		){
			// get rules for this plugin/hook 
			if ($rules = $this->get_rules(__FUNCTION__))
			{
				// do we need to filter by bundle?
				if ($data['bundle_id'])
				{
					// find out if the defined rules restrict the action of the hook to one or more specific bundles
					// or if any one of the rules has no bundle assigned (i.e. any bundle allowed)
					$matching_bundle = FALSE;

					foreach($rules as $r)
					{
						if ( $r['bundle_id'] == $data['bundle_id'] || ! $r['bundle_id'])
						{
							$matching_bundle = TRUE; 
						}
					}

					if ( ! $matching_bundle)
					{
						return; // bail out, none of the rules apply to this bundle
					}
				}
			}
			else
			{
				return; // bail out, no rules defined
			}

			// OK, let's go ahead and try to purge the URI from Varnish

			$uri = '/'; // clear whole cache by default

			// check if we're purging an individual full page
			// => cached items using the @URI pointer are most likely to represent cached pages
			if ( $data['key_name'] 
				 && $data['key_label'] 
				 && ( strncmp($data['key_label'], '@URI:', 5) == 0 )
			){
				// parse out the uri
				$uri = $this->EE->mustash_model->parse_uri_from_key($data['key_name']);
				$uri = '/' . ltrim($uri, '/');
			}

			// construct the full URL to the page
			$protocol 	= (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https://" : "http://";
			$purge_url 	= $protocol . $_SERVER['HTTP_HOST'] . $uri;

			// send a special header to Varnish that should be intercepted by the conditions in vcl_recv
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $purge_url);
			curl_setopt($ch, CURLOPT_PORT , (int)$this->port);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->header);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_exec($ch);
		}
		
	}

}

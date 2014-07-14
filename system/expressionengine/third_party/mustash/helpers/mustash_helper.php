<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Mustash -  Helpers
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2014, hallmarkdesign
 * @link		http://hallmark-design.co.uk/code/mustash
 * @since		1.0
 * @filesource 	./system/expressionengine/third_party/mustash/helpers/mustash_helper.php
 */
if ( ! function_exists('stash_convert_timestamp'))
{
	function stash_convert_timestamp($date, $format = FALSE)
	{
		$EE =& get_instance();
		$EE->load->helper('date');
		if(!$format)
		{
			$format = $EE->mustash_lib->settings['date_format'];
		}
		if ($date)
		{
			#$date = mdate($format, $date);	
			// localize displayed date to user's timezone
			$date = $EE->localize->format_date($format, $date);
		}
		else
		{
			return '&infin;';
		}
		return $date;	
	}
}


// --------------------------------------------------------------------

/**
 * Array column
 *
 * Accepts a db resultset and returns an array column
 *
 * @param      array/boolean
 * @param      string    key of array to use as value
 * @param      string    key of array to use as key (optional)
 * @return     array
 */
if ( ! function_exists('stash_array_column'))
{
	function stash_array_column($resultset, $val, $key = FALSE)
	{
		$array = array();

		if ($resultset)
		{
			foreach ($resultset AS $row)
			{
				if ($key !== FALSE)
				{
					$array[$row[$key]] = $row[$val];
				}
				else
				{
					$array[] = $row[$val];
				}
			}
		}

		return $array;
	}
}


// --------------------------------------------------------------

/**
 * Zebra table helper
 *
 * @param       bool
 * @return      string
 */
if ( ! function_exists('stash_zebra'))
{
	function stash_zebra($reset = FALSE)
	{
		static $i = 0;

		if ($reset) $i = 0;

		return (++$i % 2 ? 'odd' : 'even');
	}
}

// --------------------------------------------------------------

/**
 * Hook name
 *
 * @param       bool
 * @return      string
 */
if ( ! function_exists('stash_translate_hook_name'))
{
	function stash_translate_hook_name($hook, $plugin)
	{
		if ($hook === '@all')
		{
			return $plugin . ": " .lang('all_hooks');
		}
		else
		{
			return $hook;
		}
	}
}

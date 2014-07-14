<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * Mustash javascript class
 *
 * @package		Mustash
 * @author		Mark Croxton
 * @copyright	Copyright (c) 2014, hallmarkdesign
 * @link		http://hallmark-design.co.uk/code/mustash
 * @since		1.0
 * @filesource 	./system/expressionengine/third_party/mustash/Mustash_js.php
 */

class Mustash_js
{	
	public function __construct()
	{
		$this->EE =& get_instance();
	}

	/**
	 * The custom datatable JS for the variables list view
	 * @param string $ajax_method
	 * @param int $piplength
	 * @param int $perpage
	 * @param string $extra
	 */	
	public function get_variables_datatables($ajax_method, $piplength, $perpage, $extra='')
	{	
		$js = '

		$(".toggle_all").toggle(function(){$("input.toggle").each(function(){this.checked=!0})},function(){$("input.toggle").each(function(){this.checked=!1})});
		$("#tablePagination").css("display", "none");

		/*  pipelining - http://www.datatables.net/examples/server_side/pipeline.html */
		var oCache = {
			iCacheLower: -1
		};
		
		function fnSetKey( aoData, sKey, mValue ) {
			for ( var i=0, iLen=aoData.length ; i<iLen ; i++ ) {
				if ( aoData[i].name == sKey ) {
					aoData[i].value = mValue;
				}
			}
		}
		
		function fnGetKey( aoData, sKey ) {
			for ( var i=0, iLen=aoData.length ; i<iLen ; i++ ) {
				if ( aoData[i].name == sKey ) {
					return aoData[i].value;
				}
			}
			return null;
		}
		
		function fnDataTablesPipeline ( sSource, aoData, fnCallback ) {

			var iPipe = '.$piplength.',

				bNeedServer 	= false,
				sEcho 			= fnGetKey(aoData, "sEcho"),
				iRequestStart 	= fnGetKey(aoData, "iDisplayStart"),
				iRequestLength 	= fnGetKey(aoData, "iDisplayLength"),
				iRequestEnd 	= iRequestStart + iRequestLength,
				
				k_search    = document.getElementById("keywords"),
				bundle_id   = document.getElementById("bundle_id"),
				scope 		= document.getElementById("scope"),
				total_range	= document.getElementById("total_range");
		
			function k_search_value() {
				return k_search.value;
			}	

			aoData.push( 
				{ "name": "k_search", "value": k_search_value() },
				{ "name": "bundle_id", "value": bundle_id.value },
				{ "name": "scope", "value": scope.value }
			 );
			
			oCache.iDisplayStart = iRequestStart;
			
			/* outside pipeline? */
			if ( oCache.iCacheLower < 0 || iRequestStart < oCache.iCacheLower || iRequestEnd > oCache.iCacheUpper ) {
				bNeedServer = true;
			}
			
			/* sorting etc changed? */
			if ( oCache.lastRequest && !bNeedServer ) {
				for( var i=0, iLen=aoData.length ; i<iLen ; i++ ) {
					if ( aoData[i].name != "iDisplayStart" && aoData[i].name != "iDisplayLength" && aoData[i].name != "sEcho" ) {
						if ( aoData[i].value != oCache.lastRequest[i].value ) {
							bNeedServer = true;
							break;
						}
					}
				}
			}
			
			/* Store the request for checking next time around */
			oCache.lastRequest = aoData.slice();

			if ( bNeedServer ) {
				/* run a new query */
				if ( iRequestStart < oCache.iCacheLower ) {
					iRequestStart = iRequestStart - (iRequestLength*(iPipe-1));
					if ( iRequestStart < 0 ) {
						iRequestStart = 0;
					}
				}
				
				oCache.iCacheLower = iRequestStart;
				oCache.iCacheUpper = iRequestStart + (iRequestLength * iPipe);
				oCache.iDisplayLength = fnGetKey( aoData, "iDisplayLength" );
				fnSetKey( aoData, "iDisplayStart", iRequestStart );
				fnSetKey( aoData, "iDisplayLength", iRequestLength*iPipe );
				
				aoData.push( 
					{ "name": "k_search", "value": k_search_value() },
					{ "name": "bundle_id", "value": bundle_id.value },
					{ "name": "scope", "value": scope.value }
				 );
		
				$.getJSON( sSource, aoData, function (json) {

					/* cache the json response for later */
					oCache.lastJson = jQuery.extend(true, {}, json);
		 			
					if ( oCache.iCacheLower != oCache.iDisplayStart ) {
						json.aaData.splice( 0, oCache.iDisplayStart-oCache.iCacheLower );
					}
					json.aaData.splice( oCache.iDisplayLength, json.aaData.length );
					
					fnCallback(json)
				} );

			} else {
				/* retrieve json response from cache */
				json = jQuery.extend(true, {}, oCache.lastJson);
				json.sEcho = sEcho; /* Update the echo for each response */
				json.aaData.splice( 0, iRequestStart-oCache.iCacheLower );
				json.aaData.splice( iRequestLength, json.aaData.length );
				fnCallback(json);
				return;
			}
		}

		/* initilize the datatable */

		var time = new Date().getTime();
	
		oTable = $(".mainTable").dataTable( {	
			"sPaginationType": "full_numbers",
			"bLengthChange": false,
			"bFilter": false,
			"sWrapper": false,
			"sInfo": false,
			"bAutoWidth": false,
			"iDisplayLength": '.$perpage.', 
			'.$extra.'			
			"aoColumns": [null, null, null, null, null, null, { "bSortable": false }],		
				
			"oLanguage": {
				"sZeroRecords": "'.lang('no_matching_variables').'",
				"oPaginate": {
					"sFirst": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />",
					"sPrevious": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />",
					"sNext": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />", 
					"sLast": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />"
				}
			},
			
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": EE.BASE+"&C=addons_modules&M=show_module_cp&module=mustash&method='.$ajax_method.'&time=" + time,
			"fnServerData": fnDataTablesPipeline,
			"fnDrawCallback": function() {
				/* 	
				Hide pagination if only one page of results.
				This is a inelegant workaround required because EE is using an old Datatables, v1.6.2 
				In later v1.8.0+ this is the right way to do it: http://datatables.net/blog/Creating_feature_plug-ins 
				*/
				if(oTable.dataTableSettings[0]._iRecordsDisplay < oTable.dataTableSettings[0]._iDisplayLength) {
					$("#tablePagination").css("display", "none");
				} else {
					$("#tablePagination").css("display", "block");
				}
            }
		});
		
		/* events that redraw the form */

		$("#keywords").bind("keydown blur paste", function (e) {
			/* Filter on the column (the index) of this element */
	    	setTimeout(function(){oTable.fnDraw();}, 1);
		});
		
		$("#variables_form").submit(function() {
			oTable.fnDraw();
  			return false;
		});		
		
		$("select#bundle_id").change(function () {
			oTable.fnDraw();
		});

		$("select#scope").change(function () {
			oTable.fnDraw();
		});						
		
		$(".keyword_filter_value").live("click", function(){ 
			var replace = $(this).attr("rel");
			$("#keywords").val(replace);
			oTable.fnDraw();
			return false;
		});			
	
		';
		
		return $js;
	}


	/**
	 * The custom datatable JS for the variables list view
	 * @param string $ajax_method
	 * @param int $piplength
	 * @param int $perpage
	 * @param string $extra
	 */	
	public function get_bundles_datatables($ajax_method, $piplength, $perpage, $extra='')
	{	
		$js = '

		$("#tablePagination").css("display", "none");

		/*  pipelining - http://www.datatables.net/examples/server_side/pipeline.html */
		var oCache = {
			iCacheLower: -1
		};
		
		function fnSetKey( aoData, sKey, mValue ) {
			for ( var i=0, iLen=aoData.length ; i<iLen ; i++ ) {
				if ( aoData[i].name == sKey ) {
					aoData[i].value = mValue;
				}
			}
		}
		
		function fnGetKey( aoData, sKey ) {
			for ( var i=0, iLen=aoData.length ; i<iLen ; i++ ) {
				if ( aoData[i].name == sKey ) {
					return aoData[i].value;
				}
			}
			return null;
		}
		
		function fnDataTablesPipeline ( sSource, aoData, fnCallback ) {

			var iPipe = '.$piplength.',

				bNeedServer 	= false,
				sEcho 			= fnGetKey(aoData, "sEcho"),
				iRequestStart 	= fnGetKey(aoData, "iDisplayStart"),
				iRequestLength 	= fnGetKey(aoData, "iDisplayLength"),
				iRequestEnd 	= iRequestStart + iRequestLength,
		
				total_range	= document.getElementById("total_range");
		
			function k_search_value() {
				return k_search.value;
			}	

			oCache.iDisplayStart = iRequestStart;
			
			/* outside pipeline? */
			if ( oCache.iCacheLower < 0 || iRequestStart < oCache.iCacheLower || iRequestEnd > oCache.iCacheUpper ) {
				bNeedServer = true;
			}
			
			/* sorting etc changed? */
			if ( oCache.lastRequest && !bNeedServer ) {
				for( var i=0, iLen=aoData.length ; i<iLen ; i++ ) {
					if ( aoData[i].name != "iDisplayStart" && aoData[i].name != "iDisplayLength" && aoData[i].name != "sEcho" ) {
						if ( aoData[i].value != oCache.lastRequest[i].value ) {
							bNeedServer = true;
							break;
						}
					}
				}
			}
			
			/* Store the request for checking next time around */
			oCache.lastRequest = aoData.slice();

			if ( bNeedServer ) {
				/* run a new query */
				if ( iRequestStart < oCache.iCacheLower ) {
					iRequestStart = iRequestStart - (iRequestLength*(iPipe-1));
					if ( iRequestStart < 0 ) {
						iRequestStart = 0;
					}
				}
				
				oCache.iCacheLower = iRequestStart;
				oCache.iCacheUpper = iRequestStart + (iRequestLength * iPipe);
				oCache.iDisplayLength = fnGetKey( aoData, "iDisplayLength" );
				fnSetKey( aoData, "iDisplayStart", iRequestStart );
				fnSetKey( aoData, "iDisplayLength", iRequestLength*iPipe );
		
				$.getJSON( sSource, aoData, function (json) {

					/* cache the json response for later */
					oCache.lastJson = jQuery.extend(true, {}, json);
		 			
					if ( oCache.iCacheLower != oCache.iDisplayStart ) {
						json.aaData.splice( 0, oCache.iDisplayStart-oCache.iCacheLower );
					}
					json.aaData.splice( oCache.iDisplayLength, json.aaData.length );
					
					fnCallback(json)
				} );

			} else {
				/* retrieve json response from cache */
				json = jQuery.extend(true, {}, oCache.lastJson);
				json.sEcho = sEcho; /* Update the echo for each response */
				json.aaData.splice( 0, iRequestStart-oCache.iCacheLower );
				json.aaData.splice( iRequestLength, json.aaData.length );
				fnCallback(json);
				return;
			}
		}

		/* initilize the datatable */

		var time = new Date().getTime();
	
		oTable = $(".mainTable").dataTable( {	
			"sPaginationType": "full_numbers",
			"bLengthChange": false,
			"bFilter": false,
			"sWrapper": false,
			"sInfo": false,
			"bAutoWidth": false,
			"iDisplayLength": '.$perpage.', 
			'.$extra.'			
			"aoColumns": [null, null, { "bSortable": false }, { "bSortable": false }, { "bSortable": false }, { "bSortable": false }],		
				
			"oLanguage": {
				"sZeroRecords": "'.lang('no_matching_variables').'",
				"oPaginate": {
					"sFirst": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />",
					"sPrevious": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />",
					"sNext": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />", 
					"sLast": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />"
				}
			},
			
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": EE.BASE+"&C=addons_modules&M=show_module_cp&module=mustash&method='.$ajax_method.'&time=" + time,
			"fnServerData": fnDataTablesPipeline,
			"fnDrawCallback": function() {
				if(oTable.dataTableSettings[0]._iRecordsDisplay < oTable.dataTableSettings[0]._iDisplayLength) {
					$("#tablePagination").css("display", "none");
				} else {
					$("#tablePagination").css("display", "block");
				}
            }
		});
		';
		
		return $js;
	}
}
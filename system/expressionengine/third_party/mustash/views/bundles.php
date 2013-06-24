<!-- div required by datatables -->
<div id="filter_ajax_indicator"></div>

<?php 
	
	$this->table->set_template($cp_pad_table_template);
	$this->table->set_heading(
		lang('ID'),
		lang('bundle_label'),
		'&nbsp;',
		'&nbsp;',
		'&nbsp;',
		'&nbsp;'
	);

	if(count($bundles) >= '1')
	{
		foreach($bundles as $b)
		{
			$this->table->add_row(
				$b['id'],
				( $b['is_locked'] == 1 ? '<i class="stash_icon icon-lock"></i>' : '') . '<strong>'.$b['bundle_label'].'</strong>',
				anchor($url_base.'variables&amp;bundle_id='.$b['id'], lang('view_variables')) . ' ('.$b['cnt'].')',
				anchor($url_base.'clear_cache&amp;bundle_id='.$b['id'], lang('delete_variables')) ,
				( $b['is_locked'] == 1 ? "&ndash;" : anchor($url_base.'edit_bundle&amp;bundle_id='.$b['id'], lang('edit_bundle')) ),
				( $b['is_locked'] == 1 ? "&ndash;" : anchor($url_base.'delete_bundle_confirm&amp;bundle_id='.$b['id'], lang('delete_bundle')))
			);
		}
	}
	else
	{
		$cell = array('data' => lang('no_matching_bundles'), 'colspan' => 7);
		$this->table->add_row($cell);
	}
	
	echo $this->table->generate();
	
?>
<div class="tableFooter">
	<span class="js_hide"><?php echo $pagination?></span>	
	<div id="tablePagination"><span class="pagination" id="filter_pagination"></span></div>
</div>	

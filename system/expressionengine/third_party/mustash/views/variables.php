<!-- div required by datatables -->
<div id="filter_ajax_indicator"></div>

<?php echo form_open($query_base.'variables', array('id' => 'variables_form'))?>
	<div id="filterMenu">
		<fieldset>
			<legend><?php echo lang('variables')?>: <?php echo $total_entries; ?></legend>

			<div class="group">
				<?php echo form_dropdown('bundle_id', $bundle_select_options, array($bundle_id), 'id="bundle_id"').NBS.NBS?>	
				<?php echo form_dropdown('scope', $scope_select_options, array($scope), 'id="scope"').NBS.NBS?>	
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
			</div>	
					
			<p>
				<?php echo form_label(lang('keywords').NBS, 'keywords', array('class' => 'field js_hide'))?>
				<?php echo form_input(array('id'=>'keywords', 'name'=>'keywords', 'class'=>'field', 'placeholder' => lang('keywords'), 'value'=>$keywords))?>
				&nbsp;&nbsp;
				<?php echo form_submit('submit', lang('search'), 'id="filter_variables_submit" class="submit"')?>	 
			</p>
		</fieldset>
	</div>
<?php echo form_close()?>	

<?php echo form_open($query_base.'delete_variables_confirm'); ?>

<?php
	$this->table->set_template($cp_pad_table_template);
	$this->table->set_heading(
		lang('ID'),
		lang('var_name'),
		lang('var_date_created'),
		lang('var_date_expire'),
		lang('var_bundle'),
		lang('var_scope'),
		form_checkbox('select_all', 'true', FALSE, 'class="toggle_all"')
	);

	if(count($variables) >= '1')
	{
		foreach($variables as $v)
		{		
			$this->table->add_row(
				$v['id'],
				'<a href="'.$query_base.'edit_variable&amp;id='.$v['id'].'" rel="'.$v['key_name'].'">'.$v['key_name'].'</a>',
				stash_convert_timestamp($v['created']),
				stash_convert_timestamp($v['expire']),
				$v['bundle_label'],
				ucfirst($v['scope']),
				form_checkbox('toggle[]', $v['id'], '', ' class="toggle" id="delete_box_'.$v['id'].'"')
			);
		}
	}
	else
	{
		$cell = array('data' => lang('no_matching_variables'), 'colspan' => 7);
		$this->table->add_row($cell);
	}
	
	echo $this->table->generate();
	
?>

<div class="tableFooter">
	<div class="tableSubmit">
		<?php echo form_submit('delete', lang('delete_selected'), 'class="submit"').NBS.NBS?>
	</div>
	<span class="js_hide"><?php echo $pagination?></span>	
	<div id="tablePagination"><span class="pagination" id="filter_pagination"></span></div>
</div>	

<?php echo form_close()?>

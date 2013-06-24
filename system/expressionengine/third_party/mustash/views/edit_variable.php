<?php echo form_open($query_base.'edit_variable&amp;id='.$id, array('id'=>'settings'))?>

	<input type="hidden" value="yes" name="update_variable">

	<table cellpadding="0" cellspacing="0" style="width:100%" class="mainTable">
		<colgroup>
			<col style="width:40%" />
			<col style="width:60%" />
		</colgroup>
		<thead>
			<tr>
				<th scope="col" colspan="2"><?php echo lang('edit_variable')?> (#<?php echo $id?>)</th>
			</tr>
		</thead>
		<tbody>

			<tr class="<?php echo stash_zebra()?>">
				<td>
					<strong><?php echo lang('var_name')?></strong>
				</td>
				<td>
					<?php echo  $key_name?>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td>
					<strong><?php echo lang('var_label')?></strong>
				</td>
				<td>
					<?php echo $key_label?>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td>
					<strong><?php echo lang('site_id')?></strong>
				</td>
				<td>
					<?php echo $site_id?>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td>
					<strong><?php echo lang('var_bundle')?></strong>
				</td>
				<td>
					<?php echo $bundle_label?>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td>
					<strong><?php echo lang('var_scope')?></strong>
				</td>
				<td>
					<?php echo $scope?>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td>
					<strong><?php echo lang('var_session_id')?></strong>
				</td>
				<td>
					<?php echo $session_id?>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td>
					<strong><?php echo lang('var_date_created')?></strong>
				</td>
				<td>
					<?php echo stash_convert_timestamp($created)?>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td>
					<strong><?php echo lang('var_date_expire')?></strong>
				</td>
				<td>
					<?php echo stash_convert_timestamp($expire)?>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td style="vertical-align:top">
					<strong><?php echo lang('var_parameters')?></strong>
				</td>
				<td>
					<?php echo  form_textarea(array(
						'name' 	=> 'parameters',
						'id'	=> 'parameters',
						'value' => $parameters,
						'rows'	=> '20',
						'cols'	=> '40',
						'class' => 'stash_code_format'
					)); ?>
				</td>
			</tr>

		</tbody>
	</table>
	<input type="submit" class="submit" value="<?php echo lang('save')?>" />
<?php echo form_close()?>
<?php $this->load->view('errors'); ?>

<?php echo form_open(
	$query_base.(isset($id)? 'edit_bundle&amp;bundle_id='.$id : 'add_bundle'), 
	array('id'=>'edit_bundle')
)?>

	<input type="hidden" value="yes" name="<?php echo isset($id)?'update_bundle':'insert_bundle'?>">

	<table cellpadding="0" cellspacing="0" style="width:100%" class="mainTable">
		<colgroup>
			<col style="width:40%" />
			<col style="width:60%" />
		</colgroup>
		<thead>
			<tr>
				<th scope="col" colspan="2"><?php echo isset($id)? lang('edit_bundle') : lang('add_bundle')?> <?php echo  isset($id)? '#'.$id : ''?></th>
			</tr>
		</thead>
		<tbody>

			<tr class="<?php echo stash_zebra()?>">
				<td>
					<label class="stash-label" for="bundle_name"><em class="required">*</em> <?php echo lang('bundle_name')?></label>
					<div class="stash-notes"><?php echo lang('bundle_name_help')?></div>
				</td>
				<td>
					<?php echo form_input('bundle_name', (isset($bundle_name)? $bundle_name : ''), 'id="bundle_name"') ?>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td>
					<label class="stash-label" for="bundle_label"><em class="required">*</em> <?php echo lang('bundle_label')?></label>
					<div class="stash-notes"><?php echo lang('bundle_label_help')?></div>
				</td>
				<td>
					<?php echo  form_input('bundle_label', (isset($bundle_label)? $bundle_label : ''), 'id="bundle_label"') ?>
				</td>
			</tr>

		</tbody>
	</table>
	<input type="submit" class="submit" value="<?php echo lang('save')?>" />
<?php echo form_close()?>
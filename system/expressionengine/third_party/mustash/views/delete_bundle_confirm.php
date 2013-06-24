<?php echo form_open($query_base.'delete_bundle', array('id' => 'delete_bundle_confirm'))?>

	<?php echo form_hidden('bundle_id', $id)?>

	<p><strong><?php echo $message?></strong></p>

	<ul class="stash_list">
		<li><?php echo $bundle_label?></li>
	</ul>

	<p class="notice"><?php echo lang('action_can_not_be_undone')?></p>

	<p><?php echo form_submit('delete', lang('delete'), 'class="submit"')?></p>

<?php echo form_close()?>

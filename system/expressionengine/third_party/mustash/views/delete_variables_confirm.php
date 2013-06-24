<?php echo form_open($query_base.'delete_variables', array('id' => 'delete_variables_confirm'))?>

	<?php foreach($variables as $var):?>
		<?php echo form_hidden('toggle[]', $var['id'])?>
	<?php endforeach;?>

	<p><strong><?php echo $message?></strong></p>

	<ul class="stash_list">
	<?php foreach($variables as $var):?>
		<li><?php echo $var['key_name']?></li>
	<?php endforeach;?>
	</ul>

	<p><?php echo form_submit('delete', lang('delete'), 'class="submit"')?></p>

<?php echo form_close()?>

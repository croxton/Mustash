<?php echo form_open($query_base.'clear_cache', array('id' => 'clear_cache_confirm'))?>

	<p>
		<?php
		$data = array(
		  'name'        => 'scope',
		  'id'          => 'all',
		  'value'       => 'all',
		  'checked'     => TRUE
		);
		echo form_radio($data);?>
		<?php echo lang('clear_all_vars')?>
	</p>

	<p>
		<?php
		$data = array(
		  'name'        => 'scope',
		  'id'          => 'site',
		  'value'       => 'site'
		);
		echo form_radio($data);?>
		<?php echo lang('clear_site_vars')?>
	</p>

	<p>
		<?php
		$data = array(
		  'name'        => 'scope',
		  'id'          => 'user',
		  'value'       => 'user'
		);
		echo form_radio($data);?>
		<?php echo lang('clear_user_vars')?>
	</p>

	<p><?php echo form_submit('delete', lang('delete'), 'class="submit"')?></p>

<?php echo form_close()?>

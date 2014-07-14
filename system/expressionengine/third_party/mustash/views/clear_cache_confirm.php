<?php echo form_open($query_base.'clear_cache', array('id' => 'clear_cache_confirm'))?>
	
	<fieldset>
		<legend><?php echo lang('clear_mode')?></legend>
		<br>
		<p>
			<?php
			$data = array(
			  'name'        => 'scope',
			  'id'          => 'all',
			  'value'       => 'all',
			  'checked'     => TRUE
			);
			echo form_radio($data);?>&nbsp;
			<strong><?php echo lang('clear_all_vars')?></strong>
		</p>

		<p>
			<?php
			$data = array(
			  'name'        => 'scope',
			  'id'          => 'site',
			  'value'       => 'site'
			);
			echo form_radio($data);?>&nbsp;
			<strong><?php echo lang('clear_site_vars')?></strong>
		</p>

		<p>
			<?php
			$data = array(
			  'name'        => 'scope',
			  'id'          => 'user',
			  'value'       => 'user'
			);
			echo form_radio($data);?>&nbsp;
			<strong><?php echo lang('clear_user_vars')?></strong>
		</p>

	</fieldset>

	<br>

	<fieldset>	
		<legend><?php echo lang('clear_options')?></legend>

		<br>

		<p>
			<?php echo form_checkbox('soft_delete', 'Y', true);?>&nbsp;
			<strong><?php echo lang('clear_soft')?></strong>
		</p>
		<p>
			<?php 
			$data = array(
			  'name'        => 'invalidate',
			  'id'          => 'invalidate',
			  'value'       => $invalidate,
			  'maxlength'   => '20',
              'size'        => '20',
              'style'       => 'width:50px;',
			);
			echo form_input($data);
			?>
			&nbsp;seconds
		</p>
				<p class="stash_notes">
			<?php echo lang('clear_soft_help')?>
		</p>
	</fieldset>

	<br>

	<p><?php echo form_submit('delete', lang('clear'), 'class="submit"')?></p>

<?php echo form_close()?>

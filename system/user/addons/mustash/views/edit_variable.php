<div class="box">

	<h1><?php echo lang('edit_variable')?> (#<?php echo $id?>)</h1>

	<?php echo form_open($form_url, array('class'=>'settings'))?>

		<fieldset class="col-group">
			<div class="setting-txt col w-4">
				<h3><?php echo lang('var_name')?></h3>
			</div>
			<div class="setting-field col w-12">
				<input type="text" value="<?php echo htmlentities($key_name)?>" disabled="disabled">
			</div>
		</fieldset>

		<fieldset class="col-group">
			<div class="setting-txt col w-4">
				<h3><?php echo lang('var_label')?></h3>
			</div>
			<div class="setting-field col w-12">
				<input type="text" value="<?php echo htmlentities($key_label)?>" disabled="disabled">
			</div>
		</fieldset>

		<fieldset class="col-group">
			<div class="setting-txt col w-4">
				<h3><?php echo lang('site_id')?></h3>
			</div>
			<div class="setting-field col w-12">
				<input type="text" value="<?php echo htmlentities($site_id)?>" disabled="disabled">
			</div>
		</fieldset>

		<fieldset class="col-group">
			<div class="setting-txt col w-4">
				<h3><?php echo lang('var_bundle')?></h3>
			</div>
			<div class="setting-field col w-12">
				<input type="text" value="<?php echo htmlentities($bundle_label)?>" disabled="disabled">
			</div>
		</fieldset>

		<fieldset class="col-group">
			<div class="setting-txt col w-4">
				<h3><?php echo lang('var_scope')?></h3>
			</div>
			<div class="setting-field col w-12">
				<input type="text" value="<?php echo htmlentities($scope)?>" disabled="disabled">
			</div>
		</fieldset>

		<fieldset class="col-group">
			<div class="setting-txt col w-4">
				<h3><?php echo lang('var_session_id')?></h3>
			</div>
			<div class="setting-field col w-12">
				<input type="text" value="<?php echo htmlentities($session_id)?>" disabled="disabled">
			</div>
		</fieldset>

		<fieldset class="col-group">
			<div class="setting-txt col w-4">
				<h3><?php echo lang('var_date_created')?></h3>
			</div>
			<div class="setting-field col w-12">
				<input type="text" value="<?php echo stash_convert_timestamp($created)?>" disabled="disabled">
			</div>
		</fieldset>

		<fieldset class="col-group">
			<div class="setting-txt col w-4">
				<h3><?php echo lang('var_date_expire')?></h3>
			</div>
			<div class="setting-field col w-12">
				<input type="text" value="<?php echo stash_convert_timestamp($expire)?>" disabled="disabled">
			</div>
		</fieldset>

		<fieldset class="col-group last">
			<div class="setting-txt col w-4">
				<h3><?php echo lang('var_parameters')?></h3>
			</div>
			<div class="setting-field col w-12">
				<?php echo  form_textarea(array(
					'name' 	=> 'parameters',
					'id'	=> 'parameters',
					'value' => $parameters,
					'rows'	=> '20',
					'cols'	=> '40'
				)); ?>
			</div>
		</fieldset>

		<fieldset class="form-ctrls">
			<input type="hidden" value="yes" name="update_variable">
			<input class="btn" type="submit" value="<?php echo lang('save_variable')?>" data-submit-text="<?php echo lang('save_variable')?>" data-work-text="<?php echo lang('btn_saving')?>">
		</fieldset>
	<?php echo form_close()?>
</div>
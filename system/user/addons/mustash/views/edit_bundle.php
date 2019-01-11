<div class="box">

	<h1><?php echo $cp_heading?> <span class="req-title"><?php echo lang('required_fields')?></span></h1>

	<?php echo form_open($form_url, array('class'=>'settings'))?>

		<?php echo ee('CP/Alert')->getAllInlines()?>

		<input type="hidden" value="yes" name="<?php echo isset($id)?'update_bundle':'insert_bundle'?>">

		<fieldset class="col-group<?php if (array_key_exists('bundle_name', $errors)) : ?> invalid<?php endif;?> required">
			<div class="setting-txt col w-8">
				<h3><?php echo lang('bundle_name')?></h3>
				<em><?php echo lang('bundle_name_help')?></em>
			</div>

			<div class="setting-field col w-8">
				<?php echo form_input('bundle_name', (isset($bundle_name)? $bundle_name : ''), 'id="bundle_name"') ?>
				<?php if (array_key_exists('bundle_name', $errors)) : ?><em class="ee-form-error-message"><?php echo $errors['bundle_name'];?></em><?php endif;?>
			</div>
		</fieldset>

		<fieldset class="col-group<?php if (array_key_exists('bundle_label', $errors)) : ?> invalid<?php endif;?> required">

			<div class="setting-txt col w-8">
				<h3><?php echo lang('bundle_label')?></h3>
				<em><?php echo lang('bundle_label_help')?></em>
			</div>

			<div class="setting-field col w-8">
				<?php echo  form_input('bundle_label', (isset($bundle_label)? $bundle_label : ''), 'id="bundle_label"') ?>
				<?php if (array_key_exists('bundle_label', $errors)) : ?><em class="ee-form-error-message"><?php echo $errors['bundle_label'];?></em><?php endif;?>
			</div>

		</fieldset>
			
		<fieldset class="form-ctrls">
			<input class="btn" type="submit" value="<?php echo lang('save_bundle')?>" data-submit-text="<?php echo lang('save_bundle')?>" data-work-text="<?php echo lang('btn_saving')?>">
		</fieldset>

	<?php echo form_close()?>
</div>
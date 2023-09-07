<div class="panel">

    <?php echo form_open($form_url, array('class'=>'settings'))?>

    <div class="panel-heading">
        <div class="form-btns form-btns-top">
            <div class="title-bar title-bar--large">
                <h3 class="title-bar__title"><?php echo $cp_heading ?></h3>

                <div class="title-bar__extra-tools">
                    <input class="button button--primary" type="submit" value="<?php echo lang('save_bundle')?>" data-submit-text="<?php echo lang('save_settings')?>" data-work-text="<?php echo lang('btn_saving')?>">
                </div>
            </div>
        </div>
    </div>

    <div class="panel-body">

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
    </div>

    <div class="panel-footer">
        <div class="form-btns">
            <input class="button button--primary" type="submit" value="<?php echo lang('save_bundle')?>" data-submit-text="<?php echo lang('save_settings')?>" data-work-text="<?php echo lang('btn_saving')?>">
        </div>
    </div>

	<?php echo form_close()?>
</div>
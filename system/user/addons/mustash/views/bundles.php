<div class="panel">
		<?php echo form_open($form_url)?>

        <div class="panel-heading">
            <div class="form-btns form-btns-top">
                <div class="title-bar title-bar--large">
                    <h3 class="title-bar__title"><?php echo $cp_heading ?></h3>

                    <div class="title-bar__extra-tools">

                        <a class="button button--primary" href="<?php echo $add_bundle_url ?>"><?php echo lang('add_bundle'); ?></a>

                    </div>
                </div>
            </div>
        </div>

        <div class="panel-body">

			<?php echo ee('CP/Alert')->getAllInlines()?>

			<?php echo $table; ?>
			<?php echo $pagination?>

			<?php if ($total > 0) : ?>
			<fieldset class="tbl-bulk-act hidden">
				<select name="bulk_action">
					<option value="">-- <?php echo lang('with_selected')?> --</option>
					<option value="remove" data-confirm-trigger="selected" rel="modal-confirm-remove-entry"><?php echo lang('remove')?></option>
				</select>
				<button class="btn submit" data-conditional-modal="confirm-trigger"><?php echo lang('submit')?></button>
			</fieldset>
			<?php endif; ?>
        </div>

		<?php echo form_close()?>
</div>

<?php
$modal_vars = array(
	'name'		=> 'modal-confirm-remove-entry',
	'form_url'	=> $form_url,
	'hidden'	=> array(
		'bulk_action'	=> 'remove'
	)
);

$modal = $this->make('ee:_shared/modal_confirm_remove')->render($modal_vars);
ee('CP/Modal')->addModal('remove-entry', $modal);
?>

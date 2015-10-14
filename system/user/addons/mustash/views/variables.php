<div class="box">
	<div class="tbl-ctrls">
		<?php echo form_open($form_url)?>
			<fieldset class="tbl-search right">
				<a class="btn tn action" href="<?php echo $clear_cache_url ?>"><?php echo lang('delete_variables'); ?></a>
			</fieldset>
			<h1>
				<?php echo $cp_heading ?>
			</h1>
			<?=ee('CP/Alert')->getAllInlines()?>

			<div class="tbl-search right">
				<div class="meta-info"><?=$cp_subheading?></div>
			</div>

			<?php if (isset($filters)) echo $filters; ?>

			<?php echo $table; ?>
			<?=$pagination?>
			
			<?php if ($total > 0) : ?>
			<fieldset class="tbl-bulk-act hidden">
				<select name="bulk_action">
					<option value="">-- <?=lang('with_selected')?> --</option>
					<option value="remove" data-confirm-trigger="selected" rel="modal-confirm-remove-entry"><?=lang('remove')?></option>
				</select>
				<button class="btn submit" data-conditional-modal="confirm-trigger"><?=lang('submit')?></button>
			</fieldset>
			<?php endif; ?>

		<?=form_close()?>
	</div>
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


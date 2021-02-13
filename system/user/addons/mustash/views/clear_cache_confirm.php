<div class="panel">

	<?php echo form_open($form_url, array('class'=>'settings'))?>

    <div class="panel-heading">
        <div class="form-btns form-btns-top">
            <div class="title-bar title-bar--large">
                <h3 class="title-bar__title"><?php echo $cp_heading ?></h3>

                <div class="title-bar__extra-tools">
                    <input class="btn" type="submit" value="<?php echo lang('delete_variables')?>" data-submit-text="<?php echo lang('delete_variables')?>" data-work-text="<?php echo lang('clearing_cache')?>">
                </div>
            </div>
        </div>
    </div>

    <div class="panel-body">
				
		<fieldset class="col-group required">
			<div class="setting-txt col w-8">
				<h3><?php echo lang('clear_mode')?></h3>
				<em><?php echo lang('clear_mode_help')?></em>
			</div>
			<div class="setting-field col w-8 last">

				<label class="checkbox-label">
					<input type="radio" name="scope" value="all" checked="checked">
                    <div class="checkbox-label__text">
                        <?php echo lang('clear_all_vars')?>
                    </div>
				</label>

				<label class="checkbox-label">
					<input type="radio" name="scope" value="site">
                    <div class="checkbox-label__text">
                        <?php echo lang('clear_site_vars')?>
                    </div>
				</label>

				<label class="checkbox-label">
					<input type="radio" name="scope" value="user">
                    <div class="checkbox-label__text">
                        <?php echo lang('clear_user_vars')?>
                    </div>
				</label>
			</div>
		</fieldset>

		<fieldset class="col-group">
			<div class="setting-txt col w-8">
				<h3><?php echo lang('clear_soft')?></h3>
				<em><?php echo lang('clear_soft_help')?></em>
			</div>
            <div class="setting-field col w-8 last">
                <label class="checkbox-label">
                    <input type="radio" name="soft_delete" value="y">
                    <div class="checkbox-label__text">yes</div>
                </label>
                <label class="checkbox-label">
                    <input type="radio" name="soft_delete" value="n" checked="checked">
                    <div class="checkbox-label__text">no</div>
                </label>
            </div>
		</fieldset>

		<fieldset class="col-group last">

			<div class="setting-txt col w-8">
				<h3><?php echo lang('clear_soft_period'); ?></h3>
				<em><?php echo lang('clear_soft_period_help')?></em>
			</div>

			<div class="setting-field col w-8 last">
				<?php 
				$data = array(
				  'name'        => 'invalidate',
				  'id'          => 'invalidate',
				  'value'       => $invalidate,
				  'maxlength'   => '20',
	              'size'        => '20',
	              'style'       => 'width:75px;',
				);
				echo form_input($data);
				?>
				&nbsp;<?php echo lang('clear_soft_seconds'); ?>
			</div>

		</fieldset>
    </div>

    <div class="panel-footer">
        <div class="form-btns">
            <input class="btn" type="submit" value="<?php echo lang('delete_variables')?>" data-submit-text="<?php echo lang('delete_variables')?>" data-work-text="<?php echo lang('clearing_cache')?>">
        </div>
    </div>

	<?php echo form_close()?>

</div>



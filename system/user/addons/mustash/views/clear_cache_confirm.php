<div class="box">
					
	<h1><?php echo $cp_heading?> <span class="req-title"><?php echo lang('required_fields')?></span></h1>

	<?php echo form_open($form_url, array('class'=>'settings'))?>
				
		<fieldset class="col-group required">
			<div class="setting-txt col w-8">
				<h3><?php echo lang('clear_mode')?></h3>
				<em><?php echo lang('clear_mode_help')?></em>
			</div>
			<div class="setting-field col w-8 last">

				<label class="choice mr block chosen">
					<input type="radio" name="scope" value="all" checked="checked"> <?php echo lang('clear_all_vars')?>
				</label>

				<label class="choice mr block ">
					<input type="radio" name="scope" value="site"> <?php echo lang('clear_site_vars')?>
				</label>

				<label class="choice mr block ">
					<input type="radio" name="scope" value="user"> <?php echo lang('clear_user_vars')?>
				</label>
			</div>
		</fieldset>

		<fieldset class="col-group">
			<div class="setting-txt col w-8">
				<h3><?php echo lang('clear_soft')?></h3>
				<em><?php echo lang('clear_soft_help')?></em>
			</div>
			<div class="setting-field col w-8">
				<label class="choice mr yes"><input type="radio" name="soft_delete" value="y"> yes</label>
				<label class="choice chosen no"><input type="radio" name="soft_delete" value="n" checked="checked"> no</label>
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
	              'style'       => 'width:50px;',
				);
				echo form_input($data);
				?>
				&nbsp;<?php echo lang('clear_soft_seconds'); ?>
			</div>

		</fieldset>

		<fieldset class="form-ctrls">
			<input class="btn" type="submit" value="<?php echo lang('delete_variables')?>" data-submit-text="<?php echo lang('delete_variables')?>" data-work-text="<?php echo lang('clearing_cache')?>">
		</fieldset>
	

	<?php echo form_close()?>

</div>



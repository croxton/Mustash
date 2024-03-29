<div class="panel">

    <?php echo form_open($form_url, array('id'=>'stash_settings', 'class'=>'settings'))?>

    <div class="panel-heading">
        <div class="form-btns form-btns-top">
            <div class="title-bar title-bar--large">
                <h3 class="title-bar__title"><?php echo $cp_heading ?></h3>

                <div class="title-bar__extra-tools">
                    <input class="button button--primary" type="submit" value="<?php echo lang('save_settings')?>" data-submit-text="<?php echo lang('save_settings')?>" data-work-text="<?php echo lang('btn_saving')?>">
                </div>
            </div>
        </div>
    </div>

    <div class="panel-body">

	<?php echo ee('CP/Alert')->getAllInlines()?>

	<input type="hidden" value="yes" name="update_mustash_settings">

	<fieldset class="col-group">

		<div class="setting-txt col w-8">
			<h3><label for="can_manage_bundles_1"><?php echo lang('can_manage_bundles')?></label></h3>
			<em><?php echo lang('can_manage_bundles_help')?></em>
		</div>

		<div class="setting-field col w-8">
			<?php $index= 0; ?>
			<?php foreach ($member_groups AS $group_id => $group_name): ?>
				<?php ++$index; ?>
                <label class="checkbox-label">
					<?php echo form_checkbox(array(
							'name' 	  => 'can_manage_bundles[]', 
							'id'   	  => 'can_manage_bundles_'.$index, 
							'value'	  => $group_id, 
							'checked' => (in_array($group_id, $settings['can_manage_bundles'])),
						)) . '<div class="checkbox-label__text">'. $group_name . '</div>'; ?>
				</label>
				
			<?php endforeach; ?>
		</div>
	</fieldset>

	<fieldset class="col-group">

		<div class="setting-txt col w-8">
			<h3><label for="can_manage_rules_1"><?php echo lang('can_manage_rules')?></label></h3>
			<em><?php echo lang('can_manage_rules_help')?></em>
		</div>

		<div class="setting-field col w-8">
			<?php $index= 0; ?>
			<?php foreach ($member_groups AS $group_id => $group_name): ?>
				<?php ++$index; ?>
                <label class="checkbox-label">
				<?php echo form_checkbox(array(
						'name' 	  => 'can_manage_rules[]', 
						'id'   	  => 'can_manage_rules_'.$index, 
						'value'	  => $group_id, 
						'checked' => (in_array($group_id, $settings['can_manage_rules'])),
					)) . '<div class="checkbox-label__text">'. $group_name . '</div>'; ?>
				</label>
	
			<?php endforeach; ?>
		</div>

	</fieldset>

	<fieldset class="col-group">
		<div class="setting-txt col w-8">
			<h3><label for="can_manage_settings_1"><?php echo lang('can_manage_settings')?></label></h3>
			<em><?php echo lang('can_manage_settings_help')?></em>
		</div>
		<div class="setting-field col w-8">
			<?php $index= 0; ?>
			<?php foreach ($member_groups AS $group_id => $group_name): ?>
				<?php ++$index; ?>
                <label class="checkbox-label">
				<?php echo form_checkbox(array(
						'name' 	  => 'can_manage_settings[]', 
						'id'   	  => 'can_manage_settings_'.$index, 
						'value'	  => $group_id, 
						'checked' => (in_array($group_id, $settings['can_manage_settings'])),
					)) . '<div class="checkbox-label__text">'. $group_name .'</div>'; ?>
				</label>

			<?php endforeach; ?>
		</div>
	</fieldset>

	<fieldset class="col-group">
		<div class="setting-txt col w-8">
			<h3><label for="enabled_plugins_1"><?php echo lang('plugins')?></label></h3>
			<em><?php echo lang('plugins_help')?></em>
		</div>

		<div class="setting-field col w-8">
		<?php $index= 0; ?>
		<?php foreach($plugin_options AS $plugin => $p): ?>
			<?php ++$index; ?>
			<label class="checkbox-label">
			<?php
				$checkbox_config = array(
					'name' 	  => 'enabled_plugins[]', 
					'id'   	  => 'enabled_plugins_'.$index, 
					'value'	  => $plugin, 
					'checked' => (@in_array($plugin, $settings['enabled_plugins'])),
				);
				if (FALSE == $p->dependencies_are_installed())
				{
					$checkbox_config = array_merge($checkbox_config, array(
						'disabled' => 'disabled',
						'checked' => FALSE
					));
				}
			echo form_checkbox($checkbox_config) . '<div class="checkbox-label__text">'. $p->name .'</div>'; ?>
			</label>
			<?php endforeach; ?>
		</div>
	</fieldset>

	<fieldset class="col-group security-enhance">
		<div class="setting-txt col w-8">
			<h3><label for="api_key"><?php echo lang('api_key')?></label></h3>
			<em><?php echo lang('api_key_help')?></em>
		</div>
		<div class="setting-field col w-8">
			<?php echo form_input('api_key', $api_key, 'id="api_key"') ?> 
		</div>
	</fieldset>

	<fieldset class="col-group">
		<div class="setting-txt col w-8">
			<h3><label for="api_hooks"><?php echo lang('api_hooks')?></label></h3>
			<em><?php echo lang('api_hooks_help')?></em>
		</div>
		<div class="setting-txt col w-8">
			<?php echo form_input('api_hooks_tags', '', 'id="api_hooks_tags" class="tm-input tm-input-info" autocomplete="off" style="width:14em;" placeholder="Enter a word and press return"') ?> 
		</div>
	</fieldset>

	<fieldset class="col-group">
		<div class="setting-txt col w-8">
			<h3><?php echo lang('api_url')?></h3>
			<em><?php echo lang('api_url_help')?></em>
		</div>
		<div class="setting-field col w-8">
			<span class="stash_url"><?php echo $api_url ?></span>
		</div>
	</fieldset>

	<fieldset class="col-group">
		<div class="setting-txt col w-8">
			<h3><?php echo lang('api_url_prune')?></h3>
			<em><?php echo lang('api_url_prune_help')?></em>
		</div>
		<div class="setting-field col w-8">
			<span class="stash_url"><?php echo $api_url_prune ?></span>
		</div>
	</fieldset>

	<fieldset class="col-group">
		<div class="setting-txt col w-8">
			<h3><label for="list_limit"><?php echo lang('list_limit')?></label></h3>
			<em><?php echo lang('list_limit_help')?></em>
		</div>
		<div class="setting-field col w-8">
			<?php echo form_input('list_limit', $settings['list_limit'], 'id="list_limit"') ?> 
		</div>
	</fieldset>

	<fieldset class="col-group">
		<div class="setting-txt col w-8">
			<h3><label for="date_format"><?php echo lang('date_format')?></label></h3>
			<em><?php echo lang('date_format_help')?></em>
		</div>
		<div class="setting-field col w-8">
			<?php echo form_input('date_format', $settings['date_format'], 'id="date_format"') ?> 
		</div>
	</fieldset>

    </div>

    <div class="panel-footer">
        <div class="form-btns">
            <input class="button button--primary" type="submit" value="<?php echo lang('save_settings')?>" data-submit-text="<?php echo lang('save_settings')?>" data-work-text="<?php echo lang('btn_saving')?>">
        </div>
    </div>

    <?php echo form_close()?>

</div>
<?php $this->load->view('errors'); ?>
	
<?php echo form_open($query_base.'settings', array('id'=>'stash_settings'))?>

	<input type="hidden" value="yes" name="update_mustash_settings">

	<table cellpadding="0" cellspacing="0" style="width:100%" class="mainTable">
		<colgroup>
			<col style="width:40%" />
			<col style="width:60%" />
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><?php echo lang('preference')?></th>
				<th scope="col"><?php echo lang('setting')?></th>
			</tr>
		</thead>
		<tbody>

			<tr class="<?php echo stash_zebra()?>">
				<td>
					<label class="stash_label" for="license_number"><em class="required">*</em> <?php echo lang('license_number')?></label>
					<div class="stash_notes"><?php echo lang('license_number_help')?></div>
				</td>
				<td>
					<?php echo form_input('license_number', $settings['license_number'], 'id="license_number"'); ?>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td style="vertical-align:top">
					<strong class="stash_label"><?php echo lang('can_manage_bundles')?></strong>
					<div class="stash_notes"><?php echo lang('can_manage_bundles_help')?></div>
				</td>
				<td>
					<?php foreach ($member_groups AS $group_id => $group_name): ?>
						<label style="display:block; cursor:pointer">
							<?php echo form_checkbox(array(
									'name' 	  => 'can_manage_bundles[]', 
									'id'   	  => 'can_manage_bundles', 
									'value'	  => $group_id, 
									'checked' => (in_array($group_id, $settings['can_manage_bundles'])),
								)) . NBS . $group_name; ?>
						</label>
					<?php endforeach; ?>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td style="vertical-align:top">
					<strong class="stash_label"><?php echo lang('can_manage_rules')?></strong>
					<div class="stash_notes"><?php echo lang('can_manage_rules_help')?></div>
				</td>
				<td>
					<?php foreach ($member_groups AS $group_id => $group_name): ?>
						<label style="display:block; cursor:pointer">
							<?php echo form_checkbox(array(
									'name' 	  => 'can_manage_rules[]', 
									'id'   	  => 'can_manage_rules', 
									'value'	  => $group_id, 
									'checked' => (in_array($group_id, $settings['can_manage_rules'])),
								)) . NBS . $group_name; ?>
						</label>
					<?php endforeach; ?>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td style="vertical-align:top">
					<strong class="stash_label"><?php echo lang('can_manage_settings')?></strong>
					<div class="stash_notes"><?php echo lang('can_manage_settings_help')?></div>
				</td>
				<td>
					<?php foreach ($member_groups AS $group_id => $group_name): ?>
						<label style="display:block; cursor:pointer">
							<?php echo form_checkbox(array(
									'name' 	  => 'can_manage_settings[]', 
									'id'   	  => 'can_manage_settings', 
									'value'	  => $group_id, 
									'checked' => (in_array($group_id, $settings['can_manage_settings'])),
								)) . NBS . $group_name; ?>
						</label>
					<?php endforeach; ?>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td style="vertical-align:top">
					<strong class="stash_label"><?php echo lang('plugins')?></strong>
					<div class="stash_notes"><?php echo lang('plugins_help')?></div>
				</td>
				<td>
					<?php foreach($plugin_options AS $plugin => $name): ?>
						<label style="display:block;cursor:pointer">
							<?php echo form_checkbox(array(
									'name' 	  => 'enabled_plugins[]', 
									'id'   	  => 'enabled_plugins', 
									'value'	  => $plugin, 
									'checked' => (@in_array($plugin, $settings['enabled_plugins'])),
								)) . NBS . $name; ?>
						</label>
					<?php endforeach; ?>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td style="vertical-align:top">
					<strong class="stash_label"><?php echo lang('api_key')?></strong>
					<div class="stash_notes"><?php echo lang('api_key_help')?></div>
				</td>
				<td>
					<label style="display:block;cursor:pointer">
						<?php echo form_input('api_key', $api_key, 'id="api_key"') ?> 
					</label>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td style="vertical-align:top">
					<strong class="stash_label"><?php echo lang('api_hooks')?></strong>
					<div class="stash_notes"><?php echo lang('api_hooks_help')?></div>
				</td>
				<td>
					<label style="display:block;cursor:pointer">
						<?php echo form_input('api_hooks_tags', '', 'id="api_hooks_tags" class="tm-input tm-input-info" autocomplete="off" style="width:14em;" placeholder="Enter a word and press return"') ?> 
					</label>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td style="vertical-align:top">
					<strong class="stash_label"><?php echo lang('api_url')?></strong>
					<div class="stash_notes"><?php echo lang('api_url_help')?></div>
				</td>
				<td>
					<?php echo $api_url ?> 
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td style="vertical-align:top">
					<strong class="stash_label"><?php echo lang('api_url_prune')?></strong>
					<div class="stash_notes"><?php echo lang('api_url_prune_help')?></div>
				</td>
				<td>
					<?php echo $api_url_prune ?> 
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td style="vertical-align:top">
					<strong class="stash_label"><?php echo lang('list_limit')?></strong>
					<div class="stash_notes"><?php echo lang('list_limit_help')?></div>
				</td>
				<td>
					<label style="display:block;cursor:pointer">
						<?php echo form_input('list_limit', $settings['list_limit'], 'id="list_limit"') ?> 
					</label>
				</td>
			</tr>

			<tr class="<?php echo stash_zebra()?>">
				<td style="vertical-align:top">
					<strong class="stash_label"><?php echo lang('date_format')?></strong>
					<div class="stash_notes"><?php echo lang('date_format_help')?></div>
				</td>
				<td>
					<label style="display:block;cursor:pointer">
						<?php echo form_input('date_format', $settings['date_format'], 'id="date_format"') ?> 
					</label>
				</td>
			</tr>
		</tbody>
	</table>
	<input type="submit" class="submit" value="<?php echo lang('submit')?>" />

<?php echo form_close()?>

<script>
$(document).ready(function() {
  $(".tm-input").tagsManager({
  	prefilled : "<?php echo $settings['api_hooks']?>",
    blinkBGColor_1: '#FFFF9C',
    blinkBGColor_2: '#CDE69C',
    hiddenTagListName: 'api_hooks'
  });
});
</script>
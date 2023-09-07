<div class="panel">
    <div class="tbl-ctrls">
        <?php echo form_open($form_url, array('id' => 'frm-stash-rules'))?>

        <div class="panel-heading">
            <div class="form-btns form-btns-top">
                <div class="title-bar title-bar--large">
                    <h3 class="title-bar__title"><?php echo $cp_heading ?></h3>
                    <div class="title-bar__extra-tools">
                        <input class="button button--primary" type="submit" value="<?php echo lang('save_rules')?>" data-submit-text="<?php echo lang('save_rules')?>" data-work-text="<?php echo lang('btn_saving')?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-body">
            <?php echo ee('CP/Alert')->getAllInlines()?>
        </div>

            <?php echo form_hidden('update_mustash_rules', 'yes'); ?>

            <div class="stash_rules tbl-wrap pb">

                <table id="stash-rules" cellspacing="0">
                    <colgroup>
                        <col style="width:1%" />
                        <col style="width:38%" />
                        <col style="width:60%" />
                        <col style="width:1%" />
                    </colgroup>
                	<thead>
                		<tr>
                			<th class="first reorder-col">&nbsp;</th>
                			<th scope="col">
                                <?php echo lang('filters_col');?>
                            </th>
                			<th scope="col">
                                <?php echo lang('pattern');?>
                            </th>
                            <th>&nbsp;</th>
                		</tr>
                	</thead>
                	<tbody>

                    <?php foreach($rules as $rule): ?>
                    <tr>

                        <td class="center stash_reorder_col">
                            <div class="reorder stash_drag_handle">
                                <i class="fas fa-bars"></i>
                            </div>
                        </td>

                        <td>
                            <label class="stash_control">
                                <span class="stash_control_label"><?php echo lang('hook');?></span>
                                <select name="hook[]" class="hook">

                                    <option value="NULL">-- Please select --</option>

                                    <?php foreach($plugins as $p): ?>
                                    <?php if (count($p->get_hooks()) > 0) : ?>    

                                    <optgroup label="<?php echo $p->name?>">
                                    <?php foreach($p->get_hooks() as $hook): ?>
                                        <option value="<?php echo $p->short_name."--".$hook?>"<?php echo( ($rule['hook'] == $hook->name &&  $rule['plugin'] == $p->short_name) ? ' selected="selected"' : '');?>><?php echo stash_translate_hook_name($hook->name, $p->name)?></option>
                                    <?php endforeach; ?>
                                    </optgroup> 

                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label class="stash_control">
                                <span class="stash_control_label"><?php echo lang('group');?></span>
                                <select name="group[]" class="group">

                                    <option value="NULL">--</option>

                                    <?php foreach($plugins as $p): ?>

                                        <?php 
                                            $hooks = array();
                                            foreach($p->get_hooks() as $hook) :
                                                $hooks[] = $p->short_name."--".$hook->name;
                                            endforeach;
                                            $hooks = implode(' ', $hooks);
                                        ?>
                                        <?php foreach( $p->get_groups() as $group_id => $group_name): ?>
                                            <option value="<?php echo $group_id?>" class="<?php echo $hooks?>"<?php echo( ($rule['plugin'] . $rule['group_id']) == ($p->short_name . $group_id) ? ' selected="selected"' : '');?>><?php echo $group_name?></option>
                                        <?php endforeach; ?>

                                    <?php endforeach; ?>

                                </select>
                            </label>
                            <label class="stash_control">
                                <span class="stash_control_label"><?php echo lang('bundle');?></span>
                                <?php echo form_dropdown('bundle[]', $bundle_select_options, array($rule['bundle_id']), 'id="bundle"').NBS.NBS?>
                            </label>
                            <label class="stash_control">
                                <span class="stash_control_label"><?php echo lang('scope');?></span>
                                <?php echo form_dropdown('scope[]', $scope_select_options, array($rule['scope']), 'id="scope"').NBS.NBS?> 
                            </label>
                        </td>
          
                        <td>
                            <?php echo form_input('pattern[]', $rule['pattern'], 'class="stash_pattern" placeholder="e.g. #^products/{url_title}$#"') ?>
                            <textarea name="notes[]" rows="3" cols="50" class="stash_pattern_note" placeholder="Your notes about this rule"><?php echo $rule['notes'];?></textarea>
                        </td>

                        <td class="center">
                            <button type="button" rel="remove_row" class="stash_remove_row button button--small button--default">
                                <span class="grid-field__column-tool danger-link" title="remove row">
                                    <i class="fas fa-fw fa-trash-alt">
                                        <span class="hidden"><?php echo lang('remove_rule');?></span>
                                    </i>
                                </span>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <tr id="add-template">
                        <td class="center stash_reorder_col">
                            <div class="reorder stash_drag_handle">
                                <i class="fas fa-bars"></i>
                            </div>
                        </td>
                        <td>
                            <label class="stash_control">
                                <span class="stash_control_label"><?php echo lang('hook');?></span>
                                <select name="hook[]" class="hook">

                                    <option value="NULL">-- Please select --</option>

                                    <?php foreach($plugins as $p): ?>
                                    <optgroup label="<?php echo $p->name?>">
                                        <?php foreach($p->get_hooks() as $hook): ?>
                                        <option value="<?php echo $p->short_name."--".$hook->name?>"><?php echo stash_translate_hook_name($hook->name, $p->name)?></option>
                                        <?php endforeach; ?>
                                    </optgroup> 
                                    <?php endforeach; ?>

                                </select>
                            </label>

                            <label class="stash_control">
                                <span class="stash_control_label"><?php echo lang('group');?></span>
                                <select name="group[]" class="group">

                                    <option value="NULL">--</option>

                                    <?php foreach($plugins as $p): ?>

                                        <?php 
                                            $hooks = array();
                                            foreach($p->get_hooks() as $hook) :
                                                $hooks[] = $p->short_name."--".$hook->name;
                                            endforeach;
                                            $hooks = implode(' ', $hooks);
                                        ?>
                                        <?php foreach($p->get_groups() as $group_id => $group_name): ?>
                                            <option value="<?php echo $group_id?>" class="<?php echo $hooks?>"><?php echo $group_name?></option>
                                        <?php endforeach; ?>

                                    <?php endforeach; ?>
                                </select>

                            </label>

                            <label class="stash_control">
                                <span class="stash_control_label"><?php echo lang('bundle');?></span>
                                <?php echo form_dropdown('bundle[]', $bundle_select_options, array(), 'id="bundle"').NBS.NBS?>
                            </label>

                            <label class="stash_control">
                                <span class="stash_control_label"><?php echo lang('scope');?></span>
                                <?php echo form_dropdown('scope[]', $scope_select_options, array(), 'id="scope"').NBS.NBS?> 
                            </label>
                        </td>
                       
                        <td>
                            <?php echo form_input('pattern[]', NULL, 'class="stash_pattern" placeholder="e.g. #^products/{url_title}$#"') ?>
                            <textarea name="notes[]" rows="3" cols="50" class="stash_pattern_note" placeholder="Your notes about this rule"></textarea>
                        </td>
                       
                        <td class="center">
                            <button type="button" rel="remove_row" class="stash_remove_row button button--small button--default">
                                <span class="grid-field__column-tool danger-link" title="remove row">
                                    <i class="fas fa-fw fa-trash-alt">
                                        <span class="hidden"><?php echo lang('remove_rule');?></span>
                                    </i>
                                </span>
                            </button>
                        </td>

                    </tr>
                </tbody>
                </table>

            </div>

            <div class="panel-body">
                <ul class="toolbar stash_add_row" id="add-row">
                    <li class="add">
                        <div class="button-group">
                            <button type="button" rel="add_row" class="button button--default button--small js-grid-add-row">
                                <?php echo lang('add_rule');?>
                            </button>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="panel-footer">
                <div class="form-btns">
                    <input class="button button--primary" type="submit" value="<?php echo lang('save_rules')?>" data-submit-text="<?php echo lang('save_rules')?>" data-work-text="<?php echo lang('btn_saving')?>">
                </div>
            </div>

        <?php echo form_close()?>
    </div>
</div>

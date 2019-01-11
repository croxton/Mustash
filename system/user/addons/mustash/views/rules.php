<div class="box">
    <div class="tbl-ctrls">

        <?php echo form_open($form_url, array('id' => 'frm-stash-rules'))?>

            <fieldset class="tbl-search right">
                <a href="#" class="reveal stash_btn_help" aria-controls="stash-help"><?php echo lang('need_help');?></a>
            </fieldset>

            <h1>
                <?php echo $cp_heading?>
            </h1>

            <?php echo ee('CP/Alert')->getAllInlines()?>

            <div class="stash_footnote" id="stash-help" aria-expanded="false">

                <h3><?php echo lang('rules');?></h3>
                <p><?php echo lang('rules_help');?></p>

                <h3><?php echo lang('hook');?></h3>
                <p><?php echo lang('hook_help');?></p>

                <h3><?php echo lang('group');?></h3>
                <p><?php echo lang('group_help');?></p>

                <h3><?php echo lang('bundle');?></h3>
                <p><?php echo lang('bundle_help');?></p>

                <h3><?php echo lang('scope');?></h3>
                <p><?php echo lang('scope_help');?></p>

                <h3><?php echo lang('pattern');?></h3>
                <p><?php echo lang('pattern_help');?></p>

                <h4><?php echo lang('example_patterns');?></h4>
                <ul>
                    <li><code>my_variable</code></li>
                    <li><code>my_context:my_variable</code></li>
                    <li><code>#^my_context:my_variable$#</code></li>
                    <li><code>#^my_context:{url_title}$#</code></li> 
                </ul>     

                <h3><?php echo lang('available_markers');?></h3>   

                <?php foreach($plugins as $p): ?>
                    <?php if ( $p->short_name !== 'api') : ?>
                        <h4><?php echo $p->name ?></h4>

                        <?php foreach($p->get_hooks() as $hook) : ?>
                            <?php $markers = $hook->get_markers(); ?>
                            <h5><?php echo stash_translate_hook_name($hook->name, $p->name) ?></h5>
                            <?php if ( count($markers) > 0) : ?>
                            <ul>
                                <?php foreach($markers as $m): ?>
                                <li><code>{<?php echo $m ?>}</code></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php else: ?>
                            <p><?php echo lang('no_markers_defined');?></p>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>


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
                                <?php echo lang('filters');?>
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

                        <td class="center reorder-col">
                            <span class="ico reorder stash_drag_handle"></span>
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
                            <div class="toolbar-wrap">
                                <ul class="toolbar">
                                    <li class="remove"><a href="#" title="<?php echo lang('remove_rule');?>" class="stash_remove_row"></a></li>
                                </ul>                        
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <tr id="add-template">

                        <td class="center reorder-col">
                            <span class="ico reorder stash_drag_handle"></span>
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
                            <div class="toolbar-wrap">
                                <ul class="toolbar">
                                    <li class="remove"><a href="#" title="<?php echo lang('remove_rule');?>" class="stash_remove_row"></a></li>
                                </ul>                        
                            </div>
                        </td>

                    </tr>
                </tbody>
                </table>

            </div>

            <ul class="toolbar stash_add_row" id="add-row">
                <li class="add"><a href="#" title="<?php echo lang('add_rule');?>"></a></li>
            </ul>


            <fieldset class="stash_rules_footer form-ctrls">
                <input class="btn" type="submit" value="<?php echo lang('save_rules')?>" data-submit-text="<?php echo lang('save_rules')?>" data-work-text="<?php echo lang('btn_saving')?>">
            </fieldset>

        <?php echo form_close()?>
    </div>
</div>

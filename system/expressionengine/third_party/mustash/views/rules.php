<?php echo form_open($query_base.'rules', array('id' => 'frm-stash-rules'))?>

    <?php echo form_hidden('update_mustash_rules', 'yes'); ?>

    <div class="stash_rules">

        <table id="stash-rules" class="mainTable padTable">
            <colgroup>
                <col style="width:1%" />
                <col style="width:15%" />
                <col style="width:15%" />
                <col style="width:15%" />
                <col style="width:15%" />
                <col style="width:38%" />
                <col style="width:1%" />
            </colgroup>
        	<thead>
        		<tr>
        			<th>&nbsp;</th>
        			<th scope="col"><?php echo lang('hook');?></th>
        			<th scope="col"><?php echo lang('group');?></th>
                    <th scope="col"><?php echo lang('bundle');?></th>
                    <th scope="col"><?php echo lang('scope');?></th>
        			<th scope="col"><?php echo lang('pattern');?></th>
                    <th>&nbsp;</th>
        		</tr>
        	</thead>
        	<tbody>

            <?php foreach($rules as $rule): ?>
            <tr>
                <td class="center">
                    <i class="icon-move stash_drag_handle"></i>
                </td>
                 <td>
                    <select name="hook[]" class="hook">

                        <option value="NULL">-- Please select --</option>

                        <?php foreach($plugins as $p): ?>

                        <optgroup label="<?php echo $p->name?>">
                        <?php foreach($p->get_hooks() as $hook => $markers): ?>
                            <?php $hook = is_array($markers) ? $hook : $markers; ?>
                            <option value="<?php echo $p->short_name."--".$hook?>"<?php echo( ($rule['hook'] == $hook &&  $rule['plugin'] == $p->short_name) ? ' selected="selected"' : '');?>><?php echo $hook?></option>
                        <?php endforeach; ?>
                        </optgroup> 
                        <?php endforeach; ?>

                    </select>
                </td>
                <td>
                    <select name="group[]" class="group">

                        <option value="NULL">--</option>

                        <?php foreach($plugins as $p): ?>

                            <?php 
                                $hooks = array();
                                foreach($p->get_hooks() as $hook => $markers) :
                                    $hooks[] = $p->short_name."--".( is_array($markers) ? $hook : $markers );
                                endforeach;
                                $hooks = implode(' ', $hooks);
                            ?>
                            <?php foreach( $p->get_groups() as $group_id => $group_name): ?>
                                <option value="<?php echo $group_id?>" class="<?php echo $hooks?>"<?php echo( ($rule['plugin'] . $rule['group_id']) == ($p->short_name . $group_id) ? ' selected="selected"' : '');?>><?php echo $group_name?></option>
                            <?php endforeach; ?>

                        <?php endforeach; ?>

                    </select>
                </td>
                <td>
                    <?php echo form_dropdown('bundle[]', $bundle_select_options, array($rule['bundle_id']), 'id="bundle"').NBS.NBS?>
                </td>
                <td>
                    <?php echo form_dropdown('scope[]', $scope_select_options, array($rule['scope']), 'id="scope"').NBS.NBS?> 
                </td>
                <td>
                    <?php echo form_input('pattern[]', $rule['pattern']) ?>
                </td>
               
                <td class="center">
                    <i class="icon-remove-sign stash_remove_row"></i>
                </td>
            </tr>
            <?php endforeach; ?>

            <tr id="add-template">

                <td class="center">
                    <i class="icon-move stash_drag_handle"></i>
                </td>
                 <td>
                    <select name="hook[]" class="hook">

                        <option value="NULL">-- Please select --</option>

                        <?php foreach($plugins as $p): ?>

                        <optgroup label="<?php echo $p->name?>">
                            <?php foreach($p->get_hooks() as $hook => $markers): ?>
                            <?php $hook = is_array($markers) ? $hook : $markers; ?>
                            <option value="<?php echo $p->short_name."--".$hook?>"><?php echo $hook?></option>
                            <?php endforeach; ?>
                        </optgroup> 
                        <?php endforeach; ?>

                    </select>
                </td>
                <td>
                    <select name="group[]" class="group">

                        <option value="NULL">--</option>

                        <?php foreach($plugins as $p): ?>

                            <?php 
                                $hooks = array();
                                foreach($p->get_hooks() as $hook => $markers) :
                                    $hooks[] = $p->short_name."--".( is_array($markers) ? $hook : $markers );
                                endforeach;
                                $hooks = implode(' ', $hooks);
                            ?>
                            <?php foreach($p->get_groups() as $group_id => $group_name): ?>
                                <option value="<?php echo $group_id?>" class="<?php echo $hooks?>"><?php echo $group_name?></option>
                            <?php endforeach; ?>

                        <?php endforeach; ?>

                        
                    </select>
                </td>
                <td>
                    <?=form_dropdown('bundle[]', $bundle_select_options, array(), 'id="bundle"').NBS.NBS?>
                </td>
                <td>
                    <?=form_dropdown('scope[]', $scope_select_options, array(), 'id="scope"').NBS.NBS?> 
                </td>
                <td>
                    <?php echo form_input('pattern[]') ?>
                </td>
               
                <td class="center">
                    <i class="icon-remove-sign stash_remove_row"></i>
                </td>


            </tr>
        </tbody>
        </table>

        <div id="add-row"  class="stash_add_row">
            <i class="icon-plus-sign"></i> <a href="#"><?php echo lang('add_rule');?></a>
        </div> 

    </div>


    <div class="stash_rules_footer">
        <div class="stash_rules_footer-save">
             <input type="submit" value="Save rules" class="submit">
        </div>
  
        <div class="stash_rules_footer-help">
            <a href="#" class="reveal stash_btn_help" aria-controls="stash-help"><i class="icon-info-sign"></i> <?php echo lang('need_help');?></a>
        </div>
    </div>

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
            <li>my_variable</li>
            <li>my_context:my_variable</li>
            <li>#^my_context:my_variable$#</li>
            <li>#^my_context:{url_title}$#</li> 
        </ul>     

        <h3><?php echo lang('available_markers');?></h3>   

        <?php foreach($plugins as $p): ?>
            <?php if ( $p->short_name !== 'api') : ?>
                <h4><?php echo $p->name ?></h4>

                <?php foreach($p->get_hooks() as $hook => $markers) : ?>

                    <?php if ( is_array($markers)) : ?>
                    <h5><?php echo $hook ?></h5>
                    <ul>
                        <?php foreach($markers as $m): ?>
                        <li>{<?php echo $m ?>}</li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <h5><?php echo $markers ?></h5>
                    <p><?php echo lang('no_markers_defined');?></p>
                    <?php endif; ?>

                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>


    </div>

<?php echo form_close()?>
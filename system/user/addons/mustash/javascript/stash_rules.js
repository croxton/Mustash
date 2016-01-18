/*
 * Stash rules behaviour
 *
 * Copyright (c) 2013 Mark Croxton
 */
$(document).ready(function(){

    // clonable table rows
    $('#stash-rules').dynoTable({
        removeClass: '.stash_remove_row',        //class for the clickable row remover
        cloneClass: '.stash_clone_row',          //class for the clickable row cloner
        addRowTemplateId: '#add-template',       //id for the "add row template" 
        addRowButtonId: '#add-row',              //id for the clickable add row button, link, etc
        lastRowRemovable: true,                  //If true, ALL rows in the table can be removed, otherwise there will always be at least one row
        orderable: true,                         //If true, table rows can be rearranged
        dragHandleClass: ".stash_drag_handle",   //class for the click and draggable drag handle
        insertFadeSpeed: "slow",                 //Fade in speed when row is added
        removeFadeSpeed: "fast",                 //Fade in speed when row is removed
        hideTableOnEmpty: true,                  //If true, table is completely hidden when empty
        onRowRemove: function(){
            //Do something when a row is removed
        },
        onRowClone: function(){
            //Do something when a row is cloned
        },
        onRowAdd: function($row){
            // add the chained select functionality doe the newly cloned row
            var $groupSelect = $row.find('.group');
            var $hookSelect = $row.find('.hook');
            $groupSelect.chained($hookSelect);

        },
        onTableEmpty: function(){
            //Do something when ALL rows have been removed
        },
        onRowReorder: function(){
            //Do something when table rows have been rearranged
        }
    }); 

    // apply chained select for existing rules on page load
    $(".group").each(function() {
        $self = $(this);
        var $hookSelect = $self.parent().prev().find('.hook');
        $self.chained($hookSelect);
    });

    // accessible show/hide regions using ARIA attributes
    $('.reveal').click(function(e) { 

        // find the region the button controls 
        var region = $(this).attr('aria-controls');
        var $region = $('#' + region); 
        var $self = $(this);
        
        if (!$self.hasClass('active')) {

            // update the aria-expanded attribute of the region 
            $region.attr('aria-expanded', 'true'); 

            // move focus to the region 
            $region.focus(); 

            // register active state on the clicked button
            $self.addClass('active');

        } else {

            // update the aria-expanded attribute of the region 
            $region.attr('aria-expanded', 'false'); 

            // move focus to the clicked button 
            //$self.focus(); 

            // remove active state
            $self.removeClass('active');
        }

        e.stopPropagation(); 
        return false; 
    }); 
});               
    
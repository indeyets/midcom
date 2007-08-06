var checkboxes_selected = false;

function toggle_checkboxes()
{
    var checkboxes = jQuery('table td.selection input[@type=checkbox]');
    jQuery.each( checkboxes, function(i,n){
        if (checkboxes_selected)
        {
            jQuery(n).removeAttr('checked');
        }
        else
        {
            jQuery(n).attr('checked','checked');
        }
    });
    checkboxes_selected = !checkboxes_selected;
}
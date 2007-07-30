function toggle_tag_listing(calendar_id)
{
    var element = jQuery("#calendar-list-item-" + calendar_id + "-tags");
    if (element)
    {
        element.toggle();       
    }
}

function toggle_layer_visibility(calendar_id)
{
    var search_string = "#calendar-layer-" + calendar_id + "";
    if (calendar_config["type"] == 2)
    {
        /* We are in month view. Use classes instead of ids */
        var search_string = "div.calendar-layer-" + calendar_id + "";
    }
    jQuery(search_string).toggle();
}

function toggle_tag_visibility(calendar_id, tag_id)
{   
    var search_string = "#calendar-layer-" + calendar_id + " div.tag-" + tag_id + "[@in_shelf=false]";
    if (calendar_config["type"] == 2)
    {
        /* We are in month view. Use classes on layers instead of ids, and li instead of div */
        var search_string = "div.calendar-layer-" + calendar_id + " li.tag-" + tag_id + "[@in_shelf=false]";
    }
    jQuery(search_string).toggle();
}
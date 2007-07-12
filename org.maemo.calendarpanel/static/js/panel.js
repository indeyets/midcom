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
	jQuery(search_string).toggle();
}

function toggle_tag_visibility(calendar_id, tag_id)
{	
	var search_string = "#calendar-layer-" + calendar_id + " div.tag-" + tag_id + "";
	jQuery(search_string).toggle();
}
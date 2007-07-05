function toggle_tag_listing(calendar_id)
{
	var element = jQuery("#calendar-list-item-" + calendar_id + "-tags");
	if (element)
	{
		element.toggle();		
	}
}
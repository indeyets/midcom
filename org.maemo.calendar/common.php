<?php
/**
 * @package org.maemo.calendar 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package org.maemo.calendar
 */
class org_maemo_calendar_common
{	
	function org_maemo_calendar_common()
	{
	}
	
	function fetch_available_user_tags()
	{
		debug_push_class(__CLASS__, __FUNCTION__);
		
		$tags = array();
		
		$current_user = $_MIDCOM->auth->user->get_storage();
		
		/* Read users tags */
		$users_tags = $current_user->list_parameters('org.openpsa.calendar:tag');
		
		if (empty($users_tags))
		{
			debug_add("No tags defined! Creating the default tag to users parameters.");

			$current_user->add_parameter('org.openpsa.calendar:tag','default','FFFF99');
			$users_tags = $current_user->list_parameters('org.openpsa.calendar:tag');
		}
		
		foreach ($users_tags as $tag => $color)
		{
			$tags[] = array( 'name' => $tag,
							 'id' => org_maemo_calendar_common::tag_name_to_identifier($tag),
							 'color' => $color );
		}
		
		
		debug_print_r("Found tags: ",$tags);
		
		debug_pop();
		return $tags;
	}

	function tag_name_to_identifier($tag_name)
	{
		$tag_identifier = strtolower($tag_name);
		$tag_identifier = str_replace(" ","_",$tag_identifier);
		
		return $tag_identifier;
	}
	
	function get_users_tags($as_key_value_pairs=false)
	{
		$available_tags = org_maemo_calendar_common::fetch_available_user_tags();
		
		if ($as_key_value_pairs)
		{
			$key_val_pairs = array();
			
			foreach ($available_tags as $k => $tag_data)
			{
				$key_val_pairs[$tag_data['id']] = $tag_data['name'];
			}
			
			return $key_val_pairs;
		}
		
		return $available_tags;
	}
		
}

?>
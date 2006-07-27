<?php

/**
 * Helper function for generating uniformal URL name field definitions in schemas
 * 
 * @param string $location	Location of the field.
 * @param bool $required	Required field.
 * @return Array			URLName Schema definition.
 * @package midcom.helper.datamanager
 */ 
function midcom_helper_datamanager_urlname_field($location="name", $required=false) {
  $urlname_array = array ();
  $urlname["description"] = "URL name";
  $urlname["helptext"]    = "Lowercase, no special characters or spaces";
  $urlname["datatype"]    = "text";
  $urlname["location"]    = $location;
  $urlname["aisonly"]     = true;
  if ($required) {
    $urlname["required"]  = true;
  }
  /*$urlname["validation"]  = array( 
    'alphanumeric' => array (
       'message' => "Only alphanumerics allowed"
    )
  );*/
  return $urlname;
}

/**
 * @ignore
 */
 /* helper for the recursive part of midcom_helper_datamanager_selectlist_allgroups */
function midcom_helper_datamanager__selectlist_allgroups_recursor($up, $spacer, &$data, $sitegroup) {
    if (is_null ($up))
    {
        $groups = mgd_list_groups();
    }
    else
    {
        $groups = mgd_list_groups($up);
    }
    if ($groups) 
    {
        while ($groups->fetch()) 
        {
            if ($groups->sitegroup != $sitegroup)
            {
                continue;
            }
            
            // Don't show groups deeper in hierarchy as toplevel
            $group = mgd_get_group($groups->id);
            if (is_null($up) && $group->owner != 0)
            {
                continue;
            }
            
            if (strlen($group->name) > 0)
            {
                $name = $group->name;
            }
            else
            {
                $name = "ID {$group->id}";
            }
            $data[$group->guid()] = $spacer . $group->name;
            midcom_helper_datamanager__selectlist_allgroups_recursor($groups->id, 
                                                                     $spacer . "&nbsp;&nbsp;&nbsp;&nbsp;",
                                                                     $data, 
                                                                     $sitegroup);
        }
    }
}

/**
 * Lists all groups recursivly, using four spaces to indent subgroups.
 * 
 * A no-selection element is added ontop of the list having an empty
 * string as key.
 * 
 * @return Array select datatype compatible group listing, indexed by guid.
 * @package midcom.helper.datamanager
 */
function midcom_helper_datamanager_selectlist_allgroups() 
{
    $midgard = $GLOBALS["midcom"]->get_midgard();
    $i18n =& $GLOBALS["midcom"]->get_service("i18n");
    $l10n =& $i18n->get_l10n("midcom.helper.datamanager");
    $data = Array();
    $data[""] = $l10n->get("no selection");
    midcom_helper_datamanager__selectlist_allgroups_recursor(null, "", $data, $midgard->sitegroup);
    return $data;
}

/**
* Lists all users of the current sitegroup.
* 
* A no-selection element is added ontop of the list having an empty
* string as key.
* 
* @return Array select datatype compatible user listing, indexed by guid.
* @package midcom.helper.datamanager
*/
function midcom_helper_datamanager_selectlist_allpersons()
{
    $i18n =& $_MIDCOM->get_service('i18n');
    $l10n =& $i18n->get_l10n('midcom.helper.datamanager');
    
    $qb = midcom_db_person::new_query_builder();
    $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
    $qb->add_constraint('username', '<>', '');
     
    $data = Array();
    $data[''] = $l10n->get('no selection');
    
    $persons = $qb->execute();
    foreach ($persons as $person)
    {
        $data[$person->guid] = $person->rname;
    }
   return $data;
}

/**
 * Lists all users of the current sitegroup.
 * 
 * A no-selection element is added ontop of the list having an empty
 * string as key.
 * 
 * @return Array select datatype compatible user listing, indexed by id.
 * @package midcom.helper.datamanager
 */
function midcom_helper_datamanager_selectlist_allpersons_id()
{
    $i18n =& $_MIDCOM->get_service('i18n');
    $l10n =& $i18n->get_l10n('midcom.helper.datamanager');
    
    $qb = midcom_db_person::new_query_builder();
    $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
    $qb->add_constraint('username', '<>', '');
     
    $data = Array();
    $data[''] = $l10n->get('no selection');
    
    $persons = $qb->execute();
    foreach ($persons as $person)
    {
        $data[$person->id] = $person->rname;
    }
    return $data;
}

/* TN: Where does this $id come from? disabling the function it seems broken. Please document
 * throughoutly!
function midcom_helper_datamanager_get_next_score () 
{
    $topics = mgd_list_topics($id, 'score');
    if ($topics) 
    {
        return $topics->score +1;
    }
    print_r($GLOBALS['midcom']); 
    return 0;
}
 */

?>

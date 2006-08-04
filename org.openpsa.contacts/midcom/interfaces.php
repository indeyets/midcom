<?php
/**
 * OpenPSA Contact registers/user manager
 * 
 * 
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_interface extends midcom_baseclasses_components_interface
{
    
    function org_openpsa_contacts_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'org.openpsa.contacts';
        $this->_autoload_class_definitions = array('midcom_dba_classes.inc');
        $this->_autoload_files = array(
            'buddy.php',
            'viewer.php',
            'group_midcomdba.php',
            'person_midcomdba.php',
            'person_handler.php',
            'group_handler.php',
            'admin.php',
            'navigation.php',
            'duplicates.php',
        );
        $this->_autoload_libraries = Array(
            'org.openpsa.core', 
            'org.openpsa.helpers',
            'org.openpsa.contactwidget',
            'midcom.helper.datamanager',
            'org.openpsa.qbpager',
            'org.openpsa.relatedto',
        );
    }
    
    /**
     * Initialize
     * 
     * Initialize the basic data structures needed by the component
     */
    function _on_initialize()
    {
        //$_MIDCOM->componentloader->load('net.nehmer.buddylist');
        return true;
    }
    
    /**
     * Locates the root group
     */
    function find_root_group(&$config)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        //Check if we have already initialized
        if (   array_key_exists('contacts_root_group', $GLOBALS['midcom_component_data']['org.openpsa.contacts'])
            && is_object($GLOBALS['midcom_component_data']['org.openpsa.contacts']['contacts_root_group']))
        {
            debug_add('We have already checked initialization and variables are in place');
            debug_pop();
            return $GLOBALS['midcom_component_data']['org.openpsa.contacts']['contacts_root_group'];
        }
        
        // Check that Contacts group structure exists
        $GLOBALS['midcom_component_data']['org.openpsa.contacts']['contacts_root_group'] = false;
        $qb = midcom_baseclasses_database_group::new_query_builder();
        $qb->add_constraint('owner', '=', 0);
        $qb->add_constraint('name', '=', '__org_openpsa_contacts');
        //mgd_debug_start();  
        $results = $qb->execute($qb);
        //mgd_debug_stop();
        debug_add("results for searching '__org_openpsa_contacts'\n===\n" . sprint_r($results) . "===\n");
        if (   is_array($results)
            && count($results) > 0)
        {
            foreach ($results as $group)
            {
                debug_add("found '__org_openpsa_contacts' group #{$group->id}");
                $GLOBALS['midcom_component_data']['org.openpsa.contacts']['contacts_root_group'] = $group;
            }
        }
        else
        {
            debug_add("OpenPsa Contacts root group could not be found", MIDCOM_LOG_WARN);
            //Attempt to  auto-initialize the group.
            $_MIDCOM->auth->request_sudo();
            $grp = new midcom_baseclasses_database_group();
            $grp->owner = 0;
            $grp->name = '__org_openpsa_contacts';
            $ret = $grp->create();
            $_MIDCOM->auth->drop_sudo(); 
            if (!$ret)
            {
                debug_add("Could not auto-initialize the module, create root group '__org_openpsa_contacts' manually", MIDCOM_LOG_ERROR);
                debug_pop();            
                return false;
            }
            $GLOBALS['midcom_component_data']['org.openpsa.contacts']['contacts_root_group'] = $grp;
        }
        debug_pop();
        return $GLOBALS['midcom_component_data']['org.openpsa.contacts']['contacts_root_group'];
    }

    function _on_resolve_permalink($topic, $config, $guid)
    {
        $group = false;
        $person = false;
        
        $group = new org_openpsa_contacts_group($guid);
        if (!$group)
        {
            $person = new org_openpsa_contacts_person($guid);
        }
        switch (true)
        {
            case is_object($group):
                return "group/{$group->guid}/";
                break;
            case is_object($person):
                return "person/{$person->guid}/";
                break;
        }
        return null;
    }
}


?>
<?php
/**
 * @package org.openpsa.contacts
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * OpenPSA Contact registers/user manager
 *
 *
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_interface extends midcom_baseclasses_components_interface
{

    function __construct()
    {
        parent::__construct();

        $this->_component = 'org.openpsa.contacts';

        $this->_autoload_files = array('group.php'); // needed when creating an invoice from sales project

        $this->_autoload_libraries = array
        (
            'org.openpsa.helpers',
            'midcom.helper.datamanager2',
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
        if (   !$group
            || !$group->guid)
        {
            $group = null;
            $person = new org_openpsa_contacts_person($guid);
            if (   !$person
                || !$person->guid)
            {
                $person = null;
            }
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

    /**
     * Support for contacts person merge
     */
    function org_openpsa_contacts_duplicates_merge_person(&$person1, &$person2, $mode)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        switch($mode)
        {
            case 'all':
                break;
            case 'future':
                // Contacts does not have future references so we have nothing to transfer...
                return true;
                break;
            default:
                // Mode not implemented
                debug_add("mode {$mode} not implemented", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
                break;
        }
        $qb = midcom_db_member::new_query_builder();
        // Make sure we stay in current SG even if we could see more
        $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $qb->begin_group('OR');
            // We need the remaining persons memberships later when we compare the two
            $qb->add_constraint('uid', '=', $person1->id);
            $qb->add_constraint('uid', '=', $person2->id);
        $qb->end_group();
        $members = $qb->execute();
        if ($members === false)
        {
            // Some error with QB
            debug_add('QB Error', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // Transfer memberships
        $membership_map = array();
        foreach ($members as $member)
        {
            if ($member->uid != $person1->id)
            {
                debug_add("Transferred membership #{$member->id} to person #{$person1->id} (from #{$member->uid})");
                $member->uid = $person1->id;
            }
            if (   !isset($membership_map[$member->gid])
                || !is_array($membership_map[$member->gid]))
            {
                $membership_map[$member->gid] = array();
            }
            $membership_map[$member->gid][] = $member;
        }
        unset($members);
        // Merge memberships
        foreach ($membership_map as $gid => $members)
        {
            foreach ($members as $key => $member)
            {
                if (count($members) == 1)
                {
                    // We only have one membership in this group, skip rest of the logic
                    if (!$member->update())
                    {
                        // Failure updating member
                        debug_add("Failed to update member #{$member->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                        debug_pop();
                        return false;
                    }
                    continue;
                }

                // TODO: Compare memberships to determine which of them are identical and thus not worth keeping

                if (!$member->update())
                {
                    // Failure updating member
                    debug_add("Failed to update member #{$member->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
            }
        }

        // Transfer metadata dependencies from classes that we drive
        $classes = array(
            'midcom_db_member',
            'org_openpsa_contacts_person',
            'org_openpsa_contacts_group'
        );
        foreach($classes as $class)
        {
            if ($version_not_18 = true)
            {
                switch($class)
                {
                    // Person would have creator&revisor but old-api gets confused...
                    case 'org_openpsa_contacts_person':
                    case 'midcom_db_member':
                        $metadata_fields = array();
                        break;
                    default:
                        $metadata_fields = array(
                            'creator' => 'id',
                            'revisor' => 'id' // Though this will probably get touched on update we need to check it anyways to avoid invalid links
                        );
                        break;
                }
            }
            else
            {
                // TODO: 1.8 metadata format support
            }
            $ret = org_openpsa_contacts_duplicates_merge::person_metadata_dependencies_helper($class, $person1, $person2, $metadata_fields);
            if (!$ret)
            {
                // Failure updating metadata
                debug_add("Failed to update metadata dependencies in class {$class}, errsrtr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }

        // Copy fields missing from person1 and present in person2 over
        $skip_properties = array(
            'id' => true,
            'guid' => true,
        );
        $changed = false;
        foreach($person2 as $property => $value)
        {
            // Copy only simple properties not marked to be skipped missing from person1
            if (   empty($person2->$property)
                || !empty($person1->$property)
                || isset($skip_properties[$property])
                || is_array($value)
                || is_object($value)
                )
            {
                continue;
            }
            $person1->$property = $value;
            $changed = true;
        }
        // Avoid unnecessary updates
        if ($changed)
        {
            if (!$person1->update())
            {
                // Error updating person
                debug_add("Error updating person #{$person->id}, errstr: " . mgd_errstr, MIDCOM_LOG_ERROR);
                return false;
            }
        }
        // PONDER: sensible way to do the same for parameters ??

        // All done, byebye
        return true;
    }

    function _get_data_from_url($url)
    {
        $data = array();

        // TODO: Error handling
        $_MIDCOM->load_library('org.openpsa.httplib');
        $client = new org_openpsa_httplib();
        $html = $client->get($url);

        // Check for ICBM coordinate information
        $icbm = org_openpsa_httplib_helpers::get_meta_value($html, 'icbm');
        if ($icbm)
        {
            $data['icbm'] = $icbm;
        }

        // Check for RSS feed
        $rss_url = org_openpsa_httplib_helpers::get_link_values($html, 'alternate');
        if (   $rss_url
            && count($rss_url) > 0)
        {
            $data['rss_url'] = $rss_url[0]['href'];

            // We have a feed URL, but we should check if it is GeoRSS as well
            $_MIDCOM->load_library('net.nemein.rss');
            $rss_content = net_nemein_rss_fetch::raw_fetch($data['rss_url']);

            if (   isset($rss_content->items)
                && count($rss_content->items) > 0)
            {
                if (   array_key_exists('georss', $rss_content->items[0])
                    || array_key_exists('geo', $rss_content->items[0]))
                {
                    // This is a GeoRSS feed
                    $data['georss_url'] = $data['rss_url'];
                }
            }
        }
        
        if (class_exists('hkit'))
        {
            // We have the Microformats parsing hKit available, see if the page includes a hCard
            $hkit = new hKit();
            $hcards = @$hkit->getByURL('hcard', $url);
            if (   is_array($hcards)
                && count($hcards) > 0)
            {
                // We have found hCard data here
                $data['hcards'] = $hcards;
            }
        }

        return $data;
    }

    /**
     * AT handler for fetching Semantic Web data for person or group
     * @param array $args handler arguments
     * @param object &$handler reference to the cron_handler object calling this method.
     * @return boolean indicating success/failure
     */
    function check_url($args, &$handler)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !array_key_exists('person', $args)
            && !array_key_exists('group', $args))
        {
            $msg = 'Person or Group GUID not set, aborting';
            debug_add($msg, MIDCOM_LOG_ERROR);
            $handler->print_error($msg);
            debug_pop();
            return false;
        }

        if (array_key_exists('person', $args))
        {
            // Handling for persons

            $person = new org_openpsa_contacts_person($args['person']);
            if (!$person)
            {
                $msg = "Person {$args['person']} not found, error " . mgd_errstr();
                debug_add($msg, MIDCOM_LOG_ERROR);
                $handler->print_error($msg);
                debug_pop();
                return false;
            }

            if (!$person->homepage)
            {
                $msg = "Person {$args['person']} has no homepage, skipping";
                debug_add($msg, MIDCOM_LOG_ERROR);
                $handler->print_error($msg);
                debug_pop();
                return false;
            }

            $data = org_openpsa_contacts_interface::_get_data_from_url($person->homepage);

            // Use the data we got
            if (array_key_exists('georss_url', $data))
            {
                // GeoRSS subscription is a good way to keep track of person's location
                $person->parameter('org.routamc.positioning:georss', 'georss_url', $data['georss_url']);
            }
            elseif (array_key_exists('icbm', $data))
            {
                // Instead of using the ICBM position data directly we can subscribe to it so we get modifications too
                $person->parameter('org.routamc.positioning:html', 'icbm_url', $person->homepage);
            }

            if (array_key_exists('rss_url', $data))
            {
                // Instead of using the ICBM position data directly we can subscribe to it so we get modifications too
                $person->parameter('net.nemein.rss', 'url', $data['rss_url']);
            }
            
            if (array_key_exists('hcards', $data))
            {
                // Process those hCard values that are interesting for us
                foreach ($data['hcards'] as $hcard)
                {
                    foreach ($hcard as $key => $val)
                    {
                        switch ($key)
                        {
                            case 'email':
                                $person->email = $val;
                                break;
                                
                            case 'tel':
                                $person->workphone = $val;
                                break;
                                
                            case 'note':
                                $person->extra = $val;
                                break;
    
                            case 'photo':
                                // TODO: Importing the photo would be cool
                                break;
                            
                            case 'adr':
                                if (array_key_exists('street-address', $val))
                                {
                                    $person->street = $val['street-address'];
                                }
                                if (array_key_exists('locality', $val))
                                {
                                    $person->city = $val['locality'];
                                }
                                if (array_key_exists('locality', $val))
                                {
                                    $person->country = $val['country-name'];
                                }
                                break;
                        }
                    }
                }
                
                $person->update();
            }
        }
        elseif (array_key_exists('group', $args))
        {
            // Handling for persons

            $group = new org_openpsa_contacts_group($args['group']);
            if (!$group)
            {
                $msg = "Group {$args['group']} not found, error " . mgd_errstr();
                debug_add($msg, MIDCOM_LOG_ERROR);
                $handler->print_error($msg);
                debug_pop();
                return false;
            }

            if (!$group->homepage)
            {
                $msg = "Group {$args['group']} has no homepage, skipping";
                debug_add($msg, MIDCOM_LOG_ERROR);
                $handler->print_error($msg);
                debug_pop();
                return false;
            }

            $data = org_openpsa_contacts_interface::_get_data_from_url($group->homepage);

            // Use the data we got
            if (array_key_exists('icbm', $data))
            {
                // We know where the group is located
                $icbm_parts = explode(',', $data['icbm']);
                if (count($icbm_parts) == 2)
                {
                    $latitude = (float) $icbm_parts[0];
                    $longitude = (float) $icbm_parts[1];
                    if (   (   $latitude < 90
                            && $latitude > -90)
                        && (   $longitude < 180
                            && $longitude > -180))
                    {
                        $_MIDCOM->load_library('org.routamc.positioning');
                        $location = new org_routamc_positioning_location_dba();
                        $location->date = time();
                        $location->latitude = $latitude;
                        $location->longitude = $longitude;
                        $location->relation = ORG_ROUTAMC_POSITIONING_RELATION_LOCATED;
                        $location->parent = $group->guid;
                        $location->parentclass = 'org_openpsa_contacts_group';
                        $location->parentcomponent = 'org.openpsa.contacts';
                        $stat = $location->create();
                    }
                    else
                    {
                        // This is no earth coordinate, my friend
                    }
                }
            }
            // TODO: We can use a lot of other data too
            if (array_key_exists('hcards', $data))
            {
                // Process those hCard values that are interesting for us
                foreach ($data['hcards'] as $hcard)
                {
                    foreach ($hcard as $key => $val)
                    {
                        switch ($key)
                        {
                            case 'email':
                                $group->email = $val;
                                break;
                                
                            case 'tel':
                                $group->workphone = $val;
                                break;
                                
                            case 'note':
                                $group->extra = $val;
                                break;
    
                            case 'photo':
                                // TODO: Importing the photo would be cool
                                break;
                            
                            case 'adr':
                                if (array_key_exists('street-address', $val))
                                {
                                    $group->street = $val['street-address'];
                                }
                                if (array_key_exists('postal-code', $val))
                                {
                                    $group->postcode = $val['postal-code'];
                                }
                                if (array_key_exists('locality', $val))
                                {
                                    $group->city = $val['locality'];
                                }
                                if (array_key_exists('country-name', $val))
                                {
                                    $group->country = $val['country-name'];
                                }
                                break;
                        }
                    }
                }
                
                $group->update();
            }
        }

        return true;
    }
}


?>
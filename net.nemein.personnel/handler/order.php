<?php
/**
 * @package net.nemein.personnel
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 4840 2006-12-29 06:25:07Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * View page handler, renders index and detail views.
 *
 * @package net.nemein.personnel
 */

class net_nemein_personnel_handler_order extends midcom_baseclasses_components_handler
{
    /**
     * The person to display in single view mode.
     *
     * @var midcom_db_person
     * @access private
     */
    var $_person = null;

    /**
     * The persons to display on the index page, already ordered correctly.
     *
     * @var Array
     * @access private
     */
    var $_persons = null;

    /**
     * The Datamanager of the article to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;
    
    /**
     * Simple default constructor.
     */
    function net_nemein_personnel_handler_order()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['person'] =& $this->_person;
        $this->_request_data['persons'] =& $this->_persons;
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }
    
    /**
     * Save the form data: set the metadata.score decreasing from the maximum count to 1.
     * 
     * In the end relocate to the welcome page.
     * 
     * @access private
     */
    function _set_order()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $i = count($_POST['net_nemein_personnel_index']);
        $memberships = array ();
        
        debug_add("Total of {$i} indexes in POST form 'net_nemein_personnel_index'");
        
        // Initialize the midgard_query_builder
        $qb = midcom_db_member::new_query_builder();
        
        if (version_compare(mgd_version(), '1.8.0alpha1', '>'))
        {
            $qb->add_constraint('gid.guid', '=', $this->_config->get('group'));
            $qb->add_order('uid.lastname');
            $qb->add_order('uid.firstname');
        }
        else
        {
            $group = new midcom_db_group($this->_config->get('group'));
            $qb->add_constraint('gid', '=', $group->id);
        }
        
        
        // Store the memberships in an associative array with key defining the person ID
        foreach ($qb->execute_unchecked() as $membership)
        {
            $memberships[$membership->uid] = $membership;
        }
        
        debug_print_r('Membership records: user id => membership id', $memberships);
        
        // Loop through the POST form to connect posted indexes to the memberships
        foreach ($_POST['net_nemein_personnel_index'] as $id)
        {
            // Bulletproofing against those memberships aren't oddly available
            if (!array_key_exists($id, $memberships))
            {
                debug_add("person ID {$id} was not found in the query builder based memberships list, skipping.");
                continue;
            }
            
            $membership =& $memberships[$id];
            
            if (version_compare(mgd_version(), '1.8.0', '>='))
            {
                debug_add("Setting metadata.score to {$i} for {$membership->id}");
                
                // Set the new score
                $membership->metadata->score = $i;
                
                if (!$membership->update())
                {
                    debug_add("Error in updating the membership: ".mgd_errstr());
                }
                else
                {
                    debug_add("Update successful!");
                }
                
                // Set the approval status
                if (   $this->_topic->can_do('midgard:approve')
                    && isset($_POST['auto_approve']))
                {
                    debug_add('Maintaining the approval status: setting the object back to be approved.');
                    $metadata =& midcom_helper_metadata::retrieve($membership);
                    $metadata->approve();
                }
            }
            else
            {
                debug_add("Setting a parameter: 'net.nemein.personnel', 'score', {$i}");
                $membership->set_parameter('net.nemein.personnel', 'score', $i);
            }
            $i--;
        }
        
        debug_pop();
        $_MIDCOM->relocate();
    }

    /**
     * This function creates a DM2 Datamanager instance to without any set storage so far.
     * The configured schema will be selected, but no set_storage is done. The various
     * view handlers treat this differently.
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager(
            midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb')));

        if (   ! $this->_datamanager
            || ! $this->_datamanager->set_schema($this->_config->get('schema')))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a DM2 instance.');
            // This will exit.
        }
    }
    
    /**
     * Set the head elements and JavaScript source files to be loaded
     * 
     * @access private
     */
    function _load_headers()
    {
        // Include Scriptaculous JavaScript library to headers
        // Scriptaculous/scriptaculous.js
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/Pearified/JavaScript/Scriptaculous/scriptaculous.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/net.nemein.personnel/net_nemein_personnel_sort.js');
        $_MIDCOM->add_link_head
        (
            array
            (
                'type' => 'text/css',
                'rel' => 'stylesheet',
                'href' => MIDCOM_STATIC_URL . '/net.nemein.personnel/order.css',
                'media' => 'screen',
            )
        );
        
        // Set the breadcrumb
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "order/",
            MIDCOM_NAV_NAME => $this->_l10n->get('sort personnel manually'),
        );
        
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);
    }
    
    /**
     * Handler for checking the manual ordering request
     * 
     * @access private
     * @return boolean Indicating success
     */
    function _handler_order($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:update');
        
        if (   !$this->_config->get('manual_order')
            || !$this->_config->get('group'))
        {
            $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "config.html");
        }
        
        $this->_helper = new net_nemein_personnel_sorted_groups($this->_config->get('group'), true);
        $this->_persons = $this->_helper->get_sorted_members();
        
        // Save the form data
        if (array_key_exists('f_submit', $_POST))
        {
            $this->_set_order();
        }
        
        // Relocate on cancel
        if (array_key_exists('f_cancel', $_POST))
        {
            $_MIDCOM->relocate();
        }
        
        $this->_load_datamanager();
        $this->_load_headers();
        
        return true;
    }
    
    /**
     * Show sorting form
     * 
     * @access private
     */
    function _show_order($handler_id, &$data)
    {
        midcom_show_style('admin-order-header');
        
        if (   !isset($this->_persons)
            || count($this->_persons) === 0)
        {
            midcom_show_style('admin-order-empty');
            midcom_show_style('admin-order-footer');
            return;
        }
        
        foreach ($this->_persons as $group_id => $persons)
        {
            foreach ($persons as $person)
            {
                // Set the storage reference for midcom_helper_datamanager2_datamanager
                $this->_datamanager->set_storage($person);
                
                $data['datamanager'] =& $this->_datamanager;
                $data['person_id'] = $person->id;
                
                midcom_show_style('admin-order-item');
            }
        }
        
        midcom_show_style('admin-order-footer');
    }
    
    /**
     * Save the grouped memberships data
     *
     * @access private
     */
    function _save_grouped_memberships()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $groups = array ();
        $count = count($_POST['sortable']);
        
        foreach ($_POST['sortable'] as $i => $value)
        {
            $args = explode('::', $value);
            if (count($args) !== 3)
            {
                continue;
            }
            
            if ($args[0] === 'group')
            {
                if ($args[1] === 'new')
                {
                    debug_add("Trying to create a new group with name '{$args[2]}'");
                    
                    $group = new midcom_db_group();
                    $group->owner = $this->_group->id;
                    $group->name = $args[2];
                    $group->official = $args[2];
                    
                    // Try to create a new group object
                    if (!$group->create())
                    {
                        debug_print_r('Failed to create a new midcom_db_group object, last mgd_errstr() was ' . mgd_errstr(), $group, MIDCOM_LOG_ERROR);
                        debug_pop();
                        
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a new group. See error level log for details. Last Midgard error was '. mgd_errstr());
                        // This will exit
                    }
                    
                    if (    $this->_topic->can_do('midgard:approve')
                        && isset($_POST['auto_approve']))
                    {
                        debug_print_r('Created a new midcom_db_group object', $group);
                        $metadata =& midcom_helper_metadata::retrieve($group);
                        $metadata->approve();
                    }
                }
                else
                {
                    $group = new midcom_db_group(str_replace('group_', '', $args[1]));
                }
                
                if (   !$group
                    || !$group->guid)
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not get the {$args[1]}");
                }
                
                $groups[] =& $group;
                
                // Get the original approval status
                $metadata =& midcom_helper_metadata::retrieve($group);
                $approval_status = false;
                
                // Get the approval status if metadata object is available
                if (   is_object($metadata)
                    && $metadata->is_approved())
                {
                    $approval_status = true;
                }
                
                if ($group->guid !== $this->_group->guid)
                {
                    $group->name = $args[2];
                    $group->official = $args[2];
                }
                
                // Set the order
                if (version_compare(mgd_version(), '1.8.2', '>='))
                {
                    $group->metadata->score = $count - $i;
                }
                else
                {
                    $group->set_parameter('net.nemein.personnel', 'score', $count - $i);
                }
                
                if (!$group->update())
                {
                    debug_print_r("Failed to update the group object", $group, MIDCOM_LOG_ERROR);
                    debug_add('Last Midgard error was: '.mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_pop();
                    
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to update the group object. See error level log for details.');
                    // This will exit
                }
                
                // Maintain the approval status - if the object had been approved before
                // it should still be kept as approved
                if (   $approval_status
                    || ($this->_topic->can_do('midgard:approve')
                        && isset($_POST['auto_approve'])))
                {
                    debug_add('Maintaining the approval status: setting the object back to be approved.');
                    $metadata =& midcom_helper_metadata::retrieve($group);
                    $metadata->approve();
                }
                continue;
            }
            
            
            $membership = new midcom_db_member(str_replace('membership_', '', $args[1]));
            
            if (   !$membership
                || !$membership->guid)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to get the membership '{$args[1]}', this is fatal!");
            }
            
            // Get the original approval status
            $metadata =& midcom_helper_metadata::retrieve($group);
            $approval_status = false;
            
            // Get the approval status if metadata object is available
            if (   is_object($metadata)
                && $metadata->is_approved())
            {
                $approval_status = true;
            }
            
            // Determine what to do with the membership
            if ($membership->gid !== $group->id)
            {
                // Original was master, create new
                if ($membership->gid === $this->_group->id)
                {
                    debug_add('Original membership was in the master group, creating a new membership to preserve the original but to use sub grouping');
                    
                    $new_membership = new midcom_db_member();
                    $new_membership->uid = $membership->uid;
                    $new_membership->gid = $group->id;
                    
                    // Set the order
                    if (version_compare(mgd_version(), '1.8.2', '>='))
                    {
                        $new_membership->metadata->score = $count - $i;
                    }
                    
                    if (!$new_membership->create())
                    {
                        debug_print_r("Failed to create the midcom_db_member object", $new_membership, MIDCOM_LOG_ERROR);
                        debug_add('Last Midgard error was: '.mgd_errstr(), MIDCOM_LOG_ERROR);
                        debug_pop();
                        
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to update the membership details. See error level log for details.');
                        // This will exit
                    }
                    
                    debug_print_r('New membership created successfully', $new_membership);
                    
                    // Possible to set the score only after creation of the new object
                    if (version_compare(mgd_version(), '1.8.2', '<'))
                    {
                        $group->set_parameter('net.nemein.personnel', 'score', $count - $i);
                    }
                    
                    // Maintain the approval status - if the object had been approved before
                    // it should still be kept as approved
                    if (   $approval_status
                        || ($this->_topic->can_do('midgard:approve')
                            && isset($_POST['auto_approve'])))
                    {
                        debug_add('Maintaining the approval status: setting the object back to be approved.');
                        $metadata =& midcom_helper_metadata::retrieve($new_membership);
                        $metadata->approve();
                    }
                }
                else if ($group->id === $this->_group->id)
                {
                    debug_add('Sub group changed into master group, deleting the sub group membership.');
                    
                    if (!$membership->delete())
                    {
                        debug_print_r('Failed to delete the midcom_db_member object due to '.mgd_errstr(), $membership, MIDCOM_LOG_ERROR);
                        debug_pop();
                        
                        $_MIDCOM->generate_erro(MIDCOM_ERRCRIT, 'Failed to change the membership status, see error level log for details.');
                        // This will exit
                    }
                    
                    debug_add('Membership deleted successfully!');
                    continue;
                }
                else
                {
                    debug_add("Sub group changed from {$membership->gid} to {$group->id}");
                    $membership->gid = $group->id;
                }
            }
            else
            {
                // Update also the master group membership to keep the sorting order
                $qb = midcom_db_member::new_query_builder();
                $qb->add_constraint('gid', '=', $this->_group->id);
                $qb->add_constraint('uid', '=', $membership->uid);
                $qb->set_limit(1);
                
                $result = $qb->execute_unchecked();
                
                if (   !$result[0]
                    || !isset($result[0]->guid)
                    || !$result[0]->guid)
                {
                    continue;
                }
                
                $root_membership =& $result[0];
                
                // Set the score order
                if (version_compare(mgd_version(), '1.8.2', '>='))
                {
                    $root_membership->metadata->score = $count - $i;
                }
                else
                {
                    $root_membership->set_parameter('net.nemein.personnel', 'score', $count - $i);
                }
                
                // Maintain the approval status - if the object had been approved before
                // it should still be kept as approved
                if (   $approval_status
                    || ($this->_topic->can_do('midgard:approve')
                        && isset($_POST['auto_approve'])))
                {
                    debug_add('Maintaining the approval status: setting the object back to be approved.');
                    $metadata =& midcom_helper_metadata::retrieve($result[0]);
                    $metadata->approve();
                }
            }
            
            // Set the score order
            if (version_compare(mgd_version(), '1.8.2', '>='))
            {
                $membership->metadata->score = $count - $i;
            }
            else
            {
                $membership->set_parameter('net.nemein.personnel', 'score', $count - $i);
            }
            
            
            if (!$membership->update())
            {
                debug_print_r('Failed to update the midcom_db_member object. Last error was '.mgd_errstr(), $membership);
                debug_pop();
                
                $_MIDCOM->generate_erro(MIDCOM_ERRCRIT, 'Failed to change the membership status, see error level log for details.');
                // This will exit
            }
            
            // Maintain the approval status - if the object had been approved before
            // it should still be kept as approved
            if ($approval_status)
            {
                debug_add('Maintaining the approval status: setting the object back to be approved.');
                $metadata =& midcom_helper_metadata::retrieve($membership);
                $metadata->approve();
            }
            
            debug_print_r('Membership updated successfully', $membership);
        }
        
        debug_add('Finished updating.');
        debug_pop();
        
        // Show confirmation for the user
        $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('net.nemein.personnel'), $this->_l10n->get('order saved'));
        
        return true;
    }
    
    /**
     * Handler for checking the request to sort personnel into groups
     * 
     * @access private
     * @return boolean Indicating success
     */
    function _handler_grouped($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_topic->require_do('midgard:update');
        
        // Get the parent group
        $this->_group = new midcom_db_group($this->_config->get('group'));
        
        if (   !$this->_group
            || !$this->_group->guid)
        {
            $_MIDCOM->relocate('config.html');
        }
        
        // Initialize the helper class for fetching sorted memberships
        $this->_helper = new net_nemein_personnel_sorted_groups($this->_config->get('group'), true);
        $this->_persons = $this->_helper->get_sorted_members();
        
        if (isset($_POST['f_cancel']))
        {
            $_MIDCOM->relocate('');
        }
        
        if (isset($_POST['f_submit']))
        {
            if ($this->_save_grouped_memberships())
            {
                $_MIDCOM->relocate('');
            }
        }
        
        $this->_load_headers();
        
        return true;
    }
    
    /**
     * Show personnel grouped into sub categories
     * 
     * @access private
     */
    function _show_grouped($handler_id, &$data)
    {
        $data['root_group'] =& $this->_group;
        $data['can_approve'] = $this->_topic->can_do('midgard:approve');
        
        midcom_show_style('admin-order-grouped-header');
        
        foreach ($this->_helper->groups as $i => $group)
        {
            $data['index'] = $i;
            $data['group'] =& $group;
            
            if ($i === 'unsorted')
            {
                midcom_show_style('admin-order-group-header-unsorted');
                $data['group']->official = $this->_l10n->get('unsorted');
            }
            else
            {
                midcom_show_style('admin-order-group-header');
            }
            
            // Bulletproofing against groups without personnel
            if (   !isset($this->_persons[$group->id])
                || !is_array($this->_persons[$group->id]))
            {
                midcom_show_style('admin-order-group-footer');
                continue;
            }
            
            foreach ($this->_persons[$group->id] as $membership_guid => $person)
            {
                $data['person'] =& $person;
                $data['membership_guid'] = $membership_guid;
                midcom_show_style('admin-order-group-person');
            }
            
            midcom_show_style('admin-order-group-footer');
        }
        
        midcom_show_style('admin-order-grouped-footer');
    }
}
?>
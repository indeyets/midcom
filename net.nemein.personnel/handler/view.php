<?php
/**
 * @package net.nemein.personnel
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * View page handler, renders index and detail views.
 *
 * @package net.nemein.personnel
 */

class net_nemein_personnel_handler_view extends midcom_baseclasses_components_handler
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
     * @var array
     * @access private
     */
    var $_persons = null;

    /**
     * The Datamanager of the person to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The filter character used in alphabetic indexing mode.
     *
     * @var string
     * @access private
     */
    var $_alpha_filter = null;

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
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Internal helper which wraps the membership->person transformation in an
     * ACL safe way.
     *
     * @param array $membership A resultset that was queried using midcom_baseclasses_database_member::new_query_builder()
     * @return array An array of midcom_baseclasses_database_person() objects.
     */
    function _get_persons_for_memberships($memberships)
    {
        $result = array();
        foreach ($memberships as $membership)
        {
            $person = new midcom_db_person($membership->uid);
            if (   $person
                && $person->is_object_visible_onsite())
            {
                // We have access to the person.
                $result[] = $person;
            }
        }
        return $result;
    }

    /**
     * This function creates a DM2 Datamanager instance to without any set storage so far.
     * The configured schema will be selected, but no set_storage is done. The various
     * view handlers treat this differently.
     *
     * @access private
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
     * This function creates a DM2 Datamanager instance to without any set storage so far.
     * The configured schema will be selected, but no set_storage is done. The various
     * view handlers treat this differently.
     *
     * @access private
     */
    function _load_datamanager_for_groups()
    {
        $this->_dm_group = new midcom_helper_datamanager2_datamanager(
            midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb')));

        if (   ! $this->_dm_group
            || ! $this->_dm_group->set_schema($this->_config->get('schema_group')))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a DM2 instance.');
            // This will exit.
        }
    }

    /**
     * Can-Handle check against the person username and GUID (in this order). We have to do
     * this explicitly in can_handle already, otherwise we would hide all subtopics as the
     * request switch accepts all argument count matches unconditionally.
     *
     * If an argument matches both a GUID and a username (highly improbable), the result is
     * undefined.
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success
     */
    function _can_handle_person ($handler_id, $args, &$data)
    {
        if ($handler_id === 'view-grouped-person')
        {
            $arg = $args[1];
            $this->_group = new midcom_db_group($args[0]);
        }
        else
        {
            $arg = $args[0];
        }

        if (!$this->_config->get('group'))
        {
            return false;
        }
        $qb = midcom_db_member::new_query_builder();

        $qb->add_constraint('gid.guid', '=', $this->_config->get('group'));
        $qb->begin_group('OR');
            $qb->add_constraint('uid.guid', '=', $arg);
            $qb->add_constraint('uid.username', '=', $arg);
        $qb->end_group();

        $qb->set_limit(1);
        $qb->hide_invisible = false;

        //mgd_debug_start();
        $result = $qb->execute_unchecked();
        //mgd_debug_stop();

        if (! $result)
        {
            if ($qb->denied)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRDENIED,
                    "You do not have sufficient privileges to view the memberships of person '{$arg}'.");
               // This will exit.
            }
            elseif (mgd_is_guid($arg))
            {
                $person = new midcom_db_person($arg);
                if (!$person)
                {
                    return false;
                }
                $this->_person = $person;
                return true;
            }
            else
            {
                return false;
            }
        }

        $person = new midcom_db_person($result[0]->uid);
        if (! $person)
        {
            if (mgd_errno() == MGD_ERR_ACCESS_DENIED)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRDENIED,
                    "You do not have sufficient privileges to view the person '{$arg}'.");
               // This will exit.
            }
            else
            {
                // Normally, this should not happen, as the uid.xxx constraints of the QB
                // ensure that the UID can be resolved. But, ultimately, you never know,
                // DB inconsistencies can wreak havoc on assumptions like this.
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The membership Record '{$result[0]->id}' points to a user which failed to load, skipping silently.",
                    MIDCOM_LOG_INFO);
                debug_print_r('Record retrieved:', $result[0]);
                debug_pop();
                return false;
            }
        }

        $this->_person = $person;
        return true;
    }

    /**
     * Displays the detail view of a given person.
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success
     */
    function _handler_person($handler_id, $args, &$data)
    {
        $this->_load_datamanager();
        $this->_datamanager->set_storage($this->_person);

        if ($this->_person->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "admin/edit/{$this->_person->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'e',
                )
            );
        }
        if ($this->_person->can_do('midgard:delete'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "admin/delete/{$this->_person->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'd',
                )
            );
        }

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "account/{$this->_person->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('user account'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'a',
            )
        );

        $_MIDCOM->set_26_request_metadata($this->_person->metadata->revised, $this->_person->guid);
        $this->_view_toolbar->bind_to($this->_person);
        $this->_prepare_request_data();

        $tmp = array();

        if ($this->_config->get('enable_alphabetical'))
        {
            $this->_alpha_filter = $this->_person->lastname[0];
            $tmp[] = array
            (
                MIDCOM_NAV_URL => "alpha/{$this->_alpha_filter}/",
                MIDCOM_NAV_NAME => $this->_alpha_filter,
            );
        }

        // Set the active navigation leaf
        if ($handler_id === 'view-grouped-person')
        {
            switch ($this->_config->get('display_in_navigation'))
            {
                case 'groups':
                    $this->_component_data['active_leaf'] = $this->_group->guid;
                    $tmp[$this->_person->guid] = array
                    (
                        MIDCOM_NAV_URL => "group/{$this->_person->guid}/{$this->_person->guid}/",
                        MIDCOM_NAV_NAME => $this->_person->name,
                    );
                    break;

                case 'personnel':
                    $this->_component_data['active_leaf'] = $this->_person->guid;
                    break;

                default:
                    $tmp[$this->_group->guid] = array
                    (
                        MIDCOM_NAV_URL => "group/{$this->_group->guid}/",
                        MIDCOM_NAV_NAME => ($this->_group->official) ? $this->_group->official : $this->_group->name,
                    );
                    $tmp[$this->_person->guid] = array
                    (
                        MIDCOM_NAV_URL => "group/{$this->_group->guid}/{$this->_person->guid}/",
                        MIDCOM_NAV_NAME => $this->_person->name,
                    );

            }
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_person->name}");

        return true;
    }

    /**
     * Displays the detail view of a given person
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_person($handler_id, &$data)
    {
        midcom_show_style('show-person');
    }

    /**
     * Returns a post-processed list of persons to display on the index page.
     */
    function _load_index_persons()
    {
        $qb = midcom_db_member::new_query_builder();

        $qb->add_constraint('gid.guid', '=', $this->_config->get('group'));

        if ($this->_alpha_filter)
        {
            $qb->add_constraint('uid.lastname', 'LIKE', "{$this->_alpha_filter}%");
        }

        foreach ($this->_config->get('index_order') as $ordering)
        {
            $qb->add_order($ordering);
        }

        $qb->hide_invisible = false;

        $this->_persons = $this->_get_persons_for_memberships($qb->execute());

        if ($this->_config->get('preferred_person'))
        {
            $this->_process_preferred_person();
        }
    }

    /**
     * Iterates over the _persons member and pushes the preferred person into the
     * front of it.
     */
    function _process_preferred_person()
    {
        $new_persons = array();
        $preferred_person = null;
        $preferred_person_guid = $this->_config->get('preferred_person');

        foreach ($this->_persons as $person)
        {
            if ($person->guid == $preferred_person_guid)
            {
                $preferred_person = $person;
            }
            else
            {
                $new_persons[] = $person;
            }
        }

        if ($preferred_person)
        {
            array_unshift($new_persons, $preferred_person);
            $this->_persons = $new_persons;
        }
    }

    /**
     * Renders the Person Index. If alphabetic indexing is enabled, the filter char
     * is extracted and set so that the index is limited accordingly. (Defaults to 'A'
     * in case no filter is specified.)
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        if (!$this->_config->get('group'))
        {
            $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "config/");
        }
        
        // Get the root group
        $this->_group = new midcom_db_group($this->_config->get('group'));
        $this->_load_datamanager_for_groups();
        
        // Pass the reference for external usage
        $data['group'] =& $this->_group;

        if ($this->_config->get('enable_alphabetical'))
        {
            if ($handler_id == 'view-index-alpha')
            {
                if (ctype_alpha($args[0]))
                {
                    $this->_alpha_filter = strtoupper($args[0]);
                }
                else
                {
                    $_MIDCOM->generate_error(MIDCOM_ERR_NOTFOUND,
                        "The Argument '{$args[0]}' is not a valid alphabetic string. Use a-z only.");
                    // This will exit.
                }
            }
            else
            {
                $this->_alpha_filter = 'A';
            }
            $this->_request_data['alpha_filter'] = $this->_alpha_filter;

            $tmp = array();
            $tmp[] = array
            (
                MIDCOM_NAV_URL => "alpha/{$this->_alpha_filter}/",
                MIDCOM_NAV_NAME => $this->_alpha_filter,
            );
            $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        }

        switch ($this->_config->get('sort_order'))
        {
            case 'sorted':
                $this->_persons[] = $this->get_group_members($this->_group->id);
                break;

            case 'sorted and grouped':
                $mc = midcom_db_group::new_collector('owner', $this->_group->id);
                $mc->add_value_property('id');
                $mc->add_constraint('metadata.navnoentry', '<>', 1);
                $mc->add_order('metadata.score', 'DESC');
                $mc->execute();
                
                foreach ($mc->list_keys() as $group => $array)
                {
                    $group_id = $mc->get_subkey($group, 'id');
                    $this->_persons[$group_id] = $this->get_group_members($group_id);
                }
                
                if (!$this->_group->metadata->hidden)
                {
                    $this->_persons[$this->_group->id] = $this->get_group_members($this->_group->id);
                }
                
                break;

            default:
                $this->_load_index_persons();
                break;
        }

        $this->_load_datamanager();

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "admin/edit/group/{$data['group']->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit group'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_people.png',
            )
        );

        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $this->_prepare_request_data();
        $_MIDCOM->set_pagetitle($this->_topic->extra);

        $this->_view_toolbar->bind_to($this->_group);

        return true;
    }
    
    /**
     * Get the people belonging to the requested group
     *
     * @static
     * @access public
     * @param int $id     ID of the requested group
     * @return Array      Containing the midcom_db_person objects
     */
    function get_group_members($id)
    {
        $mc = midcom_db_member::new_collector('gid', $id);
        $mc->add_value_property('uid');
        $mc->add_constraint('metadata.navnoentry', '=', 0);
        $mc->add_order('metadata.score', 'DESC');
        $mc->execute();
        
        // Check if the order is forced
        if (   ($forced = $this->_config->get('force_sort_key'))
            && array_key_exists($forced, get_object_vars(new midcom_db_person())))
        {
            $temp = array();
        }
        else
        {
            $forced = false;
            $temp = false;
        }
        
        $persons = array();
        
        // Get the memberships and eventually the persons
        foreach ($mc->list_keys() as $guid => $array)
        {
            $person = new midcom_db_person($mc->get_subkey($guid, 'uid'));
            if ($person->metadata->hidden)
            {
                continue;
            }
            
            if ($forced)
            {
                $temp[$person->guid] = $person->$forced;
                $persons_holder[$person->guid] = $person;
                continue;
            }
            
            $persons[] = $person;
        }
        
        // Get the force-sorted person objects
        if ($temp)
        {
            // Sort the order
            if (!preg_match('/desc/i', $this->_config->get('force_sort_order')))
            {
                asort($temp);
            }
            else
            {
                // Reverse sort the order
                arsort($temp);
            }
            
            foreach ($temp as $guid => $value)
            {
                $persons[] = $persons_holder[$guid];
            }
        }
        
        return $persons;
    }

    /**
     * Show grouped personnel records
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access public
     */
    function _show_grouped($handler_id, &$data)
    {
        $data['topic'] =& $this->_topic;
        $data['root_group'] =& $this->_group;

        $this->_dm_group->set_storage($this->_group);
        $data['datamanager'] =& $this->_dm_group;

        midcom_show_style('show-grouped-header');

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $this->_display_group($this->_persons, &$data);

        midcom_show_style('show-grouped-footer');
    }
    
    /**
     * Display groups
     * 
     * @access private
     * @param array $array    Array consisting of midcom_db_group::id => array of midcom_db_person
     */
    function _display_group($array, &$data)
    {
        $data['row'] = 1;
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        
        foreach ($array as $group_id => $persons)
        {
            if (   $group_id === $this->_group->id
                && $this->_config->get('show_unsorted') === false)
            {
                continue;
            }

            $data['group'] = new midcom_db_group($group_id);
            $this->_dm_group->set_storage($data['group']);
            $data['datamanager'] =& $this->_dm_group;

            midcom_show_style('show-group-header');

            midcom_show_style('show-group-row-header');

            $i = 0;
            foreach ($persons as $person)
            {
                $this->_datamanager->set_storage($person);
                $data['datamanager'] =& $this->_datamanager;
                $data['person'] =& $person;

                $url = net_nemein_personnel_viewer::get_url($person, $this->_group->guid);

                $data['column'] = (int) fmod($i, (int) $this->_config->get('persons_in_row')) + 1;
                $data['view_url'] = "{$prefix}{$url}";

                midcom_show_style('show-group-person');
                $i++;

                if ((int) fmod($i, (int) $this->_config->get('persons_in_row')) === 0)
                {
                    $data['row']++;
                    midcom_show_style('show-group-row-footer');
                    midcom_show_style('show-group-row-header');
                }
            }

            while ((int) fmod($i, (int) $this->_config->get('persons_in_row')) !== 0)
            {
                $data['column'] = (int) fmod($i, $this->_config->get('persons_in_row')) + 1;
                midcom_show_style('show-group-empty-cell');
                $i++;
            }

            midcom_show_style('show-group-row-footer');

            midcom_show_style('show-group-footer');
        }
    }

    /**
     * Renders the Person Index.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_index ($handler_id, &$data)
    {
        if ($this->_config->get('sort_order') === 'sorted and grouped')
        {
            $this->_show_grouped($handler_id, &$data);
            return;
        }

        if ($this->_persons)
        {
            $data['persons'] =& $this->_persons;
            
            midcom_show_style('show-index-header');

            $current_col = 0;
            $max_cols = (int) $this->_config->get('persons_in_row');
            if ($max_cols < 1)
            {
                $max_cols = 3;
            }
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

            foreach ($this->_persons as $person)
            {
                // Finalize the request data
                $this->_person = $person;
                $this->_datamanager->set_storage($this->_person);
                $url = net_nemein_personnel_viewer::get_url($this->_person, $this->_group->guid);

                $data['view_url'] = "{$prefix}{$url}";

                if ($current_col == 0)
                {
                    midcom_show_style('show-index-row-header');
                }

                midcom_show_style('show-index-item');

                $current_col++;

                if ($current_col >= $max_cols)
                {
                    midcom_show_style('show-index-row-footer');
                    $current_col = 0;
                }
            }

            // Finish the table if necessary
            if ($current_col > 0)
            {
                for (; $current_col < $max_cols; $current_col++)
                {
                    midcom_show_style('show-index-item-empty');
                }
                midcom_show_style('show-index-row-footer');
            }

            midcom_show_style('show-index-footer');
        }
        else
        {
            midcom_show_style('show-index-empty');
        }
    }

    /**
     * Check the request for showing people of one single group
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success
     */
    function _handler_group($handler_id, $args, &$data)
    {
        $master_group = new midcom_db_group($this->_config->get('group'));

        if (   !$master_group
            || !$master_group->id)
        {
            $_MIDCOM->relocate('config/');
            // This will exit
        }

        // Relocate back to master page if trying to request for a simple master group view
        if ($args[0] === $this->_config->get('group'))
        {
            $_MIDCOM->relocate('');
            // This will exit
        }

        $this->_group = new midcom_db_group($args[0]);
        $data['group'] =& $this->_group;

        if (   !$data['group']
            || !$data['group']->id
            || $data['group']->owner !== $master_group->id)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, 'The requested group was not found!');
            // This will exit
        }

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "admin/edit/group/{$data['group']->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit group'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_people.png',
            )
        );
        
        // Get the group members
        $this->_persons[$this->_group->id] = $this->get_group_members($this->_group->id);

        $this->_view_toolbar->bind_to($data['group']);

        $this->_load_datamanager();
        $this->_load_datamanager_for_groups();

        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $this->_prepare_request_data();
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['group']->official}");

        $this->_view_toolbar->bind_to($data['group']);

        // Set the breadcrumb
        switch ($this->_config->get('display_in_navigation'))
        {
            case 'groups':
                $this->_component_data['active_leaf'] = $this->_group->guid;
                if (isset($this->_person))
                {
                    $tmp[] = array
                    (
                        MIDCOM_NAV_URL => "group/{$this->_person->guid}/{$this->_person->guid}/",
                        MIDCOM_NAV_NAME => $this->_person->name,
                    );
                }
                break;

            default:
                $tmp[$this->_group->guid] = array
                (
                    MIDCOM_NAV_URL => "group/{$this->_group->guid}/",
                    MIDCOM_NAV_NAME => ($this->_group->official) ? $this->_group->official : $this->_group->name,
                );
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        if ($this->_group->official)
        {
            $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_group->official}");
        }
        else
        {
            $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_group->name}");
        }

        return true;
    }

    /**
     * Show a group listing
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access public
     */
    function _show_group($handler_id, &$data)
    {
        $this->_dm_group->set_storage(&$data['group']);
        $data['datamanager'] =& $this->_dm_group;

        $this->_display_group($this->_persons, &$data);
    }
}
?>
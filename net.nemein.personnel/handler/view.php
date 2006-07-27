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
    function net_nemein_personnel_handler_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Internal helper which wraps the membership->person transformation in an
     * ACL safe way.
     *
     * @param Array $membership A resultset that was queried using midcom_baseclasses_database_member::new_query_builder()
     * @return Array An array of midcom_baseclasses_database_person() objects.
     */
    function _get_persons_for_memberships($memberships)
    {
        $result = Array();
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
     * Can-Handle check against the person username and GUID (in this order). We have to do
     * this explicitly in can_handle already, otherwise we would hide all subtopics as the
     * request switch accepts all argument count matches unconditionally.
     *
     * If an argument matches both a GUID and a username (highly improbable), the result is
     * undefined.
     */
    function _can_handle_person ($handler_id, $args, &$data)
    {
        if (!$this->_config->get('group'))
        {
            return false;
        }
        $qb = midcom_db_member::new_query_builder();

        if (version_compare(mgd_version(), '1.7', '>'))
        {
            $qb->add_constraint('gid.guid', '=', $this->_config->get('group'));
            $qb->begin_group('OR');
            $qb->add_constraint('uid.guid', '=', $args[0]);
            $qb->add_constraint('uid.username', '=', $args[0]);
            $qb->end_group();
        }
        else
        {
            $group = new midcom_db_group($this->_config->get('group'));
            $qb->add_constraint('gid.id', '=', $group->id);
            $qb->add_constraint('uid.username', '=', $args[0]);
        }

        $qb->set_limit(1);
        $qb->hide_invisible = false;

        mgd_debug_start();
        $result = $qb->execute_unchecked();
        mgd_debug_stop();

        if (! $result)
        {
            if ($qb->denied)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRDENIED,
                    "You do not have sufficient privileges to view the memberships of person '{$args[0]}'.");
               // This will exit.
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
                    "You do not have sufficient privileges to view the person '{$args[0]}'.");
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
     */
    function _handler_person($handler_id, $args, &$data)
    {
        $this->_load_datamanager();
        $this->_datamanager->set_storage($this->_person);

        if ($this->_person->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "admin/edit/{$this->_person->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            ));
        }
        if ($this->_person->can_do('midgard:delete'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "admin/delete/{$this->_person->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            ));
        }

        if (version_compare(mgd_version(), '1.7', '>'))
        {
            $_MIDCOM->set_26_request_metadata($this->_person->metadata->revised, $this->_person->guid);
        }
        $this->_view_toolbar->bind_to($this->_person);
        $this->_prepare_request_data();


        $tmp = Array();
        if ($this->_config->get('enable_alphabetical'))
        {
            $this->_alpha_filter = $this->_person->lastname[0];
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "alpha/{$this->_alpha_filter}.html",
                MIDCOM_NAV_NAME => $this->_alpha_filter,
            );
        }
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => net_nemein_personnel_viewer::get_url($this->_person),
            MIDCOM_NAV_NAME => $this->_person->name,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_person->name}");

        return true;
    }

    /**
     * Displays the detail view of a given person
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
        
        if (version_compare(mgd_version(), '1.7', '>'))
        {
            $qb->add_constraint('gid.guid', '=', $this->_config->get('group'));
    
            if ($this->_alpha_filter)
            {
                $qb->add_constraint('uid.lastname', 'LIKE', "{$this->_alpha_filter}%");
            }
    
            foreach ($this->_config->get('index_order') as $ordering)
            {
                $qb->add_order($ordering);
            }
        }
        else
        {
            $group = new midcom_db_group($this->_config->get('group'));
            $qb->add_constraint('gid.id', '=', $group->id);
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
        $new_persons = Array();
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
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        if (!$this->_config->get('group'))
        {
            $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "config.html");
        }
    
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

            $tmp = Array();
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "alpha/{$this->_alpha_filter}.html",
                MIDCOM_NAV_NAME => $this->_alpha_filter,
            );
            $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        }

        $this->_load_index_persons();
        $this->_load_datamanager();

        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $this->_prepare_request_data();
        $_MIDCOM->set_pagetitle($this->_topic->extra);

        return true;
    }

    /**
     * Renders the Person Index.
     */
    function _show_index ($handler_id, &$data)
    {
        if ($this->_persons)
        {
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
                $url = net_nemein_personnel_viewer::get_url($this->_person);
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

            // Finish the table if neccessary
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
}
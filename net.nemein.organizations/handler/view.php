<?php
/**
 * @package net.nemein.organizations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 4840 2006-12-29 06:25:07Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * View page handler, renders index and detail views.
 *
 * @package net.nemein.organizations
 */

class net_nemein_organizations_handler_view extends midcom_baseclasses_components_handler
{
    /**
     * The group to display in single view mode.
     *
     * @var org_openpsa_contacts_group
     * @access private
     */
    var $_group = null;

    /**
     * The groups to display on the index page, already ordered correctly.
     *
     * @var Array
     * @access private
     */
    var $_groups = null;

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
        $this->_request_data['group'] =& $this->_group;
        $this->_request_data['groups'] =& $this->_groups;
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }

    /**
     * Simple default constructor.
     */
    function net_nemein_organizations_handler_view()
    {
        parent::midcom_baseclasses_components_handler();
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
     * Can-Handle check against the group username and GUID (in this order). We have to do
     * this explicitly in can_handle already, otherwise we would hide all subtopics as the
     * request switch accepts all argument count matches unconditionally.
     *
     * If an argument matches both a GUID and a username (highly improbable), the result is
     * undefined.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean True if the request can be handled, false otherwise.
     */
    function _can_handle_group ($handler_id, $args, &$data)
    {
        if (!$this->_config->get('group'))
        {
            return false;
        }
        $qb = org_openpsa_contacts_group::new_query_builder();

        $parent = new org_openpsa_contacts_group($this->_config->get('group'));
        $qb->add_constraint('owner', '=', $parent->id);
        $qb->begin_group('OR');
            $qb->add_constraint('name', '=', $args[0]);
            //$qb->add_constraint('guid', '=', $args[0]);
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
                    "You do not have sufficient privileges to view the memberships of group '{$args[0]}'.");
               // This will exit.
            }
            elseif (mgd_is_guid($args[0]))
            {
                $group = new org_openpsa_contacts_group($args[0]);
                if (   !$group
                    || $group->owner != $parent->id)
                {
                    return false;
                }
                $this->_group = $group;
                return true;
            }
            else
            {
                return false;
            }
        }

        $group = $result[0];
        if (! $group)
        {
            if (mgd_errno() == MGD_ERR_ACCESS_DENIED)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRDENIED,
                    "You do not have sufficient privileges to view the group '{$args[0]}'.");
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

        $this->_group = $group;
        return true;
    }

    /**
     * Displays the detail view of a given groowner.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_group($handler_id, $args, &$data)
    {
        $this->_load_datamanager();
        $this->_datamanager->set_storage($this->_group);

        if ($this->_group->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "admin/edit/{$this->_group->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            ));
        }
        if ($this->_group->can_do('midgard:delete'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "admin/delete/{$this->_group->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            ));
        }

        if (version_compare(mgd_version(), '1.8.0alpha1', '>'))
        {
            $_MIDCOM->set_26_request_metadata($this->_group->metadata->revised, $this->_group->guid);
        }
        $this->_view_toolbar->bind_to($this->_group);
        $this->_prepare_request_data();


        $tmp = Array();
        if ($this->_config->get('enable_alphabetical'))
        {
            $this->_alpha_filter = $this->_group->official[0];
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "alpha/{$this->_alpha_filter}.html",
                MIDCOM_NAV_NAME => $this->_alpha_filter,
            );
        }
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => net_nemein_organizations_viewer::get_url($this->_group),
            MIDCOM_NAV_NAME => $this->_group->official,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_group->official}");

        return true;
    }

    /**
     * Displays the detail view of a given group
     */
    function _show_group($handler_id, &$data)
    {
        midcom_show_style('show-group');
    }

    /**
     * Returns a post-processed list of groups to display on the index page.
     */
    function _load_index_groups()
    {
        $qb = org_openpsa_contacts_group::new_query_builder();

        $parent = new org_openpsa_contacts_group($this->_config->get('group'));
        $qb->add_constraint('owner', '=', $parent->id);

        if ($this->_alpha_filter)
        {
            $qb->add_constraint('official', 'LIKE', "{$this->_alpha_filter}%");
        }

        foreach ($this->_config->get('index_order') as $ordering)
        {
            $qb->add_order($ordering);
        }

        $qb->hide_invisible = false;

        $this->_groups = $qb->execute();

        if ($this->_config->get('preferred_group'))
        {
            $this->_process_preferred_group();
        }
    }

    /**
     * Iterates over the _groups member and pushes the preferred group into the
     * front of it.
     */
    function _process_preferred_group()
    {
        $new_groups = Array();
        $preferred_group = null;
        $preferred_group_guid = $this->_config->get('preferred_group');

        foreach ($this->_groups as $group)
        {
            if ($group->guid == $preferred_group_guid)
            {
                $preferred_group = $group;
            }
            else
            {
                $new_groups[] = $group;
            }
        }

        if ($preferred_group)
        {
            array_unshift($new_groups, $preferred_group);
            $this->_groups = $new_groups;
        }
    }

    /**
     * Renders the Group Index. If alphabetic indexing is enabled, the filter char
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

        $this->_load_index_groups();
        $this->_load_datamanager();

        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $this->_prepare_request_data();
        $_MIDCOM->set_pagetitle($this->_topic->extra);

        return true;
    }

    /**
     * Renders the Group Index.
     */
    function _show_index ($handler_id, &$data)
    {
        if ($this->_groups)
        {
            midcom_show_style('show-index-header');

            $current_col = 0;
            $max_cols = (int) $this->_config->get('groups_in_row');
            if ($max_cols < 1)
            {
                $max_cols = 3;
            }
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

            foreach ($this->_groups as $group)
            {
                // Finalize the request data
                $this->_group = $group;
                $this->_datamanager->set_storage($this->_group);
                $url = net_nemein_organizations_viewer::get_url($this->_group);
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
}

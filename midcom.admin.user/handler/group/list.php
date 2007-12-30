<?php
/**
 * @package midcom.admin.user
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * List groups
 *
 * @package midcom.admin.user
 */
class midcom_admin_user_handler_group_list extends midcom_baseclasses_components_handler
{
    /**
     * Currently viewed group
     *
     * @access private
     * @var midcom_db_group
     */
    var $_group = null;

    /**
     * Simple constructor, call for the parent class contructor
     *
     * @access public
     */
    function midcom_admin_user_handler_group_list()
    {
        $this->_component = 'midcom.admin.user';
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {

        $this->_l10n = $_MIDCOM->i18n->get_l10n('midcom.admin.user');
        $this->_request_data['l10n'] = $this->_l10n;

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.admin.user/usermgmt.css',
            )
        );

        midgard_admin_asgard_plugin::prepare_plugin($this->_l10n->get('midcom.admin.user'), $this->_request_data);
    }

    function _update_breadcrumb($handler_id)
    {
        // Populate breadcrumb
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.user/",
            MIDCOM_NAV_NAME => $this->_l10n->get('midcom.admin.user', 'midcom.admin.user'),
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.user/group/",
            MIDCOM_NAV_NAME => $this->_l10n->get('groups'),
        );

        if (preg_match('/group_move$/', $handler_id))
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.user/group/{$this->_request_data['group']->guid}/",
                MIDCOM_NAV_NAME => $this->_request_data['group']->official,
            );
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.user/group/move/{$this->_request_data['group']->guid}/",
                MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('move', 'midcom'),
            );
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Handle the moving of a group phase
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_move($handler_id, $args, &$data)
    {
        $data['group'] = new midcom_db_group($args[0]);

        if (   !$data['group']
            || !$data['group']->guid)
        {
            return false;
        }

        // Get the prefix
        $data['prefix'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        if (isset($_POST['f_cancel']))
        {
            $_MIDCOM->relocate("__mfa/asgard_midcom.admin.user/group/edit/{$data['group']->guid}/");
            // This will exit
        }

        if (isset($_POST['f_submit']))
        {
            echo "<pre>\n";
            print_r($_POST);
            echo "</pre>\n";
            $data['group']->owner = (int) $_POST['midcom_admin_user_move_group'];

            if ($data['group']->update())
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midcom.admin.user'), $_MIDCOM->i18n->get_string('updated', 'midcom'));
                $_MIDCOM->relocate("__mfa/asgard_midcom.admin.user/group/edit/{$data['group']->guid}/");
                // This will exit
            }
            else
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('Failed to update the group, last mgd_errstr was '. mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_print_r('We operated on this object', $data['group'], MIDCOM_LOG_ERROR);
                debug_pop();

                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to update the group, see error level log for details');
                // This will exit
            }
        }

        // Set the toolbar
        $data['asgard_toolbar'] = new midcom_helper_toolbar();
        midgard_admin_asgard_plugin::get_common_toolbar($data);

        $data['view_title'] = sprintf($this->_l10n->get('move %s'), $data['group']->official);
        $_MIDCOM->set_pagetitle($data['view_title']);

        $this->_update_breadcrumb($handler_id);
        return true;
    }

    /**
     * Show the moving of a group phase
     *
     * @access public
     */
    function _show_move($handler_id, &$data)
    {
        midgard_admin_asgard_plugin::asgard_header();
        midcom_show_style('midcom-admin-user-group-list-start');

        // Show the form headers
        midcom_show_style('midcom-admin-user-move-group-start');

        // Show the recursive listing
        $this->list_groups(0, &$data, true);

        // Show the form footers
        midcom_show_style('midcom-admin-user-move-group-end');

        midcom_show_style('midcom-admin-user-group-list-end');
        midgard_admin_asgard_plugin::asgard_footer();
    }

    /**
     * Handle the listing phase
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        // Get the prefix
        $data['prefix'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $data['view_title'] = $_MIDCOM->i18n->get_string('groups', 'midcom.admin.user');

        // Set the toolbar
        $data['asgard_toolbar'] = new midcom_helper_toolbar();
        midgard_admin_asgard_plugin::get_common_toolbar($data);

        $_MIDCOM->set_pagetitle($data['view_title']);

        $this->_update_breadcrumb($handler_id);
        return true;
    }

    /**
     * Show the group listing
     */
    function _show_list($handler_id, &$data)
    {
        midgard_admin_asgard_plugin::asgard_header();
        midcom_show_style('midcom-admin-user-group-list-start');

        // Show the recursive listing
        $this->list_groups(0, &$data);

        midcom_show_style('midcom-admin-user-group-list-end');
        midgard_admin_asgard_plugin::asgard_footer();
    }

    /**
     * Internal helper for showing the groups recursively
     *
     * @access private
     * @param int $id
     * @param array &$data
     */
    function list_groups($id, &$data, $move = false)
    {
        $mc = midcom_db_group::new_collector('owner', (int) $id);
        $mc->add_value_property('name');
        $mc->add_value_property('official');
        $mc->add_value_property('id');

        // Hide SG0 groups if not in SG0 view
        if ($_MIDGARD['sitegroup'] !== 0)
        {
            $mc->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        }

        // Set the order
        $mc->add_order('metadata.score');
        $mc->add_order('official');
        $mc->add_order('name');

        // Get the results
        $mc->execute();
        $keys = $mc->list_keys();

        // Hide empty groups
        if ($mc->count() === 0)
        {
            return;
        }

        $data['parent_id'] = $id;

        // Group header
        midcom_show_style('midcom-admin-user-group-list-header');

        // Show the groups
        foreach ($keys as $guid => $array)
        {
            $data['guid'] = $guid;
            $data['id'] = $mc->get_subkey($guid, 'id');
            $data['name'] = $mc->get_subkey($guid, 'name');

            if (($title = $mc->get_subkey($guid, 'official')))
            {
                $data['title'] = $title;
            }
            else
            {
                $data['title'] = $data['name'];
            }

            if (!$data['title'])
            {
                $data['title'] = $_MIDCOM->i18n->get_string('unknown', 'midcom.admin.user');
            }

            // Show the group
            if ($move)
            {
                // Prevent moving owner to any of its children
                $data['disabled'] = false;
                if (midcom_admin_user_handler_group_list::belongs_to($data['id'], $data['group']->id))
                {
                    $data['disabled'] = true;
                }

                midcom_show_style('midcom-admin-user-group-list-group-move');
            }
            else
            {
                midcom_show_style('midcom-admin-user-group-list-group');
            }
        }

        // Group footer
        midcom_show_style('midcom-admin-user-group-list-footer');
    }

    /**
     * Internal helper to check if the requested group belongs to the haystack
     *
     * @access public
     * @static
     * @param int $id
     * @param int $owner
     */
    function belongs_to($id, $owner)
    {
        $i = 0;
        do
        {
            if ($id === $owner)
            {
                return true;
            }

            $mc = midcom_db_group::new_collector('id', $id);
            $mc->add_value_property('owner');
            $mc->set_limit(1);
            $mc->execute();

            $keys = $mc->list_keys();

            // Get the first array key
            foreach ($keys as $subguid => $array)
            {
                if ($mc->get_subkey($subguid, 'owner') === 0)
                {
                    return false;
                }

                $id = $mc->get_subkey($subguid, 'owner');
            }
        }
        while ($mc->count() > 0);

        return false;
    }
}
?>
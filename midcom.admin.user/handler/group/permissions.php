<?php
/**
 * @package midcom.admin.user
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Style editor class for listing style elements
 * 
 * @package midcom.admin.user
 */
class midcom_admin_user_handler_group_permissions extends midcom_baseclasses_components_handler
{
    var $_group = null;

    /**
     * Simple constructor
     * 
     * @access public
     */
    function midcom_admin_user_handler_group_permissions()
    {
        $this->_component = 'midcom.admin.user';
        parent::__construct();
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

        midgard_admin_asgard_plugin::prepare_plugin($this->_l10n->get('midcom.admin.user'),$this->_request_data);

    }
    
    function _update_breadcrumb()
    {
        // Populate breadcrumb
        $tmp = Array();
        $grp = $this->_group;
        
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.user/group/folders/{$grp->guid}",
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('folders', 'midcom.admin.user'),
        );
        
        while ($grp)
        {        
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.user/group/edit/{$grp->guid}",
                MIDCOM_NAV_NAME => $grp->official,
            );
            $grp = $grp->get_parent();
        }
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.user/",
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('midcom.admin.user', 'midcom.admin.user'),
        ); 
        $tmp = array_reverse($tmp);
        
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
    
    /**
     * Handler method for listing style elements for the currently used component topic
     *
     * @access private
     * @param string $handler_id Name of the used handler
     * @param mixed $args Array containing the variable arguments passed to the handler
     * @param mixed &$data Data passed to the show method
     * @return boolean Indicating successful request
     */
    function _handler_folders($handler_id, $args, &$data)
    {
        $this->_group = new midcom_db_group($args[0]);
        if (   !$this->_group
            || !$this->_group->guid)
        {
            return false;
        }
    
        $data['asgard_toolbar'] = new midcom_helper_toolbar();
        $data['language_code'] = '';
        midgard_admin_asgard_plugin::bind_to_object($this->_group, $handler_id, &$data);

        $qb = new midgard_query_builder('midcom_core_privilege_db');
        $qb->add_constraint('assignee', '=', "group:{$this->_group->guid}");
        $privileges = $qb->execute();
        $data['objects'] = array();
        $data['privileges'] = array();
        foreach ($privileges as $privilege)
        {
            if (!$privilege->objectguid)
            {
                // We're only interested in privs applying to objects now, skip
                continue;
            }
            $data['privileges'][] = $privilege->name;
            if (!isset($data['objects'][$privilege->objectguid]))
            {
                $data['objects'][$privilege->objectguid] = array();
            }
            $data['objects'][$privilege->objectguid][$privilege->name] = $privilege->value;
        }

        $data['view_title'] = sprintf($_MIDCOM->i18n->get_string('folders of %s', 'midcom.admin.user'), $this->_group->official);
        $_MIDCOM->set_pagetitle($data['view_title']);

        $this->_update_breadcrumb();
        
        return true;
    }
    
    /**
     * Show list of the style elements for the currently edited topic component
     * 
     * @access private
     * @param string $handler_id Name of the used handler
     * @param mixed &$data Data passed to the show method
     */
    function _show_folders($handler_id, &$data)
    {
        midgard_admin_asgard_plugin::asgard_header();        

        $data['group'] =& $this->_group;
        midcom_show_style('midcom-admin-user-group-folders');
        
        midgard_admin_asgard_plugin::asgard_footer();
    }
}
?>
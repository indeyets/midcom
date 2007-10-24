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
class midcom_admin_user_handler_list extends midcom_baseclasses_components_handler
{
    var $_persons = array();

    /**
     * Simple constructor
     * 
     * @access public
     */
    function midcom_admin_user_handler_list()
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

        midgard_admin_asgard_plugin::prepare_plugin($this->_l10n->get('midcom.admin.user'),$this->_request_data);

    }

    
    function _update_breadcrumb()
    {
        // Populate breadcrumb
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.user/",
            MIDCOM_NAV_NAME => $this->_request_data['view_title'],
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    function _prepare_toolbar(&$data)
    {
        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "__mfa/asgard_midcom.admin.user/create/",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('create user', 'midcom.admin.user'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_person-new.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_config->get('allow_manage_accounts'),
            )
        );
        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "__mfa/asgard_midcom.admin.user/group/create/",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('create group', 'midcom.admin.user'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_people-new.png',
            )
        );

        midgard_admin_asgard_plugin::get_common_toolbar($data);
    }
    
    /**
     * Handler method for listing style elements for the currently used component topic
     *
     * @access private
     * @param string $handler_id Name of the used handler
     * @param mixed $args Array containing the variable arguments passed to the handler
     * @param mixed $data Data passed to the show method
     * @return boolean Indicating successful request
     */
    function _handler_list($handler_id, $args, &$data)
    {   

        // See what fields we want to use in the search
        $data['search_fields'] = $this->_config->get('search_fields');
        $data['list_fields'] = $this->_config->get('list_fields');
        
        if (   isset($_POST['midcom_admin_user'])
            && is_array($_POST['midcom_admin_user'])
            && $_POST['midcom_admin_user_action'])
        {
            foreach ($_POST['midcom_admin_user'] as $person_id)
            {
                $person = new midcom_db_person($person_id);
                
                switch ($_POST['midcom_admin_user_action'])
                {
                    case 'removeaccount':
                        if (!$this->_config->get('allow_manage_accounts'))
                        {
                            break;
                        }
                        $person->parameter('net.nehmer.account', 'username', $person->username);
                        $person->username = '';
                        $person->password = '';
                        if ($person->update())
                        {
                            $_MIDCOM->uimessages->add($this->_l10n->get('midcom.admin.user'), sprintf($this->_l10n->get('user account revoked for %s'), $person->name));
                        }
                        break;
                        
                    case 'groupadd':
                        if (isset($_POST['midcom_admin_user_group']))
                        {
                            $member = new midcom_db_member();
                            $member->uid = $person->id;
                            $member->gid = (int) $_POST['midcom_admin_user_group'];
                            if ($member->create())
                            {
                                $_MIDCOM->uimessages->add($this->_l10n->get('midcom.admin.user'), sprintf($this->_l10n->get('user %s added to group'), $person->name));
                            }
                        }
                }
            }
        }
        
        if (isset($_REQUEST['midcom_admin_user_search']))
        {
            // Run the person-seeking QB
            $qb = midcom_db_person::new_query_builder();
            $qb->begin_group('OR');
                foreach ($data['search_fields'] as $field)
                {
                    $qb->add_constraint($field, 'LIKE', "{$_REQUEST['midcom_admin_user_search']}%");
                }
            $qb->end_group('OR');
            $qb->add_order('lastname');
            $qb->add_order('firstname');
            
            $this->_persons = $qb->execute();
        }
        else
        {
            // List all persons if there are less than N of them
            $qb = midcom_db_person::new_query_builder();
            
            if ($qb->count_unchecked() < $this->_config->get('list_without_search'))
            {
                $qb->add_order('lastname');
                $qb->add_order('firstname');
            
                $this->_persons = $qb->execute();
            }
        }
        
        $data['groups'] = array
        (
            0 => 'Midgard Administrators',
        );
        if (count($this->_persons) > 0)
        {
            $qb = midcom_db_group::new_query_builder();
            $groups = $qb->execute();
            foreach ($groups as $group)
            {
                $data['groups'][$group->id] = $group;
            }
        }
        
        $this->_update_breadcrumb();
        $this->_prepare_toolbar($data);
        $_MIDCOM->set_pagetitle($data['view_title']);        

        return true;
    }
    
    /**
     * Show list of the style elements for the currently edited topic component
     * 
     * @access private
     * @param string $handler_id Name of the used handler
     * @param mixed $data Data passed to the show method
     */
    function _show_list($handler_id, &$data)
    {
        midgard_admin_asgard_plugin::asgard_header();
        $data['config'] =& $this->_config;
        
        $data['persons'] =& $this->_persons;
        midcom_show_style('midcom-admin-user-personlist-header');
        
        $data['even'] = false;
        foreach ($data['persons'] as $person)
        {
            $data['person'] = $person;
            midcom_show_style('midcom-admin-user-personlist-item');
            if (!$data['even'])
            {
                $data['even'] = true;
            }
            else
            {
                $data['even'] = false;
            }
        }
        
        midcom_show_style('midcom-admin-user-personlist-footer');
        midgard_admin_asgard_plugin::asgard_footer();
        
    }
}
?>

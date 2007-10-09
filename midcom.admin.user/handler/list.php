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
    
    function _update_breadcrumb()
    {
        // Populate breadcrumb
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.user/",
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('user management', 'midcom.admin.user'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
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
        $data['view_title'] = $_MIDCOM->i18n->get_string('user management', 'midcom.admin.user');
        $_MIDCOM->set_pagetitle($data['view_title']);
        
        $data['asgard_toolbar'] = new midcom_helper_toolbar();   
        $data['asgard_toolbar']->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "__mfa/asgard_midcom.admin.user/user/create/",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('create user', 'midcom.admin.user'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_person.png',
            )
        );      
        
        // See what fields we want to use in the search
        $data['search_fields'] = $this->_config->get('search_fields');
        $data['list_fields'] = $this->_config->get('list_fields');
        
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
        
        $this->_update_breadcrumb();
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midgard.admin.asgard');
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.user');
        $_MIDCOM->skip_page_style = true;
        
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
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
        
        $data['persons'] =& $this->_persons;
        midcom_show_style('midcom-admin-user-personlist-header');
        
        foreach ($data['persons'] as $person)
        {
            $data['person'] = $person;
            midcom_show_style('midcom-admin-user-personlist-item');
        }
        
        midcom_show_style('midcom-admin-user-personlist-footer');
        
        midcom_show_style('midgard_admin_asgard_footer');    
    }
}
?>

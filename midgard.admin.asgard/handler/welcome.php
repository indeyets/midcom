<?php
/**
 * @package midgard.admin.asgard
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: parameters.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Welcome interface
 *
 * @package midgard.admin.asgard
 */
class midgard_admin_asgard_handler_welcome extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function midgard_admin_asgard_handler_welcome()
    {
        $this->_component = 'midgard.admin.asgard';
        parent::midcom_baseclasses_components_handler();
    }
    
    function _on_initialize()
    {
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midgard.admin.asgard');
        $_MIDCOM->skip_page_style = true;
                
        $_MIDCOM->load_library('midcom.helper.datamanager2');
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
    }
    
    /**
     * Object editing view
     */
    function _handler_welcome($handler_id, $args, &$data)
    {
        $this->_prepare_request_data();

        $data['view_title'] = $this->_l10n->get('asgard');
        $_MIDCOM->set_pagetitle($data['view_title']);
        
        $data['asgard_toolbar'] = new midcom_helper_toolbar();
        
        midgard_admin_asgard_plugin::get_common_toolbar($data);
        return true;
    }

    /**
     * Shows the loaded object in editor.
     */
    function _show_welcome($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');    
        midcom_show_style('midgard_admin_asgard_welcome');
        midcom_show_style('midgard_admin_asgard_footer');
    }
}
?>
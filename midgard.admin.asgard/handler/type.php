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
class midgard_admin_asgard_handler_type extends midcom_baseclasses_components_handler
{
    var $type = '';

    /**
     * Simple default constructor.
     */
    function midgard_admin_asgard_handler_type()
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
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_type($handler_id, $args, &$data)
    {
        $this->type = $args[0];
        $root_types = midgard_admin_asgard_reflector_tree::get_root_classes();
        /*if (!in_array($this->type, $root_types))
        {
            return false;
        }*/

        $this->_prepare_request_data();

        $data['view_title'] = midgard_admin_asgard_plugin::get_type_label($this->type);
        $_MIDCOM->set_pagetitle($data['view_title']);

        $data['asgard_toolbar'] = new midcom_helper_toolbar();

        if ($_MIDCOM->auth->can_user_do('midgard:create', null, $this->type))
        {
            $data['asgard_toolbar']->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__mfa/asgard/object/create/{$this->type}/",
                    MIDCOM_TOOLBAR_LABEL => sprintf($_MIDCOM->i18n->get_string('create %s', 'midcom'), midgard_admin_asgard_plugin::get_type_label($this->type)),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/' . midgard_admin_asgard_reflector_tree::get_create_icon($this->type),
                )
            );
        }

        if ($_MIDCOM->auth->admin)
        {
            $qb = new midgard_query_builder($this->type);
            $qb->include_deleted();
            $qb->add_constraint('metadata.deleted', '=', true);
            $deleted = $qb->count();
            if ($deleted > 0)
            {
                $data['asgard_toolbar']->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "__mfa/asgard/trash/{$this->type}/",
                        MIDCOM_TOOLBAR_LABEL => sprintf($_MIDCOM->i18n->get_string('%s deleted items', 'midgard.admin.asgard'), $deleted),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash-full.png',
                    )
                );
            }
            else
            {
                $data['asgard_toolbar']->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "__mfa/asgard/trash/{$this->type}/",
                        MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('trash is empty', 'midgard.admin.asgard'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                    )
                );
            }
        }

        midgard_admin_asgard_plugin::get_common_toolbar($data);


        return true;
    }

    /**
     * Shows the loaded object in editor.
     */
    function _show_type($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');
        $data['current_type'] = $this->type;
        midcom_show_style('midgard_admin_asgard_middle');

        $dummy = new $this->type;
        $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($dummy);
        $component = $_MIDCOM->dbclassloader->_mgdschema_class_handler[$midcom_dba_classname];
        $help_component = '';
        if ( $component == 'midcom')
        {
            $component = 'midgard';
            $help_component = 'midgard.admin.asgard';
        }

        $help = new midcom_admin_help_help();
        $data['help'] =  $help->get_help_contents('asgard_'.$this->type, $help_component);
        $data['component'] =  $component;
        $data['type'] = $this->type;
        midcom_show_style('midgard_admin_asgard_type');
        midcom_show_style('midgard_admin_asgard_footer');
    }
}
?>
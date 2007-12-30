<?php
/**
 * @package midgard.admin.asgard
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: parameters.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Component display
 *
 * @package midgard.admin.asgard
 */
class midgard_admin_asgard_handler_components extends midcom_baseclasses_components_handler
{

    /**
     * Simple default constructor.
     */
    function midgard_admin_asgard_handler_components()
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

    function _list_components()
    {
        $this->_request_data['components'] = array();
        $this->_request_data['libraries'] = array();

        foreach($_MIDCOM->componentloader->manifests as $name => $manifest)
        {
            if (!array_key_exists('package.xml', $manifest->_raw_data))
            {
                // This component is not yet packaged, skip
                continue;
            }

            $type = 'components';
            if ($manifest->purecode)
            {
                $type = 'libraries';
            }

            $component_array = array();
            $component_array['name'] = $name;
            $component_array['title'] = $_MIDCOM->i18n->get_string($name, $name);

            if (isset($manifest->_raw_data['icon']))
            {
                $component_array['icon'] = $manifest->_raw_data['icon'];
            }
            else
            {
                $component_array['icon'] = 'stock-icons/16x16/package.png';
            }

            if (isset($manifest->_raw_data['package.xml']['description']))
            {
                $component_array['description'] = $manifest->_raw_data['package.xml']['description'];
            }
            else
            {
                $component_array['description'] = '';
            }
            $component_array['version'] = $manifest->_raw_data['version'];

            $component_array['toolbar'] = new midcom_helper_toolbar();
            $component_array['toolbar']->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__mfa/asgard/components/configuration/{$name}",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('component configuration', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
            /*$component_array['toolbar']->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'tmp',
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('localization', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/locale.png',
                )
            );*/

            $this->_request_data[$type][$name] = $component_array;
        }
    }

    /**
     * Object editing view
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        $data['view_title'] = $_MIDCOM->i18n->get_string('components', 'midgard.admin.asgard');
        $_MIDCOM->set_pagetitle($data['view_title']);

        $this->_list_components();

        $data['asgard_toolbar'] = new midcom_helper_toolbar();
        midgard_admin_asgard_plugin::get_common_toolbar($data);


        // Set the breadcrumb data
        $tmp = array();
        $tmp[] = array
        (
            MIDCOM_NAV_URL => '__mfa/asgard/',
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('midgard.admin.asgard', 'midgard.admin.asgard'),
        );
        $tmp[] = array
        (
            MIDCOM_NAV_URL => '__mfa/asgard/components/',
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('components', 'midgard.admin.asgard'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Shows the loaded object in editor.
     */
    function _show_list($handler_id, &$data)
    {
        midgard_admin_asgard_plugin::asgard_header();

        $data['list_type'] = 'components';
        midcom_show_style('midgard_admin_asgard_components_header');
        foreach ($data['components'] as $component => $component_data)
        {
            $data['component_data'] = $component_data;
            midcom_show_style('midgard_admin_asgard_components_item');
        }
        midcom_show_style('midgard_admin_asgard_components_footer');

        $data['list_type'] = 'libraries';
        midcom_show_style('midgard_admin_asgard_components_header');
        foreach ($data['libraries'] as $component => $component_data)
        {
            $data['component_data'] = $component_data;
            midcom_show_style('midgard_admin_asgard_components_item');
        }
        midcom_show_style('midgard_admin_asgard_components_footer');

        midgard_admin_asgard_plugin::asgard_footer();
    }
}
?>
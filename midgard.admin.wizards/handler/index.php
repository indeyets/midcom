<?php
/**
 * @package midgard.admin.wizards
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a URL handler class for midgard.admin.wizards
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 *
 * @package midgard.admin.wizards
 */
class midgard_admin_wizards_handler_index  extends midcom_baseclasses_components_handler
{

    /**
     * Simple default constructor.
     */
    function midgard_admin_wizards_handler_index()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler.
     */
    function _on_initialize()
    {
    }

    /**
     * The handler for the index article.
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_index($handler_id, $args, &$data)
    {
        $this->_request_data['name']  = "midgard.admin.wizards";
        $this->_update_breadcrumb_line($handler_id);
        $title = $this->_l10n->get('midgard wizards');
        $_MIDCOM->set_pagetitle($title);
        $this->_request_data['plugin_groups'] = $this->_config->get('plugin_groups');

        return true;
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_group($handler_id, $args, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        // Relocating to the first plugin of the current group
        $config = $this->_config->get('plugin_groups');
        if (isset($args[0]) && isset($config[$args[0]]))
        {
            $first = each($config[$args[0]]['plugins']);
            $plugin = $first['key'];
            $session_id = time();

            $_MIDCOM->relocate($prefix . $args[0] . "/" . $plugin . "/" . $session_id);
        }
        else
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('midcom.admin.wizards'),
                $this->_l10n->get('plugin group does not exists')
            );
            $_MIDCOM->relocate('');
        }

        return true;
    }

    /**
     * This function does the output.
     */
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('index');
    }

    function _show_group($handler_id, &$data)
    {
        // No need to show a style. The handler relocates to the first plugin
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => $this->_l10n->get('index'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>

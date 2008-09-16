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
 *
 * @see midcom_baseclasses_components_handler
 * @package midgard.admin.wizards
 */
class midgard_admin_wizards_handler_index  extends midcom_baseclasses_components_handler
{

    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * The handler for the index article.
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_index($handler_id, $args, &$data)
    {
        $this->_request_data['name']  = "midgard.admin.wizards";

        $title = $this->_l10n->get('midgard wizards');
        $_MIDCOM->set_pagetitle($title);
        $data['plugin_groups'] = $this->_config->get('plugin_groups');
        
        if (count($data['plugin_groups']) == 1)
        {
            // Relocate directly
            $plugin_group_names = array_keys($this->_request_data['plugin_groups']);
            $_MIDCOM->relocate("{$plugin_group_names[0]}/");
        }

        return true;
    }

    /**
     * This function does the output.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('index');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_group($handler_id, $args, &$data)
    {
        $config = $this->_config->get('plugin_groups');

        if (   !isset($args[0]) 
            || !isset($config[$args[0]]))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Plug-in group {$args[0]} does not exist.");
            // This will exit
        }
        
        // Relocating to the first plugin of the current group
        $first = each($config[$args[0]]['plugins']);
        $plugin = $first['key'];
        $session_id = time();

        $_MIDCOM->relocate("{$args[0]}/{$plugin}/{$session_id}");
    }
}
?>
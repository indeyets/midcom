<?php
/**
 * @package org.maemo.devcodes
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a URL handler class for org.maemo.devcodes
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 *
 * @see midcom_baseclasses_components_handler
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_handler_index  extends midcom_baseclasses_components_handler
{
    var $_mode = 'user';

    /**
     * Simple default constructor.
     */
    function org_maemo_devcodes_handler_index()
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
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        // PONDER: Is there a better way to construct prefix that we can use with DL ?
        $data['prefix'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $data['dl_prefix'] = preg_replace("#^(.*?://{$_SERVER['SERVER_NAME']})?{$_MIDGARD['self']}#", '', $data['prefix']);

        $data['title'] = 'org.maemo.devcodes';
        if ($this->_topic->can_do('org.maemo.devcodes:manage'))
        {
            $this->_mode = 'admin';
        }
        else
        {
            $this->_mode = 'user';
        }

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => $data['title'],
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['title']}");
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
        midcom_show_style('index-' . $this->_mode);
    }

}
?>
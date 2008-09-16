<?php
/**
 * @package no.odindata.quickform
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3145 2007-03-27 10:09:00Z NetBlade $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 *
 * @package no.odindata.quickform
 */
class no_odindata_quickform_handler_aftersubmits extends midcom_baseclasses_components_handler
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Populate a single global variable with the current schema database, so that the
     * configuration schema works again.
     *
     * @todo Rewrite this to use the real schema select widget, which is based on some
     *     other field which contains the URL of the schema.
     */
    function _on_initialize()
    {
        // no caching as different input requires different emails .)
        $_MIDCOM->cache->content->no_cache();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_submitok()
    {
        $this->_request_data['end_message'] = $this->_config->get('end_message');
        return true;
    }

    function _show_submitok()
    {
        midcom_show_style('show-form-finished');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_submitnotok()
    {
        $this->_request_data['end_message'] = $this->_l10n->get('error sending the message');
        return true;
    }

    function _show_submitnotok()
    {
        midcom_show_style('show-form-failed');
    }

}

?>
<?php
/**
 * @package org.openpsa.jabber
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.5 2006/02/03 15:21:21 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.jabber site interface class.
 *
 * Instant Messaging powered by JabberApplet
 */
class org_openpsa_jabber_viewer extends midcom_baseclasses_components_request
{

    /**
     * Constructor.
     */
    function org_openpsa_jabber_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);

        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();

        // Match /applet
        $this->_request_switch[] = array(
            'fixed_args' => 'applet',
            'handler' => 'applet'
        );

        // Match /summary
        $this->_request_switch[] = array(
            'fixed_args' => 'summary',
            'handler' => 'summary'
        );

        // Match /
        $this->_request_switch[] = array(
            'handler' => 'frontpage'
        );
    }

    function _handler_applet($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        // We're using a popup here
        $GLOBALS['midcom']->skip_page_style = true;
        return true;
    }

    function _show_applet($handler_id, &$data)
    {
        midcom_show_style("jabber-applet");
    }

     function _handler_summary($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        return true;
    }

    function _show_summary($handler_id, &$data)
    {
        midcom_show_style("show-summary");
    }

    function _handler_frontpage($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        return true;
    }

    function _show_frontpage($handler_id, &$data)
    {
        midcom_show_style("show-frontpage");
    }
}
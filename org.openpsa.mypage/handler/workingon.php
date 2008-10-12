<?php
/**
 * @package org.openpsa.mypage
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: workingon.php,v 1.1 2006/05/10 13:00:45 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Projects "now working on"
 *
 * @package org.openpsa.mypage
 */
class org_openpsa_projects_handler_workingon extends midcom_baseclasses_components_handler
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_set($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        if ($_SERVER['REQUEST_METHOD'] != 'POST')
        {
            $_MIDCOM->generate_error(MIDCOM_ERRFORBIDDEN, 'Only POST requests are allowed here.');
        }

        if (!array_key_exists('task', $_POST))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'No task specified, aborting.');
        }

        // Handle "not working on anything"
        if ($_POST['task'] == 'none')
        {
            $_POST['task'] = '';
        }

        // Set the "now working on" status
        $workingon = new org_openpsa_mypage_workingon();
        $stat = $workingon->set($_POST['task']);
        if (!$stat)
        {
            $_MIDCOM->uimessages->add($this->_l10n->get('org.openpsa.mypage'),  'Failed to set "working on" parameter to "' . $_POST['task'] . '", reason ' . mgd_errstr(), 'error');
        }

        if (array_key_exists('url', $_POST))
        {
            $_MIDCOM->relocate($_POST['url']);
            // This will exit
        }

        $_MIDCOM->relocate('');
        // This will exit
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_check($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        // Set the "now working on" status
        $data['workingon'] = new org_openpsa_mypage_workingon();

        $_MIDCOM->skip_page_style = true;

        $_MIDCOM->cache->content->content_type("text/xml");
        $_MIDCOM->header("Content-type: text/xml; charset=UTF-8");

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_check($handler_id, &$data)
    {
        midcom_show_style('show-workingon-xml');
    }
}
?>
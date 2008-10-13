<?php
/**
 * @package org.openpsa.mypage
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.23 2006/06/13 10:49:53 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.mypage site interface class.
 *
 * Personal summary page into OpenPSA
 *
 * @package org.openpsa.mypage
 */
class org_openpsa_mypage_viewer extends midcom_baseclasses_components_request
{
    var $_toolbars = null;

    /**
     * Constructor.
     */
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        $this->_toolbars =& midcom_helper_toolbars::get_instance();

        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();

        // Match /savefilter
        $this->_request_switch[] = array
        (
            'fixed_args' => 'savefilter',
            'handler' => 'savefilter'
        );

        // Match /userinfo
        $this->_request_switch[] = array
        (
            'fixed_args' => 'userinfo',
            'handler' => 'userinfo'
        );

        // Match /updates
        $this->_request_switch[] = array
        (
            'fixed_args' => 'updates',
            'handler' => 'updates'
        );

        // Match /
        $this->_request_switch['today'] = array
        (
            'handler' => array('org_openpsa_mypage_handler_today', 'today'),
        );

        // Match /day/<date>
        $this->_request_switch['day'] = array
        (
            'handler' => array('org_openpsa_mypage_handler_today', 'today'),
            'fixed_args' => array('day'),
            'variable_args' => 1,
        );

        // Match /weekreview/<date>
        $this->_request_switch['weekreview'] = array
        (
            'handler' => array('org_openpsa_mypage_handler_weekreview', 'review'),
            'fixed_args' => array('weekreview'),
            'variable_args' => 1,
        );

        // Match /weekreview/
        $this->_request_switch['weekreview_redirect'] = array
        (
            'handler' => array('org_openpsa_mypage_handler_weekreview', 'redirect'),
            'fixed_args' => array('weekreview'),
        );

        // Match /workingon/set
        $this->_request_switch['workingon_set'] = array
        (
            'handler' => array('org_openpsa_projects_handler_workingon', 'set'),
            'fixed_args' => array('workingon', 'set' ),
        );
        // Match /workingon/check
        $this->_request_switch['workingon_check'] = array
        (
            'handler' => array('org_openpsa_projects_handler_workingon', 'check'),
            'fixed_args' => array('workingon', 'check'),
        );
        
        // Match /config/
        $this->_request_switch['config'] = array
        (
            'handler' => array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/org/openpsa/mypage/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => array('config'),
        );
    }

    function _on_handle($handler, $args)
    {
        $_MIDCOM->auth->require_valid_user();

        $_MIDCOM->load_library('org.openpsa.contactwidget');

        // This component uses Ajax, include the handler javascripts
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/ajaxutils.js");

        return parent::_on_handle($handler, $args);
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     */
    function _handler_savefilter($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        if (array_key_exists('org_openpsa_workgroup_filter', $_POST))
        {
            $session = new midcom_service_session('org.openpsa.core');
            $session->set('org_openpsa_core_workgroup_filter', $_POST['org_openpsa_workgroup_filter']);
            // TODO: Check that session actually was saved
            $ajax=new org_openpsa_helpers_ajax();
            $ajax->simpleReply(true, 'Session saved');
        }
        else
        {
            $ajax=new org_openpsa_helpers_ajax();
            $ajax->simpleReply(false, 'No filter given');
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_userinfo($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        if ($_MIDCOM->auth->user)
        {
            $this->_request_data['virtual_groups']['all'] = $this->_request_data['l10n']->get('all groups');
            $this->_request_data['virtual_groups'] += org_openpsa_helpers_workgroups();
        }

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_userinfo($handler_id, &$data)
    {
        if ($_MIDCOM->auth->user)
        {
            midcom_show_style("show-userinfo");
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_updates($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        // Instantiate indexer
        $indexer =& $_MIDCOM->get_service('indexer');

        $start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $query = '__TOPIC_URL:"'.$_MIDCOM->get_host_name().'*"';
        $filter = new midcom_services_indexer_filter_date('__EDITED', $start, 0);
        $this->_request_data['today'] = $indexer->query($query, $filter);
        $start = mktime(0, 0, 0, date('m'), date('d')-1, date('Y'));
        $end = mktime(23, 59, 59, date('m'), date('d')-1, date('Y'));
        $query = '__TOPIC_URL:"'.$_MIDCOM->get_host_name().'*"';
        $filter = new midcom_services_indexer_filter_date('__EDITED', $start, $end);
        $this->_request_data['yesterday'] = $indexer->query($query, $filter);
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_updates($handler_id, &$data)
    {
        midcom_show_style("show-updates");
    }

}
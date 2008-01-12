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
    function org_openpsa_mypage_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
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
        $this->_request_switch[] = array(
            'fixed_args' => 'savefilter',
            'handler' => 'savefilter'
        );

        // Match /userinfo
        $this->_request_switch[] = array(
            'fixed_args' => 'userinfo',
            'handler' => 'userinfo'
        );

        // Match /updates
        $this->_request_switch[] = array(
            'fixed_args' => 'updates',
            'handler' => 'updates'
        );

        // Match /
        $this->_request_switch['today'] = Array
        (
            'handler' => Array('org_openpsa_mypage_handler_today', 'today'),
        );

        // Match /day/<date>
        $this->_request_switch['day'] = Array
        (
            'handler' => Array('org_openpsa_mypage_handler_today', 'today'),
            'fixed_args' => Array('day'),
            'variable_args' => 1,
        );

        // Match /weekreview/<date>
        $this->_request_switch['weekreview'] = Array
        (
            'handler' => Array('org_openpsa_mypage_handler_weekreview', 'review'),
            'fixed_args' => Array('weekreview'),
            'variable_args' => 1,
        );

        // Match /weekreview/
        $this->_request_switch['weekreview_redirect'] = Array
        (
            'handler' => Array('org_openpsa_mypage_handler_weekreview', 'redirect'),
            'fixed_args' => Array('weekreview'),
        );

        // Match /config/
        $this->_request_switch['config'] = Array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/org/openpsa/mypage/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );
    }

    function _on_handle($handler, $args)
    {
        $_MIDCOM->auth->require_valid_user();

        // This component uses Ajax, include the handler javascripts
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/ajaxutils.js");

        $this->_request_data['schemadb_default'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_default'));

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

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_frontpage($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_request_data['sidebar_items'] = array();
        $this->_request_data['main_items'] = array();
        $this->_request_data['wide_items'] = array();

        $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get('my summary'));

        // List toplevel nodes
        $nap = new midcom_helper_nav();
        $nodes = $nap->list_nodes($nap->get_root_node());
        foreach ($nodes as $node_id)
        {
            //Add toolbar buttons and required headers as warranted by nodes
            $node = $nap->get_node($node_id);
            switch ($node[MIDCOM_NAV_COMPONENT])
            {
                // Main bar
                /*case 'de.linkm.newsticker':
                    // List 4 latest news in left sidebar
                    $this->_request_data['leftbar_items'][$node[MIDCOM_NAV_RELATIVEURL].'latest/4'] = $node;
                    break;*/
                case 'net.nemein.wiki':
                    // List 4 latest news in left sidebar
                    $this->_request_data['leftbar_items'][$node[MIDCOM_NAV_RELATIVEURL].'latest/'] = $node;
                    break;
                case 'net.nemein.discussion':
                    // List 4 latest comments in left sidebar
                    $this->_request_data['leftbar_items'][$node[MIDCOM_NAV_RELATIVEURL].'latest/all/4'] = $node;

                    // Dynamically loaded hour reporting requires this too
                    $_MIDCOM->add_link_head(array(
                        'rel' => 'stylesheet',
                        'type' => 'text/css',
                        'href' => MIDCOM_STATIC_URL."/net.nemein.discussion/discussion.css",
                    ));
                    break;
                // Sidebar
                case 'org.openpsa.contacts':
                    $this->_toolbars->top->add_item(
                        Array(
                            MIDCOM_TOOLBAR_URL => "{$node[MIDCOM_NAV_FULLURL]}person/new/",
                            MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('create person'),
                            MIDCOM_TOOLBAR_HELPTEXT => null,
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_person.png',
                            MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_contacts_person'),
                        )
                    );

                    // Show Buddy list in sidebar
                    if ($this->_config->get('show_buddylist'))
                    {
                        $this->_request_data['sidebar_items'][$node[MIDCOM_NAV_RELATIVEURL].'buddylist'] = $node;
                    }
                    break;
                case 'org.openpsa.calendar':
                    $this->_toolbars->top->add_item(
                        Array(
                            MIDCOM_TOOLBAR_URL => "#",
                            MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('create event'),
                            MIDCOM_TOOLBAR_HELPTEXT => null,
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new-event.png',
                            MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:create', $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']),
                            //MIDCOM_TOOLBAR_ENABLED => true,
                            MIDCOM_TOOLBAR_OPTIONS  => Array(
                                'rel' => 'directlink',
                                'onclick' => org_openpsa_calendar_interface::calendar_newevent_js($node),
                            ),
                        )
                    );
                     $this->_request_data['sidebar_items'][$node[MIDCOM_NAV_RELATIVEURL].'agenda/day/'.date('Y-m-d')] = $node;
                    break;
                case 'org.openpsa.projects':
                    // List User's tasks in main area
                    $this->_request_data['wide_items'][$node[MIDCOM_NAV_RELATIVEURL].'task/list'] = $node;
                    $this->_toolbars->top->add_item(
                        Array(
                            MIDCOM_TOOLBAR_URL => $node[MIDCOM_NAV_FULLURL].'task/new/',
                            MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("create task"),
                            MIDCOM_TOOLBAR_HELPTEXT => null,
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new_task.png',
                            MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_projects_task'),
                        )
                    );
                    // FIXME: Could be great to make d_l do this
                    $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/ajax_tableform.js");
                    $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.projects/hours_widget.js");
                    $_MIDCOM->add_link_head(array(
                        'rel' => 'stylesheet',
                        'type' => 'text/css',
                        'href' => MIDCOM_STATIC_URL."/org.openpsa.projects/hours_widget.css",
                    ));
                    $_MIDCOM->add_link_head(array(
                        'rel' => 'stylesheet',
                        'type' => 'text/css',
                        'href' => MIDCOM_STATIC_URL."/midcom.helper.datamanager/datamanager.css",
                    ));

                    // JSCalendar
                    $prefix = MIDCOM_STATIC_URL . '/midcom.helper.datamanager/jscript-calendar';
                    $_MIDCOM->add_jsfile("{$prefix}/calendar.js");

                    // Select correct locale
                    $i18n =& $_MIDCOM->get_service("i18n");
                    $language = $i18n->get_current_language();
                    switch ($language)
                    {
                        // TODO: Add more languages here when corresponding locale files exist
                        case "fi":
                            $_MIDCOM->add_jsfile("{$prefix}/calendar-fi.js");
                            break;
                        case "en":
                        default:
                            $_MIDCOM->add_jsfile("{$prefix}/calendar-en.js");
                            break;
                    }

                    $_MIDCOM->add_jsfile("{$prefix}/calendar-setup.js");
                    break;
            }
        }

        $root_node = $nap->get_node($nap->get_root_node());
        switch ($root_node[MIDCOM_NAV_COMPONENT])
        {
            case 'org.openpsa.mypage':
                if ($this->_config->get('show_buddylist'))
                {
                    // Show last modified items in left sidebar
                    $this->_request_data['leftbar_items'][$root_node[MIDCOM_NAV_RELATIVEURL].'updates'] = $root_node;
                }
                else
                {
                    // Show last modified items in right sidebar
                    $this->_request_data['sidebar_items'][$root_node[MIDCOM_NAV_RELATIVEURL].'updates'] = $root_node;
                }
                break;
        }

        if (   $GLOBALS['org_openpsa_core_workgroup_filter'] != 'all'
            && strstr($GLOBALS['org_openpsa_core_workgroup_filter'], 'vgroup:org.openpsa.projects'))
        {
            // Some project is selected, get the news and forum topics
            if (!class_exists('org_openpsa_projects_project'))
            {
                $_MIDCOM->componentloader->load('org.openpsa.projects');
            }

            $project = new org_openpsa_projects_project(str_replace('vgroup:org.openpsa.projects-', '', $GLOBALS['org_openpsa_core_workgroup_filter']));
            if ($project)
            {
                if ($project->newsTopic)
                {
                    $news_node = $nap->get_node($project->newsTopic);
                    if ($news_node)
                    {
                        $this->_request_data['leftbar_items'][$news_node[MIDCOM_NAV_RELATIVEURL].'latest/4'] = $news_node;
                    }
                }
                if ($project->forumTopic)
                {
                    $forum_node = $nap->get_node($project->forumTopic);
                    if ($forum_node)
                    {
                        $this->_request_data['leftbar_items'][$forum_node[MIDCOM_NAV_RELATIVEURL].'latest/4'] = $forum_node;
                    }
                }
            }
        }

        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_toolbars->top->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => 'config.html',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_frontpage($handler_id, &$data)
    {
        midcom_show_style("show-frontpage");
    }
}
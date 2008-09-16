<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: action.php,v 1.1 2006/05/10 13:00:45 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Project action handler
 *
 * @package org.openpsa.projects
 */
class org_openpsa_projects_handler_project_action extends midcom_baseclasses_components_handler
{
    var $_datamanagers;

    function __construct()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
        $this->_datamanagers = array
        (
            'project' => new midcom_helper_datamanager($this->_config->get('schemadb_project'))
        );
    }

    function _load_project($identifier)
    {
        $project = new org_openpsa_projects_project($identifier);

        if (!is_object($project))
        {
            return false;
        }

        //Fill the customer field to DM
        debug_add("schema before \n===\n" . sprint_r($this->_datamanagers['project']->_layoutdb['default']) . "===\n");
        org_openpsa_helpers_schema_modifier($this->_datamanagers['project'], 'customer', 'widget', 'select', 'default', false);
        org_openpsa_helpers_schema_modifier($this->_datamanagers['project'], 'customer', 'widget_select_choices', org_openpsa_helpers_task_groups($project), 'default', false);
        debug_add("schema after \n===\n" . sprint_r($this->_datamanagers['project']->_layoutdb['default']) . "===\n");

        // Load the project to datamanager
        if (!$this->_datamanagers['project']->init($project))
        {
            return false;
        }
        return $project;
    }

    /**
     * FIXME: This should be properly reorganized to its own handlers
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_action($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $this->_request_data['project'] = $this->_load_project($args[0]);
        if (!$this->_request_data['project'])
        {
            return false;
        }

        // Check if the action is a valid one
        $this->_request_data['project_action'] = $args[1];
        switch ($args[1])
        {
            case 'subscribe':
                // If person is already a member just redirect
                if (   array_key_exists($_MIDGARD['user'], $this->_request_data['project']->resources)
                    || array_key_exists($_MIDGARD['user'], $this->_request_data['project']->contacts))
                {
                    debug_pop();
                    $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                        . "project/{$this->_request_data['project']->guid}/");
                    // This will exit
                }

                if (!$_MIDCOM->auth->can_do('midgard:create', $this->_request_data['project']))
                {
                    // We usually want to skip ACL here and allow anybody to subscribe
                    $_MIDCOM->auth->request_sudo();
                }
                $_MIDCOM->auth->require_do('midgard:create', $this->_request_data['project']);

                // FIXME: Move this to a method in the project class
                $subscriber = new org_openpsa_projects_task_resource();
                $subscriber->person = $_MIDGARD['user'];
                $subscriber->task = $this->_request_data['project']->id;
                $subscriber->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_PROJECTCONTACT;

                if ($subscriber->create())
                {
                    debug_pop();
                    $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                        . "project/{$this->_request_data['project']->guid}/");
                    // This will exit
                }
                else
                {
                    debug_pop();
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to subscribe, reason ".mgd_errstr());
                    // This will exit
                }

            case 'unsubscribe':
                // If person is not a subscriber just redirect
                if (!array_key_exists($_MIDGARD['user'], $this->_request_data['project']->contacts))
                {
                    debug_pop();
                    $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                        . "project/{$this->_request_data['project']->guid}/");
                    // This will exit
                }

                $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_task_resource');
                $qb->add_constraint('person', '=', $_MIDGARD['user']);
                $qb->add_constraint('task', '=', $this->_request_data['project']->id);
                $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECTCONTACT);
                $ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
                if (   is_array($ret)
                    && count($ret) > 0)
                {
                    foreach ($ret as $subscriber)
                    {
                        $subscriber->delete();
                    }
                }
                debug_pop();
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . "project/{$this->_request_data['project']->guid}/");
                // This will exit

            case 'create_news':
                $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['project']);
                $_MIDCOM->auth->require_do('midgard:create', $this->_request_data['project_topic']);

                if ($this->_request_data['project']->newsTopic)
                {
                    debug_pop();
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The news topic already exists");
                    // This will exit
                }

                if (!$this->_request_data['config']->get('enable_project_news'))
                {
                    return false;
                }

                // Create the news topic
                // FIXME: Move this to a method in the project class
                $news_topic = new midcom_baseclasses_database_topic();
                $news_topic->up = $this->_request_data['project_topic']->id;
                $news_topic->extra = sprintf($this->_request_data['l10n']->get("%s news area"), $this->_request_data['project']->title);
                $news_topic->component = 'net.nehmer.blog';
                $news_topic->name = midcom_generate_urlname_from_string($news_topic->extra);
                $news_topic->create();

                if ($news_topic->id)
                {
                    // Set the topic to use correct component
                    $news_topic = new midcom_baseclasses_database_topic($news_topic->id);

                    // Fix the ACLs for the topic
                    $sync = new org_openpsa_core_acl_synchronizer();
                    $sync->write_acls($news_topic, $this->_request_data['project']->orgOpenpsaOwnerWg, $this->_request_data['project']->orgOpenpsaAccesstype);

                    // Add the news topic to the project
                    $this->_request_data['project']->newsTopic = $news_topic->id;
                    $this->_request_data['project']->update();

                    debug_pop();
                    $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                        . "project/" . $this->_request_data["project"]->guid);
                    // This will exit
                }
                else
                {
                    debug_pop();
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create project news topic, reason ".$news_topic->errstr);
                    // This will exit
                }

            case 'create_forum':
                $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['project']);
                $_MIDCOM->auth->require_do('midgard:create', $this->_request_data['project_topic']);

                if (!$this->_request_data['config']->get('enable_project_forum'))
                {
                    return false;
                }

                if ($this->_request_data['project']->forumTopic)
                {
                    debug_pop();
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The forum topic already exists");
                    // This will exit
                }

                // Create the news topic
                // FIXME: Move this to a method in the project class
                $forum_topic = new midcom_baseclasses_database_topic();
                $forum_topic->up = $this->_request_data['project_topic']->id;
                $forum_topic->extra = sprintf($this->_request_data['l10n']->get("%s discussion"), $this->_request_data['project']->title);
                $forum_topic->component = 'net.nemein.discussion';
                $forum_topic->name = midcom_generate_urlname_from_string($forum_topic->extra);
                $forum_topic->create();

                if ($forum_topic->id)
                {
                    // Set the topic to use correct component
                    $forum_topic = new midcom_baseclasses_database_topic($forum_topic->id);

                    // Fix the ACLs for the topic
                    $sync = new org_openpsa_core_acl_synchronizer();
                    $sync->write_acls($forum_topic, $this->_request_data['project']->orgOpenpsaOwnerWg, $this->_request_data['project']->orgOpenpsaAccesstype);

                    // Add the news topic to the project
                    $this->_request_data['project']->forumTopic = $forum_topic->id;
                    $this->_request_data['project']->update();

                    debug_pop();
                    $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                        . "project/" . $this->_request_data["project"]->guid);
                    // This will exit
                }
                else
                {
                    debug_pop();
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create project forum topic, reason ".$forum_topic->errstr);
                    // This will exit
                }

            default:
                return false;
        }
    }

    /**
     * The edit handler
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $this->_request_data['project'] = $this->_load_project($args[0]);
        if (!$this->_request_data['project'])
        {
            return false;
        }
        $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['project']);

        switch ($this->_datamanagers['project']->process_form()) {
            case MIDCOM_DATAMGR_EDITING:
                $this->_view = "edit";

                $_MIDCOM->set_pagetitle(sprintf($this->_request_data['l10n']->get('edit project %s'), $this->_request_data['project']->title));

                // Add toolbar items
                org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);

                return true;

            case MIDCOM_DATAMGR_SAVED:
            case MIDCOM_DATAMGR_CANCELLED:
                $this->_view = "default";
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . "project/" . $this->_request_data["project"]->guid . "/");
                // This will exit()

            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
        }
        return true;
    }
    

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_action($handler_id, &$data)
    {
    }
    
    /**
     * The edit show phase
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {
        $this->_request_data['project_dm'] =& $this->_datamanagers['project'];
        midcom_show_style("show-project-edit");
    }
    
}
?>
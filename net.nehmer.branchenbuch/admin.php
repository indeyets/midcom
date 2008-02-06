<?php
/**
 * @package net.nehmer.branchenbuch
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Branchenbuch (Yellow Pages) AIS interface class
 *
 * @package net.nehmer.branchenbuch
 */

class net_nehmer_branchenbuch_admin extends midcom_baseclasses_components_request_admin
{
    function net_nehmer_branchenbuch_admin($topic, $config)
    {
        parent::midcom_baseclasses_components_request_admin($topic, $config);
    }

    /**
     * @access private
     */
    function _on_initialize()
    {
        // Configuration
        $this->_request_switch[] = Array
        (
            'handler' => 'config_dm',
            'fixed_args' => Array('config'),
            'schemadb' => 'file:/net/nehmer/branchenbuch/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => false
        );

        // Welcome page, sets up the component if required.
        $this->_request_switch[] = Array
        (
            'handler' => 'welcome',
        );

        // Category Management
        $this->_request_switch[] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_admin_manage', 'list'),
            'fixed_args' => Array('manage', 'list'),
        );
        $this->_request_switch[] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_admin_manage', 'edit'),
            'fixed_args' => Array('manage', 'edit'),
            'variable_args' => 1,
        );
        $this->_request_switch[] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_admin_manage', 'create'),
            'fixed_args' => Array('manage', 'create'),
            'variable_args' => 1,
        );
        $this->_request_switch[] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_admin_manage', 'delete'),
            'fixed_args' => Array('manage', 'delete'),
            'variable_args' => 1,
        );
    }

    /**
     * The welcome handler sets various configuration options if required, so that the
     * component startup is valid from that time on. It will bail with an Error if
     * no admin level user is logged in if anything needs to be done.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_welcome($handler_id, $args, &$data)
    {
        $data['processing_msg'] = '';

        $this->_check_account_topic();
        $this->_check_root_branchen();

        if ($data['processing_msg'] == '')
        {
            $data['processing_msg'] = $this->_l10n->get('init: component fully operational');
        }

        return true;
    }

    /**
     * This function checks and, if necessary, auto-populates the account_topic setting.
     */
    function _check_account_topic()
    {
        if ($this->_config->get('account_topic') === null)
        {
            $_MIDCOM->auth->require_admin_user();

            $result = midcom_helper_find_node_by_component('net.nehmer.account');
            if (! $result)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'There must be an instance of net.nehmer.account on the website for this component to work.');
                // This will exit.
            }

            $this->_topic->set_parameter('net.nehmer.branchenbuch', 'account_topic', $result[MIDCOM_NAV_GUID]);
            $this->_request_data['processing_msg'] .= "Set account_topic to link to {$result[MIDCOM_NAV_GUID]} (ID {$result[MIDCOM_NAV_ID]}, {$result[MIDCOM_NAV_NAME]})\n";
        }
        else
        {
            $test = new midcom_db_topic($this->_config->get('account_topic'));
            if (! $test)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'The net.nehmer.account topic specified in the configuration could not be loaded: Topic does not exist.');
                // This will exit.
            }
            if ($test->component != 'net.nehmer.account')
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'The net.nehmer.account topic specified in the configuration could not be loaded: Invalid topic (wrong component).');
                // This will exit.
            }
        }
    }

    /**
     * This function loads the account management interface class to determine the account
     * types and check whether we have all required root categories.
     */
    function _check_root_branchen()
    {
        $_MIDCOM->componentloader->load('net.nehmer.account');
        $interface =& $_MIDCOM->componentloader->get_interface_class('net.nehmer.account');
        $remote = $interface->create_remote_controller($this->_config->get('account_topic'));
        foreach ($remote->list_account_types() as $name => $description)
        {
            $group =& $_MIDCOM->auth->get_midgard_group_by_name($name);
            if (! $group)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Could not validate the account type {$name}, it has no corresponding midgard_group record.");
                // This will exit.
            }

            $qb = net_nehmer_branchenbuch_branche::new_query_builder();
            $qb->add_constraint('parent', '=', '');
            $qb->add_constraint('name', '=', $description);
            $result = $qb->execute();
            if (! $result)
            {
                $_MIDCOM->auth->require_admin_user();
                $branche = new net_nehmer_branchenbuch_branche();
                $branche->parent = '';
                $branche->name = $description;
                $branche->type = $name;
                $branche->itemcount = 0;
                if (! $branche->create())
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create root category for account type {$name}");
                    // This will exit.
                }

                $branche->set_privilege('midgard:create', $group);

                $this->_request_data['processing_msg'] .= "Auto-created root category {$description} from account type {$name}\n";
            }
        }
    }

    /**
     * Simple show welcome handler, shows the current processing message.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_welcome($handler_id, &$data)
    {
        echo '<h2>' . $this->_l10n->get('net.nehmer.branchenbuch') . '</h2>';
        echo '<p>' . nl2br(trim($data['processing_msg'])) . '</p>';
    }

    /**
     * General request initialization, which populates the topic toolbar.
     */
    function _on_handle($handler_id, $args)
    {
        $this->_prepare_topic_toolbar();
        return true;
    }

    /**
     * This function adds all of the standard items (configuration and create links)
     * to the topic toolbar.
     *
     * @access private
     */
    function _prepare_topic_toolbar()
    {
        $this->_topic_toolbar->add_item(Array
        (
            MIDCOM_TOOLBAR_URL => 'manage/list.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('category management'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true,
        ), 0);
        $this->_topic_toolbar->add_item(Array
        (
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            (
                   ! $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
                || ! $_MIDCOM->auth->can_do('midcom:component_config', $this->_topic)
            )
        ));
    }


}

/**
 * Helper function required for the config schemas. Detects all other BB topics
 * which are suitable for use with the index_to field.
 */
function net_nehmer_branchenbuch_index_to_topic_list()
{
    $result = Array
    (
        '' => $_MIDCOM->i18n->get_string('index_to current topic')
    );

    // We use a Midgard QB to query the parameters, they are currently not covered
    // by the DBA layer in terms of arbitrary queries.
    $query = new midgard_query_builder('midgard_parameter');
    $query->add_constraint('tablename', '=', 'topic');
    $query->add_constraint('domain', '=', 'midcom');
    $query->add_constraint('name', '=', 'component');
    $query->add_constraint('value', '=', 'net.nehmer.branchenbuch');
    $query_result = @$query->execute();

    if ($query_result)
    {
        $root_topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);
        $separator = ' > ';

        foreach ($query_result as $parameter)
        {
            // traverse upwards until we find the site's root topic in the
            // up field. If we arrive at another root topic (up=0) instead,
            // we bail, as this means the topic is from another site.
            // We do some sanity and ACL checks before.

            $topic = new midcom_db_topic($parameter->oid);
            $title = null;
            $guid = $topic->guid;

            do
            {
                if ($title)
                {
                    $title = "{$topic->extra}{$separator}{$title}";
                }
                else
                {
                    $title = $topic->extra;
                }
                if ($topic->up == $root_topic->id)
                {
                    // this topic is valid, we process it and break out of the loop.
                    $result[$guid] = "{$root_topic->extra}{$separator}{$title}";
                    break;
                }

                $topic = $topic->get_parent();
            }
            while ($topic);
        }
    }

    return $result;
}

?>
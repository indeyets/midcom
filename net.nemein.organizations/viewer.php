<?php
/**
 * @package net.nemein.organizations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: viewer.php 4838 2006-12-28 16:18:40Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Group viewer Site interface class.
 *
 * @package net.nemein.organizations
 */
class net_nemein_organizations_viewer extends midcom_baseclasses_components_request
{
    function net_nemein_organizations_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    /**
     * Initializes the request switch
     */
    function _on_initialize()
    {
        $this->_request_switch['view-index'] = Array
        (
            'handler' => Array('net_nemein_organizations_handler_view', 'index'),
        );

        if ($this->_config->get('enable_alphabetical'))
        {
            $this->_request_switch['view-index-alpha'] = Array
            (
                'handler' => Array('net_nemein_organizations_handler_view', 'index'),
                'fixed_args' => 'alpha',
                'variable_args' => 1,
            );
        }


        // The view handler checks against GUIDs or usernames. The index handler
        // takes care to avoid users named "vcard.vcf" and "foaf.rdf".
        $this->_request_switch['view-group'] = Array
        (
            'handler' => Array('net_nemein_organizations_handler_view', 'group'),
            'variable_args' => 1,
        );

        /*
         * not yet implemented
         *
        if ($this->_config->get('enable_foaf'))
        {
            $this->_request_switch['foaf-all'] = Array
            (
                'handler' => Array('net_nemein_organizations_handler_foaf', 'all'),
                'fixed_args' => Array('foaf.rdf'),
            );
            $this->_request_switch['foaf-group'] = Array
            (
                'handler' => Array('net_nemein_organizations_handler_foaf', 'group'),
                'fixed_args' => Array('foaf'),
                'variable_args' => 1,
            );
        }

        if ($this->_config->get('enable_vcard'))
        {
            $this->_request_switch['vcard-all'] = Array
            (
                'handler' => Array('net_nemein_organizations_handler_vcard', 'all'),
                'fixed_args' => Array('vcard.vcf'),
            );
            $this->_request_switch['vcard-group'] = Array
            (
                'handler' => Array('net_nemein_organizations_handler_vcard', 'group'),
                'fixed_args' => Array('vcard'),
                'variable_args' => 1,
            );
        }
         */

        // Administrative stuff
        $this->_request_switch['admin-edit'] = Array
        (
            'handler' => Array('net_nemein_organizations_handler_admin', 'edit'),
            'fixed_args' => Array('admin', 'edit'),
            'variable_args' => 1,
        );
        $this->_request_switch['admin-delete'] = Array
        (
            'handler' => Array('net_nemein_organizations_handler_admin', 'delete'),
            'fixed_args' => Array('admin', 'delete'),
            'variable_args' => 1,
        );
        $this->_request_switch['admin-create'] = Array
        (
            'handler' => Array('net_nemein_organizations_handler_admin', 'create'),
            'fixed_args' => Array('admin', 'create'),
        );

        $this->_request_switch['search'] = Array
        (
            'handler' => Array('net_nemein_organizations_handler_search', 'search'),
            'fixed_args' => Array('search'),
        );

        // Match /config/
        $this->_request_switch['config'] = Array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/net/nemein/organizations/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $this->_populate_node_toolbar();
        return true;
    }

    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * Currently, creation is only allowed for administrator accounts. In the future,
     * create on both groups in general and group members below the selected group
     * should be appropriate. (Needs rethinking.)
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        if ($_MIDCOM->auth->admin)
        {
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "admin/create.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create group'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_people.png',
            ));
        }

        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'config.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            ));
        }
    }

    /**
     * Simple helper, gives the base URL for a group (either username.html or
     * guid.html, depending on the username).
     *
     * All 1 argument handlers are filtered here.
     *
     * @param org_openpsa_contacts_group The group to query.
     * @return string The URL to use.
     */
    function get_url($group)
    {
        if (   $group->name
            && $group->name != 'vcard.vcf'
            && $group->name != 'foaf.rdf')
        {
            return "{$group->name}.html";
        }
        else
        {
            return "{$group->guid}.html";
        }
    }

    /**
     * Indexes a group.
     *
     * This function is usually called statically from various handlers.
     *
     * @param midcom_helper_datamanager2_datamanager &$dm The Datamanager encapsulating the group.
     * @param midcom_services_indexer &$indexer The indexer instance to use.
     * @param midcom_db_topic The topic which we are bound to. If this is not an object, the code
     *     tries to load a new topic instance from the database identified by this parameter.
     */
    function index(&$dm, &$indexer, $topic)
    {
        if (!is_object($topic))
        {
            $tmp = new midcom_db_topic($topic);
            if (! $tmp)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to load the topic referenced by {$topic} for indexing, this is fatal.");
                // This will exit.
            }
            $topic = $tmp;
        }

        // Don't index directly, that would loose a reference due to limitations
        // of the index() method. Needs fixes there.

        $nav = new midcom_helper_nav();
        $node = $nav->get_node($topic->id);

        $document = $indexer->new_document($dm);
        $document->topic_guid = $topic->guid;
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->read_metadata_from_object($dm->storage->object);
        $document->component = $topic->component;
        $indexer->index($document);
    }

}
?>

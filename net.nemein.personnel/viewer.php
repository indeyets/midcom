<?php
/**
 * @package net.nemein.personnel
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Person viewer Site interface class.
 *
 * @package net.nemein.personnel
 */
class net_nemein_personnel_viewer extends midcom_baseclasses_components_request
{
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
    }

    /**
     * Initializes the request switch
     */
    function _on_initialize()
    {

        // Match /config/
        $this->_request_switch['config'] = array
        (
            'handler' => array ('midcom_core_handler_configdm2', 'config'),
            'fixed_args' => array ('config'),
        );

        // Show list of the persons
        $this->_request_switch['view-index'] = array
        (
            'handler' => array('net_nemein_personnel_handler_view', 'index'),
        );

        // Show alphabetical order
        if ($this->_config->get('enable_alphabetical'))
        {
            $this->_request_switch['view-index-alpha'] = array
            (
                'handler' => array('net_nemein_personnel_handler_view', 'index'),
                'fixed_args' => array ('alpha'),
                'variable_args' => 1,
            );
        }

        // Show a subgroup list
        // Match /group/<group guid>/
        $this->_request_switch['subgroup-list'] = array
        (
            'handler' => array ('net_nemein_personnel_handler_view', 'group'),
            'fixed_args' => array ('group'),
            'variable_args' => 1,
        );

        // View person in a group
        // Match /group/<group guid>/<person identificator>
        $this->_request_switch['view-grouped-person'] = array
        (
            'handler' => array('net_nemein_personnel_handler_view', 'person'),
            'fixed_args' => array ('group'),
            'variable_args' => 2,
        );

        // Create a user account
        // Match /account/<person identificator>
        $this->_request_switch['account'] = array
        (
            'handler' => array('net_nemein_personnel_handler_account', 'account'),
            'fixed_args' => array('account'),
            'variable_args' => 1,
        );

        // Generate random passwords
        // Match /passwords
        $this->_request_switch['passwords'] = array
        (
            'handler' => array('net_nemein_personnel_handler_account', 'passwords'),
            'fixed_args' => array('passwords'),
        );

        // Show a person according to the username or GUID
        // Match /<person identificator>
        $this->_request_switch['view-person'] = array
        (
            'handler' => array('net_nemein_personnel_handler_view', 'person'),
            'variable_args' => 1,
        );

        //

        /*
         * not yet implemented
         *
        if ($this->_config->get('enable_foaf'))
        {
            $this->_request_switch['foaf-all'] = array
            (
                'handler' => array('net_nemein_personnel_handler_foaf', 'all'),
                'fixed_args' => array('foaf.rdf'),
            );
            $this->_request_switch['foaf-person'] = array
            (
                'handler' => array('net_nemein_personnel_handler_foaf', 'person'),
                'fixed_args' => array('foaf'),
                'variable_args' => 1,
            );
        }

        if ($this->_config->get('enable_vcard'))
        {
            $this->_request_switch['vcard-all'] = array
            (
                'handler' => array('net_nemein_personnel_handler_vcard', 'all'),
                'fixed_args' => array('vcard.vcf'),
            );
            $this->_request_switch['vcard-person'] = array
            (
                'handler' => array('net_nemein_personnel_handler_vcard', 'person'),
                'fixed_args' => array('vcard'),
                'variable_args' => 1,
            );
        }
         */

        /* CSV export */
        $this->_request_switch['csv-export-redirect'] = Array
        (
            'handler' => Array('net_nemein_personnel_handler_csv', 'export'),
            'fixed_args' => Array('csv', 'export'),
            'variable_args' => 0,
        );
        $this->_request_switch['csv-export'] = Array
        (
            'handler' => Array('net_nemein_personnel_handler_csv', 'export'),
            'fixed_args' => Array('csv', 'export'),
            'variable_args' => 1,
        );
        /* CSV import */
        $this->_request_switch['csv-import'] = Array
        (
            'handler' => Array('net_nemein_personnel_handler_csv', 'import'),
            'fixed_args' => Array('csv', 'import'),
        );

        // Administrative stuff
        $this->_request_switch['admin-edit-group'] = array
        (
            'handler' => array('net_nemein_personnel_handler_admin', 'editgroup'),
            'fixed_args' => array('admin', 'edit', 'group'),
            'variable_args' => 1,
        );
        $this->_request_switch['admin-edit'] = array
        (
            'handler' => array('net_nemein_personnel_handler_admin', 'edit'),
            'fixed_args' => array('admin', 'edit'),
            'variable_args' => 1,
        );
        $this->_request_switch['admin-delete'] = array
        (
            'handler' => array('net_nemein_personnel_handler_admin', 'delete'),
            'fixed_args' => array('admin', 'delete'),
            'variable_args' => 1,
        );
        $this->_request_switch['admin-create'] = array
        (
            'handler' => array('net_nemein_personnel_handler_admin', 'create'),
            'fixed_args' => array('admin', 'create'),
        );

        $this->_request_switch['search'] = array
        (
            'handler' => array('net_nemein_personnel_handler_search', 'search'),
            'fixed_args' => array('search'),
        );


    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $this->_populate_node_toolbar();

        if ($handler === 'config')
        {
            $this->_get_members();
            $this->_get_schemadbs();
        }

        return true;
    }

    /**
     * Get a list of group members for the configuration page
     *
     * @access private
     */
    function _get_members()
    {
        if (!$this->_config->get('group'))
        {
            $GLOBALS['net_nemein_personnel_members'] = array ();
            return;
        }

        $qb = midcom_db_member::new_query_builder();
        if (version_compare(mgd_version(), '1.8.0alpha1', '>'))
        {
            $qb->add_constraint('gid.guid', '=', $this->_config->get('group'));
            $qb->add_order('uid.lastname');
            $qb->add_order('uid.firstname');
        }
        else
        {
            $group = new midcom_db_group($this->_config->get('group'));
            $qb->add_constraint('gid', '=', $group->id);
        }

        $members = array ();

        foreach ($qb->execute_unchecked() as $membership)
        {
            $person = new midcom_db_person($membership->uid);
            $members[$person->guid] = $person->rname;
        }

        asort($members);

        $GLOBALS['net_nemein_personnel_members'] = $members;
    }

    function _get_schemadbs()
    {
        $GLOBALS['net_nemein_personnel_schemadbs'] = array_merge
        (
            Array
            (
                '' => $this->_l10n->get('default setting')
            ),
            $this->_config->get('schemadbs')
        );
    }



    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * Currently, creation is only allowed for administrator accounts. In the future,
     * create on both persons in general and group members below the selected group
     * should be appropriate. (Needs rethinking.)
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        if ($_MIDCOM->auth->admin)
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "admin/create/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create person'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_person.png',
                )
            );
        }

        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item(array(
                MIDCOM_TOOLBAR_URL => 'config/',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            ));
        }
    }

    /**
     * Simple helper, gives the base URL for a person (either username/ or
     * guid/, depending on the username).
     *
     * All 1 argument handlers are filtered here.
     *
     * @param midcom_db_person The person to query.
     * @return string The URL to use.
     */
    function get_url($person, $guid = null)
    {
        $prefix = '';
        if (!is_null($guid))
        {
            $prefix = "group/{$guid}/";
        }

        if (   $person->username
            && $person->username != 'vcard.vcf'
            && $person->username != 'foaf.rdf')
        {
            return "{$prefix}{$person->username}/";
        }
        else
        {
            return "{$prefix}{$person->guid}/";
        }
    }

    /**
     * Indexes a person.
     *
     * This function is usually called statically from various handlers.
     *
     * @param midcom_helper_datamanager2_datamanager &$dm The Datamanager encapsulating the person.
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
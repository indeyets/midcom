<?php

/**
 * @package net.nemein.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar Viewer interface class.
 *
 * @package net.nemein.calendar
 */

class net_nemein_calendar_viewer extends midcom_baseclasses_components_request
{
    /**
     * Simple constructor, connect to the parent class constructor method
     * 
     * @access public
     */
    function net_nemein_calendar_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }
    
    /**
     * Set the request switches
     * 
     * @access private
     */
    function _on_initialize()
    {

        // Define the URL space

        // /upcoming/N shows next N events
        $this->_request_switch['open'] = Array
        (
            'handler' => Array('net_nemein_calendar_handler_list', 'open'),
            'fixed_args' => Array('open'),
            'variable_args' => 1,
        );

        // / shows next N (configured number) events in RSS format
        $this->_request_switch['feed-rss2'] = Array
        (
            'handler' => Array('net_nemein_calendar_handler_feed', 'rss'),
            'fixed_args' => Array('rss.xml'),
        );

        // / shows next N (configured number) events
        $this->_request_switch['upcoming'] = Array
        (
            'handler' => Array('net_nemein_calendar_handler_list', 'upcoming'),
        );

        // /upcoming/N shows next N events
        $this->_request_switch['upcoming-count'] = Array
        (
            'handler' => Array('net_nemein_calendar_handler_list', 'upcoming'),
            'fixed_args' => Array('upcoming'),
            'variable_args' => 1,
        );

        // /rootevent creates root event for the calendar
        $this->_request_switch['create-rootevent'] = Array
        (
            'handler' => Array('net_nemein_calendar_handler_create', 'rootevent'),
            'fixed_args' => Array('rootevent'),
        );

        // /week/<date> shows all events of selected week
        $this->_request_switch['week'] = Array
        (
            'handler' => Array('net_nemein_calendar_handler_list', 'week'),
            'fixed_args' => Array('week'),
            'variable_args' => 1,
        );

        // /between/<from date>/<to date> shows all events of selected week
        $this->_request_switch['between'] = Array
        (
            'handler' => Array('net_nemein_calendar_handler_list', 'between'),
            'fixed_args' => Array('between'),
            'variable_args' => 2,
        );

        // /archive/between/<from date>/<to date> shows all events of selected week
        // in Archive mode, only relevant for style code, it sets a flag
        // which allows better URL handling: The request context key 'archive_mode'
        // will be true in this case.
        $this->_request_switch['archive-between'] = Array
        (
            'handler' => Array('net_nemein_calendar_handler_list', 'between'),
            'fixed_args' => Array('archive', 'between'),
            'variable_args' => 2,
        );
        
        // Match /calendar/
        $this->_request_switch['calendar_current'] = Array
        (
            'handler' => Array ('net_nemein_calendar_handler_list', 'calendar'),
            'fixed_args' => Array('calendar'),
            'variable_args' => 0,
        );
        
        // Match /calendar/<year>/<month>/
        $this->_request_switch['calendar_defined'] = Array
        (
            'handler' => Array ('net_nemein_calendar_handler_list', 'calendar'),
            'fixed_args' => Array ('calendar'),
            'variable_args' => 2,
        );
        
        // /archive.html Main archive page
        $this->_request_switch['archive-welcome'] = Array
        (
            'handler' => Array('net_nemein_calendar_handler_archive', 'welcome'),
            'fixed_args' => Array('archive'),
        );
        
        // /create/<schema>.html Event creation view
        $this->_request_switch['create'] = Array
        (
            'handler' => Array('net_nemein_calendar_handler_create', 'create'),
            'fixed_args' => Array('create'),
            'variable_args' => 1,
        );        

        // /edit/<event guid>.html Event editing view
        $this->_request_switch['edit'] = Array
        (
            'handler' => Array('net_nemein_calendar_handler_edit', 'edit'),
            'fixed_args' => Array('edit'),
            'variable_args' => 1,
        ); 

        // /delete/<event guid>.html Event deletion view
        $this->_request_switch['delete'] = Array
        (
            'handler' => Array('net_nemein_calendar_handler_delete', 'delete'),
            'fixed_args' => Array('delete'),
            'variable_args' => 1,
        ); 

        // /archive/view/<event GUID>.html duplicate of the view handler for archive
        // operation, only relevant for style code, it sets a flag
        // which allows better URL handling: The request context key 'archive_mode'
        // will be true in this case.
        $this->_request_switch['archive-view'] = Array
        (
            'handler' => Array('net_nemein_calendar_handler_view', 'view'),
            'fixed_args' => Array('archive', 'view'),
            'variable_args' => 1,
        );

        // Match /config/
        $this->_request_switch['config'] = Array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/net/nemein/calendar/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );

        // /<event GUID>.html shows individual event (comes last since it could hide other
        // handlers
        $this->_request_switch['view'] = Array
        (
            'handler' => Array('net_nemein_calendar_handler_view', 'view'),
            'variable_args' => 1,
        );

        // Make the hCalendar output GRDDL compatible
        // FIXME: We need method for adding:
        // <head profile="http://www.w3.org/2003/g/data-view">
        $_MIDCOM->add_link_head(
            Array(
                'rel' => 'transformation',
                'href' => 'http://www.w3.org/2002/12/cal/glean-hcal.xsl'
            )
        );
    }
    
    /**
     * Load the root objects
     * 
     * @access private
     * @return boolean
     */
    function _on_can_handle($handler, $args)
    {
        // Prevent infinite loops
        if (in_array('rootevent', $args))
        {
            return true;
        }
        
        // Load master and root event
        if (count($args) > 0)
        {
            $qb = midcom_db_topic::new_query_builder();
            $qb->add_constraint('name', '=', (string) $args[0]);
            $qb->add_constraint('up', '=', $this->_topic->id);
            
            if ($qb->count() > 0)
            {
                return true;
            }
            
            // content topic nested
            $this->_load_root_objects($args[0]);
        }
        else
        {
            $this->_load_root_objects();
        }
        
        return true;
    }
    
    function _on_handle($handler, $args)
    {
        if (array_key_exists('root_event', $this->_request_data))
        {
            // Load schema database
            $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
            $this->_add_categories();
        
            // Populate toolbars
            $this->_populate_node_toolbar();
        }
        
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel'   => 'alternate',
                'type'  => 'application/rss+xml',
                'title' => $this->_l10n->get('rss 2.0 feed'),
                'href'  => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'rss.xml',
            )
        );
        
        return true;
    }
    
    function _load_root_objects($arg = '')
    {
        // Load master event if set
        if (is_null($this->_config->get('master_event')))
        {
            $this->_request_data['master_event'] = 0;
        }
        else
        {
            $master_event = new net_nemein_calendar_event($this->_config->get('master_event'));
            if (!$master_event)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Master event not found: ".mgd_errstr());
            }
            $this->_request_data['master_event'] = $master_event->id;
        }
        
        
        // Load root event or redirect to creation
        if (is_null($this->_config->get('root_event')))
        {
            if ($arg !== 'rootevent')
            {
                $nap = new midcom_helper_nav();
                $node = $nap->get_node($this->_topic->id);
                
                $_MIDCOM->relocate("{$node[MIDCOM_NAV_FULLURL]}rootevent.html");
                // This will exit
            }
        }
        
        $this->_request_data['root_event'] = new net_nemein_calendar_event($this->_config->get('root_event'));
        
        if (!is_object($this->_request_data['root_event']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Root event not found: ".mgd_errstr());
            // This will exit
        }
    }

    /**
     * Populates the node toolbar depending on the users rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        if ($this->_request_data['root_event']->can_do('midgard:create'))
        {
            foreach (array_keys($this->_request_data['schemadb']) as $name)
            {
                $this->_node_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "create/{$name}.html",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create %s'),
                        $this->_l10n->get($this->_request_data['schemadb'][$name]->description)
                    ),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'n',
                ));
            }
        }

        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item(
                array
                (
                    MIDCOM_TOOLBAR_URL => 'config.html',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }
    }
    
    /**
     * Populate the categories configured for the topic into the schemas
     */
    function _add_categories()
    {
        if ($this->_config->get('categories') == '')
        {
            // No categories defined, skip this.
            $this->_request_data['categories'] = Array();
            return false;
        }
        
        $this->_request_data['categories'] = explode(',', $this->_config->get('categories'));
        
        foreach ($this->_request_data['schemadb'] as $name => $schema)
        {
            if (   array_key_exists('categories', $schema->fields)
                && $this->_request_data['schemadb'][$name]->fields['categories']['type'] == 'select')
            {
                // TODO: Merge schema local and component config categories?
                $this->_request_data['schemadb'][$name]->fields['categories']['type_config']['options'] = Array();
                foreach ($this->_request_data['categories'] as $category)
                {
                    $this->_request_data['schemadb'][$name]->fields['categories']['type_config']['options'][$category] = $category;
                }
            }
        }
    }
    
    /**
     * Indexes an event.
     *
     * This function is usually called statically from various handlers.
     *
     * @param midcom_helper_datamanager2_datamanager $dm The Datamanager encaspulating the event.
     * @param midcom_services_indexer $indexer The indexer instance to use.
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
        $document->component = $topic->component;
        $document->read_metadata_from_object($dm->storage->object);
        $indexer->index($document);
    }
}
?>
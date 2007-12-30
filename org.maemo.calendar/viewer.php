<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module. 
 * 
 * @package org.maemo.calendar
 */

class org_maemo_calendar_viewer extends midcom_baseclasses_components_request
{

    function org_maemo_calendar_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);

        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();       
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        /**
         * Prepare the request switch, which contains URL handlers for the component
         */
 
        // Handle /config
        $this->_request_switch['config'] = array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/org/maemo/calendar/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );

        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('org_maemo_calendar_handler_index', 'index'),
        );
        
        // Match /view/<timestamp>/<type>
        $this->_request_switch['view'] = array(
            'handler' => Array('org_maemo_calendar_handler_index', 'view'),
            'fixed_args' => array('view'),
            'variable_args' => 2,
        );

       // Match /event/create/<timestamp>
       $this->_request_switch['event-create'] = array(
           'handler' => Array('org_maemo_calendar_handler_event_create', 'create'),
           'fixed_args' => array('event', 'create'),
           'variable_args' => 1,
       );
       // Match /ajax/event/create/<timestamp>
       $this->_request_switch['ajax-event-create'] = array(
           'handler' => Array('org_maemo_calendar_handler_event_create', 'create'),
           'fixed_args' => array('ajax', 'event', 'create'),
           'variable_args' => 1,
       );

       // Match /event/edit/<guid>
       $this->_request_switch['event-edit'] = array(
           'handler' => Array('org_maemo_calendar_handler_event_admin', 'edit'),
           'fixed_args' => array('event', 'edit'),
           'variable_args' => 1,
       );
       // Match /ajax/event/edit/<guid>
       $this->_request_switch['ajax-event-edit'] = array(
           'handler' => Array('org_maemo_calendar_handler_event_admin', 'edit'),
           'fixed_args' => array('ajax', 'event', 'edit'),
           'variable_args' => 1,
       ); 
       // Match /ajax/event/move/<guid>/<timestamp>
       $this->_request_switch['ajax-event-move'] = array(
          'handler' => Array('org_maemo_calendar_handler_event_admin', 'move'),
          'fixed_args' => array('ajax', 'event', 'move'),
          'variable_args' => 2,
       );
             
       // Match /event/show/<guid>
       $this->_request_switch['event-show'] = array(
           'handler' => Array('org_maemo_calendar_handler_event_view', 'show'),
           'fixed_args' => array('event', 'show'),
           'variable_args' => 1,
       );
       // Match /ajax/event/show/<guid>
       $this->_request_switch['ajax-event-show'] = array(
           'handler' => Array('org_maemo_calendar_handler_event_view', 'show'),
           'fixed_args' => array('ajax', 'event', 'show'),
           'variable_args' => 1,
       );

       // Match /ajax/event/remove/<guid>
       $this->_request_switch['ajax-event-delete'] = array(
           'handler' => Array('org_maemo_calendar_handler_event_admin', 'delete'),
           'fixed_args' => array('ajax', 'event', 'delete'),
           'variable_args' => 1,
       );              
       
       // Match /ajax/buddylist/search
       $this->_request_switch['ajax-buddylist-search'] = array(
          'handler' => Array('org_maemo_calendar_handler_buddylist_admin', 'search'),
          'fixed_args' => array('ajax', 'buddylist', 'search'),
       );
        // Match /ajax/buddylist/add/<guid>
        $this->_request_switch['ajax-buddylist-add'] = array(
           'handler' => Array('org_maemo_calendar_handler_buddylist_admin', 'add'),
           'fixed_args' => array('ajax', 'buddylist', 'add'),
           'variable_args' => 1,
        );
        // Match /ajax/buddylist/remove/<guid>
        $this->_request_switch['ajax-buddylist-remove'] = array(
           'handler' => Array('org_maemo_calendar_handler_buddylist_admin', 'remove'),
           'fixed_args' => array('ajax', 'buddylist', 'remove'),
           'variable_args' => 1,
        );
        // Match /ajax/buddylist/action/<action>/<guid>
        $this->_request_switch['ajax-buddylist-action'] = array(
           'handler' => Array('org_maemo_calendar_handler_buddylist_admin', 'action'),
           'fixed_args' => array('ajax', 'buddylist', 'action'),
           'variable_args' => 2,
        );
            
       // Match /ajax/change/date/<timestamp>
       $this->_request_switch['ajax-change-date'] = array(
        'handler' => Array('org_maemo_calendar_handler_ajax', 'ajax_change_date'),
           'fixed_args' => array('ajax', 'change', 'date'),
           'variable_args' => 1,
       );
       // Match /ajax/change/date/<timestamp>/<type>
       $this->_request_switch['ajax-change-date'] = array(
           'handler' => Array('org_maemo_calendar_handler_ajax', 'ajax_change_date'),
           'fixed_args' => array('ajax', 'change', 'date'),
           'variable_args' => 2,
       );
        // Match /ajax/change/view/<timestamp>/<type>
        $this->_request_switch['ajax-change-view'] = array(
            'handler' => Array('org_maemo_calendar_handler_ajax', 'ajax_change_view'),
            'fixed_args' => array('ajax', 'change', 'view'),
            'variable_args' => 2,
        );
        // Match /ajax/change/timezone/<timestamp>/<type>
        $this->_request_switch['ajax-change-timezone'] = array(
            'handler' => Array('org_maemo_calendar_handler_ajax', 'ajax_change_timezone'),
            'fixed_args' => array('ajax', 'change', 'timezone'),
            'variable_args' => 2,
        );
        
        // Match /ajax/profile/view
        $this->_request_switch['ajax-profile-view'] = array(
            'handler' => Array('org_maemo_calendar_handler_profile_view', 'view'),
            'fixed_args' => array('ajax', 'profile', 'view'),
        );
        // Match /ajax/profile/view/<guid>
        $this->_request_switch['ajax-profile-view-other'] = array(
            'handler' => Array('org_maemo_calendar_handler_profile_view', 'view'),
            'fixed_args' => array('ajax', 'profile', 'view'),
            'variable_args' => 1,
        );
        // Match /ajax/profile/edit
        $this->_request_switch['ajax-profile-edit'] = array(
            'handler' => Array('org_maemo_calendar_handler_profile_admin', 'edit'),
            'fixed_args' => array('ajax', 'profile', 'edit'),
        );
        // Match /ajax/profile/publish
        $this->_request_switch['ajax-profile-publish'] = array(
            'handler' => Array('org_maemo_calendar_handler_profile_publish', 'publish'),
            'fixed_args' => array('ajax', 'profile', 'publish'),
        );
        // Match /ajax/profile/publish/ok
        $this->_request_switch['ajax-profile-publish-ok'] = array(
            'handler' => Array('org_maemo_calendar_handler_profile_publish', 'publish_ok'),
            'fixed_args' => array('ajax', 'profile', 'publish', 'ok'),
        );
        
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.maemo.calendar/styles/application.css",
            )
        );
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.maemo.calendar/styles/elements.css",
            )
        );
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.maemo.calendar/styles/farbtastic.css",
            )
        );        
        // $_MIDCOM->add_link_head
        // (
        //     array
        //     (
        //         'rel' => 'stylesheet',
        //         'type' => 'text/css',
        //         'href' => MIDCOM_STATIC_URL."/org.maemo.calendar/styles/jqModal.css",
        //     )
        // );
    
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/midcom.helper.datamanager2/jscript-calendar/calendar-win2k-1.css",
            )
        );
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/midcom.helper.datamanager2/universalchooser.css",
            )
        );
        $_MIDCOM->add_link_head(
            array(
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/tags/jquery.tags_widget.css'
            )
        );
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/midcom.helper.datamanager2/legacy.css",
            )
        );

        $_MIDCOM->enable_jquery();

        // // Load required Javascripts
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/org.maemo.calendar/js/jquery.textSelection.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/interface/interface-1.2.js');
        // //$_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/org.maemo.calendar/js/jqModal.js',true);
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/org.maemo.calendar/js/jquery.blockUI.js',false);
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/jquery.flydom-3.0.6.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/jquery.form-1.0.3.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/org.maemo.calendar/js/jquery.farbtastic.js');
                        
        //$_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/org.openpsa.helpers/ajaxutils.js', false);
        //$_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/org.openpsa.relatedto/related_to.js', false);
        //$_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/universalchooser.js', false);
        
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/tags/jquery.bgiframe.min.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/jquery.dimensions.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/tags/jquery.tags_widget.js');

        $_MIDCOM->add_link_head(
            array(
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/chooser/jquery.chooser_widget.css'
            )
        );        
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/chooser/jquery.chooser_widget.js');
        
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/jscript-calendar/calendar-setup.js', true);
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/jscript-calendar/lang/calendar-en.js', true);
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/jscript-calendar/calendar.js', true);

        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/Pearified/JavaScript/Prototype/prototype.js', true);        

        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/org.maemo.calendar/js/calendar.pack.js');
        //$_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/org.maemo.calendar/js/calendar.js');
        
        $script = 'var MIDCOM_STATIC_URL = "' . MIDCOM_STATIC_URL . '";'."\n";
        $script .= 'var HOST_PREFIX = "' . $_MIDCOM->get_host_prefix() . '";'."\n";
        
        $_MIDCOM->add_jscript($script,"",true);
        
        $script = '            
            jQuery("#main-panel-accordion").Accordion(
                            {
                                headerSelector  : "div.accordion-leaf-header",
                                panelSelector   : "div.accordion-leaf-body",
                                activeClass     : "accordion-leaf-active",
                                hoverClass      : "accordion-leaf-hover",
                                panelHeight     : 235,
                                speed           : 80
                            }
            );
            jQuery("div.calendar-layerholder div.calendar-object-event-header").textSelection("disable");
            load_shelf_contents();
            modify_foreground_color("div.calendar-object-event");
            show_layout();
        '."\n";
        
        $_MIDCOM->add_jquery_state_script($script);
    }

    /**
     * Indexes an article.
     *
     * This function is usually called statically from various handlers.
     *
     * @param midcom_helper_datamanager2_datamanager $dm The Datamanager encapsulating the event.
     * @param midcom_services_indexer $indexer The indexer instance to use.
     * @param midcom_db_topic The topic which we are bound to. If this is not an object, the code
     *     tries to load a new topic instance from the database identified by this parameter.
     */
    function index(&$dm, &$indexer, $topic)
    {
        // if (is_object($topic))
        // {
        //     $tmp = new midcom_db_topic($topic);
        //     if (! $tmp)
        //     {
        //         $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
        //             "Failed to load the topic referenced by {$topic} for indexing, this is fatal.");
        //         // This will exit.
        //     }
        //     $topic = $tmp;
        // }
    
        // Don't index directly, that would loose a reference due to limitations
        // of the index() method. Needs fixes there.
    
        // $nav = new midcom_helper_nav();
        // $node = $nav->get_node($topic->id);
        // $author = $_MIDCOM->auth->get_user($dm->storage->object->creator);
        //     
        // $document = $indexer->new_document($dm);
        // $document->topic_guid = $topic->guid;
        // $document->component = $topic->component;
        // $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        // $document->author = $author->name;
        // $document->created = $dm->storage->object->created;
        // $document->edited = $dm->storage->object->revised;
        // $indexer->index($document);
    }

    /**
     * Populates the node toolbar depending on the users rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {   
        /*
        if ($this->_content_topic->can_do('midgard:create'))
        {
            foreach (array_keys($this->_request_data['schemadb']) as $name)
            {
                $this->_node_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "create/{$name}.html",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create %s'),
                        $this->_request_data['schemadb'][$name]->description
                    ),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                ));
            }
        }
        */
        if (   $this->_request_data['root_event']->can_do('midgard:update')
            && $this->_request_data['root_event']->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => '__ais/acl/edit/' . $this->_request_data['root_event']->guid . '.html',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('root event permissions'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('root event permission helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                )
            );
        }        
        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item
            (
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
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $this->_request_data['root_event_id'] = (int)$GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']->id;
        $this->_request_data['root_event'] =& $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event'];
        
        $src = $this->_config->get('schemadb');
        $schemadb = midcom_helper_datamanager2_schema::load_database($src);

        if (count($schemadb) < 1)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to load the schema db from '{$src}'!");
            // This will exit.
        }

        $this->_populate_node_toolbar();

        $this->_request_data['schemadb'] = $schemadb;

        return true;
    }   

}

?>

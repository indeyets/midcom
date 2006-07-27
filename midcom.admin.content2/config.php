<?php
/**
 * Basic handler class
 * @package aegir.admin.content2
 */

class midcom_admin_content2_config extends midcom_baseclasses_components_handler {

    /**
     * pointer to the topic or page in question
     * (for now it is the topic id!)
     */
    var $_page = null;

    /**
     * The datamanager used to edit the component
     *
     * @var midcom_helper_datamanager2_controller
     * @access private
     */
    var $_controller = null;
    /**
     * Helper variable, containg a localized message to be shown to the user indicating the form's
     * processing state.
     *
     * @var string
     * @access private
     */
    var $_processing_msg = '';

    /**
     * Pointer to the module configuration
     */
    var $_handler_config = null;
    /**
     * The schema to use for the current dm
     */
    var $_schema;


    function midcom_admin_content2_config()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        // Populate the request data with references to the class members we might need
        /* if we are missing the aegir_interface, we load it from the pluginclass */
        if (!array_key_exists('aegir_interface',$this->_request_data))
        {
            $this->_request_data['aegir_interface'] =  midcom_admin_core_plugin::get_handler('midcom.admin.content2', 'midcom_admin_content2_aegir');
            $this->_page = $this->_master->_topic;
        }
        $this->_handler_config = &$this->_request_data['aegir_interface']->get_handler_config('midcom.admin.content2');

        $_MIDCOM->style->prepend_component_styledir('midcom.admin.content2');

        //$this->_request_data['toolbars']    = & midcom_helper_toolbars::get_instance();
        $_MIDCOM->load_library('midcom.helper.datamanager2');

        $style_attributes = array ( 'rel'   =>  "stylesheet" ,
                                    'type'  =>  "text/css" ,
                                    'media' =>  "screen"
                                    );
        $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.helper.datamanager/datamanager.css";
        $_MIDCOM->add_link_head( $style_attributes);
        $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.content/ais.css";
        $_MIDCOM->add_link_head( $style_attributes);
        $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.content/midcom_toolbar.css";
        $_MIDCOM->add_link_head( $style_attributes);
        // moved to request.css. 
        //$style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.core/toolbars.css";
        //$_MIDCOM->add_link_head( $style_attributes);

        $this->_request_data['l10n_handler'] = $this->_l10n =& $this->_i18n->get_l10n('midcom.admin.content2');

    }

    /**
     * generate the datamanager controller instance and run it.
     * Note:
     * Nullstorage users have to run process_form themselves.
     * @param int id of topic
     * @param string type of controller (simple or nullstorage)
     */
    function _run_datamanager($component_id, $type, $defaults = array ())
    {
        $this->_page = new midcom_db_topic($component_id);
        // set the current node
        $this->_request_data['aegir_interface']->set_current_node($this->_page->id);

        $this->_controller =& midcom_helper_datamanager2_controller::create($type);
        $this->_controller->set_schemadb(&$this->_schema);

        //$this->_controller->schemaname = 'component_config';
        if ($type != 'nullstorage') {
            $this->_controller->set_storage($this->_page);
        }
        $this->_controller->defaults = $defaults;
        $this->_controller->initialize();

        $this->_request_data['datamanager'] = & $this->_controller;

        return $this->_controller->process_form();
    }

    function _handler_view ($handler_id, $args, &$data)
    {
        //$this->create_context($args[0]);
        $this->_prepare_main_toolbar();
        $this->_run_datamanager($args[0], 'nullstorage');
        $this->_request_data['title'] = $this->_page->name;
        return true;
    }

    function _handler_edit ($handler_id, $args, &$data)
    {

        $defaults = array();
        $this->_schema = midcom_helper_datamanager2_schema::load_database($this->_handler_config->get('schemadb_modules'));
        $this->_run_datamanager($args[0], 'simple', $defaults);

        $this->_prepare_main_toolbar();

        return true;
    }

    function _show_edit($handler_id, &$data)
    {
        $data['title'] = $_MIDCOM->i18n->get_string('edit', 'midcom');
        midcom_show_style('topic-edit');
    }

/**
     * Locates the topic to delete and prepares everything for the view run,
     * there the user has to confirm the deletion. This includes the toolbar
     * preparations and the preparation of the
     * topic and datamanager instances.
     *
     * @access private
     */
    function _handler_delete ($handler_id, $args, &$data)
    {
        if (array_key_exists('admin_content_simplecontent_deleteok', $_POST)) {
            $up = $this->_page->guid;
            if ($this->_delete_topic($args[0]))
            {
                $_MIDCOM->relocate('../');
                // this will exit
            }
        }
        $defaults = array();
        $this->_schema = midcom_helper_datamanager2_schema::load_database($this->_handler_config->get('schemadb_modules'));
        $this->_run_datamanager($args[0], 'simple', $defaults);
        $this->_request_data['title'] = $this->_page->extra;
        $this->_request_data['id'] = $this->_page->guid;

        $this->_prepare_main_toolbar();

        //$this->_request_data['object'] = &$this->_page;
        return true;
    }

    function _handler_create ($handler_id, $args, &$data)
    {
        $defaults = array('component' => 'de.linkm.taviewer');

        $schema = $this->_load_schema();

        $schema['component']['fields']['component'] =Array
            (
            'title' => 'Component',
            'description' => 'Choose the component you want for this topic',
            'helptext' => '',
            'storage' => Array
                (
                    'location' => 'parameter',
                    'domain'   => 'midcom',
                ),
            'type' => 'select',
            'type_config' => Array
            (
                'options' => null,
                'option_callback' => 'midcom_admin_content2_callbacks_componentselector',
                'allow_multiple' => false,
                'allow_other' => false,
            ),
            'widget' => 'select',
            );

        $this->_schema = midcom_helper_datamanager2_schema::load_database($schema);


        $result = $this->_run_datamanager($args[0], 'nullstorage', $defaults);

        if ($result == 'save' ) {
            if (($id = $this->_create_topic()) )
            {
                $topic = new midcom_db_topic($id);
                $controller = midcom_helper_datamanager2_controller::create('simple');
                $controller->schemadb = & $this->_controller->schemadb;
                $controller->set_storage($topic, 'component');
                $controller->initialize();
                $result = $controller->process_form();
                if ($result == 'edit') {
                    $_MIDCOM->generate_error("Could not update created topic with id $id!");
                    // this will exit.
                }

                $_MIDCOM->relocate("{$topic->name}/");
            } else {
                $_MIDCOM->generate_error("Could not create topic.");
            }
        }


        $this->_prepare_main_toolbar();

        return true;
    }
    /**
     * This function is used by the _handler_create function to get the schema.
     */
    function _load_schema() {
        $data = midcom_get_snippet_content($this->_handler_config->get('schemadb_modules'));
        eval ("\$schema = Array ( {$data}\n );");
        return $schema;
    }

    function _create_topic()
    {
        $topic = new midcom_db_topic();
        $topic->up = $this->_page->id;
        $topic->name = "tempname-" . time();
        $id = $topic->create();
        if (!$id) {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not create Midcom topic!" . mgd_errstr());
            debug_pop();
        }
        return $topic->guid;
    }
    function _handler_move ($handler_id, $args, &$data){

    }

    function _show_view () {
        midcom_show_style('admin_view2');
    }

    function _show_create($handler_id, &$data) {
        $data['title'] = $_MIDCOM->i18n->get_string('create', 'midcom');
        midcom_show_style("topic-edit");
    }

    /**
     * Renders the selected topic using the datamnager view mode.
     *
     * @access private
     */
    function _show_delete ($handler_id, &$data)
    {
        midcom_show_style('admin_deletecheck');
    }


    function _midcom_admin_content_list_styles_selector($up = null, $spacer = '', $path = '/', $show_shared = false) {
      $midgard = $GLOBALS["midcom"]->get_midgard();

      if (array_key_exists("view",$GLOBALS)) {
        $current_style = $GLOBALS["view"]->parameter("midcom", "style");
      } else {
        $current_style = '';
      }

      if (is_null ($up)) {
        $styles = mgd_list_styles();
      } else {
        $styles = mgd_list_styles($up);
      }

      if ($styles) {
        while ($styles->fetch()) {
          $style = mgd_get_style($styles->id);

          if (!$show_shared && ($style->sitegroup != $midgard->sitegroup)) {
            continue;
          }
          if ($show_shared && ($style->sitegroup == $midgard->sitegroup)) {
            continue;
          }

          // Don't show groups deeper in hierarchy as toplevel
          if (is_null($up)) {
            if ($style->up != 0) {
              continue;
            }
          }

          if ($current_style == $path.$styles->name) {
            echo '<option value="' . $path.$styles->name . '" selected="selected">' . $spacer . $styles->name . "</option>\n";
          } else {
            echo '<option value="' . $path.$styles->name . '">' . $spacer . $styles->name . "</option>\n";
          }
          midcom_admin_content_list_styles_selector($styles->id, $spacer."&nbsp;&nbsp;&nbsp;&nbsp;",$path.$styles->name."/",$show_shared);
        }
      }
    }

    function _prepare_main_toolbar()
    {
        if (array_key_exists('aegir_interface',$this->_request_data))
        {
            $this->_request_data['aegir_interface']->prepare_toolbar();
        }
        return;

    }
    /**
     * This function deletes a topic and it's decendants.
     * Noone is spared.
     */
    function _delete_topic($topic)
    {
        $this->_delete_topic_update_index();

        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_page->id);
        $articles = $qb->execute();

        if (is_null($articles))
        {
            debug_add("Failed to query the articles of this topic: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            $this->_contentadm->msg = "Error: Could not delete Topic contents: " . mgd_errstr();
            return false;
        }

        foreach ($articles as $article)
        {
            if (!$article->delete())
            {
                debug_add("Could not delete Article {$article->id}:" . mgd_errstr(), MIDCOM_LOG_ERROR);
                $this->_contentadm->msg = "Error: Could not delete Topic contents: " . mgd_errstr();
                return false;
            }
        }

        if (!$this->_page->delete())
        {
            debug_add("Could not delete Topic {$this->_page->id}: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            $this->_contentadm->msg = "Error: Could not delete Topic contents: " . mgd_errstr();
            return false;
        }

        // Invalidate everything since we operate recursive here.
        $GLOBALS['midcom']->cache->invalidate_all();
        return true;
    }

    /**
     * Clear the topic from the index.
     */
    function _delete_topic_update_index()
    {
        if ($GLOBALS['midcom_config']['indexer_backend'] === false)
        {
            // Indexer is not configured.
            return;
        }

        debug_push_class(__CLASS__, __FUNCTION__);

        debug_add("Dropping all NAP registered objects from the index.");

        // First we collect everything we have to delete, this might take a while
        // so we keep an eye on the script timeout.
        $guids = Array();
        $nap = new midcom_helper_nav();

        $node_list = Array($nap->get_current_node());

        while (count($node_list) > 0)
        {
            set_time_limit(30);

            // Add the node being processed.
            $nodeid = array_shift($node_list);
            debug_add("Processing node {$nodeid}");

            $node = $nap->get_node($nodeid);
            $guids[] = $node[MIDCOM_NAV_GUID];

            debug_add("Processing leaves of node {$nodeid}");
            $leaves = $nap->list_leaves($nodeid, true);
            debug_add("Got " . count($leaves) . " leaves.");
            foreach ($leaves as $leafid)
            {
                $leaf = $nap->get_leaf($leafid);
                $guids[] = $leaf[MIDCOM_NAV_GUID];
            }

            debug_add("Loading subnodes");
            $node_list = array_merge($node_list, $nap->list_nodes($nodeid, true));
            debug_print_r("Remaining node queue", $node_list);
        }

        debug_add("We have to delete " . count($guids) . " objects from the index.");

        // Now we go over the entire index and delete the corresponding objects.
        // We load all attachments of the corresponding objects as well, to have
        // them deleted too.
        //
        // Again we keep an eye on the script timeout.
        $indexer =& $GLOBALS['midcom']->get_service('indexer');
        foreach ($guids as $guid)
        {
            set_time_limit(60);

            $object = mgd_get_object_by_guid($guid);
            $atts = $object->listattachments();
            if ($atts)
            {
                while ($atts->fetch())
                {
                    debug_add("Deleting attachment {$atts->id} from the index.");
                    $indexer->delete($atts->guid());
                }
            }

            debug_add("Deleting guid {$guid} from the index.");
            $indexer->delete($guid);
        }

        debug_pop();
    }

}

?>
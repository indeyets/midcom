<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Wikipage creation handler
 *
 * @package net.nemein.wiki
 */
class net_nemein_wiki_handler_create extends midcom_baseclasses_components_handler
{
    /**
     * Wiki word we're creating page for
     * @var string
     */
    var $_wikiword = '';

    /**
     * The wikipage we're creating
     *
     * @var net_nemein_wiki_wikipage
     * @access private
     */
    var $_page = null;

    /**
     * The Controller of the article used for editing
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    var $_controller = null;

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The schema to use for the new article.
     *
     * @var string
     * @access private
     */
    var $_schema = 'default';

    /**
     * The defaults to use for the new article.
     *
     * @var Array
     * @access private
     */
    var $_defaults = Array();

    function net_nemein_wiki_handler_create()
    {
        parent::midcom_baseclasses_components_handler();
        $_MIDCOM->load_library('org.openpsa.relatedto');
    }

    /**
     * Internal helper, fires up the creation mode controller. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_request_data['schemadb'];
        $this->_controller->schemaname = $this->_schema;
        $this->_controller->defaults = $this->_defaults;
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function & dm2_create_callback (&$controller)
    {
        $this->_page = new net_nemein_wiki_wikipage();
        $this->_page->topic = $this->_topic->id;
        $this->_page->title = $this->_wikiword;
        $this->_page->author = $_MIDGARD['user'];

        // We can clear the session now
        $this->_request_data['session']->remove('wikiword');

        if (! $this->_page->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_page);
            debug_pop();
            if (class_exists('org_openpsa_relatedto_handler'))
            {
                // Save failed and we are likely to have data hanging around in session, clean it up
                org_openpsa_relatedto_handler::get2session_cleanup();
            }
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new page, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        $this->_page = new net_nemein_wiki_wikipage($this->_page->id);

        // Store old format "related to" information (TO BE DEPRECATED!)
        if (array_key_exists('related_to', $this->_request_data))
        {
            foreach ($this->_request_data['related_to'] as $guid => $related_to)
            {
                // Save the relation information
                $this->_page->parameter('net.nemein.wiki:related_to', $this->_request_data['related_to'][$guid]['target'], "{$this->_request_data['related_to'][$guid]['node'][MIDCOM_NAV_COMPONENT]}:{$this->_request_data['related_to'][$guid]['node'][MIDCOM_NAV_GUID]}");
            }
        }
        // Save new format "related to" information (if we have the component available)
        if (class_exists('org_openpsa_relatedto_handler'))
        {
            $rel_ret = org_openpsa_relatedto_handler::on_created_handle_relatedto($this->_page, 'net.nemein.wiki');
            //sprint_r is not part of MidCOM helpers
            ob_start();
            print_r($rel_ret);
            $rel_ret_r = ob_get_contents();
            ob_end_clean();
            debug_add("org_openpsa_relatedto_handler returned \n===\n{$rel_ret_r}===\n");
        }

        return $this->_page;
    }

    function _check_unique_wikiword($wikiword)
    {
        $resolver = new net_nemein_wiki_wikipage();
        $resolver->topic = $this->_topic->id;
        $resolved = $resolver->path_to_wikipage($wikiword, true, true);
        if (!empty($resolved['latest_parent']))
        {
            $to_node =& $resolved['latest_parent'];
        }
        else
        {
            $to_node =& $resolved['folder'];
        }
        $created_page = false;
        switch (true)
        {
            case (strstr($resolved['remaining_path'], '/')):
                    // One or more namespaces left, find first, create it and recurse
                    $paths = explode('/', $resolved['remaining_path']);
                    $folder_title = array_shift($paths);
                    //echo "NOTICE: Creating new wiki topic '{$folder_title}' under #{$to_node[MIDCOM_NAV_ID]}<br/>\n";
                    $topic = new midcom_db_topic();
                    $topic->up = $to_node[MIDCOM_NAV_ID];
                    $topic->extra = $folder_title;
                    if (isset($topic->title))
                    {
                        // 1.8 topic->title support
                        $topic->title = $folder_title;
                    }
                    $topic->name = midcom_generate_urlname_from_string($folder_title);
                    if (isset($topic->component))
                    {
                        $topic->component = 'net.nemein.wiki';
                    }
                    if (!$topic->create())
                    {
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not create wiki namespace '{$folder_title}'");
                        // This will exit()
                    }
                    // refresh
                    $topic = new midcom_db_topic($topic->id);
                    // Set the component parameter (even if we have it in object)
                    /*
                    if (!isset($topic->component))
                    {
                    */
                        $topic->set_parameter('midcom', 'component', 'net.nemein.wiki');
                    //}

                    // See if we have article with same title in immediate parent
                    $qb = net_nemein_wiki_wikipage::new_query_builder();
                    $qb->add_constraint('title', '=', $folder_title);
                    $qb->add_constraint('topic', '=', $topic->up);
                    $results = $qb->execute();
                    /*
                    echo "DEBUG: results for searching page '{$folder_title}' in topic #{$topic->up}<pre>\n";
                    print_r($results);
                    echo "</pre>\n";
                    */
                    if (   is_array($results)
                        && count($results) == 1)
                    {
                        //echo "INFO: Found page with same title in parent, moving to be index of this new topic<br/>\n";
                        $article =& $results[0];
                        $article->name = 'index';
                        $article->topic = $topic->id;
                        if (!$article->update())
                        {
                            // Could not move article, do something ?
                            //echo "FAILURE: Could not move the page, errstr: ". mgd_errstr() . "<br/>\n";
                        }
                    }
                    else
                    {
                        $created_page = net_nemein_wiki_viewer::initialize_index_article($topic);
                        if (!$created_page)
                        {
                            // Could not create index
                            $topic->delete();
                            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not create index for new topic, errstr: " . mgd_errstr());
                            // This will exit()
                        }
                    }
                    // We have created a new topic, now recurse to create the rest of the path.
                    //echo "INFO: New topic created with id #{$topic->id}, now recursing the import to process next levels<br/>\n";
                    return $this->_check_unique_wikiword($wikiword);
                break;
            case (is_object($resolved['wikipage'])):
                    // Page exists
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Wiki page with that name already exists.');
                    //This will exit()
                break;
            default:
                    // No more namespaces left, create the page to latest parent
                    if ($to_node[MIDCOM_NAV_ID] != $this->_topic->id)
                    {
                        // Last parent is not this topic, redirect there
                        $wikiword_url = rawurlencode($resolved['remaining_path']);
                        $_MIDCOM->relocate($to_node[MIDCOM_NAV_FULLURL] . "create/{$this->_schema}?wikiword={$wikiword_url}");
                        // This will exit()
                    }
                break;
        }
        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        // Initialize sessioning first
        $data['session'] = new midcom_service_session();

        if (!array_key_exists('wikiword', $_GET))
        {
            if (!$data['session']->exists('wikiword'))
            {
                // No wiki word given
                return false;
            }
            else
            {
                $this->_wikiword = $data['session']->get('wikiword');
            }
        }
        else
        {
            $this->_wikiword = $_GET['wikiword'];
            $data['session']->set('wikiword', $this->_wikiword);
        }

        $this->_topic->require_do('midgard:create');

        if ($handler_id == 'create_by_word_schema')
        {
            $this->_schema = $args[0];
        }
        else
        {
            $this->_schema = $this->_config->get('default_schema');
        }

        if (!array_key_exists($this->_schema, $data['schemadb']))
        {
            return false;
        }

        $this->_check_unique_wikiword($this->_wikiword);

        $this->_defaults['title'] = $this->_wikiword;

        $this->_load_controller();

        if ($handler_id == 'create_by_word_relation')
        {
            if (   mgd_is_guid($args[0])
                && mgd_is_guid($args[1]))
            {
                // We're in "Related to" mode
                $nap = new midcom_helper_nav();
                $related_to_node = $nap->resolve_guid($args[1]);
                if ($related_to_node)
                {
                    $data['related_to'][$related_to_node[MIDCOM_NAV_GUID]] = array
                    (
                        'node'   => $related_to_node,
                        'target' => $args[1],
                    );
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Reindex the article
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nemein_wiki_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);

                $_MIDCOM->uimessages->add($this->_l10n->get('net.nemein.wiki'), sprintf($this->_l10n->get('page %s added'), $this->_wikiword), 'ok');

                $_MIDCOM->relocate("{$this->_page->name}/");
                // This will exit.

            case 'cancel':
                if (class_exists('org_openpsa_relatedto_handler'))
                {
                    // Save cancelled and we are likely to have data hanging around in session, clean it up
                    org_openpsa_relatedto_handler::get2session_cleanup();
                }
                $_MIDCOM->relocate('');
                // This will exit.
        }

        $data['view_title'] = sprintf($this->_request_data['l10n']->get('create wikipage %s'), $this->_wikiword);
        $_MIDCOM->set_pagetitle($data['view_title']);
        $data['preview_mode'] = false;

        // DM2 form action does not include our GET parameters, store them in session for a moment
        if (class_exists('org_openpsa_relatedto_handler'))
        {
            org_openpsa_relatedto_handler::get2session();
        }

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "create/?wikiword=" . rawurlencode($this->_wikiword),
            MIDCOM_NAV_NAME => sprintf($this->_l10n->get('create wikipage %s'), $this->_wikiword),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        // Set the help object in the toolbar
        $this->_view_toolbar->add_help_item('markdown', 'net.nemein.wiki');

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_create($handler_id, &$data)
    {
        $this->_request_data['controller'] =& $this->_controller;
        midcom_show_style('view-wikipage-edit');
    }
}
?>
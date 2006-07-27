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
    var $_schema = null;

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
        // Check for duplicates
        // TODO: This is basically duplicate from functionality in DBA create method
        $qb = net_nemein_wiki_wikipage::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_constraint('title', '=', $wikiword);
        $result = $qb->execute();
        if (count($result) > 0)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Wiki page with that name already exists.');
            // This will exit.
        }
    }
    
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:create');
        
        $this->_wikiword = $args[0];
        $this->_check_unique_wikiword($this->_wikiword);
        $this->_defaults['title'] = $this->_wikiword;
        
        $this->_load_controller();
        
        if (count($args) == 0)
        {
            return false;
        }
        if (count($args) == 3)
        {
            if (   mgd_is_guid($args[1])
                && mgd_is_guid($args[2]))
            {
                // We're in "Related to" mode
                $nap = new midcom_helper_nav();
                $related_to_node = $nap->resolve_guid($args[1]);
                if ($related_to_node)
                {
                    $this->_request_data['related_to'][$related_to_node[MIDCOM_NAV_GUID]] = array(
                        'node'   => $related_to_node,
                        'target' => $args[2],
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
                // Index the article
                //$indexer =& $_MIDCOM->get_service('indexer');
                //net_nemein_discussion_viewer::index($this->_controller->datamanager, $indexer, $this->_thread);

                $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('net.nemein.wiki'), sprintf($this->_request_data['l10n']->get('page "%s" added'), $this->_wikiword), 'ok');

                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "{$this->_page->name}.html");
                // This will exit.

            case 'cancel':
                if (class_exists('org_openpsa_relatedto_handler'))
                {
                    // Save cancelled and we are likely to have data hanging around in session, clean it up
                    org_openpsa_relatedto_handler::get2session_cleanup();
                }
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
                // This will exit.
        }
                
        $_MIDCOM->set_pagetitle(sprintf($this->_request_data['l10n']->get('create %s'), $this->_wikiword));
        
        // DM2 form action does not include our GET parameters, store them in session for a moment
        if (class_exists('org_openpsa_relatedto_handler'))
        {
            org_openpsa_relatedto_handler::get2session();
        }
        return true;
    }
    
    function _show_create($handler_id, &$data)
    {
        $this->_request_data['controller'] =& $this->_controller;    
        midcom_show_style('view-wikipage-edit');
    }    
}
?>

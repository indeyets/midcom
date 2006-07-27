<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Wikipage edit handler
 * 
 * @package net.nemein.wiki
 */
class net_nemein_wiki_handler_edit extends midcom_baseclasses_components_handler
{
    /**
     * The wikipage we're editing
     *
     * @var net_nemein_wiki_wikipage
     * @access private
     */
    var $_page = null;
    
    /**
     * The Datamanager of the article to display
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

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
    
    function net_nemein_wiki_handler_edit() 
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Loads and prepares the schema database.
     *
     * Special treatement is done for the name field, which is set readonly for non-creates
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
    }
    
   /**
     * Internal helper, loads the datamanager for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_page))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for article {$this->_article->id}.");
            // This will exit.
        }
    }

    /**
     * Internal helper, loads the controller for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_page);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for article {$this->_article->id}.");
            // This will exit.
        }
    }
    
    function _load_page($wikiword)
    {
        $qb = net_nemein_wiki_wikipage::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_constraint('name', '=', $wikiword);
        $result = $qb->execute();
        
        if (count($result) > 0)
        {
            $this->_page = $result[0];
            return true;
        }
        else
        {
            return false;
        }
    }

    function _handler_edit($handler_id, $args, &$data)
    {
        if (!$this->_load_page($args[0]))
        {
            return false;
        }
        $this->_page->require_do('midgard:update');
        
        $this->_load_controller();
        
        switch ($this->_controller->process_form())
        {
            case 'save':
                // Reindex the article
                //$indexer =& $_MIDCOM->get_service('indexer');
                //net_nehmer_blog_viewer::index($this->_controller->datamanager, $indexer, $this->_content_topic);
                $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('net.nemein.wiki'), sprintf($this->_request_data['l10n']->get('page "%s" saved'), $this->_page->title), 'ok');
                // *** FALL-THROUGH ***
            case 'cancel':
                if ($this->_page->name == 'index')
                {
                    $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));  
                }
                else
                {                
                    $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "{$this->_page->name}.html");            
                }
                // This will exit.
        }  
        
        $this->_view_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "{$this->_page->name}/",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('view'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_page->can_do('midgard:update'),
            )
        );    
        $this->_node_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "rcs/net.nemein.wiki/{$this->_page->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('Show history'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        
        $_MIDCOM->set_pagetitle(sprintf($this->_request_data['l10n']->get('edit %s'), $this->_page->title));
        
        return true;
    }
    
    function _show_edit($handler_id, &$data)
    {
        $this->_request_data['controller'] =& $this->_controller;
        midcom_show_style('view-wikipage-edit');
    }
}
?>

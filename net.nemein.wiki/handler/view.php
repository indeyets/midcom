<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Wikipage view handler
 * 
 * @package net.nemein.wiki
 */
class net_nemein_wiki_handler_view extends midcom_baseclasses_components_handler
{
    /**
     * The wikipage we're viewing
     *
     * @var net_nemein_wiki_wikipage
     * @access private
     */
    var $_page = null;
    
    /**
     * The Datamanager 2 controllerof the article to display
     *
     * @var midcom_helper_datamanager2_controller
     * @access private
     */
    var $_controller = null;
    
    function net_nemein_wiki_handler_view() 
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Internal helper, loads the datamanager for the current wikipage. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('ajax');
        $this->_controller->schemadb =& $this->_request_data['schemadb'];
        $this->_controller->set_storage($this->_page);
        $this->_controller->process_ajax();
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
            if ($wikiword == 'index')
            {
                // Autoinitialize
                $this->_topic->require_do('midgard:create');
                $this->_page = new net_nemein_wiki_wikipage();
                $this->_page->topic = $this->_topic->id;
                $this->_page->name = $wikiword;
                $this->_page->title = $this->_topic->extra;
                $this->_page->content = $this->_l10n->get('wiki default page content');
                $this->_page->author = $_MIDGARD['user'];
                if ($this->_page->create())
                {
                    return true;
                }
            }
        
            return false;
        }
    }

    /**
     * Can-Handle check against the current wikipage name. We have to do this explicitly
     * in can_handle already, otherwise we would hide all subtopics as the request switch
     * accepts all argument count matches unconditionally.
     */
    function _can_handle_view($handler_id, $args, &$data)
    {
        if (count($args) == 0)
        {
            return $this->_load_page('index');
        }
        else
        {
            return $this->_load_page($args[0]);
        }
    }
    
    function _handler_view($handler_id, $args, &$data, $view_mode = true)
    {
    
        if (!$this->_page)
        {
            return false;
        }

        
        $this->_load_datamanager();
        
        $this->_view_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "edit/{$this->_page->name}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_page->can_do('midgard:update'),
            )
        );    
        $this->_view_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "delete/{$this->_page->name}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('delete'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_page->can_do('midgard:delete'),
            )
        );  
        $this->_view_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "rcs/net.nemein.wiki/{$this->_page->guid}/",
                MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('show history', 'no.bergfald.rcs'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        ); 
        $this->_view_toolbar->bind_to($this->_page);          
        
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "{$this->_page->name}/",
            MIDCOM_NAV_NAME => $this->_page->title,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        
        $_MIDCOM->set_pagetitle($this->_page->title);        
        return true;
    }
    
    function _show_view($handler_id, &$data)
    {
        $this->_request_data['wikipage_view'] = $this->_controller->get_content_html();
        $this->_request_data['wikipage'] =& $this->_page;
        
        // Replace wikiwords
        $this->_request_data['wikipage_view']['content'] = preg_replace_callback($this->_config->get('wikilink_regexp'), array($this->_page, 'replace_wikiwords'), $this->_request_data['wikipage_view']['content']);
                
        midcom_show_style('view-wikipage');
    }
    
    function _handler_raw($handler_id, $args, &$data, $view_mode = true)
    {
        $this->_load_page($args[0]);
        if (!$this->_page)
        {
            return false;
        }
        $_MIDCOM->skip_page_style = true;
        $this->_load_datamanager();
         
        return true;
    }
    
    function _show_raw($handler_id, &$data)
    {
        $this->_request_data['wikipage_view'] = $this->_controller->get_content_html();
        
        // Replace wikiwords
        $this->_request_data['wikipage_view']['content'] = preg_replace_callback($this->_config->get('wikilink_regexp'), array($this->_page, 'replace_wikiwords'), $this->_request_data['wikipage_view']['content']);
                
        midcom_show_style('view-wikipage-raw');
    }
}
?>

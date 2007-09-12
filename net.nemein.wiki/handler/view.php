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
        
        $this->_request_data['page'] =& $this->_page;
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
    
    function _populate_toolbar()
    {

        $this->_view_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "{$this->_page->name}/",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('view'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );     
        $this->_view_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "edit/{$this->_page->name}/",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_page->can_do('midgard:update'),
            )
        );    
        $this->_view_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "delete/{$this->_page->name}/",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('delete'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_page->can_do('midgard:delete'),
            )
        );
        
        foreach (array_keys($this->_request_data['schemadb']) as $name)
        {
            if ($name == $this->_controller->datamanager->schema->name)
            {
                // The page is already of this type, skip
                continue;
            }
            
            $this->_view_toolbar->add_item(
                array
                (
                    MIDCOM_TOOLBAR_URL => "change/{$this->_page->name}/",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n->get('change to %s'),
                        $this->_l10n->get($this->_request_data['schemadb'][$name]->description)
                    ),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_refresh.png',
                    MIDCOM_TOOLBAR_POST => true,
                    MIDCOM_TOOLBAR_POST_HIDDENARGS => Array
                    (
                        'change_to' => $name,
                    ),
                    MIDCOM_TOOLBAR_ENABLED => $this->_page->can_do('midgard:update'),
                )
            );
        }
        $this->_view_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "whatlinks/{$this->_page->name}/",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('what links'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/jump-to.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        
        if ($_MIDCOM->auth->user)
        {
            $user = $_MIDCOM->auth->user->get_storage();    
            if ($this->_page->parameter('net.nemein.wiki:watch', $user->guid))
            {
                $this->_view_toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "subscribe/{$this->_page->name}/",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('unsubscribe'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mail.png',
                        MIDCOM_TOOLBAR_POST => true,
                        MIDCOM_TOOLBAR_POST_HIDDENARGS => Array
                        (
                            'unsubscribe' => 1,
                        ),
                    )
                );
            }
            else
            {
                $this->_view_toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "subscribe/{$this->_page->name}/",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('subscribe'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mail.png',
                        MIDCOM_TOOLBAR_POST => true,
                        MIDCOM_TOOLBAR_POST_HIDDENARGS => Array
                        (
                            'subscribe' => 1,
                        ),
                    )
                );
            }
        }
        
        $_MIDCOM->bind_view_to_object($this->_page, $this->_controller->datamanager->schema->name);
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

        if ($wikiword == 'index')
        {
            // Autoinitialize
            $this->_topic->require_do('midgard:create');
            $this->_page = net_nemein_wiki_viewer::initialize_index_article($this->_topic);
            if ($this->_page)
            {
                return true;
            }
        }
        
        $topic_qb = midcom_db_topic::new_query_builder();
        $topic_qb->add_constraint('up', '=', $this->_topic->id);
        $topic_qb->add_constraint('name', '=', $wikiword);
        $topics = $topic_qb->execute();
        if (count($topics) > 0)
        {
            // There is a topic by this URL name underneath, go there
            return false;
        }
        
        // We need to get the node from NAP for safe redirect
        $nap = new midcom_helper_nav();
        $node = $nap->get_node($this->_topic->id);
        
        $urlized_wikiword = midcom_generate_urlname_from_string($wikiword);
        if ($urlized_wikiword != $wikiword)
        {
            // Lets see if the page for the wikiword exists
            $qb = net_nemein_wiki_wikipage::new_query_builder();
            $qb->add_constraint('topic', '=', $this->_topic->id);
            $qb->add_constraint('title', '=', $wikiword);
            $result = $qb->execute();  
            if (count($result) > 0)
            {
                // This wiki page actually exists, so go there as "Permanent Redirect"
                $_MIDCOM->relocate("{$node[MIDCOM_NAV_ABSOLUTEURL]}{$result[0]->name}/", 301);
            }
        }
        $_MIDCOM->relocate("{$node[MIDCOM_NAV_ABSOLUTEURL]}notfound/" . rawurlencode($wikiword));
        // This will exit
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
        
        if ($this->_controller->datamanager->schema->name == 'redirect')
        {
            $qb = net_nemein_wiki_wikipage::new_query_builder();
            $qb->add_constraint('topic.component', '=', 'net.nemein.wiki');
            $qb->add_constraint('title', '=', $this->_page->url);
            $result = $qb->execute();
            if (count($result) == 0)
            {
                // No matching redirection page found, relocate to editing
                // TODO: Add UI message
                $_MIDCOM->relocate("edit/{$this->_page->name}/");
                // This will exit
            }
            
            if ($result[0]->topic == $this->_topic->id)
            {
                $_MIDCOM->relocate("{$result[0]->name}/");
            }
            else
            {
                $_MIDCOM->relocate($_MIDCOM->permalinks->create_permalink($result[0]->guid));
            }
        }

        $this->_populate_toolbar();
        $this->_view_toolbar->hide_item("{$this->_page->name}/");

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
        $data['wikipage_view'] = $this->_controller->get_content_html();
        $data['wikipage'] =& $this->_page;
        $data['autogenerate_toc'] = $this->_config->get('autogenerate_toc');
        $data['display_related_to'] = $this->_config->get('display_related_to');
        
        // Replace wikiwords
        // TODO: We should somehow make DM2 do this so it would also work in AJAX previews
        $data['wikipage_view']['content'] = preg_replace_callback($this->_config->get('wikilink_regexp'), array($this->_page, 'replace_wikiwords'), $data['wikipage_view']['content']);
        
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
        $data['wikipage_view'] = $this->_controller->get_content_html();
        $data['autogenerate_toc'] = $this->_config->get('autogenerate_toc');
        $data['display_related_to'] = $this->_config->get('display_related_to');
                        
        // Replace wikiwords
        $data['wikipage_view']['content'] = preg_replace_callback($this->_config->get('wikilink_regexp'), array($this->_page, 'replace_wikiwords'), $data['wikipage_view']['content']);
                
        midcom_show_style('view-wikipage-raw');
    }
    
    function _handler_subscribe($handler_id, $args, &$data)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
        {
            $_MIDCOM->generate_error(MIDCOM_ERRFORBIDDEN, 'Only POST requests are allowed here.');
        }
        
        $_MIDCOM->auth->require_valid_user();
        
        if (!$this->_load_page($args[0]))
        {
            return false;
        }
        
        $_MIDCOM->auth->request_sudo('net.nemein.wiki');
        
        $user = $_MIDCOM->auth->user->get_storage();
        
        if (   array_key_exists('target', $_POST)
            && $_POST['target'] == 'folder')
        {
            // We're subscribing to the whole wiki
            $object = $this->_topic;
            $target = sprintf($this->_request_data['l10n']->get('whole wiki %s'), $this->_topic->extra);
        }
        else
        {
            $object = $this->_page;
            $target = sprintf($this->_request_data['l10n']->get('page %s'), $this->_page->title);
        }
        
        if (array_key_exists('subscribe', $_POST))
        {
            // Subscribe to page
            $object->parameter('net.nemein.wiki:watch', $user->guid, time());
            $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('net.nemein.wiki'), sprintf($this->_request_data['l10n']->get('subscribed to changes in %s'), $target), 'ok');
        }
        else
        {
            // Remove subscription
            $object->parameter('net.nemein.wiki:watch', $user->guid, '');
            $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('net.nemein.wiki'), sprintf($this->_request_data['l10n']->get('unsubscribed from changes in %s'), $target), 'ok');
        }
        
        $_MIDCOM->auth->drop_sudo();
        
        // Redirect to editing
        if ($this->_page->name == 'index')
        {
            $_MIDCOM->relocate("");
        }
            $_MIDCOM->relocate("{$this->_page->name}/");        
        // This will exit
    }
    
    
    function _handler_whatlinks($handler_id, $args, &$data, $view_mode = true)
    {
        $this->_load_page($args[0]);
        if (!$this->_page)
        {
            return false;
        }
        
        $this->_load_datamanager();
        
        $this->_populate_toolbar();
        $this->_view_toolbar->hide_item("whatlinks/{$this->_page->name}/");
        
        $qb = net_nemein_wiki_link_dba::new_query_builder();
        $qb->add_constraint('topage', '=', $this->_page->title);
        $data['wikilinks'] = $qb->execute();
         
        return true;
    }
    
    function _show_whatlinks($handler_id, &$data)
    {
        $data['wikipage_view'] = $this->_controller->get_content_html();
        
        // Replace wikiwords
        $data['wikipage_view']['content'] = preg_replace_callback($this->_config->get('wikilink_regexp'), array($this->_page, 'replace_wikiwords'), $data['wikipage_view']['content']);
                
        midcom_show_style('view-wikipage-whatlinks');
    }
}
?>

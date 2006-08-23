<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Wiki Site interface class.
 * 
 * @package net.nemein.wiki
 */
class net_nemein_wiki_viewer extends midcom_baseclasses_components_request
{
    function net_nemein_wiki_viewer($topic, $config) 
    {
        parent::midcom_baseclasses_components_request($topic, $config);
        
        // Match /create/<wikipage>
        $this->_request_switch[] = array(
            'fixed_args' => 'create',
            'variable_args' => 1,
            'handler' => Array('net_nemein_wiki_handler_create', 'create'),
        ); 
        
        // Match /delete/<wikipage>
        $this->_request_switch[] = array(
            'fixed_args' => 'delete',
            'variable_args' => 1,
            'handler' => Array('net_nemein_wiki_handler_delete', 'delete'),
        ); 

        // Match /raw/<wikipage>
        $this->_request_switch[] = array(
            'fixed_args' => 'raw',
            'variable_args' => 1,
            'handler' => Array('net_nemein_wiki_handler_view', 'raw'),
        ); 
        
        // Match /create/<wikipage>/<related to node>/<related to object>
        $this->_request_switch[] = array(
            'fixed_args' => 'create',
            'variable_args' => 3,
            'handler' => Array('net_nemein_wiki_handler_create', 'create'),
        );         
        
        // Match /edit/<wikipage>
        $this->_request_switch[] = array(
            'fixed_args' => 'edit',
            'variable_args' => 1,
            'handler' => Array('net_nemein_wiki_handler_edit', 'edit'),
        ); 
        
        // Match /rss.xml        
        $this->_request_switch[] = array(
            'fixed_args' => 'rss.xml',
            'handler' => Array('net_nemein_wiki_handler_feed', 'rss'),
        );  
        
        // Match /latest        
        $this->_request_switch[] = array(
            'fixed_args' => 'latest',
            'handler' => Array('net_nemein_wiki_handler_latest', 'latest'),
        );       

        // Match /
        $this->_request_switch[] = array(
            'handler' => Array('net_nemein_wiki_handler_view', 'view'),
        );

        // Match /email_import
        $this->_request_switch[] = array(
            'fixed_args' => 'email_import',
            'handler' => Array('net_nemein_wiki_handler_emailimport', 'emailimport'),
        );

        // Match /<wikipage>
        $this->_request_switch[] = array(
            'variable_args' => 1,
            'handler' => Array('net_nemein_wiki_handler_view', 'view'),
        );
        
        $rcs_array = no_bergfald_rcs_handler::get_request_switch();
        foreach ($rcs_array as $key => $switch) 
        {
            $this->_request_switch[] = $switch;
        }

        //Add common relatedto request switches
        org_openpsa_relatedto_handler::common_request_switches($this->_request_switch, 'net.nemein.wiki');
        //If you need any custom switches add them here               
    }
    
    function _on_handle($handler_id, $args)
    {
        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
    
        // Add machine-readable RSS link    
        $_MIDCOM->add_link_head(
            array(
                'rel'   => 'alternate',
                'type'  => 'application/rss+xml',
                'title' => 'Latest changes RSS',
                'href'  => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX).'rss.xml',
            )
        );

        // Populate toolbars for RCS handlers
        if (   count($args)
            && $args[0] == 'net.nemein.wiki')
        {
            $wikipage = new net_nemein_wiki_wikipage($args[count($args)-1]);
            if ($wikipage)
            {
                $this->_view_toolbar->add_item(
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "{$wikipage->name}/",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('view'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                        MIDCOM_TOOLBAR_ENABLED => true,
                    )
                );    
            }
        }
        
        return true;
    }
    
}
?>

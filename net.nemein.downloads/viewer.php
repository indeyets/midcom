<?php
/**
 * @package net.nemein.downloads
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Download manager Site interface class.
 * 
 * @package net.nemein.downloads
 */
class net_nemein_downloads_viewer extends midcom_baseclasses_components_request
{
    function net_nemein_downloads_viewer($topic, $config) 
    {
        parent::midcom_baseclasses_components_request($topic, $config);       

        // Match /create/<schema>
        $this->_request_switch['create'] = array
        (
            'fixed_args' => 'create',
            'variable_args' => 1,
            'handler' => array
            (
                'net_nemein_downloads_handler_create', 
                'create'
            ),
        );
        
        // Match /edit/<downloadpage>
        $this->_request_switch['edit'] = array
        (
            'fixed_args' => 'edit',
            'variable_args' => 1,
            'handler' => array
            (
                'net_nemein_downloads_handler_admin', 
                'edit'
            ),
        );
        
        // Match /delete/<downloadpage>
        $this->_request_switch['delete'] = array
        (
            'fixed_args' => 'delete',
            'variable_args' => 1,
            'handler' => array
            (
                'net_nemein_downloads_handler_admin', 
                'delete'
            ),
        );

        if ($this->_config->get('current_release'))
        {
            // Match /
            $this->_request_switch['view_current'] = array
            (
                'handler' => array
                (
                    'net_nemein_downloads_handler_view', 
                    'view'
                ),
            );
        }
        else
        {
            // No "current release" set, show list
            $this->_request_switch['index'] = array
            (
                'handler' => array
                (
                    'net_nemein_downloads_handler_view', 
                    'index'
                ),
            );
        }
        
        // Match /config/
        $this->_request_switch['config'] = array
        (
            'handler' => array
            (
                'midcom_core_handler_configdm', 
                'configdm'
            ),
            'schemadb' => 'file:/net/nemein/downloads/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => array
            (
                'config'
            ),
        );

        // Match /<downloadpage>
        $this->_request_switch['view'] = array
        (
            'variable_args' => 1,
            'handler' => array
            (
                'net_nemein_downloads_handler_view', 
                'view'
            ),
        );        
    }
    
    function _on_handle($handler_id, $args)
    {
        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        if ($this->_topic->can_do('midgard:create'))
        {
            foreach (array_keys($this->_request_data['schemadb']) as $name)
            {
                $this->_node_toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "create/{$name}.html",
                        MIDCOM_TOOLBAR_LABEL => sprintf
                        (
                            $this->_l10n_midcom->get('create %s'),
                            $this->_l10n->get($this->_request_data['schemadb'][$name]->description)
                        ),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                        MIDCOM_TOOLBAR_ACCESSKEY => 'n',
                    )
                );
            }
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
        
        $this->_request_data['node'] =& $this->_topic;
        
        return true;
    }
    
    /**
     * Static method for listing GUIDs and names of releases
     */
    function list_releases()
    {
        $data =& $_MIDCOM->get_custom_context_data('request_data');
        $release_array = array();
        
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $data['node']->id);
        $qb->add_order('title', 'DESC');
        $releases = $qb->execute();
        foreach ($releases as $release)
        {
            $release_array[$release->guid] = $release->title;
        }
        return $release_array;
    }
}
?>
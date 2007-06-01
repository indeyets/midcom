<?php
/**
 * @package midgard.admin.sitewizard
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.32 2006/06/08 14:12:38 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * midgard.admin.sitewizard site template handling interface class.
 */
class midgard_admin_sitewizard_sitetemplate extends midcom_baseclasses_components_handler
{
    /**
     * Simple constructor, which only initializes the parent constructor.
     */
    function midgard_admin_sitewizard_sitetemplate()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Get the plugin handlers, which act alike with Request Switches of MidCOM
     * Baseclasses Components (midcom.baseclasses.components.request)
     *
     * @access public
     */
    function get_plugin_handlers()
    {
        return Array
        (
            'export_opml' => Array
            (
                'handler' => Array('midgard_admin_sitewizard_sitetemplate', 'export'),
                'fixed_args' => array('export', 'opml'),
            ),
        );
    }
    
    function _on_initialize()
    {
        $_MIDCOM->load_library('midcom.helper.xml');
    }
    
    function _load_nodes(&$parent_node)
    {
        $node = array();
        $node['name']          = $parent_node->name;
        $node['title']         = $parent_node->extra;
        $node['component']     = $parent_node->component;
        $node['style']         = $parent_node->style;
        $node['style_inherit'] = $parent_node->styleInherit;
        
        // Load all parameters in the component namespace to get local settings
        $node['settings']      = $parent_node->list_parameters($node['component']);
        
        $node['children'] = array();
        $children_qb = midcom_db_topic::new_query_builder();
        $children_qb->add_constraint('up', '=', $parent_node->id);
        $children_qb->add_order('score', 'DESC');
        $children = $children_qb->execute();
        $i = 0;
        foreach ($children as $child_node)
        {
            $i++;
            $node['children']["node_{$i}"] = $this->_load_nodes($child_node);
        }
        
        return $node;
    }
    
    function _load_snippets(&$snippetdir)
    {
        $snippets = array
        (
            'name'          => $snippetdir->name,
        );
        
        $snippets['snippets'] = array();
        $snippets_qb = midcom_baseclasses_database_snippet::new_query_builder();
        $snippets_qb->add_constraint('up', '=', $snippetdir->id);
        $snippets_qb->add_order('name', 'DESC');
        $child_snippets = $snippets_qb->execute();
        $i = 0;
        foreach ($child_snippets as $snippet)
        {
            $i++;
            $snippets['snippets']["snippetdir_{$i}"] = array
            (
                'name'   => $snippet->name,
                'code'   => $snippet->code,
                'doc'    => $snippet->doc,
                'author' => $snippet->author,
            );
        }
        
        $snippets['snippetdirs'] = array();
        $snippetdirs_qb = midcom_baseclasses_database_snippetdir::new_query_builder();
        $snippetdirs_qb->add_constraint('up', '=', $snippetdir->id);
        $snippetdirs_qb->add_order('name', 'DESC');
        $snippetdirs = $snippetdirs_qb->execute();
        $i = 0;
        foreach ($snippetdirs as $child_snippetdir)
        {
            $i++;
            $snippets['snippetdirs']["snippetdir_{$i}"] = $this->_load_snippets($child_snippetdir);
        }
        
        return $snippets;
    }
    
    function _generate_opml($nodes_array)
    {
        $opml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $opml .= "<opml version=\"2.0\"";
        $opml .= " xmlns:mgd=\"http://www.midgard-project.org/repligard/1.4\">\n";
        $opml .= "    <head>\n";
        // TODO: Be smart about this
        $opml .= "        <title>Midgard site template</title>\n";
        $opml .= "    </head>\n";
        $opml .= "    <body>\n";
        
        $opml .= $this->_node2opml($nodes_array, '        ');
        
        $opml .= "    </body>\n";
        $opml .= "</opml>\n";
        
        return $opml;
    }
    
    function _node2opml($node, $prefix)
    {
        $opml  = "{$prefix}<outline";
        $opml .= " text=\"{$node['title']}\"";
        $opml .= " type=\"folder\"";
        $opml .= " mgd:name=\"{$node['name']}\"";
        $opml .= " mgd:component=\"{$node['component']}\"";
        $opml .= " mgd:style=\"{$node['style']}\"";
        
        $inherit = 'false';
        if ($node['style_inherit'])
        {
        $inherit = 'true';
        }
        
        $opml .= " mgd:styleInherit=\"{$inherit}\"";
        $opml .= ">\n";
        
        if (count($node['children']) > 0)
        {
            foreach ($node['children'] as $child_node)
            {
                $opml .= $this->_node2opml($child_node, "{$prefix}    ");
            }
        }
        
        $opml .= "{$prefix}</outline>\n";
        
        return $opml;
    }
    
    /**
     * Loads requested object export
     *
     * @access private
     * @return boolean Indicating success
     */
    function _handler_export($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();
        
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->content_type("text/xml");
        $_MIDCOM->header("Content-type: text/xml; charset=UTF-8");

        $root_topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);

        $data['sitetemplate'] = array
        (
            'nodes' => $this->_load_nodes($root_topic),
        );

        /*
        // TODO: support $GLOBALS['midcom_config']['midcom_sgconfig_basedir']
        $snippetdirs_qb = midcom_baseclasses_database_snippetdir::new_query_builder();
        $snippetdirs_qb->add_constraint('up', '=', 0);
        $snippetdirs_qb->add_constraint('name', '=', 'sitegroup-config');
        $root_snippetdir = $snippetdirs_qb->execute();
        if (count($root_snippetdir) > 0)
        {
            $data['sitetemplate']['code'] = $this->_load_snippets($root_snippetdir[0]);
        }
        */

        switch ($handler_id)
        {
            case '____mfa-sitetemplate-export_opml':
                $data['sitetemplate_formatted'] = $this->_generate_opml($data['sitetemplate']['nodes']);
                break;
            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Unrecognized export format.');
                // This will exit.
        }

        return true;
    }

    /**
     * Output the object export in desired format
     *
     * @access private
     */
    function _show_export($handler_id, &$data)
    {
        echo $data['sitetemplate_formatted'];
    }
}
?>
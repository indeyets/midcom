<?php
/**
* @package midcom.helper.filesync
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * Export a site structure to the format used by Site Wizard
 *
 * @package midcom.helper.filesync
 */
class midcom_helper_filesync_exporter_structure extends midcom_helper_filesync_exporter
{
    var $structure_array = array();
    var $structure = '';

    function midcom_helper_filesync_exporter_structure()
    {
        parent::midcom_helper_filesync_exporter();
    }
    
    function read_node($node)
    {
        $node_array = array();
        $node_array['name'] = $node->name;
        $node_array['title'] = $node->extra;
        $node_array['component'] = $node->component;
        $node_array['style'] = $node->style;
        $node_array['style_inherit'] = $node->styleInherit;
        
        // Per-component specialities
        switch ($node->component)
        {
            case 'net.nehmer.static':
                $node_array['create_index'] = true;
        }
        
        // Get parameters
        $node_array['parameters'] = $node->list_parameters();
        
        // TODO: Implement ACL exporting
        $node_array['acl'] = array();
        
        // Recurse subnodes
        $node_array['nodes'] = array();
        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('up', '=', $node->id);
        $qb->add_constraint('component', '<>', '');
        $children = $qb->execute();
        foreach ($children as $child)
        {
            $node_array['nodes'][$child->name] = $this->read_node($child);
        }
        
        return $node_array;
    }
    
    function read_structure()
    {
        // Generate a safe name for the structure
        $host = new midcom_db_host($_MIDGARD['host']);
        $structure_name = midcom_generate_urlname_from_string($host->get_label());
        
        // Prepare structure
        $structure = array();
        $structure[$structure_name] = array();
        $structure[$structure_name]['name'] = $structure_name;
        $root_page = new midcom_db_page($host->root);
        $structure[$structure_name]['title'] = $root_page->title;
        $structure[$structure_name]['examples'] = array($_MIDCOM->get_page_prefix());
        // Read the topic data
        $root_node = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);
        $structure[$structure_name]['root'] = $this->read_node($root_node);
        
        return $structure;
    }
    
    function _draw_array($array, $prefix = '')
    {
        $data = '';
        foreach ($array as $key => $val)
        {
            switch(gettype($val))
            {
                case 'boolean':
                    $data .= ($val)?"{$prefix}'{$key}' => true,\n":"{$prefix}'{$key}' => false,\n";
                    break;
                case 'array':
                    $data .= "{$prefix}'{$key}' => array\n{$prefix}(\n" . $this->_draw_array($val, "{$prefix}    ") . "\n{$prefix}),\n";
                    break;

                default:
                    if (is_numeric($val))
                    {
                        $data .= "{$prefix}'{$key}' => {$val},\n";
                    }
                    else
                    {
                        $data .= "{$prefix}'{$key}' => '{$val}',\n";
                    }
            }

        }
        return $data;
    }
    
    function export()
    {
        echo "<pre>\n";
        echo $this->_draw_array($this->read_structure());
        echo "</pre>\n";
    }
}
?>
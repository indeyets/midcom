<?php
/**
* @package midcom.helper.filesync
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * Export a site style to the format used by Site Wizard
 *
 * @package midcom.helper.filesync
 */
class midcom_helper_filesync_exporter_style extends midcom_helper_filesync_exporter
{
    var $root_dir = '';
    
    function midcom_helper_filesync_exporter_style()
    {
        parent::midcom_helper_filesync_exporter();
        
        $this->root_dir = midcom_helper_filesync_interface::prepare_dir('style');
    }
    
    function read_style($style, $path)
    {
        $style_path = "{$path}{$style->name}";
        if (!file_exists($style_path))
        {
            mkdir($style_path);
        }
        
        $element_qb = midcom_db_element::new_query_builder();
        $element_qb->add_constraint('style', '=', $style->id);
        $elements = $element_qb->execute();
        foreach ($elements as $element)
        {
            // TODO: Multilang support?     
            file_put_contents("{$style_path}/{$element->name}.php", $element->value);
        }
        
        $style_qb = midcom_db_style::new_query_builder();
        $style_qb->add_constraint('up', '=', $style->id);
        $styles = $style_qb->execute();
        foreach ($styles as $style)
        {
            $this->read_style($style, "{$style_path}/");
        }
    }
    
    function read_root($style_id)
    {
        $style = new midcom_db_style($style_id);
        if (!$style->guid)
        {
            return null;
        }
        
        $this->read_style($style, $this->root_dir);
    }
 
    function export()
    {
        $this->read_root($_MIDGARD['style']);
    }
}
?>
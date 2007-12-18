<?php
/**
* @package midcom.helper.filesync
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * Export a site snippet to the format used by Site Wizard
 *
 * @package midcom.helper.filesync
 */
class midcom_helper_filesync_exporter_snippet extends midcom_helper_filesync_exporter
{
    var $root_dir = '';
    
    function midcom_helper_filesync_exporter_snippet()
    {
        parent::midcom_helper_filesync_exporter();
        
        $this->root_dir = midcom_helper_filesync_interface::prepare_dir('snippets');
    }
    
    function read_snippetdir($snippetdir, $path)
    {
        $snippetdir_path = "{$path}{$snippetdir->name}";
        if (!file_exists($snippetdir_path))
        {
            mkdir($snippetdir_path);
        }
        
        $snippet_qb = midcom_baseclasses_database_snippet::new_query_builder();
        $snippet_qb->add_constraint('up', '=', $snippetdir->id);
        $snippets = $snippet_qb->execute();
        foreach ($snippets as $snippet)
        {
            // TODO: Multilang support?     
            file_put_contents("{$snippetdir_path}/{$snippet->name}.php", $snippet->code);
            
            if (!empty($snippet->doc))
            {
                file_put_contents("{$snippetdir_path}/{$snippet->name}.txt", $snippet->doc);
            }
        }
        
        $dir_qb = midcom_baseclasses_database_snippetdir::new_query_builder();
        $dir_qb->add_constraint('up', '=', $snippetdir->id);
        $dirs = $dir_qb->execute();
        foreach ($dirs as $dir)
        {
            $this->read_snippetdir($dir, "{$snippetdir_path}/");
        }
    }
    
    function read_root($snippetpath)
    {
        $snippetdir = new midcom_baseclasses_database_snippetdir();
        $snippetdir->get_by_path($snippetpath);
        if (!$snippetdir->guid)
        {
            return null;
        }
        
        $this->read_snippetdir($snippetdir, $this->root_dir);
    }
 
    function export()
    {
        $this->read_root($GLOBALS['midcom_config']['midcom_sgconfig_basedir']);
    }
}
?>
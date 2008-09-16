<?php
/**
* @package midcom.helper.filesync
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * Import a site snippet from the format used by Site Wizard
 *
 * @package midcom.helper.filesync
 */
class midcom_helper_filesync_importer_snippet extends midcom_helper_filesync_importer
{
    var $root_dir = '';

    function __construct()
    {
        parent::__construct();

        $this->root_dir = midcom_helper_filesync_interface::prepare_dir('snippets');
    }

    function read_snippetdir($path, $parent_id)
    {
        $snippetdir_name = basename($path);

        $object_qb = midcom_baseclasses_database_snippetdir::new_query_builder();
        $object_qb->add_constraint('up', '=', $parent_id);
        $object_qb->add_constraint('name', '=', $snippetdir_name);
        if ($object_qb->count() == 0)
        {
            // New snippet
            $snippetdir = new midcom_baseclasses_database_snippetdir();
            $snippetdir->up = $parent_id;
            $snippetdir->name = $snippetdir_name;
            if (!$snippetdir->create())
            {
                return false;
            }
        }
        else
        {
            $snippetdirs = $object_qb->execute();
            $snippetdir = $snippetdirs[0];
        }

        $directory = dir($path);
        while (false !== ($entry = $directory->read()))
        {
            if (substr($entry, 0, 1) == '.')
            {
                // Ignore dotfiles
                continue;
            }

            if (is_dir("{$path}/{$entry}"))
            {
                // Recurse deeper
                $this->read_snippetdir("{$path}/{$entry}", $snippetdir->id);
            }

            // Check file type
            $filename_parts = explode('.', $entry);
            if (count($filename_parts) < 2)
            {
                continue;
            }
            $snippet_name = $filename_parts[0];
            $field = false;
            switch($filename_parts[count($filename_parts) - 1])
            {
                case 'php':
                    $field = 'code';
                    break;
                case 'txt':
                    $field = 'doc';
                    break;
            }
            if (!$field)
            {
                continue;
            }

            // Deal with element
            $file_contents = file_get_contents("{$path}/{$entry}");
            $encoding = mb_detect_encoding($file_contents);
            if ($encoding != 'UTF-8')
            {
                $file_contents = @iconv($encoding, 'UTF-8', $file_contents);
            }

            $qb = midcom_baseclasses_database_snippet::new_query_builder();
            $qb->add_constraint('up', '=', $snippetdir->id);
            $qb->add_constraint('name', '=', $snippet_name);
            if ($qb->count() == 0)
            {
                // New element
                $snippet = new midcom_baseclasses_database_snippet();
                $snippet->up = $snippetdir->id;
                $snippet->name = $snippet_name;
                $snippet->$field = $file_contents;
                $snippet->create();
                continue;
            }

            $snippets = $qb->execute();
            $snippet = $snippets[0];

            // Update existing elements only if they have actually changed
            if ($snippet->$field != $file_contents)
            {
                $snippet->$field = $file_contents;
                $snippet->update();
            }
        }
        $directory->close();
    }

    function read_dirs($path)
    {
        $directory = dir($path);
        while (false !== ($entry = $directory->read()))
        {
            if (substr($entry, 0, 1) == '.')
            {
                // Ignore dotfiles
                continue;
            }

            if (is_dir("{$path}/{$entry}"))
            {
                // Recurse deeper
                $this->read_snippetdir("{$path}/{$entry}", 0);
            }
        }
        $directory->close();
    }

    function import()
    {
        $this->read_dirs($this->root_dir);
    }
}
?>
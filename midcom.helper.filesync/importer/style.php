<?php
/**
* @package midcom.helper.filesync
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * Import a site style from the format used by Site Wizard
 *
 * @package midcom.helper.filesync
 */
class midcom_helper_filesync_importer_style extends midcom_helper_filesync_importer
{
    var $root_dir = '';

    function midcom_helper_filesync_importer_style()
    {
        parent::__construct();

        $this->root_dir = midcom_helper_filesync_interface::prepare_dir('style');
    }

    function read_style($path, $parent_id)
    {
        $style_name = basename($path);

        $object_qb = midcom_db_style::new_query_builder();
        $object_qb->add_constraint('up', '=', $parent_id);
        $object_qb->add_constraint('name', '=', $style_name);
        if ($object_qb->count() == 0)
        {
            // New style
            $style = new midcom_db_style();
            $style->up = $parent_id;
            $style->name = $style_name;
            if (!$style->create())
            {
                return false;
            }
        }
        else
        {
            $styles = $object_qb->execute();
            $style = $styles[0];
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
                $this->read_style("{$path}/{$entry}", $style->id);
            }

            // Deal with element

            // Check file type
            $filename_parts = explode('.', $entry);
            if (count($filename_parts) < 2)
            {
                continue;
            }
            $element_name = $filename_parts[0];
            $field = false;
            switch($filename_parts[count($filename_parts) - 1])
            {
                case 'php':
                    $field = 'value';
                    break;
            }
            if (!$field)
            {
                continue;
            }

            $file_contents = file_get_contents("{$path}/{$entry}");
            $encoding = mb_detect_encoding($file_contents);
            if ($encoding != 'UTF-8')
            {
                $file_contents = @iconv($encoding, 'UTF-8', $file_contents);
            }

            $qb = midcom_db_element::new_query_builder();
            $qb->add_constraint('style', '=', $style->id);
            $qb->add_constraint('name', '=', $element_name);
            if ($qb->count() == 0)
            {
                // New element
                $element = new midcom_db_element();
                $element->style = $style->id;
                $element->name = $element_name;
                $element->$field = $file_contents;
                $element->create();
                continue;
            }

            $elements = $qb->execute();
            $element = $elements[0];

            // Update existing elements only if they have actually changed
            if ($element->$field != $file_contents)
            {
                $element->$field = $file_contents;
                $element->update();
            }
        }
        $directory->close();
    }

    function read_styledir($path)
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
                $this->read_style("{$path}/{$entry}", 0);
            }
        }
        $directory->close();
    }

    function import()
    {
        $this->read_styledir($this->root_dir);
    }
}
?>
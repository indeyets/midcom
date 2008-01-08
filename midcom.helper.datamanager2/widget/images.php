<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 images widget
 *
 * As with all subclasses, the actual initialization is done in the initialize() function,
 * not in the constructor, to allow for error handling.
 *
 * This widget supports the images type or any subtype thereof.
 *
 * All processing is done during the on_submit handlers, enforcing immediate update of the
 * associated storage objects. No other solution is possible, since we need to transfer
 * uploaded files somehow through multiple requests.
 *
 * The type will show a tabular view of all uploaded images. Existing images have
 * an editable tile and can be deleted or replaced. A single new upload line is displayed
 * always. There is no preview, but there is a download link.
 *
 * <b>Available configuration options:</b>
 *
 * - set_name_and_title_on_upload use this if you want the user to be able to set the 
 *   filename and title when uploading a form. 
 * - integer max_count Maximum number of images allowed for a field. Set this 
 *
 * <b>Implementation notes:</b>
 *
 * The construction of the widget is relatively complex, it relies on a combination of
 * static and input elements to do its work. It should be fairly customizable using CSS.
 *
 * 1. Quickform Element Naming
 *
 * All elements will be added in a group using the groupname[elementname] Feature of QF.
 * Static elements are all prefixed s_, f.x. s_header. The actual elements use an e_, f.x.
 * e_new_title. All elements in the new upload row append a new_ to this prefix as seen
 * in the last example. Finally, elements referencing existing attachments append an
 * exist_{$identifier}_ to the prefix, f.x. e_exist_{$identifier}_title.
 *
 * 2. CSS names
 *
 * The table gets the Name of the field as id and midcom_helper_datamanager2_widget_images
 * as class. Each column also gets its own CSS class: filename, title, file, upload and delete.
 * An additional class is assigned depending whether this is a row for an existing item (exist) or
 * a new one (new). So a full class for the new filename element would be "new filename". Note,
 * that the classes are assigned to both the td and input elements. The th elements do not have
 * any additional class
 *
 * 3. Image identifiers
 *
 * The auto-generated image identifiers from the images base type are used.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_widget_images extends midcom_helper_datamanager2_widget
{
    /**
     * The group of elements forming our widget.
     *
     * @access private
     * @var HTML_QuickForm_Group
     */
    var $_group = null;

    /**
     * The list of elements added to the widget, indexed by their element name.
     *
     * @var Array
     * @access private
     */
    var $_elements = null;

    /**
     * Should the user be able to set the filename and title on upload?
     * If so , set this to true. 
     * @var boolean
     */
    var $set_name_and_title_on_upload = true;

    /**
     * Maximum amount of images allowed to be stored in the same field
     * 
     * @access public
     * @var integer
     */
    var $max_count = 0;

    /**
     * The initialization event handler post-processes the maxlength setting.
     *
     * @return bool Indicating Success
     */
    function _on_initialize()
    {
        if (! is_a($this->_type, 'midcom_helper_datamanager2_type_images'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} is not an images type or subclass thereof, you cannot use the images widget with it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        // Reflect the type config setting for maximum count
        if (   isset($this->_type->max_count)
            && !$this->max_count)
        {
            $this->max_count = $this->_type->max_count;
        }

        $_MIDCOM->add_jscript($this->_get_filename_validation_script());

        return true;
    }
    
    function _get_filename_validation_script()
    {
        return <<<END
function midcom_helper_dm2_widget_images_check(evt, id) {
    evt = (evt) ? evt: ( (window.event) ? event : null);
    var obj,reg, msg;
    if (evt) {
        reg = /\.(png|gif|jpe?g|tiff?)$/;
        obj = (evt.target) ? evt.target : evt.srcElement;
        if (!obj.value.match(reg)) {
            obj.style.color = "red";
            msg = document.getElementById(id);
            msg.style.display = "block";
        }
    }
}
    
END;
    }

    /**
     * Adds the table header to the widget.
     *
     * @param bool $frozen Set this to true, if you want to skip all elements which cannot be frozen.
     * @access private
     */
    function _add_table_header($frozen)
    {
        if ($frozen)
        {
            $html = "<table class='midcom_helper_datamanager2_widget_images' id='{$this->_namespace}{$this->name}' >\n" .
                    "<tr>\n" .
                    "<th class='filename'>" . $this->_l10n_midcom->get('name') . "</th>\n" .
                    "<th class='title'>" . $this->_l10n_midcom->get('title') . "</th>\n" .
                    "</tr>\n";
        }
        else
        {
            $html = "<table class='midcom_helper_datamanager2_widget_images' id='{$this->_namespace}{$this->name}' >\n" .
                    "<tr>\n" .
                    "<th class='filename'>" . $this->_l10n_midcom->get('name') . "</th>\n" .
                    "<th class='title'>" . $this->_l10n_midcom->get('title') . "</th>\n" .
                    "<th class='upload'>" . $this->_l10n_midcom->get('upload') . "</th>\n" .
                    "</tr>\n";
        }
        $this->_elements['s_header'] =& HTML_QuickForm::createElement('static', 's_header', '', $html);
    }

    /**
     * Adds the new upload row to the bottom of the table.
     *
     * @param bool $frozen Set this to true, if you want to skip all elements which cannot be frozen.
     * @access private
     */
    function _add_new_upload_row_old($frozen)
    {
        // Show only a configured amount of new image rows
        if (   $this->max_count
            && count($this->_type->images) >= $this->max_count)
        {
            return;
        }
        
        // Filename column
        $html = "<tr >\n" .
                "<td class='new filename'>";
        $this->_elements['s_new_filename'] =& HTML_QuickForm::createElement('static', 's_new_filename', '', $html);
        $attributes = Array
        (
            'class' => 'new filename',
            'id'    => "{$this->_namespace}{$this->name}_e_new_filename",
        );
        $this->_elements['e_new_filename'] =& HTML_QuickForm::createElement('text', 'e_new_filename', '', $attributes);

        // Title Column
        $html = "</td>\n" .
                "<td class='new title'>";
        $this->_elements['s_new_title'] =& HTML_QuickForm::createElement('static', 's_new_title', '', $html);
        $attributes = Array
        (
            'class' => 'new title',
            'id'    => "{$this->_namespace}{$this->name}_e_new_title",
        );
        $this->_elements['e_new_title'] =& HTML_QuickForm::createElement('text', 'e_new_title', '', $attributes);

        if (! $frozen)
        {
            // Controls Column
            $html = "</td>\n" .
                    "<td class='new upload'>";
            $this->_elements['s_new_upload'] =& HTML_QuickForm::createElement('static', 's_new_upload', '', $html);
            $attributes = Array
            (
                'class' => 'new file',
                'id'    => "{$this->_namespace}{$this->name}_e_new_file",
            );
            $this->_elements['e_new_file'] =& HTML_QuickForm::createElement('file', 'e_new_file', '', $attributes);
            $attributes = Array
            (
                'class' => 'new upload',
                'id'    => "{$this->_namespace}{$this->name}_e_new_upload",
            );
            
            $this->_elements['e_new_upload'] =& HTML_QuickForm::createElement('submit', "{$this->name}_e_new_upload", $this->_l10n->get('upload file'), $attributes);
        }

        $html = "</td>\n" .
                "</tr>\n";
        $this->_elements['s_new_file'] =& HTML_QuickForm::createElement('static', 's_new_file', '', $html);
    }
    /**
     * Adds the new upload row to the bottom of the table.
     *
     * @param bool $frozen Set this to true, if you want to skip all elements which cannot be frozen.
     * @access private
     */
    function _add_new_upload_row($frozen)
    {
        // Show only a configured amount of new image rows
        if (   $this->max_count
            && count($this->_type->images) >= $this->max_count)
        {
            return;
        }
        
        // Filename column
        $html = "<tr >\n" .
                "<td class='new text' colspan='1'>";
        $html .= sprintf("%s:", $this->_l10n->get('Add new file'));       
        $this->_elements['s_new_filename'] =& HTML_QuickForm::createElement('static', 's_new_filename', '', $html);
        
        if (! $frozen)
        {
            // Controls Column
            $html = "</td><td class='new upload' colspan='2'>";
            $this->_elements['s_new_upload'] =& HTML_QuickForm::createElement('static', 's_new_upload', '', $html);
            $attributes = Array
            (
                'class' => 'new file',
                'id'    => "{$this->_namespace}{$this->name}_e_new_file",
            );
            $this->_elements['e_new_file'] =& HTML_QuickForm::createElement('file', 'e_new_file', '', $attributes);
            $attributes = Array
            (
                'class' => 'new upload',
                'id'    => "{$this->_namespace}{$this->name}_e_new_upload",
            );
            $this->_elements['e_new_upload'] =& HTML_QuickForm::createElement('submit', "{$this->name}_e_new_upload", $this->_l10n->get('upload file'), $attributes);
        }

        $html = "</td>\n" .
                "</tr>\n";
        $this->_elements['s_new_file'] =& HTML_QuickForm::createElement('static', 's_new_file', '', $html);
    }


    /**
     * Adds a row for an existing image
     *
     * @param string $identifier The identifier of the image to add.
     * @param bool $frozen Set this to true, if you want to skip all elements which cannot be frozen.
     * @access private
     */
    function _add_image_row($identifier, $frozen)
    {
        if (isset($this->_type->images[$identifier]['main']))
        {
            $info = $this->_type->images[$identifier]['main'];
        }
        else
        {
            foreach($this->_type->images[$identifier] as $key => $data)
            {
                $info = $data;
                break;
            }
        }
        if (!isset($info))
        {
            // Panic, what to do ??
            return;
        }
        if (   !isset($info['object'])
            || !is_object($info['object'])
            || !isset($info['object']->guid)
            || empty($info['object']->guid))
        {
            //Panic, broken identifier
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Identifier '{$identifier}' does not have a valid object behind it",  MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }

        // Get preview image source
        if (array_key_exists('thumbnail', $this->_type->images[$identifier]))
        {
            $url = $this->_type->images[$identifier]['thumbnail']['url'];
            $sizeline = $this->_type->images[$identifier]['thumbnail']['size_line'];
            $preview = "<a href='{$info['url']}' class='download'><img src='{$url}' {$sizeline} /></a>";
        }
        else
        {
            $url = $info['url'];
            $x = $info['size_x'];
            $y = $info['size_y'];

            // Downscale Preview image to max 75px, rotect against broken images:
            if (   $x != 0
                && $y != 0)
            {
                $aspect = $x/$y;
                if ($x > 75)
                {
                    $x = 75;
                    $y = round($x / $aspect);
                }
                if ($y > 75)
                {
                    $y = 75;
                    $x = round($y * $aspect);
                }
            }

            $size_line = "width='{$x}' height='{$y}'";
            $preview = "<a href='{$url}' class='download'><img src='{$url}' {$size_line} /></a>";
        }


        // Filename column
        $html = "<tr title='{$info['guid']}' class='midcom_helper_datamanager2_widget_images_image'>\n" .
                "<td class='exist filename' title='{$info['filename']}'>" .
                "{$preview}<br /><a href='{$info['url']}'>{$info['filename']}</a>" .
                "</td>\n";
        $this->_elements["s_exist_{$identifier}_filename"] =& HTML_QuickForm::createElement('static', "s_exist_{$identifier}_filename", '', $html);

        // Title Column, set the value explicitly, as we are sometimes called after the defaults kick in.
        $html = "<td class='exist title' title='{$info['description']}'>";
        $this->_elements["s_exist_{$identifier}_title"] =& HTML_QuickForm::createElement('static', "s_exist_{$identifier}_title", '', $html);
        $attributes = Array
        (
            'class' => 'exist title',
            'id'    => "{$this->_namespace}{$this->name}_e_exist_{$identifier}_title",
        );
        $this->_elements["e_exist_{$identifier}_title"] =& HTML_QuickForm::createElement('text', "e_exist_{$identifier}_title", '', $attributes);
        $this->_elements["e_exist_{$identifier}_title"]->setValue($info['description']);

        if (! $frozen)
        {
            // Controls Column
            $html = "</td>\n" .
                    "<td class='exist upload'>";
            $this->_elements["s_exist_{$identifier}_upload"] =& HTML_QuickForm::createElement('static', "s_exist_{$identifier}_upload", '', $html);
            $attributes = Array
            (
                'class' => 'exist file',
                'id'    => "{$this->_namespace}{$this->name}_e_exist_{$identifier}_file",
                'onchange' => "midcom_helper_dm2_widget_images_check(event, 'e_exist_{$identifier}_delete')",
            );
            $this->_elements["e_exist_{$identifier}_file"] =& HTML_QuickForm::createElement('file', "e_exist_{$identifier}_file", '', $attributes);
            $this->_elements["s_exist_{$identifier}_br"] =& HTML_QuickForm::createElement('static', "s_exist_{$identifier}_upload", '', "<br/>");
            $attributes = Array
            (
                'class' => 'exist upload',
                'id'    => "{$this->_namespace}{$this->name}_e_exist_{$identifier}_upload",
            );
            $this->_elements["e_exist_{$identifier}_upload"] =& HTML_QuickForm::createElement('submit', "{$this->name}_e_exist_{$identifier}_upload", $this->_l10n->get('replace file'), $attributes);
                        
            $attributes = Array
            (
                'class' => 'exist delete',
                'id'    => "{$this->_namespace}{$this->name}_e_exist_{$identifier}_delete",
            );
            $this->_elements["e_exist_{$identifier}_delete"] =& HTML_QuickForm::createElement('submit', "{$this->name}_e_exist_{$identifier}_delete", $this->_l10n->get('delete file'), $attributes);
            $html = sprintf("<span id='e_exist_{$identifier}_delete' style='display:none;color:red'>%s</span>",
                            $this->_l10n_midcom->get('You can only upload images here. This file will not be saved.')
                            );
            $this->_elements["s_exist_{$identifier}_error"] =& HTML_QuickForm::createElement('static', "s_exist_{$identifier}_upload", '', $html);
        }

        $html = "</td>\n" .
                "</tr>\n";
        $this->_elements["s_exist_{$identifier}_file"] =& HTML_QuickForm::createElement('static', "s_exist_{$identifier}_file", '', $html);
    }

    /**
     * Adds the table footer.
     *
     * @param bool $frozen Set this to true, if you want to skip all elements which cannot be frozen.
     * @access private
     */
    function _add_table_footer($frozen)
    {
        $html = "</table>";
        $this->_elements['s_footer'] =& HTML_QuickForm::createElement('static', 's_footer', '', $html);
    }

    /**
     * Constructs the upload list.
     */
    function add_elements_to_form()
    {
        $this->_compute_elements();
        $this->_group =& $this->_form->addGroup($this->_elements, $this->name, $this->_translate($this->_field['title']), "\n");
    }

    /**
     * Computes the element list to form the widget. It populates the _elements memeber, which is
     * initialized with a new, empty array during startup.
     *
     * @param bool $frozen Set this to true, if you want to skip all elements which cannot be frozen.
     * @access private
     */
    function _compute_elements($frozen = false)
    {
        $this->_elements = Array();

        $this->_add_table_header($frozen);

        foreach ($this->_type->images as $identifier => $images)
        {
            $this->_add_image_row($identifier, $frozen);
        }
        if ($this->set_name_and_title_on_upload) 
        {
            $this->_add_new_upload_row_old($frozen);
        }
        else
        {
            $this->_add_new_upload_row($frozen);
        }
        $this->_add_table_footer($frozen);
    }

    /**
     * Checks whether a new file has been uploaded. If yes, it is processed.
     *
     * @param Array $values The values associated with our element group (not the full submit value list).
     * @access private
     */
    function _check_new_upload($values)
    {
        if (! array_key_exists('e_new_file', $this->_elements))
        {
            // We are frozen, no upload can happen, so we exit immediately.
            return;
        }

        if ($this->_elements['e_new_file']->isUploadedFile())
        {
            $file = $this->_elements['e_new_file']->getValue();

            if ( preg_match('/\.(zip|tar(\.gz|\.bz2)?|tgz)$/', strtolower($file['name']), $extension_matches))
            {
                // PHP5-TODO: This must be copy-by-value
                $copy = $file;
                unset($file);
                if (! $this->_type->_batch_handler($extension_matches[1], $copy))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to add attachments from compressed files to the field '{$this->name}'. Ignoring silently.", MIDCOM_LOG_WARN);
                    debug_pop();
                }
                return;
            }

            
            if (   array_key_exists('e_new_title', $values)
                && !empty($values['e_new_title']))
            {
                $title = $values['e_new_title'];
            }
            else
            {
                $title = $file['name'];
            }
            
            if (   array_key_exists('e_new_filename', $values)
                && !empty($values['e_new_filename']))
            {
                $filename = $values['e_new_filename'];
            }
            else
            {
                $filename = $file['name'];
            }

            if (! $this->_type->add_image($filename, $file['tmp_name'], $title))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to add an attachment to the field '{$this->name}'. Ignoring silently.", MIDCOM_LOG_WARN);
                debug_pop();
            }
        }
    }

    /**
     * The following checks are made, in order:
     *
     * 1. If the delete button was clicked, the image is dropped.
     * 2. If a new file has been uploaded, it replaces the current one.
     * 3. If neither of the above is triggered, the title of the image is synchronized.
     *
     * Calls for images which are not listed in the form, will be silently ignored.
     * This may happen, for example, if two users edit the same object simultaneoulsy,
     * or during addition of new elements.
     *
     * @param string $identifier The attachment identifier to check for updates.
     * @param Array $values The values associated with our element group (not the full submit value list).
     * @access private
     */
    function _check_for_update($identifier, $values)
    {
        if (! array_key_exists($identifier, $this->_type->images))
        {
            // The image does no longer exist
            return;
        }

        if (array_key_exists("{$this->name}_e_exist_{$identifier}_delete", $values))
        {
            if (! $this->_type->delete_image($identifier))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to delete the image {$identifier} on the field '{$this->name}'. Ignoring silently.", MIDCOM_LOG_WARN);
                debug_pop();
            }
        }
        else if
        (   array_key_exists("e_exist_{$identifier}_file", $this->_elements)
                 && $this->_elements["e_exist_{$identifier}_file"]->isUploadedFile())
        {
            $file = $this->_elements["e_exist_{$identifier}_file"]->getValue();
            $title = $values["e_exist_{$identifier}_title"];
            $filename = $this->_type->images[$identifier]['main']['filename'];

            if (! $title)
            {
                $title = $filename;
            }

            if (! $this->_type->update_image($identifier, $filename, $file['tmp_name'], $title))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to update the image {$identifier} on the field '{$this->name}'. Ignoring silently.", MIDCOM_LOG_WARN);
                debug_pop();
            }
        }
        else if
        (   array_key_exists("e_exist_{$identifier}_title", $values)
                 && isset($this->_type->images[$identifier]['main'])
                 && isset($this->_type->images[$identifier]['main']['description'])
                 && $values["e_exist_{$identifier}_title"] != $this->_type->images[$identifier]['main']['description'])
        {
            $this->_type->update_image_title($identifier, $values["e_exist_{$identifier}_title"]);
        }
    }

    /**
     * The on_submit event handles all operations immediately. This includes title updates (all
     * are done regardless of actual updates).
     */
    function on_submit($results)
    {
        parent::on_submit($results);

        if (! array_key_exists($this->name, $results))
        {
            return;
        }

        $values = $results[$this->name];

        $this->_check_new_upload($values);

        foreach (array_keys($this->_type->images) as $identifier)
        {
            $this->_check_for_update($identifier, $values);
        }

        // Rebuild Widget
        $this->_compute_elements();
        $this->_group->setElements($this->_elements);
    }

    /**
     * Freeze the entire group, special handling applies to skipp all elements which cannot be
     * frozen.
     */
    function freeze()
    {
        // Rebuild Widget
        $this->_compute_elements(true);
        $this->_group->setElements($this->_elements);
    }

    /**
     * Unfreeze the entire group, special handling applies, the formgroup is replaced by a the
     * full input widget set.
     */
    function unfreeze()
    {
        // Rebuild Widget
        $this->_compute_elements(false);
        $this->_group->setElements($this->_elements);
    }

    /**
     * Nothing to do here.
     */
    function sync_type_with_widget($results) {}

    /**
     * Populates the title fields with their defaults.
     */
    function get_default()
    {
        $defaults = Array();
        foreach($this->_type->images as $identifier => $images)
        {
            if (   isset($images['main'])
                && isset($images['main']['description']))
            {
                $defaults["e_exist_{$identifier}_title"] = $images['main']['description'];
            }
            else
            {
                $defaults["e_exist_{$identifier}_title"] = '';
            }
        }
        return Array ($this->name => $defaults);
    }
}
?>
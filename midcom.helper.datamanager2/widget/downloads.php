<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 download widget
 *
 * As with all subclasses, the actual initialization is done in the initialize() function,
 * not in the constructor, to allow for error handling.
 *
 * This widget supports the blobs type or any subtype thereof.
 *
 * All processing is done during the on_submit handlers, enforcing immediate update of the
 * associated storage objects. No other solition is possible, since we need to transfer
 * uploaded files somehow through multiple requests.
 *
 * Note, that this widget (as opposed to the image & co widgets) uses the blobs base type
 * directly and thus has no post-processing capabilities whatsoever.
 *
 * The type will show a tabluar view of all uploaded attachments. Existing attachments have
 * an editable tile and can be deleted or replaced. A single new upload line is displayed
 * always. There is no preview, but there is a download link.
 *
 * <b>Available configuration options:</b>
 *
 * - none
 *
 * <b>Implementation notes:</b>
 *
 * The construnciton of the widget is relatively complex, it relies on a combination of
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
 * The table gets the Name of the field as id and midcom_helper_datamanager2_widget_downloads
 * as class. Each column also gets its own CSS class: filename, title, file, upload and delete.
 * An additionalclass is assigned depending whether this is a row for an existing item (exist) or
 * a new one (new). So a full class for the new filename element would be "new filename". Note,
 * that the classes are assigned to both the td and input elements. The th elements do not have
 * any additional class
 *
 * 3. Attachment identifiers
 *
 * Attachments are identified using an MD5 hash constructed from original upload time, uploaded
 * file name and the temporary file name used during upload. Before adding the actual attachments,
 * they are ordered by filename.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_widget_downloads extends midcom_helper_datamanager2_widget
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
     * The initialization event handler post-processes the maxlength setting.
     *
     * @return bool Indicating Success
     */
    function _on_initialize()
    {
        if (! is_a($this->_type, 'midcom_helper_datamanager2_type_blobs'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} is not a blobs type or subclass thereof, you cannot use the downloads widget with it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        return true;
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
            $html = "<table class='midcom_helper_datamanager2_widget_downloads' id='{$this->_namespace}{$this->name}' >\n" .
                    "<tr>\n" .
                    "<th class='filename'>" . $this->_l10n_midcom->get('name') . "</th>\n" .
                    "<th class='title'>" . $this->_l10n_midcom->get('title') . "</th>\n" .
                    "</tr>\n";
        }
        else
        {
            $html = "<table class='midcom_helper_datamanager2_widget_downloads' id='{$this->_namespace}{$this->name}' >\n" .
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
    function _add_new_upload_row($frozen)
    {
        // Filename column
        $html = "<tr>\n" .
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
     * Adds a row for an existing attachment
     *
     * @param bool $frozen Set this to true, if you want to skip all elements which cannot be frozen.
     * @access private
     */
    function _add_attachment_row($identifier, $info, $frozen)
    {
        // Filename column
        $html = "<tr class='midcom_helper_datamanager2_widget_downloads_download' title='{$info['guid']}'>\n" .
                "<td class='exist filename' title='{$info['filename']}'>" .
                "<a href='{$info['url']}'>{$info['filename']}</a>" .
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
            );
            $this->_elements["e_exist_{$identifier}_file"] =& HTML_QuickForm::createElement('file', "e_exist_{$identifier}_file", '', $attributes);
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

        foreach ($this->_type->attachments_info as $identifier => $info)
        {
            $this->_add_attachment_row($identifier, $info, $frozen);
        }
        $this->_add_new_upload_row($frozen);
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
            $title = $values['e_new_title'];
            $filename = $values['e_new_filename'];

            if (! $filename)
            {
                $filename = $file['name'];
            }
            if (! $title)
            {
                $title = $filename;
            }

            $identifier = md5(time() . $filename . $file['tmp_name']);

            if (! $this->_type->add_attachment($identifier, $filename, $title, $file['type'], $file['tmp_name']))
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
     * 1. If the delete button was clicked, the attachment is dropped.
     * 2. If a new file has been uploaded, it replaces the current one.
     * 3. If neither of the above is triggered, the title of the attachment is
     *    synchronized.
     *
     * Calls for attachments which are not listed in the form, will be silently ignored.
     * This may happen, for example, if two users edit the same object simultaneoulsy,
     * or during addition of new elements.
     *
     * @param string $identifier The attachment identifier to check for updates.
     * @param Array $values The values associated with our element group (not the full submit value list).
     * @access private
     */
    function _check_for_update($identifier, $values)
    {
        if (! array_key_exists($identifier, $this->_type->attachments_info))
        {
            // The attachment does no longer exist
            return;
        }

        if (array_key_exists("{$this->name}_e_exist_{$identifier}_delete", $values))
        {
            if (! $this->_type->delete_attachment($identifier))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to delete the attachment {$identifier} on the field '{$this->name}'. Ignoring silently.", MIDCOM_LOG_WARN);
                debug_pop();
            }
        }
        else if (   array_key_exists("e_exist_{$identifier}_file", $this->_elements)
                 && $this->_elements["e_exist_{$identifier}_file"]->isUploadedFile())
        {
            $file = $this->_elements["e_exist_{$identifier}_file"]->getValue();
            $title = $values["e_exist_{$identifier}_title"];
            $filename = $this->_type->attachments_info[$identifier]['filename'];

            if (! $title)
            {
                $title = $filename;
            }

            if (! $this->_type->update_attachment($identifier, $filename, $title, $file['type'], $file['tmp_name']))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to update the attachment {$identifier} on the field '{$this->name}'. Ignoring silently.", MIDCOM_LOG_WARN);
                debug_pop();
            }
        }
        else if (   array_key_exists("e_exist_{$identifier}_title", $values)
                 && $values["e_exist_{$identifier}_title"] != $this->_type->attachments_info[$identifier]['description'])
        {
            $this->_type->update_attachment_title($identifier, $values["e_exist_{$identifier}_title"]);
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

        foreach (array_keys($this->_type->attachments_info) as $identifier)
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
        foreach($this->_type->attachments_info as $identifier => $info)
        {
            $defaults["e_exist_{$identifier}_title"] = $info['description'];
        }
        return Array ($this->name => $defaults);
    }
}

?>

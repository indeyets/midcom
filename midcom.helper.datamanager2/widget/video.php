<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: image.php 10973 2007-06-16 07:36:53Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 simple image widget
 *
 * As with all subclasses, the actual initialization is done in the initialize() function,
 * not in the constructor, to allow for error handling.
 *
 * This widget supports the image type or any subtype thereof.
 *
 * All processing is done during the on_submit handlers, enforcing immediate update of the
 * associated storage objects. No other solution is possible, since we need to transfer
 * uploaded files somehow through multiple requests.
 *
 * <b>Available configuration options:</b>
 *
 * - <i>bool show_title:</i> This flag controls whether the title field is shown or not.
 *   If this is flag, the whole title processing will be disabled. This flag is true
 *   by default.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_widget_video extends midcom_helper_datamanager2_widget
{

    /**
     * The QF upload form element, used for processing.
     *
     * @var HTML_QuickForm_file
     * @access private
     */
    var $_upload_element = null;
    var $_upload_element_video = null;

    /**
     * Controls title processing.
     *
     * @var bool
     */
    var $show_title = true;

    /**
     * The initialization event handler post-processes the maxlength setting.
     *
     * @return bool Indicating Success
     */
    function _on_initialize()
    {
        if (! is_a($this->_type, 'midcom_helper_datamanager2_type_video'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} is not an image type or subclass thereof, you cannot use the image widget with it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        return true;
    }

    /**
     * Adds a simple single-line text form element at this time.
     */
    function add_elements_to_form()
    {
        $attributes = Array
        (
            'class' => 'fileselector',
            'id'    => "{$this->_namespace}{$this->name}",
        );
        $this->_upload_element =& HTML_QuickForm::createElement('file', "{$this->name}_file", '', $attributes);

        $attributes = Array
        (
            'class' => 'fileselector',
            'id'    => "{$this->_namespace}{$this->name}_video",
        );
        $this->_upload_element_video =& HTML_QuickForm::createElement('file', "{$this->name}_file_video", '', $attributes);
 
        $elements = Array();
        $elements_video = Array();

	if (isset($this->_type->attachments['main']) && !isset($this->_type->attachments['main_video']))
	{
            $this->_create_replace_elements($elements);
            $this->_create_upload_elements_video($elements_video);
	}
	elseif (isset($this->_type->attachments['main_video']) && !isset($this->_type->attachments['main']))
	{
            $this->_create_replace_elements_video($elements_video);
            $this->_create_upload_elements($elements);
	}
	elseif (isset($this->_type->attachments['main_video']) && isset($this->_type->attachments['main']))
        {
            $this->_create_replace_elements($elements);
            $this->_create_replace_elements_video($elements_video);
	}
	else
	{
            $this->_create_upload_elements($elements);
            $this->_create_upload_elements_video($elements_video);
	}

        $this->_form->addGroup($elements, $this->name, "Screenshot", ' ', false);
        $this->_form->addGroup($elements_video, $this->name ."_video", "Video", ' ', false);
    }

    /**
     * Switches the Element Group from a replace/delete constellation to a
     * simple upload form.
     *
     * @access private
     */
    function _cast_formgroup_to_upload()
    {
        $new_elements = Array();
        $this->_create_upload_elements($new_elements);

        $group =& $this->_form->getElement($this->name);
        $group->setElements($new_elements);
    }

    /**
     * Switches the Element Group from a simple upload form to a
     * replace/delete constellation.
     *
     * @access private
     */
    function _cast_formgroup_to_replacedelete()
    {
        $new_elements = Array();
        $this->_create_replace_elements($new_elements);

        $group =& $this->_form->getElement($this->name);
        $group->setElements($new_elements);
    }

    function _cast_formgroup_to_upload_video()
    {
        $new_elements = Array();
        $this->_create_upload_elements_video($new_elements);

        $group =& $this->_form->getElement($this->name . "_video");
        $group->setElements($new_elements);
    }

    function _cast_formgroup_to_replacedelete_video()
    {
        $new_elements = Array();
        $this->_create_replace_elements_video($new_elements);

        $group =& $this->_form->getElement($this->name . "_video");
        $group->setElements($new_elements);
    }

    /**
     * Creates the upload elements for empty types.
     *
     * @param Array $elements The array where the references to the created elements should
     *     be added.
     */
    function _create_upload_elements(&$elements)
    {
        // Get preview image size
        if ($this->_type->auto_thumbnail)
        {
            $x = $this->_type->auto_thumbnail[0];
            $y = $this->_type->auto_thumbnail[1];
        }
        else
        {
            $x = 75;
            $y = 75;
        }

        // Treate auto-scales sanely
        if ($x == 0)
        {
            $x = $y;
        }
        if ($y == 0)
        {
            $y = $x;
        }

        // Start widget table, add upload image Frame, the statistcs frame will just keep a no file notice.
        $static_html = "<table border='0' class='midcom_helper_datamanager2_widget_image_table' id='{$this->_namespace}{$this->name}_table'>\n<tr>\n" .
            "<td align='center' valign='top' class='midcom_helper_datamanager2_widget_image_thumbnail'>" .
            "<div class='midcom_helper_datamanager2_widget_image_thumbnail' style='width:{$x}px; height:{$y}px; border: 1px solid black; margin-bottom: 0.5ex;'>" .
            "&nbsp;</div>" .
            "</td>\n<td valign='top' class='midcom_helper_datamanager2_widget_image_stats'>" . $this->_l10n->get('no file uploaded') .
            "</td>\n</tr>\n";

        // Add the upload widget
        $static_html .= "<tr>\n<td class='midcom_helper_datamanager2_widget_image_label'>" .
            $this->_l10n->get('upload image') . ":</td>\n" .
            "<td class='midcom_helper_datamanager2_widget_image_upload'>";
        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_start", '', $static_html);

        $elements[] =& $this->_upload_element;
        $attributes = Array
        (
            'id'    => "{$this->_namespace}{$this->name}_upload_button",
        );
        //$elements[] =& HTML_QuickForm::createElement('submit', "{$this->name}_upload", $this->_l10n->get('upload file'), $attributes);

        // Add Title line if configured to do so
        if ($this->show_title)
        {
            $static_html = "</td>\n</tr>\n" .
                "<tr>\n<td class='midcom_helper_datamanager2_widget_image_label'>" .
                $this->_l10n_midcom->get('title') . ":</td>\n" .
                "<td class='midcom_helper_datamanager2_widget_image_title'>";
            $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_inter2", '', $static_html);

            $attributes = Array
            (
                'class' => 'shorttext',
                'id'    => "{$this->_namespace}{$this->name}_title",
            );
            $elements[] =& HTML_QuickForm::createElement('text', "{$this->name}_title", $this->_type->title, $attributes);
        }

        $static_html = "\n</td>\n</tr>\n</table>\n";
        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_end", '', $static_html);
    }

    /**
     * Creates the elements to manage an existing upload, offering "delete" and "upload new file"
     * operations.
     *
     * @param Array $elements The array where the references to the created elements should
     *     be added.
     */
    function _create_replace_elements(&$elements)
    {
        // Get preview image source
        if (array_key_exists('thumbnail', $this->_type->attachments))
        {
            $url = $this->_type->attachments_info['thumbnail']['url'];
            $x = $this->_type->attachments_info['thumbnail']['size_x'];
            $y = $this->_type->attachments_info['thumbnail']['size_y'];
            $is_thumbnail = true;
        }
        else
        {
	   $is_thumbnail = false;
	   $url = "";
	   $x = "";
	   $y = "";

            $url = $this->_type->attachments_info['main']['url'];
            $x = $this->_type->attachments_info['main']['size_x'];
            $y = $this->_type->attachments_info['main']['size_y'];
            $is_thumbnail = false;

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
        }

        $size = " width='{$x}' height='{$y}'";

	if (isset($this->_type->attachments_info['main']))
	{
            $main_info = $this->_type->attachments_info['main'];
        }

        // Start widget table, add Thumbnail
        $static_html = "<table border='0' class='midcom_helper_datamanager2_widget_image_table' id='{$this->_namespace}{$this->name}_table'>\n<tr>\n" .
            "<td align='center' valign='top' class='midcom_helper_datamanager2_widget_image_thumbnail'>
	    <img class='midcom_helper_datamanager2_widget_image_thumbnail' src='{$url}' {$size} />";
        if ($is_thumbnail)
        {
            $static_html .= "<br />\n(" . $this->_l10n->get('type image: thumbnail') . ')';
        }

        // Statistcs & Available sizes
        $static_html .= "</td>\n<td valign='top' class='midcom_helper_datamanager2_widget_image_stats'>
	" . $this->_l10n->get('type blobs: file size') . ": {$main_info['formattedsize']} Byte <br/>\n";
        $static_html .= $this->_l10n->get('type image: available sizes') . ":\n" .
                "<ul class='midcom_helper_datamanager2_widget_image_sizelist'>";

//echo "<pre>";
//print_r($this->_type->attachments);
//print_r($this->_type->attachments_info);
//echo "</pre>";

        foreach ($this->_type->attachments_info as $name => $info)
        {
	if ($name == "main")
	{
            if (   $info['size_x']
                && $info['size_y'])
            {
                $size = "{$info['size_x']}x{$info['size_y']}";
            }
            else
            {
                $size = $this->_l10n_midcom->get('unknown');
            }
            $static_html .= "<li title=\"{$info['guid']}\"><a href='{$info['url']}' target='_new'>{$info['filename']}:</a> " .
                "{$size}, {$info['formattedsize']} Byte</li>\n";
        }
	
        }
        $static_html .= "</ul>\n";
        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_start", '', $static_html);

        // Add the Delete button
        $attributes = Array
        (
            'id'    => "{$this->_namespace}{$this->name}_delete_button",
        );
        $elements[] =& HTML_QuickForm::createElement('submit', "{$this->name}_delete", $this->_l10n->get('delete image'), $attributes);

        // Add action buttons
        $this->add_action_elements($elements);

        // Add the upload widget
        $static_html = "</td>\n</tr>\n" .
            "<tr>\n<td class='midcom_helper_datamanager2_widget_image_label'>" .
            $this->_l10n->get('replace image') . ":</td>\n" .
            "<td class='midcom_helper_datamanager2_widget_image_upload'>";
        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_inter1", '', $static_html);

        $elements[] =& $this->_upload_element;
        $attributes = Array
        (
            'id'    => "{$this->_namespace}{$this->name}_upload_button",
        );

        $attributes = Array
        (
            'id'    => "{$this->_namespace}{$this->name}_upload_button",
        );
        //$elements[] =& HTML_QuickForm::createElement('submit', "{$this->name}_upload", $this->_l10n->get('upload file'), $attributes);

        // Add Title line if configured to do so.
        if ($this->show_title)
        {
            $static_html = "</td>\n</tr>\n" .
                "<tr>\n<td class='midcom_helper_datamanager2_widget_image_label'>" .
                $this->_l10n_midcom->get('title') . ":</td>\n" .
                "<td class='midcom_helper_datamanager2_widget_image_title'>";
            $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_inter2", '', $static_html);

            $attributes = Array
            (
                'class' => 'shorttext',
                'id'    => "{$this->_namespace}{$this->name}_title",
            );
            $elements[] =& HTML_QuickForm::createElement('text', "{$this->name}_title", $this->_type->title, $attributes);
        }

        $static_html = "\n</td>\n</tr>\n</table>\n";
        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_end", '', $static_html);
    }

   
    function _create_upload_elements_video(&$elements)
    {
        // The table
        $static_html = "<table border='0' class='midcom_helper_datamanager2_widget_image_table' id='{$this->_namespace}{$this->name}_table'>\n";
        
        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_start", '', $static_html);

        // The video file
        $static_html = "<tr><td>Video:</td><td>" . $this->_l10n->get('no file uploaded') . "</td></tr>\n";

	// Video info
        	
        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_inter2", '', $static_html);
	
        // The upload field
	$static_html = "<tr><td>" . $this->_l10n->get('upload file') . "</td><td>\n";

        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_inter2", '', $static_html);

        $elements[] =& $this->_upload_element_video;
        $attributes = Array
        (
            'id'    => "{$this->_namespace}{$this->name}_upload_button_video",
        );
        //$elements[] =& HTML_QuickForm::createElement('submit', "{$this->name}_upload_video", $this->_l10n->get('upload file'), $attributes);
	
	$static_html = "</td></tr>\n";

        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_inter2", '', $static_html);

        $static_html = "\n</table>\n";
        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_end", '', $static_html);
    }

    function _create_replace_elements_video(&$elements)
    {
        // The table
        $static_html = "<table border='0' class='midcom_helper_datamanager2_widget_image_table' id='{$this->_namespace}{$this->name}_table'>\n";
        
        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_start", '', $static_html);

        // The video file
        $static_html = "<tr><td>Video:</td><td><ul>\n";

	// Video info
        	
        foreach ($this->_type->attachments_info as $name => $info)
	{
	    if ($name == "main_video")
  	    {
                $static_html .= "<li title=\"{$info['guid']}\"><a href='{$info['url']}' target='_new'>{$info['filename']}:</a> " .
                    "{$info['formattedsize']} Byte</li>\n";
	    }
	}

	$static_html .= "</ul>\n";
        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_inter2", '', $static_html);
	
        // Add the Delete button
        $attributes = Array
        (
            'id'    => "{$this->_namespace}{$this->name}_delete_button",
        );
        $elements[] =& HTML_QuickForm::createElement('submit', "{$this->name}_delete_video", $this->_l10n->get('delete video'), $attributes);
        
	// The upload field
	$static_html = "</td></tr><tr><td>" . $this->_l10n->get('upload file') . "</td><td>\n";

        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_inter2", '', $static_html);

        $elements[] =& $this->_upload_element_video;
        $attributes = Array
        (
            'id'    => "{$this->_namespace}{$this->name}_upload_button_video",
        );
        //$elements[] =& HTML_QuickForm::createElement('submit', "{$this->name}_upload_video", $this->_l10n->get('upload file'), $attributes);
	
	$static_html = "</td></tr>";

        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_inter2", '', $static_html);

        $static_html = "\n</table>\n";
        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_end", '', $static_html);
    }

    /**
     * Constructs the widget for frozen operation: Only a single static element is added
     * indicating the current type state.
     *
     * @param Array $elements The array where the references to the created elements should
     *     be added.
     */
    function _create_frozen_elements(&$elements)
    {
        if ($this->_type->attachments)
        {
            // Get preview image source
            if (array_key_exists('thumbnail', $this->_type->attachments))
            {
                $url = $this->_type->attachments_info['thumbnail']['url'];
                $x = $this->_type->attachments_info['thumbnail']['size_x'];
                $y = $this->_type->attachments_info['thumbnail']['size_y'];
            }
            else
            {
                $url = $this->_type->attachments_info['main']['url'];
                $x = $this->_type->attachments_info['main']['size_x'];
                $y = $this->_type->attachments_info['main']['size_y'];

                // Downscale Preview image to max 75px, protect against broken images:
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
            }

            $size = " width='{$x}' height='{$y}'";
            $main_info = $this->_type->attachments_info['main'];

            // Add Image with Preview
            $html = "<p><img src='{$url}' {$size} />&nbsp;" .
                "<a href='{$main_info['url']}' target='_new'>{$main_info['filename']}</a>, " .
                "{$main_info['formattedsize']} B";
        }
        else
        {
            $html = $this->_l10n->get('no file uploaded');
        }
        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_filename", '', "<p>{$html}</p>");
    }

    /**
     * The on_submit event handles all file uploads immediately. They are passed through
     * the type at that point. This is required, since we do not have persistent upload
     * file management on the QF side. Deletions take precedence over uploads.
     */
    function on_submit($results)
    {
        parent::on_submit($results);

        // TODO: refator these checks to separate methods
        if (array_key_exists("{$this->name}_delete_video", $results))
        {
            if (! $this->_type->delete_all_attachments())
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to delete all attached old images on the field {$this->name}.",
                    MIDCOM_LOG_ERROR);
                debug_pop();
            }

            // Adapt the form:
            $this->_cast_formgroup_to_upload();
            $this->_cast_formgroup_to_upload_video();
        }
        elseif (array_key_exists("{$this->name}_delete", $results))
        {
            if (! $this->_type->delete_screenshot())
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to delete all attached old images on the field {$this->name}.",
                    MIDCOM_LOG_ERROR);
                debug_pop();
            }

            // Adapt the form:
            $this->_cast_formgroup_to_upload();
        }
        else if (array_key_exists("{$this->name}_rotate", $results))
        {
            // The direction is the key (since the value is the point clicked on the image input)
            list ($direction, $dummy) = each($results["{$this->name}_rotate"]);
            if (! $this->_type->rotate($direction))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to rotate image on the field {$this->name}.",
                    MIDCOM_LOG_ERROR);
                debug_pop();
            }

        }   
    	else
    	{
            if ($this->_upload_element_video->isUploadedFile())
            {
    
                $file_video = $this->_upload_element_video->getValue();
    
                //echo "<pre>";
                //print_r($this->_upload_element_video);
                //print_r($this->_upload_element);
                //echo "</pre>";
    
                if (!empty($file_video['name']))
                {
                    if (! $this->_type->set_video($file_video['name'], $file_video['tmp_name'], 'Video file'))
                    {
                        debug_push_class(__CLASS__, __FUNCTION__);
                        debug_add("Failed to process image {$this->name}.", MIDCOM_LOG_INFO);
                        debug_pop();
                        $this->_cast_formgroup_to_upload_video();
                    }
                    else
                    {
                        $this->_cast_formgroup_to_replacedelete_video();
                    }
                }
            }
            
            if ($this->_upload_element->isUploadedFile())
            {
                $file = $this->_upload_element->getValue();
            
                //echo "<pre>";
                //print_r($this->_upload_element_video);
                //print_r($this->_upload_element);
                //echo "</pre>";
    
                if ($this->show_title)
                {
                    $title = $results["{$this->name}_title"];
                }
                else
                {
                    $title = '';
                }
                
                if (!empty($file['name']))
                {
                    if (! $this->_type->set_image($file['name'], $file['tmp_name'], $title))
                    {
                        debug_push_class(__CLASS__, __FUNCTION__);
                        debug_add("Failed to process image {$this->name}.", MIDCOM_LOG_INFO);
                        debug_pop();
                        $this->_cast_formgroup_to_upload();
                    }
                    else
                    {
                        $this->_cast_formgroup_to_replacedelete();
                    }
                }
            }
        }
    }

    /**
     * Freeze the entire group, special handling applies, the formgroup is replaced by a single
     * static element.
     */
    function freeze()
    {
        $new_elements = Array();
        $this->_create_frozen_elements($new_elements);

        $group =& $this->_form->getElement($this->name);
        $group->setElements($new_elements);
    }

    /**
     * Unfreeze the entire group, special handling applies, the formgroup is replaced by a the
     * full input widget set.
     */
    function unfreeze()
    {
        $new_elements = Array();

        if ($this->_type->attachments)
        {
            $this->_create_replace_elements($new_elements);
        }
        else
        {
            $this->_create_upload_elements($new_elements);
        }

        $group =& $this->_form->getElement($this->name);
        $group->setElements($new_elements);
    }

    /**
     * Synchronize the title field
     */
    function sync_type_with_widget($results)
    {
        if (   $this->show_title
            && isset($results["{$this->name}_title"]))
        {
            $this->_type->title = $results["{$this->name}_title"];
        }
    }

    /**
     * Populate the title field.
     */
    function get_default()
    {
        if ($this->show_title)
        {
            return Array("{$this->name}_title" => $this->_type->title);
        }
    }

    /**
     * Adds common image operations to the QF.
     *
     * @param array $elements reference to the elements array to add the actions to
     */
    function add_action_elements(&$elements)
    {
        // TODO: namespace the inputs properly (NOTE: the on_submit checks need to be changed accordingly!)
        $static_html = "\n<div class='midcom_helper_datamanager2_widget_image_actions_container' id='{$this->_namespace}{$this->name}_image_actions_container'>\n";
        $static_html .= "    <span class='field_text'>" . $this->_l10n->get('type image: actions') . ":</span>\n";
        $static_html .= "    <ul class='midcom_helper_datamanager2_widget_image_actions' id='{$this->_namespace}{$this->name}_image_actions'>\n";
        $static_html .= "        <li title='" . $this->_l10n->get('rotate left') . "'>\n";
        $static_html .= "            <input type='image' name='{$this->name}_rotate[left]' src='".MIDCOM_STATIC_URL."/stock-icons/16x16/rotate_ccw.png' />\n";
        $static_html .= "         </li>\n";
        $static_html .= "         <li title='" . $this->_l10n->get('rotate right') . "'>\n";
        $static_html .= "             <input type='image' name='{$this->name}_rotate[right]' src='".MIDCOM_STATIC_URL."/stock-icons/16x16/rotate_cw.png' />\n";
        $static_html .= "         </li>\n";
        $static_html .= "    </ul>\n";
        $static_html .= "</div>\n";
        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_image_actions_static", '', $static_html);
    }

}

?>

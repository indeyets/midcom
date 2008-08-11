<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a widget to handle image attachments.
 *
 * This widget should only be used with the image datatype. It will show a bunch
 * of meta-data fields (unless in simple mode). Deletion is handled through a
 * checkbox while saving the datamanager form.
 *
 * <b>Configuration parameters:</b>
 *
 * <b>widget_image_simple:</b> Set this to true to reduce the field to a very
 * simple upload field without the ability to set mime-type, filename and
 * file description.
 *
 * <b>Sample configuration</b>
 *
 * <pre>
 * "image" => array (
 *     "description" => "Image",
 *     "datatype" => "image",
 *     "widget_image_simple" => true
 * ),
 * </pre>
 *
 * <b>CSS Styles in use by the Widget</b>
 *
 * The fieldset is set to fieldset.image. The inputfields are input.shorttext for all
 * text fields and input.fileupload for the actual upload field. The deletion checkbox
 * is of the class input.checkbox.
 *
 * Note, that while the field is empty, the fieldset is only used, when the full-scale
 * widget is used. The simple widget does not use it. While editing an existing image,
 * both have the same UI.
 *
 * The format selection dropdowns are of select.dropdown.
 *
 * The download link is a simple paragraph within the enclosing fieldset.
 *
 * The preview link is a nested div: div.image_preview surrounds the entire preview area,
 * div.image_frame surrounds any displayed image.
 *
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_widget_image extends midcom_helper_datamanager_widget{

    /**
     * Fieldname constant
     *
     * @var string
     * @access private
     */
    var $_fieldname_delete;

    /**
     * Fieldname constant
     *
     * @var string
     * @access private
     */
    var $_fieldname_description;

    /**
     * Fieldname constant
     *
     * @var string
     * @access private
     */
    var $_fieldname_filename;

    /**
     * Fieldname constant
     *
     * @var string
     * @access private
     */
    var $_fieldname_mimetype;

    /**
     * Fieldname constant
     *
     * @var string
     * @access private
     */
    var $_fieldname_storetype;

    /**
     * Fieldname constant
     *
     * @var string
     * @access private
     */
    var $_fieldname_thumbtype;

    /**
     * The array with the commands to execute (for the type)
     *
     * @var array
     * @access private
     */
    var $_commands;

    /**
     * Set to true if the simple upload widget should be shown.
     *
     * @var boolean
     * @access private
     */
    var $_simple;

    function _constructor (&$datamanager, $field, $defaultvalue) {
        $this->_fieldname_delete = "{$datamanager->form_prefix}_{$field['name']}_delete";
        $this->_fieldname_description = "{$datamanager->form_prefix}_{$field['name']}_description";
        $this->_fieldname_filename = "{$datamanager->form_prefix}_{$field['name']}_filename";
        $this->_fieldname_mimetype = "{$datamanager->form_prefix}_{$field['name']}_mimetype";
        $this->_fieldname_storetype = "{$datamanager->form_prefix}_{$field['name']}_storetype";
        $this->_fieldname_thumbtype = "{$datamanager->form_prefix}_{$field['name']}_thumbtype";

        if (! array_key_exists('widget_image_simple', $field))
        {
            $field['widget_image_simple'] = false;
        }
        $this->_simple = ($field['widget_image_simple'] == true);

        $this->_commands = Array();

        parent::_constructor ($datamanager, $field, $defaultvalue);

    }

    /**
     * Evaluates the various controls and populates the command array for
     * processing through datatype_image
     *
     * @see midcom_helper_datamanager_datatype_image
     */
    function _read_formdata () {
        if (array_key_exists($this->_fieldname_delete, $_REQUEST) &&
            ! array_key_exists("upload", $this->_commands))
            $this->_commands["delete"] = ($_REQUEST[$this->_fieldname_delete] == 1);

        // Uploaded files are in _FILES autoglobal on PHP 4.3+ and _REQUEST on earlier
        if (version_compare(phpversion(),"4.3.0",">=")) {
            if (array_key_exists($this->_fieldname, $_FILES) &&
                is_uploaded_file($_FILES[$this->_fieldname]["tmp_name"]))
                $this->_commands["upload"] = $_FILES[$this->_fieldname];
        } else {
            if (array_key_exists($this->_fieldname, $_REQUEST) &&
                is_uploaded_file($_REQUEST[$this->_fieldname]["tmp_name"]))
                $this->_commands["upload"] = $_REQUEST[$this->_fieldname];
        }

        if (array_key_exists($this->_fieldname_description, $_REQUEST))
            $this->_commands["meta"]["description"] = $_REQUEST[$this->_fieldname_description];
        else
            $this->_commands["meta"]["description"] = "";

        if (array_key_exists($this->_fieldname_filename, $_REQUEST))
            $this->_commands["meta"]["filename"] = $_REQUEST[$this->_fieldname_filename];
        else
            $this->_commands["meta"]["filename"] = "";

        if (array_key_exists($this->_fieldname_mimetype, $_REQUEST))
            $this->_commands["meta"]["mimetype"] = $_REQUEST[$this->_fieldname_mimetype];
        else
            $this->_commands["meta"]["mimetype"] = "";

        if (array_key_exists($this->_fieldname_storetype, $_REQUEST))
            $this->_commands["meta"]["storetype"] = $_REQUEST[$this->_fieldname_storetype];
        else
            $this->_commands["meta"]["storetype"] = "";

        if (array_key_exists($this->_fieldname_thumbtype, $_REQUEST))
            $this->_commands["meta"]["thumbtype"] = $_REQUEST[$this->_fieldname_thumbtype];
        else
            $this->_commands["meta"]["thumbtype"] = "";

    }

    function draw_view () {
        if (is_null($this->_value)) {
            ?><div class="form_shorttext"><?php echo $this->_l10n->get("no file"); ?></div><?php
        } else {
            $value = $this->_value;
            $preview_url = ( array_key_exists("thumbnail",$value) ? $value["thumbnail"]["url"] : $value["url"] );
            ?>
            <table style="padding: 7px; width: 100%; margin-left: 4em;">
             <tr>
              <td><a href="<?echo htmlspecialchars($value["url"]);?>"><?echo htmlspecialchars($value["filename"]);?></a></td>
              <td><?echo htmlspecialchars($value["formattedsize"]);?> Byte</td>
              <td><?echo htmlspecialchars($value["mimetype"]);?></td>
              <td rowspan="3">
            <?php if (array_key_exists("thumbnail",$value)) { ?>
               <div class="contentadm_attachments_preview"><img class="contentadm_attachments_preview" src="<?echo htmlspecialchars($preview_url);?>" /></div>
            <?php } else { ?>
               <?php $this->_l10n->get("no preview available"); ?>
            <?php } ?>
              </td>
             </tr><tr>
              <td>&nbsp;</td>
              <td colspan="2" valign="top"><?echo htmlspecialchars($value["description"]);?><?php if (array_key_exists("thumbnail", $value)) echo "<br />(" . $this->_l10n->get("thumbnail shown") . ")"; ?></td>
             </tr>
            </table><?php
        }
    }

    /**
     * The widget will use a field group to put all related fields together,
     * unless we're in simple upload mode.
     */
    function draw_widget_start()
    {
        if ($this->_simple && is_null($this->_value))
        {
            parent::draw_widget_start();
        }
        else
        {
            $css = $this->get_css_classes_required();
            if ($css != '')
            {
                $css = " class='$css'";
            }
            echo "<fieldset class='blob' id='{$this->_fieldname}_fieldset'>\n";
            echo "  <legend{$css}>\n";
            echo '    ' . htmlspecialchars($this->_field['description']);
            $this->draw_helptext();
            echo "\n";
            echo "  </legend>\n";
        }
    }

    /**
     * The widget will use a field group to put all related fields together,
     * unless we're in simple upload mode.
     */
    function draw_widget_end()
    {
        if ($this->_simple && is_null($this->_value))
        {
            parent::draw_widget_end();
        }
        else
        {
            echo "</fieldset>\n";
        }
    }

    /**
     * The widget will use a field group to put all related fields together,
     * unless we're in simple upload mode.
     */
    function draw_widget () {
        if (is_null($this->_value))
        {
            if ($this->_simple)
            {
                echo "<input type='file' class='fileselector' name='{$this->_fieldname}' id='{$this->_fieldname}' />\n";
            }
            else
            {
                $field = $this->_fieldname;
                $label = $this->_l10n->get("upload file");
                echo "  <label for='{$field}' id='{$field}_label'>\n";
                echo "    {$label}\n";
                echo "    <input type='file' class='fileselector' name='{$field}' id='{$field}' />\n";
                echo "  </label>\n";

                $field = $this->_fieldname_description;
                $label = $this->_l10n->get("description") . ' (' . $this->_l10n_midcom->get("optional") . ')';
                echo "  <label for='{$field}' id='{$field}_label'>\n";
                echo "    {$label}\n";
                echo "    <input class='shorttext' name='{$field}' id='{$field}' maxlength='255' />\n";
                echo "  </label>\n";

                $field = $this->_fieldname_filename;
                $label = $this->_l10n->get("filename") . ' (' . $this->_l10n_midcom->get("optional") . ')';
                echo "  <label for='{$field}' id='{$field}_label'>\n";
                echo "    {$label}\n";
                echo "    <input class='shorttext' name='{$field}' id='{$field}' maxlength='255' />\n";
                echo "  </label>\n";

                $formats = Array (
                    '' => $this->_l10n->get('automatic'),
                    'png' => $this->_l10n->get('png'),
                    'jpeg' => $this->_l10n->get('jpeg'),
                    'asis' => $this->_l10n->get('asis')
                );

                $field = $this->_fieldname_storetype;
                $label = $this->_l10n->get("store type") . ' (' . $this->_l10n_midcom->get("optional") . ')';
                echo "  <label for='{$field}' id='{$field}_label'>\n";
                echo "    {$label}\n";
                echo "    <select class='dropdown' name='{$field}' id='{$field}'>\n";
                foreach ($formats as $key => $value)
                {
                    echo "      <option value='{$key}'>{$value}</option>\n";
                }
                echo "    </select>\n";
                echo "  </label>\n";

                $field = $this->_fieldname_thumbtype;
                $label = $this->_l10n->get("thumb type") . ' (' . $this->_l10n_midcom->get("optional") . ')';
                echo "  <label for='{$field}' id='{$field}_label'>\n";
                echo "    {$label}\n";
                echo "    <select class='dropdown' name='{$field}' id='{$field}'>\n";
                foreach ($formats as $key => $value)
                {
                    echo "      <option value='{$key}'>{$value}</option>\n";
                }
                echo "    </select>\n";
                echo "  </label>\n";

            }
        } else {
            $value = $this->_value;
            $preview_url = ( array_key_exists("thumbnail",$value) ? $value["thumbnail"]["url"] : $value["url"] );

            echo "  <div class='blob_preview'>\n";
            if (substr($this->_value["mimetype"],0,5) == "image")
            {
                echo '    ' . $this->_l10n->get('preview') . ":\n";
                echo "    <div class='image_frame'><img src='{$preview_url}' ALT='BLOB Preview' /></div>\n";
                if (array_key_exists("thumbnail", $value))
                {
                    echo "(" .  $this->_l10n->get("thumbnail shown") . ")";
                }
            }
            else
            {
                echo $this->_l10n->get('no preview available');
            }
            echo "  </div>\n";

            $field = $this->_fieldname_delete;
            $label = $this->_l10n->get("delete file");
            echo "  <label for='{$field}' id='{$field}_label'>\n";
            echo "    {$label}&nbsp;&nbsp;<input type='checkbox' class='checkbox' name='{$field}' id='{$field}' />\n";
            echo "  </label>\n";

            $field = $this->_fieldname_description;
            $label = $this->_l10n->get("description") . ' (' . $this->_l10n_midcom->get("optional") . ')';
            $val = htmlspecialchars($value["description"], ENT_QUOTES);
            echo "  <label for='{$field}' id='{$field}_label'>\n";
            echo "    {$label}\n";
            echo "    <input class='shorttext' name='{$field}' id='{$field}' maxlength='255' value='{$val}' />\n";
            echo "  </label>\n";

            $field = $this->_fieldname_filename;
            $label = $this->_l10n->get("filename") . ' (' . $this->_l10n_midcom->get("optional") . ')';
            $val = htmlspecialchars($value["filename"], ENT_QUOTES);
            echo "  <label for='{$field}' id='{$field}_label'>\n";
            echo "    {$label}\n";
            echo "    <input class='shorttext' name='{$field}' id='{$field}' maxlength='255' value='{$val}' />\n";
            echo "  </label>\n";

            $download = sprintf($this->_l10n_midcom->get('download %s'), $value['filename']);
            echo "  <p>\n";
            echo "    <a href={$value['url']}>{$download}</a>\n";
            echo "    ({$value['mimetype']}, {$value['formattedsize']}&nbsp;Byte)\n";
            echo "  </p>\n";

        }
    }

    function get_value() {
        return $this->_commands;
    }

}

?>
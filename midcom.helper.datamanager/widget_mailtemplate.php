<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a widget that manages E-Mail templates.
 *
 * This widget can only be used with the mailtemplate type.
 *
 * Non-Power-Users will see only the From, Reply-To, CC, Subject and Body fields.
 *
 * <b>Configuration parameters:</b>
 *
 * None except those of the type.
 *
 * <b>Sample configuration</b>
 *
 * <pre>
 * "mailtemplate" => Array (
 *     "description" => "mailtemplate",
 *     "datatype" => "mailtemplate"
 * ),
 * </pre>
 *
 * <b>CSS Styles in use by the Widget</b>
 *
 * All input fields use the class input.shorttext or textarea.longtext respectivly.
 * The fieldset is set to fieldset.mailtemplate.
 *
 * @package midcom.helper.datamanager
 */

class midcom_helper_datamanager_widget_mailtemplate extends midcom_helper_datamanager_widget {

    /**
     * Poweruser flag
     *
     * @access private
     * @var bool
     */
    var $_poweruser;

    function _constructor (&$datamanager, $field, $defaultvalue)
    {
        parent::_constructor ($datamanager, $field, $defaultvalue);

        $midgard = $GLOBALS["midcom"]->get_midgard();
        if ($midgard->admin)
        {
            $this->_poweruser = true;
        }
        else
        {
            $person = mgd_get_person($midgard->user);
            if ($person != false)
            {
                if ($person->parameter("Interface", "Power_User") == "YES")
                {
                    $this->_poweruser = true;
                }
                else
                {
                    $this->_poweruser = false;
                }
            }
            else
            {
                $this->_poweruser = false;
            }
        }
    }

    /**
     * Small helper that determines if a given field is available only
     * for Power Users.
     *
     * @return bool Indicating Power-User-only availability
     */
    function _is_poweruser_field($key)
    {
        switch ($key)
        {
            case "from":
            case "reply-to":
            case "cc":
            case "subject":
            case "body":
                return false;

            default:
                return true;
        }
    }

    function draw_view ()
    {
        ?>
<div class="form_account">
  <dl>
<?php
        foreach ($this->_value as $key => $value)
        {
            if ($this->_is_poweruser_field($key) && ! $this->_poweruser)
                continue;
            ?>
    <dt><?php echo $this->_l10n->get("mailtemplate $key"); ?>:</dt>
    <dd><?echo htmlspecialchars($value);?></dd>
<?php
        }
        ?>
  </dl>
</div>
<?php
    }

    /**
     * A fieldgroup will contain all related fields.
     */
    function draw_widget_start()
    {
        $css = $this->get_css_classes_required();
        if ($css != '')
        {
            $css = " class='$css'";
        }
        echo "<fieldset class='mailtemplate' id='{$this->_fieldname}_fieldset'>\n";
        echo "  <legend{$css}>\n";
        echo '    ' . htmlspecialchars($this->_field['description']);
        $this->draw_helptext();
        echo "\n";
        echo "  </legend>\n";
    }

    /**
     * A fieldgroup will contain all related fields.
     */
    function draw_widget_end()
    {
        echo "</fieldset>\n";
    }


    function draw_widget ()
    {
        foreach ($this->_value as $key => $value)
        {
            if ($this->_is_poweruser_field($key) && ! $this->_poweruser )
            {
                continue;
            }
            $field = "{$this->_fieldname}_{$key}";
            $label = $this->_l10n->get("mailtemplate {$key}");
            $value = htmlspecialchars($value, ENT_QUOTES);
            switch ($key)
            {
                case "body":
                    echo "  <label for='{$field}' id='{$id}_label'>\n";
                    echo "    {$label}\n";
                    echo "    <textarea class='longtext' name='{$field}' id='{$field}'>${value}</textarea>\n";
                    echo "  </label>\n";
                    break;

                default:
                    echo "  <label for='{$field}' id='{$id}_label'>\n";
                    echo "    {$label}\n";
                    echo "    <input class='shorttext' name='{$field}' id='{$field}' value='${value}' />\n";
                    echo "  </label>\n";
                    break;
            }
        }
    }

    /**
     * This override will scan for the available keys in the request data.
     * It updates the corresponding value array keys when found.
     */
    function _read_formdata ()
    {
        debug_push("WIDGET_MAILTEMPLATE::_read_formdata");
        foreach ($_REQUEST as $key => $value)
        {
            if (substr($key, 0, strlen($this->_fieldname)) != $this->_fieldname)
            {
                debug_add("Skipping request field $key");
                continue;
            }
            $keyname = substr($key, strlen($this->_fieldname) + 1);
            debug_print_r("Found Key $keyname: ", $value);
            $this->_value[$keyname] = $value;
        }
        debug_print_r("Updated the data array to:", $this->_value);
        debug_pop();
    }

}


?>
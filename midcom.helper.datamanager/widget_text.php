<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a flexible text widget, that allows for input of plain text in
 * several forms. Supported widgets are:
 *
 * - Simple single line text field ('shorttext')
 * - Auto-wrapping textarea ('longtext')
 * - Preformatted textarea ('longtext_preformatted')
 * - Password field, it will show only a single * for an entered password ('password')
 *
 * This widget is the default widget for these datatypes:
 *
 * - text
 * - number
 *
 * <b>Configuration parameters:</b>
 *
 * <b>widget_text_inputstyle:</b> This defines the actual widget to use, as listed
 * above, defaults to 'shorttext'.
 *
 * <b>widget_text_maxlength:</b> The maximum input length, only relevant for the single
 * line modes shorttext and password. It defaults to 255 there. The value will be
 * ignored for the longtext fields. If set, this must be a positive integer number.
 *
 * <b>widget_text_height, widget_text_width:</b> These to variables are only used with
 * the longtext widgets, they allow you to override the size of the fields using
 * the CSS width and height parameters while editing only. The style='' is used to
 * override the default values.
 *
 * <b>Sample configuration</b>
 *
 * <pre>
 * "title" => array (
 *     "description" => "Title",
 *     "datatype" => "text",
 *     "location" => "title",
 *     "required" => true,
 *     "widget_text_inputstyle" => "longtext",
 *     "widget_text_height" => "5em",
 *     // Example for the shorttext (default) input style
 *     // "widget_text_maxlength" => 60,
 * ),
 * </pre>
 *
 * <b>CSS Styles in use by the Widget</b>
 *
 * The input fields have one of these CSS classes assigned to them in respect to the
 * selected input style:
 *
 * - input.shorttext
 * - textarea.longtext
 * - textarea.longtext_preformatted
 * - input.password
 *
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_widget_text extends midcom_helper_datamanager_widget {

    /**
     * Maximum length of the input text for single-line fields.
     *
     * @var int
     * @access private
     */
    var $_maxlength;

    /**
     * The widget style to use, one of shorttext, longtext, longtext_preformatted
     * or password.
     *
     * @var string
     * @access private
     */
    var $_inputstyle;

    /**
     * Height of longtext fields.
     *
     * Remember to define em or px, as this uses CSS rules, this overrides
     * any rules in ais.css.
     *
     * @var string
     * @access private
     */
    var $_height;

    /**
     * Width of both long- and shorttext fields.
     *
     * Remember to define em or px, as this uses CSS rules, this overrides
     * any rules in ais.css.
     *
     * @var string
     * @access private
     */
    var $_width;

    // Documented in base class, nothing special here.
    function _constructor (&$datamanager, $field, $defaultvalue) {

        if (!array_key_exists("widget_text_inputstyle", $field))
        {
            $field["widget_text_inputstyle"] = "shorttext";
        }
        $this->_inputstyle = $field["widget_text_inputstyle"];

        switch ($this->_inputstyle)
        {
            case 'shorttext':
                if (!array_key_exists("widget_text_maxlength", $field))
                {
                    $field["widget_text_maxlength"] = 255;
                }
                if (!array_key_exists("widget_text_divstyle", $field))
                {
                    $field["widget_text_divstyle"] = "form_shorttext";
                }
                break;

            case 'longtext':
                if (!array_key_exists("widget_text_maxlength", $field))
                {
                    $field["widget_text_maxlength"] = null;
                }
                if (!array_key_exists("widget_text_divstyle", $field))
                {
                    $field["widget_text_divstyle"] = "form_longtext";
                }
                break;

            case 'longtext_preformatted':
                if (!array_key_exists("widget_text_maxlength", $field))
                {
                    $field["widget_text_maxlength"] = null;
                }
                if (!array_key_exists("widget_text_divstyle", $field))
                {
                    $field["widget_text_divstyle"] = "form_longtext_preformatted";
                }
                break;

            case 'password':
                if (!array_key_exists("widget_text_maxlength", $field))
                {
                    $field["widget_text_maxlength"] = 255;
                }
                if (!array_key_exists("widget_text_divstyle", $field))
                {
                    $field["widget_text_divstyle"] = "form_shorttext";
                }
                break;
        }

        if (!array_key_exists("widget_text_height", $field))
        {
            $field["widget_text_height"] = null;
        }
        if (!array_key_exists("widget_text_width", $field))
        {
            $field["widget_text_width"] = null;
        }

        $this->_width = $field["widget_text_width"];
        $this->_height = $field["widget_text_height"];
        $this->_maxlength = (int) $field["widget_text_maxlength"];
        parent::_constructor ($datamanager, $field, $defaultvalue);
    }

    // Documented in base class, nothing special here.
    function draw_view () {
        switch ($this->_inputstyle)
        {
            case "shorttext":
            case "longtext":
                ?><div><?echo nl2br(htmlspecialchars($this->_value));?></div><?php
                break;

            case "longtext_preformatted":
                ?><pre><?echo htmlspecialchars($this->_value);?></pre><?php
                break;

            case "password":
                ?><div>**********</div><?php
                break;
        }
    }

    // Documented in base class, nothing special here.
    function draw_widget () {
        switch ($this->_inputstyle)
        {
            case "shorttext":
                $max = htmlspecialchars($this->_maxlength);
                echo "<input class='{$this->_inputstyle}' name='{$this->_fieldname}' id='{$this->_fieldname}' maxlength='{$this->_maxlength}' value='";
                echo htmlspecialchars($this->_value, ENT_QUOTES);
                echo "' />\n";
                break;

            case "longtext_preformatted":
                echo "<textarea wrap='off' class='{$this->_inputstyle}' name='{$this->_fieldname}' id='{$this->_fieldname} '";
                if ($this->_height != null || $this->_width != null)
                {
                    echo "style='";
                    if ($this->_width != null )
                    {
                        echo "width: {$this->_width};";
                    }
                    if ($this->_height != null )
                    {
                        echo " height: {$this->_height};";
                    }
                    echo "'";
                }
                echo ">{$this->_value}</textarea>\n";
                break;

            case "longtext":
                echo "<textarea class='{$this->_inputstyle}' name='{$this->_fieldname}' id='{$this->_fieldname}'";
                if ($this->_height != null || $this->_width != null)
                {
                    echo "style='";
                    if ($this->_width != null )
                    {
                        echo "width: {$this->_width};";
                    }
                    if ($this->_height != null )
                    {
                        echo " height: {$this->_height};";
                    }
                    echo "'";
                }
                echo ">{$this->_value}</textarea>\n";
                break;

            case "password":
                if (strlen ($this->_value) > 0)
                {
                    $value = "*";
                }
                else
                {
                    $value = "";
                }
                $max = htmlspecialchars($this->_maxlength, ENT_QUOTES);
                echo "<input type='password' class='{$this->_inputstyle}' name='{$this->_fieldname}' id='{$this->_fieldname}' maxlength='{$this->_maxlength}' value='";
                echo htmlspecialchars($this->_value, ENT_QUOTES);
                echo "' />\n";
                break;
        }
    }

    /**
     * Overridden to handle the password style widget. It mustn't
     * update in certain cases.
     */
    function _read_formdata () {
        if (array_key_exists($this->_fieldname, $_REQUEST))
        {
            $value = $_REQUEST[$this->_fieldname];
            if (   $this->_inputstyle != "password"
                || ($value != "*" && strlen($this->_value) > 0)
                || strlen($this->_value) == 0)
            {
                $this->_value = $value;
            }
        }
    }

}


?>
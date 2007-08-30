<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a widget to show a JS driven date selector.
 *
 * This widget should currently only be used with the unixdate type, for which it
 * is the default currently.
 *
 * <b>Configuration parameters:</b>
 *
 * <b>date_withtime:</b> Controls, if the stored value is to be shown with or without
 * the time-of-day. If omitted, 00:00:00 is assumed.
 *
 * <b>widget_date_minyear:</b> Lower boundary of the year selection, defaults to "0".
 * Note, that certain types might be more restrictive here.
 *
 * <b>widget_date_maxyear:</b> Upper boundary of the year selection, defaults to "9999".
 * Note, that certain types might be more restrictive here.
 *
 * <b>widget_date_enable_outside_ais:</b> Set this to true to allow the JS code to
 * be added even if we are not within AIS. This is disabled by default to keep on-site
 * performance up.
 *
 * <b>Sample configuration</b>
 *
 * <pre>
 * "date" => array (
 *     "description" => "Date (Unix-Timestamp style)",
 *     "datatype" => "unixdate",
 *     "widget" => "date",
 *     "date_withtime" => true,
 * ),
 * </pre>
 *
 * <b>CSS Styles in use by the Widget</b>
 *
 * The fieldset will be of fieldset.date, the input within it is input.date and
 * the button that invokes the JS code is button.date.
 *
 * <b>JScript Calendar licence information</b>
 *
 * The DHTML Calendar, details and latest version at: http://dynarch.com/mishoo/calendar.epl
 *
 * This script is distributed under the GNU Lesser General Public License.
 * Read the entire license text here: http://www.gnu.org/licenses/lgpl.html
 *
 * @package midcom.helper.datamanager
 */

class midcom_helper_datamanager_widget_date extends midcom_helper_datamanager_widget {

    var $_initfuncname;
    var $_withtime;
    var $_minimumyear;
    var $_maximumyear;
    var $_enable_outside_ais;

    function _constructor (&$datamanager, $field, $defaultvalue) {
        $this->_datamanager =& $datamanager;
        $this->_field = $field;
        $this->_fieldname = $this->_datamanager->form_prefix . "field_" . $field["name"];
        $this->_value = $defaultvalue;

        if (!array_key_exists("date_withtime", $field))
        {
            $field["date_withtime"] = false;
        }
        if (!array_key_exists("widget_date_minyear", $field))
        {
            $field["widget_date_minyear"] = 0;
        }
        if (!array_key_exists("widget_date_maxyear", $field))
        {
            $field["widget_date_maxyear"] = 9999;
        }
        if (!array_key_exists("widget_date_enable_outside_ais", $field))
        {
            $field["widget_date_enable_outside_ais"] = false;
        }

        $this->_withtime = $field["date_withtime"];
        $this->_maximumyear = $field["widget_date_maxyear"];
        $this->_minimumyear = $field["widget_date_minyear"];
        if ($this->_minimumyear > $this->_maximumyear)
            $this->_minimumyear = $this->_maximumyear;
        $this->_enable_outside_ais = $field["widget_date_enable_outside_ais"];

        $this->_read_formdata();

        // Ensure that AIS is running
        if (   $this->_enable_outside_ais
            || (   array_key_exists('view_contentmgr', $GLOBALS)
                && $GLOBALS['view_contentmgr']))
        {
            $midgard = $_MIDCOM->get_midgard();


            static $js_included = false;
            if (! $js_included)
            {
                $js_included = true;
                $midgard = mgd_get_midgard();
                $prefix = MIDCOM_STATIC_URL . '/midcom.helper.datamanager/jscript-calendar';
                $_MIDCOM->add_jsfile("{$prefix}/calendar.js");

                // Select correct locale
                $i18n =& $_MIDCOM->get_service("i18n");
                $language = $i18n->get_current_language();
                switch ($language)
                {
                    // TODO: Add more languages here when corresponding locale files exist
                    case "de":
                        $_MIDCOM->add_jsfile("{$prefix}/calendar-de.js");
                        break;
                    case "fi":
                        $_MIDCOM->add_jsfile("{$prefix}/calendar-fi.js");
                        break;
                    case "en":
                    default:
                        $_MIDCOM->add_jsfile("{$prefix}/calendar-en.js");
                        break;
                }

                $_MIDCOM->add_jsfile("{$prefix}/calendar-setup.js");
            }
            $this->_initfuncname = "showCalendar" . md5($this->_fieldname);

            $tmp = <<<EOT
function {$this->_initfuncname}() {
    Calendar.setup(
        {
EOT;
            $tmp .= "\nifFormat : \"%Y-%m-%d";
            if ($this->_withtime)
                $tmp .= " %H:%M:%S";
            $tmp .= "\",\n";
            if ($this->_withtime)
                $tmp .= "showsTime : true,\n";
            else
                $tmp .= "showsTime : false,\n";
            $tmp .= <<<EOT
            align       : "Br",
            firstDay    : 1,
            timeFormat  : 24,
            showOthers  : true,
            singleClick : false,
            range       : [{$this->_minimumyear}, {$this->_maximumyear}],
            inputField  : "{$this->_fieldname}",
            button      : "{$this->_fieldname}_trigger"
        }
    );
}
EOT;
            if ($_MIDCOM->get_current_context() == 0)
            {
                // We're loaded in main request handler
                $_MIDCOM->add_jscript($tmp);
            }
            else
            {
                // This is dynamic load, just echo the javascript
                echo "<script type=\"text/javascript\">{$tmp}</script>\n";
            }
        }
    }

    function draw_view () {
        ?><div><?echo htmlspecialchars($this->_value);?></div><?php
    }

    /**
     * The widget will use a field group to put all related fields together.
     */
    function draw_widget_start()
    {
        $css = $this->get_css_classes_required();
        if ($css != '')
        {
            $css = " class='{$css}'";
        }
        parent::draw_widget_start();
        echo "<fieldset class='date' id='{$this->_fieldname}_fieldset'>\n";
    }

    /**
     * The widget will use a field group to put all related fields together,.
     */
    function draw_widget_end()
    {
        echo "</fieldset>\n";
        parent::draw_widget_end();
    }

    function draw_widget () {
        $value = htmlspecialchars($this->_value);
        echo "  <input type='text' class='date' name='{$this->_fieldname}' id='{$this->_fieldname}' value='{$value}' />\n";
        echo "  <button type='button' class='date' id='{$this->_fieldname}_trigger' onclick='{$this->_initfuncname}();'></button>\n";
    }


}


?>
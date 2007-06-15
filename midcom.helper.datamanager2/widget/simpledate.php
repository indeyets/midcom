<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 simple date widget
 *
 * This widget is built around the PEAR QuickForm Date widget, which effectivly
 * consists of a set of input fields for each part of the date/time. It is limited
 * to seconds precision therefore. Currently unsupported are the Day options (selects
 * Monday through Sunday) and 12-Hour Time formats (AM/PM time).
 *
 * This widget requires the date type or a subclass thereof.
 *
 * <b>Available configuration options:</b>
 *
 * - <i>string format:</i> The format of the input fields, as outlined in the QuickForm
 *   documentation (referenced at the $format member). This defaults to 'dmY'.
 * - <i>int minyear:</i> Minimum Year available for selection (defaults to 2000).
 * - <i>int maxyear:</i> Maximum Year available for selection (defaults to 2010).
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_widget_simpledate extends midcom_helper_datamanager2_widget
{
    /**
     * The format to use for display.
     *
     * @see http://pear.php.net/manual/en/package.html.html-quickform.html-quickform-date.html-quickform-date.php
     * @var string
     */
    var $format = 'dmY';

    /**
     * Minimum Year available for selection.
     *
     * @var int
     */
    var $minyear = 2000;

    /**
     * Maximum Year available for selection.
     *
     * @var int
     */
    var $maxyear = 2010;

    /**
     * Validates the base type
     */
    function _on_initialize()
    {
        if (! is_a($this->_type, 'midcom_helper_datamanager2_type_date'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} is not a select type or subclass thereoff, you cannot use the select widget with it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        return true;
    }

    /**
     * Adds a PEAR Date widget to the form
     */
    function add_elements_to_form()
    {
        $options = Array
        (
            'language' => $_MIDCOM->i18n->get_current_language(),
            'format' => $this->format,
            'minYear' => $this->minyear,
            'maxYear' => $this->maxyear,
            'addEmptyOption' => true,
        );
        $this->_form->addElement('date', $this->name, $this->_translate($this->_field['title']), $options);
    }

    /**
     * The default call parses the format string and retrieves the corresponding
     * information from the Date class of the type.
     */
    function get_default()
    {
        $defaults = Array();
        for($i = 0; $i < strlen($this->format); $i++)
        {
            switch ($this->format{$i})
            {
                case 'd':
                    $value = $this->_type->value->getDay();
                    if ($value == 0)
                    {
                        $defaults[$this->format{$i}] = '';
                    }
                    else
                    {
                        $defaults[$this->format{$i}] = $value;
                    }
                    break;

                case 'M':
                case 'm':
                case 'F':
                    $value = $this->_type->value->getMonth();
                    if ($value == 0)
                    {
                        $defaults[$this->format{$i}] = '';
                    }
                    else
                    {
                        $defaults[$this->format{$i}] = $value;
                    }
                    break;

                case 'Y':
                    $value = $this->_type->value->getYear();
                    if ($value == 0)
                    {
                        $defaults[$this->format{$i}] = '';
                    }
                    else
                    {
                        $defaults[$this->format{$i}] = $value;
                    }
                    break;

                case 'y':
                    $defaults[$this->format{$i}] = substr($this->_type->value->getYear(), -2);
                    break;

                case 'H':
                    $defaults[$this->format{$i}] = $this->_type->value->getHour();
                    break;

                case 'i':
                    $defaults[$this->format{$i}] = $this->_type->value->getMinute();
                    break;

                case 's':
                    $defaults[$this->format{$i}] = $this->_type->value->getSecond();
                    break;
            }
        }
        return Array($this->name => $defaults);
    }

    function sync_type_with_widget($results)
    {
        if (! $results[$this->name])
        {
            return;
        }

        foreach ($results[$this->name] as $formatter => $value)
        {
            switch ($formatter)
            {
                case 'd':
                    $this->_type->value->day = ($value == '' ? 0 : $value);
                    break;

                case 'M':
                case 'm':
                case 'F':
                    $this->_type->value->month = ($value == '' ? 0 : $value);
                    break;

                case 'y':
                    if ($value < 30)
                    {
                        $value += 2000;
                    }
                    else
                    {
                        $value += 1900;
                    }
                    // ** FALL THROUGH **

                case 'Y':
                    $this->_type->value->year = ($value == '' ? 0 : $value);
                    break;

                case 'H':
                    $this->_type->value->setHour($value);
                    break;

                case 'i':
                    $this->_type->value->setMinute($value);
                    break;

                case 's':
                    $this->_type->value->setSecond($value);
                    break;
            }
        }
    }

    /**
     * Renders the date using an ISO syntax
     */
    function render_content()
    {
        $with_date = false;
        $with_time = false;

        for($i = 0; $i < strlen($this->format); $i++)
        {
            switch ($this->format{$i})
            {
                case 'd':
                case 'M':
                case 'm':
                case 'F':
                case 'Y':
                case 'y':
                    $with_date = true;
                    break;

                case 'H':
                case 'i':
                case 's':
                    $with_time = true;
                    break;
            }
        }
        $format_string = '';
        if ($with_date)
        {
            $format_string .= '%Y-%m-%d';
        }
        if (   $with_date
            && $with_time)
        {
            $format_string .= ' ';
        }
        if ($with_time)
        {
            $format_string .= '%T';
        }

        echo $this->_type->value->format($format_string);
    }
}

?>
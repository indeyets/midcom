<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanger 2 JS date widget
 *
 * This widget is built around the JS date calendar, thus being mostly equivalent
 * with the DM1 date widget.
 *
 * This widget requires the date type or a subclass thereof.
 *
 * <b>Available configuration options:</b>
 *
 * - <i>bool show_time:</i> Boolean that controls, if the stored value is to be shown with or without
 *   the time-of-day. If omitted, 00:00:00 is assumed. Defaults to true.
 * - <i>int minyear:</i> Minimum Year available for selection, default see below.
 * - <i>int maxyear:</i> Maximum Year available for selection, default see below.
 *
 * <b>Default values for min/maxyear</b>
 *
 * If unspecified, it defaults to the 0-9999 range *unless* the base date type uses
 * the UNIXDATE storage mode, in which case 1970-2030 will be used instead.
 *
 * <b>JScript Calendar licence information</b>
 *
 * The DHTML Calendar, details and latest version at: http://dynarch.com/mishoo/calendar.epl
 *
 * This script is distributed under the GNU Lesser General Public License.
 * Read the entire license text here: http://www.gnu.org/licenses/lgpl.html
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_widget_jsdate extends midcom_helper_datamanager2_widget
{
    /**
     * Indicates wether the timestamp should be shown or not.
     *
     * @var bool
     */
    var $show_time = true;

    /**
     * Minimum Year available for selection.
     *
     * @var int
     */
    var $minyear = 0;

    /**
     * Maximum Year available for selection.
     *
     * @var int
     */
    var $maxyear = 9999;

    /**
     * Adapts the min/maxyear defaults if the base date is set to UNIXDATE storage.
     */
    function _on_configuring()
    {
        if (   is_a($this->_type, 'midcom_helper_datamanager2_type_date')
            && $this->_type->storage_type == 'UNIXDATE')
        {
            $this->minyear = 1970;
            $this->maxyear = 2030;
        }
    }

    /**
     * Validates the base type.
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
        
        if ($this->_initialize_dependencies)
        {
            $this->_add_external_html_elements();
        }
        
        return true;
    }

    /**
     * Adds the external HTML dependencies, both JS and CSS. A static flag prevents
     * multiple insertions of these dependencies.
     *
     * @access private
     */
    function _add_external_html_elements()
    {
        static $executed = false;

        if ($executed)
        {
            return;
        }

        $executed = true;

        $prefix = MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/jscript-calendar';
        $_MIDCOM->add_jsfile("{$prefix}/calendar.js");
        $lang = $_MIDCOM->i18n->get_current_language();
        $_MIDCOM->add_jsfile("{$prefix}/lang/calendar-{$lang}.js");
        $_MIDCOM->add_jsfile("{$prefix}/calendar-setup.js");


        $attributes = Array('rel' => 'stylesheet', 'type' => 'text/css');
        $attributes['href'] = "{$prefix}/calendar-win2k-1.css";
        $_MIDCOM->add_link_head($attributes);
    }

    /**
     * Generates the initscript for the current field.
     *
     * @return string The init script.
     */
    function _create_initscript()
    {
        if ($this->show_time)
        {
            $format = '%Y-%m-%d %H:%M:%S';
            $showstime = 'true';
        }
        else
        {
            $format = '%Y-%m-%d';
            $showstime = 'false';
        }

        $script = <<<EOT
<script type="text/javascript">
    Calendar.setup(
        {
            ifFormat    : "{$format}",
            daFormat    : "{$format}",
            showsTime   : {$showstime},
            align       : "Br",
            firstDay    : 1,
            timeFormat  : 24,
            showOthers  : true,
            singleClick : false,
            range       : [{$this->minyear}, {$this->maxyear}],
            inputField  : "{$this->_namespace}{$this->name}",
            button      : "{$this->_namespace}{$this->name}_trigger"
        }
    );
</script>
EOT;
        return $script;
    }

    /**
     * Adds a simple single-line text form element at this time.
     */
    function add_elements_to_form()
    {
        $this->_add_external_html_elements();

        $elements = Array();
        $this->_create_unfrozen_elements($elements);

        $this->_form->addGroup($elements, $this->name, $this->_translate($this->_field['title']), '', false);
    }

    /**
     * Create the unfrozen element listing.
     */
    function _create_unfrozen_elements(&$elements)
    {
        $attributes = Array
        (
            'class' => 'date',
            'id'    => "{$this->_namespace}{$this->name}",
        );
        $elements[] =& HTML_QuickForm::createElement('text', $this->name, '', $attributes);

        $attributes = Array
        (
            'class' => 'date_trigger',
            'id'    => "{$this->_namespace}{$this->name}_trigger",
        );
        $elements[] =& HTML_QuickForm::createElement('button', "{$this->name}_trigger", '...', $attributes);
        $elements[] =& HTML_QuickForm::createElement('static', "{$this->name}_initscript", '', $this->_create_initscript());
    }

    /**
     * Create the frozen element listing.
     */
    function _create_frozen_elements(&$elements)
    {
        $attributes = Array
        (
            'class' => 'date',
            'id'    => $this->name,
        );
        $element =& HTML_QuickForm::createElement('text', $this->name, '', $attributes);
        $element->freeze();
        $elements[] =& $element;
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
        $this->_create_unfrozen_elements($new_elements);

        $group =& $this->_form->getElement($this->name);
        $group->setElements($new_elements);
    }

    /**
     * The default call produces a simple text representation of the current date.
     */
    function get_default()
    {
        if ($this->_type->value->year == 0)
        {
            $this->_type->value->year = '0000';
        }
        if ($this->show_time)
        {
            $format = '%Y-%m-%d %H:%M:%S';
        }
        else
        {
            $format = '%Y-%m-%d';
        }
        return $this->_type->value->format($format);
    }

    /**
     * Tells the base date class instance to parse the value from the input field.
     */
    function sync_type_with_widget($results)
    {
        $this->_type->value = new Date($results[$this->name]);
    }

    /**
     * Renders the date in the ISO format.
     */
    function render_content()
    {
        if ($this->show_time)
        {
            $format = '%Y-%m-%d %H:%M:%S';
        }
        else
        {
            $format = '%Y-%m-%d';
        }
        echo $this->_type->value->format($format);
    }
}

?>
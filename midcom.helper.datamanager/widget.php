<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * The widget concept is the interface to the user.
 *
 * It displays both
 * a simple view of the stored data within the AIS and the form elements
 * required to modify this data. It also handles the interaction between
 * the forms and the PHP-representation of the data.
 *
 * A important point is that this is a base class for a class hierarchy
 * of datatypes. To make creating child classes a little bit easier and
 * less error_prone, the classes constructor has been moved to the
 * method _constructor, which can be overwritten by clients. The advantage
 * of this is that you can just call parent:: on this and don't need to
 * remember the name of the parent class to call its constructor. As PHP
 * automatically uses the parent classes constructor if non is defined,
 * it is enough to define the _constructor method in the subclasses.
 *
 * See also this PHP Manual Note:
 *
 * <i>
 * If you have a complex class hierarchy, I find that it's a good idea
 * to have a function constructor() in every class, and the 'real' php
 * constructor only exists in the absolute base class. From the basic
 * constructor, you call $this->constructor(). Now descendants simply
 * have to call parent::constructor() in their own constructor, which
 * eliminates complicated parent calls.
 * </i>
 *
 * The default behavior implemented in this class is sufficient for data
 * with a text-representation. It does not, however, implement the draw*
 * methods.
 *
 * @abstract Widget base class
 *
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_widget {

    /**
     * A copy of the field definition we use.
     *
     * @var Array
     * @access protected
     */
    var $_field;

    /**
     * A reference to our Datamanager.
     *
     * @var midcom_helper_datamanager
     * @access protected
     */
    var $_datamanager;

    /**
     * The name we should use for HTML Form elements, can also
     * be used as prefix.
     *
     * This consists of the Datamanager's form prefix, the string "field_" completed
     * with the name of the field. For ease of use it is also passed through
     * htmlspecialchars automatically.
     *
     * @var string
     * @access protected
     */
    var $_fieldname;

    /**
     * Our actual value as initialized by the datatype and (if applicable)
     * extracted from the HTTP POST information.
     *
     * @var mixed
     * @access protected
     */
    var $_value;

    /**
     * midcom.helper.datamanager L10n database reference.
     *
     * @var midcom_helper_services__i18n_l10n
     * @access protected
     */
    var $_l10n;

    /**
     * MidCOM core L10n database reference.
     *
     * @var midcom_helper_services__i18n_l10n
     * @access protected
     */
    var $_l10n_midcom;

    /**
     * This flag is true if the field is a required input field. The rendering code
     * should take this into account accordingly. This is used by the draw_widget_start
     * method.
     *
     * @see midcom_helper_datamanager_widget::draw_widget_start()
     * @var boolean
     */
    var $required;

    /**
     * This flag is true if the field is a required input field which was missing
     * in the last submit run. The rendering code should take this into account
     * accordingly. This is used by the draw_widget_start method.
     *
     * @see midcom_helper_datamanager_widget::draw_widget_start()
     * @var boolean
     */
    var $missingrequired;

    /**
     * Constructor
     *
     * This base-class constructor is used throughout the hierarchy and just
     * relays the constructor call to _construct. See there.
     *
     * @param midcom_helper_datamanager $datamanager The datamanager this type is assigned to.
     * @param Array $field The field definition to construct a datatype from.
     * @param mixed $defaultvalue The value to initialize the snippet with (used if no HTTP POST is available).
     * @see midcom_helper_datamanager_widget::_constructor()
     */
    function midcom_helper_datamanager_widget (&$datamanager, $field, $defaultvalue)
    {
        $this->required = false;
        $this->missingrequired = false;
        $this->_constructor($datamanager, $field, $defaultvalue);
    }

    /**
     * The constructor populates the internal members with a reference to the
     * datamanager we belong to, the definition of the field we use and the
     * default value we should use to initialize the widget's data.
     *
     * After doing this, it will call _read_formdata to catch any changes
     * submitted by the user, see there for details.
     *
     * Override this method if you need a custom class construction.
     *
     * @param midcom_helper_datamanager $datamanager The datamanager this type is assigned to.
     * @param Array $field The field definition to construct a datatype from.
     * @param mixed $defaultvalue The value to initialize the snippet with (used if no HTTP POST is available).
     * @access protected
     */
    function _constructor(&$datamanager, $field, $defaultvalue)
    {
        $this->_datamanager =& $datamanager;
        $this->_field = $field;
        $this->_fieldname = htmlspecialchars($this->_datamanager->form_prefix . "field_" . $field["name"]);
        $this->_value = $defaultvalue;

        $i18n =& $_MIDCOM->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("midcom.helper.datamanager");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");

        $this->_read_formdata();
    }

    /**
     * Reads the HTTP POST request data if available.
     *
     * It checks the $_REQUEST array whether there is anything related to our
     * widget. If yes, it will be read and stored in the widget.
     *
     * You should override this only if you cannot use the Request data
     * directly, as the default behavior is to copy it to the _value member.
     *
     * @access protected
     */
    function _read_formdata ()
    {
        if (array_key_exists($this->_fieldname, $_REQUEST))
        {
            $this->_value = $_REQUEST[$this->_fieldname];
        }
    }

    /**
     * Return the widget's value.
     *
     * You should override this if the _value representation of the widget
     * does not match the one returned to the type.
     *
     * @return mixed Value of the widget.
     */
    function get_value ()
    {
        return $this->_value;
    }

    /**
     * Set the value of the widget.
     *
     * You should override this if the _value representation of the widget
     * does not match the one returned to the type.
     *
     * @param mixed $value The new value to set.
     */
    function set_value ($value)
    {
        $this->_value = $value;
    }

    /**
     * When called, this method should display the current data without any
     * editing widget.
     *
     * You must override this member.
     *
     * @abstract Override with view output implementation.
     */
    function draw_view ()
    {
    }

    /**
     * This method draws the HTML-Form widgets required to edit the data. This
     * can be anything from a single form tag to a full list of them. Note, that
     * you will be automatically enclosed within the calls to draw_widget_start
     * and draw_widget_end.
     *
     * You must override this member.
     *
     * @see midcom_helper_datamanager_widget::draw_widget_start()
     * @see midcom_helper_datamanager_widget::draw_widget_end()
     * @abstract Override with widget output implementation.
     */
    function draw_widget ()
    {
    }


    /**
     * Evaluates the required and missingrequired field states and
     * returns an according list of CSS classes when one or both of these
     * variables is set. Required fields are reported as 'required',
     * while missing required fields are 'required missing'. If none
     * of these values is set, an empty string is returned.
     *
     * @return string The CSS classes indicating the required state.
     */
    function get_css_classes_required()
    {
        $css = '';
        if ($this->required)
        {
            $css .= 'required';
            if ($this->missingrequired)
            {
                $css .= ' missing';
            }
        }
        return $css;
    }

    /**
     * This is the default widget "introduction" code rendered before the actual
     * field code. It will open a <label> tag and display the heading.
     *
     * @see midcom_helper_datamanager_widget::draw_widget()
     * @see midcom_helper_datamanager_widget::draw_widget_end()
     * @see midcom_helper_datamanager_widget::draw_helptext()
     */
    function draw_widget_start()
    {
        $css = $this->get_css_classes_required();
        if ($css != '')
        {
            $css = "class='$css' ";
        }
        echo "<label {$css}for='{$this->_fieldname}' id='{$this->_fieldname}_label'>\n";
        $title = htmlspecialchars($this->_field['description']);
        echo "<span class='field_text'>  {$title}</span>";
        $this->draw_helptext();
        echo "\n";
    }

    /**
     * This is the default widget "introduction" code rendered before the actual
     * field code. It will close the <label> tag.
     *
     * @see midcom_helper_datamanager_widget::draw_widget()
     * @see midcom_helper_datamanager_widget::draw_widget_start()
     */
    function draw_widget_end()
    {
        echo "</label>\n";
    }

    /**
     * Helper function that renders the helptext if applicable. This is usually
     * appended directly after the label.
     *
     * @see midcom_helper_datamanager_widget::draw_widget_start()
     */
    function draw_helptext()
    {
        if (strlen($this->_field["helptext"]) > 0)
        {
            $text = htmlspecialchars($this->_field["helptext"]);
            echo "&nbsp;&nbsp;<img src='{$this->_datamanager->_url_help_icon}' alt='{$text}' title='{$text}'>";
        }
    }
}

?>
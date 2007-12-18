<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a widget to handle the selection schemas out of an existing
 * schma database.
 * 
 * This widget can only be used with text widgets. It requires to be linked to
 * a second field, the "link field", which contains the path to the schema 
 * database entered by the user. If this field is blank, the default schema
 * database from the configuration will be used.
 * 
 * Ultimately, this widget is usful almost only within Datamanager driven component
 * configuration screeens.
 * 
 * This widget inherits from the regular select widget, so the documentation of
 * this class is also valid here, unless noted otherwise.
 * 
 * <b>Configuration parameters:</b>
 * 
 * <b>widget_select_choices:</b> While this option is mandatory for the regular select
 * widget, its content is auto-generated from the selected schema database. You 
 * <i>cannot configure</i> this value therefore, anything passed in this field will
 * therefore be <i>silently overwritten</i>.
 * 
 * <b>widget_schemaselect_linkto:</b> Contains the name of the field from the current
 * schema which will contain the path of the schema database. As usual, you 
 * can either directly write the path to a snippet or use the file: prefix to refer
 * to a MidCOM library file. This field is mandatory.
 * 
 * <b>widget_schemselect_default_schemapath:</b> This is the path to the schema 
 * database that should be used in case the user leaves the linked field empty.
 * This field is mandatory.
 * 
 * <b>Sample configuration</b>
 * 
 * <pre>
 * "schema_picture" => Array (
 * 	   "description" => "Schema to use for picture records",
 *     "datatype" => "text",
 *     "location" => "config",
 *     "config_domain" => "net.siriux.photos",
 *     "config_key" => "schema_picture",
 *     "widget" => "schemaselect",
 *     "widget_schemaselect_linkto" => "schemadb",
 *     "widget_schemaselect_default_schemapath" => "file:/net/siriux/photos/config/schemadb_default.dat",
 * ),
 * </pre>
 * 
 * <b>CSS Styles in use by the Widget</b>
 *
 * None, except those of widget_select.
 * 
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_widget_schemaselect extends midcom_helper_datamanager_widget_select 
{

    /**
     * Field that refers to the schema database.
     * 
     * @var string
     * @access private
     */
    var $_linkto;
    
    /**
     * Default schema database to use in case linked field is empty.
     * 
     * @var string
     * @access private
     */
    var $_schemapath;

    /**
     * The constructor will clear the choices passed to the field just in 
     * case somebody will try to use it.
     */
    function _constructor (&$datamanager, $field, $defaultvalue) 
    {
        $field['widget_select_choices'] = Array();
        
        parent::_constructor ($datamanager, $field, $defaultvalue);
        
        $this->_linkto = $field['widget_schemaselect_linkto'];
        $this->_default_schemapath = trim($field['widget_schemaselect_default_schemapath']);
    }
    
    /**
     * This helper function will load the referenced schema database and creates a 
     * simple key/value list of all schemas in that file. This list is assigned
     * to $this->_choices.
     * 
     * Note, that this function can only be called if the datamanager has been
     * initialized successfully. This prevents usage during the constructor, in which
     * the content of the linked field is yet unknown.
     * 
     * @see midcom_helper_datamanager_widget_select::_choices
     * @access private
     */
    function _get_schema() 
    {
        if (! array_key_exists($this->_linkto, $this->_datamanager->data))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "The field {$this->_linkto} does not seem to be a valid datafield of the current schema.");
            // This will exit()
        }
        
        $path = trim($this->_datamanager->data[$this->_linkto]);
        if (strlen($path) == 0)
        {
            $path = $this->_default_schemapath;
        }
        if (strlen($path) == 0)
        {
            return Array();
        }
        
        $data = midcom_get_snippet_content($path);
        eval ("\$schemadb = Array(\n{$data}\n);");
        $this->_choices = Array();
        $this->_choices[''] = $this->_l10n_midcom->get('default setting');
        foreach ($schemadb as $key => $value)
        {
            $this->_choices[$key] = $value['description'];
        }
    }
    
    /**
     * Updates the list of choices and calles the parent.
     * 
     * @see midcom_helper_datamanager_widget_schemaselect::_get_schema()
     */
    function draw_view () 
    {
        $this->_get_schema();
        parent::draw_view();
    }
    
    /**
     * Updates the list of choices and calles the parent.
     * 
     * @see midcom_helper_datamanager_widget_schemaselect::_get_schema()
     */
    function draw_widget () 
    {
        $this->_get_schema();
        parent::draw_widget();
    }
}


?>
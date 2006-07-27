<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanger 2 ajax Form Manager class.
 *
 * This class uses special operations to allow for ajax forms.
 *
 * The form rendering is done using the widgets and is based on HTML_QuickForm.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_formmanager_ajax extends midcom_helper_datamanager2_formmanager
{
    /**
     * Latest exit code
     */
    var $_exitcode = null;
     
    /**
     * Initializes the Form manager with a list of types for a given schema.
     *
     * @param midcom_helper_datamanager2_schema $schema The schema to use for processing. This
     *     variable is taken by reference.
     * @param Array $types A list of types matching the passed schema, used as a basis for the
     *     form types. This variable is taken by reference.
     */
    function midcom_helper_datamanager2_formmanager_ajax(&$schema, &$types)
    {
        parent::midcom_helper_datamanager2_formmanager($schema, $types);
    }

    /**
     * This function will create all widget objects for the current schema. It will load class
     * files where neccessary (using require_once), and then create a set of instances
     * based on the schema.
     *
     * @param string $name The name of the field for which we should load the widget.
     * @return bool Indicating success
     * @access protected
     */
    function _load_widget($name)
    {
        $config = $this->_schema->fields[$name];
        $filename = MIDCOM_ROOT . "/midcom/helper/datamanager2/widget/{$config['widget']}.php";
        $classname = "midcom_helper_datamanager2_widget_{$config['widget']}";
        require_once($filename);

        $this->widgets[$name] = new $classname();
        if (! $this->widgets[$name]->initialize($name, $config['widget_config'], $this->_schema, $this->_types[$name], $this->namespace, true))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to initialize the widget for {$name}, see the debug level log for full details, this field will be skipped.",
                MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        return true;
    }

    /**
     * ...
     *
     * @param name The name of the form. This defaults to the name of the currently active component, which should
     *     suffice in most cases.
     * @return bool Indicating success.
     */
    function initialize($name = null)
    {
        /* The idea:
         *
         * First, we construct the regular foorm, to allow for a call to process_form.
         * In process_form, we then process the page switch. There we will have to
         * reconstruct the formn with the new page elements, along with all hidden
         * values. The trick here is to rebuild the form with all unseen fields added
         * as hidden elements in a way so that the reconstructed form can create
         * its widgets directly from it.
         */

        return parent::initialize($name);
    }

    /**
     * This call will render the form in AJAX-readable fashion
     */
    function display_form($form_identifier = 'ajax')
    {
        $_MIDCOM->cache->content->content_type('text/xml');
        $_MIDCOM->header('Content-type: text/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>' . "\n";

        $exitcode = '';
        if (!is_null($this->_exitcode))
        {
            $exitcode = " exitcode=\"{$this->_exitcode}\"";
        }
                
        echo "<form id=\"{$form_identifier}\"{$exitcode} editable=\"true\">\n";    
        
        if (count($this->form->_errors) > 0)
        {
            foreach ($this->form->_errors as $field => $error)
            {
                echo "<error field=\"{$field}\">{$error}</error>\n";
            }
        }    
        
        foreach ($this->widgets as $name => $copy)
        {
            // TODO: Add support for other datatypes as we go
            switch (get_class($this->_types[$name]))
            {
                case 'midcom_helper_datamanager2_type_text':
                case 'midcom_helper_datamanager2_type_select':
                case 'midcom_helper_datamanager2_type_date':
                    echo "<field name=\"{$name}\"><![CDATA[\n";
                    $element =& $this->form->getElement($name);
                    echo $element->toHtml();
                    echo "]]></field>\n";
                    break;
            }
        }
        echo "</form>\n";
    }

    /**
     * This call will render the contents in AJAX-readable fashion
     */    
    function display_view($form_identifier = 'ajax', $new_form_identifier = null)
    {
        $_MIDCOM->cache->content->content_type('text/xml');
        $_MIDCOM->header('Content-type: text/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>' . "\n";
        
        $exitcode = '';
        if (!is_null($this->_exitcode))
        {
            $exitcode = " exitcode=\"{$this->_exitcode}\"";
        }
        
        $new_identifier = '';
        if (!is_null($new_form_identifier))
        {
            $new_identifier = " new_identifier=\"{$new_form_identifier}\"";
        }
                
        echo "<form id=\"{$form_identifier}\"{$exitcode}{$new_identifier}>\n";    
        
        if (count($this->form->_errors) > 0)
        {
            foreach ($this->form->_errors as $field => $error)
            {
                echo "<error field=\"{$field}\">{$error}</error>\n";
            }
        }        
        
        foreach ($this->widgets as $name => $widget)
        {

            // TODO: Add support for other datatypes as we go
            switch (get_class($this->_types[$name]))
            {
                case 'midcom_helper_datamanager2_type_text':
                case 'midcom_helper_datamanager2_type_select':
                case 'midcom_helper_datamanager2_type_date':
                    echo "<field name=\"{$name}\"><![CDATA[\n";
                    echo $widget->render_content();
                    echo "]]></field>\n";
                    break;
            }
        }
        echo "</form>\n";    
    }

    /**
     * ...
     *
     * @return string One of 'editing', 'save', 'next', 'previous' and 'cancel'
     */
    function process_form()
    {
        $this->_exitcode = parent::process_form();

        // Process next/previous

        return $this->_exitcode;
    }
}
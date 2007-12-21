<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 table type.
 *
 * <b>Configuration options:</b>
 * <i>callback_name - name of the class to be used for populating the rows and colums.</i>
 *
 * The callback class must support the following methods:
 *
 * <code>
 * class table_callback {
 * array get_headers () ;
 * array get_rows() ; //Returns an array containing the values for each row.
 * boolean set_rows($values);
 * void set_type(&$midcom_helper_datamanager2_type_table ) ;
 *
 * Array with headernames in the same order as the row colums.
 *
 */
class midcom_helper_datamanager2_type_table extends midcom_helper_datamanager2_type
{

    /**
     * The name of or reference to the callback object to be used.
     * @var string
     * @access private
     */
    var $callback = null;


    /**
     * A list of rows for the table
     */
    var $rows = array();
    /**
     * Headers for the rows
     * @var array
     * @access public
     */
    var $headers = array('domain', 'name', 'value', 'delete');

       /**
     * Set this to true if you want the keys to be exported to the csv dump instead of the
     * values. Note, that this does not affect import, which is only available with keys, not
     * values.
     * <b>NB:</b>
     * This option is not supported at the moment.
     *
     * @var bool
     * @access public
     */
    var $csv_export_key = false;

    /**
     * In case the options are returned by a callback, this member holds the callback
     * instance.
     * @var object
     * @access public
     */
    var $_callback = null;



    /**
     * Initialize the class, if necessary, create a callback instance, otherwise
     * validate that an option array is present.
     */
    function _on_initialize()
    {

        if (is_string($this->callback))
        {
            $classname = $this->callback;

            // Try auto-load.
            $path = MIDCOM_ROOT . '/' . str_replace('_', '/', $classname) . '.php';
            if (! file_exists($path))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Auto-loading of the class {$classname} from {$path} failed: File does not exist.", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            require_once($path);
            if (! class_exists($classname))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The class {$classname} was defined as option callback for the field {$this->name} but did not exist.", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $this->_callback = new $classname($this->option_callback_arg);
            $this->_callback->set_type(&$this);

            return true;
        }
        elseif (is_object($this->callback))
        {
            $this->_callback = &$this->callback;

            return true;
        }
        // todo check the headers and rows
        return false;
    }


    /**
     * Converts storage format to live format, all invalid keys are dropped, and basic validation
     * is done to ensure constraints like allow_multiple are met.
     */
    function convert_from_storage ($source)
    {

        // reset the rows.
        $this->rows = $this->_callback->get_rows();
    }

    /**
     *
     * @return Array The storage information.
     */
    function convert_to_storage()
    {
        $rows = $this->rows;
        $this->rows = array();
        $this->_callback->set_rows($this->rows);
        $this->rows = $this->_callback->get_rows();

        return;

    }

    /**
     * CSV conversion works from the storage representation, converting the arrays
     * into simple text lists.
     */
    function convert_from_csv ($source)
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "This operation is not supported");
    }

    /**
     * CSV conversion works from the storage representation, converting the arrays
     * into simple text lists.
     */
    function convert_to_csv()
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "This operation is not supported");
    }

    /**
     * The validateion callback ensures that we dont't have an array or an object
     * as a value, which would be wrong.
     *
     * @return bool Indicating validity.
     */
    function _on_validate()
    {
        return true;
    }

    function convert_to_html()
    {
        $table = "<table border='0' cellspacing='0' ><tr>";
        foreach ($this->headers as $header )
        {
            $table .= "<td>{$header}</td>\n";
        }
        $table .= "</tr>\n";
        foreach ($this->rows as $key => $row)
        {
            $table .= "<tr>\n";
            foreach ($row as $row_key => $value )
            {
                $table .= "<td>{$value}</td>\n";
            }
        }
        $table .= "</tr>\n";

        return $table;
    }

}

?>
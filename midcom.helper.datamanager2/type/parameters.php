<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 parameters type. This allows the editing of all parameters of 
 * the storage object.
 * 
 * <b>Configuration options:</b>
 * <i>headers</i>
 * Array with headernames in the same order as the row columns.
 *  
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_type_parameters extends midcom_helper_datamanager2_type
{

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
     * @var boolean
     * @access public
     */
    var $csv_export_key = false;

    /**
     * Initialize the class, if necessary, create a callback instance, otherwise
     * validate that an option array is present.
     */
    function _on_initialize()
    {
        // todo check the headers and rows
        return true;
    }


    /**
     * Converts storage format to live format, all invalid keys are dropped, and basic validation
     * is done to ensure constraints like allow_multiple are met.
     */
    function convert_from_storage ($source)
    {
        if ( $this->storage->object === null)
        {
            return ;
        }
        // reset the rows.
        $this->rows = array();
        foreach ($this->storage->object->list_parameters() as $domain => $name) 
        {
            $this->rows[] = array (0 => $domain, 
                                   1 => key($name), 
                                   2 => $name[key($name)],
                                   3 => 0);
        }
    }

    /**
     *
     * @return Array The storage information.
     */
    function convert_to_storage()
    {
        /**
         * Row indexes:
         * 0 = domain
         * 1 = name
         * 2 = value
         */
        
        $rows = $this->rows;
        $this->rows = array();
        foreach ($rows as $key => $row ) 
        {
            if (array_key_exists(3 , $row ) && $row[3] == 1) 
            {
                $this->storage->object->delete_parameter( $row[0],$row[1]);
                unset ($this->rows[$key]);    
            } // only update parameters that do not have empty names or domains. 
            else if (trim($row[0]) != '' && trim($row[1]) != '') 
            {
                
                if (! $this->storage->object->set_parameter(
                    $row[0],
                    $row[1],
                    $row[2]
                )) 
                {
                    echo "Could not update parameter {$row[0]} {$row[1]}!";
                }
                
            } 
        }
        
        $this->convert_from_storage(true);
        return;
        
    }

    /**
     * CSV conversion works from the storage representation, converting the arrays
     * into simple text lists.
     */
    function convert_from_csv ($source)
    {
        $source = explode(',', $source);
        $this->convert_from_storage($source);
    }

    /**
     * CSV conversion works from the storage representation, converting the arrays
     * into simple text lists.
     */
    function convert_to_csv()
    {
        if ($this->csv_export_key)
        {
            $data = $this->convert_to_storage();
            if (is_array($data))
            {
                return implode(',', $data);
            }
            else
            {
                return $data;
            }
        }
        else
        {
            $selection = Array();
            foreach($this->selection as $item)
            {
                $selection[] = $this->get_name_for_key($item);
            }
            if ($this->others)
            {
                $values = array_merge($selection, Array($this->others));
            }
            else
            {
                $values = $selection;
            }
            return implode($values, ', ');
        }
    }

    /**
     * The validation callback ensures that we dont't have an array or an object
     * as a value, which would be wrong.
     *
     * @return boolean Indicating validity.
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
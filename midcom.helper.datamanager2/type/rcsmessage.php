<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: rcsmessage.php 10966 2007-06-15 07:00:37Z adrenalin $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 Simple rcsmessage datatype. The rcsmessage value encaspulated by this type is
 * passed as-is to the RCS service, no specialieties done, just a string.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_type_rcsmessage extends midcom_helper_datamanager2_type
{
    /**
     * The current string encaspulated by this type.
     *
     * @var string
     * @access public
     */
    var $value = '';

    function convert_from_storage ($source)
    {
        if (method_exists($this->storage->object, 'get_rcs_message'))
        {    
            $this->value = $this->storage->object->get_rcs_message();
        }
        // Nullstorage doesn't have RCS
    }

    function convert_to_storage()
    {
        if (method_exists($this->storage->object, 'set_rcs_message'))
        {
            $this->storage->object->set_rcs_message($this->value);
        }
        // Nullstorage doesn't have RCS
    }

    function convert_from_csv ($source)
    {
        $this->value = $source;
    }

    function convert_to_csv()
    {
        return $this->value;
    }

    function convert_to_html()
    {
        return $this->value;
    }
}

?>
<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager boolean data type. 
 * 
 * Implements boolean handling and displaying
 * 
 * <b>Available configuration options</b>
 * 
 * -<i>string true_text:</i> an url of an custom image for displaying a true value
 * 
 * -<i>string false_text:</i> an url of an custom image for displaying a false value
 * 
 * @package midcom_helper_datamanager
 */

class midcom_helper_datamanager_type_boolean extends midcom_helper_datamanager_type_baseclass
{
    /**
     * Current value of this type
     *
     * @var boolean
     * @access public
     */
    public $value = false;
    
    /**
     * Text presentation of the true value
     *
     * @var string
     * @access public
     */
    public $true_text = null;
    
    /**
     * Text presentation of the true value
     *
     * @var string
     * @access public
     */
    public $false_text = null;
    
    /**
     * Function ensures that value that comes from storage
     * is converted to a boolean
     * 
     * @todo: Should we check that coming value is a boolean?
     *
     * @param boolean $source
     */
    public function convert_from_storage($source)
    {
        if ($source)
        {
            $this->value = true;
        }
        else
        {
            $this->value = false;
        }
    }
    
    public function convert_to_storage()
    {
        // Only true value is returned as true. All others are false     

        if ($this->value)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function convert_to_html()
    {
        if ($this->value)
        {
            if (is_null($this->true_text))
            {
                $src = MIDCOM_STATIC_URL . '/stock-icons/16x16/stock_mark.png';
            }
            else
            {
                $src = $this->true_text;
            }
        }
        else
        {
            if (is_null($this->false_text))
            {
                $src = MIDCOM_STATIC_URL . '/stock-icons/16x16/cancel.png';
            }
            else
            {
                $src = $this->false_text;
            }
        }
        return "<img src='{$src}' />";
    }
    /**
     * Validation ensures that there is really a boolean value
     *
     * @return boolean Indicating validity.
     */
    public function on_validate()
    {
        if (is_bool($this->value))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

?>

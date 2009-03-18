<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager Simple text data type. The text value encapsulated by this type is
 * passed as-is to the storage layers, no specialties done, just a string.
 *
 * <b>Available configuration options:</b>
 *
 * - <i>int maxlength:</i> The maximum length of the string allowed for this field.
 *   This includes any newlines, which account as at most two characters, depending
 *   on the OS. Set this to 0 (the default) to have unlimited input.
 * - <i>string output_mode:</i> This option controls how convert_to_html operates. The
 *   default 'specialchars' will just pass the data entered in the field through
 *   htmlspecialchars(). See below for a full option listing.
 *
 * Available output modes:
 *
 * - 'html': No conversion is done.
 * - 'specialchars': The value is run through htmlspecialchars() (the default).
 * - 'midgard_f': Uses the Midgard :f formatter.
 * - 'midgard_F': Uses the Midgard :F formatter.
 *
 * @package midcom_helper_datamanager
 */
class com_rohea_account_datamanager_type_array extends midcom_helper_datamanager_type_baseclass
{
    /**
     * The current string encapsulated by this type.
     *
     * @var string
     */
    public $value = '';

    private $valuearray = array();

    /**
     * Maximum length of the string encapsulated by this type. 0 means no limit.
     * This is checked during validation.
     *
     * @param int
     */
    public $maxlength = 0;

    /**
     * Sets output formatting. See class introduction.
     *
     * @var string
     */
    public $output_mode = 'specialchars';

    public function on_initialize()
    {
        return true;
//        $this->valuearray = unserialize($this->storage->
    }

    public function convert_from_storage($source)
    {
        $this->valuearray = unserialize($source);
    }

    public function convert_to_storage()
    {
        // Normalize line breaks to the UNIX format
        if(! is_array($this->valuearray))
        {
            $this->valuearray = array();
        }
        
        if( in_array($this->value, $this->valuearray))
        {
            $this->value = serialize($this->valuearray);
            return $this->value;
        }
        $this->valuearray[] = $this->value;
        $this->value = serialize($this->valuearray);
        return $this->value;
    }
    
    public function convert_to_html()
    {
        switch ($this->output_mode)
        {
            case 'html':
                return $this->value;

            case 'code':
                return '<pre style="overflow:auto">'.htmlspecialchars($this->value).'</pre>';

            case 'specialchars':
                return htmlspecialchars($this->value);

            case 'midgard_f':
                return mgd_format($this->value, 'f');

            case 'midgard_F':
                return mgd_format($this->value, 'F');
        }
    }

    /**
     * The validation callback ensures that we don't have an array or an object
     * as a value, which would be wrong.
     *
     * @return boolean Indicating validity.
     */
    protected function on_validate()
    {
        return true;
    }
}

?>
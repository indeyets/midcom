<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** We need the PEAR Date class. See http://pear.php.net/package/Date/docs/latest/ */
require_once('Date.php');

/**
 * Datamanager 2 date datatype. The type is based on the PEAR date types
 * types.
 *
 * <b>Available configuration options:</b>
 *
 * - <i>string storage_type:</i> Defines the storage format of the date. The default
 *   is 'ISO', see below for details.
 *
 * <b>Available storage formats:</b>
 *
 * - ISO: YYYY-MM-DD HH:MM:SS
 * - ISO_DATE: YYYY-MM-DD
 * - ISO_EXTENDED: YYYY-MM-DDTHH:MM:SS(Z|[+-]HH:MM)
 * - ISO_EXTENDED_MICROTIME: YYYY-MM-DDTHH:MM:SS.S(Z|[+-]HH:MM)
 * - UNIXTIME: Unix Timestamps (seconds since epoch)
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_type_date extends midcom_helper_datamanager2_type
{
    /**
     * The current date encapsulated by this type.
     *
     * @var Date
     * @link http://pear.php.net/package/Date/docs/latest/
     */
    var $value = null;

    /**
     * The storage type to use, see the class introduction for details.
     *
     * @var string
     */
    var $storage_type = 'ISO';

    /**
     * Initialize the value with an empty Date class.
     */
    function _on_configuring($config)
    {
        $this->value = new Date();
    }

    /**
     * This function uses the PEAR Date constructor to handle the conversion.
     * It should be able to deal with all three storage variants transparently.
     *
     * @param mixed $source The storage data structure.
     */
    function convert_from_storage ($source)
    {
        if (! $source)
        {
            // Get some way for really undefined dates until we can work with null
            // dates everywhere midgardside.
            $this->value = new Date('00-00-0000 00:00:00');
            $this->value->day = 0;
            $this->value->month = 0;
        }
        else
        {
            $this->value = new Date($source);
        }
    }

    /**
     * Converts Date object to storage representation.
     *
     * @todo Move to getDate where possible.
     * @return string The string representation of the Date according to the
     *     storage_type.
     */
    function convert_to_storage()
    {
        switch ($this->storage_type)
        {
            case 'ISO':
                if ($this->is_empty())
                {
                    return '0000-00-00 00:00:00';
                }
                else
                {
                    return $this->value->format('%Y-%m-%d %T');
                }

            case 'ISO_DATE':
                if ($this->is_empty())
                {
                    return '0000-00-00';
                }
                else
                {
                    return $this->value->format('%Y-%m-%d');
                }

            case 'ISO_EXTENDED':
            case 'ISO_EXTENDED_MICROTIME':
                if ($this->is_empty())
                {
                    return '0000-00-00T00:00:00.0';
                }
                else
                {
                    return str_replace(',', '.', $this->value->format('%Y-%m-%dT%H:%M:%s%O'));
                }

            case 'UNIXTIME':
                if ($this->is_empty())
                {
                    return 0;
                }
                else
                {
                    return $this->value->getTime();
                }

            default:
                $_MIDCOM->generate_error("Invalid storage type for the Datamanager Date Type: {$this->storage_type}");
                // This will exit.
        }
    }

    /**
     * CVS conversion is mapped to regular type conversion.
     */
    function convert_from_csv ($source)
    {
        $this->convert_from_storage($source);
    }

    /**
     * CVS conversion is mapped to regular type conversion.
     */
    function convert_to_csv()
    {
        return $this->convert_to_storage();
    }

    function convert_to_html()
    {
        if ($this->is_empty())
        {
            return '';
        }
        else
        {
            if ($this->storage_type == 'ISO_DATE')
            {
                $format = $this->_l10n_midcom->get('short date');
            }
            else
            {
                // FIXME: This is not exactly an elegant way to do this
                if (   array_key_exists('show_time', $this->storage->_schema->fields[$this->name]['widget_config'])
                    && !$this->storage->_schema->fields[$this->name]['widget_config']['show_time'])
                {
                    $format = $this->_l10n_midcom->get('short date');
                }
                else
                {
                    $format = $this->_l10n_midcom->get('short date') . ' %T';
                }
            }
            return htmlspecialchars($this->value->format($format));
        }
    }

    /**
     * Tries to detect whether the date value entered is empty in terms of the Midgard
     * core. For this, all values are compared to zero, if all tests succeed, the date
     * is considered empty.
     *
     * @return boolean Indicating Emptyness state.
     */
    function is_empty()
    {
        return
        (
               $this->value->year == 0
            && $this->value->month == 0
            && $this->value->day == 0
            && $this->value->hour == 0
            && $this->value->minute == 0
            && $this->value->second == 0
        );
    }
}

?>
<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 Simple boolean datatype.
 *
 * Storage is done in number format, where 0 is false and 1 is true. When reading
 * the storage, the PHP boolean conversion is used, so other representations (for legacy
 * compatibility) are recognized as well.
 *
 * <b>Available configuration options:</b>
 *
 * - <i>string true_text:</i> The text displayed if the value of the type is true. This defaults
 *   to a graphic "checked" icon. Must be valid for usage in convert_to_html.
 * - <i>string false_text:</i> The text displayed if the value of the type is false. This defaults
 *   to a graphic "not checked" icon. Must be valid for usage in convert_to_html.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_type_boolean extends midcom_helper_datamanager2_type
{
    /**
     * The current string encapsulated by this type.
     *
     * @var bool
     * @access public
     */
    var $value = false;

    /**
     * The text displayed if the value of the type is true. This defaults to
     * a graphic "checked" icon. Must be valid for usage in convert_to_html.
     *
     * @var string
     * @access public
     */
    var $true_text = null;

    /**
     * The text displayed if the value of the type is false. This defaults to
     * a graphic "not checked" icon. Must be valid for usage in convert_to_html.
     *
     * @var string
     * @access public
     */
    var $false_text = null;

    function convert_from_storage ($source)
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

    function convert_to_storage()
    {
        if ($this->value)
        {
            return 1;
        }
        else
        {
            return 0;
        }
    }

    function convert_from_csv ($source)
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

    function convert_to_csv()
    {
        if ($this->value)
        {
            return '1';
        }
        else
        {
            return '0';
        }
    }

    /**
     * The HTML representation returns either the configured texts or a
     * checked / unchecked icon if left on defaults.
     */
    function convert_to_html()
    {
        if ($this->value)
        {
            if ($this->true_text)
            {
                $result = $this->true_text;
            }
            else
            {
                $src = MIDCOM_STATIC_URL . '/stock-icons/16x16/stock_mark.png';
                $result = "<img src='{$src}'/>";
            }
        }
        else
        {
            if ($this->false_text)
            {
                $result = $this->false_text;
            }
            else
            {
                $src = MIDCOM_STATIC_URL . '/stock-icons/16x16/cancel.png';
                $result = "<img src='{$src}'/>";
            }
        }
        return $result;
    }


}

?>
<?php

/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Plain text datatype.
 *
 * It will store and retrieve the value as-is, it just adds a basic
 * configuration to the basic datatype class.
 *
 * <b>Default Parameters</b>
 *
 * - <i>Location</i>: attachment
 * - <i>widget</i>: text
 *
 */

class midcom_helper_datamanager_datatype_text extends midcom_helper_datamanager_datatype
{

    /**
     * Constructor with default configuration.
     */
    function midcom_helper_datamanager_datatype_text (&$datamanager, &$storage, $field)
    {
        if (!array_key_exists("location", $field))
        {
            $field["location"] = "attachment";
        }
        if (!array_key_exists("widget", $field))
        {
            $field["widget"] = "text";
        }

        parent::_constructor ($datamanager, $storage, $field);
    }
}

?>
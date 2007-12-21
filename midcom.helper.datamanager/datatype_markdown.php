<?php

/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Markdown datatype.
 *
 * This datatype stores content as is, and renders it for viewing using
 * the Markdown system. See more information on the Markdown website in
 * http://daringfireball.net/projects/markdown/
 *
 * <b>Default Parameters</b>
 *
 * - <i>Location</i>: attachment
 * - <i>widget</i>: text
 *
 */


class midcom_helper_datamanager_datatype_markdown extends midcom_helper_datamanager_datatype
{

    function midcom_helper_datamanager_datatype_markdown (&$datamanager, &$storage, $field)
    {

        if (!array_key_exists("location", $field))
        {
            $field["location"] = "attachment";
        }

        $field["widget"] = "markdown";
        $field["widget_text_inputstyle"] = "longtext";

        // Include the Markdown library
        $_MIDCOM->load_library('net.nehmer.markdown');

        parent::_constructor ($datamanager, $storage, $field);

    }

    /**
     * Return contents in Markdown-processed format
     */
    function get_value()
    {
        return Markdown($this->_value);
    }

    function _get_widget_default_value ()
    {
        return $this->_value;
    }
}

?>
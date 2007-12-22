<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a widget for editing Markdown content. It is mostly the same as the
 * regular text widget
 *
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_widget_markdown extends midcom_helper_datamanager_widget_text {

    // Documented in base class, nothing special here.
    function draw_view () {
        switch ($this->_inputstyle)
        {
            case "shorttext":
            case "longtext":
                ?><div><?echo Markdown($this->_value);?></div><?php
                break;

            case "longtext_preformatted":
                ?><pre><?echo Markdown($this->_value);?></pre><?php
                break;
        }
    }
}
?>

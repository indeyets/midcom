<?php
/**
 * @package de.linkm.fontrenderer
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * fontrenderer MidCOM interface class.
 *
 * @package de.linkm.fontrenderer
 */
class de_linkm_fontrenderer_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files.
     */
    function __construct()
    {
        parent::__construct();

        $this->_component = 'de.linkm.fontrenderer';
        $this->_autoload_files = Array('main.php');
        $this->_purecode = true;
    }
}

?>
<?php
/**
 * @package fi.hut.loginbroker
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: navigation.php 12882 2007-10-18 15:06:54Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Stub navigation interface class
 *
 * See the individual member documentations about special NAP options in use.
 *
 * @package fi.hut.loginbroker
 */

class fi_hut_loginbroker_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function fi_hut_loginbroker_navigation()
    {
        parent::__construct();
    }

    function get_leaves()
    {
        $leaves = array();
        return $leaves;
    }
}
?>
<?php
/**
 * @package midcom.helper.dm2config
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 2789 2006-01-27 18:03:21Z torben $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 Component Interface Class. This is a pure code library.
 *
 * @package midcom.helper.dm2config
 */
class midcom_helper_dm2config_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing needs to be done, besides connecting to the parent class constructor.
     */
    function midcom_helper_dm2config_interface()
    {
        parent::__construct();
        $this->_component = 'midcom.helper.dm2config';
    }
}
?>
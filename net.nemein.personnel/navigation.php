<?php
/**
 * @package net.nemein.personnel
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Person viewer NAP interface class.
 * 
 * Does not deliver any leaves as person address listings can grow quite big.
 * 
 * @package net.nemein.personnel
 */
class net_nemein_personnel_navigation extends midcom_baseclasses_components_navigation
{
    function net_nemein_personnel_navigation() 
    {
        parent::midcom_baseclasses_components_navigation();
    }

}

?>
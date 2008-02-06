<?php
/**
 * @package net.nemein.organizations
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: navigation.php 3582 2006-06-08 14:38:52Z torben $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Group viewer NAP interface class.
 * 
 * Does not deliver any leaves as group address listings can grow quite big.
 * 
 * @package net.nemein.organizations
 */
class net_nemein_organizations_navigation extends midcom_baseclasses_components_navigation
{
    function net_nemein_organizations_navigation() 
    {
        parent::midcom_baseclasses_components_navigation();
    }

}

?>
<?php

/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Forum NAP interface class.
 * 
 * @package net.nemein.discussion
 */

class net_nemein_discussion_navigation extends midcom_baseclasses_components_navigation
{
    function net_nemein_discussion_navigation() 
    {
        parent::midcom_baseclasses_components_navigation();
    }


    function get_leaves() 
    {
        // At the moment we have no leaves to show
        $leaves = array();
        return $leaves;
    }
} 

?>
<?php

/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Forum AIS interface class.
 * 
 * @package net.nemein.discussion
 */
class net_nemein_discussion_admin extends midcom_baseclasses_components_request_admin
{
    function net_nemein_discussion_admin($topic, $config) 
    {
         parent::__construct($topic, $config);
    }
}
?>
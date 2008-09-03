<?php

/**
 * @package net.nemein.simpledb
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * SimpleDB AIS interface class.
 * 
 * @package net.nemein.simpledb
 */
class net_nemein_simpledb_admin extends midcom_baseclasses_components_request_admin
{
    function __construct($topic, $config) 
    {
         parent::__construct($topic, $config);
    }
}
?>
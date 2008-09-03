<?php
/**
 * @package net.nemein.downloads
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Download manager Admin interface class.
 * 
 * @package net.nemein.downloads
 */
class net_nemein_downloads_admin extends midcom_baseclasses_components_request_admin
{
    function net_nemein_downloads_admin($topic, $config) 
    {
        parent::__construct($topic, $config);
    }
}
?>
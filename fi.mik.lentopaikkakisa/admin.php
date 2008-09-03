<?php
/**
 * @package fi.mik.lentopaikkakisa
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Forum AIS interface class.
 *
 * @package fi.mik.lentopaikkakisa
 */
class fi_mik_lentopaikkakisa_admin extends midcom_baseclasses_components_request_admin
{
    function fi_mik_lentopaikkakisa_admin($topic, $config)
    {
         parent::__construct($topic, $config);
    }
}
?>
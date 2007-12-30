<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:privilege.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Dummy DBA object (needed by replicator) (soon maybe less dummy ??)
 * @package midcom
 */
class midcom_core_privilege_dba extends __midcom_core_privilege_dba
{
    function midcom_core_privilege_dba($id=null)
    {
        return parent::__midcom_core_privilege_dba($id);
    }
}

?>
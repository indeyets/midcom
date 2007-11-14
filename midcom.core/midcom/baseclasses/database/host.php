<?php
/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:host.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM level replacement for the Midgard Host record with framework support.
 * 
 * Hosts do not have a parent object.
 * 
 * Note, as with all MidCOM DB layer objects, you should not use the get_by*
 * operations directly, instead, you have to use the constructor's $id parameter.
 * 
 * Also, all QueryBuilder operations need to be done by the factory class 
 * obtainable through the statically callable new_query_builder() DBA methods.
 * 
 * This class uses an auto-generated base class provided by midcom_services_dbclassloader.
 * 
 * @package midcom.baseclasses
 * @see midcom_services_dbclassloader
 */
class midcom_baseclasses_database_host extends __midcom_baseclasses_database_host
{
    function midcom_baseclasses_database_host($id = null)
    {
        parent::__midcom_baseclasses_database_host($id);
    }
}

?>
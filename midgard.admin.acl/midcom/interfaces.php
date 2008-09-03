<?php
/**
 * ACL editor handler
 *
 * @package midgard.admin.acl
 * @author Henri Bergius, http://bergie.iki.fi
 * @version $Id$
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 *
 * Startup loads main class, which is used for all operations.
 *
 * @package midgard.admin.acl
 */

class midgard_admin_acl_interface extends midcom_baseclasses_components_interface
{
    /**
     * Initializes the library and loads needed files
     */
    function __construct()
    {
        parent::__construct();

        $this->_component = 'midgard.admin.acl';
        $this->_purecode = true;
        $this->_autoload_files = Array('acl_editor.php');
    }

}
?>
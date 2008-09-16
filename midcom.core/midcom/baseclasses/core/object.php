<?php
/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:object.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the base class for all objects within the MidCOM core. 
 * Usually you should not need to inherit from this class directly, as all 
 * baseclasses made available for usage are somehow derived from this class.
 * 
 * A notable exception are all inherited MgdSchema driven database classes, which
 * are not inherited from this class. 
 * 
 * <b>Transition notes:</b>
 * 
 * This class has been introduced at the beginning of the MidCOM 2.5 development strain.
 * It will take a while until all parts of the framework utilize this for real.
 * 
 * @package midcom.baseclasses
 */
class midcom_baseclasses_core_object
{
    function __construct() {}
    
    function get_class_magic_default_privileges()
    {
        return array
        (
            'EVERYONE' => array(),
            'ANONYMOUS' => array(),
            'USERS' => array()
        );
    }
}

/**
 * Temporary class definition for setups running with midgard < 1.9.
 */
if(!class_exists("midgard_error_exception"))
{
    class midgard_error_exception extends Exception
    {
        function __construct($msg = null, $code = 0)
        {
            if($msg == null)
                $msg = mgd_errstr();

            parent::__construct($msg, $code);
        }
    }
}

?>
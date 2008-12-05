<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */


/**
 * Constants ?
 */

/**
 * Datamanager type exception
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_exception_type extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code=0)
    {
        parent::__construct($message, $code);
    }
}

/**
 * Datamanager schema exception
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_exception_schema extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code=0) 
    {
        parent::__construct($message, $code);
    }
}

?>
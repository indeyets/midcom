<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */


/**
 * Constants for codes ?
 */

/**
 * Datamanager type exception
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_exception_datamanager extends Exception
{
    // Redefine the exception so code isn't optional
    public function __construct($message, $code=0)
    {
        parent::__construct($message, $code);
    }
}

/**
 * Datamanager storage exception
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_exception_storage extends midcom_helper_datamanager_exception_datamanager
{
}

/**
 * Datamanager type exception
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_exception_type extends midcom_helper_datamanager_exception_datamanager
{
}

/**
 * Datamanager schema exception
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_exception_schema extends midcom_helper_datamanager_exception_datamanager
{
}

/**
 * Datamanager widget exception
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_exception_widget extends midcom_helper_datamanager_exception_datamanager
{
}

/**
 * Datamanager "saved" state exception
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_exception_save extends midcom_helper_datamanager_exception_datamanager
{
    public function __construct($message = 'save', $code = 0)
    {
        parent::__construct($message, $code);
    }
}

/**
 * Datamanager "cancel" state exception
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_exception_cancel extends midcom_helper_datamanager_exception_datamanager
{
    public function __construct($message = 'cancel', $code = 0)
    {
        parent::__construct($message, $code);
    }
}

/**
 * Datamanager validation exception
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_exception_validation extends midcom_helper_datamanager_exception_datamanager
{
}


/**
 * Datamanager locked exception
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_exception_locked extends midcom_helper_datamanager_exception_datamanager
{
}
?>
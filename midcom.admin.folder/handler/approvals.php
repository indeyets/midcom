<?php
/**
 * @package midcom.admin.folder
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Handle the requests for approving objects.
 *
 * @package midcom.admin.folder
 */
class midcom_admin_folder_handler_approvals extends midcom_baseclasses_components_handler
{
    /**
     * Constructor metdot
     *
     * @access public
     */
    function midcom_admin_folder_handler_approvals ()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Checks the integrity of the content topic and gets the stored approvals of
     * the content folder.
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success
     */
    function _handler_approval($handler_id, $args, &$data)
    {
        if (   ! array_key_exists('guid', $_REQUEST)
            || ! array_key_exists('return_to', $_REQUEST))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Cannot process approval request, request is incomplete.');
            // This will exit.
        }

        $object = $_MIDCOM->dbfactory->get_object_by_guid($_REQUEST['guid']);
        if (! $object)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The GUID '{$_REQUEST['guid']}' was not found.");
            // This will exit.
        }
        $object->require_do('midcom:approve');

        $metadata =& midcom_helper_metadata::retrieve($object);

        if (! $metadata)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to retrieve Metadata for '{$object->__table__}' ID {$object->id}.");
            // This will exit.
        }

        if ($handler_id == '____ais-folder-approve')
        {
            $metadata->approve();
        }
        else
        {
            $metadata->unapprove();
        }

        $_MIDCOM->relocate($_REQUEST['return_to']);
        // This will exit.
    }

}
?>
<?php
/**
 * @package net.nehmer.comments
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: moderate.php 14447 2008-01-17 08:49:32Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Comments moderation handler
 *
 * @package net.nehmer.comments
 */

class net_nehmer_comments_handler_moderate extends midcom_baseclasses_components_handler
{
    /**
     * Comment we are currently working with.
     *
     * @var Array
     * @access private
     */
    var $_comment = null;

    /**
     * The GUID of the object we're bound to.
     *
     * @var string GUID
     * @access private
     */
    var $_objectguid = null;

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['objectguid'] =& $this->_objectguid;
        $this->_request_data['comment'] =& $this->_comment;
    }


    /**
     * Simple default constructor.
     */
    function net_nehmer_comments_handler_moderate()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Marks comment as possible abuse
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report($handler_id, $args, &$data)
    {
        if (!array_key_exists('mark', $_POST))
        {
            return false;
        }

        $this->_comment = new net_nehmer_comments_comment($args[0]);
        if (!$this->_comment)
        {
            return false;
        }

        $this->_comment->_sudo_requested = false;

        if (!$this->_comment->can_do('midgard:update'))
        {
            $this->_comment->_sudo_requested = true;
            $_MIDCOM->auth->request_sudo('net.nehmer.comments');
        }

        switch ($_POST['mark'])
        {
            case 'abuse':
                // Report the abuse
                $this->_comment->report_abuse();
                if (isset($_POST['return_url']))
                {
                    $_MIDCOM->relocate($_POST['return_url']);
                    // This will exit.
                }
                break;

            case 'confirm_abuse':
                $this->_comment->require_do('net.nehmer.comments:moderation');
                // Confirm the message is abuse
                $this->_comment->confirm_abuse();

                // Update the index
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->delete($this->_comment->guid);

                break;

            case 'confirm_junk':
                $this->_comment->require_do('net.nehmer.comments:moderation');
                // Confirm the message is abuse
                $this->_comment->confirm_junk();

                // Update the index
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->delete($this->_comment->guid);

                break;

            case 'not_abuse':
                $this->_comment->require_do('net.nehmer.comments:moderation');
                // Confirm the message is abuse
                $this->_comment->report_not_abuse();
                
                if (isset($_POST['return_url']))
                {
                    $_MIDCOM->relocate($_POST['return_url']);
                    // This will exit.
                }
                
                $_MIDCOM->relocate("read/{$this->_comment->guid}.html");
                // This will exit
        }
        if ($this->_comment->_sudo_requested)
        {
            $this->_comment->_sudo_requested = false;
            $_MIDCOM->auth->drop_sudo();
        }


        if (isset($_POST['return_url']))
        {
            $_MIDCOM->relocate($_POST['return_url']);
            // This will exit.
        }

        $_MIDCOM->relocate('');
        // This will exit.
    }
}

?>
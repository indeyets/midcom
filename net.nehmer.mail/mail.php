<?php
/**
 * @package net.nehmer.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * On-Site Mail System Mailbox class
 *
 * @package net.nehmer.mail
 */
class net_nehmer_mail_mail extends __net_nehmer_mail_mail
{
    function net_nehmer_mail_mail($id = null)
    {
        parent::__net_nehmer_mail_mail($id);
    }

    /**
     * The get_parent_guid_uncached method links to the owning mailbox. If the mailbox cannot be resolved,
     * the error is logged but ignored silently, to allow for error handling.
     */
    function get_parent_guid_uncached()
    {
        return $this->mailbox;
    }

    /**
     * Returns an instance of the owning mailbox. A new object is created.
     *
     * @return net_nehmer_mail_mailbox The mailbox of this mail
     */
    function get_mailbox()
    {
        return new net_nehmer_mail_mailbox($this->mailbox);
    }

    /**
     * DBA magic defaults which assign write privileges for all USERS, so that they can freely
     * create mails without the need to sudo of the component. Also, we deny read unconditionally,
     * as read privileges are set during creation for the sender, and are inherited from the
     * mailbox for the receiver.
     */
    function get_class_magic_default_privileges()
    {
        return Array (
            'EVERYONE' => Array('midgard:read' => MIDCOM_PRIVILEGE_DENY),
            'ANONYMOUS' => Array(),
            'USERS' => Array('midgard:create' => MIDCOM_PRIVILEGE_ALLOW),
        );
    }

    /**
     * Returns the HMTL-formatted body of the message. Uses the net.nehmer.markdown
     * library.
     *
     * @return string The HTML-Formatted body.
     */
    function get_body_formatted()
    {
        static $markdown = null;
        if (! $markdown)
        {
            $markdown = new net_nehmer_markdown_markdown();
        }

        return $markdown->render($this->body);
    }

}
<?php
/**
 * @package net.nehmer.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: mail.php 3181 2006-03-30 17:51:50Z torben $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * On-Site Mail System MailToMailbox relation class
 *
 * @package net.nehmer.mail
 */
class net_nehmer_mail_relation extends __net_nehmer_mail_relation
{
    function net_nehmer_mail_relation($id = null)
    {
        parent::__net_nehmer_mail_relation($id);
    }
    
    function get_mailbox()
    {
        return new net_nehmer_mail_mailbox($this->mailbox);
    }

    function get_mail()
    {
        return new net_nehmer_mail_mail($this->mail);
    }

}

?>
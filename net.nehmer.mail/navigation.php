<?php
/**
 * @package net.nehmer.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Onsite Mailing System NAP interface class
 *
 *
 * @package net.nehmer.mail
 */

class net_nehmer_mail_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function net_nehmer_mail_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }

    /**
     * Little helper, transforms a mailbox into a leaf structure.
     *
     * @param net_nehmer_mail_mailbox $mailbox The Mailbox to transform.
     * @return Array A NAP leaf structure.
     */
    function _mailbox_to_leaf($mailbox)
    {
        if ($mailbox->name == 'INBOX')
        {
            $name = $this->_l10n->get('inbox');
            $url = "mailbox/INBOX.html";
        }
        else if ($mailbox->name == 'OUTBOX')
        {
            $name = $this->_l10n->get('outbox');
            $url = "mailbox/OUTBOX.html";
        }
        else
        {
            $name = $mailbox->name;
            $url = "mailbox/{$mailbox->guid}.html";
        }

        return array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => $url,
                MIDCOM_NAV_NAME => $name
            ),
            MIDCOM_NAV_ADMIN => Array
            (
                MIDCOM_NAV_URL => "mailbox/edit/{$mailbox->guid}",
                MIDCOM_NAV_NAME => $name
            ),
            MIDCOM_NAV_GUID => $mailbox->guid,
            MIDCOM_NAV_TOOLBAR => null,
            MIDCOM_NAV_OBJECT => $mailbox,
            MIDCOM_META_CREATOR => 0,
            MIDCOM_META_EDITOR => 0,
            MIDCOM_META_CREATED => 0,
            MIDCOM_META_EDITED => 0
        );

    }

    function get_leaves()
    {
        $leaves = Array();
        if ($_MIDCOM->auth->user !== null)
        {
            $leaves[NET_NEHMER_MAIL_LEAFID_NEW] = Array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => 'mail/new.html',
                    MIDCOM_NAV_NAME => $this->_l10n->get('write new mail'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_NAV_TOOLBAR => null,
                MIDCOM_META_CREATOR => 0,
                MIDCOM_META_EDITOR => 0,
                MIDCOM_META_CREATED => 0,
                MIDCOM_META_EDITED => 0
            );

            $mailboxes = net_nehmer_mail_mailbox::list_mailboxes();
            if (array_key_exists('INBOX', $mailboxes))
            {
                $leaves[$mailboxes['INBOX']->guid] = $this->_mailbox_to_leaf($mailboxes['INBOX']);
            }
            if (array_key_exists('OUTBOX', $mailboxes))
            {
                $leaves[$mailboxes['OUTBOX']->guid] = $this->_mailbox_to_leaf($mailboxes['OUTBOX']);
            }
            foreach ($mailboxes as $name => $mailbox)
            {
                if (   $name == 'INBOX'
                    || $name == 'OUTBOX')
                {
                    continue;
                }

                $leaves[$mailbox->guid] = $this->_mailbox_to_leaf($mailbox);
            }
        }
        return $leaves;
    }

    /*
    function get_node()
    {
        return array
        (
            MIDCOM_NAV_URL => '',
            MIDCOM_NAV_NAME => $this->_topic->extra,
            MIDCOM_NAV_TOOLBAR => $toolbar,
            MIDCOM_NAV_NOENTRY => $hidden,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
    }
     */

}

?>

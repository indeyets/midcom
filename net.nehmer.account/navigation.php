<?php
/**
 * @package net.nehmer.account
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Account Management NAP interface class
 *
 * @package net.nehmer.account
 */

class net_nehmer_account_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function net_nehmer_account_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }

    function get_leaves()
    {
        $leaves = Array();

        if ($_MIDCOM->auth->user !== null)
        {
            $leaves[NET_NEHMER_ACCOUNT_LEAFID_EDIT] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "edit.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('edit account'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->creator,
                MIDCOM_META_EDITOR => $this->_topic->revisor,
                MIDCOM_META_CREATED => $this->_topic->created,
                MIDCOM_META_EDITED => $this->_topic->revised
            );

            if ($this->_config->get('allow_publish'))
            {
                $leaves[NET_NEHMER_ACCOUNT_LEAFID_PUBLISH] = array
                (
                    MIDCOM_NAV_SITE => Array
                    (
                        MIDCOM_NAV_URL => "publish.html",
                        MIDCOM_NAV_NAME => $this->_l10n->get('publish account details'),
                    ),
                    MIDCOM_NAV_ADMIN => null,
                    MIDCOM_META_CREATOR => $this->_topic->creator,
                    MIDCOM_META_EDITOR => $this->_topic->revisor,
                    MIDCOM_META_CREATED => $this->_topic->created,
                    MIDCOM_META_EDITED => $this->_topic->revised
                );
            }

            $leaves[NET_NEHMER_ACCOUNT_LEAFID_PASSWORD] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "password.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('change password'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->creator,
                MIDCOM_META_EDITOR => $this->_topic->revisor,
                MIDCOM_META_CREATED => $this->_topic->created,
                MIDCOM_META_EDITED => $this->_topic->revised
            );

            if ($this->_config->get('allow_change_username'))
            {
                $leaves[NET_NEHMER_ACCOUNT_LEAFID_USERNAME] = array
                (
                    MIDCOM_NAV_SITE => Array
                    (
                        MIDCOM_NAV_URL => "username.html",
                        MIDCOM_NAV_NAME => $this->_config->get('username_is_email') ? 
                            $this->_l10n->get('change email') : $this->_l10n->get('change username'),
                    ),
                    MIDCOM_NAV_ADMIN => null,
                    MIDCOM_META_CREATOR => $this->_topic->creator,
                    MIDCOM_META_EDITOR => $this->_topic->revisor,
                    MIDCOM_META_CREATED => $this->_topic->created,
                    MIDCOM_META_EDITED => $this->_topic->revised
                );
            }

            if ($this->_config->get('allow_cancel_membership'))
            {
                $leaves[NET_NEHMER_ACCOUNT_LEAFID_CANCELMEMBERSHIP] = array
                (
                    MIDCOM_NAV_SITE => Array
                    (
                        MIDCOM_NAV_URL => "cancel_membership.html",
                        MIDCOM_NAV_NAME => $this->_l10n->get('cancel membership'),
                    ),
                    MIDCOM_NAV_ADMIN => null,
                    MIDCOM_META_CREATOR => $this->_topic->creator,
                    MIDCOM_META_EDITOR => $this->_topic->revisor,
                    MIDCOM_META_CREATED => $this->_topic->created,
                    MIDCOM_META_EDITED => $this->_topic->revised
                );
            }
        }
        else
        {
            if ($this->_config->get('allow_register'))
            {
                $leaves[NET_NEHMER_ACCOUNT_LEAFID_REGISTER] = array
                (
                    MIDCOM_NAV_SITE => Array
                    (
                        MIDCOM_NAV_URL => "register.html",
                        MIDCOM_NAV_NAME => $this->_l10n->get('account registration'),
                    ),
                    MIDCOM_NAV_ADMIN => null,
                    MIDCOM_META_CREATOR => $this->_topic->creator,
                    MIDCOM_META_EDITOR => $this->_topic->revisor,
                    MIDCOM_META_CREATED => $this->_topic->created,
                    MIDCOM_META_EDITED => $this->_topic->revised
                );
            }
            $leaves[NET_NEHMER_ACCOUNT_LEAFID_LOSTPASSWORD] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "lostpassword.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('lost password'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->creator,
                MIDCOM_META_EDITOR => $this->_topic->revisor,
                MIDCOM_META_CREATED => $this->_topic->created,
                MIDCOM_META_EDITED => $this->_topic->revised
            );
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

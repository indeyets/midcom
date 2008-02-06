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

        if ($_MIDCOM->auth->user == null)
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
                    MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
                    MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
                    MIDCOM_META_CREATED => $this->_topic->metadata->created,
                    MIDCOM_META_EDITED => $this->_topic->metadata->revised
                );
            }
            if ($this->_config->get('show_lost_password_in_navi'))
            {
                $leaves[NET_NEHMER_ACCOUNT_LEAFID_LOSTPASSWORD] = array
                (
                    MIDCOM_NAV_SITE => Array
                    (
                        MIDCOM_NAV_URL => "lostpassword.html",
                        MIDCOM_NAV_NAME => $this->_l10n->get('lost password'),
                    ),
                    MIDCOM_NAV_ADMIN => null,
                    MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
                    MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
                    MIDCOM_META_CREATED => $this->_topic->metadata->created,
                    MIDCOM_META_EDITED => $this->_topic->metadata->revised
                );
            }
        }
        else
        {
            if ($this->_config->get('allow_change_password'))
            {
                $leaves[NET_NEHMER_ACCOUNT_LEAFID_PASSWORDCHANGE] = array
                (
                    MIDCOM_NAV_SITE => Array
                    (
                        MIDCOM_NAV_URL => "password.html",
                        MIDCOM_NAV_NAME => $this->_l10n->get('change password'),
                    ),
                    MIDCOM_NAV_ADMIN => null,
                    MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
                    MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
                    MIDCOM_META_CREATED => $this->_topic->metadata->created,
                    MIDCOM_META_EDITED => $this->_topic->metadata->revised
                );
            }
        }

        // Pending registrations
        if (   $_MIDCOM->auth->admin
            && $this->_config->get('require_activation'))
        {
            // Get the count of persons waiting for approval
            $qb = midcom_db_person::new_query_builder();
            $qb->add_constraint('parameter.domain', '=', 'net.nehmer.account');
            $qb->add_constraint('parameter.name', '=', 'require_approval');
            $qb->add_constraint('parameter.value', '=', 'require_approval');
            
            $count = $qb->count();
            
            $leaves[NET_NEHMER_ACCOUNT_LEAFID_PENDING] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "pending/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('pending approvals') . " ({$count})",
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
                MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
                MIDCOM_META_CREATED => $this->_topic->metadata->created,
                MIDCOM_META_EDITED => $this->_topic->metadata->revised
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
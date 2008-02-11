<?php

/**
 * @package net.nehmer.account
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Account Manager MidCOM interface class.
 *
 * The component config can be used to control the various features:
 *
 * TODO complete this
 *
 * <b>username_is_email:</b> If you enable this feature, the component syncs the E-Mail
 * field of the person record with the username field on every update. Note, that if you
 * include the email field in the account editing schema, there will be no reverse-sync
 * if the user changes just the email field. It is thus recommended either to omit the
 * email field or to set it readonly.
 *
 * <b>register_allow_type:</b> Use this to explicitly limit the account types that can
 * be registered to. This must be a subset of the type listing found in the account schema.
 *
 * <b>fallback_type:</b> Set this to avoid having to place each user into the same group.
 * If a user's group cannot be determined and this option is set, it is used as a fallback
 * type.
 *
 * @package net.nehmer.account
 */
class net_nehmer_account_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nehmer_account_interface()
    {
        parent::midcom_baseclasses_components_interface();

        define ('NET_NEHMER_ACCOUNT_LEAFID_EDIT', 0);
        define ('NET_NEHMER_ACCOUNT_LEAFID_PUBLISH', 1);
        define ('NET_NEHMER_ACCOUNT_LEAFID_PASSWORD', 2);
        define ('NET_NEHMER_ACCOUNT_LEAFID_REGISTER', 3);
        define ('NET_NEHMER_ACCOUNT_LEAFID_USERNAME', 4);
        define ('NET_NEHMER_ACCOUNT_LEAFID_LOSTPASSWORD', 5);
        define ('NET_NEHMER_ACCOUNT_LEAFID_CANCELMEMBERSHIP', 5);
        define ('NET_NEHMER_ACCOUNT_LEAFID_PASSWORDCHANGE', 6);
        define ('NET_NEHMER_ACCOUNT_LEAFID_PENDING', 10);
        define ('NET_NEHMER_ACCOUNT_LEAFID_OTHER', 999);

        $this->_component = 'net.nehmer.account';
        $this->_autoload_files = array
        (
            'viewer.php', 
            'navigation.php', 
            'invite.php',
        );
        
        $this->_autoload_libraries = Array
        (
            'midcom.helper.datamanager2',
            'org.openpsa.mail',
        );
    }
    
    function _on_initialize()
    {
        if (!class_exists('midcom_helper_itemlist'))
        {
            require(MIDCOM_ROOT . '/midcom/helper/itemlist.php');
        }
        
        /** This should be deprecated
        if (!class_exists('midcom_helper_mailtemplate'))
        {
            require(MIDCOM_ROOT . '/midcom/helper/mailtemplate.php');
        }
        */
        return true;
    }
    
    /**
     * This is a factory method which creates a remote controller instance bound to a specific
     * topic.
     *
     * Any error results in generate_error being called.
     *
     * @param string $guid The guid that should be controlled.
     * @return net_nehmer_account_remote The remote controller instance.
     */
    function create_remote_controller($guid)
    {
        require_once(MIDCOM_ROOT . '/net/nehmer/account/remote.php');
        return new net_nehmer_account_remote($guid);
    }

    /* *
     * TODO
     * /
    function _on_resolve_permalink($topic, $config, $guid)
    {
    }
    */
}
?>
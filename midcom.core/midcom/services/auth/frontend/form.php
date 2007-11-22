<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:form.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** Auth Frontend Base Class */
require_once (MIDCOM_ROOT . '/midcom/services/auth/frontend.php');

/**
 * Form-based authentication frontend. This one is rather simple, it just renders a
 * two-field (username/password) form which is targeted at the current URL.
 *
 * @package midcom.services
 */
class midcom_services_auth_frontend_form extends midcom_services_auth_frontend
{
    /**
     * Nothing to do here.
     */
    function midcom_services_auth_frontend_form ()
    {
        return parent::midcom_services_auth_frontend();
    }

    /**
     * This call checks whether the two form fields we have created are present, if yes
     * it reads and returns their values.
     *
     * @return Array A simple accociative array with the two indexes 'username' and
     *     'password' holding the information read by the driver or NULL if no
     *     information could be read.
     */
    function read_authentication_data()
    {
        if (   ! array_key_exists('midcom_services_auth_frontend_form_submit', $_REQUEST)
            || ! array_key_exists('username', $_REQUEST)
            || ! array_key_exists('password', $_REQUEST))
        {
            return null;
        }

        return Array
        (
            'username' => trim($_REQUEST['username']),
            'password' => trim($_REQUEST['password'])
        );
    }

    /**
     * This call renders a simple form without any formatting (that is to be
     * done by the callee) that asks the user for his username and password.
     *
     * The default should be quite useable through its CSS.
     *
     * If you want to replace the form by some custom style, you can define
     * the style- or page-element <i>midcom_services_auth_frontend_form</i>. If this
     * element is present, it will be shown instead of the default style
     * included in this function. In that case you should look into the source
     * of it to see exactly what is required.
     *
     * See https://www.midgard-project.org/midcom-permalink-c5e99db3cfbb779f1108eff19d262a7c
     * for further information about how to style these elements.
     */
    function show_authentication_form()
    {
        if (   function_exists('mgd_is_element_loaded')    
            && mgd_is_element_loaded('midcom_services_auth_frontend_form'))
        {
            mgd_show_element('midcom_services_auth_frontend_form');
        }
        else
        {
            ?>
            <form name="midcom_services_auth_frontend_form" method='post' id="midcom_services_auth_frontend_form">
                <label for="username">
                    <p><?php echo $_MIDCOM->i18n->get_string('username', 'midcom'); ?></p>
                    <input name="username" id="username" class="input" />
                </label>
                <label for="password">
                    <p><?php echo $_MIDCOM->i18n->get_string('password', 'midcom'); ?></p>
                    <input name="password" id="password" type="password" class="input" />
                </label>
                <div class="clear"></div>
                <input type="submit" name="midcom_services_auth_frontend_form_submit" id="midcom_services_auth_frontend_form_submit" value="<?php
                    echo $_MIDCOM->i18n->get_string('login', 'midcom'); ?>" />
            </form>
            <?php
        }

        if ($GLOBALS['midcom_config']['auth_openid_enable'])
        {
            $_MIDCOM->load_library('net.nemein.openid');
            $url = $_MIDCOM->get_host_prefix() . 'midcom-exec-net.nemein.openid/initiate.php';
            ?>
            <!--<h3><?php echo $_MIDCOM->i18n->get_string('login using openid', 'net.nemein.openid'); ?></h3>-->
            
            <div id="open_id_form">
                <form action="<?php echo $url; ?>" method="post">
                    <label for="openid_url">
                        <p><?php echo $_MIDCOM->i18n->get_string('openid url', 'net.nemein.openid'); ?></p>
                        <input name="openid_url" id="openid_url" type="text" class="input" value="http://" />
                    </label>
                    <!--
                    <p class="helptext">
                      OpenID lets you safely sign in to different websites with a single password. <a href="https://www.myopenid.com/affiliate_signup?affiliate_id=17">Get an OpenID</a>.
                    </p>
                    -->
                    <input type="submit" name="midcom_services_auth_frontend_form_submit" id="openid_submit" value="<?php
                        echo $_MIDCOM->i18n->get_string('login', 'midcom'); ?>" />
                </form>
            </div>	
            <?php
        }
    }

    /**
     * ??? IS THIS NEEDED ???
     * @ignore
     */
    function access_denied($reason) { die(__CLASS__ . '::' . __FUNCTION__ . ' must be overridden.'); }
}
?>
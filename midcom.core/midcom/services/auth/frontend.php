<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:frontend.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Authentication frontend, responsible for rendering the login screen, reading
 * the credencials and displaying access denied information.
 * 
 * All functions must be implemented, see their individual documentation about 
 * what exactly they should do.
 * 
 * Configuration, if neccessary, should be done using the MidCOM configuration
 * system, prefixing all values with 'auth_frontend_$name_', e.g. 
 * 'auth_frontend_form_cssclass'.
 * 
 * @package midcom.services
 */
class midcom_services_auth_frontend extends midcom_baseclasses_core_object
{
    /**
     * The constructor should do only basic initialization.
     */
    function midcom_services_auth_frontend ()
    {
        return parent::midcom_baseclasses_core_object();
    }
    
    /**
     * This call should process the current authentication credentials and return
     * the username / password pair that should be tried to authentication
     * or null for anonymous access.
     * 
     * @return Array A simple accociative array with the two indexes 'username' and
     *     'password' holding the information read by the driver or NULL if no 
     *     information could be read.
     */
    function read_authentication_data() 
    { 
        die(__CLASS__ . '::' . __FUNCTION__ . ' must be overridden.'); 
    }
    
    /**
     * This call should show the authentication form (or whatever means of input
     * you use). This content you print is assumed to work within an HTML DIV element,
     * so you should usually stick to a simple form, which should also by styleable 
     * using CSS alone.
     * 
     * You should use HTTP POST to submit the form data to the page you originated from.
     * 
     * If you really need to redirect to some external page, ensure that you send
     * the user back to the original location unharmed. Be aware that this type of 
     * operation is strongly discouraged.
     * 
     * You MAY send HTTP Authentication headers if your auth driver uses them and stop
     * execution immediately afterwards. (2DO: How to treat sent content (it is
     * in the output buffer) at this point?)
     */
    function show_authentication_form() 
    { 
        die(__CLASS__ . '::' . __FUNCTION__ . ' must be overridden.'); 
    }
    
    /**
     * ??? IS THIS NEEDED ???
     */
    function access_denied($reason) { die(__CLASS__ . '::' . __FUNCTION__ . ' must be overridden.'); }
     
}

?>
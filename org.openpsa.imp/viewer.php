<?php
/**
 * @package org.openpsa.imp
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.5 2006/02/15 14:32:19 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.imp site interface class.
 *
 * "SSO" to Horde/Imp
 */
class org_openpsa_imp_viewer extends midcom_baseclasses_components_request
{
    var $_server_uri = false;
    var $_imp_username = false;
    var $_imp_password = false;
    var $_global_server = false;
    var $_toolbars;

    /**
     * Constructor.
     */
    function org_openpsa_imp_viewer($topic, $config)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        parent::midcom_baseclasses_components_request($topic, $config);

        $this->_toolbars =& midcom_helper_toolbars::get_instance();

        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();

        // Match /redirect
        $this->_request_switch[] = array(
            'fixed_args' => 'redirect',
            'handler' => 'redirect'
        );

        // Match /settings
        $this->_request_switch[] = array(
            'fixed_args' => 'settings',
            'handler' => 'settings'
        );

        // Match /
        $this->_request_switch[] = array(
            'handler' => 'frontpage'
        );

        debug_pop();
        return true;
    }

    function _populate_toolbar()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        //Add icon for user settings
        $this->_toolbars->bottom->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'settings.html',
            MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('settings'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));

        debug_pop();
        return true;
    }

    /**
     * Tries to read settings for webmail
     */
    function _check_imp_settings()
    {
        $current_topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
        $current_user_dbobj = $_MIDCOM->auth->user->get_storage();

        if (!is_object($current_user_dbobj))
        {
            debug_add("Current user not found", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // Get server URI
        if ($this->_server_uri = $current_topic->parameter('org.openpsa.imp', 'imp_global_uri'))
        {
            //Global server URI found always use it
            $this->_global_server = true;
        }
        else
        {
            $this->_server_uri = $current_user_dbobj->get_parameter('org.openpsa.imp', 'imp_uri');
        }
        if (!$this->_server_uri)
        {
            debug_add("Server URI not found", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        //Get username
        $this->_imp_username = $current_user_dbobj->get_parameter('org.openpsa.imp', 'imp_username');
        if (!$this->_imp_username)
        {
            debug_add("Imp username not found", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        //Get password
        $this->_imp_password = $current_user_dbobj->get_parameter('org.openpsa.imp', 'imp_password');
        if (!$this->_imp_password)
        {
            debug_add("Imp password not found", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        return true;
    }


    function _handler_redirect($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);

        $formData = false;
        $nextUri = false;

        if (!$this->_check_imp_settings())
        {
            debug_add("Horde/Imp settings incomplete, aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        //Try to get remote login form
        @$fp=fopen($this->_server_uri, 'r');
        if (!$fp) {
           //Could not open remote URI, this might be lack of SSL wrappers or something
           debug_add(sprintf('Could not open %s for reading', $server_uri));
        } else {
            //Read remote information (copied from old OpenPSA)
            $HTMLBody='';
            while (!feof($fp))
            {
                 $HTMLBody.=fread($fp, 4096);
            }
            preg_match('/<form[^>]*action="([^"]+)"[^>]*>/', $HTMLBody, $matches1);
            $actionUri = $matches1[1];
            preg_match_all('/<input[^>]*name="([^"]+)" (value="([^"]*)")?[^>]*>/', $HTMLBody, $matches2);
            preg_match('%(https?://[^/]+)(.*)%', $this->_server_uri, $matches3);
            $uriServer = $matches3[1];
            if (!preg_match('%^/%', $actionUri))
            {
                preg_match('%(https?://.+/)(.*)%', $this->_server_uri, $matches4);
                $uriServer=$matches4[1];
            }
            $nextUri = $uriServer . $actionUri;

            $formData='<form id="org_openpsa_imp_autoSubmit" method="post" action="'.$nextUri.'">'."\n";
            while (list ($n, $k) = each ($matches2[1]))
            {
                 switch ($k)
                 {
                        default:
                             $v=$matches2[3][$n];
                        break;
                        case 'login_username':
                        case 'imapuser':
                             $v=$this->_imp_username;
                        break;
                        case 'secretkey':
                        case 'pass':
                             $v=$this->_imp_password;
                        break;
                 }
                 $formData.='    <input type="hidden" name="'.$k.'" value="'.$v.'" />'."\n";
            }
            reset ($matches2[1]);
            $formData.="<input type=\"submit\" value=\"".'log in'."\">\n</form>\n";
        }

        if (!$nextUri)
        {
            //Address to post the form to not found, we try to just to redirect to the given server URI
            debug_add('Action URI not found in data, relocating to server base URI');
            debug_pop();
            $_MIDCOM->relocate($this->_server_uri);
            //This will exit
        }

        $this->_request_data['login_form_html'] = $formData;

        // We're using a popup here
        $_MIDCOM->skip_page_style = true;

        debug_pop();
        return true;
    }

    function _show_redirect($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        midcom_show_style("imp-redirect");

        debug_pop();
        return true;
    }

    function _handler_settings($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->_check_imp_settings();

        //Initialize/handle DM
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);

        // Load the schema definition file
        $schemadb_contents = midcom_get_snippet_content($this->_config->get("schemadb_horde_account"));
        eval("\$schemadb = Array ( {$schemadb_contents} );");

        //Choose schema
        if ($this->_global_server)
        {
            $schema = 'globalserver';
        }
        else
        {
            $schema = 'default';
        }
        debug_add('Chose schema: "' . $schema . '"');

        // Instantiate datamanager
        $this->_request_data['datamanager'] = new midcom_helper_datamanager($schemadb);

        if (!$this->_request_data['datamanager']) {
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Datamanager could not be instantinated.");
            // This will exit.
        }

        // Load the person record into DM
        $person_record = $_MIDCOM->auth->user->get_storage();
        $this->_request_data['datamanager']->init($person_record, $schema);

        // Process the form
        switch ($this->_request_data['datamanager']->process_form())
        {
            case MIDCOM_DATAMGR_EDITING:
                // Add toolbar items
                org_openpsa_helpers_dm_savecancel($this->_toolbars->bottom, $this);
                break;
            case MIDCOM_DATAMGR_SAVED:
                $_MIDCOM->relocate( $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) );
                //this will exit
                break;
            case MIDCOM_DATAMGR_CANCELLED:
                $_MIDCOM->relocate( $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) );
                //this will exit
                break;
            case MIDCOM_DATAMGR_FAILED:
                debug_pop();
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Datamanager failed with: {$GLOBALS['midcom_errstr']}.");
                // This will exit
        }
        debug_pop();
        return true;
    }

    function _show_settings($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        midcom_show_style("show-settings");

        debug_pop();
        return true;
    }

    function _handler_frontpage($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);

        //If settings are not complete redirect to settings page
        if (!$this->_check_imp_settings())
        {
            debug_add("Horde/Imp settings incomplete, redirecting to settings page.");
            debug_pop();
            $_MIDCOM->relocate( $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                                . 'settings.html');
            //This will exit
        }

        $this->_populate_toolbar();

        debug_pop();
        return true;
    }

    function _show_frontpage($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        midcom_show_style("show-frontpage");

        debug_pop();
        return true;
    }
}
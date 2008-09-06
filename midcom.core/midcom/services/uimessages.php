<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:uimessages.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * User interface messaging service
 *
 * This service is used for passing messages from applications to the MidCOM
 * user.
 *
 * <b>Displaying UI messages on site:</b>
 *
 * If you want the UI messages to be shown in your site, you must place
 * the following call inside the HTML BODY tags of your style:
 *
 * <code>
 * $_MIDCOM->uimessages->show();
 * </code>
 *
 * <b>Adding UI messages to show:</b>
 *
 * Any MidCOM component can add its own UI messages to be displayed. The
 * messages also carry across a relocate() call so you can tell a document
 * has been saved before relocating user into its view.
 *
 * UI messages can be specified into the following types: <i>info</i>,
 * <i>ok</i>, <i>warning</i> and <i>error</i>.
 *
 * To add a UI message, call the following:
 *
 * <code>
 * $_MIDCOM->uimessages->add($title, $message, $type);
 * </code>
 *
 * For example:
 *
 * <code>
 * $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('net.nemein.wiki'), sprintf($this->_request_data['l10n']->get('page "%s" added'), $this->_wikiword), 'ok');
 * </code>
 *
 * <b>Configuration:</b>
 *
 * See midcom_config.php for configuration options.
 *
 * @package midcom.services
 */
class midcom_services_uimessages extends midcom_baseclasses_core_object
{
    /**
     * The current message stack
     *
     * @var Array
     * @access private
     */
    var $_message_stack = Array();

    /**
     * List of allowed message types
     *
     * @var Array
     * @access private
     */
    var $_allowed_types = Array();

    /**
     * List of messages retrieved from session to avoid storing them again
     *
     * @var Array
     * @access private
     */
    var $_messages_from_session = Array();

    /**
     * ID of the latest UI message added so we can auto-increment
     *
     * @var integer
     * @access private
     */
    var $_latest_message_id = 0;

    /**
     * Simple constructor, calls base class.
     */
    function midcom_services_uimessages()
    {
        parent::__construct();

        // Set the list of allowed message types
        $this->_allowed_types[] = 'info';
        $this->_allowed_types[] = 'ok';
        $this->_allowed_types[] = 'warning';
        $this->_allowed_types[] = 'error';
        $this->_allowed_types[] = 'debug';
    }

    /**
     * Initialize the message stack on service start-up. Reads older unshown
     * messages from user session.
     */
    function initialize()
    {
        if ($_MIDCOM->auth->can_user_do('midcom:ajax', null, 'midcom_services_uimessages'))
        {
            // $_MIDCOM->enable_jquery();
            // $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.services.uimessages/jquery.midcom_services_uimessages.js');
            // $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.services.uimessages/metadata.js');
            //
            // $_MIDCOM->add_link_head(
            //     array
            //     (
            //         'rel'   => 'stylesheet',
            //         'type'  => 'text/css',
            //         'media' => 'screen',
            //         'href'  => MIDCOM_STATIC_URL . '/midcom.services.uimessages/growl.css',
            //     )
            // );
            //
            // $config = "{}";
            // $script = "jQuery.midcom_services_uimessages({$config}, false);";
            // $_MIDCOM->add_jquery_state_script($script);
            
            $_MIDCOM->enable_jquery();

            $_MIDCOM->add_link_head(
                array
                (
                    'rel'   => 'stylesheet',
                    'type'  => 'text/css',
                    'media' => 'screen',
                    'href'  => MIDCOM_STATIC_URL . '/midcom.services.uimessages/protoGrowl.css',
                )
            );
        }
        else
        {
            $_MIDCOM->add_link_head(
                array
                (
                    'rel'   => 'stylesheet',
                    'type'  => 'text/css',
                    'media' => 'screen',
                    'href'  => MIDCOM_STATIC_URL . '/midcom.services.uimessages/simple.css',
                )
            );
        }

        /* Sessions service now tries to be smarter about things
        if (! $_MIDGARD['user'])
        {
            // Don't use sessioning for non-users as that kills cache usage
            // TODO: Device a better approach for this
            return true;
        }
        */

        // Read messages from session
        $session = new midcom_service_session('midcom_services_uimessages');
        if ($session->exists('midcom_services_uimessages_stack'))
        {
            // We've got old messages in the session
            $stored_messages = $session->get('midcom_services_uimessages_stack');
            $session->remove('midcom_services_uimessages_stack');
            if (!is_array($stored_messages))
            {
                return false;
            }

            foreach ($stored_messages as $message)
            {
                $id = $this->add($message['title'], $message['message'], $message['type']);
                $this->_messages_from_session[] = $id;
            }
        }
    }

    function get_class_magic_default_privileges()
    {
        $privileges = parent::get_class_magic_default_privileges();
        //$privileges['EVERYONE']['midgard:read'] = MIDCOM_PRIVILEGE_DENY;
        return $privileges;
    }

    /**
     * Store unshown UI messages from the stack to user session.
     */
    function store()
    {
        //$this->add('MIDCOM', "Storing messages, latest id is {$this->_latest_message_id}...");
        if (count($this->_message_stack) == 0)
        {
            // No unshown messages
            return true;
        }

        /* Sessions service now tries to be smarter about things
        if (!$_MIDGARD['user'])
        {
            // Don't use sessioning for non-users as that kills cache usage
            return true;
        }
        */

        // We have to be careful what messages to store to session to prevent them
        // from accumulating
        $messages_to_store = Array();
        foreach ($this->_message_stack as $id => $message)
        {
            // Check that the messages were not coming from earlier session
            if (!in_array($id, $this->_messages_from_session))
            {
                $messages_to_store[$id] = $message;
            }
        }
        if (count($messages_to_store) == 0)
        {
            // We have only messages coming from earlier sessions, and we ditch those
            return true;
        }

        $session = new midcom_service_session('midcom_services_uimessages');

        // Check if some other request has added stuff to session as well
        if ($session->exists('midcom_services_uimessages_stack'))
        {
            $old_stack = $session->get('midcom_services_uimessages_stack');
            $messages_to_store = array_merge($old_stack, $messages_to_store);
        }
        $session->set('midcom_services_uimessages_stack', $messages_to_store);
        $this->_message_stack = Array();
    }

    /**
     * Add a message to be shown to the user.
     * @param string $title Message title
     * @param string $message Message contents, may contain HTML
     * @param string $type Type of the message
     */
    function add($title, $message, $type='info')
    {
        // Make sure the given class is allowed
        if (!in_array($type, $this->_allowed_types))
        {
            // Message class not in allowed list
            debug_add("Message type {$type} is not allowed");
            return false;
        }

        // Normalize the title and message contents
        $title = str_replace("'", '"', $title);
        $message = str_replace("'", '"', $message);

        $this->_latest_message_id++;

        // Append to message stack
        $this->_message_stack[$this->_latest_message_id] = Array(
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
        );
        return $this->_latest_message_id;
    }

    /**
     * Show the message stack via javascript calls or simple html
     */
    function show($show_simple_also=true)
    {
        if (count($this->_message_stack) > 0)
        {
            if ($_MIDCOM->auth->can_user_do('midcom:ajax', null, 'midcom_services_uimessages'))
            {
                echo "<script type=\"text/javascript\">\n";
                //echo '<div class="midcom_services_uimessages_holder">';
                foreach ($this->_message_stack as $id => $message)
                {
                    // TODO: Use our own JS call for this
                    // $options = "{
                    //     type: '{$message['type']}'
                    // }";
                    // $data = "{
                    //     title: '{$message['title']}',
                    //     message: '{$message['message']}'
                    // }";
                    //echo "    jQuery('<div>').midcom_services_uimessage({$options}, {$data});";
                    echo "    new protoGrowl({type: '{$message['type']}', title: '{$message['title']}', message: '{$message['message']}'});\n";

                    //$this->_render_message($message);

                    // Remove the message from stack
                    unset($this->_message_stack[$id]);
                }
                echo "</script>\n";
                //echo '</div>';
            }
            else
            {
                if ($show_simple_also)
                {
                    $this->show_simple();
                }
            }
        }
    }

    /**
     * Show the message stack via simple html only
     */
    function show_simple($prefer_fancy=false)
    {
        if (   $prefer_fancy
            && $_MIDCOM->auth->can_user_do('midcom:ajax', null, 'midcom_services_uimessages'))
        {
            return $this->show();
        }

        if (count($this->_message_stack) > 0)
        {
            echo "<div class=\"midcom_services_uimessages_holder\">\n";

            foreach ($this->_message_stack as $id => $message)
            {
                $this->_render_message($message);

                // Remove the message from stack
                unset($this->_message_stack[$id]);
            }

            echo "</div>\n";
        }
    }

    /**
     * Render the message
     */
    function _render_message($message)
    {
        echo "<div class=\"midcom_services_uimessages_message msu_{$message['type']}\">";

        echo "    <div class=\"midcom_services_uimessages_message_type\">{$message['type']}</div>";
        echo "    <div class=\"midcom_services_uimessages_message_title\">{$message['title']}</div>";
        echo "    <div class=\"midcom_services_uimessages_message_msg\">{$message['message']}</div>";

        echo "</div>\n";
    }

}

?>
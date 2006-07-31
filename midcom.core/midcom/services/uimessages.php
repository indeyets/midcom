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
 * <strong>Displaying UI messages on site:</strong>
 *
 * If you want the UI messages to be shown in your site, you must place
 * the following call inside the HTML BODY tags of your style:
 *
 * <code>
 * $_MIDCOM->uimessages->show();
 * </code>
 *
 * <strong>Adding UI messages to show:</strong>
 *
 * Any MidCOM component can add its own UI messages to be displayed. The
 * messages also carry across a relocate() call so you can tell a document
 * has been saved before relocating user into its view.
 *
 * UI messages can be specified into the following types: <i>info</i>,
 * <i>ok</i>, <i>warning</i> and <i>error</i>.
 * 
 * To add an UI message, call the following:
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
 * <strong>Configuration:</strong>
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
        parent::midcom_baseclasses_core_object();

        // Set the list of allowed message types
        $this->_allowed_types[] = 'info';
        $this->_allowed_types[] = 'ok';
        $this->_allowed_types[] = 'warning';
        $this->_allowed_types[] = 'error';
        
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/Pearified/JavaScript/Prototype/prototype.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/Pearified/JavaScript/Scriptaculous/scriptaculous.js?effects");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.services.uimessages/protoGrowl.js');
        
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

    /**
     * Initialize the message stack on service start-up. Reads older unshown
     * messages from user session.
     */
    function initialize()
    {      
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
        
        // TODO: Check if some other request has added stuff to session as well
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
     * Show the message stack via javascript calls
     */
    function show()
    {
        if (count($this->_message_stack) > 0)
        {
            echo "<script language=\"javascript\">\n";

            foreach ($this->_message_stack as $id => $message)
            {
                // TODO: Use our own JS call for this
                echo "    new protoGrowl({type: '{$message['type']}', title: '{$message['title']}', message: '{$message['message']}'});\n";
                //echo "ooDisplayMessage('{$message['title']}: {$message['message']}', '{$message['type']}');\n";
                
                // Remove the message from stack
                unset($this->_message_stack[$id]);
            }
            echo "</script>\n";        
        }
    }
}

?>
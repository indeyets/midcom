<?php
/**
 * Class for sending messages to the OpenPsa user interface. The messages are added to a stack
 * and are displayed together with Ajax status messages in the #org_openpsa_messagearea element
 * of the document.
 * @package org.openpsa.helpers
 * @author Henri Bergius, http://bergie.iki.fi
 * @version $Id: messages.php,v 1.6 2006/06/06 15:54:56 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class org_openpsa_helpers_uimessages extends midcom_baseclasses_components_purecode
{

    /**
     * Initializes the class and sets the following strings into the list of allowed classes:
     * normal, ok, warning and error
     */
    function org_openpsa_helpers_uimessages()
    {
        parent::midcom_baseclasses_components_purecode();
    }

    /**
     * Initialized the UI messages stack and adds correct JS headers
     */
    function initialize_stack()
    {
    }

    /**
     * Adds message to the message queue. Calls the addMessage() method.
     * @param string $message The actual message to display to the user
     * @param string $class Optional CSS class of the message
     * @return boolean Whether addition was successful
     * @see org_openpsa_helpers_uimessages::addMessage()
     */
    function add_message($message, $class='normal')
    {
        return $this->addMessage($message, $class);
    }

    /**
     * Adds message to the message queue.
     * @param string $message The actual message to display to the user
     * @param string $class Optional CSS class of the message
     * @return boolean Whether addition was successful
     */
    function addMessage($message, $class='info')
    {
        if ($class == 'normal')
        {
            $class = 'info';
        }
        $_MIDCOM->uimessages->add('OpenPsa', $message, $class);
        return true;
    }

    /**
     * Adds necessary JavaScript calls to populate the messagearea based on the queue
     */
    function html_add_php_messages()
    {
        $_MIDCOM->uimessages->show();
    }
}

?>
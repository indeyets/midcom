<?php
/**
 * @package com_rohea_mjumpaccount
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Controller for ordering a new password
 *
 * @package com_rohea_mjumpaccount
 */
class com_rohea_account_controllers_password
{

    private $password_helper;
    private $account;
    private $current_user;

    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
    }

    /**
      * Controller action_newpassword handles of changing users's password
      * and sending new one via email
      * @param route_id
      * @param data
      * @param args
      */
    public function action_newpassword($route_id, &$data, $args)
    {
        $data['name'] = "com_rohea_account";

        // Checking if all required fields are set
        if (   isset($_POST['username'])
            && isset($_POST['email']))
        {
            $qb = new midgard_query_builder('midgard_person');
            $qb->add_constraint('username', '=', $_POST['username']);
            $qb->add_constraint('email', '=', $_POST['email']);
            $res = $qb->execute();
            
            // If person is not found, throwing feedback
            if ( count($res) == 0)
            {
                $data['usernamenotfound'] = true;
                $title_text = $_MIDCOM->i18n->get('key: Error');
                $message_text = $_MIDCOM->i18n->get('key: A user with given username and e-mail was not found.');
                $message = array('title' => $title_text, 'message' => $message_text, 'type' => 'error');
                $_MIDCOM->uimessages->add($message);
                return true;
            }

            // Entering ACL - sudo and assigning new password
            $_MIDCOM->authorization->enter_sudo('com_rohea_account');
            $pw = $this->generate_password();
            $res[0]->password = "**{$pw}";
            $res[0]->update();
            
            // Generating email
            $mail = $_MIDCOM->i18n->get('key: new password mail');
            $mail .= ("
$pw

");
            $mail .= $_MIDCOM->i18n->get('key: new password mail regards');
            $from_mail = $this->configuration->get('message_from');
            mail($res[0]->email, $_MIDCOM->i18n->get('key: forgot password email subject'), $mail, "From: {$from_mail}");

            // Generating feedback
            $title_text = $_MIDCOM->i18n->get('key: Success');
            $message_text = $_MIDCOM->i18n->get('key: A new password has been sent to you.');
            $message = array('title' => $title_text, 'message' => $message_text, 'type' => 'ok');
            $_MIDCOM->uimessages->add($message);
            $_MIDCOM->uimessages->store();
    	    $_MIDCOM->authorization->leave_sudo();
    	    
    	    $url = $_MIDGARD['uri'];
    	    header("Location: $url");
    	    exit();
        }
    }
    
    /**
      * Internal helper function for generating a password
      * This function does not change password, just generates one.
      *
      * @param length lenght of the new passford
      * @return new password as string
      *
      */
    private function generate_password ($length = 8)
    {

        // start with a blank password
        $password = "";

          // define possible characters
        $possible = "0123456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVXYZ"; 
    
        // set up a counter
        $i = 0; 
    
        // add random characters to $password until $length is reached
        while ($i < $length)
        { 

            // pick a random character from the possible ones
        $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
        
        // we don't want this character if it's already in the password
        if (!strstr($password, $char))
        { 
            $password .= $char;
            $i++;
        }

        }

        // done!
        return $password;

    }

    
}
?>

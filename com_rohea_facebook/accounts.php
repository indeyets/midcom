<?php 
include_once('facebook-client/facebook.php');

class com_rohea_facebook_accounts
{
    private $api_key;
    private $secret;
    
    public function __construct($api_key = '', $secret = '')
    {
        if (empty($api_key) && empty($secret))
        {
            //$this->api_key = '70833505157458e6cd3720cc958549f8';
            //$this->secret = 'b2c44add2378ce2de9daf1f9a481fddb';
            
           // $this->api_key = 'ac779b2cf669f116377a86995de17243';
           // $this->secret = 'ff1fd7e43e7d754a5aa896e05e67102d';
            
        }
        else {
            $this->api_key = $api_key;
            $this->secret = $secret;       
        }
    }
    
    public function getRegistered()
    {
        $fb = new Facebook($this->api_key, $this->secret);
        $fb_uid = $fb->get_loggedin_user();
        //$user_id = $fb->require_login();

        //print_r($fb->api_client);



        // Greet the currently logged-in user!
        //echo "<p>Hello, <fb:name uid=\"$user_id\" useyou=\"false\" />!</p>";

        // Print out at most 25 of the logged-in user's friends,
        // using the friends.get API method
        /*
        echo "<p>Friends:";
        $friends = $fb->api_client->friends_get();
        $friends = array_slice($friends, 0, 25);
        foreach ($friends as $friend) {
          echo "<br>$friend";
        }
        echo "</p>";*/
        
        return $fb_uid;
    }
    public function get_userdetails_from_facebook($fbid)
    {
        $info = array();
        $fb = new Facebook($this->api_key, $this->secret);  
        $info = $fb->api_client->users_getInfo($fbid, array('last_name','first_name', 'locale', 'pic_square')); 
        if (!empty($info[0])) return $info[0];
        else return false;
    }
    public function getfacebookuser()
    {
        $fb = new Facebook($this->api_key, $this->secret);
        $fb_uid = $fb->get_loggedin_user();
        
        if (!empty($fb_uid))
        {
            return $fb_uid;
        }
        else return false;
    }
    public function checkfacebooklink($facebookid) {
        $qb = new midgard_query_builder("com_rohea_facebook_link");
        $qb->add_constraint("facebookid", "=", $facebookid);
        $result = $qb->execute();
        if (sizeof($result) >0)
        {
            return $result[0]->personguid;
        }
        else return false;
       
    }    
    public function addfacebooklink($facebookid, $midgardguid) {
        
        if (!empty($facebookid))
        { 
            if ($this->checkfacebooklink($facebookid) != false)
            {
               // if id has already been registered, return false
                return false;
            }
            else {
                $linking = new com_rohea_facebook_link();
                $linking->facebookid = $facebookid;
                $linking->personguid = $midgardguid;
                $linking->create();     
                return true;
            }
        }
        
    }
    public function login_midgard_person($midgardguid)
    {
        $person = new midgard_person($midgardguid);
        //$_MIDCOM->authentication->login($person->username, substr($person->password, 2));
        $_MIDCOM->authentication->trusted_login($person->username);
    }
    public function copy_image_to_avatar_image($image_url)
    {
        $avatar = new com_rohea_images_container();
        $context = 'avatar';
        
        //Set target object guid for the image
        $objectguid = $args['personguid'];
        $avatar->attach_new_image();
    }

}


?>
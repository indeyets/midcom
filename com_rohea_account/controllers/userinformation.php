<?php

class com_rohea_account_controllers_userinformation
{
    private $domain = 'com_rohea_10things_accounts';

    public function __construct($instance) 
    {
        $this->configuration = $instance->configuration;
        // Tsekataan onko käyttäjä kirjautunut.
        if (! $_MIDCOM->authentication->is_user())
        {
            throw new midcom_exception_unauthorized("Requires logged user");
        }
        $this->current_user = $_MIDCOM->authentication->get_person();    
        
    }
    
    public function action_userinfo($route_id, &$data, $args)
    {
        $image_container = new com_rohea_images_container();
    
        if($description = $this->current_user->get_parameter($this->domain, "description"))
            $data['description'] = $description;
        
        $imageurl = $image_container->getImage($this->current_user);
        $data['imageurl'] = $imageurl;
        //echo $data['imageurl'];
        
        if ($imageurl != false) $data['hasimage'] = true;     
        else $data['hasimage'] = false;
        
        $data['posturl'] = $_MIDCOM->dispatcher->generate_url("settings_userinformation", array(), $_MIDCOM->context->get_item('page', 0));
        $url = $_MIDCOM->dispatcher->generate_url("settings", array(), $_MIDCOM->context->get_item('page', 0));
        
        if (!empty($_POST))
        {
            if (!empty($_POST['description']))
            {
                if (strlen($_POST['description']) > 160)
                {
                    $_POST['description'] = substr($_POST['description'], 0, 160);
                }
                if (!$this->current_user->set_parameter($this->domain, 'description', $_POST['description']))
                {
                    print 'Kuvauksen asetus epaonnistui'; exit();
                }
            }
            if (!empty($_FILES['image']) && $_FILES['image']['size'] != 0)
            {
                if (!$image_container->setImage($this->current_user))
                {
                    echo $image_container->getError();
                    exit();
                }
            }
            header("Location: " .  $url);
        }
        
        
    
    }

}

?>
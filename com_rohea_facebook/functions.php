<?php
class com_rohea_facebook_functions
{

    public function checkfacebooklink($facebookid) {
        $qb = new midgard_query_builder("com_rohea_facebook_link");
        $qb->add_constraint("facebookid", "=", $facebookid);
        $result = $qb->execute();
        if (sizeof($result) >0)
        {
            return $result[0]['personguid'];
        }
        else return false;
       
    }
    public function addfacebooklink($facebookid, $midgardguid) {
        
        if ($this->checkfacebooklink($facebookid) != false)
        {
           // if id has already been registered, return false
            return false;
        }
        else {
            $linking = new com_rohea_friends_request();
            $linking->facebookid = $facebookid;
            $linking->personguid = $midgardguid;
            $linking->create();     
            return true;
        }
        
    }
    

}

?>
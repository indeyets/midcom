<?php 

/**
 * midcom_helper_big_parameter
 *
 * Helper class to cope with too large parameters.
 * A nice crutch until MgdSchema takes off.
 */

class midcom_helper_big_parameter {

    /**
     *  Midgard object to attach parameters to.
     *  @access private
     * */
    var $_obj; // Midgard object to attach parameters to.

    /** 
     * Constructur function.
     * @param MidgardObject obj - the object to attach the param to.
     * */

    function midcom_helper_big_parameter(&$obj) {
        $this->_obj = &$obj;
    }

    function parameter ($domain, $name, $value = null) {
    
        if ($value == null ) {
            return $this->_readParam($domain,$name);
        } else {
            return $this->_writeParam($domain,$name,$value);
        }
    
    
    }
    
    function _readParam($domain,$name) 
    {
        $value = "";
        
        $n = $this->_obj->parameter($domain, $name ."_n");
        
        /* parameter returns an empty string if no param found. */
        if ($n != '' ) 
        {
            for ($i = 0; $i < $n ; $i++) 
            {
                $value .= $this->_obj->parameter($domain,$name . "_$i");
            }
            
        } else {
            /* note this returns "" if the parameter doesn't exist just like 
             * the normal call would.*/
            return  $this->_obj->parameter($domain, $name );
        }
    
        return $value;
    
    }
    
    function _writeParam($domain, $name, $value) 
    {
        
        $n = $this->_obj->parameter($domain,$name . "_n");
        // handle small parameters the old way.
        if (strlen($value) < 255 ) {
            // delete the old values
            if ($n > 0 ) {
                for ($i = 0; $i < $n ;$i++) {
                    $this->_obj->parameter($domain,$name . "_$i" , "");
                }
                $this->_obj->parameter($domain, $name . "_n", '');
            }
            return $this->_obj->parameter($domain,$name,$value);
        }
        
        $matches = array();
        
        preg_match_all('/.{1,' . 255 . '}/s', $value, $matches);
        
        for ($i = 0; $i < count ($matches[0]);$i++) {
            // check if we manage to set the param, otherwise return false as the
            // normal call would do.
            if (! $this->_obj->parameter($domain,$name . "_$i" , $matches[0][$i]) ) return false;
        }
        /*  Delete the old values
         *  using the old $i here! 
         *  */
        if ($n > $i ) for (;$i < $n ; $i++) $this->_obj->parameter($domain,$name . "_$i" ,'');
        
        /* if we can write one parameter we can write them all!  */
        $this->_obj->parameter($domain , $name . "_n" , count($matches[0]));
        
        /* delete the old parameter  */
        $this->_obj->parameter($domain,$name, '');
        return true;

    }



}




?>
